<?php
session_start();
require_once __DIR__ . '/config.php';

$action = trim($_REQUEST['action'] ?? '');

// ── Public endpoints (no login required) ──────────────────────────────────────

if ($action === 'login') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_resp('error', 405, 'Method not allowed');
    $pin = trim($_POST['pin'] ?? '');
    if (empty($pin))           json_resp('error', 400, 'Please enter your PIN');
    if (!pin_valid($pin))      json_resp('error', 400, 'PIN must be 4 digits');
    if (md5($pin) !== md5(pin_get())) json_resp('error', 403, 'Incorrect PIN');
    $_SESSION['auth'] = true;
    json_resp('success', 200, 'Logged in');
}

if ($action === 'logout') {
    session_destroy();
    json_resp('success', 200, 'Logged out');
}

// ── Auth guard ────────────────────────────────────────────────────────────────
if (empty($_SESSION['auth'])) {
    json_resp('error', 401, 'Login required');
}

// ── Protected endpoints ───────────────────────────────────────────────────────

if ($action === 'dashboard') {
    $portal = portal_get();
    $meta   = mac_get_meta();
    $chs    = mac_get_channels();
    $expiry = '-';
    if (!empty($meta['expiry'])) {
        $ts = strtotime($meta['expiry']);
        $expiry = $ts ? date('F d, Y', $ts) : $meta['expiry'];
    }
    json_resp('success', 200, 'OK', [
        'portal'   => $portal,
        'expiry'   => $expiry,
        'channels' => count($chs),
        'proxy'    => proxy_get(),
        'has_portal' => !empty($portal['server_url']),
    ]);
}

if ($action === 'save_portal') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_resp('error', 405, 'Method not allowed');
    $url  = trim(strip_tags($_POST['server_url']  ?? ''));
    $mac  = trim(strip_tags($_POST['mac_id']      ?? ''));
    $ser  = trim(strip_tags($_POST['serial']      ?? ''));
    $dv1  = trim(strip_tags($_POST['device_id1']  ?? ''));
    $dv2  = trim(strip_tags($_POST['device_id2']  ?? ''));
    $sig  = trim(strip_tags($_POST['signature']   ?? ''));
    if (empty($url)) json_resp('error', 400, 'Server URL is required');
    if (empty($mac)) json_resp('error', 400, 'MAC ID is required');
    if (substr($url, -3) !== '/c/') json_resp('error', 400, 'Server URL must end with /c/');
    portal_save(compact('url', 'mac', 'ser', 'dv1', 'dv2', 'sig') + [
        'server_url' => $url, 'mac_id' => $mac, 'serial' => $ser,
        'device_id1' => $dv1, 'device_id2' => $dv2, 'signature' => $sig,
    ]);
    app_log('SUCCESS', 'Portal saved');
    json_resp('success', 200, 'Portal saved');
}

if ($action === 'refresh_portal') {
    if (empty(mac_server_url())) json_resp('error', 503, 'Portal not configured');
    // Clear caches to force fresh fetch
    @unlink(DATA_DIR . '/token.json');
    @unlink(DATA_DIR . '/channels.json');
    @unlink(DATA_DIR . '/meta.json');
    $profile  = mac_get_profile();
    if (empty($profile)) json_resp('error', 502, 'Failed to fetch profile — check your portal details');
    $channels = mac_get_channels(true);
    if (empty($channels)) json_resp('error', 502, 'Failed to fetch channel list');
    app_log('SUCCESS', 'Portal refreshed');
    json_resp('success', 200, 'Portal refreshed — ' . count($channels) . ' channels loaded');
}

if ($action === 'delete_portal') {
    portal_delete();
    app_log('SUCCESS', 'Portal deleted');
    json_resp('success', 200, 'Portal deleted');
}

if ($action === 'toggle_proxy') {
    $new = proxy_toggle();
    json_resp('success', 200, 'Stream proxy is now ' . $new, ['proxy' => $new]);
}

if ($action === 'change_pin') {
    $pin = trim($_POST['pin'] ?? '');
    if (!pin_valid($pin)) json_resp('error', 400, 'PIN must be exactly 4 digits');
    pin_set($pin);
    session_destroy();
    json_resp('success', 200, 'PIN updated — please log in again');
}

if ($action === 'get_channels') {
    $chs = mac_get_channels();
    if (empty($chs)) json_resp('error', 404, 'No channels found');
    $out = array_map(fn($c) => ['id' => $c['id'], 'title' => $c['title'], 'logo' => fix_logo($c['logo'])], $chs);
    json_resp('success', 200, count($out) . ' channels', $out);
}

if ($action === 'export_m3u') {
    global $PROTO, $HOST;
    $chs = mac_get_channels();
    if (empty($chs)) { http_response_code(404); exit; }
    $base = $PROTO . '://' . $HOST . str_replace(basename($_SERVER['PHP_SELF']), '', $_SERVER['PHP_SELF']);
    $m3u  = "#EXTM3U\n";
    $i = 0;
    foreach ($chs as $ch) {
        $i++;
        $m3u .= '#EXTINF:-1 tvg-id="' . $i . '" tvg-name="' . htmlspecialchars($ch['title'])
              . '" tvg-logo="' . fix_logo($ch['logo']) . '" group-title="' . APP_NAME . '",'
              . $ch['title'] . "\n";
        $m3u .= $base . 'live.php?id=' . $ch['id'] . "\n";
    }
    header('Content-Type: application/vnd.apple.mpegurl');
    header('Content-Disposition: attachment; filename="' . clean_str(APP_NAME) . '_' . time() . '.m3u"');
    exit($m3u);
}

if ($action === 'logs') {
    $f = DATA_DIR . '/app.log';
    $logs = file_exists($f) ? file_get_contents($f) : 'No logs yet.';
    json_resp('success', 200, 'Logs', $logs);
}

json_resp('error', 400, 'Unknown action');
