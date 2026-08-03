<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Registration Confirmed</title>
</head>
<body style="margin:0;padding:0;background:#0d1f06;font-family:Arial,sans-serif">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#0d1f06;padding:32px 16px">
<tr><td align="center">
<table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#111D02;border-radius:16px;overflow:hidden;border:1px solid #185909">

  {{-- Header --}}
  <tr><td style="background:#185909;padding:24px 32px">
    <p style="margin:0;color:#D29500;font-size:11px;letter-spacing:3px;text-transform:uppercase">Insiza District Industrial Expo</p>
    <h1 style="margin:6px 0 0;color:#ffffff;font-size:22px;font-weight:800">IDIEXPO {{ $attendee->expo->year ?? '2026' }} · Registration Confirmed</h1>
  </td></tr>

  {{-- Gold bar --}}
  <tr><td style="background:linear-gradient(90deg,#D29500,#f0c040,#D29500);height:4px;padding:0"></td></tr>

  {{-- Body --}}
  <tr><td style="padding:32px">
    <p style="margin:0 0 8px;color:#9ca3af;font-size:13px">Hello,</p>
    <h2 style="margin:0 0 24px;color:#ffffff;font-size:26px;font-weight:800">{{ $attendee->name }}</h2>

    <p style="color:#d1d5db;line-height:1.6;margin:0 0 24px">
      You have successfully registered to attend the
      <strong style="color:#D29500">Insiza District Industrial Expo {{ $attendee->expo->year ?? '2026' }}</strong>
      at <strong style="color:#ffffff">{{ $attendee->expo->venue ?? 'Filabusi Show Grounds' }}</strong>,
      {{ \Carbon\Carbon::parse($attendee->expo->start_date ?? '2026-09-16')->format('d M') }}–{{ \Carbon\Carbon::parse($attendee->expo->end_date ?? '2026-09-18')->format('d M Y') }}.
    </p>

    {{-- Registration card --}}
    <table width="100%" cellpadding="0" cellspacing="0" style="background:rgba(24,89,9,0.15);border:1px solid rgba(24,89,9,0.4);border-radius:12px;padding:0;margin-bottom:24px">
      <tr><td style="padding:20px 24px">
        <p style="margin:0 0 4px;color:#9ca3af;font-size:10px;letter-spacing:2px;text-transform:uppercase">Registration Number</p>
        <p style="margin:0 0 16px;color:#D29500;font-size:24px;font-weight:800;font-family:monospace;letter-spacing:2px">{{ $attendee->registration_number }}</p>
        @if($attendee->organisation)
        <p style="margin:0 0 4px;color:#9ca3af;font-size:11px">Organisation</p>
        <p style="margin:0 0 12px;color:#D29500;font-size:14px;font-weight:600">{{ $attendee->organisation }}</p>
        @endif
        <p style="margin:0;color:#9ca3af;font-size:11px">
          📞 {{ $attendee->phone }}
          @if($attendee->email) &nbsp;·&nbsp; ✉️ {{ $attendee->email }} @endif
        </p>
      </td></tr>
    </table>

    {{-- CTA buttons --}}
    <table cellpadding="0" cellspacing="0">
      <tr>
        <td style="padding-right:12px">
          <a href="{{ $attendee->verifyUrl() }}"
             style="display:inline-block;background:#185909;color:#ffffff;text-decoration:none;padding:12px 24px;border-radius:8px;font-size:14px;font-weight:700">
            View My Pass
          </a>
        </td>
        <td>
          <a href="{{ $attendee->verifyUrl() }}"
             style="display:inline-block;background:rgba(210,149,0,0.15);color:#D29500;text-decoration:none;padding:12px 24px;border-radius:8px;font-size:14px;font-weight:700;border:1px solid #D29500">
            Download QR Card
          </a>
        </td>
      </tr>
    </table>

    <p style="color:#6b7280;font-size:13px;margin-top:24px;line-height:1.6">
      Present your QR code at the expo entrance for fast check-in.<br>
      You can also WhatsApp your registration number <strong style="color:#9ca3af">{{ $attendee->registration_number }}</strong>
      to our bot for instant verification.
    </p>
  </td></tr>

  {{-- Footer --}}
  <tr><td style="background:rgba(24,89,9,0.2);padding:16px 32px;border-top:1px solid rgba(24,89,9,0.4)">
    <p style="margin:0;color:#6b7280;font-size:11px;text-align:center">
      insizaexpo.online &nbsp;·&nbsp; Connect · Collaborate · Grow &nbsp;·&nbsp;
      <a href="{{ url('/attend') }}" style="color:#9ca3af">Registration</a>
    </p>
  </td></tr>

</table>
</td></tr>
</table>
</body>
</html>
