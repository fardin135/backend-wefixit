<?php

namespace App\Jobs;

use App\Mail\resetPassword\PasswordResetOtpMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendPasswordResetOtp implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct( public string $email, public string $otp ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Mail::to($this->email)
            ->send(new PasswordResetOtpMail($this->otp));
    }
}
