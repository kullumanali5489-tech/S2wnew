<?php
session_start();
require_once __DIR__ . '/config.php';
$authed = !empty($_SESSION['auth']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title><?= APP_NAME ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet"/>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
<script src="https://cdnjs.cloudflare.com/ajax/libs/hls.js/1.5.7/hls.min.js"></script>
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

:root {
    --bg:       #0a0a0f;
    --surface:  #111118;
    --border:   #1e1e2e;
    --accent:   #6c63ff;
    --accent2:  #ff6584;
    --green:    #00e5a0;
    --text:     #e8e8f0;
    --muted:    #6b6b80;
    --font:     'Syne', sans-serif;
    --mono:     'DM Mono', monospace;
    --radius:   12px;
    --glow:     0 0 40px rgba(108,99,255,0.15);
}

html, body { height: 100%; }

body {
    background: var(--bg);
    color: var(--text);
    font-family: var(--font);
    min-height: 100vh;
    overflow-x: hidden;
}

/* ── Animated background grid ─────────────────────── */
body::before {
    content: '';
    position: fixed; inset: 0; z-index: 0;
    background-image:
        linear-gradient(rgba(108,99,255,0.03) 1px, transparent 1px),
        linear-gradient(90deg, rgba(108,99,255,0.03) 1px, transparent 1px);
    background-size: 40px 40px;
    pointer-events: none;
}

body::after {
    content: '';
    position: fixed;
    width: 600px; height: 600px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(108,99,255,0.08) 0%, transparent 70%);
    top: -200px; right: -200px;
    pointer-events: none;
    z-index: 0;
}

/* ── Login screen ─────────────────────────────────── */
.login-wrap {
    position: relative; z-index: 1;
    display: flex; align-items: center; justify-content: center;
    min-height: 100vh;
}

.login-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 20px;
    padding: 48px 40px;
    width: 360px;
    box-shadow: var(--glow), 0 40px 80px rgba(0,0,0,0.5);
    animation: slideUp 0.5s cubic-bezier(0.34, 1.56, 0.64, 1) both;
}

@keyframes slideUp {
    from { opacity: 0; transform: translateY(30px) scale(0.96); }
    to   { opacity: 1; transform: translateY(0)     scale(1); }
}

.login-logo {
    display: flex; align-items: center; gap: 10px;
    margin-bottom: 32px;
}

.login-logo .icon {
    width: 40px; height: 40px;
    background: linear-gradient(135deg, var(--accent), var(--accent2));
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 18px; color: #fff;
}

