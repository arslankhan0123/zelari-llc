<?php

namespace App\Console\Commands;

use App\Support\SeoAudit;
use Illuminate\Console\Command;

class SeoAuditCommand extends Command
{
    protected $signature = 'seo:audit {--base= : Override the base URL to crawl}';
    protected $description = 'Run the on-site SEO audit and save a dated report';

    public function handle(): int
    {
        $this->info('Running SEO audit...');
        $r = SeoAudit::run($this->option('base') ?: null);
        $this->info('Done — overall score '.$r['score'].'/100 across '.$r['pages_audited'].' pages, '.$r['products_checked'].' products.');
        return self::SUCCESS;
    }
}
