<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class SendPushNotification implements ShouldQueue
{
    use Dispatchable, Queueable;

    public $tries = 2;

    public $timeout = 30;

    public $tokens;
    public $title;
    public $body;
    public $data;

    /**
     * Create a new job instance.
     */
    public function __construct($tokens,$title,$body,$data=[])
    {
        $this->tokens = $tokens;
        $this->title = $title;
        $this->body = $body;
        $this->data = $data;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {

            Log::info(" Job Woking".json_encode($this->tokens));
        
            app(\App\Services\PushNotificationService::class)
            ->send($this->tokens,$this->title,$this->body,$this->data);

        } catch (\Exception $e) {

        Log::error($e->getMessage());

        }
    }
}
