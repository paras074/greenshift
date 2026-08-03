<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 1. Superadmin bypasses ALL permission checks
        Gate::before(function ($user, $ability) {
            return $user->hasRole('superadmin') ? true : null;
        });

        require_once app_path('Helpers/helpers.php');

        $this->loadDynamicMailSettings();
    }

    /**
     * Fetch settings from DB and override Mail config.
     */
    protected function loadDynamicMailSettings(): void
    {
        if (Schema::hasTable('core_settings')) {
            $settings = DB::table('core_settings')->pluck('setting_value', 'setting_key');
        
            if (isset($settings['sendgrid_api_key']) && !empty($settings['sendgrid_api_key'])) {
                try {
                    $decryptedKey = Crypt::decryptString($settings['sendgrid_api_key']); 
                    Config::set('mail.mailers.sendgrid.api_key', $decryptedKey);

                    $fromAddress = trim($settings['mail_from_address'] ?? '', ' "');
                    $fromName = trim($settings['mail_from_name'] ?? '', ' "');

                    Config::set('mail.mailers.smtp.host', trim($settings['mail_host'] ?? 'smtp.sendgrid.net', ' "'));
                    Config::set('mail.mailers.smtp.port', trim($settings['mail_port'] ?? 587, ' "'));
                    Config::set('mail.mailers.smtp.encryption', trim($settings['mail_encryption'] ?? 'tls', ' "'));
                
                    Config::set('mail.from.address', $fromAddress ?: 'notifications@perfectwebservices.com');
                    Config::set('mail.from.name', $fromName ?: 'Greenshift CRM');
                    
                    Config::set('mail.default', 'sendgrid');
                } catch (DecryptException $e) {
                    \Log::error('SendGrid Decryption Failed: ' . $e->getMessage());
                }
            }
        }
    }
}