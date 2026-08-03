<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="margin:0;padding:24px;background:#f9fafb;font-family:Arial,sans-serif">
<div style="max-width:520px;background:#fff;border-radius:10px;padding:28px;border:1px solid #e5e7eb">
  <p style="margin:0 0 4px;color:#185909;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:2px">IDIEXPO {{ $booking->expo->year ?? '2026' }}</p>
  <h2 style="margin:0 0 6px;color:#111D02;font-size:20px">Stand Booking Request Received</h2>
  <p style="margin:0 0 20px;color:#6b7280;font-size:13px">Your booking request is under review. You will be notified once confirmed.</p>

  <table width="100%" cellpadding="8" cellspacing="0" style="border-collapse:collapse;font-size:14px">
    <tr style="border-bottom:1px solid #f3f4f6">
      <td style="color:#6b7280;width:140px">Stand</td>
      <td style="color:#111827;font-weight:600">{{ $booking->stand->stand_number ?? 'N/A' }}</td>
    </tr>
    <tr style="border-bottom:1px solid #f3f4f6">
      <td style="color:#6b7280">Company</td>
      <td style="color:#111827">{{ $booking->company_name }}</td>
    </tr>
    <tr style="border-bottom:1px solid #f3f4f6">
      <td style="color:#6b7280">Contact</td>
      <td style="color:#111827">{{ $booking->contact_person }}</td>
    </tr>
    <tr style="border-bottom:1px solid #f3f4f6">
      <td style="color:#6b7280">Phone</td>
      <td style="color:#111827">{{ $booking->contact_phone }}</td>
    </tr>
    <tr style="border-bottom:1px solid #f3f4f6">
      <td style="color:#6b7280">Email</td>
      <td style="color:#111827">{{ $booking->contact_email }}</td>
    </tr>
    <tr>
      <td style="color:#6b7280">Status</td>
      <td style="color:#D29500;font-weight:700;text-transform:capitalize">{{ $booking->status->value ?? $booking->status }}</td>
    </tr>
  </table>

  <p style="margin:20px 0 0;color:#9ca3af;font-size:12px;line-height:1.5">
    IDIEXPO {{ $booking->expo->year ?? '2026' }} &nbsp;·&nbsp;
    {{ $booking->expo->venue ?? 'Filabusi Show Grounds' }} &nbsp;·&nbsp;
    {{ \Carbon\Carbon::parse($booking->expo->start_date ?? '2026-09-16')->format('d M') }}–{{ \Carbon\Carbon::parse($booking->expo->end_date ?? '2026-09-18')->format('d M Y') }}
  </p>
</div>
</body>
</html>
