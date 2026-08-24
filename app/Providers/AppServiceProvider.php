<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Http\Request;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('login', function (Request $request) {
            return [
                Limit::perMinute(5)->by(
                    strtolower($request->input('email', 'guest'))
                    . '|' .
                    $request->ip()
                ),
            ];
        });

        RateLimiter::for('register', function (Request $request) {
            return [
                Limit::perMinute(3)->by(
                    $request->ip()
                ),
            ];
        });

        RateLimiter::for('password-reset', function (Request $request) {
            return [
                Limit::perMinute(3)->by(
                    strtolower($request->input('email', 'guest'))
                    . '|' .
                    $request->ip()
                ),
            ];
        });

        RateLimiter::for('otp', function (Request $request) {
            return [
                Limit::perMinute(3)->by(
                    strtolower($request->input('email', 'guest'))
                    . '|' .
                    $request->ip()
                ),
            ];
        });

        RateLimiter::for('api', function (Request $request) {
            return [
                Limit::perMinute(60)->by(
                    $request->user()?->id ?? $request->ip()
                ),
            ];
        });
    }
}
