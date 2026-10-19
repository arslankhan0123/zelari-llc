<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

/**
 * Admin → Website Content: the dynamic sections of the storefront
 * (shipping promises, deal-of-the-week banner, FAQs). Each card posts
 * to update() with its own section name so cards save independently.
 */
class ContentController extends Controller
{
    public function index()
    {
        $settings = Setting::allValues();
        return view('admin.content.index', compact('settings'));
    }

    public function update(Request $request, string $section)
    {
        $rules = match ($section) {
            'shipping' => [
                'ship_free_over_usd' => 'required|numeric|min:0|max:100000',
                'ship_fee_usd'       => 'required|numeric|min:0|max:10000',
                'ship_delivery_days' => 'required|string|max:20',
                'ship_pack_hours'    => 'required|integer|min:1|max:720',
                'ship_return_days'   => 'required|integer|min:0|max:365',
                'ship_coverage'      => 'nullable|string|max:60',
            ],
            'deal' => [
                'deal_enabled'       => 'nullable|boolean',
                'deal_eyebrow'       => 'nullable|string|max:40',
                'deal_title'         => 'required|string|max:60',
                'deal_title_accent'  => 'nullable|string|max:60',
                'deal_text'          => 'nullable|string|max:300',
                'deal_badge'         => 'nullable|string|max:30',
                'deal_button'        => 'nullable|string|max:30',
                'deal_ends_at'       => 'nullable|date',
                'deal_link'          => 'nullable|url|max:255',
            ],
            'promo' => [
                'promo_enabled'       => 'nullable|boolean',
                'promo_title'         => 'required|string|max:80',
                'promo_title_accent'  => 'nullable|string|max:80',
                'promo_text'          => 'nullable|string|max:300',
                'promo_btn1_label'    => 'nullable|string|max:30',
                'promo_btn1_link'     => 'nullable|url|max:255',
                'promo_btn2_label'    => 'nullable|string|max:30',
                'promo_btn2_link'     => 'nullable|url|max:255',
                'promo_perk1_icon' => 'nullable|string|max:40', 'promo_perk1_title' => 'nullable|string|max:40', 'promo_perk1_text' => 'nullable|string|max:80',
                'promo_perk2_icon' => 'nullable|string|max:40', 'promo_perk2_title' => 'nullable|string|max:40', 'promo_perk2_text' => 'nullable|string|max:80',
                'promo_perk3_icon' => 'nullable|string|max:40', 'promo_perk3_title' => 'nullable|string|max:40', 'promo_perk3_text' => 'nullable|string|max:80',
                'promo_perk4_icon' => 'nullable|string|max:40', 'promo_perk4_title' => 'nullable|string|max:40', 'promo_perk4_text' => 'nullable|string|max:80',
            ],
            'faqs' => [
                'store_faqs'         => 'nullable|string|max:8000',
            ],
            'auth' => [
                'auth_title'  => 'required|string|max:90',
                'auth_text'   => 'nullable|string|max:200',
                'auth_perk1_icon' => 'nullable|string|max:40', 'auth_perk1_title' => 'nullable|string|max:40', 'auth_perk1_text' => 'nullable|string|max:80',
                'auth_perk2_icon' => 'nullable|string|max:40', 'auth_perk2_title' => 'nullable|string|max:40', 'auth_perk2_text' => 'nullable|string|max:80',
                'auth_perk3_icon' => 'nullable|string|max:40', 'auth_perk3_title' => 'nullable|string|max:40', 'auth_perk3_text' => 'nullable|string|max:80',
                'auth_perk4_icon' => 'nullable|string|max:40', 'auth_perk4_title' => 'nullable|string|max:40', 'auth_perk4_text' => 'nullable|string|max:80',
            ],
            default => abort(404),
        };

        $data = $request->validate($rules);

        if ($section === 'shipping') {
            $data['ship_free_over_usd'] = (string) round((float) $data['ship_free_over_usd'], 2);
            $data['ship_fee_usd']       = (string) round((float) $data['ship_fee_usd'], 2);
            $data['ship_coverage']      = trim((string) ($data['ship_coverage'] ?? '')) ?: 'nationwide';
        }
        if ($section === 'deal') {
            $data['deal_enabled'] = $request->boolean('deal_enabled') ? '1' : '0';
            $data['deal_ends_at'] = !empty($data['deal_ends_at'])
                ? \Carbon\Carbon::parse($data['deal_ends_at'])->format('Y-m-d H:i')
                : '';
            foreach (['deal_eyebrow', 'deal_title_accent', 'deal_text', 'deal_badge', 'deal_button', 'deal_link'] as $k) {
                $data[$k] = trim((string) ($data[$k] ?? ''));
            }
        }
        if ($section === 'promo' || $section === 'auth') {
            if ($section === 'promo') $data['promo_enabled'] = $request->boolean('promo_enabled') ? '1' : '0';
            foreach ($data as $k => $v) {
                if ($k === 'promo_enabled') continue;
                $v = trim((string) $v);
                if (str_ends_with($k, '_icon')) {
                    $v = preg_replace('/[^a-z0-9\-]/', '', strtolower($v));
                    if ($v !== '' && !str_starts_with($v, 'fa-')) $v = 'fa-' . $v;
                }
                $data[$k] = $v;
            }
        }
        if ($section === 'faqs') {
            // normalise: drop lines without a "|" so the storefront never shows half a FAQ
            $lines = array_filter(array_map('trim', preg_split('/\r?\n/', (string) ($data['store_faqs'] ?? ''))), fn ($l) => str_contains($l, '|'));
            $data['store_faqs'] = implode("\n", $lines);
        }

        Setting::setMany($data);

        $label = ['shipping' => 'Shipping & delivery', 'deal' => 'Deal of the week banner', 'promo' => 'Promo banner', 'faqs' => 'FAQs', 'auth' => 'Login & register pages'][$section];
        return redirect()->route('admin.content.index', ['#' . $section])->with('success', "$label saved.");
    }
}
