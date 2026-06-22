<?php
require_once __DIR__ . '/auth/db_config.php';
session_start_secure();

if (is_logged_in()) redirect('/dashboard');

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email']    ?? '');
    $password = $_POST['password']      ?? '';
    $confirm  = $_POST['confirm']       ?? '';
    $plan     = $_POST['plan']          ?? 'free';

    // Validation
    if (strlen($username) < 3 || strlen($username) > 30)
        $error = 'Username must be 3–30 characters.';
    elseif (!preg_match('/^[a-zA-Z0-9_\-]+$/', $username))
        $error = 'Username may only contain letters, numbers, _ and -.';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL))
        $error = 'Invalid email address.';
    elseif (strlen($password) < 8)
        $error = 'Password must be at least 8 characters.';
    elseif ($password !== $confirm)
        $error = 'Passwords do not match.';
    elseif (!in_array($plan, ['free', 'pro', 'arsenal']))
        $error = 'Invalid plan selected.';
    else {
        try {
            $pdo  = db_connect();
            $stmt = $pdo->prepare('SELECT id FROM users WHERE email=? OR username=? LIMIT 1');
            $stmt->execute([$email, $username]);
            if ($stmt->fetch()) {
                $error = 'Email or username already taken.';
            } else {
                $hash = password_hash($password, PASSWORD_ARGON2ID);
                $ip   = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '';
                $pdo->prepare('INSERT INTO users (email, username, password, plan, ip_address) VALUES (?,?,?,?,?)')
                    ->execute([$email, $username, $hash, $plan, $ip]);
                $userId = $pdo->lastInsertId();
                // Auto-login after register
                $_SESSION['user_id']  = $userId;
                $_SESSION['username'] = $username;
                $_SESSION['plan']     = $plan;
                redirect('/dashboard');
            }
        } catch (Exception $e) {
            $error = 'Registration failed. Please try again.';
        }
    }
}
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Deploy Free — ShadowBridge</title>
<link rel="icon" type="image/x-icon" href="/favicon.ico">
<style>
*{box-sizing:border-box;margin:0;padding:0}
:root{
  --bg:#060810;--surface:#0d1117;--surface2:#111827;
  --border:#1e2738;--fg:#e2e8f0;--muted:#6b7280;
  --cyan:#00d4ff;--cyan2:#00ff9d;--red:#ff3b5c;
  --purple:#8b5cf6;--yellow:#fbbf24;
}
body{background:var(--bg);color:var(--fg);font-family:'Segoe UI','Inter',system-ui,sans-serif;min-height:100vh;display:flex;flex-direction:column;}
body::before{content:'';position:fixed;top:0;left:0;width:100%;height:100%;background:repeating-linear-gradient(0deg,transparent,transparent 2px,rgba(0,212,255,.012) 2px,rgba(0,212,255,.012) 4px);pointer-events:none;z-index:9999;}
nav{position:sticky;top:0;z-index:100;background:rgba(6,8,16,.92);backdrop-filter:blur(16px);border-bottom:1px solid rgba(0,212,255,.12);padding:14px 48px;display:flex;align-items:center;justify-content:space-between;}
.nav-logo{display:flex;align-items:center;gap:10px;text-decoration:none;font-size:.9rem;font-weight:700;letter-spacing:2px;color:var(--cyan);text-transform:uppercase;}
.nav-logo svg{width:28px;height:28px;}
.nav-right a{color:var(--muted);text-decoration:none;font-size:.82rem;margin-left:24px;}
.nav-right a:hover{color:var(--fg);}
main{flex:1;display:flex;align-items:center;justify-content:center;padding:60px 20px;}
.card{background:var(--surface);border:1px solid var(--border);border-radius:12px;padding:48px;width:100%;max-width:480px;position:relative;overflow:hidden;}
.card::before{content:'';position:absolute;top:0;left:0;right:0;height:2px;background:linear-gradient(90deg,transparent,var(--cyan),var(--cyan2),transparent);}
.badge{display:inline-block;background:rgba(0,212,255,.08);border:1px solid rgba(0,212,255,.2);color:var(--cyan);font-size:.65rem;letter-spacing:2px;text-transform:uppercase;padding:4px 12px;border-radius:4px;margin-bottom:20px;}
h1{font-size:1.6rem;font-weight:700;margin-bottom:8px;}
.sub{color:var(--muted);font-size:.85rem;margin-bottom:32px;}
.form-group{margin-bottom:20px;}
label{display:block;font-size:.75rem;letter-spacing:1px;text-transform:uppercase;color:var(--muted);margin-bottom:8px;}
input[type=text],input[type=email],input[type=password]{width:100%;background:var(--surface2);border:1px solid var(--border);border-radius:6px;padding:12px 16px;color:var(--fg);font-size:.9rem;outline:none;transition:.2s;}
input:focus{border-color:var(--cyan);box-shadow:0 0 0 3px rgba(0,212,255,.08);}
.plan-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:8px;}
.plan-option{position:relative;}
.plan-option input{position:absolute;opacity:0;width:0;height:0;}
.plan-label{display:block;border:1px solid var(--border);border-radius:6px;padding:12px 8px;cursor:pointer;text-align:center;transition:.2s;font-size:.75rem;}
.plan-label .plan-name{font-weight:700;letter-spacing:1px;text-transform:uppercase;margin-bottom:4px;}
.plan-label .plan-price{color:var(--muted);font-size:.7rem;}
.plan-option input:checked + .plan-label{border-color:var(--cyan);background:rgba(0,212,255,.06);color:var(--cyan);}
.plan-option input:checked + .plan-label .plan-name{color:var(--cyan);}
.btn{display:block;width:100%;background:var(--cyan);color:#000;border:none;border-radius:6px;padding:14px;font-size:.85rem;font-weight:700;letter-spacing:1px;text-transform:uppercase;cursor:pointer;margin-top:28px;transition:.2s;}
.btn:hover{box-shadow:0 0 24px rgba(0,212,255,.4);transform:translateY(-1px);}
.error{background:rgba(255,59,92,.08);border:1px solid rgba(255,59,92,.3);color:var(--red);border-radius:6px;padding:12px 16px;font-size:.83rem;margin-bottom:20px;}
.signin-link{text-align:center;margin-top:24px;font-size:.82rem;color:var(--muted);}
.signin-link a{color:var(--cyan);text-decoration:none;}
footer{text-align:center;padding:24px;color:var(--muted);font-size:.72rem;border-top:1px solid var(--border);}
</style>
</head>
<body>
<nav>
  <a class="nav-logo" href="/">
    <svg viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
      <path d="M14 2L4 7v7c0 5.5 4.3 10.7 10 12 5.7-1.3 10-6.5 10-12V7L14 2z" stroke="#00d4ff" stroke-width="1.5" fill="none"/>
      <path d="M10 14l3 3 5-5" stroke="#00ff9d" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>
    ShadowBridge
  </a>
  <div class="nav-right">
    <a href="/">Home</a>
    <a href="/login">Sign in</a>
  </div>
</nav>

<main>
  <div class="card">
    <div class="badge">Free Deployment</div>
    <h1>Deploy your arsenal</h1>
    <p class="sub">Create your ShadowBridge account — no credit card required.</p>

    <?php if ($error): ?>
    <div class="error">⚠ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" novalidate>
      <div class="form-group">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" placeholder="operator_callsign"
               value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required autocomplete="username">
      </div>
      <div class="form-group">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" placeholder="you@domain.tld"
               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required autocomplete="email">
      </div>
      <div class="form-group">
        <label for="password">Password <span style="color:var(--muted);font-size:.7rem;text-transform:none;">(min 8 chars)</span></label>
        <input type="password" id="password" name="password" placeholder="••••••••" required autocomplete="new-password">
      </div>
      <div class="form-group">
        <label for="confirm">Confirm password</label>
        <input type="password" id="confirm" name="confirm" placeholder="••••••••" required autocomplete="new-password">
      </div>

      <div class="form-group">
        <label>Plan</label>
        <div class="plan-grid">
          <div class="plan-option">
            <input type="radio" name="plan" id="p_free" value="free" <?= (($_POST['plan'] ?? 'free') === 'free') ? 'checked' : '' ?>>
            <label class="plan-label" for="p_free">
              <div class="plan-name">Free</div>
              <div class="plan-price">$0 / mo</div>
            </label>
          </div>
          <div class="plan-option">
            <input type="radio" name="plan" id="p_pro" value="pro" <?= (($_POST['plan'] ?? '') === 'pro') ? 'checked' : '' ?>>
            <label class="plan-label" for="p_pro">
              <div class="plan-name">Pro</div>
              <div class="plan-price">$12 / mo</div>
            </label>
          </div>
          <div class="plan-option">
            <input type="radio" name="plan" id="p_ars" value="arsenal" <?= (($_POST['plan'] ?? '') === 'arsenal') ? 'checked' : '' ?>>
            <label class="plan-label" for="p_ars">
              <div class="plan-name">Arsenal</div>
              <div class="plan-price">$49 / mo</div>
            </label>
          </div>
        </div>
      </div>

      <button type="submit" class="btn">Deploy free →</button>
    </form>

    <div class="signin-link">
      Already have an account? <a href="/login">Sign in →</a>
    </div>
  </div>
</main>

<footer>© 2026 ShadowBridge — Cybersecurity Arsenal Platform</footer>
</body>
</html>
