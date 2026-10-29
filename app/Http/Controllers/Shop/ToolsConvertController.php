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
        $available = $this->adobeCreds()
            || ($t['engine'] === 'soffice' ? (bool) $this->findSoffice() : (bool) $this->findPdf2docx());

        return response()->json(['available' => $available]);
    }

    public function convert(Request $request, string $slug)
    {
        $t = self::TOOLS[$slug] ?? null;
        if (!$t) {
            return response()->json(['error' => 'Unknown tool.'], 404);
        }
        $request->validate(['file' => 'required|file|max:51200']);
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
                try {
                    $this->adobeConvert($slug, $inPath, $ext, $outPath);
                } catch (\Throwable $e) {
                    @unlink($outPath);
                }
                if (is_file($outPath) && filesize($outPath) > 100) {
                    return response()->download($outPath, $origBase . '.' . $t['out'])->deleteFileAfterSend(true);
                }
            }

            /* 2) Local engines, if installed */
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

    private function adobeConvert(string $slug, string $in, string $ext, string $out): void
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
        $H = ['Authorization' => 'Bearer ' . $token, 'x-api-key' => $id];
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

        if ($slug === 'pdf-to-word') {
            $op = 'exportpdf';
            $body = ['assetID' => $assetId, 'targetFormat' => 'docx'];
        } else {
            $op = 'createpdf';
            $body = ['assetID' => $assetId];
        }
        $j = Http::withHeaders($H)->timeout(30)->post($base . '/operation/' . $op, $body);
        if ($j->status() !== 201 || !$j->header('location')) {
            throw new \RuntimeException('Adobe job failed (' . $j->status() . '): ' . Str::limit($j->body(), 120));
        }
        $loc = $j->header('location');

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
                try {
                    Http::withHeaders($H)->timeout(15)->delete($base . '/assets/' . $assetId);
                } catch (\Throwable $e) {
                }

                return;
            }
            if ($status === 'failed') {
                throw new \RuntimeException('Adobe reported the conversion as failed.');
            }
        }
        throw new \RuntimeException('Adobe conversion timed out.');
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
