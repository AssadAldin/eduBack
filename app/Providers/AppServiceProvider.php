<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Config;

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
        // Customize verification email link
        VerifyEmail::createUrlUsing(function ($notifiable) {
            $frontendUrl = config('app.frontend_url') ?? env('FRONTEND_URL', 'http://localhost:8080');

            $backendUrl = URL::temporarySignedRoute(
                'verification.verify',
                now()->addMinutes(Config::get('auth.verification.expire', 60)),
                [
                    'id' => $notifiable->getKey(),
                    'hash' => sha1($notifiable->getEmailForVerification()),
                ]
            );

            // Parse both path and query string
            $parsed = parse_url($backendUrl);

            $path = $parsed['path'] ?? '';
            $query = $parsed['query'] ?? '';

            return rtrim($frontendUrl, '/') . '/verify-email/' . $notifiable->getKey() . '/' . sha1($notifiable->getEmailForVerification()) . '?' . $query;

        });
    }
}
