<?php
require_once __DIR__ . '/auth/db_config.php';
require_login();
$username = htmlspecialchars($_SESSION['username'] ?? 'Operator');
$plan     = $_SESSION['plan'] ?? 'free';
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>NOX Control — ShadowBridge</title>
<link rel="icon" type="image/x-icon" href="/favicon.ico">
<style>
*{box-sizing:border-box;margin:0;padding:0}
:root{
  --bg:#060810;--surface:#0d1117;--surface2:#111827;--surface3:#1a2235;
  --border:#1e2738;--border2:#2a3a50;
  --fg:#e2e8f0;--muted:#6b7280;--muted2:#4b5563;
  --cyan:#00d4ff;--cyan2:#00ff9d;--red:#ff3b5c;--orange:#ff9500;
  --purple:#a855f7;--yellow:#fbbf24;
  --font:'SF Mono','Fira Code',monospace;
}
html,body{height:100%;background:var(--bg);color:var(--fg);font-family:var(--font);font-size:14px;}
.layout{display:flex;height:100vh;overflow:hidden;}
/* Sidebar */
.sidebar{width:220px;min-width:220px;background:var(--surface);border-right:1px solid var(--border);display:flex;flex-direction:column;overflow-y:auto;}
.sidebar-logo{padding:20px 16px 16px;border-bottom:1px solid var(--border);}
.sidebar-logo .brand{font-size:.85rem;font-weight:700;letter-spacing:2px;color:var(--cyan);text-transform:uppercase;}
.sidebar-logo .sub{font-size:.65rem;color:var(--muted);letter-spacing:1px;margin-top:2px;}
.node-status{margin:12px;padding:10px 12px;background:var(--surface2);border:1px solid var(--border);border-radius:6px;font-size:.7rem;}
.node-status .label{color:var(--muted);letter-spacing:1px;text-transform:uppercase;margin-bottom:6px;}
.node-status .val{display:flex;align-items:center;gap:6px;}
.dot{width:7px;height:7px;border-radius:50%;display:inline-block;}
.dot.online{background:var(--cyan2);box-shadow:0 0 6px var(--cyan2);}
.dot.offline{background:var(--red);}
.dot.stale{background:var(--orange);}
.dot.unknown{background:var(--muted);}
nav{flex:1;padding:8px 0;}
nav a{display:flex;align-items:center;gap:10px;padding:9px 16px;color:var(--muted);text-decoration:none;font-size:.75rem;letter-spacing:.5px;text-transform:uppercase;transition:.15s;border-left:2px solid transparent;position:relative;}
nav a:hover{color:var(--fg);background:var(--surface2);}
nav a.active{color:var(--cyan);border-left-color:var(--cyan);background:rgba(0,212,255,.05);}
nav a .ico{width:16px;text-align:center;font-size:.85rem;}
nav a .nav-badge{position:absolute;right:12px;background:var(--red);color:#fff;font-size:.55rem;font-weight:700;padding:1px 5px;border-radius:8px;letter-spacing:0;}
.nav-sep{height:1px;background:var(--border);margin:6px 12px;}
.nav-section{padding:8px 16px 4px;font-size:.6rem;color:var(--muted2);letter-spacing:2px;text-transform:uppercase;}
.sidebar-footer{padding:12px 16px;border-top:1px solid var(--border);font-size:.68rem;color:var(--muted);}
.sidebar-footer a{color:var(--muted);text-decoration:none;}
.sidebar-footer a:hover{color:var(--cyan);}
/* Main */
.main{flex:1;display:flex;flex-direction:column;overflow:hidden;}
.topbar{height:48px;min-height:48px;background:var(--surface);border-bottom:1px solid var(--border);display:flex;align-items:center;padding:0 20px;gap:16px;}
.topbar .page-title{font-size:.8rem;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:var(--fg);flex:1;}
.topbar .last-sync{font-size:.68rem;color:var(--muted);display:flex;align-items:center;gap:6px;}
.btn-sm{padding:5px 12px;font-size:.68rem;border-radius:4px;border:1px solid var(--border2);background:var(--surface2);color:var(--muted);cursor:pointer;letter-spacing:.5px;text-transform:uppercase;transition:.15s;}
.btn-sm:hover{border-color:var(--cyan);color:var(--cyan);}
.content{flex:1;overflow-y:auto;padding:20px;}
/* Panels */
.panel{display:none;} .panel.active{display:block;}
/* Cards */
.stats-row{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:20px;}
.stat-box{background:var(--surface);border:1px solid var(--border);border-radius:8px;padding:16px;}
.stat-box .val{font-size:1.6rem;font-weight:700;color:var(--cyan);font-variant-numeric:tabular-nums;}
.stat-box .lbl{font-size:.62rem;color:var(--muted);text-transform:uppercase;letter-spacing:1.5px;margin-top:4px;}
.stat-box .sub{font-size:.62rem;color:var(--muted2);margin-top:2px;}
.overview-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:14px;margin-bottom:20px;}
.svc-card{background:var(--surface);border:1px solid var(--border);border-radius:8px;padding:16px;position:relative;overflow:hidden;transition:.2s;cursor:pointer;}
.svc-card::before{content:'';position:absolute;top:0;left:0;right:0;height:2px;}
.svc-card.cyan::before{background:linear-gradient(90deg,transparent,var(--cyan),transparent);}
.svc-card.green::before{background:linear-gradient(90deg,transparent,var(--cyan2),transparent);}
.svc-card.red::before{background:linear-gradient(90deg,transparent,var(--red),transparent);}
.svc-card.purple::before{background:linear-gradient(90deg,transparent,var(--purple),transparent);}
.svc-card.orange::before{background:linear-gradient(90deg,transparent,var(--orange),transparent);}
.svc-card:hover{border-color:var(--border2);transform:translateY(-1px);}
.svc-card .svc-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;}
.svc-card .svc-name{font-size:.78rem;font-weight:700;letter-spacing:.5px;text-transform:uppercase;}
.svc-card .svc-port{font-size:.65rem;color:var(--muted);background:var(--surface2);padding:2px 7px;border-radius:3px;border:1px solid var(--border);}
.svc-card .svc-desc{font-size:.7rem;color:var(--muted);line-height:1.5;}
.svc-card .svc-status{display:flex;align-items:center;gap:6px;font-size:.72rem;margin-top:10px;}
.badge{display:inline-flex;align-items:center;gap:4px;padding:2px 8px;border-radius:3px;font-size:.62rem;font-weight:700;letter-spacing:.5px;text-transform:uppercase;}
.badge.up{background:rgba(0,255,157,.1);color:var(--cyan2);border:1px solid rgba(0,255,157,.2);}
.badge.down{background:rgba(255,59,92,.1);color:var(--red);border:1px solid rgba(255,59,92,.2);}
.badge.warn{background:rgba(255,149,0,.1);color:var(--orange);border:1px solid rgba(255,149,0,.2);}
/* Event feed */
.event-feed{background:var(--surface);border:1px solid var(--border);border-radius:8px;overflow:hidden;}
.feed-header{padding:12px 16px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;}
.feed-header .title{font-size:.75rem;font-weight:700;letter-spacing:.5px;text-transform:uppercase;}
.feed-body{max-height:320px;overflow-y:auto;}
.feed-item{padding:10px 16px;border-bottom:1px solid var(--border);display:grid;grid-template-columns:auto 1fr auto;gap:12px;align-items:start;}
.feed-item:last-child{border-bottom:none;}
.feed-type{font-size:.62rem;padding:2px 7px;border-radius:3px;white-space:nowrap;font-weight:700;}
.feed-type.sentinel{background:rgba(255,59,92,.15);color:var(--red);}
.feed-type.atlas{background:rgba(168,85,247,.15);color:var(--purple);}
.feed-type.system{background:rgba(0,212,255,.1);color:var(--cyan);}
.feed-type.honeyai{background:rgba(255,149,0,.15);color:var(--orange);}
.feed-type.ids{background:rgba(251,191,36,.15);color:var(--yellow);}
.feed-msg{font-size:.72rem;color:var(--muted);line-height:1.5;}
.feed-time{font-size:.62rem;color:var(--muted2);white-space:nowrap;}
/* Machine grid */
.machine-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:12px;}
.machine-card{background:var(--surface);border:1px solid var(--border);border-radius:8px;padding:16px;}
.machine-card .mname{font-size:.78rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px;}
.machine-card .mip{font-size:.68rem;color:var(--muted);}
.machine-card .mstats{display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px;margin-top:12px;}
.machine-card .mstat .v{font-size:.95rem;font-weight:700;color:var(--cyan);font-variant-numeric:tabular-nums;text-align:center;}
.machine-card .mstat .l{font-size:.58rem;color:var(--muted2);text-transform:uppercase;letter-spacing:1px;text-align:center;}
.progress{height:3px;background:var(--surface2);border-radius:2px;margin-top:3px;}
.progress-bar{height:100%;border-radius:2px;transition:width .5s;}
.progress-bar.cpu{background:var(--cyan);}
.progress-bar.mem{background:var(--purple);}
.progress-bar.disk{background:var(--orange);}
/* Terminal */
.term-section{background:var(--surface);border:1px solid var(--border);border-radius:8px;overflow:hidden;margin-bottom:16px;}
.term-bar{padding:8px 14px;background:var(--surface2);border-bottom:1px solid var(--border);display:flex;align-items:center;gap:8px;}
.term-dot{width:10px;height:10px;border-radius:50%;}
.term-dot.r{background:#ff5f56;} .term-dot.y{background:#ffbd2e;} .term-dot.g{background:#27c93f;}
.term-title-txt{font-size:.68rem;color:var(--muted);flex:1;text-align:center;letter-spacing:1px;}
.term-body{padding:14px;font-size:.72rem;line-height:1.8;color:var(--cyan2);max-height:300px;overflow-y:auto;}
.t-muted{color:var(--muted);} .t-red{color:var(--red);} .t-orange{color:var(--orange);} .t-cyan{color:var(--cyan);} .t-yellow{color:var(--yellow);} .t-purple{color:var(--purple);}
/* ATLAS */
.ttp-table{width:100%;border-collapse:collapse;font-size:.72rem;}
.ttp-table th{text-align:left;padding:8px 12px;border-bottom:1px solid var(--border);color:var(--muted);font-size:.62rem;letter-spacing:1px;text-transform:uppercase;}
.ttp-table td{padding:8px 12px;border-bottom:1px solid var(--border);}
.ttp-table tr:last-child td{border-bottom:none;}
.ttp-status{padding:2px 8px;border-radius:3px;font-size:.62rem;font-weight:700;}
.ttp-status.red{background:rgba(255,59,92,.15);color:var(--red);}
.ttp-status.blue{background:rgba(0,212,255,.1);color:var(--cyan);}
.ttp-status.purple{background:rgba(168,85,247,.15);color:var(--purple);}
/* Chat */
.chat-wrap{display:flex;flex-direction:column;height:calc(100vh - 180px);}
.chat-msgs{flex:1;overflow-y:auto;padding:16px;display:flex;flex-direction:column;gap:12px;}
.chat-msg{max-width:72%;padding:10px 14px;border-radius:8px;font-size:.78rem;line-height:1.6;}
.chat-msg.user{background:rgba(0,212,255,.1);border:1px solid rgba(0,212,255,.2);align-self:flex-end;}
.chat-msg.ai{background:var(--surface2);border:1px solid var(--border);align-self:flex-start;}
.chat-msg.ai .ai-label{font-size:.62rem;color:var(--cyan);letter-spacing:1px;text-transform:uppercase;margin-bottom:4px;}
.chat-input-row{display:flex;gap:8px;padding:12px 0 0;}
.chat-input{flex:1;background:var(--surface2);border:1px solid var(--border);border-radius:6px;padding:10px 14px;color:var(--fg);font-family:var(--font);font-size:.78rem;outline:none;transition:.2s;}
.chat-input:focus{border-color:var(--cyan);}
.btn-primary{padding:10px 18px;background:var(--cyan);color:#000;border:none;border-radius:6px;font-family:var(--font);font-size:.75rem;font-weight:700;cursor:pointer;letter-spacing:.5px;text-transform:uppercase;}
/* RuView */
.ruview-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px;}
.ruview-presence{background:var(--surface);border:1px solid var(--border);border-radius:8px;padding:24px;text-align:center;}
.ruview-big{font-size:3rem;font-weight:700;margin:12px 0;}
.ruview-big.present{color:var(--cyan2);}
.ruview-big.absent{color:var(--muted);}
.vitals-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px;}
.vital-card{background:var(--surface);border:1px solid var(--border);border-radius:8px;padding:16px;text-align:center;}
.vital-val{font-size:2rem;font-weight:700;color:var(--cyan);}
.vital-unit{font-size:.72rem;color:var(--muted);margin-left:4px;}
.vital-lbl{font-size:.62rem;color:var(--muted2);text-transform:uppercase;letter-spacing:1.5px;margin-top:4px;}
/* IDS */
.ids-table{width:100%;border-collapse:collapse;font-size:.7rem;}
.ids-table th{padding:8px 10px;border-bottom:1px solid var(--border);color:var(--muted);font-size:.6rem;letter-spacing:1px;text-transform:uppercase;text-align:left;}
.ids-table td{padding:7px 10px;border-bottom:1px solid var(--border);}
.ids-table tr:last-child td{border-bottom:none;}
.ids-sev{padding:2px 7px;border-radius:3px;font-size:.6rem;font-weight:700;}
.ids-sev.s1{background:rgba(255,59,92,.2);color:var(--red);}
.ids-sev.s2{background:rgba(255,149,0,.2);color:var(--orange);}
.ids-sev.s3{background:rgba(251,191,36,.15);color:var(--yellow);}
/* Toast */
#toastContainer{position:fixed;bottom:24px;right:24px;display:flex;flex-direction:column;gap:8px;z-index:9999;}
.toast{background:var(--surface2);border:1px solid var(--border2);border-radius:6px;padding:10px 14px;font-size:.72rem;max-width:300px;box-shadow:0 4px 16px rgba(0,0,0,.4);animation:slideIn .2s ease;display:flex;align-items:flex-start;gap:10px;}
.toast.alert{border-color:rgba(255,59,92,.4);}
.toast.info{border-color:rgba(0,212,255,.3);}
.toast-ico{font-size:.9rem;margin-top:1px;}
.toast-msg{flex:1;line-height:1.4;}
.toast-close{cursor:pointer;color:var(--muted);font-size:.85rem;}
@keyframes slideIn{from{transform:translateX(40px);opacity:0}to{transform:translateX(0);opacity:1}}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:.4}}
.pulsing{animation:pulse 1.5s infinite;}
::-webkit-scrollbar{width:4px;height:4px}
::-webkit-scrollbar-track{background:transparent}
::-webkit-scrollbar-thumb{background:var(--border2);border-radius:2px}
</style>
</head>
<body>
<div class="layout">

