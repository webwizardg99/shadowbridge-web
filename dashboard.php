<?php
require_once __DIR__ . '/auth/db_config.php';
require_login();

$username = htmlspecialchars($_SESSION['username'] ?? 'Operator');
$plan     = $_SESSION['plan'] ?? 'free';
$planColors = ['free' => '#6b7280', 'pro' => '#00d4ff', 'arsenal' => '#00ff9d'];
$planColor  = $planColors[$plan] ?? '#6b7280';
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Dashboard — ShadowBridge</title>
<link rel="icon" type="image/x-icon" href="/favicon.ico">
<style>
*{box-sizing:border-box;margin:0;padding:0}
:root{
  --bg:#060810;--surface:#0d1117;--surface2:#111827;
  --border:#1e2738;--fg:#e2e8f0;--muted:#6b7280;
  --cyan:#00d4ff;--cyan2:#00ff9d;--red:#ff3b5c;--purple:#8b5cf6;
}
body{background:var(--bg);color:var(--fg);font-family:'Segoe UI','Inter',system-ui,sans-serif;min-height:100vh;}
body::before{content:'';position:fixed;top:0;left:0;width:100%;height:100%;background:repeating-linear-gradient(0deg,transparent,transparent 2px,rgba(0,212,255,.012) 2px,rgba(0,212,255,.012) 4px);pointer-events:none;z-index:9999;}
nav{position:sticky;top:0;z-index:100;background:rgba(6,8,16,.92);backdrop-filter:blur(16px);border-bottom:1px solid rgba(0,212,255,.12);padding:14px 48px;display:flex;align-items:center;justify-content:space-between;}
.nav-logo{display:flex;align-items:center;gap:10px;text-decoration:none;font-size:.9rem;font-weight:700;letter-spacing:2px;color:var(--cyan);text-transform:uppercase;}
.nav-logo svg{width:28px;height:28px;}
.nav-right{display:flex;align-items:center;gap:20px;}
.plan-badge{font-size:.68rem;letter-spacing:1.5px;text-transform:uppercase;padding:3px 10px;border-radius:4px;border:1px solid;font-weight:700;}
.logout{color:var(--muted);text-decoration:none;font-size:.8rem;}
.logout:hover{color:var(--red);}
main{max-width:960px;margin:0 auto;padding:48px 24px;}
.welcome{margin-bottom:40px;}
.welcome h1{font-size:1.8rem;font-weight:700;margin-bottom:8px;}
.welcome p{color:var(--muted);font-size:.9rem;}
.grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:16px;}
.module{background:var(--surface);border:1px solid var(--border);border-radius:10px;padding:24px;transition:.2s;position:relative;overflow:hidden;}
.module::before{content:'';position:absolute;top:0;left:0;right:0;height:2px;}
.module.cyan::before{background:linear-gradient(90deg,transparent,var(--cyan),transparent);}
.module.green::before{background:linear-gradient(90deg,transparent,var(--cyan2),transparent);}
.module.red::before{background:linear-gradient(90deg,transparent,var(--red),transparent);}
.module.purple::before{background:linear-gradient(90deg,transparent,var(--purple),transparent);}
.module:hover{border-color:rgba(0,212,255,.3);transform:translateY(-2px);}
.module-icon{font-size:1.5rem;margin-bottom:14px;}
.module h3{font-size:.9rem;font-weight:700;letter-spacing:.5px;text-transform:uppercase;margin-bottom:6px;}
.module p{font-size:.78rem;color:var(--muted);line-height:1.6;}
.module .status{display:inline-flex;align-items:center;gap:6px;margin-top:14px;font-size:.72rem;color:var(--muted);}
.dot{width:6px;height:6px;border-radius:50%;display:inline-block;}
.dot.live{background:var(--cyan2);box-shadow:0 0 6px var(--cyan2);animation:pulse 2s infinite;}
.dot.setup{background:var(--muted);}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:.4}}
.setup-box{background:var(--surface2);border:1px solid rgba(0,212,255,.15);border-radius:10px;padding:28px;margin-top:40px;}
.setup-box h2{font-size:1rem;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:var(--cyan);margin-bottom:16px;}
.setup-box ol{color:var(--muted);font-size:.83rem;line-height:2;padding-left:20px;}
.setup-box code{background:rgba(0,212,255,.06);border:1px solid rgba(0,212,255,.12);border-radius:4px;padding:2px 8px;font-size:.8rem;color:var(--cyan2);font-family:monospace;}
</style>
</head>
<body>
<nav>
  <a class="nav-logo" href="/">
    <svg viewBox="0 0 28 28" fill="none">
      <path d="M14 2L4 7v7c0 5.5 4.3 10.7 10 12 5.7-1.3 10-6.5 10-12V7L14 2z" stroke="#00d4ff" stroke-width="1.5" fill="none"/>
      <path d="M10 14l3 3 5-5" stroke="#00ff9d" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>
    ShadowBridge
  </a>
  <div class="nav-right">
    <span class="plan-badge" style="color:<?= $planColor ?>;border-color:<?= $planColor ?>40;"><?= strtoupper($plan) ?></span>
    <span style="font-size:.82rem;color:var(--muted);"><?= $username ?></span>
    <a class="logout" href="/logout">Sign out</a>
  </div>
