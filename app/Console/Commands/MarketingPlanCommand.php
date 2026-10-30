<?php

namespace App\Console\Commands;

use App\Support\MarketingPlan;
use Illuminate\Console\Command;

class MarketingPlanCommand extends Command
{
    protected $signature = 'marketing:plan';
    protected $description = 'Generate the weekly social content plan';

    public function handle(): int
    {
        $r = MarketingPlan::generate();
        $this->info('Marketing plan generated — '.count($r['items']).' posts for week of '.$r['week_of']);
        return self::SUCCESS;
    }
}
