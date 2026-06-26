<?php
require_once __DIR__ . '/auth/db_config.php';
session_start_secure();

if (is_logged_in()) redirect('/dashboard.php');

$error    = '';
$redirect = $_GET['redirect'] ?? '/dashboard.php';
if (!preg_match('/^\/[a-zA-Z0-9\/_\-\.]*$/', $redirect)) $redirect = '/dashboard.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identity = trim($_POST['identity'] ?? '');
    $password = $_POST['password']      ?? '';
    $remember = !empty($_POST['remember']);

    if (!$identity || !$password) {
        $error = 'Please enter your email/username and password.';
    } else {
        try {
            $pdo  = db_connect();
            $stmt = $pdo->prepare('SELECT id, username, password, plan FROM users WHERE email=? OR username=? LIMIT 1');
            $stmt->execute([$identity, $identity]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id']  = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['plan']     = $user['plan'];

                // Update last login
                $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '';
                $pdo->prepare('UPDATE users SET last_login=NOW(), ip_address=? WHERE id=?')
                    ->execute([$ip, $user['id']]);

                if ($remember) {
                    // Extend session cookie to 30 days
                    $lifetime = 30 * 24 * 3600;
                    session_set_cookie_params(['lifetime' => $lifetime, 'secure' => true, 'httponly' => true, 'samesite' => 'Strict']);
                    session_regenerate_id(true);
                }
                redirect($redirect);
            } else {
                $error = 'Invalid credentials. Check your email/username and password.';
                // Add small delay to slow brute-force
                usleep(500000);
            }
        } catch (Exception $e) {
            $error = 'Login failed. Please try again.';
        }
    }
}
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Sign In — ShadowBridge</title>
<link rel="icon" type="image/x-icon" href="/favicon.ico">
<style>
*{box-sizing:border-box;margin:0;padding:0}
:root{
  --bg:#060810;--surface:#0d1117;--surface2:#111827;
  --border:#1e2738;--fg:#e2e8f0;--muted:#6b7280;
  --cyan:#00d4ff;--cyan2:#00ff9d;--red:#ff3b5c;
}
body{background:var(--bg);color:var(--fg);font-family:'Segoe UI','Inter',system-ui,sans-serif;min-height:100vh;display:flex;flex-direction:column;}
body::before{content:'';position:fixed;top:0;left:0;width:100%;height:100%;background:repeating-linear-gradient(0deg,transparent,transparent 2px,rgba(0,212,255,.012) 2px,rgba(0,212,255,.012) 4px);pointer-events:none;z-index:9999;}
nav{position:sticky;top:0;z-index:100;background:rgba(6,8,16,.92);backdrop-filter:blur(16px);border-bottom:1px solid rgba(0,212,255,.12);padding:14px 48px;display:flex;align-items:center;justify-content:space-between;}
.nav-logo{display:flex;align-items:center;gap:10px;text-decoration:none;font-size:.9rem;font-weight:700;letter-spacing:2px;color:var(--cyan);text-transform:uppercase;}
.nav-logo svg{width:28px;height:28px;}
.nav-right a{color:var(--muted);text-decoration:none;font-size:.82rem;margin-left:24px;}
.nav-right a:hover{color:var(--fg);}
main{flex:1;display:flex;align-items:center;justify-content:center;padding:60px 20px;}
.card{background:var(--surface);border:1px solid var(--border);border-radius:12px;padding:48px;width:100%;max-width:420px;position:relative;overflow:hidden;}
.card::before{content:'';position:absolute;top:0;left:0;right:0;height:2px;background:linear-gradient(90deg,transparent,var(--cyan),var(--cyan2),transparent);}
.badge{display:inline-block;background:rgba(0,212,255,.08);border:1px solid rgba(0,212,255,.2);color:var(--cyan);font-size:.65rem;letter-spacing:2px;text-transform:uppercase;padding:4px 12px;border-radius:4px;margin-bottom:20px;}
h1{font-size:1.6rem;font-weight:700;margin-bottom:8px;}
.sub{color:var(--muted);font-size:.85rem;margin-bottom:32px;}
.form-group{margin-bottom:20px;}
label{display:block;font-size:.75rem;letter-spacing:1px;text-transform:uppercase;color:var(--muted);margin-bottom:8px;}
input[type=text],input[type=email],input[type=password]{width:100%;background:var(--surface2);border:1px solid var(--border);border-radius:6px;padding:12px 16px;color:var(--fg);font-size:.9rem;outline:none;transition:.2s;}
input:focus{border-color:var(--cyan);box-shadow:0 0 0 3px rgba(0,212,255,.08);}
.remember-row{display:flex;align-items:center;gap:10px;margin-bottom:8px;}
.remember-row input[type=checkbox]{accent-color:var(--cyan);width:16px;height:16px;cursor:pointer;}
.remember-row label{font-size:.8rem;color:var(--muted);text-transform:none;letter-spacing:0;margin:0;cursor:pointer;}
.btn{display:block;width:100%;background:var(--cyan);color:#000;border:none;border-radius:6px;padding:14px;font-size:.85rem;font-weight:700;letter-spacing:1px;text-transform:uppercase;cursor:pointer;margin-top:28px;transition:.2s;}
.btn:hover{box-shadow:0 0 24px rgba(0,212,255,.4);transform:translateY(-1px);}
.error{background:rgba(255,59,92,.08);border:1px solid rgba(255,59,92,.3);color:var(--red);border-radius:6px;padding:12px 16px;font-size:.83rem;margin-bottom:20px;}
.register-link{text-align:center;margin-top:24px;font-size:.82rem;color:var(--muted);}
.register-link a{color:var(--cyan);text-decoration:none;}
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
    <a href="/register">Deploy free</a>
  </div>
</nav>

<main>
  <div class="card">
    <div class="badge">Operator Auth</div>
    <h1>Welcome back</h1>
    <p class="sub">Sign in to your ShadowBridge command center.</p>

    <?php if ($error): ?>
    <div class="error">⚠ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" novalidate>
      <div class="form-group">
        <label for="identity">Email or Username</label>
        <input type="text" id="identity" name="identity" placeholder="operator@domain.tld"
               value="<?= htmlspecialchars($_POST['identity'] ?? '') ?>" required autocomplete="username">
      </div>
      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="••••••••" required autocomplete="current-password">
      </div>
      <div class="remember-row">
        <input type="checkbox" id="remember" name="remember" value="1">
        <label for="remember">Remember me for 30 days</label>
      </div>
      <button type="submit" class="btn">Access command center →</button>
    </form>

    <div class="register-link">
      No account? <a href="/register">Deploy free →</a>
    </div>
  </div>
</main>

<footer>© 2026 ShadowBridge — Cybersecurity Arsenal Platform</footer>
</body>
</html>
