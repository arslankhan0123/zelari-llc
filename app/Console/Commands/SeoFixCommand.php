<?php

namespace App\Console\Commands;

use App\Support\SeoFix;
use Illuminate\Console\Command;

class SeoFixCommand extends Command
{
    protected $signature = 'seo:fix';
    protected $description = 'Apply safe automatic SEO fixes (slug repair, etc.)';

    public function handle(): int
    {
        $f = SeoFix::run();
        $this->info('SEO auto-fix done — slugs repaired: '.$f['slugs']);
        return self::SUCCESS;
    }
}
