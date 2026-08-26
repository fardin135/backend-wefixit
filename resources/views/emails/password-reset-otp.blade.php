@extends('emails.layout')

@section('title', 'Password Reset OTP')

@section('content')
    <h1>Password Reset Request</h1>
    <p>We received a request to reset your Wefixit account password. Enter the following code to proceed:</p>
    
    <div class="otp-box">
        <p class="otp-code">{{ $otp }}</p>
    </div>
    
    <p>This code will expire in 5 minutes. If you did not request a password reset, you can safely ignore this email.</p>
@endsection