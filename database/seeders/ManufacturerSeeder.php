<?php

namespace Database\Seeders;

use App\Models\Manufacturer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * Demo brands for the Manufacturers section.
 *
 * These are invented brands with original logos (already in
 * storage/app/public/manufacturers), so nothing here uses a real company's
 * trademark. Replace them with your real suppliers when you go live.
 *
 *   php artisan db:seed --class=ManufacturerSeeder
 *
 * Safe to re-run: brands are matched by slug and updated, never duplicated.
 */
class ManufacturerSeeder extends Seeder
{
    public function run(): void
    {
        $brands = [
            [
                'slug'             => 'lumora',
                'name'             => 'Lumora',
                'country'          => 'France',
                'established_year' => 2016,
                'website'          => 'https://www.lumora.example',
                'email'            => 'hello@lumora.example',
                'phone'            => '+33 1 86 00 11 22',
                'description'      => 'Hair and skin care built around light, water-based formulas. Sulphate-free shampoos, conditioners and serums for everyday use.',
                'featured'         => true,
                'sort_order'       => 10,
            ],
            [
                'slug'             => 'verdela',
                'name'             => 'Verdela',
                'country'          => 'Pakistan',
                'established_year' => 2019,
                'website'          => 'https://www.verdela.example',
                'email'            => 'care@verdela.example',
                'phone'            => '+92 21 111 220 330',
                'description'      => 'Botanical skincare made in small batches — cold-pressed oils, clay masks and herbal cleansers with no synthetic fragrance.',
                'featured'         => false,
                'sort_order'       => 20,
            ],
            [
                'slug'             => 'northfell',
                'name'             => 'Northfell',
                'country'          => 'United States',
                'established_year' => 1998,
                'website'          => 'https://www.northfell.example',
                'email'            => 'wholesale@northfell.example',
                'phone'            => '+1 503 555 0142',
                'description'      => 'Denim supply co. making heavyweight jeans, jackets and work shirts designed to be worn in and repaired, not replaced.',
                'featured'         => true,
                'sort_order'       => 30,
            ],
            [
                'slug'             => 'noor-thread',
                'name'             => 'Noor & Thread',
                'country'          => 'Pakistan',
                'established_year' => 2011,
                'website'          => 'https://www.noorandthread.example',
                'email'            => 'orders@noorandthread.example',
                'phone'            => '+92 42 111 440 550',
                'description'      => 'Pret and unstitched collections with hand-finished embroidery, from everyday lawn to formal festive wear.',
                'featured'         => false,
                'sort_order'       => 40,
            ],
            [
                'slug'             => 'voltnine',
                'name'             => 'VoltNine',
                'country'          => 'South Korea',
                'established_year' => 2008,
                'website'          => 'https://www.voltnine.example',
                'email'            => 'support@voltnine.example',
                'phone'            => '+82 2 6000 1190',
                'description'      => 'Consumer electronics for small spaces — wireless audio, fast chargers, power banks and compact displays.',
                'featured'         => true,
                'sort_order'       => 50,
            ],
            [
                'slug'             => 'hearthline',
                'name'             => 'Hearthline',
                'country'          => 'Germany',
                'established_year' => 1974,
                'website'          => 'https://www.hearthline.example',
                'email'            => 'service@hearthline.example',
                'phone'            => '+49 30 5557 0088',
                'description'      => 'Home appliances with a long service life — ovens, hobs, kettles and air fryers, with spare parts kept in stock for ten years.',
                'featured'         => false,
                'sort_order'       => 60,
            ],
            [
                'slug'             => 'paceform',
                'name'             => 'Paceform',
                'country'          => 'United Kingdom',
                'established_year' => 2014,
                'website'          => 'https://www.paceform.example',
                'email'            => 'team@paceform.example',
                'phone'            => '+44 161 555 0117',
                'description'      => 'Running and training footwear with a focus on cushioning that holds its shape past 500 km. Also makes technical socks and tees.',
                'featured'         => false,
                'sort_order'       => 70,
            ],
            [
                'slug'             => 'zafran-house',
                'name'             => 'Zafran House',
                'country'          => 'Pakistan',
                'established_year' => 1986,
                'website'          => 'https://www.zafranhouse.example',
                'email'            => 'sales@zafranhouse.example',
                'phone'            => '+92 21 111 990 110',
                'description'      => 'House of spice — single-origin saffron, whole spices and recipe masalas ground in small batches and sealed the same day.',
                'featured'         => false,
                'sort_order'       => 80,
            ],
        ];

        foreach ($brands as $b) {
            // Only set the logo when the file is actually there
            $logo = 'manufacturers/' . $b['slug'] . '.png';
            $b['logo'] = Storage::disk('public')->exists($logo) ? $logo : null;
            $b['status'] = 'active';

            $existing = Manufacturer::where('slug', $b['slug'])->first();

            // Don't wipe a logo someone uploaded by hand
            if ($existing && $existing->logo && $b['logo'] === null) {
                unset($b['logo']);
            }

            Manufacturer::updateOrCreate(['slug' => $b['slug']], $b);

            $this->command?->info(sprintf('%-16s %s', $b['name'], $b['logo'] ?? 'no logo file'));
        }
    }
}
