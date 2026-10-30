<?php

namespace App\Support;

use App\Models\Product;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/**
 * Browser-free, API-free on-site SEO auditor.
 * Crawls the store's own key pages over HTTP and checks the product catalog
 * in the database, scores SEO health, and saves a dated JSON report.
 * Runs daily via the scheduler (command seo:audit) and on demand from admin.
 */
class SeoAudit
{
    const DIR = 'seo';
    const PRODUCT_SAMPLE = 15;   // product pages fetched over HTTP each run
    const KEEP_DAYS = 30;

    /** Run a full audit, save it, and return the report array. */
    public static function run(?string $base = null): array
    {
        $base = rtrim($base ?: config('app.url'), '/');
        $pages = self::pageList($base);
        $pageReports = [];
        foreach ($pages as $p) {
            $pageReports[] = self::auditUrl($p['url'], $p['label']);
        }

        $catalog = self::auditCatalog();

        // Scores
        $ok = array_values(array_filter($pageReports, fn ($r) => $r['status'] === 200));
        $pageScore = count($ok) ? (int) round(array_sum(array_column($ok, 'score')) / count($ok)) : 0;
        $overall = (int) round($pageScore * 0.7 + $catalog['health'] * 0.3);

        // Aggregate issue counts
        $counts = [];
        foreach ($pageReports as $r) {
            foreach ($r['issues'] as $i) {
                $key = $i['type'];
                $counts[$key] = ($counts[$key] ?? 0) + 1;
            }
        }
        arsort($counts);

        $report = [
            'date'            => now()->toDateString(),
            'generated_at'    => now()->toIso8601String(),
            'score'           => $overall,
            'page_score'      => $pageScore,
            'catalog_health'  => $catalog['health'],
            'pages_audited'   => count($pageReports),
            'pages_ok'        => count($ok),
            'products_checked'=> $catalog['total'],
            'issue_counts'    => $counts,
            'pages'           => $pageReports,
            'catalog'         => $catalog,
            'recommendations' => self::recommend($counts, $catalog),
        ];

        self::save($report);

        return $report;
    }

    /** Key public pages to crawl + a sample of active product pages. */
    private static function pageList(string $base): array
    {
        $list = [
            ['url' => $base.'/',         'label' => 'Home'],
            ['url' => $base.'/shop',     'label' => 'Shop'],
            ['url' => $base.'/brands',   'label' => 'Brands'],
            ['url' => $base.'/about',    'label' => 'About'],
            ['url' => $base.'/sourcing', 'label' => 'Sourcing'],
        ];

        $sections = [
            ['\\App\\Support\\PdfTools',     '/pdf-tools',    'PDF Tools'],
            ['\\App\\Support\\Calculators',  '/calculators',  'Calculators'],
            ['\\App\\Support\\ImageTools',   '/image-tools',  'Image Tools'],
            ['\\App\\Support\\TextTools',    '/text-tools',   'Text Tools'],
            ['\\App\\Support\\DevTools',     '/dev-tools',    'Developer Tools'],
            ['\\App\\Support\\QrTools',      '/qr-tools',     'QR & Barcode'],
            ['\\App\\Support\\ColorTools',   '/color-tools',  'Color Tools'],
            ['\\App\\Support\\RandomTools',  '/random-tools', 'Random Tools'],
            ['\\App\\Support\\SellerTools',  '/seller-tools', 'Seller Tools'],
        ];
        foreach ($sections as [$cls, $path, $label]) {
            try {
                if (class_exists($cls) && $cls::enabled()) {
                    $list[] = ['url' => $base.$path, 'label' => $label];
                }
            } catch (\Throwable $e) {
            }
        }

        try {
            Product::where('status', 'active')->whereNotNull('slug')
                ->latest()->take(self::PRODUCT_SAMPLE)->get(['slug', 'name'])
                ->each(function ($p) use (&$list) {
                    $list[] = ['url' => $base.'/product/'.$p->slug, 'label' => 'Product: '.$p->name];
                });
        } catch (\Throwable $e) {
        }

        return $list;
    }

