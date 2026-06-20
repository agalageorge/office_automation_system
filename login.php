<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../../core/RoleRouter.php';

Auth::startSession();


$error   = '';
$success = Auth::getFlash('success') ?? '';

// ── Handle POST ───────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!Auth::verifyCsrf($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please refresh and try again.';

    } else {
        $email    = trim($_POST['email']    ?? '');
        $password = trim($_POST['password'] ?? '');

        if (!$email || !$password) {
            $error = 'Please enter your email and password.';

        } else {
            $result = Auth::login($email, $password);

            if ($result['success']) {
                // ── Route each role to its own dashboard ──────────────────────
                RoleRouter::redirectToDashboard();

            } else {
                $error = $result['message'];
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sign In — <?= htmlspecialchars(APP_NAME) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  :root {
    --navy:       #0f2544;
    --navy2:      #1a3560;
    --gold:       #c9922a;
    --gold-lt:    #f0c060;
    --white:      #ffffff;
    --gray-50:    #f8f8f6;
    --gray-100:   #ededea;
    --gray-300:   #c8c7c0;
    --gray-500:   #888780;
    --gray-700:   #444441;
    --text:       #1a1a18;
    --danger:     #a32d2d;
    --danger-bg:  #fcebeb;
    --radius:     12px;
    --radius-sm:  8px;
  }
  body {
    font-family: 'DM Sans', sans-serif;
    background: var(--gray-50);
    min-height: 100vh;
    display: flex;
    align-items: stretch;
  }

  /* ── LEFT PANEL ── */
  .left-panel {
    width: 44%;
    background: var(--navy);
    display: flex;
    flex-direction: column;
    justify-content: center;
    padding: 60px 56px;
    position: relative;
    overflow: hidden;
  }
  .left-panel::before {
    content: '';
    position: absolute;
    width: 380px; height: 380px;
    border-radius: 50%;
    border: 60px solid rgba(201,146,42,.12);
    bottom: -120px; right: -120px;
  }
  .left-panel::after {
    content: '';
    position: absolute;
    width: 200px; height: 200px;
    border-radius: 50%;
    border: 40px solid rgba(201,146,42,.08);
    top: -60px; left: -60px;
  }
  .lp-logo { display: flex; align-items: center; gap: 14px; margin-bottom: 56px; }
  .lp-logo-icon {
    width: 100px; height: 100px;
    background: var(--gold);
    border-radius: var(--radius);
    display: flex; align-items: center; justify-content: center;
  }
  .lp-logo-icon i { font-size: 26px; color: #fff; }
  .lp-logo-name { color: #fff; font-size: 15px; font-weight: 600; line-height: 1.3; }
  .lp-logo-name small { display: block; font-size: 11px; color: rgba(255,255,255,.45); font-weight: 400; }
  .lp-headline {
    font-family: 'DM Serif Display', serif;
    color: #fff; font-size: 36px; line-height: 1.2; margin-bottom: 16px;
  }
  .lp-headline span { color: var(--gold-lt); }
  .lp-sub {
    color: rgba(255,255,255,.55); font-size: 14px;
    line-height: 1.7; max-width: 320px; margin-bottom: 48px;
  }
  .lp-features { display: flex; flex-direction: column; gap: 14px; }
  .lp-feat { display: flex; align-items: center; gap: 12px; color: rgba(255,255,255,.7); font-size: 13px; }
  .lp-feat i { font-size: 18px; color: var(--gold); }

  /* ── RIGHT PANEL ── */
  .right-panel {
    flex: 1;
    display: flex; align-items: center; justify-content: center;
    padding: 40px 32px;
  }
  .login-box { width: 100%; max-width: 420px; }
  .login-box h1 { font-size: 26px; font-weight: 600; color: var(--text); margin-bottom: 6px; }
  .login-box .subtitle { font-size: 13px; color: var(--gray-500); margin-bottom: 32px; }

  /* ALERT */
  .alert {
    padding: 12px 14px; border-radius: var(--radius-sm);
    font-size: 13px; margin-bottom: 20px;
    display: flex; align-items: flex-start; gap: 8px;
  }
  .alert-error   { background: var(--danger-bg); color: var(--danger); border: 0.5px solid #f09595; }
  .alert-success { background: #eaf3de; color: #3b6d11; border: 0.5px solid #c0dd97; }
  .alert i { font-size: 16px; margin-top: 1px; flex-shrink: 0; }

  /* FORM */
  .field { margin-bottom: 18px; }
  .field label {
    display: block; font-size: 12px; font-weight: 500;
    color: var(--gray-700); margin-bottom: 6px; letter-spacing: .01em;
  }
  .input-wrap { position: relative; }
  .input-wrap i {
    position: absolute; left: 12px; top: 50%; transform: translateY(-50%);
    font-size: 17px; color: var(--gray-300); pointer-events: none;
  }
  .input-wrap input {
    width: 100%; padding: 11px 14px 11px 38px;
    border: 1px solid var(--gray-100); border-radius: var(--radius-sm);
    font-family: 'DM Sans', sans-serif; font-size: 14px;
    color: var(--text); background: var(--white);
    transition: border-color .2s, box-shadow .2s; outline: none;
  }
  .input-wrap input:focus {
    border-color: var(--navy);
    box-shadow: 0 0 0 3px rgba(15,37,68,.08);
  }
  .input-wrap input::placeholder { color: var(--gray-300); }
  .toggle-pass {
    position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
    cursor: pointer; color: var(--gray-300); font-size: 17px; user-select: none;
  }
  .toggle-pass:hover { color: var(--gray-500); }

  .form-row { display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; }
  .remember { display: flex; align-items: center; gap: 7px; font-size: 13px; color: var(--gray-500); cursor: pointer; user-select: none; }
  .remember input[type=checkbox] { accent-color: var(--navy); width: 14px; height: 14px; }
  .forgot { font-size: 12px; color: var(--navy2); text-decoration: none; font-weight: 500; }
  .forgot:hover { text-decoration: underline; }

  .btn-login {
    width: 100%; padding: 13px; background: var(--navy); color: #fff;
    border: none; border-radius: var(--radius-sm);
    font-family: 'DM Sans', sans-serif; font-size: 14px; font-weight: 600;
    cursor: pointer; letter-spacing: .02em;
    transition: background .2s, transform .1s;
    display: flex; align-items: center; justify-content: center; gap: 8px;
  }
  .btn-login:hover  { background: var(--navy2); }
  .btn-login:active { transform: scale(.99); }
  .btn-login i { font-size: 18px; }
  .btn-login.loading { opacity: .7; pointer-events: none; }

  .divider {
    display: flex; align-items: center; gap: 12px;
    margin: 24px 0; color: var(--gray-300); font-size: 11px;
  }
  .divider::before, .divider::after { content: ''; flex: 1; border-top: 0.5px solid var(--gray-100); }

  .login-footer { margin-top: 28px; font-size: 11px; color: var(--gray-300); text-align: center; line-height: 1.6; }

  @media(max-width: 768px) {
    .left-panel { display: none; }
    .right-panel { padding: 32px 20px; }
  }
</style>
</head>
<body>

  <!-- ── LEFT PANEL ── -->
  <div class="left-panel">
    <div class="lp-logo">
      <div class="lp-logo-icon"><img src="../../images/uoa_logo.png" height="100%" width="100%"></div>
      <div class="lp-logo-name">DVC Office <small>Automation System</small></div>
    </div>
    <h1 class="lp-headline">Smart tools for a<br><span>productive office.</span></h1>
    <p class="lp-sub">A unified platform for documents, tasks, attendance, appointments, and communication — built for the Office of the Deputy Vice Chancellor.</p>
    <div class="lp-features">
      <div class="lp-feat"><i class="ti ti-files"></i> Document management &amp; versioning</div>
      <div class="lp-feat"><i class="ti ti-checklist"></i> Task assignment &amp; tracking</div>
      <div class="lp-feat"><i class="ti ti-fingerprint"></i> Biometric attendance system</div>
      <div class="lp-feat"><i class="ti ti-calendar-event"></i> Appointment scheduling</div>
      <div class="lp-feat"><i class="ti ti-shield-lock"></i> Role-based access control</div>
    </div>
  </div>

  <!-- ── RIGHT PANEL ── -->
  <div class="right-panel">
    <div class="login-box">
      <h1>Welcome back</h1>
      <p class="subtitle">Sign in to your office account to continue</p>

      <?php if ($error): ?>
        <div class="alert alert-error">
          <i class="ti ti-alert-circle"></i>
          <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <?php if ($success): ?>
        <div class="alert alert-success">
          <i class="ti ti-circle-check"></i>
          <?= htmlspecialchars($success) ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="" novalidate onsubmit="handleSubmit(this)">
        <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">

        <div class="field">
          <label for="email">Email address</label>
          <div class="input-wrap">
            <i class="ti ti-mail"></i>
            <input
              type="email" id="email" name="email"
              placeholder="you@university.edu.ng"
              value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
              autocomplete="email" required
            >
          </div>
        </div>

        <div class="field">
          <label for="password">Password</label>
          <div class="input-wrap">
            <i class="ti ti-lock"></i>
            <input
              type="password" id="password" name="password"
              placeholder="Enter your password"
              autocomplete="current-password" required
            >
            <span class="toggle-pass" onclick="togglePass()">
              <i class="ti ti-eye" id="eyeIcon"></i>
            </span>
          </div>
        </div>

        <div class="form-row">
          <label class="remember">
            <input type="checkbox" name="remember"> Remember me
          </label>
          <a href="<?= BASE_URL ?>/modules/auth/forgot_password.php" class="forgot">Forgot password?</a>
        </div>

        <button type="submit" class="btn-login" id="submitBtn">
          <i class="ti ti-login"></i> Sign in to dashboard
        </button>
      </form>

      <div class="divider">secured access</div>

      <p class="login-footer">
        Having trouble signing in? Contact the ICT Office.<br>
        &copy; <?= date('Y') ?> <?= htmlspecialchars(APP_NAME) ?>
      </p>
    </div>
  </div>

<script>
function togglePass() {
  const inp  = document.getElementById('password');
  const icon = document.getElementById('eyeIcon');
  if (inp.type === 'password') {
    inp.type = 'text';
    icon.className = 'ti ti-eye-off';
  } else {
    inp.type = 'password';
    icon.className = 'ti ti-eye';
  }
}

function handleSubmit(form) {
  const btn = document.getElementById('submitBtn');
  btn.classList.add('loading');
  btn.innerHTML = '<i class="ti ti-loader-2" style="animation:spin .7s linear infinite"></i> Signing in...';
}

const s = document.createElement('style');
s.textContent = '@keyframes spin{to{transform:rotate(360deg)}}';
document.head.appendChild(s);
</script>
</body>
</html>
