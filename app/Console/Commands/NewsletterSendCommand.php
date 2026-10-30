<?php

namespace App\Console\Commands;

use App\Support\Newsletter;
use Illuminate\Console\Command;

class NewsletterSendCommand extends Command
{
    protected $signature = 'newsletter:send {--force : Send now even if not due}';
    protected $description = 'Send the newsletter campaign to subscribers when due';

    public function handle(): int
    {
        if (!$this->option('force') && !Newsletter::due()) {
            $this->info('Newsletter not due yet — skipping.');
            return self::SUCCESS;
        }
        $n = Newsletter::sendCampaign();
        $this->info('Newsletter sent to '.$n.' subscriber(s).');
        return self::SUCCESS;
    }
}