<aside class="sidebar">
  <div class="sidebar-logo">
    <div class="brand">ShadowBridge</div>
    <div class="sub">NOX Control Panel</div>
  </div>
  <div class="node-status">
    <div class="label">NOX Lab Node</div>
    <div class="val"><span class="dot unknown" id="nodeDot"></span><span id="nodeLabel" style="font-size:.72rem;color:var(--muted)">Checking…</span></div>
    <div style="font-size:.62rem;color:var(--muted2);margin-top:4px;" id="nodeAge"></div>
  </div>
  <nav>
    <span class="nav-section">Overview</span>
    <a href="#" class="active" data-panel="overview"><span class="ico">⚡</span>Dashboard</a>
    <a href="#" data-panel="machines"><span class="ico">🖥</span>Machines</a>
    <div class="nav-sep"></div>
    <span class="nav-section">Security</span>
    <a href="#" data-panel="sentinel"><span class="ico">🕯</span>SENTINEL</a>
    <a href="#" data-panel="ids"><span class="ico">🛡</span>IDS/Suricata</a>
    <a href="#" data-panel="atlas"><span class="ico">🎯</span>ATLAS</a>
    <a href="#" data-panel="vault"><span class="ico">🔐</span>VAULT</a>
    <a href="#" data-panel="honeyai"><span class="ico">🍯</span>HoneyAI</a>
    <div class="nav-sep"></div>
    <span class="nav-section">Operations</span>
    <a href="#" data-panel="noxbrain"><span class="ico">🤖</span>NOX-BRAIN</a>
    <a href="#" data-panel="command"><span class="ico">⚙</span>NOX-COMMAND</a>
    <a href="#" data-panel="ruview"><span class="ico">📡</span>RuView WiFi</a>
    <div class="nav-sep"></div>
    <span class="nav-section">Communications</span>
    <a href="#" data-panel="webmail"><span class="ico">📧</span>Webmail</a>
  </nav>
  <div class="sidebar-footer">
    <div><?= htmlspecialchars($username) ?> · <?= strtoupper($plan) ?></div>
    <div style="margin-top:4px;"><a href="/logout">Sign out</a></div>
  </div>
