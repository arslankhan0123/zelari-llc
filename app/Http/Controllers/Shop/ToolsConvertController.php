<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

/**
 * High-fidelity document conversion. Engine priority:
 *  1. Adobe PDF Services API (free tier: 500 docs/month) — same engine as
 *     Acrobat, exact same-to-same conversion. Needs only ADOBE_CLIENT_ID and
 *     ADOBE_CLIENT_SECRET in .env — pure HTTPS, works on shared hosting.
 *  2. LibreOffice (soffice) / pdf2docx if installed on the machine.
 *  3. Otherwise endpoints return 503 and the frontend silently falls back
 *     to the in-browser converter.
 */
class ToolsConvertController extends Controller
{
    private const TOOLS = [
        'pdf-to-word'  => ['engine' => 'pdf2docx', 'in' => ['pdf'],           'out' => 'docx'],
        'pdf-to-excel' => ['engine' => 'adobe',    'in' => ['pdf'],           'out' => 'xlsx'],
        'html-to-pdf'  => ['engine' => 'html',     'in' => [],                'out' => 'pdf'],
        'unlock-pdf'   => ['engine' => 'adobe',    'in' => ['pdf'],           'out' => 'pdf', 'suffix' => '-unlocked'],
        'protect-pdf'  => ['engine' => 'adobe',    'in' => ['pdf'],           'out' => 'pdf', 'suffix' => '-protected'],
        'word-to-pdf'  => ['engine' => 'soffice',  'in' => ['doc', 'docx', 'rtf', 'odt'], 'out' => 'pdf'],
        'ppt-to-pdf'   => ['engine' => 'soffice',  'in' => ['ppt', 'pptx', 'odp'],        'out' => 'pdf'],
        'excel-to-pdf' => ['engine' => 'soffice',  'in' => ['xls', 'xlsx', 'csv', 'ods'], 'out' => 'pdf'],
    ];

    private const MIMES = [
        'pdf'  => 'application/pdf',
        'doc'  => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'ppt'  => 'application/vnd.ms-powerpoint',
        'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'xls'  => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'rtf'  => 'text/rtf',
    ];

    private function adobeCreds(): bool
    {
        return (bool) (env('ADOBE_CLIENT_ID') && env('ADOBE_CLIENT_SECRET'));
    }

    public function status(string $slug)
    {
        $t = self::TOOLS[$slug] ?? null;
        if (!$t) {
            return response()->json(['available' => false]);
        }
        $available = $t['engine'] === 'html'
            || $this->adobeCreds()
            || ($t['engine'] === 'soffice' ? (bool) $this->findSoffice()
                : ($t['engine'] === 'pdf2docx' ? (bool) $this->findPdf2docx() : false));

        return response()->json(['available' => $available]);
    }

    public function convert(Request $request, string $slug)
    {
        $t = self::TOOLS[$slug] ?? null;
        if (!$t) {
            return response()->json(['error' => 'Unknown tool.'], 404);
        }
        if ($slug === 'html-to-pdf') {
            return $this->htmlToPdf($request);
        }
        $request->validate(['file' => 'required|file|max:51200']);
        $password = null;
        if (in_array($slug, ['unlock-pdf', 'protect-pdf'], true)) {
            $request->validate(['password' => 'required|string|min:1|max:128']);
            $password = $request->input('password');
        }
        @set_time_limit(360);

        $file = $request->file('file');
        $origBase = preg_replace('/\.[^.]+$/', '', $file->getClientOriginalName()) ?: 'converted';
        $ext = strtolower($file->getClientOriginalExtension());
        if (!in_array($ext, $t['in'], true)) {
            return response()->json(['error' => 'Unsupported file type for this tool.'], 422);
        }

        $this->cleanupOld();
        $work = storage_path('app' . DIRECTORY_SEPARATOR . 'tools-convert' . DIRECTORY_SEPARATOR . Str::random(16));
        if (!is_dir($work)) {
            @mkdir($work, 0777, true);
        }
        $file->move($work, 'input.' . $ext);
        $inPath = $work . DIRECTORY_SEPARATOR . 'input.' . $ext;
        $outPath = $work . DIRECTORY_SEPARATOR . 'input.' . $t['out'];

        try {
            /* 1) Adobe PDF Services (exact, same-to-same) */
            if ($this->adobeCreds() && isset(self::MIMES[$ext])) {
                $adobeErr = null;
                try {
                    $this->adobeConvert($slug, $inPath, $ext, $outPath, $password);
                } catch (\Throwable $e) {
                    $adobeErr = $e->getMessage();
                    @unlink($outPath);
                }
                if (is_file($outPath) && filesize($outPath) > 100) {
                    return response()->download($outPath, $origBase . ($t['suffix'] ?? '') . '.' . $t['out'])->deleteFileAfterSend(true);
                }
                if ($password !== null) {
                    return response()->json(['error' => $slug === 'unlock-pdf'
                        ? 'Could not unlock — the password may be incorrect.'
                        : 'Could not protect this PDF: ' . Str::limit((string) $adobeErr, 100)], 422);
                }
            }

            /* 2) Local engines, if installed */
            if ($t['engine'] === 'adobe') {
                return response()->json(['error' => 'offline'], 503);
            }
            if ($t['engine'] === 'soffice') {
                $bin = $this->findSoffice();
                if (!$bin) {
                    return response()->json(['error' => 'offline'], 503);
                }
                $profile = 'file:///' . str_replace(['\\', ' '], ['/', '%20'], $work . DIRECTORY_SEPARATOR . 'lo-profile');
                $p = new Process([$bin, '--headless', '--norestore', '-env:UserInstallation=' . $profile,
                    '--convert-to', $t['out'], '--outdir', $work, $inPath]);
                $p->setTimeout(300);
                $p->run();
            } else {
                $cmd = $this->findPdf2docx();
                if (!$cmd) {
                    return response()->json(['error' => 'offline'], 503);
                }
                $p = new Process(array_merge($cmd, ['convert', $inPath, $outPath]));
                $p->setTimeout(300);
                $p->run();
            }

            if (!is_file($outPath) || filesize($outPath) < 100) {
                return response()->json(['error' => 'The server engine could not convert this file.'], 500);
            }

            return response()->download($outPath, $origBase . '.' . $t['out'])->deleteFileAfterSend(true);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Conversion failed: ' . $e->getMessage()], 500);
        }
    }

