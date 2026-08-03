<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="margin:0;padding:24px;background:#f9fafb;font-family:Arial,sans-serif">
<div style="max-width:520px;background:#fff;border-radius:10px;padding:28px;border:1px solid #e5e7eb">
  <p style="margin:0 0 4px;color:#185909;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:2px">IDIEXPO Admin Alert</p>
  <h2 style="margin:0 0 20px;color:#111D02;font-size:20px">New Attendee Registration</h2>

  <table width="100%" cellpadding="8" cellspacing="0" style="border-collapse:collapse;font-size:14px">
    <tr style="border-bottom:1px solid #f3f4f6">
      <td style="color:#6b7280;width:140px">Name</td>
      <td style="color:#111827;font-weight:600">{{ $attendee->name }}</td>
    </tr>
    @if($attendee->organisation)
    <tr style="border-bottom:1px solid #f3f4f6">
      <td style="color:#6b7280">Organisation</td>
      <td style="color:#111827">{{ $attendee->organisation }}</td>
    </tr>
    @endif
    <tr style="border-bottom:1px solid #f3f4f6">
      <td style="color:#6b7280">Phone</td>
      <td style="color:#111827">{{ $attendee->phone }}</td>
    </tr>
    @if($attendee->email)
    <tr style="border-bottom:1px solid #f3f4f6">
      <td style="color:#6b7280">Email</td>
      <td style="color:#111827">{{ $attendee->email }}</td>
    </tr>
    @endif
    <tr style="border-bottom:1px solid #f3f4f6">
      <td style="color:#6b7280">Reg Number</td>
      <td style="color:#D29500;font-weight:700;font-family:monospace">{{ $attendee->registration_number }}</td>
    </tr>
    <tr>
      <td style="color:#6b7280">Registered</td>
      <td style="color:#111827">{{ $attendee->created_at->format('d M Y H:i') }}</td>
    </tr>
  </table>

  <div style="margin-top:20px">
    <a href="{{ $attendee->verifyUrl() }}"
       style="display:inline-block;background:#185909;color:#fff;text-decoration:none;padding:10px 20px;border-radius:6px;font-size:13px;font-weight:700">
      View Pass
    </a>
    <a href="{{ url('/admin/attendees') }}"
       style="display:inline-block;background:#f3f4f6;color:#374151;text-decoration:none;padding:10px 20px;border-radius:6px;font-size:13px;font-weight:700;margin-left:8px">
      Admin Attendees
    </a>
  </div>
</div>
</body>
</html>