</aside>

<div class="main">
  <div class="topbar">
    <div class="page-title" id="pageTitle">Dashboard</div>
    <div class="last-sync"><span class="dot unknown pulsing" id="syncDot"></span><span id="syncLabel">Connecting…</span></div>
    <button class="btn-sm" onclick="fetchStatus()">↺ Refresh</button>
  </div>
  <div class="content">

    <!-- OVERVIEW -->
    <div class="panel active" id="panel-overview">
      <div class="stats-row">
        <div class="stat-box"><div class="val" id="stat-services">—</div><div class="lbl">Services Up</div></div>
        <div class="stat-box"><div class="val" id="stat-machines">—</div><div class="lbl">Machines Online</div></div>
        <div class="stat-box"><div class="val" id="stat-honeypot">—</div><div class="lbl">Honeypot Hits 24h</div></div>
        <div class="stat-box"><div class="val" id="stat-alerts">—</div><div class="lbl">Alerts Today</div><div class="sub" id="stat-ids-sub">IDS: —</div></div>
      </div>
      <div class="overview-grid" id="serviceGrid"></div>
      <div class="event-feed">
        <div class="feed-header"><span class="title">Live Event Feed</span><span id="feedCount" style="font-size:.65rem;color:var(--muted)">—</span></div>
        <div class="feed-body" id="eventFeed"><div style="padding:24px;text-align:center;color:var(--muted);font-size:.72rem;">Waiting for events…</div></div>
      </div>
    </div>

    <!-- MACHINES -->
    <div class="panel" id="panel-machines">
      <div class="machine-grid" id="machineGrid">
        <div class="empty-state"><div class="ico">🖥</div><p>Waiting for NOX Lab data push…<br><br>Start the push daemon on your NOX node.</p></div>
      </div>
    </div>

    <!-- SENTINEL -->
    <div class="panel" id="panel-sentinel">
      <div class="stats-row" style="grid-template-columns:repeat(4,1fr);">
        <div class="stat-box"><div class="val" id="sent-ssh">—</div><div class="lbl">SSH Total</div></div>
        <div class="stat-box"><div class="val" id="sent-ssh24">—</div><div class="lbl">SSH 24h</div></div>
        <div class="stat-box"><div class="val" id="sent-http">—</div><div class="lbl">HTTP Honeypot</div></div>
        <div class="stat-box"><div class="val" id="sent-canary">—</div><div class="lbl">Canary Triggers</div></div>
      </div>
      <div class="term-section">
        <div class="term-bar"><div class="term-dot r"></div><div class="term-dot y"></div><div class="term-dot g"></div><div class="term-title-txt">SENTINEL — Live Honeypot Events</div></div>
        <div class="term-body" id="sentinelLog"><span class="t-muted">Waiting for honeypot data…</span></div>
      </div>
    </div>

    <!-- IDS / SURICATA -->
    <div class="panel" id="panel-ids">
      <div class="stats-row" style="grid-template-columns:repeat(2,1fr);">
        <div class="stat-box"><div class="val" id="ids-total">—</div><div class="lbl">Total Alerts (last 500 events)</div></div>
        <div class="stat-box"><div class="val" id="ids-recent-count">—</div><div class="lbl">Shown in Feed</div></div>
      </div>
      <div class="term-section">
        <div class="term-bar"><div class="term-dot r"></div><div class="term-dot y"></div><div class="term-dot g"></div><div class="term-title-txt">Suricata IDS — Alert Feed</div></div>
        <div class="term-body" style="padding:0;max-height:500px;">
          <table class="ids-table">
            <thead><tr><th>Time</th><th>Src IP</th><th>Port</th><th>Signature</th><th>Sev</th></tr></thead>
            <tbody id="idsBody"><tr><td colspan="5" style="text-align:center;padding:24px;color:var(--muted)">No Suricata alerts in last push window</td></tr></tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ATLAS -->
    <div class="panel" id="panel-atlas">
      <div class="term-section">
        <div class="term-bar"><div class="term-dot r"></div><div class="term-dot y"></div><div class="term-dot g"></div><div class="term-title-txt">ATLAS — MITRE ATT&amp;CK TTP Tracker</div></div>
        <div class="term-body" style="padding:0;">
          <table class="ttp-table">
            <thead><tr><th>TTP ID</th><th>Technique</th><th>Tactic</th><th>Status</th><th>Last seen</th></tr></thead>
            <tbody id="atlasTtps"><tr><td colspan="5" style="text-align:center;padding:24px;color:var(--muted)">No TTPs tracked yet</td></tr></tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- VAULT -->
    <div class="panel" id="panel-vault">
      <div class="stats-row" style="grid-template-columns:repeat(3,1fr);">
        <div class="stat-box"><div class="val" id="vault-total">—</div><div class="lbl">Loot Items</div></div>
        <div class="stat-box"><div class="val" id="vault-cracked">—</div><div class="lbl">Cracked Hashes</div></div>
        <div class="stat-box"><div class="val" id="vault-jobs">—</div><div class="lbl">Active Jobs</div></div>
      </div>
      <div class="term-section">
        <div class="term-bar"><div class="term-dot r"></div><div class="term-dot y"></div><div class="term-dot g"></div><div class="term-title-txt">VAULT — Credential Intelligence</div></div>
        <div class="term-body" id="vaultLog"><span class="t-muted">Loading vault data…</span></div>
      </div>
    </div>

    <!-- HONEYAI -->
    <div class="panel" id="panel-honeyai">
      <div class="stats-row" style="grid-template-columns:repeat(3,1fr);">
        <div class="stat-box"><div class="val" id="hai-sessions">—</div><div class="lbl">Active Sessions</div></div>
        <div class="stat-box"><div class="val" id="hai-ttps">—</div><div class="lbl">TTPs Detected</div></div>
        <div class="stat-box"><div class="val" id="hai-cmds">—</div><div class="lbl">Commands Seen</div></div>
      </div>
      <div class="term-section">
        <div class="term-bar"><div class="term-dot r"></div><div class="term-dot y"></div><div class="term-dot g"></div><div class="term-title-txt">HoneyAI — SSH Attacker Sessions</div></div>
        <div class="term-body" id="honeyaiLog"><span class="t-muted">Loading HoneyAI session data…</span></div>
      </div>
    </div>

    <!-- NOX-BRAIN -->
    <div class="panel" id="panel-noxbrain">
      <div class="chat-wrap">
        <div class="chat-msgs" id="chatMsgs">
          <div class="chat-msg ai"><div class="ai-label">NOX-BRAIN</div>Hello, <?= htmlspecialchars($username) ?>. I'm connected to your lab's live SENTINEL, Villain and IDS data. What do you want to analyze?</div>
        </div>
        <div class="chat-input-row">
          <input type="text" class="chat-input" id="chatInput" placeholder="Ask NOX-BRAIN about your lab…" onkeydown="if(event.key==='Enter')sendChat()">
          <button class="btn-primary" onclick="sendChat()">Send</button>
        </div>
      </div>
    </div>

    <!-- NOX-COMMAND -->
    <div class="panel" id="panel-command">
      <div class="stats-row" style="grid-template-columns:repeat(3,1fr);">
        <div class="stat-box"><div class="val" id="cmd-ops">—</div><div class="lbl">Operations</div></div>
        <div class="stat-box"><div class="val" id="cmd-active">—</div><div class="lbl">Active</div></div>
        <div class="stat-box"><div class="val" id="cmd-phase">—</div><div class="lbl">Current Phase</div></div>
      </div>
      <div class="term-section">
        <div class="term-bar"><div class="term-dot r"></div><div class="term-dot y"></div><div class="term-dot g"></div><div class="term-title-txt">NOX-COMMAND — Kill Chain Orchestrator</div></div>
        <div class="term-body" id="commandLog"><span class="t-muted">Loading operations…</span></div>
      </div>
    </div>

    <!-- RUVIEW WiFi -->
    <div class="panel" id="panel-ruview">
      <div style="margin-bottom:16px;font-size:.7rem;color:var(--muted);">WiFi DensePose — presence &amp; vital sign detection without cameras (port 3002/3003)</div>
      <div class="ruview-grid">
        <div class="ruview-presence">
          <div style="font-size:.65rem;color:var(--muted);text-transform:uppercase;letter-spacing:2px;">Presence Detection</div>
          <div class="ruview-big" id="ruview-presence-val">—</div>
          <div style="font-size:.78rem;color:var(--muted)" id="ruview-count-lbl">People detected: —</div>
          <div style="margin-top:16px;">
            <div style="font-size:.62rem;color:var(--muted2);margin-bottom:8px;">DETECTED LOCATIONS</div>
            <div id="ruview-locations" style="font-size:.72rem;color:var(--cyan2);line-height:2;">—</div>
          </div>
        </div>
        <div>
          <div style="font-size:.65rem;color:var(--muted);text-transform:uppercase;letter-spacing:2px;margin-bottom:12px;">Vital Signs</div>
          <div class="vitals-grid">
            <div class="vital-card">
              <div class="vital-val" id="ruview-hr">—<span class="vital-unit">bpm</span></div>
              <div class="vital-lbl">Heart Rate</div>
            </div>
            <div class="vital-card">
              <div class="vital-val" id="ruview-rr">—<span class="vital-unit">rpm</span></div>
              <div class="vital-lbl">Respiratory Rate</div>
            </div>
          </div>
          <div style="margin-top:16px;padding:12px 14px;background:var(--surface);border:1px solid var(--border);border-radius:6px;">
            <div style="font-size:.62rem;color:var(--muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:6px;">Status</div>
            <div id="ruview-status" style="font-size:.72rem;color:var(--muted)">Waiting for RuView data push…</div>
            <div style="margin-top:6px;font-size:.62rem;color:var(--muted2)" id="ruview-updated"></div>
          </div>
        </div>
      </div>
    </div>

    <!-- WEBMAIL -->
    <div class="panel" id="panel-webmail">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;flex-wrap:wrap;gap:10px;">
        <div>
          <div style="font-size:.8rem;font-weight:700;letter-spacing:.5px;text-transform:uppercase;margin-bottom:4px;">Email Accounts</div>
          <div style="font-size:.7rem;color:var(--muted);">shadowbridge.store · Powered by Zoho Mail</div>
        </div>
        <a href="https://mail.zoho.eu" target="_blank"
           style="display:inline-flex;align-items:center;gap:8px;padding:10px 20px;background:var(--cyan);color:#000;border-radius:6px;text-decoration:none;font-size:.75rem;font-weight:700;letter-spacing:1px;text-transform:uppercase;">
          📧 Open Zoho Mail ↗
        </a>
      </div>
      <div id="mailAccountGrid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:14px;margin-bottom:20px;">
        <div style="padding:24px;text-align:center;color:var(--muted);font-size:.72rem;" class="pulsing">Loading accounts…</div>
      </div>
      <div class="term-section">
        <div class="term-bar"><div class="term-dot r"></div><div class="term-dot y"></div><div class="term-dot g"></div><div class="term-title-txt">Quick Access — Zoho Mail</div></div>
        <div class="term-body" style="display:grid;grid-template-columns:1fr 1fr;gap:12px;padding:14px;">
          <a href="https://mail.zoho.eu" target="_blank"
             style="display:flex;align-items:center;gap:10px;padding:12px 14px;background:var(--surface2);border:1px solid var(--border);border-radius:6px;text-decoration:none;transition:.15s;"
             onmouseover="this.style.borderColor='var(--cyan)'" onmouseout="this.style.borderColor='var(--border)'">
            <span style="font-size:1.2rem;">🔐</span>
            <div><div style="font-size:.75rem;font-weight:700;color:var(--fg)">security@</div><div style="font-size:.65rem;color:var(--muted)">shadowbridge.store</div></div>
            <span style="margin-left:auto;font-size:.65rem;color:var(--cyan)">Open ↗</span>
          </a>
          <a href="https://mail.zoho.eu" target="_blank"
             style="display:flex;align-items:center;gap:10px;padding:12px 14px;background:var(--surface2);border:1px solid var(--border);border-radius:6px;text-decoration:none;transition:.15s;"
             onmouseover="this.style.borderColor='var(--cyan)'" onmouseout="this.style.borderColor='var(--border)'">
            <span style="font-size:1.2rem;">ℹ️</span>
            <div><div style="font-size:.75rem;font-weight:700;color:var(--fg)">info@</div><div style="font-size:.65rem;color:var(--muted)">shadowbridge.store</div></div>
            <span style="margin-left:auto;font-size:.65rem;color:var(--cyan)">Open ↗</span>
          </a>
          <a href="https://mail.zoho.eu" target="_blank"
             style="display:flex;align-items:center;gap:10px;padding:12px 14px;background:var(--surface2);border:1px solid var(--border);border-radius:6px;text-decoration:none;transition:.15s;"
             onmouseover="this.style.borderColor='var(--cyan)'" onmouseout="this.style.borderColor='var(--border)'">
            <span style="font-size:1.2rem;">👥</span>
            <div><div style="font-size:.75rem;font-weight:700;color:var(--fg)">info.team@</div><div style="font-size:.65rem;color:var(--muted)">shadowbridge.store</div></div>
            <span style="margin-left:auto;font-size:.65rem;color:var(--cyan)">Open ↗</span>
          </a>
          <a href="https://mailadmin.zoho.eu" target="_blank"
             style="display:flex;align-items:center;gap:10px;padding:12px 14px;background:var(--surface2);border:1px solid rgba(168,85,247,.3);border-radius:6px;text-decoration:none;transition:.15s;"
             onmouseover="this.style.borderColor='var(--purple)'" onmouseout="this.style.borderColor='rgba(168,85,247,.3)'">
            <span style="font-size:1.2rem;">⚙️</span>
            <div><div style="font-size:.75rem;font-weight:700;color:var(--purple)">Admin Console</div><div style="font-size:.65rem;color:var(--muted)">mailadmin.zoho.eu</div></div>
            <span style="margin-left:auto;font-size:.65rem;color:var(--purple)">Open ↗</span>
          </a>
        </div>
      </div>
    </div>

  </div>
