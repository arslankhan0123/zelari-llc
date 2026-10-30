<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Support\Calculators;
use App\Support\ImageTools;
use App\Support\PdfTools;
use Illuminate\Support\Facades\Route;

/**
 * Dynamic XML sitemap at /sitemap.xml.
 *
 * Fully automatic: it auto-discovers every public, indexable page from the
 * app's route table, so ANY new static page you add later (e.g. /faq, /blog)
 * appears here with no code change. It also lists every active product and
 * every live tool detail page. Private/transactional pages are excluded.
 * Cached 1h; the Product model busts the cache on any catalog change.
 */
class SitemapController extends Controller
{
    /** Path prefixes that must never appear in the sitemap (private / transactional / system). */
    private const BLOCKED = [
        'admin', 'account', 'cart', 'checkout', 'wishlist', 'order',
        'order-confirmation', 'coupon', 'wheel', 'chatbot', 'login',
        'register', 'logout', 'password', 'forgot-password', 'reset-password',
        'email', 'verify', 'two-factor', 'api', 'storage', 'sanctum',
        'livewire', 'up', '_ignition', 'sitemap.xml', 'robots.txt',
    ];

    public function index()
    {
        $xml = cache()->remember('sitemap.xml', 3600, function () {
            $urls = [];
            $seen = [];

            $add = function (string $loc, ?string $lastmod = null, string $freq = 'weekly', string $priority = '0.6') use (&$urls, &$seen) {
                if (isset($seen[$loc])) {
                    return;
                }
                $seen[$loc] = true;
                $urls[] = compact('loc', 'lastmod', 'freq', 'priority');
            };

            // 1) Auto-discover public, parameterless, non-auth GET pages.
            foreach (Route::getRoutes() as $route) {
                if (!in_array('GET', $route->methods(), true)) {
                    continue;
                }
                $uri = $route->uri();
                if (str_contains($uri, '{')) {
                    continue; // skip pages that need a parameter (handled below)
                }
                $mw = $route->gatherMiddleware();
                if (in_array('auth', $mw, true) || in_array('auth:web', $mw, true)) {
                    continue; // skip login-only pages
                }
                $path = trim($uri, '/');
                $first = $path === '' ? '' : explode('/', $path)[0];
                if ($path !== '' && in_array($first, self::BLOCKED, true)) {
                    continue;
                }

                $loc = $path === '' ? url('/') : url('/' . $path);
                $priority = $path === '' ? '1.0' : ($path === 'shop' ? '0.9' : '0.6');
                $freq = in_array($path, ['', 'shop'], true) ? 'daily' : 'weekly';
                $add($loc, null, $freq, $priority);
            }

            // 2) Live tool DETAIL pages across every tool section (parameterized).
            $suites = [
                [\App\Support\PdfTools::class, 'tools.show'],
                [\App\Support\Calculators::class, 'calculators.show'],
                [\App\Support\ImageTools::class, 'imgtools.show'],
                [\App\Support\TextTools::class, 'texttools.show'],
                [\App\Support\DevTools::class, 'devtools.show'],
                [\App\Support\QrTools::class, 'qrtools.show'],
                [\App\Support\ColorTools::class, 'colortools.show'],
                [\App\Support\RandomTools::class, 'randomtools.show'],
                [\App\Support\SellerTools::class, 'sellertools.show'],
            ];
            foreach ($suites as [$cls, $rn]) {
                if (!$cls::enabled()) { continue; }
                foreach ($cls::all() as $s => $t) {
                    if ($t['live'] ?? false) {
                        $add(route($rn, $s), null, 'monthly', '0.6');
                    }
                }
            }

            // 3) Active products (dynamic from DB).
            Product::where('status', 'active')
                ->whereNotNull('slug')
                ->select('slug', 'updated_at')
                ->orderByDesc('updated_at')
                ->chunk(500, function ($chunk) use ($add) {
                    foreach ($chunk as $p) {
                        $add(
                            route('shop.show', $p->slug),
                            optional($p->updated_at)->toDateString(),
                            'weekly',
                            '0.8'
                        );
                    }
                });

            // Build XML
            $out = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
            $out .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
            foreach ($urls as $u) {
                $out .= "  <url>\n";
                $out .= '    <loc>' . htmlspecialchars($u['loc'], ENT_XML1) . "</loc>\n";
                if (!empty($u['lastmod'])) {
                    $out .= '    <lastmod>' . $u['lastmod'] . "</lastmod>\n";
                }
                $out .= '    <changefreq>' . $u['freq'] . "</changefreq>\n";
                $out .= '    <priority>' . $u['priority'] . "</priority>\n";
                $out .= "  </url>\n";
            }
            $out .= '</urlset>';

            return $out;
        });

        return response($xml, 200)->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
