<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Your IDIEXPO Admin Account</title>
<style>
  body { margin:0; padding:0; background:#0f1a0a; font-family:'Figtree',Arial,sans-serif; color:#e5e7eb; }
  .wrap { max-width:580px; margin:40px auto; background:#1a2e12; border-radius:16px; overflow:hidden; border:1px solid rgba(210,149,0,0.2); }
  .header { background:linear-gradient(135deg,#185909,#1e6e0a); padding:32px 36px; }
  .header h1 { margin:0; font-size:22px; font-weight:800; color:#D29500; letter-spacing:-0.3px; }
  .header p { margin:6px 0 0; font-size:13px; color:rgba(255,255,255,0.6); }
  .body { padding:32px 36px; }
  .body p { margin:0 0 16px; font-size:14px; line-height:1.7; color:rgba(255,255,255,0.8); }
  .creds { background:#111D02; border:1px solid rgba(210,149,0,0.25); border-radius:10px; padding:20px 24px; margin:24px 0; }
  .creds .row { display:flex; justify-content:space-between; align-items:center; padding:6px 0; border-bottom:1px solid rgba(255,255,255,0.06); }
  .creds .row:last-child { border-bottom:none; }
  .creds .label { font-size:11px; text-transform:uppercase; letter-spacing:.08em; color:rgba(255,255,255,0.4); }
  .creds .value { font-size:14px; font-weight:700; color:#D29500; font-family:monospace; }
  .notice { background:rgba(210,149,0,0.08); border-left:3px solid #D29500; border-radius:0 8px 8px 0; padding:12px 16px; margin:20px 0; font-size:13px; color:rgba(255,255,255,0.7); }
  .btn { display:inline-block; background:#185909; color:#fff; text-decoration:none; font-weight:700; font-size:14px; padding:12px 28px; border-radius:10px; margin-top:8px; }
  .footer { border-top:1px solid rgba(255,255,255,0.07); padding:20px 36px; font-size:11px; color:rgba(255,255,255,0.3); text-align:center; }
  .footer a { color:#D29500; text-decoration:none; }
</style>
</head>
<body>
<div class="wrap">
  <div class="header">
    <h1>INSIZA EXPO — Admin Access</h1>
    <p>Insiza District Industrial Expo Management Platform</p>
  </div>
  <div class="body">
    <p>Hi <strong style="color:#fff">{{ $user->name }}</strong>,</p>
    <p>An admin account has been created for you on the IDIEXPO platform. Use the credentials below to sign in.</p>

    <div class="creds">
      <div class="row">
        <span class="label">Login URL</span>
        <span class="value" style="font-size:12px">{{ url('/login') }}</span>
      </div>
      <div class="row">
        <span class="label">Email</span>
        <span class="value">{{ $user->email }}</span>
      </div>
      <div class="row">
        <span class="label">Temporary Password</span>
        <span class="value">{{ $plainPassword }}</span>
      </div>
    </div>

    <div class="notice">
      ⚠️ You will be required to change your password on first login. Please do not share these credentials.
    </div>

    <a href="{{ url('/login') }}" class="btn">Sign In to Admin Panel →</a>
  </div>
  <div class="footer">
    IDIEXPO &bull; Filabusi Show Grounds &bull;
    <a href="https://www.hwalima.digital">Hwalima Digital</a>
  </div>
</div>
</body>
</html>