    /** Fetch one URL and grade its on-page SEO. */
    private static function auditUrl(string $url, string $label): array
    {
        $out = ['url' => $url, 'label' => $label, 'status' => 0, 'score' => 0, 'issues' => [],
                'title_len' => 0, 'desc_len' => 0, 'h1' => 0, 'words' => 0];
        try {
            $res = Http::timeout(6)->withHeaders(['User-Agent' => 'ZelariSEO/1.0'])->get($url);
            $out['status'] = $res->status();
            if (!$res->ok()) {
                $out['issues'][] = ['type' => 'Page error', 'sev' => 'high', 'detail' => 'Returned HTTP '.$res->status()];
                return $out;
            }
            $html = $res->body();
        } catch (\Throwable $e) {
            $out['issues'][] = ['type' => 'Unreachable', 'sev' => 'high', 'detail' => 'Could not fetch page'];
            return $out;
        }

        $score = 100;
        $add = function ($type, $sev, $detail, $deduct) use (&$out, &$score) {
            $out['issues'][] = ['type' => $type, 'sev' => $sev, 'detail' => $detail];
            $score -= $deduct;
        };

        libxml_use_internal_errors(true);
        $dom = new \DOMDocument();
        $dom->loadHTML('<?xml encoding="utf-8" ?>'.$html);
        $xp = new \DOMXPath($dom);

        // Title
        $title = '';
        $tn = $dom->getElementsByTagName('title');
        if ($tn->length) $title = trim($tn->item(0)->textContent);
        $out['title_len'] = mb_strlen($title);
        if ($title === '') $add('Missing title', 'high', 'Page has no <title>', 25);
        elseif ($out['title_len'] < 30) $add('Short title', 'low', 'Title is '.$out['title_len'].' chars (aim 30–60)', 8);
        elseif ($out['title_len'] > 65) $add('Long title', 'low', 'Title is '.$out['title_len'].' chars (aim 30–60)', 6);

        // Meta description
        $desc = '';
        $dn = $xp->query('//meta[@name="description"]/@content');
        if ($dn->length) $desc = trim($dn->item(0)->nodeValue);
        $out['desc_len'] = mb_strlen($desc);
        if ($desc === '') $add('Missing meta description', 'high', 'No meta description tag', 20);
        elseif ($out['desc_len'] < 70) $add('Short meta description', 'low', $out['desc_len'].' chars (aim 70–160)', 8);
        elseif ($out['desc_len'] > 165) $add('Long meta description', 'low', $out['desc_len'].' chars (aim 70–160)', 6);

        // H1
        $out['h1'] = $dom->getElementsByTagName('h1')->length;
        if ($out['h1'] === 0) $add('No H1 heading', 'medium', 'Page has no <h1>', 15);
        elseif ($out['h1'] > 1) $add('Multiple H1s', 'low', $out['h1'].' <h1> tags found', 5);

        // Canonical
        if (!$xp->query('//link[@rel="canonical"]')->length)
            $add('No canonical tag', 'low', 'Missing rel="canonical"', 6);

        // Viewport
        if (!$xp->query('//meta[@name="viewport"]')->length)
            $add('No viewport meta', 'medium', 'Not mobile-friendly declared', 10);

        // OG image
        if (!$xp->query('//meta[@property="og:image"]')->length)
            $add('No social image', 'low', 'Missing og:image for sharing', 5);

        // Image alt coverage
        $imgs = $dom->getElementsByTagName('img');
        $total = $imgs->length; $noAlt = 0;
        foreach ($imgs as $img) { if (trim($img->getAttribute('alt')) === '') $noAlt++; }
        if ($total > 0 && $noAlt / $total > 0.3)
            $add('Images missing alt', 'low', $noAlt.' of '.$total.' images have no alt text', 8);

        // Word count
        $body = $dom->getElementsByTagName('body');
        $text = $body->length ? preg_replace('/\s+/', ' ', trim($body->item(0)->textContent)) : '';
        $out['words'] = $text === '' ? 0 : count(explode(' ', $text));
        if ($out['words'] < 150) $add('Thin content', 'low', 'Only '.$out['words'].' words', 8);

        libxml_clear_errors();
        $out['score'] = max(0, min(100, $score));
        return $out;
    }