</div>
</div>

<div id="toastContainer"></div>

<script>
const SERVICES = [
  {id:'lab-dashboard', name:'Lab Dashboard', port:8888, color:'cyan',   icon:'⚡', desc:'NOX control plane, WebSocket stats', panel:'overview'},
  {id:'sentinel-agg',  name:'SENTINEL',      port:8282, color:'red',    icon:'🕯', desc:'Honeypot aggregator & alert pipeline', panel:'sentinel'},
  {id:'sentinel-can',  name:'Canary Server', port:8181, color:'red',    icon:'🔔', desc:'HTTP canary token server', panel:'sentinel'},
  {id:'villain-api',   name:'Villain-API',   port:8383, color:'purple', icon:'👾', desc:'C2 bridge — Villain framework', panel:null},
  {id:'nox-brain',     name:'NOX-BRAIN',     port:8484, color:'cyan',   icon:'🤖', desc:'AI analysis layer (Ollama)', panel:'noxbrain'},
  {id:'vault',         name:'VAULT',         port:8585, color:'orange', icon:'🔐', desc:'Credential intel — John/hashcat', panel:'vault'},
  {id:'atlas',         name:'ATLAS',         port:8686, color:'purple', icon:'🎯', desc:'MITRE ATT&CK TTP tracker', panel:'atlas'},
  {id:'nox-command',   name:'NOX-COMMAND',   port:8787, color:'green',  icon:'⚙', desc:'Kill chain orchestrator', panel:'command'},
  {id:'honeyai',       name:'HoneyAI',       port:8191, color:'orange', icon:'🍯', desc:'AI SSH honeypot response gen', panel:'honeyai'},
];