    /* ── Adobe PDF Services API (free tier) ── */

    /** @return array{0:string,1:array} [$base, $headers] */
    private function adobeAuth(): array
    {
        $base = 'https://pdf-services.adobe.io';
        $id = env('ADOBE_CLIENT_ID');
        $token = Cache::remember('adobe_pdfsvc_token', 3300, function () use ($base, $id) {
            $r = Http::asForm()->timeout(30)->post($base . '/token', [
                'client_id' => $id,
                'client_secret' => env('ADOBE_CLIENT_SECRET'),
            ]);
            if (!$r->ok() || !$r->json('access_token')) {
                throw new \RuntimeException('Adobe auth failed (' . $r->status() . ')');
            }

            return $r->json('access_token');
        });

        return [$base, ['Authorization' => 'Bearer ' . $token, 'x-api-key' => $id]];
    }

    private function adobePoll(string $loc, array $H, string $out): void
    {
        $deadline = time() + 240;
        while (time() < $deadline) {
            sleep(2);
            $st = Http::withHeaders($H)->timeout(30)->get($loc);
            $status = $st->json('status');
            if ($status === 'done') {
                $uri = $st->json('asset.downloadUri') ?: ($st->json('content.downloadUri') ?: $st->json('resource.downloadUri'));
                if (!$uri) {
                    throw new \RuntimeException('Adobe: no download URI in result.');
                }
                $dl = Http::timeout(180)->get($uri);
                if ($dl->failed()) {
                    throw new \RuntimeException('Adobe download failed (' . $dl->status() . ')');
                }
                file_put_contents($out, $dl->body());

                return;
            }
            if ($status === 'failed') {
                throw new \RuntimeException('Adobe reported the conversion as failed.');
            }
        }
        throw new \RuntimeException('Adobe conversion timed out.');
    }

    private function adobeConvert(string $slug, string $in, string $ext, string $out, ?string $password = null): void
    {
        [$base, $H] = $this->adobeAuth();
        $mime = self::MIMES[$ext];

        $a = Http::withHeaders($H)->timeout(30)->post($base . '/assets', ['mediaType' => $mime]);
        if (!$a->ok() || !$a->json('uploadUri')) {
            throw new \RuntimeException('Adobe asset request failed (' . $a->status() . ')');
        }
        $assetId = $a->json('assetID');

        $up = Http::withHeaders(['Content-Type' => $mime])->timeout(180)
            ->withBody(file_get_contents($in), $mime)->put($a->json('uploadUri'));
        if ($up->failed()) {
            throw new \RuntimeException('Adobe upload failed (' . $up->status() . ')');
        }

        if ($slug === 'unlock-pdf') {
            $op = 'removeprotection';
            $body = ['assetID' => $assetId, 'password' => (string) $password];
        } elseif ($slug === 'protect-pdf') {
            $op = 'protectpdf';
            $body = ['assetID' => $assetId, 'encryptionAlgorithm' => 'AES_256',
                'passwordProtection' => ['userPassword' => (string) $password]];
        } elseif (str_starts_with($slug, 'pdf-to-')) {
            $op = 'exportpdf';
            $body = ['assetID' => $assetId, 'targetFormat' => $slug === 'pdf-to-excel' ? 'xlsx' : 'docx'];
        } else {
            $op = 'createpdf';
            $body = ['assetID' => $assetId];
        }
        $j = Http::withHeaders($H)->timeout(30)->post($base . '/operation/' . $op, $body);
        if ($j->status() !== 201 || !$j->header('location')) {
            throw new \RuntimeException('Adobe job failed (' . $j->status() . '): ' . Str::limit($j->body(), 120));
        }
        $this->adobePoll($j->header('location'), $H, $out);
        try {
            Http::withHeaders($H)->timeout(15)->delete($base . '/assets/' . $assetId);
        } catch (\Throwable $e) {
        }
    }

