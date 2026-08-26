<?php

namespace App\Jobs;

use App\Mail\RegistrationOtpMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendRegistrationOtp implements ShouldQueue
{
    use Queueable;

    public $email;
    public $otp;

    public function __construct($email, $otp)
    {
        $this->email = $email;
        $this->otp = $otp;
    }

    public function handle(): void
    {
        try {
            Mail::to($this->email)->send(new RegistrationOtpMail($this->otp));
        } catch (Throwable $e) {
            Log::error('Failed to send registration OTP email', [
                'email' => $this->email,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
