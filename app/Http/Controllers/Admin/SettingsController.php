<?php

namespace App\Http\Controllers\Admin;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;

class SettingsController extends Controller
{
    public function show(): View
    {
        return view('admin.settings');
    }

    public function updateBranding(Request $request): RedirectResponse
    {
        $request->validate([
            'site_name'  => 'required|string|max:120',
            'logo'       => 'nullable|image|max:2048',
            'favicon'    => 'nullable|image|max:512',
        ]);

        Setting::set('branding.site_name', $request->site_name, 'text', 'branding');

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('branding', 'public');
            Setting::set('branding.logo', $path, 'file', 'branding');
        }

        if ($request->hasFile('favicon')) {
            $path = $request->file('favicon')->store('branding', 'public');
            Setting::set('branding.favicon', $path, 'file', 'branding');
        }

        return back()->with('success_branding', 'Branding saved.');
    }

    public function updateEmail(Request $request): RedirectResponse
    {
        $request->validate([
            'mail.host'         => 'required|string|max:255',
            'mail.port'         => 'required|integer',
            'mail.username'     => 'required|string|max:255',
            'mail.password'     => 'nullable|string|max:255',
            'mail.encryption'   => 'required|in:tls,ssl,none',
            'mail.from_address' => 'required|email|max:255',
            'mail.from_name'    => 'required|string|max:120',
        ]);

        foreach ($request->input('mail') as $key => $value) {
            if ($key === 'password' && blank($value)) continue; // don't overwrite blank password
            $type = $key === 'password' ? 'password' : 'text';
            Setting::set("mail.{$key}", $value, $type, 'email');
        }

        return back()->with('success_email', 'Email settings saved.');
    }

    public function testEmail(Request $request): JsonResponse
    {
        $request->validate(['to' => 'required|email']);

        // Apply stored mail settings at runtime
        $this->applyMailConfig();

        try {
            Mail::raw('This is a test email from IDIEXPO ' . now()->format('Y') . '.', function ($m) use ($request) {
                $m->to($request->to)
                  ->subject('IDIEXPO Test Email');
            });
            return response()->json(['status' => 'ok', 'message' => 'Test email sent to ' . $request->to]);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function updateIntegrations(Request $request): RedirectResponse
    {
        $fields = [
            'groq.api_key'     => ['groq',     'password'],
            'wasender.api_key' => ['wasender', 'password'],
            'wasender.phone'   => ['wasender', 'text'],
        ];

        foreach ($fields as $key => [$group, $type]) {
            $val = $request->input(str_replace('.', '_', $key));
            if (!blank($val)) {
                Setting::set($key, $val, $type, $group);
            }
        }

        return back()->with('success_integrations', 'Integration settings saved.');
    }

    public function updateGeneral(Request $request): RedirectResponse
    {
        $request->validate([
            'general.contact_email' => 'nullable|email',
            'general.contact_phone' => 'nullable|string|max:30',
            'general.whatsapp_number' => 'nullable|string|max:30',
            'general.footer_credit' => 'nullable|string|max:200',
        ]);

        foreach ($request->input('general', []) as $key => $value) {
            Setting::set("general.{$key}", $value, 'text', 'general');
        }

        return back()->with('success_general', 'General settings saved.');
    }

    private function applyMailConfig(): void
    {
        $host    = Setting::get('mail.host', config('mail.mailers.smtp.host'));
        $port    = Setting::get('mail.port', config('mail.mailers.smtp.port'));
        $user    = Setting::get('mail.username', config('mail.mailers.smtp.username'));
        $pass    = Setting::get('mail.password', config('mail.mailers.smtp.password'));
        $enc     = Setting::get('mail.encryption', config('mail.mailers.smtp.encryption'));
        $from    = Setting::get('mail.from_address', config('mail.from.address'));
        $name    = Setting::get('mail.from_name', config('mail.from.name'));

        Config::set('mail.default', 'smtp');
        Config::set('mail.mailers.smtp.host', $host);
        Config::set('mail.mailers.smtp.port', $port);
        Config::set('mail.mailers.smtp.username', $user);
        Config::set('mail.mailers.smtp.password', $pass);
        Config::set('mail.mailers.smtp.encryption', $enc === 'none' ? null : $enc);
        Config::set('mail.from.address', $from);
        Config::set('mail.from.name', $name);
        // Allow self-signed/mismatched certs on shared cPanel hosting
        Config::set('mail.mailers.smtp.stream', [
            'ssl' => [
                'allow_self_signed' => true,
                'verify_peer'       => false,
                'verify_peer_name'  => false,
            ],
        ]);
    }
}