    private function htmlToPdf(Request $request)
    {
        $request->validate(['url' => 'required|string|max:2000']);
        $url = trim($request->input('url'));
        if (!preg_match('~^https?://~i', $url)) {
            return response()->json(['error' => 'Enter a full URL starting with http:// or https://'], 422);
        }
        @set_time_limit(300);
        $host = parse_url($url, PHP_URL_HOST) ?: 'webpage';
        $isLocal = (bool) preg_match('/^(localhost|127\.|0\.|10\.|192\.168\.|172\.(1[6-9]|2\d|3[01])\.)/i', $host);

        $this->cleanupOld();
        $work = storage_path('app' . DIRECTORY_SEPARATOR . 'tools-convert' . DIRECTORY_SEPARATOR . Str::random(16));
        @mkdir($work, 0777, true);
        $out = $work . DIRECTORY_SEPARATOR . 'page.pdf';

        /* 1) Adobe HTML-to-PDF (real browser rendering) for public URLs */
        if ($this->adobeCreds() && !$isLocal) {
            try {
                [$base, $H] = $this->adobeAuth();
                $j = Http::withHeaders($H)->timeout(30)->post($base . '/operation/htmltopdf', [
                    'inputUrl' => $url,
                    'json' => '{}',
                    'pageLayout' => ['pageWidth' => 8.27, 'pageHeight' => 11.69],
                ]);
                if ($j->status() === 201 && $j->header('location')) {
                    $this->adobePoll($j->header('location'), $H, $out);
                }
            } catch (\Throwable $e) {
                @unlink($out);
            }
        }

        /* 2) dompdf fallback (simple pages) */
        if (!is_file($out) || filesize($out) < 100) {
            try {
                $resp = Http::timeout(30)->withHeaders(['User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'])->get($url);
                if ($resp->failed()) {
                    return response()->json(['error' => 'Could not fetch that URL (' . $resp->status() . ').'], 422);
                }
                $html = $resp->body();
                $baseTag = '<base href="' . e($url) . '">';
                if (stripos($html, '<head') !== false) {
                    $html = preg_replace('/<head([^>]*)>/i', '<head$1>' . $baseTag, $html, 1);
                } else {
                    $html = $baseTag . $html;
                }
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::setOptions(['isRemoteEnabled' => true, 'isHtml5ParserEnabled' => true])
                    ->loadHTML($html)->setPaper('a4');
                file_put_contents($out, $pdf->output());
            } catch (\Throwable $e) {
                return response()->json(['error' => 'Could not convert this page: ' . Str::limit($e->getMessage(), 120)], 500);
            }
        }

        if (!is_file($out) || filesize($out) < 100) {
            return response()->json(['error' => 'Conversion produced no output.'], 500);
        }

        return response()->download($out, $host . '.pdf')->deleteFileAfterSend(true);
    }

    /* ── engine discovery (free, open-source binaries) ── */

    private function findSoffice(): ?string
    {
        $candidates = array_filter([
            env('SOFFICE_PATH'),
            'C:\\Program Files\\LibreOffice\\program\\soffice.exe',
            'C:\\Program Files (x86)\\LibreOffice\\program\\soffice.exe',
            '/usr/bin/soffice', '/usr/bin/libreoffice', '/usr/local/bin/soffice', '/snap/bin/libreoffice',
        ]);
        foreach ($candidates as $c) {
            if (is_file($c)) {
                return $c;
            }
        }
        foreach (['soffice', 'libreoffice'] as $name) {
            try {
                $p = new Process([$name, '--version']);
                $p->setTimeout(15);
                $p->run();
                if ($p->isSuccessful()) {
                    return $name;
                }
            } catch (\Throwable $e) {
            }
        }

        return null;
    }

    /** @return array<string>|null argv prefix, e.g. ['py','-m','pdf2docx'] */
    private function findPdf2docx(): ?array
    {
        if ($env = env('PDF2DOCX_CMD')) {
            return preg_split('/\s+/', trim($env));
        }
        foreach (['py', 'python', 'python3'] as $py) {
            try {
                $p = new Process([$py, '-c', 'import pdf2docx']);
                $p->setTimeout(25);
                $p->run();
                if ($p->isSuccessful()) {
                    return [$py, '-m', 'pdf2docx'];
                }
            } catch (\Throwable $e) {
            }
        }

        return null;
    }

    private function cleanupOld(): void
    {
        $root = storage_path('app' . DIRECTORY_SEPARATOR . 'tools-convert');
        if (!is_dir($root)) {
            return;
        }
        foreach (glob($root . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR) ?: [] as $dir) {
            if (filemtime($dir) < time() - 3600) {
                $this->rrmdir($dir);
            }
        }
    }

    private function rrmdir(string $dir): void
    {
        foreach (glob($dir . DIRECTORY_SEPARATOR . '{,.}*', GLOB_BRACE) ?: [] as $f) {
            $b = basename($f);
            if ($b === '.' || $b === '..') {
                continue;
            }
            is_dir($f) ? $this->rrmdir($f) : @unlink($f);
        }
        @rmdir($dir);
    }
}
