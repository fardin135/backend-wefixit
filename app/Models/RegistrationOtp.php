<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegistrationOtp extends Model
{
    protected $fillable = [
        'email',
        'otp_hash',
        'attempts',
        'verified_at',
        'expires_at',
    ];
}
