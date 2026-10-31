<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>403 — Access denied</title>
    <link rel="icon" href="{{ function_exists('zbrand') ? zbrand('icon') : asset('favicon.ico') }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Inter',system-ui,sans-serif;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px;
             background:radial-gradient(circle at 20% 20%,#1a1750,#0b0820 55%),#0b0820;color:#fff;overflow:hidden;position:relative}
        .blob{position:absolute;border-radius:50%;filter:blur(60px);opacity:.5;pointer-events:none}
        .b1{width:420px;height:420px;background:#6366f1;top:-120px;right:-80px}
        .b2{width:380px;height:380px;background:#ec4899;bottom:-140px;left:-60px}
        .card{position:relative;z-index:2;max-width:520px;width:100%;text-align:center;
              background:rgba(255,255,255,.04);backdrop-filter:blur(12px);border:1px solid rgba(255,255,255,.1);
              border-radius:28px;padding:48px 36px;box-shadow:0 30px 80px -20px rgba(0,0,0,.6)}
        .lock{width:84px;height:84px;margin:0 auto 24px;border-radius:24px;display:flex;align-items:center;justify-content:center;
              background:linear-gradient(135deg,#6366f1,#8b5cf6);box-shadow:0 12px 30px -8px rgba(99,102,241,.7)}
        .lock svg{width:40px;height:40px;stroke:#fff}
        .code{font-size:84px;font-weight:900;line-height:1;background:linear-gradient(135deg,#a5b4fc,#f0abfc);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;letter-spacing:-2px}
        h1{font-size:22px;font-weight:700;margin-top:10px}
        p{color:rgba(255,255,255,.65);margin-top:10px;font-size:15px;line-height:1.6}
        .actions{display:flex;gap:12px;justify-content:center;flex-wrap:wrap;margin-top:28px}
        .btn{display:inline-flex;align-items:center;gap:8px;padding:12px 22px;border-radius:14px;font-weight:600;font-size:14px;
             text-decoration:none;cursor:pointer;border:0;transition:transform .15s,box-shadow .15s}
        .btn:hover{transform:translateY(-2px)}
        .btn-primary{color:#fff;background:linear-gradient(135deg,#6366f1,#8b5cf6);box-shadow:0 10px 24px -8px rgba(99,102,241,.7)}
        .btn-ghost{color:rgba(255,255,255,.85);background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.12)}
        .btn svg{width:16px;height:16px;stroke:currentColor;fill:none}
        @media(max-width:480px){.card{padding:36px 22px}.code{font-size:64px}}
    </style>
</head>
<body>
    <div class="blob b1"></div>
    <div class="blob b2"></div>
    <div class="card">
        <div class="lock">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
        </div>
        <div class="code">403</div>
        <h1>Access denied</h1>
        <p>{{ $exception?->getMessage() ?: "You don't have permission to view this page. If you think this is a mistake, contact the administrator." }}</p>
        <div class="actions">
            <a href="javascript:history.back()" class="btn btn-ghost">
                <svg viewBox="0 0 24 24"><path d="M15 19l-7-7 7-7"/></svg> Go back
            </a>
            <a href="{{ url('/') }}" class="btn btn-primary">
                <svg viewBox="0 0 24 24"><path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg> Go home
            </a>
        </div>
    </div>
</body>
</html>