</nav>

<main>
  <div class="welcome">
    <h1>Command Center</h1>
    <p>Welcome back, <strong style="color:var(--cyan);"><?= $username ?></strong>. Your arsenal is ready.</p>
  </div>

  <div class="grid">
    <div class="module cyan">
      <div class="module-icon">🛡️</div>
      <h3>Lab Dashboard</h3>
      <p>Real-time NOX Lab monitoring — machines, services, network status.</p>
      <span class="status"><span class="dot setup"></span>Setup required</span>
    </div>
    <div class="module green">
      <div class="module-icon">🕯️</div>
      <h3>SENTINEL</h3>
      <p>Honeypot aggregation — Cowrie SSH, HTTP canary tokens, alert pipeline.</p>
      <span class="status"><span class="dot setup"></span>Setup required</span>
    </div>
    <div class="module red">
      <div class="module-icon">🎯</div>
      <h3>ATLAS</h3>
      <p>MITRE ATT&CK purple team mapper — track TTPs red/blue/purple.</p>
      <span class="status"><span class="dot setup"></span>Setup required</span>
    </div>
    <div class="module purple">
      <div class="module-icon">🤖</div>
      <h3>NOX-BRAIN</h3>
      <p>AI analysis layer — grounded in live SENTINEL/Villain/IDS data.</p>
      <span class="status"><span class="dot setup"></span>Setup required</span>
    </div>
    <div class="module cyan">
      <div class="module-icon">🔐</div>
      <h3>VAULT</h3>
      <p>Credential intel hub — John the Ripper + hashcat integration.</p>
      <span class="status"><span class="dot setup"></span>Setup required</span>
    </div>
    <div class="module green">
      <div class="module-icon">⚡</div>
      <h3>NOX-COMMAND</h3>
      <p>Kill chain orchestrator — OSINT → Recon → Exploit → C2 → Loot.</p>
      <span class="status"><span class="dot setup"></span>Setup required</span>
    </div>
  </div>

  <div class="setup-box">
    <h2>⚙ Getting started</h2>
    <ol>
      <li>Clone the repo: <code>git clone https://github.com/webwizardg99/shadow-lab</code></li>
      <li>Run the installer: <code>./nox-start.sh</code></li>
      <li>Configure your lab IP and Tailscale in <code>config.json</code></li>
      <li>Point your browser to <code>http://&lt;your-ip&gt;:8888</code></li>
      <li>Return here to link your instance — coming soon in v2.</li>
    </ol>
  </div>
</main>
</body>
</html>