let lastEventCount = 0;
let lastAlertsToday = 0;

// ── Toast ──────────────────────────────────────────────────────────────────
function toast(msg, type='info', ico='ℹ️') {
  const c = document.getElementById('toastContainer');
  const el = document.createElement('div');
  el.className = 'toast ' + type;
  el.innerHTML = `<span class="toast-ico">${ico}</span><span class="toast-msg">${msg}</span><span class="toast-close" onclick="this.parentElement.remove()">✕</span>`;
  c.appendChild(el);
  setTimeout(() => el.remove(), 5000);
}

// ── Main fetch ─────────────────────────────────────────────────────────────
async function fetchStatus() {
  try {
    const r = await fetch('/api/status.php?node=nox', {credentials:'include'});
    const d = await r.json();
    if (!d.ok) return;
    updateNode(d);
    if (d.data) { renderAll(d.data); }
    fetchEvents();
  } catch(e) { setOffline(); }
}

function updateNode(d) {
  const dot = document.getElementById('nodeDot');
  const lbl = document.getElementById('nodeLabel');
  const age = document.getElementById('nodeAge');
  const sd  = document.getElementById('syncDot');
  const sl  = document.getElementById('syncLabel');
  if (!d.connected) {
    dot.className='dot offline'; lbl.textContent='Offline / No push';
    sd.className='dot offline'; sl.textContent='Not connected';
    age.textContent='';
  } else {
    dot.className='dot online'; lbl.textContent='NOX · Online';
    sd.className='dot online'; sl.textContent='Live · '+new Date(d.last_push).toLocaleTimeString();
    age.textContent=(d.age_sec||0)+'s ago';
  }
}