.login-logo .name {
    font-size: 22px; font-weight: 800;
    background: linear-gradient(135deg, #fff 0%, var(--accent) 100%);
    -webkit-background-clip: text; -webkit-text-fill-color: transparent;
}

.login-card h2 {
    font-size: 14px; font-weight: 600;
    color: var(--muted); letter-spacing: 0.1em;
    text-transform: uppercase; margin-bottom: 24px;
}

.field { margin-bottom: 16px; }

.field label {
    display: block; font-size: 12px;
    font-family: var(--mono); color: var(--muted);
    margin-bottom: 6px; letter-spacing: 0.05em;
}

.field input {
    width: 100%;
    background: var(--bg);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 12px 16px;
    color: var(--text);
    font-family: var(--mono);
    font-size: 15px;
    outline: none;
    transition: border-color 0.2s, box-shadow 0.2s;
}

.field input:focus {
    border-color: var(--accent);
    box-shadow: 0 0 0 3px rgba(108,99,255,0.15);
}

.btn {
    display: inline-flex; align-items: center; justify-content: center; gap: 8px;
    padding: 12px 24px;
    border-radius: var(--radius);
    font-family: var(--font); font-weight: 700; font-size: 14px;
    border: none; cursor: pointer; transition: all 0.2s;
    white-space: nowrap;
}

.btn-primary {
    background: linear-gradient(135deg, var(--accent), #8b7fff);
    color: #fff;
    width: 100%;
    padding: 14px;
    font-size: 15px;
}
.btn-primary:hover { transform: translateY(-1px); box-shadow: 0 8px 25px rgba(108,99,255,0.4); }
.btn-primary:active { transform: translateY(0); }

.btn-ghost {
    background: transparent;
    border: 1px solid var(--border);
    color: var(--muted);
}
.btn-ghost:hover { border-color: var(--accent); color: var(--text); }

.btn-danger {
    background: rgba(255,101,132,0.1);
    border: 1px solid rgba(255,101,132,0.3);
    color: var(--accent2);
}
.btn-danger:hover { background: rgba(255,101,132,0.2); }

.btn-green {
    background: rgba(0,229,160,0.1);
    border: 1px solid rgba(0,229,160,0.3);
    color: var(--green);
}
.btn-green:hover { background: rgba(0,229,160,0.2); }

.btn:disabled { opacity: 0.5; cursor: not-allowed; transform: none !important; }

/* ── Dashboard ────────────────────────────────────── */
.shell {
    position: relative; z-index: 1;
    display: flex; min-height: 100vh;
}

/* Sidebar */
.sidebar {
    width: 220px; flex-shrink: 0;
    background: var(--surface);
    border-right: 1px solid var(--border);
    display: flex; flex-direction: column;
    padding: 24px 0;
    position: fixed; top: 0; bottom: 0; left: 0;
    z-index: 10;
}

.sidebar-logo {
    display: flex; align-items: center; gap: 10px;
    padding: 0 20px 24px;
    border-bottom: 1px solid var(--border);
}

.sidebar-logo .icon {
    width: 32px; height: 32px;
    background: linear-gradient(135deg, var(--accent), var(--accent2));
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    font-size: 14px; color: #fff; flex-shrink: 0;
}

.sidebar-logo .name {
    font-size: 16px; font-weight: 800;
    background: linear-gradient(135deg, #fff 0%, var(--accent) 100%);
    -webkit-background-clip: text; -webkit-text-fill-color: transparent;
}

.nav { flex: 1; padding: 20px 12px; display: flex; flex-direction: column; gap: 4px; }

.nav-item {
    display: flex; align-items: center; gap: 10px;
    padding: 10px 12px;
    border-radius: 8px;
    font-size: 14px; font-weight: 600;
    color: var(--muted);
    cursor: pointer; transition: all 0.15s;
    border: none; background: none; width: 100%; text-align: left;
}
.nav-item:hover { background: rgba(108,99,255,0.08); color: var(--text); }
.nav-item.active { background: rgba(108,99,255,0.15); color: var(--accent); }
.nav-item i { width: 16px; text-align: center; }

.sidebar-footer {
    padding: 16px 12px;
    border-top: 1px solid var(--border);
}

/* Main area */
.main {
    margin-left: 220px;
    flex: 1;
    padding: 32px;
    min-height: 100vh;
}

/* Topbar */
.topbar {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 32px;
}

.topbar h1 {
    font-size: 24px; font-weight: 800;
}

.topbar-actions { display: flex; gap: 10px; }

/* Stat cards */
.stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 16px;
    margin-bottom: 28px;
}

.stat-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 20px;
    position: relative; overflow: hidden;
    transition: border-color 0.2s;
}
.stat-card:hover { border-color: rgba(108,99,255,0.3); }

.stat-card::before {
    content: '';
    position: absolute; top: 0; left: 0; right: 0; height: 2px;
    background: linear-gradient(90deg, var(--accent), var(--accent2));
    opacity: 0;
    transition: opacity 0.2s;
}
.stat-card:hover::before { opacity: 1; }

.stat-label {
    font-size: 11px; font-family: var(--mono);
    color: var(--muted); text-transform: uppercase; letter-spacing: 0.08em;
    margin-bottom: 8px;
}
.stat-value {
    font-size: 28px; font-weight: 800;
    line-height: 1;
}
.stat-value.green { color: var(--green); }
.stat-value.accent { color: var(--accent); }

/* Panel / card */
.panel {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    margin-bottom: 20px;
    overflow: hidden;
}

.panel-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 18px 24px;
    border-bottom: 1px solid var(--border);
}

.panel-title {
    font-size: 14px; font-weight: 700;
    display: flex; align-items: center; gap: 8px;
    color: var(--text);
}
.panel-title i { color: var(--accent); }

.panel-body { padding: 24px; }

/* Form grid */
.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
@media (max-width: 600px) { .form-grid { grid-template-columns: 1fr; } }

.form-actions {
    display: flex; gap: 10px; flex-wrap: wrap;
    margin-top: 20px;
}

/* Badge */
.badge {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 3px 10px; border-radius: 999px;
    font-size: 11px; font-family: var(--mono); font-weight: 500;
}
.badge-on  { background: rgba(0,229,160,0.1); color: var(--green); border: 1px solid rgba(0,229,160,0.3); }
.badge-off { background: rgba(255,101,132,0.1); color: var(--accent2); border: 1px solid rgba(255,101,132,0.3); }

/* Tab system */
.tab-content { display: none; }
.tab-content.active { display: block; }

/* Channel list */
.ch-list {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
    gap: 12px;
    max-height: 480px;
    overflow-y: auto;
    padding-right: 4px;
}
.ch-list::-webkit-scrollbar { width: 4px; }
.ch-list::-webkit-scrollbar-track { background: transparent; }
.ch-list::-webkit-scrollbar-thumb { background: var(--border); border-radius: 2px; }

