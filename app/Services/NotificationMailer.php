<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

/**
 * Wraps Laravel's Mail facade to apply SMTP settings from the database
 * before sending, so emails use the admin-configured credentials.
 */
class NotificationMailer
{
    public function __construct()
    {
        $this->applySettings();
    }

    /** Apply stored SMTP settings at runtime before sending. */
    private function applySettings(): void
    {
        $host = Setting::get('mail.host');
        if (!$host) return; // no settings saved yet — fall back to .env

        Config::set('mail.default', 'smtp');
        Config::set('mail.mailers.smtp.host',       $host);
        Config::set('mail.mailers.smtp.port',        Setting::get('mail.port', 465));
        Config::set('mail.mailers.smtp.username',    Setting::get('mail.username'));
        Config::set('mail.mailers.smtp.password',    Setting::get('mail.password'));
        Config::set('mail.mailers.smtp.encryption',  Setting::get('mail.encryption', 'ssl'));
        Config::set('mail.from.address',             Setting::get('mail.from_address'));
        Config::set('mail.from.name',                Setting::get('mail.from_name', config('app.name')));
        Config::set('mail.mailers.smtp.stream', [
            'ssl' => [
                'allow_self_signed' => true,
                'verify_peer'       => false,
                'verify_peer_name'  => false,
            ],
        ]);
    }

    public function adminEmail(): ?string
    {
        return Setting::get('general.contact_email')
            ?? Setting::get('mail.from_address')
            ?? config('mail.from.address');
    }

    public function send(\Illuminate\Mail\Mailable $mailable, string|array $to): bool
    {
        try {
            Mail::to($to)->send($mailable);
            return true;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Email failed: ' . $e->getMessage());
            return false;
        }
    }
}