function setOffline() {
  document.getElementById('nodeDot').className='dot unknown';
  document.getElementById('nodeLabel').textContent='No data yet';
  document.getElementById('syncDot').className='dot unknown pulsing';
  document.getElementById('syncLabel').textContent='Awaiting first push…';
}

function renderAll(data) {
  const svcs = data.services || {};
  renderServices(svcs);
  renderMachines(data.machines || []);
  renderSentinel(data.sentinel || {});
  renderAtlas(data.atlas || {});
  renderVault(data.vault || {});
  renderHoneyAI(data.honeyai || {});
  renderCommand(data.command || {});
  renderRuView(data.ruview || {});
  renderIDS(data.suricata || {});

  const arr = Object.values(svcs);
  document.getElementById('stat-services').textContent = arr.filter(s=>s.up).length+'/'+arr.length;
  document.getElementById('stat-machines').textContent = (data.machines||[]).filter(m=>m.online).length;
  document.getElementById('stat-honeypot').textContent = data.sentinel?.ssh_24h ?? '—';

  const alertsToday = data.sentinel?.alerts_today ?? 0;
  const idstotal    = data.suricata?.total ?? 0;
  document.getElementById('stat-alerts').textContent   = alertsToday;
  document.getElementById('stat-ids-sub').textContent  = 'IDS: ' + idstotal;

  // Toast on new IDS alerts
  if (idstotal > lastAlertsToday && lastAlertsToday > 0)
    toast(`${idstotal - lastAlertsToday} new Suricata alert(s)`, 'alert', '🛡');
  lastAlertsToday = idstotal;

  // Nav badge for IDS
  const idsBadge = document.querySelector('nav a[data-panel="ids"] .nav-badge');
  if (idstotal > 0) {
    if (!idsBadge) {
      const a = document.querySelector('nav a[data-panel="ids"]');
      const b = document.createElement('span');
      b.className = 'nav-badge'; b.textContent = idstotal > 99 ? '99+' : idstotal;
      a.appendChild(b);
    } else { idsBadge.textContent = idstotal > 99 ? '99+' : idstotal; }
  }
}

function renderServices(svcs) {
  document.getElementById('serviceGrid').innerHTML = SERVICES.map(s => {
    const info = svcs[s.id] || svcs[s.port] || null;
    const up   = info?.up;
    const badge = up==null ? `<span class="badge warn">Unknown</span>`
                : up ? `<span class="badge up">● UP</span>`
                     : `<span class="badge down">● DOWN</span>`;
    const click = s.panel ? `onclick="navTo('${s.panel}')"` : '';
    return `<div class="svc-card ${s.color}" ${click}>
      <div class="svc-head"><span class="svc-name">${s.icon} ${s.name}</span><span class="svc-port">:${s.port}</span></div>
      <div class="svc-desc">${s.desc}</div>
      <div class="svc-status">${badge}${info?.uptime?` <span style="font-size:.65rem;color:var(--muted)">${info.uptime}</span>`:''}</div>
    </div>`;
  }).join('');
}