.ch-card {
    background: var(--bg);
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 12px;
    display: flex; flex-direction: column; align-items: center;
    gap: 8px; text-align: center;
    cursor: pointer; transition: all 0.15s;
}
.ch-card:hover { border-color: var(--accent); transform: translateY(-2px); }
.ch-card img {
    width: 48px; height: 48px; object-fit: contain;
    border-radius: 6px;
    background: rgba(255,255,255,0.05);
}
.ch-card .ch-name {
    font-size: 11px; font-weight: 600;
    color: var(--muted); line-height: 1.3;
    max-width: 100%; overflow: hidden;
    display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;
}

/* Toast */
.toast-wrap {
    position: fixed; bottom: 24px; right: 24px; z-index: 999;
    display: flex; flex-direction: column; gap: 10px;
    pointer-events: none;
}
.toast {
    background: var(--surface);
    border: 1px solid var(--border);
    border-left: 3px solid var(--accent);
    border-radius: 10px;
    padding: 12px 16px;
    font-size: 13px; font-weight: 600;
    min-width: 240px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.4);
    animation: toastIn 0.3s cubic-bezier(0.34, 1.56, 0.64, 1) both;
    pointer-events: auto;
}
.toast.error { border-left-color: var(--accent2); }
.toast.success { border-left-color: var(--green); }
@keyframes toastIn {
    from { opacity: 0; transform: translateX(20px); }
    to   { opacity: 1; transform: translateX(0); }
}

/* Logs */
.log-box {
    background: var(--bg); border: 1px solid var(--border);
    border-radius: 8px; padding: 16px;
    font-family: var(--mono); font-size: 12px;
    color: var(--muted); line-height: 1.7;
    max-height: 400px; overflow-y: auto;
    white-space: pre-wrap; word-break: break-all;
}

