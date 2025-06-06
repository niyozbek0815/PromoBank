<?php

namespace App\Console\Commands;

use App\Jobs\SmsSenderJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Queue;

class TestSmsJobCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-sms-job-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        SmsSenderJob::dispatch([
            'phone' => '+998901234567',
            'message' => 'This is a test from Artisan command'
        ])->onConnection('rabbitmq');




        $this->info('SmsSenderJob dispatched.');
    }
}