function renderMachines(machines) {
  if (!machines.length) return;
  document.getElementById('machineGrid').innerHTML = machines.map(m => {
    const cpu=m.cpu_pct??0, mem=m.mem_pct??0, disk=m.disk_pct??0;
    const cpuColor = cpu>80?'var(--red)':cpu>60?'var(--orange)':'var(--cyan)';
    const memColor = mem>80?'var(--red)':mem>60?'var(--orange)':'var(--purple)';
    return `<div class="machine-card">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px;">
        <span class="mname">${m.name||m.id}</span><span class="dot ${m.online?'online':'offline'}"></span>
      </div>
      <div class="mip">${m.ip||''}${m.tailscale_ip?' · '+m.tailscale_ip:''}</div>
      <div class="mstats">
        <div class="mstat"><div class="v" style="color:${cpuColor}">${cpu}%</div><div class="l">CPU</div><div class="progress"><div class="progress-bar cpu" style="width:${cpu}%;background:${cpuColor}"></div></div></div>
        <div class="mstat"><div class="v" style="color:${memColor}">${mem}%</div><div class="l">MEM</div><div class="progress"><div class="progress-bar mem" style="width:${mem}%;background:${memColor}"></div></div></div>
        <div class="mstat"><div class="v">${disk}%</div><div class="l">DISK</div><div class="progress"><div class="progress-bar disk" style="width:${disk}%"></div></div></div>
      </div>
    </div>`;
  }).join('');
}

function renderSentinel(s) {
  document.getElementById('sent-ssh').textContent    = s.ssh_total??'—';
  document.getElementById('sent-ssh24').textContent  = s.ssh_24h??'—';
  document.getElementById('sent-http').textContent   = s.http_total??'—';
  document.getElementById('sent-canary').textContent = s.canary_total??'—';
  if (s.recent_events?.length)
    document.getElementById('sentinelLog').innerHTML = s.recent_events.map(e=>{
      const sev = e.severity==='high'||e.severity===1 ? 't-red' : 't-orange';
      return `<div><span class="t-muted">[${e.ts||''}]</span> <span class="${sev}">${e.src_ip||''}</span> <span class="t-muted">→</span> ${e.message||''}</div>`;
    }).join('');
}

function renderIDS(s) {
  const total = s.total ?? 0;
  const recent = s.recent ?? [];
  document.getElementById('ids-total').textContent       = total || '—';
  document.getElementById('ids-recent-count').textContent = recent.length || '—';
  if (!recent.length) return;
  document.getElementById('idsBody').innerHTML = recent.map(a => {
    const sev = a.severity<=1 ? 's1' : a.severity<=2 ? 's2' : 's3';
    const sevLabel = a.severity<=1 ? 'HIGH' : a.severity<=2 ? 'MED' : 'LOW';
    const ts = (a.ts||'').replace('T',' ').slice(0,16);
    return `<tr>
      <td style="color:var(--muted);white-space:nowrap">${ts}</td>
      <td style="color:var(--cyan)">${a.src_ip||'—'}</td>
      <td style="color:var(--muted)">${a.dest_port||'—'}</td>
      <td style="max-width:300px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${a.signature||a.category||'—'}</td>
      <td><span class="ids-sev ${sev}">${sevLabel}</span></td>
    </tr>`;
  }).join('');
}

function renderAtlas(a) {
  const ttps = a.ttps||[];
  if (!ttps.length) return;
  document.getElementById('atlasTtps').innerHTML = ttps.map(t =>
    `<tr><td style="color:var(--cyan);font-weight:700">${t.id||''}</td><td>${t.name||''}</td><td style="color:var(--muted)">${t.tactic||''}</td>
     <td><span class="ttp-status ${{red:'red',blue:'blue',purple:'purple'}[t.status]||'blue'}">${t.status||''}</span></td>
     <td style="color:var(--muted)">${t.last_seen||''}</td></tr>`
  ).join('');
}

function renderVault(v) {
  document.getElementById('vault-total').textContent   = v.loot_count??'—';
  document.getElementById('vault-cracked').textContent = v.cracked_count??'—';
  document.getElementById('vault-jobs').textContent    = v.active_jobs??'—';
  if (v.recent_loot?.length)
    document.getElementById('vaultLog').innerHTML = v.recent_loot.map(l=>
      `<div><span class="t-cyan">[${l.type||'cred'}]</span> <span class="t-muted">${l.host||''}</span> ${l.username||''}${l.password?' → <span class="t-orange">'+l.password+'</span>':''}</div>`
    ).join('');
}

function renderHoneyAI(h) {
  document.getElementById('hai-sessions').textContent = h.active_sessions??'—';
  document.getElementById('hai-ttps').textContent     = h.ttps_total??'—';
  document.getElementById('hai-cmds').textContent     = h.commands_total??'—';
  if (h.sessions?.length)
    document.getElementById('honeyaiLog').innerHTML = h.sessions.map(s=>
      `<div><span class="t-cyan">[${s.ip||'?'}]</span> <span class="t-muted">TTPs: ${(s.ttps_detected||s.ttps||[]).join(', ')||'none'}</span> cmds:${s.command_count||0}</div>`
    ).join('');
}

function renderCommand(c) {
  document.getElementById('cmd-ops').textContent    = c.total_ops??'—';
  document.getElementById('cmd-active').textContent = c.active_ops??'—';
  document.getElementById('cmd-phase').textContent  = c.current_phase??'—';
  if (c.recent_ops?.length)
    document.getElementById('commandLog').innerHTML = c.recent_ops.map(op=>
      `<div><span class="t-cyan">[${op.phase||'?'}]</span> <span class="t-muted">${op.target||''}</span> → <span class="${op.status==='active'?'t-orange':'t-muted'}">${op.status||''}</span></div>`
    ).join('');
}

