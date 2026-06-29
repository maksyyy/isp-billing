<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

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
        if (str_starts_with(config('app.url'), 'https://')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        // Overwrite mail configuration dynamically if Master has set custom SMTP credentials
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('users')) {
                $master = \App\Models\User::where('role', 'master')->first();
                if ($master && !empty($master->smtp_host)) {
                    config([
                        'mail.mailers.smtp.host' => $master->smtp_host,
                        'mail.mailers.smtp.port' => $master->smtp_port,
                        'mail.mailers.smtp.username' => $master->smtp_username,
                        'mail.mailers.smtp.password' => $master->smtp_password,
                        'mail.mailers.smtp.encryption' => $master->smtp_encryption,
                        'mail.from.address' => $master->smtp_from_address,
                        'mail.from.name' => $master->smtp_from_name ?: config('app.name'),
                    ]);
                }
            }
        } catch (\Exception $e) {
            // Silently fail if database is not fully set up or accessible yet
        }
    }
}