/* Spinner */
.spinner {
    display: inline-block;
    width: 14px; height: 14px;
    border: 2px solid rgba(255,255,255,0.2);
    border-top-color: #fff;
    border-radius: 50%;
    animation: spin 0.6s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* Responsive */
@media (max-width: 768px) {
    .sidebar { width: 60px; }
    .sidebar-logo .name,
    .nav-item span,
    .sidebar-footer .btn span { display: none; }
    .sidebar-logo { padding: 0 14px 24px; }
    .nav-item { justify-content: center; }
    .main { margin-left: 60px; padding: 20px 16px; }
    .topbar h1 { font-size: 18px; }
}

/* ── Player Modal ─────────────────────────────────── */
.player-overlay {
    position: fixed; inset: 0; z-index: 100;
    background: rgba(0,0,0,0.92);
    display: none; align-items: center; justify-content: center;
    backdrop-filter: blur(8px);
}
.player-overlay.open { display: flex; }

.player-wrap {
    position: relative;
    width: min(900px, 96vw);
    background: #000;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 40px 100px rgba(0,0,0,0.8);
    animation: playerIn 0.3s cubic-bezier(0.34,1.56,0.64,1) both;
}
@keyframes playerIn {
    from { opacity:0; transform: scale(0.92); }
    to   { opacity:1; transform: scale(1); }
}

.player-topbar {
    position: absolute; top: 0; left: 0; right: 0; z-index: 10;
    display: flex; align-items: center; justify-content: space-between;
    padding: 14px 18px;
    background: linear-gradient(to bottom, rgba(0,0,0,0.85) 0%, transparent 100%);
}
.player-title {
    font-size: 14px; font-weight: 700;
    color: #fff; text-shadow: 0 1px 4px rgba(0,0,0,0.8);
    display: flex; align-items: center; gap: 8px;
}
.player-title .dot {
    width: 8px; height: 8px; border-radius: 50%;
    background: #ff4444;
    box-shadow: 0 0 8px #ff4444;
    animation: blink 1s ease-in-out infinite;
}
@keyframes blink { 0%,100%{opacity:1} 50%{opacity:0.3} }

.player-close {
    width: 32px; height: 32px; border-radius: 50%;
    background: rgba(255,255,255,0.15);
    border: none; color: #fff; font-size: 16px;
    cursor: pointer; display: flex; align-items: center; justify-content: center;
    transition: background 0.15s;
}
.player-close:hover { background: rgba(255,60,60,0.6); }

.player-video-wrap {
    position: relative;
    width: 100%; padding-top: 56.25%; /* 16:9 */
    background: #000;
}
.player-video-wrap video {
    position: absolute; inset: 0; width: 100%; height: 100%;
}

.player-loading {
    position: absolute; inset: 0;
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    gap: 16px; background: #000;
    color: var(--muted); font-size: 13px;
    z-index: 5;
}
.player-loading .big-spinner {
    width: 44px; height: 44px;
    border: 3px solid rgba(108,99,255,0.2);
    border-top-color: var(--accent);
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}

.player-error {
    position: absolute; inset: 0;
    display: none; flex-direction: column; align-items: center; justify-content: center;
    gap: 12px; background: #0a0005;
    color: var(--accent2); font-size: 14px; font-weight: 600;
    z-index: 5; text-align: center; padding: 24px;
}
.player-error i { font-size: 40px; opacity: 0.6; }

.player-info {
    padding: 14px 18px;
    background: #0d0d14;
    display: flex; align-items: center; justify-content: space-between;
    gap: 12px;
}
.player-info-name { font-size: 13px; font-weight: 700; }
.player-info-url  { font-size: 11px; font-family: var(--mono); color: var(--muted); word-break: break-all; flex: 1; margin: 0 12px; }
.btn-copy {
    background: rgba(108,99,255,0.15); border: 1px solid rgba(108,99,255,0.3);
    color: var(--accent); border-radius: 6px; padding: 5px 10px;
    font-size: 11px; font-family: var(--mono); cursor: pointer;
    white-space: nowrap; flex-shrink: 0;
    transition: background 0.15s;
}
.btn-copy:hover { background: rgba(108,99,255,0.3); }

</style>
</head>
<body>

<?php if (!$authed): ?>
<!-- ── LOGIN ──────────────────────────────────────────────────────────────── -->
<div class="login-wrap">
<div class="login-card">
    <div class="login-logo">
        <div class="icon"><i class="fa-solid fa-satellite-dish"></i></div>
        <div class="name"><?= APP_NAME ?></div>
    </div>
    <h2>Enter PIN to continue</h2>
    <div class="field">
        <label>Access PIN</label>
        <input type="password" id="pin" maxlength="4" placeholder="••••" autocomplete="off" inputmode="numeric"/>
    </div>
    <button class="btn btn-primary" id="loginBtn" onclick="doLogin()">
        <i class="fa-solid fa-arrow-right-to-bracket"></i> Login
    </button>
</div>
</div>

<?php else: ?>
<!-- ── DASHBOARD ──────────────────────────────────────────────────────────── -->
<div class="shell">
<aside class="sidebar">
    <div class="sidebar-logo">
        <div class="icon"><i class="fa-solid fa-satellite-dish"></i></div>
        <div class="name"><?= APP_NAME ?></div>
    </div>
    <nav class="nav">
        <button class="nav-item active" onclick="showTab('tab-overview', this)">
            <i class="fa-solid fa-gauge-high"></i><span>Overview</span>
        </button>
        <button class="nav-item" onclick="showTab('tab-portal', this)">
            <i class="fa-solid fa-plug"></i><span>Portal</span>
        </button>
        <button class="nav-item" onclick="showTab('tab-channels', this); loadChannels()">
            <i class="fa-solid fa-tv"></i><span>Channels</span>
        </button>
        <button class="nav-item" onclick="showTab('tab-settings', this)">
            <i class="fa-solid fa-sliders"></i><span>Settings</span>
        </button>
        <button class="nav-item" onclick="showTab('tab-logs', this); loadLogs()">
            <i class="fa-solid fa-scroll"></i><span>Logs</span>
        </button>
    </nav>
    <div class="sidebar-footer">
        <button class="btn btn-ghost" style="width:100%" onclick="doLogout()">
            <i class="fa-solid fa-right-from-bracket"></i><span>Logout</span>
        </button>
    </div>
</aside>

<main class="main">

    <!-- Overview -->
    <div id="tab-overview" class="tab-content active">
        <div class="topbar">
            <h1>Overview</h1>
            <div class="topbar-actions">
                <button class="btn btn-ghost" onclick="loadDashboard()">
                    <i class="fa-solid fa-arrows-rotate"></i> Refresh
                </button>
            </div>
        </div>
        <div class="stats">
            <div class="stat-card">
                <div class="stat-label">Channels</div>
                <div class="stat-value accent" id="stat-channels">—</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Expiry</div>
                <div class="stat-value" style="font-size:16px;margin-top:4px" id="stat-expiry">—</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Stream Proxy</div>
                <div id="stat-proxy" style="margin-top:6px">—</div>
            </div>
        </div>
        <div class="panel" id="overview-portal-panel">
            <div class="panel-header">
                <span class="panel-title"><i class="fa-solid fa-circle-info"></i> Portal Status</span>
                <div style="display:flex;gap:8px">
                    <button class="btn btn-green" onclick="refreshPortal()" id="refreshBtn">
                        <i class="fa-solid fa-rotate"></i> Sync Portal
                    </button>
                    <button class="btn btn-ghost" onclick="exportM3U()">
                        <i class="fa-solid fa-file-export"></i> Export M3U
                    </button>
                </div>
            </div>
            <div class="panel-body">
                <div id="overview-info" style="font-family:var(--mono);font-size:13px;color:var(--muted)">
                    Loading…
                </div>
            </div>
        </div>
    </div>

    <!-- Portal config -->
    <div id="tab-portal" class="tab-content">
        <div class="topbar"><h1>Portal Configuration</h1></div>
        <div class="panel">
            <div class="panel-header">
                <span class="panel-title"><i class="fa-solid fa-plug"></i> MAC / Stalker Portal</span>
            </div>
            <div class="panel-body">
                <div class="form-grid">
                    <div class="field" style="grid-column:1/-1">
                        <label>Server URL *</label>
                        <input type="text" id="f-server_url" placeholder="http://portal.example.com/c/"/>
                    </div>
                    <div class="field">
                        <label>MAC Address *</label>
                        <input type="text" id="f-mac_id" placeholder="00:1A:79:XX:XX:XX"/>
                    </div>
                    <div class="field">
                        <label>Serial Number</label>
                        <input type="text" id="f-serial" placeholder="Optional"/>
                    </div>
                    <div class="field">
                        <label>Device ID 1</label>
                        <input type="text" id="f-device_id1" placeholder="Optional"/>
                    </div>
                    <div class="field">
                        <label>Device ID 2</label>
                        <input type="text" id="f-device_id2" placeholder="Optional"/>
                    </div>
                    <div class="field" style="grid-column:1/-1">
                        <label>Signature</label>
                        <input type="text" id="f-signature" placeholder="Optional"/>
                    </div>
                </div>
                <div class="form-actions">
                    <button class="btn btn-primary" onclick="savePortal()" id="savePortalBtn">
                        <i class="fa-solid fa-floppy-disk"></i> Save Portal
                    </button>
                    <button class="btn btn-danger" onclick="deletePortal()" id="deletePortalBtn">
                        <i class="fa-solid fa-trash"></i> Delete Portal
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Channels -->
    <div id="tab-channels" class="tab-content">
        <div class="topbar">
            <h1>Channels</h1>
            <div class="topbar-actions">
                <input type="text" id="ch-search" placeholder="Search channels…"
                    style="background:var(--surface);border:1px solid var(--border);border-radius:8px;padding:8px 14px;color:var(--text);font-family:var(--font);font-size:13px;outline:none;width:200px"
                    oninput="filterChannels(this.value)"/>
            </div>
        </div>
        <div class="panel">
            <div class="panel-header">
                <span class="panel-title"><i class="fa-solid fa-tv"></i> Channel List</span>
                <span id="ch-count" style="font-family:var(--mono);font-size:12px;color:var(--muted)"></span>
            </div>
            <div class="panel-body">
                <div id="ch-list" class="ch-list">
                    <div style="color:var(--muted);font-size:13px">Loading channels…</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Settings -->
    <div id="tab-settings" class="tab-content">
        <div class="topbar"><h1>Settings</h1></div>
        <div class="panel">
            <div class="panel-header">
                <span class="panel-title"><i class="fa-solid fa-shield-halved"></i> Stream Proxy</span>
                <button class="btn btn-ghost" onclick="toggleProxy()" id="proxyBtn">
                    <i class="fa-solid fa-rotate-right"></i> Toggle
                </button>
            </div>
            <div class="panel-body">
                <p style="font-size:13px;color:var(--muted);margin-bottom:12px">
                    When enabled, all stream traffic is routed through this server. Useful for bypassing geo-restrictions. Current status:
                </p>
                <span id="proxy-badge">—</span>
            </div>
        </div>
        <div class="panel">
            <div class="panel-header">
                <span class="panel-title"><i class="fa-solid fa-key"></i> Change PIN</span>
            </div>
            <div class="panel-body">
                <div class="field" style="max-width:200px">
                    <label>New 4-digit PIN</label>
                    <input type="password" id="new-pin" maxlength="4" placeholder="••••" inputmode="numeric"/>
                </div>
                <div class="form-actions">
                    <button class="btn btn-primary" onclick="changePin()" style="width:auto">
                        <i class="fa-solid fa-key"></i> Update PIN
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Logs -->
    <div id="tab-logs" class="tab-content">
        <div class="topbar">
            <h1>Application Logs</h1>
            <button class="btn btn-ghost" onclick="loadLogs()">
                <i class="fa-solid fa-arrows-rotate"></i> Refresh
            </button>
        </div>
        <div class="panel">
            <div class="panel-body">
                <pre id="log-box" class="log-box">Loading…</pre>
            </div>
        </div>
    </div>

</main>
</div><!-- .shell -->
<?php endif; ?>


<!-- ── PLAYER MODAL ──────────────────────────────────────────────────────── -->
<div class="player-overlay" id="playerOverlay" onclick="closePlayerOnBackdrop(event)">
  <div class="player-wrap" id="playerWrap">
    <div class="player-topbar">
      <div class="player-title">
        <span class="dot"></span>
        <span id="playerChannelName">Loading…</span>
      </div>
      <button class="player-close" onclick="closePlayer()"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="player-video-wrap">
      <div class="player-loading" id="playerLoading">
        <div class="big-spinner"></div>
        <span>Fetching stream…</span>
      </div>
      <div class="player-error" id="playerError">
        <i class="fa-solid fa-circle-exclamation"></i>
        <span id="playerErrorMsg">Stream unavailable</span>
        <button class="btn btn-ghost" onclick="retryPlay()" style="margin-top:8px;font-size:12px">
          <i class="fa-solid fa-rotate-right"></i> Retry
        </button>
      </div>
      <video id="playerVideo" controls playsinline></video>
    </div>
    <div class="player-info">
      <span class="player-info-name" id="playerInfoName"></span>
      <span class="player-info-url" id="playerInfoUrl"></span>
      <button class="btn-copy" onclick="copyStreamUrl()" id="copyBtn">Copy URL</button>
    </div>
  </div>
</div>

<!-- Toast container -->
<div class="toast-wrap" id="toasts"></div>

<script>
// ── Utilities ──────────────────────────────────────────────────────────────────
function toast(msg, type = 'success') {
    const t = document.createElement('div');
    t.className = 'toast ' + type;
    t.textContent = msg;
    document.getElementById('toasts').appendChild(t);
    setTimeout(() => t.remove(), 3500);
}

function api(action, data = {}, method = 'POST') {
    const body = new URLSearchParams({action, ...data});
    return fetch('api.php', {method, body, headers: {'Content-Type': 'application/x-www-form-urlencoded'}})
        .then(r => r.json());
}

function setBusy(btn, busy, label = '') {
    if (!btn) return;
    btn.disabled = busy;
    if (busy) {
        btn._orig = btn.innerHTML;
        btn.innerHTML = '<span class="spinner"></span>' + (label ? ' ' + label : '');
    } else {
        btn.innerHTML = btn._orig || btn.innerHTML;
    }
}

// ── Login ──────────────────────────────────────────────────────────────────────
<?php if (!$authed): ?>
document.getElementById('pin').addEventListener('keydown', e => { if (e.key === 'Enter') doLogin(); });

async function doLogin() {
    const btn = document.getElementById('loginBtn');
    const pin = document.getElementById('pin').value.trim();
    if (!pin) { toast('Enter your PIN', 'error'); return; }
    setBusy(btn, true, 'Logging in…');
    const d = await api('login', {pin}).catch(() => null);
    if (d && d.status === 'success') {
        location.reload();
    } else {
        toast(d?.message || 'Login failed', 'error');
        setBusy(btn, false);
    }
}
<?php else: ?>

// ── Tab navigation ─────────────────────────────────────────────────────────────
function showTab(id, el) {
    document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
    document.getElementById(id).classList.add('active');
    if (el) el.classList.add('active');
}

// ── Logout ─────────────────────────────────────────────────────────────────────
async function doLogout() {
    await api('logout');
    location.reload();
}

// ── Dashboard ──────────────────────────────────────────────────────────────────
let _allChannels = [];

async function loadDashboard() {
    const d = await api('dashboard').catch(() => null);
    if (!d || d.status !== 'success') return;
    const {portal, expiry, channels, proxy, has_portal} = d.data;

    document.getElementById('stat-channels').textContent = channels || '0';
    document.getElementById('stat-expiry').textContent   = expiry  || '—';

    const pb = document.getElementById('stat-proxy');
    pb.innerHTML = proxy === 'ON'
        ? '<span class="badge badge-on"><i class="fa-solid fa-circle" style="font-size:6px"></i> ON</span>'
        : '<span class="badge badge-off"><i class="fa-solid fa-circle" style="font-size:6px"></i> OFF</span>';

    const proxyBadge = document.getElementById('proxy-badge');
    if (proxyBadge) proxyBadge.innerHTML = pb.innerHTML;

    // Fill portal form
    if (portal) {
        const map = {server_url:'f-server_url',mac_id:'f-mac_id',serial:'f-serial',
                     device_id1:'f-device_id1',device_id2:'f-device_id2',signature:'f-signature'};
        for (const [k, id] of Object.entries(map)) {
            const el = document.getElementById(id);
            if (el) el.value = portal[k] || '';
        }
    }

    const info = document.getElementById('overview-info');
    if (has_portal) {
        info.innerHTML = `<span style="color:var(--green)">✓</span> Portal configured — <b style="color:var(--text)">${portal.server_url || ''}</b><br>
            MAC: <b style="color:var(--text)">${portal.mac_id || '—'}</b>`;
    } else {
        info.innerHTML = '<span style="color:var(--muted)">No portal configured yet. Go to <b>Portal</b> tab to add one.</span>';
    }
}

// ── Portal actions ─────────────────────────────────────────────────────────────
async function savePortal() {
    const btn = document.getElementById('savePortalBtn');
    const data = {
        server_url: document.getElementById('f-server_url').value.trim(),
        mac_id:     document.getElementById('f-mac_id').value.trim(),
        serial:     document.getElementById('f-serial').value.trim(),
        device_id1: document.getElementById('f-device_id1').value.trim(),
        device_id2: document.getElementById('f-device_id2').value.trim(),
        signature:  document.getElementById('f-signature').value.trim(),
    };
    setBusy(btn, true, 'Saving…');
    const d = await api('save_portal', data).catch(() => null);
    setBusy(btn, false);
    if (d && d.status === 'success') {
        toast('Portal saved', 'success');
        loadDashboard();
    } else {
        toast(d?.message || 'Failed to save', 'error');
    }
}

async function refreshPortal() {
    const btn = document.getElementById('refreshBtn');
    setBusy(btn, true, 'Syncing…');
    const d = await api('refresh_portal').catch(() => null);
    setBusy(btn, false);
    if (d && d.status === 'success') {
        toast(d.message, 'success');
        loadDashboard();
        _allChannels = [];
    } else {
        toast(d?.message || 'Sync failed', 'error');
    }
}

async function deletePortal() {
    if (!confirm('Delete portal and all cached data?')) return;
    const d = await api('delete_portal').catch(() => null);
    if (d && d.status === 'success') {
        toast('Portal deleted', 'success');
        loadDashboard();
        _allChannels = [];
    } else {
        toast('Failed to delete', 'error');
    }
}

function exportM3U() {
    window.open('api.php?action=export_m3u', '_blank');
}

// ── Channels ───────────────────────────────────────────────────────────────────
async function loadChannels() {
    if (_allChannels.length > 0) { renderChannels(_allChannels); return; }
    const d = await api('get_channels').catch(() => null);
    if (d && d.status === 'success') {
        _allChannels = d.data;
        renderChannels(_allChannels);
    } else {
        document.getElementById('ch-list').innerHTML =
            '<div style="color:var(--muted);font-size:13px">' + (d?.message || 'No channels found') + '</div>';
    }
}

function renderChannels(list) {
    const cnt = document.getElementById('ch-count');
    if (cnt) cnt.textContent = list.length + ' channels';
    const el = document.getElementById('ch-list');
    if (!list.length) { el.innerHTML = '<div style="color:var(--muted);font-size:13px">No channels found</div>'; return; }
    el.innerHTML = list.map(ch => `
        <div class="ch-card" title="${ch.title}" onclick="playChannel(${JSON.stringify(ch.id)}, ${JSON.stringify(ch.title)})">
            <img src="${ch.logo}" onerror="this.src='assets/tv.png'" loading="lazy" alt="${ch.title}"/>
            <div class="ch-name">${ch.title}</div>
        </div>`).join('');
}

function filterChannels(q) {
    if (!q) { renderChannels(_allChannels); return; }
    const f = _allChannels.filter(c => c.title.toLowerCase().includes(q.toLowerCase()));
    renderChannels(f);
}

// ── Proxy ──────────────────────────────────────────────────────────────────────
async function toggleProxy() {
    const btn = document.getElementById('proxyBtn');
    setBusy(btn, true);
    const d = await api('toggle_proxy').catch(() => null);
    setBusy(btn, false);
    if (d && d.status === 'success') {
        toast('Stream proxy: ' + d.data.proxy, 'success');
        loadDashboard();
    } else {
        toast('Failed to toggle proxy', 'error');
    }
}

// ── PIN ────────────────────────────────────────────────────────────────────────
async function changePin() {
    const pin = document.getElementById('new-pin').value.trim();
    if (!/^\d{4}$/.test(pin)) { toast('PIN must be exactly 4 digits', 'error'); return; }
    const d = await api('change_pin', {pin}).catch(() => null);
    if (d && d.status === 'success') {
        toast('PIN updated — logging out', 'success');
        setTimeout(() => location.reload(), 1500);
    } else {
        toast(d?.message || 'Failed', 'error');
    }
}

// ── Logs ───────────────────────────────────────────────────────────────────────
async function loadLogs() {
    document.getElementById('log-box').textContent = 'Loading…';
    const d = await api('logs').catch(() => null);
    document.getElementById('log-box').textContent = d?.data || 'No logs available';
}


// ── Player ─────────────────────────────────────────────────────────────────
let _hlsInstance = null;
let _currentChannelId = null;
let _currentChannelName = null;
let _currentStreamUrl = null;

function playChannel(id, name) {
    _currentChannelId   = id;
    _currentChannelName = name;
    document.getElementById('playerChannelName').textContent = name;
    document.getElementById('playerInfoName').textContent    = name;
    document.getElementById('playerInfoUrl').textContent     = '';
    document.getElementById('playerOverlay').classList.add('open');
    document.getElementById('playerLoading').style.display  = 'flex';
    document.getElementById('playerError').style.display    = 'none';
    document.getElementById('playerVideo').style.display    = 'none';
    fetchAndPlay(id, name);
}

async function fetchAndPlay(id, name) {
    const d = await api('get_stream_url', {id}).catch(() => null);
    if (!d || d.status !== 'success') {
        showPlayerError(d?.message || 'Failed to get stream URL');
        return;
    }
    // Try direct URL first; live.php proxy is available as fallback
    const url = d.data.proxy; // use proxy so cookies/auth are handled server-side
    _currentStreamUrl = d.data.direct;
    document.getElementById('playerInfoUrl').textContent = d.data.direct;
    startPlayback(url);
}

function startPlayback(url) {
    const video = document.getElementById('playerVideo');

    // Destroy previous HLS instance
    if (_hlsInstance) { _hlsInstance.destroy(); _hlsInstance = null; }
    video.pause();
    video.src = '';

    const isHls = url.includes('.m3u8') || url.includes('.php') || url.includes('m3u');

    if (isHls && typeof Hls !== 'undefined' && Hls.isSupported()) {
        _hlsInstance = new Hls({
            enableWorker: true,
            lowLatencyMode: true,
            backBufferLength: 30,
        });
        _hlsInstance.loadSource(url);
        _hlsInstance.attachMedia(video);
        _hlsInstance.on(Hls.Events.MANIFEST_PARSED, () => {
            document.getElementById('playerLoading').style.display = 'none';
            video.style.display = 'block';
            video.play().catch(() => {});
        });
        _hlsInstance.on(Hls.Events.ERROR, (evt, data) => {
            if (data.fatal) {
                showPlayerError('Stream error: ' + (data.details || 'unknown'));
            }
        });
    } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
        // Native HLS (Safari/iOS)
        video.src = url;
        video.addEventListener('loadedmetadata', () => {
            document.getElementById('playerLoading').style.display = 'none';
            video.style.display = 'block';
            video.play().catch(() => {});
        }, { once: true });
        video.addEventListener('error', () => {
            showPlayerError('Playback failed — stream may be offline');
        }, { once: true });
    } else {
        // Direct link fallback (TS streams etc.)
        video.src = url;
        document.getElementById('playerLoading').style.display = 'none';
        video.style.display = 'block';
        video.play().catch(() => {});
    }
}

