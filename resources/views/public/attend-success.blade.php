@extends('layouts.public')
@section('title', 'Registration Confirmed – ' . $attendee->registration_number)

@push('head')
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
@endpush

@section('content')
<div class="min-h-screen py-12 px-4 flex flex-col items-center">

    {{-- Success banner --}}
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full mb-4"
             style="background:rgba(24,89,9,0.3);border:2px solid #185909">
            <svg class="w-8 h-8 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        <h1 class="text-3xl font-bold text-white">You're Registered!</h1>
        <p class="text-gray-400 mt-1">Download your pass and present it at the expo entrance.</p>
    </div>

    {{-- ─── Registration Card (captured by html2canvas) ─── --}}
    <div id="registration-card"
         style="width:800px;height:440px;background:#111D02;border-radius:16px;overflow:hidden;
                border:2px solid #185909;position:relative;font-family:Arial,sans-serif;display:flex;flex-direction:column">

        {{-- Header bar --}}
        <div style="background:#185909;padding:14px 28px;display:flex;align-items:center;justify-content:space-between">
            <div>
                <div style="color:#D29500;font-size:11px;font-weight:700;letter-spacing:3px;text-transform:uppercase">
                    Insiza District Industrial Expo
                </div>
                <div style="color:white;font-size:18px;font-weight:800;margin-top:2px">
                    IDIEXPO {{ $attendee->expo->year ?? '2026' }} &nbsp;·&nbsp; ATTENDEE PASS
                </div>
            </div>
            <div style="color:#D29500;font-size:11px;text-align:right">
                <div>{{ \Carbon\Carbon::parse($attendee->expo->start_date ?? '2026-09-16')->format('d M') }} –
                     {{ \Carbon\Carbon::parse($attendee->expo->end_date ?? '2026-09-18')->format('d M Y') }}</div>
                <div>{{ $attendee->expo->venue ?? 'Filabusi Show Grounds' }}</div>
            </div>
        </div>

        {{-- Gold accent line --}}
        <div style="height:4px;background:linear-gradient(90deg,#D29500,#f0c040,#D29500)"></div>

        {{-- Body --}}
        <div style="display:flex;flex:1;padding:24px 28px;gap:28px;align-items:center">

            {{-- Left: attendee details --}}
            <div style="flex:1">
                <div style="color:#9ca3af;font-size:10px;letter-spacing:2px;text-transform:uppercase;margin-bottom:4px">
                    Registered Attendee
                </div>
                <div style="color:white;font-size:28px;font-weight:800;line-height:1.2;margin-bottom:8px">
                    {{ $attendee->name }}
                </div>
                @if($attendee->organisation)
                <div style="color:#D29500;font-size:14px;font-weight:600;margin-bottom:20px">
                    {{ $attendee->organisation }}
                </div>
                @else
                <div style="margin-bottom:20px"></div>
                @endif

                <div style="display:flex;flex-direction:column;gap:6px">
                    @if($attendee->phone)
                    <div style="color:#9ca3af;font-size:12px">
                        <span style="color:#185909">&#9679;</span>&nbsp; {{ $attendee->phone }}
                    </div>
                    @endif
                    @if($attendee->email)
                    <div style="color:#9ca3af;font-size:12px">
                        <span style="color:#185909">&#9679;</span>&nbsp; {{ $attendee->email }}
                    </div>
                    @endif
                </div>

                <div style="margin-top:24px;border-top:1px solid rgba(210,149,0,0.3);padding-top:14px">
                    <div style="color:#9ca3af;font-size:10px;letter-spacing:2px;text-transform:uppercase">
                        Registration Number
                    </div>
                    <div style="color:#D29500;font-size:22px;font-weight:800;letter-spacing:2px;margin-top:4px">
                        {{ $attendee->registration_number }}
                    </div>
                </div>
            </div>

            {{-- Divider --}}
            <div style="width:2px;height:80%;background:rgba(210,149,0,0.3);border-radius:2px"></div>

            {{-- Right: QR code --}}
            <div style="display:flex;flex-direction:column;align-items:center;gap:10px;min-width:200px">
                <div style="background:white;padding:10px;border-radius:10px;border:3px solid #D29500">
                    <div id="qr-code-canvas" style="width:180px;height:180px;display:block"></div>
                </div>
                <div style="color:#9ca3af;font-size:10px;text-align:center;line-height:1.4">
                    Scan at entrance<br>or WhatsApp bot
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <div style="background:rgba(24,89,9,0.2);padding:10px 28px;border-top:1px solid rgba(24,89,9,0.4);
                    display:flex;align-items:center;justify-content:space-between">
            <div style="color:#6b7280;font-size:10px">insizaexpo.online</div>
            <div style="color:#6b7280;font-size:10px">
                Registered {{ $attendee->created_at->format('d M Y') }}
            </div>
            <div style="color:#6b7280;font-size:10px">Connect · Collaborate · Grow</div>
        </div>
    </div>

    {{-- ─── Action buttons ─── --}}
    <div class="flex gap-4 mt-6 flex-wrap justify-center">
        <button onclick="downloadCard()"
                class="flex items-center gap-2 px-6 py-3 rounded-lg font-semibold text-white"
                style="background:linear-gradient(135deg,#185909,#2d7a10)">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
            </svg>
            Download PNG Card
        </button>

        <a href="{{ $attendee->verifyUrl() }}" target="_blank"
           class="flex items-center gap-2 px-6 py-3 rounded-lg font-semibold"
           style="background:rgba(210,149,0,0.15);border:1px solid #D29500;color:#D29500">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
            </svg>
            View Verification Page
        </a>

        <a href="{{ route('attend') }}"
           class="flex items-center gap-2 px-6 py-3 rounded-lg font-semibold text-gray-400"
           style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1)">
            Register Another
        </a>
    </div>

    <p class="text-gray-500 text-xs mt-4 text-center max-w-md">
        Save this card to your device. You can also WhatsApp your registration number
        <strong class="text-gray-400">{{ $attendee->registration_number }}</strong>
        to our bot for verification.
    </p>
</div>

<script>
// Render QR code natively (no CORS issues for html2canvas)
document.addEventListener('DOMContentLoaded', function () {
    new QRCode(document.getElementById('qr-code-canvas'), {
        text: '{{ $attendee->verifyUrl() }}',
        width: 180,
        height: 180,
        colorDark: '#000000',
        colorLight: '#ffffff',
        correctLevel: QRCode.CorrectLevel.H
    });
});

function downloadCard() {
    const btn = event.target.closest('button');
    btn.textContent = 'Generating…';
    btn.disabled = true;

    html2canvas(document.getElementById('registration-card'), {
        scale: 2,
        useCORS: true,
        allowTaint: false,
        backgroundColor: '#111D02',
        logging: false
    }).then(canvas => {
        const link = document.createElement('a');
        link.download = '{{ $attendee->registration_number }}.png';
        link.href = canvas.toDataURL('image/png');
        link.click();
        btn.innerHTML = '✓ Downloaded';
        btn.style.background = '#185909';
        setTimeout(() => {
            btn.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg> Download PNG Card';
            btn.disabled = false;
            btn.style.background = '';
        }, 3000);
    });
}
</script>
@endsection
