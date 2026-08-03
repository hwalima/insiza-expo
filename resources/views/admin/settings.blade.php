@extends('layouts.admin')
@section('title', 'Settings')

@php
use App\Models\Setting;
$branding    = Setting::getGroup('branding');
$email       = Setting::getGroup('email');
$general     = Setting::getGroup('general');
@endphp

@section('content')
<div class="p-6 space-y-6" x-data="{ tab: 'branding' }">

    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-white">Settings</h1>
        <span class="text-xs text-gray-500">Super Admin only</span>
    </div>

    {{-- Tabs --}}
    <div class="flex gap-1 border-b border-white/10 pb-0">
        @foreach(['branding' => 'Branding', 'email' => 'Email / SMTP', 'integrations' => 'API Keys', 'general' => 'General'] as $t => $label)
        <button @click="tab = '{{ $t }}'"
                :class="tab === '{{ $t }}' ? 'border-b-2 text-white' : 'text-gray-500 hover:text-gray-300'"
                class="px-4 py-2 text-sm font-medium transition-colors -mb-px"
                style="border-color: #D29500">
            {{ $label }}
        </button>
        @endforeach
    </div>

    {{-- ─── Branding ─── --}}
    <div x-show="tab === 'branding'" class="space-y-6">
        @if(session('success_branding'))
        <div class="px-4 py-2 rounded-lg text-sm text-green-300" style="background:rgba(24,89,9,0.3);border:1px solid #185909">{{ session('success_branding') }}</div>
        @endif

        <form action="{{ route('admin.settings.branding') }}" method="POST" enctype="multipart/form-data"
              class="rounded-xl p-6 space-y-5" style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08)">
            @csrf @method('PATCH')

            <h3 class="text-white font-semibold">Site Branding</h3>

            <div>
                <label class="block text-xs text-gray-400 mb-1">Site Name</label>
                <input type="text" name="site_name"
                       value="{{ $branding['branding.site_name'] ?? config('app.name') }}"
                       class="w-full rounded-lg px-4 py-2 text-white text-sm focus:outline-none"
                       style="background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.15)">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs text-gray-400 mb-2">Logo</label>
                    @if(!empty($branding['branding.logo']))
                    <img src="{{ Storage::url($branding['branding.logo']) }}" alt="Logo"
                         class="h-12 mb-3 rounded" style="background:rgba(255,255,255,0.1);padding:4px">
                    @else
                    <div class="mb-3 text-xs text-gray-500 italic">No custom logo — using text logo</div>
                    @endif
                    <input type="file" name="logo" accept="image/*"
                           class="text-sm text-gray-400 file:mr-3 file:px-3 file:py-1 file:rounded file:border-0 file:text-xs file:font-semibold file:text-white"
                           style="file:background:#185909">
                    <p class="text-xs text-gray-500 mt-1">PNG/SVG recommended. Max 2MB.</p>
                </div>

                <div>
                    <label class="block text-xs text-gray-400 mb-2">Favicon</label>
                    @if(!empty($branding['branding.favicon']))
                    <img src="{{ Storage::url($branding['branding.favicon']) }}" alt="Favicon"
                         class="h-8 mb-3 rounded">
                    @else
                    <div class="mb-3 text-xs text-gray-500 italic">Using default SVG favicon</div>
                    @endif
                    <input type="file" name="favicon" accept="image/*"
                           class="text-sm text-gray-400 file:mr-3 file:px-3 file:py-1 file:rounded file:border-0 file:text-xs file:font-semibold file:text-white"
                           style="file:background:#185909">
                    <p class="text-xs text-gray-500 mt-1">ICO/PNG/SVG. Max 512KB.</p>
                </div>
            </div>

            <button type="submit" class="px-6 py-2 rounded-lg text-sm font-semibold text-white"
                    style="background:linear-gradient(135deg,#185909,#2d7a10)">Save Branding</button>
        </form>
    </div>

    {{-- ─── Email / SMTP ─── --}}
    <div x-show="tab === 'email'" class="space-y-6" x-data="emailSettings()">
        @if(session('success_email'))
        <div class="px-4 py-2 rounded-lg text-sm text-green-300" style="background:rgba(24,89,9,0.3);border:1px solid #185909">{{ session('success_email') }}</div>
        @endif

        <form action="{{ route('admin.settings.email') }}" method="POST"
              class="rounded-xl p-6 space-y-4" style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08)">
            @csrf @method('PATCH')
            <h3 class="text-white font-semibold">SMTP Configuration</h3>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs text-gray-400 mb-1">SMTP Host</label>
                    <input type="text" name="mail[host]" value="{{ $email['mail.host'] ?? '' }}"
                           placeholder="smtp.gmail.com"
                           class="w-full rounded-lg px-3 py-2 text-white text-sm focus:outline-none"
                           style="background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.15)">
                </div>
                <div>
                    <label class="block text-xs text-gray-400 mb-1">Port</label>
                    <input type="number" name="mail[port]" value="{{ $email['mail.port'] ?? 587 }}"
                           class="w-full rounded-lg px-3 py-2 text-white text-sm focus:outline-none"
                           style="background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.15)">
                </div>
                <div>
                    <label class="block text-xs text-gray-400 mb-1">Username</label>
                    <input type="text" name="mail[username]" value="{{ $email['mail.username'] ?? '' }}"
                           class="w-full rounded-lg px-3 py-2 text-white text-sm focus:outline-none"
                           style="background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.15)">
                </div>
                <div>
                    <label class="block text-xs text-gray-400 mb-1">Password</label>
                    <input type="password" name="mail[password]" placeholder="Leave blank to keep current"
                           class="w-full rounded-lg px-3 py-2 text-white text-sm focus:outline-none"
                           style="background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.15)">
                </div>
                <div>
                    <label class="block text-xs text-gray-400 mb-1">Encryption</label>
                    <select name="mail[encryption]"
                            class="w-full rounded-lg px-3 py-2 text-white text-sm focus:outline-none"
                            style="background:#1a2e0f;border:1px solid rgba(255,255,255,0.15)">
                        @foreach(['tls' => 'TLS (587)', 'ssl' => 'SSL (465)', 'none' => 'None'] as $v => $l)
                        <option value="{{ $v }}" {{ ($email['mail.encryption'] ?? 'tls') === $v ? 'selected' : '' }}>{{ $l }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-400 mb-1">From Address</label>
                    <input type="email" name="mail[from_address]" value="{{ $email['mail.from_address'] ?? '' }}"
                           placeholder="noreply@insizaexpo.online"
                           class="w-full rounded-lg px-3 py-2 text-white text-sm focus:outline-none"
                           style="background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.15)">
                </div>
                <div class="col-span-2">
                    <label class="block text-xs text-gray-400 mb-1">From Name</label>
                    <input type="text" name="mail[from_name]" value="{{ $email['mail.from_name'] ?? 'IDIEXPO 2026' }}"
                           class="w-full rounded-lg px-3 py-2 text-white text-sm focus:outline-none"
                           style="background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.15)">
                </div>
            </div>

            <button type="submit" class="px-6 py-2 rounded-lg text-sm font-semibold text-white"
                    style="background:linear-gradient(135deg,#185909,#2d7a10)">Save Email Settings</button>
        </form>

        {{-- Test Email --}}
        <div class="rounded-xl p-6 space-y-4" style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08)">
            <h3 class="text-white font-semibold">Test Email</h3>
            <p class="text-gray-400 text-sm">Send a test email using the saved settings above.</p>
            <div class="flex gap-3">
                <input type="email" x-model="testTo" placeholder="recipient@example.com"
                       class="flex-1 rounded-lg px-3 py-2 text-white text-sm focus:outline-none"
                       style="background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.15)">
                <button @click="sendTest()"
                        :disabled="sending"
                        class="px-5 py-2 rounded-lg text-sm font-semibold text-white min-w-[120px]"
                        style="background:rgba(210,149,0,0.3);border:1px solid #D29500;color:#D29500"
                        x-text="sending ? 'Sending…' : 'Send Test'"></button>
            </div>
            <div x-show="testResult" x-text="testResult"
                 :class="testOk ? 'text-green-400' : 'text-red-400'"
                 class="text-sm mt-2"></div>
        </div>
    </div>

    {{-- ─── API Keys ─── --}}
    <div x-show="tab === 'integrations'" class="space-y-6">
        @if(session('success_integrations'))
        <div class="px-4 py-2 rounded-lg text-sm text-green-300" style="background:rgba(24,89,9,0.3);border:1px solid #185909">{{ session('success_integrations') }}</div>
        @endif

        <form action="{{ route('admin.settings.integrations') }}" method="POST"
              class="rounded-xl p-6 space-y-5" style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08)">
            @csrf @method('PATCH')
            <h3 class="text-white font-semibold">API Keys &amp; Integrations</h3>
            <p class="text-xs text-gray-500">Values are encrypted. Leave blank to keep existing.</p>

            <div class="space-y-4">
                <div>
                    <label class="block text-xs text-gray-400 mb-1">Groq AI API Key</label>
                    <input type="password" name="groq_api_key" placeholder="gsk_…"
                           class="w-full rounded-lg px-3 py-2 text-white text-sm focus:outline-none font-mono"
                           style="background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.15)">
                    <p class="text-xs text-gray-500 mt-1">Powers the AI chat assistant. Get from <a href="https://console.groq.com" target="_blank" class="text-blue-400 underline">console.groq.com</a></p>
                </div>
                <div>
                    <label class="block text-xs text-gray-400 mb-1">Wasender API Key</label>
                    <input type="password" name="wasender_api_key" placeholder="Leave blank to keep current"
                           class="w-full rounded-lg px-3 py-2 text-white text-sm focus:outline-none font-mono"
                           style="background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.15)">
                    <p class="text-xs text-gray-500 mt-1">WhatsApp bot API key from wasenderapi.com</p>
                </div>
                <div>
                    <label class="block text-xs text-gray-400 mb-1">WhatsApp Test Number</label>
                    <input type="text" name="wasender_phone"
                           value="{{ Setting::get('wasender.phone', config('services.wasender.api_key') ? '+...' : '') }}"
                           placeholder="+27785425978"
                           class="w-full rounded-lg px-3 py-2 text-white text-sm focus:outline-none"
                           style="background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.15)">
                </div>
            </div>

            <button type="submit" class="px-6 py-2 rounded-lg text-sm font-semibold text-white"
                    style="background:linear-gradient(135deg,#185909,#2d7a10)">Save API Keys</button>
        </form>
    </div>

    {{-- ─── General ─── --}}
    <div x-show="tab === 'general'" class="space-y-6">
        @if(session('success_general'))
        <div class="px-4 py-2 rounded-lg text-sm text-green-300" style="background:rgba(24,89,9,0.3);border:1px solid #185909">{{ session('success_general') }}</div>
        @endif

        <form action="{{ route('admin.settings.general') }}" method="POST"
              class="rounded-xl p-6 space-y-4" style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08)">
            @csrf @method('PATCH')
            <h3 class="text-white font-semibold">General Settings</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs text-gray-400 mb-1">Contact Email</label>
                    <input type="email" name="general[contact_email]"
                           value="{{ $general['general.contact_email'] ?? '' }}"
                           placeholder="info@insizaexpo.online"
                           class="w-full rounded-lg px-3 py-2 text-white text-sm focus:outline-none"
                           style="background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.15)">
                </div>
                <div>
                    <label class="block text-xs text-gray-400 mb-1">Contact Phone</label>
                    <input type="text" name="general[contact_phone]"
                           value="{{ $general['general.contact_phone'] ?? '' }}"
                           placeholder="+263 774 381 008"
                           class="w-full rounded-lg px-3 py-2 text-white text-sm focus:outline-none"
                           style="background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.15)">
                </div>
                <div>
                    <label class="block text-xs text-gray-400 mb-1">WhatsApp Number (public)</label>
                    <input type="text" name="general[whatsapp_number]"
                           value="{{ $general['general.whatsapp_number'] ?? '' }}"
                           placeholder="+263 774 381 008"
                           class="w-full rounded-lg px-3 py-2 text-white text-sm focus:outline-none"
                           style="background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.15)">
                </div>
                <div>
                    <label class="block text-xs text-gray-400 mb-1">Footer Credit</label>
                    <input type="text" name="general[footer_credit]"
                           value="{{ $general['general.footer_credit'] ?? 'Created by Hwalima Digital' }}"
                           class="w-full rounded-lg px-3 py-2 text-white text-sm focus:outline-none"
                           style="background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.15)">
                </div>
            </div>

            <button type="submit" class="px-6 py-2 rounded-lg text-sm font-semibold text-white"
                    style="background:linear-gradient(135deg,#185909,#2d7a10)">Save General Settings</button>
        </form>
    </div>

</div>

<script>
function emailSettings() {
    return {
        testTo: '',
        sending: false,
        testResult: '',
        testOk: false,
        async sendTest() {
            if (!this.testTo) return;
            this.sending = true;
            this.testResult = '';
            try {
                const r = await fetch('{{ route("admin.settings.test-email") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ to: this.testTo })
                });
                const data = await r.json();
                this.testOk = data.status === 'ok';
                this.testResult = data.message;
            } catch(e) {
                this.testOk = false;
                this.testResult = 'Request failed: ' + e.message;
            }
            this.sending = false;
        }
    }
}
</script>
@endsection