function renderRuView(r) {
  if (!r || !r.online) {
    document.getElementById('ruview-status').textContent = 'RuView offline (port 3002/3003 unreachable)';
    return;
  }
  const presence = r.presence || r.count > 0;
  const pEl = document.getElementById('ruview-presence-val');
  pEl.textContent = presence ? '● PRESENT' : '○ EMPTY';
  pEl.className   = 'ruview-big ' + (presence ? 'present' : 'absent');
  document.getElementById('ruview-count-lbl').textContent = `People detected: ${r.count ?? 0}`;
  const locs = r.locations || [];
  document.getElementById('ruview-locations').innerHTML = locs.length
    ? locs.map((l,i)=>`Person ${i+1}: x=${l.x??'?'} y=${l.y??'?'}`).join('<br>')
    : '—';
  const hrEl = document.getElementById('ruview-hr');
  const rrEl = document.getElementById('ruview-rr');
  hrEl.innerHTML = r.hr != null ? `${r.hr}<span class="vital-unit">bpm</span>` : `—<span class="vital-unit">bpm</span>`;
  rrEl.innerHTML = r.rr != null ? `${r.rr}<span class="vital-unit">rpm</span>` : `—<span class="vital-unit">rpm</span>`;
  document.getElementById('ruview-status').textContent = presence ? '🟢 Motion / Presence detected' : '⚫ Room appears empty';
  document.getElementById('ruview-updated').textContent = r.updated ? 'Updated: ' + r.updated.replace('T',' ').slice(0,19) : '';
}

async function fetchEvents() {
  try {
    const r = await fetch('/api/events.php?node=nox&limit=30', {credentials:'include'});
    const d = await r.json();
    if (!d.ok||!d.events?.length) return;
    // Toast on new events
    if (d.events.length > lastEventCount && lastEventCount > 0) {
      const n = d.events.length - lastEventCount;
      toast(`${n} new lab event(s)`, 'info', '⚡');
    }
    lastEventCount = d.events.length;
    document.getElementById('feedCount').textContent = d.events.length+' events';
    document.getElementById('eventFeed').innerHTML = d.events.map(e=>{
      const p=e.data||{};
      const msg=p.message||p.cmd||p.technique||JSON.stringify(p).slice(0,80);
      const tc={sentinel:'sentinel',atlas:'atlas',system:'system',honeyai:'honeyai',ids:'ids'}[e.type]||'system';
      return `<div class="feed-item"><span class="feed-type ${tc}">${e.type}</span><span class="feed-msg">${msg}</span><span class="feed-time">${new Date(e.at).toLocaleTimeString()}</span></div>`;
    }).join('');
  } catch(e){}
}

async function sendChat() {
  const input = document.getElementById('chatInput');
  const msg = input.value.trim();
  if (!msg) return;
  input.value = '';
  const msgs = document.getElementById('chatMsgs');
  msgs.innerHTML += `<div class="chat-msg user">${msg}</div>`;
  msgs.innerHTML += `<div class="chat-msg ai" id="aiTyping"><div class="ai-label">NOX-BRAIN</div><span class="pulsing">Thinking…</span></div>`;
  msgs.scrollTop = msgs.scrollHeight;
  try {
    const r = await fetch('/api/noxbrain.php', {method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({message:msg,session_id:'sb_'+Date.now()}),credentials:'include'});
    const d = await r.json();
    document.getElementById('aiTyping').innerHTML = `<div class="ai-label">NOX-BRAIN</div>${d.response||d.reply||'No response'}`;
  } catch(e) {
    document.getElementById('aiTyping').innerHTML = `<div class="ai-label">NOX-BRAIN</div><span style="color:var(--red)">NOX-BRAIN offline — check port 8484</span>`;
  }
  msgs.scrollTop = msgs.scrollHeight;
}

async function fetchMailAccounts() {
  try {
    const r = await fetch('/api/mail.php', {credentials:'include'});
    const d = await r.json();
    if (!d.ok || !d.accounts?.length) return;
    const grid = document.getElementById('mailAccountGrid');
    grid.innerHTML = d.accounts.map(a => {
      const pct = a.disk_pct || 0;
      const barColor = pct > 80 ? 'var(--red)' : pct > 50 ? 'var(--orange)' : 'var(--cyan2)';
      const lastLogin = a.last_login ? `Last activity: ${a.last_login}` : 'Zoho Mail — no activity data';
      return `<div style="background:var(--surface);border:1px solid var(--border);border-radius:8px;padding:16px;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
          <span style="font-size:.78rem;font-weight:700;color:var(--cyan)">${a.email}</span>
          ${a.suspended ? '<span style="font-size:.62rem;color:var(--red);border:1px solid var(--red);padding:1px 6px;border-radius:3px;">SUSPENDED</span>' : '<span style="font-size:.62rem;color:var(--cyan2);border:1px solid rgba(0,255,157,.3);padding:1px 6px;border-radius:3px;">ACTIVE</span>'}
        </div>
        <div style="font-size:.68rem;color:var(--muted);margin-bottom:8px;">${lastLogin}</div>
        <div style="display:flex;justify-content:space-between;font-size:.68rem;color:var(--muted);margin-bottom:4px;">
          <span>Quota</span><span>${a.disk_quota}</span>
        </div>
        <div style="margin-top:10px;">
          <a href="https://mail.zoho.eu" target="_blank"
             style="font-size:.68rem;color:var(--cyan);text-decoration:none;">Open in Zoho Mail ↗</a>
        </div>
      </div>`;
    }).join('');
  } catch(e) {}
}

// ── Nav routing ──────────────────────────────────────────────────────────
function navTo(panelId) {
  const link = document.querySelector(`nav a[data-panel="${panelId}"]`);
  if (link) link.click();
}

document.querySelectorAll('nav a[data-panel]').forEach(a => {
  a.addEventListener('click', e => {
    e.preventDefault();
    document.querySelectorAll('nav a').forEach(x=>x.classList.remove('active'));
    document.querySelectorAll('.panel').forEach(p=>p.classList.remove('active'));
    a.classList.add('active');
    document.getElementById('panel-'+a.dataset.panel).classList.add('active');
    document.getElementById('pageTitle').textContent = a.querySelector('span:last-of-type')?.textContent || a.textContent.trim();
    if (a.dataset.panel === 'webmail') fetchMailAccounts();
  });
});

setOffline();
fetchStatus();
setInterval(fetchStatus, 30000);
</script>
</body>
</html>