function showPlayerError(msg) {
    document.getElementById('playerLoading').style.display = 'none';
    document.getElementById('playerVideo').style.display   = 'none';
    document.getElementById('playerError').style.display   = 'flex';
    document.getElementById('playerErrorMsg').textContent  = msg;
}

function retryPlay() {
    if (_currentChannelId) playChannel(_currentChannelId, _currentChannelName);
}

function closePlayer() {
    document.getElementById('playerOverlay').classList.remove('open');
    const video = document.getElementById('playerVideo');
    video.pause(); video.src = '';
    if (_hlsInstance) { _hlsInstance.destroy(); _hlsInstance = null; }
}

function closePlayerOnBackdrop(e) {
    if (e.target === document.getElementById('playerOverlay')) closePlayer();
}

function copyStreamUrl() {
    if (!_currentStreamUrl) return;
    navigator.clipboard.writeText(_currentStreamUrl).then(() => {
        const b = document.getElementById('copyBtn');
        b.textContent = 'Copied!';
        setTimeout(() => b.textContent = 'Copy URL', 1500);
    });
}

// Close player on Escape key
document.addEventListener('keydown', e => { if (e.key === 'Escape') closePlayer(); });

// ── Boot ───────────────────────────────────────────────────────────────────────
loadDashboard();
<?php endif; ?>
</script>
</body>
</html>