    /** Check every active product's SEO-relevant fields from the DB (fast, no HTTP). */
    private static function auditCatalog(): array
    {
        $r = ['total' => 0, 'missing_desc' => 0, 'missing_image' => 0, 'missing_slug' => 0,
              'with_issues' => 0, 'health' => 100, 'examples' => []];
        try {
            $products = Product::where('status', 'active')->get(['id', 'name', 'slug', 'short_description', 'description', 'feature_image']);
        } catch (\Throwable $e) {
            return $r;
        }
        $r['total'] = $products->count();
        if ($r['total'] === 0) return $r;

        foreach ($products as $p) {
            $issues = [];
            $desc = trim(strip_tags((string) ($p->short_description ?: $p->description)));
            if ($desc === '' || mb_strlen($desc) < 40) $issues[] = 'description';
            if (!$p->feature_image) $issues[] = 'image';
            if (!$p->slug) $issues[] = 'slug';

            if (in_array('description', $issues)) $r['missing_desc']++;
            if (in_array('image', $issues)) $r['missing_image']++;
            if (in_array('slug', $issues)) $r['missing_slug']++;
            if ($issues) {
                $r['with_issues']++;
                if (count($r['examples']) < 12) {
                    $r['examples'][] = ['id' => $p->id, 'name' => $p->name, 'slug' => $p->slug, 'issues' => $issues];
                }
            }
        }
        $r['health'] = (int) round(($r['total'] - $r['with_issues']) / $r['total'] * 100);
        return $r;
    }

    private static function recommend(array $counts, array $catalog): array
    {
        $rec = [];
        foreach ($counts as $type => $n) {
            $rec[] = $n.' page'.($n > 1 ? 's' : '').' — '.strtolower($type);
        }
        if ($catalog['missing_desc']) $rec[] = $catalog['missing_desc'].' product(s) need a longer description';
        if ($catalog['missing_image']) $rec[] = $catalog['missing_image'].' product(s) have no image';
        return array_slice($rec, 0, 8);
    }

    private static function save(array $report): void
    {
        try {
            Storage::disk('local')->put(self::DIR.'/'.$report['date'].'.json', json_encode($report));
            Storage::disk('local')->put(self::DIR.'/latest.json', json_encode($report));
            // prune
            $files = collect(Storage::disk('local')->files(self::DIR))
                ->filter(fn ($f) => preg_match('/\d{4}-\d{2}-\d{2}\.json$/', $f))->sort()->values();
            if ($files->count() > self::KEEP_DAYS) {
                $files->slice(0, $files->count() - self::KEEP_DAYS)->each(fn ($f) => Storage::disk('local')->delete($f));
            }
        } catch (\Throwable $e) {
        }
    }

    public static function latest(): ?array
    {
        try {
            if (Storage::disk('local')->exists(self::DIR.'/latest.json'))
                return json_decode(Storage::disk('local')->get(self::DIR.'/latest.json'), true);
        } catch (\Throwable $e) {}
        return null;
    }

    /** Last N daily scores for the trend chart, oldest first. */
    public static function history(int $days = 14): array
    {
        try {
            $files = collect(Storage::disk('local')->files(self::DIR))
                ->filter(fn ($f) => preg_match('/\d{4}-\d{2}-\d{2}\.json$/', $f))
                ->sort()->values()->slice(-$days);
            return $files->map(function ($f) {
                $d = json_decode(Storage::disk('local')->get($f), true);
                return ['date' => $d['date'] ?? '', 'score' => $d['score'] ?? 0];
            })->values()->all();
        } catch (\Throwable $e) {
            return [];
        }
    }
}
