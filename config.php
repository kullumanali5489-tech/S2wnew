<?php

error_reporting(0);

// ── App Settings ─────────────────────────────────────────────────────────────
define('APP_NAME',    'StalkerWeb');
define('DATA_DIR',    __DIR__ . '/__data__');
define('DEFAULT_PIN', '1234');

// ── Ensure data directory exists ──────────────────────────────────────────────
if (!is_dir(DATA_DIR)) mkdir(DATA_DIR, 0755, true);
if (!file_exists(DATA_DIR . '/.htaccess')) file_put_contents(DATA_DIR . '/.htaccess', 'deny from all');
if (!file_exists(DATA_DIR . '/index.php'))  file_put_contents(DATA_DIR . '/index.php',  '');

// ── Protocol / Host detection ─────────────────────────────────────────────────
$PROTO = 'http';
if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') $PROTO = 'https';
if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') $PROTO = 'https';
$HOST = $_SERVER['HTTP_HOST'] ?? 'localhost';
if (strpos($HOST, ':') !== false) $HOST = explode(':', $HOST)[0];

// ── JSON response helper ───────────────────────────────────────────────────────
function json_resp($status, $code, $message, $data = null) {
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    echo json_encode(['status' => $status, 'code' => $code, 'message' => $message, 'data' => $data]);
    exit;
}

// ── XOR encrypt / decrypt ─────────────────────────────────────────────────────
function xor_enc($action, $data) {
    $key = 'SwK9mPzXqL3tRvJ7nYdF2hCgBwA5eU8iO1sN6kT4jQpMrH0lVxGbDuEZyWcIfo';
    $out = '';
    if ($action === 'decrypt') $data = base64_decode(base64_decode($data));
    for ($i = 0, $l = strlen($data); $i < $l; $i++) {
        $out .= $data[$i] ^ $key[$i % strlen($key)];
    }
    if ($action === 'encrypt') $out = rtrim(base64_encode(base64_encode($out)), '=');
    return $out;
}

// ── PIN helpers ───────────────────────────────────────────────────────────────
function pin_valid($pin) { return (bool) preg_match('/^\d{4}$/', $pin); }
function pin_get() {
    $f = DATA_DIR . '/pin.dat';
    if (file_exists($f)) { $v = trim(file_get_contents($f)); if (pin_valid($v)) return $v; }
    return DEFAULT_PIN;
}
function pin_set($pin) {
    if (!pin_valid($pin)) return false;
    return (bool) file_put_contents(DATA_DIR . '/pin.dat', $pin);
}

// ── Portal helpers ─────────────────────────────────────────────────────────────
function portal_get() {
    $f = DATA_DIR . '/portal.json';
    if (!file_exists($f)) return [];
    $d = json_decode(file_get_contents($f), true);
    return is_array($d) ? $d : [];
}
function portal_save($data) {
    return (bool) file_put_contents(DATA_DIR . '/portal.json', json_encode($data));
}
function portal_delete() {
    foreach (['/portal.json','/token.json','/channels.json','/meta.json','/cookies.txt'] as $f)
        @unlink(DATA_DIR . $f);
}

// ── Proxy / log helpers ────────────────────────────────────────────────────────
function proxy_get() {
    $f = DATA_DIR . '/proxy.dat';
    if (file_exists($f)) { $v = trim(file_get_contents($f)); if ($v==='ON'||$v==='OFF') return $v; }
    return 'OFF';
}
function proxy_toggle() {
    $new = proxy_get() === 'ON' ? 'OFF' : 'ON';
    file_put_contents(DATA_DIR . '/proxy.dat', $new);
    return $new;
}
function app_log($status, $msg) {
    $f   = DATA_DIR . '/app.log';
    $cur = file_exists($f) ? file_get_contents($f) : '';
    $ip  = $_SERVER['REMOTE_ADDR'] ?? '-';
    $ua  = $_SERVER['HTTP_USER_AGENT'] ?? '-';
    file_put_contents($f, date('Y-m-d H:i:s')." | {$status} | {$msg} | {$ip} | {$ua}\n" . $cur);
}

// ── Cookie jar ────────────────────────────────────────────────────────────────
function cookie_jar() {
    $jar = DATA_DIR . '/cookies.txt';
    if (!file_exists($jar)) file_put_contents($jar, '');
    return $jar;
}

// ── Core HTTP — persistent cookie jar, proper STB headers ────────────────────
function stb_get($url, $extra_headers = []) {
    $jar = cookie_jar();
    $ch  = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_HTTPHEADER     => $extra_headers,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_ENCODING       => '',
        CURLOPT_COOKIEFILE     => $jar,
        CURLOPT_COOKIEJAR      => $jar,
    ]);
    $body = curl_exec($ch);
    $eff  = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['body' => $body, 'url' => $eff, 'code' => $code];
}

// Alias used by live.php
function http_get($url, $headers = []) { return stb_get($url, $headers); }

// ── URL helpers ───────────────────────────────────────────────────────────────
function url_root($url) {
    $p = parse_url($url);
    if (empty($p['host'])) return '';
    $out = $p['scheme'] . '://' . $p['host'];
    if (!empty($p['port'])) $out .= ':' . $p['port'];
    return $out;
}
function url_base($url) {
    if (strpos($url, '?') !== false) $url = explode('?', $url)[0];
    return str_replace(basename($url), '', $url);
}
function extract_uri($line) {
    $p = explode('URI="', $line);
    if (isset($p[1])) { $q = explode('"', $p[1]); if (!empty($q[0])) return trim($q[0]); }
    return '';
}
function fix_logo($logo) {
    global $PROTO, $HOST;
    if (!empty($logo) && (stripos($logo,'http://')!==false || stripos($logo,'https://')!==false)) return $logo;
    return $PROTO . '://' . $HOST . str_replace(basename($_SERVER['PHP_SELF']), '', $_SERVER['PHP_SELF']) . 'assets/tv.png';
}
function sanitize_stream_url($url) { return trim(str_replace('ffmpeg ', '', $url)); }
function clean_str($s) { return preg_replace('/[^a-zA-Z0-9_\-]/', '_', $s); }

// ── Portal field accessors ────────────────────────────────────────────────────
function mac_portal_url()  { $p = portal_get(); return $p['server_url']  ?? ''; }
function mac_server_url()  { $u = mac_portal_url(); return $u ? str_replace('/c/', '/server/load.php', $u) : ''; }
function mac_id()          { $p = portal_get(); return $p['mac_id']      ?? ''; }
function mac_serial()      { $p = portal_get(); return $p['serial']      ?? ''; }
function mac_dev1()        { $p = portal_get(); return $p['device_id1']  ?? ''; }
function mac_dev2()        { $p = portal_get(); return $p['device_id2']  ?? ''; }
function mac_signature_v() { $p = portal_get(); return $p['signature']   ?? ''; }

// ── Build STB headers — CRITICAL: token must be in Cookie AND Bearer ──────────
// This is the exact header set a real MAG250 sends. The token in the Cookie
// is what most portals actually check — Bearer alone is often insufficient.
function stb_headers($token = '') {
    $cookie = 'mac=' . mac_id() . '; stb_lang=en; timezone=Europe/Kiev';
    if (!empty($token)) {
        $cookie .= '; token=' . $token;  // ← CRITICAL: token in cookie
    }
    $h = [
        'User-Agent: Mozilla/5.0 (QtEmbedded; U; Linux; C) AppleWebKit/533.3 (KHTML, like Gecko) MAG200 stbapp ver: 2 rev: 250 Safari/533.3',
        'X-User-Agent: Model: MAG250; Link: WiFi',
        'Accept: */*',
        'Accept-Language: en-US,en;q=0.5',
        'Accept-Encoding: gzip, deflate',
        'Connection: keep-alive',
        'Referer: ' . mac_portal_url(),
        'Cookie: ' . $cookie,
    ];
    if (!empty($token)) {
        $h[] = 'Authorization: Bearer ' . $token;
    }
    return $h;
}

// ── Handshake ─────────────────────────────────────────────────────────────────
function mac_handshake($force = false) {
    $tf = DATA_DIR . '/token.json';
    if (!$force && file_exists($tf)) {
        $d = json_decode(file_get_contents($tf), true);
        if (!empty($d['token']) && time() < ($d['expires'] ?? 0))
            return ['token' => $d['token'], 'random' => $d['random'] ?? ''];
    }

    // Warm up the portal page first (sets Cloudflare cookies)
    stb_get(mac_portal_url(), stb_headers());

    $url = mac_server_url() . '?type=stb&action=handshake&token=&JsHttpRequest=1-xml';
    $res = stb_get($url, stb_headers());
    $j   = json_decode($res['body'], true);

    $token  = $j['js']['token']  ?? '';
    $random = $j['js']['random'] ?? '';

    if (empty($token)) {
        app_log('ERROR', 'Handshake failed: ' . strip_tags($res['body']) . ' (HTTP ' . $res['code'] . ')');
        return ['token' => '', 'random' => ''];
    }

    file_put_contents($tf, json_encode([
        'token'   => $token,
        'random'  => $random,
        'expires' => time() + 100,
    ]));
    return ['token' => $token, 'random' => $random];
}

// ── Get profile — MUST be called before create_link to authorise the session ──
function mac_get_profile($hs = null) {
    if ($hs === null) $hs = mac_handshake();
    if (empty($hs['token'])) return [];

    $qs = 'type=stb&action=get_profile&hd=1'
        . '&ver=' . urlencode('ImageDescription: 0.2.18-r14-pub-250; ImageDate: Fri Jan 15 15:20:44 EET 2016; PORTAL version: 5.1.0; API Version: JS API version: 328; STB API version: 134; Player Engine version: 0x566')
        . '&num_banks=2&sn=' . mac_serial()
        . '&stb_type=MAG250&image_version=218&video_out=hdmi'
        . '&device_id='  . mac_dev1()
        . '&device_id2=' . mac_dev2()
        . '&signature='  . mac_signature_v()
        . '&auth_second_step=1&hw_version=1.7-BD-00&not_valid_token=0&client_type=STB'
        . '&hw_version_2=36da041e6358ee8f8801105e36a63474'
        . '&timestamp=' . time()
        . '&api_signature=263'
        . '&metrics=' . urlencode('{"mac":"' . mac_id() . '","sn":"' . mac_serial() . '","model":"MAG250","type":"STB","uid":"","random":"' . $hs['random'] . '"}')
        . '&JsHttpRequest=1-xml';

    $res = stb_get(mac_server_url() . '?' . $qs, stb_headers($hs['token']));
    $j   = json_decode($res['body'], true);

    $name   = $j['js']['fname'] ?? ($j['js']['name'] ?? '');
    $expiry = $j['js']['expirydate'] ?? ($j['js']['expire_billing_date'] ?? '');

    if (empty($name)) {
        app_log('ERROR', 'Profile fetch failed: ' . strip_tags($res['body']) . ' (HTTP ' . $res['code'] . ')');
        return [];
    }

    $out = [
        'name'     => $name,
        'expiry'   => $expiry,
        'username' => $j['js']['login']    ?? '',
        'password' => $j['js']['password'] ?? '',
    ];
    file_put_contents(DATA_DIR . '/meta.json', json_encode($out));
    return $out;
}

function mac_get_meta() {
    $f = DATA_DIR . '/meta.json';
    if (!file_exists($f)) return [];
    return json_decode(file_get_contents($f), true) ?: [];
}

// ── Get channels ──────────────────────────────────────────────────────────────
function mac_get_channels($force = false) {
    $cf = DATA_DIR . '/channels.json';
    if (!$force && file_exists($cf)) {
        $d = json_decode(file_get_contents($cf), true);
        if (!empty($d[0])) return $d;
    }

    // Profile MUST be called before channel listing (authenticates session)
    $hs = mac_handshake($force);
    mac_get_profile($hs);

    $url = mac_server_url() . '?type=itv&action=get_all_channels&JsHttpRequest=1-xml';
    $res = stb_get($url, stb_headers($hs['token']));
    $j   = json_decode($res['body'], true);

    if (empty($j['js']['data'][0]['cmd'])) {
        app_log('ERROR', 'Channels fetch failed: ' . strip_tags($res['body']) . ' (HTTP ' . $res['code'] . ')');
        return [];
    }

    $out = [];
    foreach ($j['js']['data'] as $ch) {
        $out[] = ['id' => $ch['id'], 'title' => $ch['name'], 'logo' => $ch['logo'], 'cmd' => $ch['cmd']];
    }
    file_put_contents($cf, json_encode($out));
    app_log('SUCCESS', 'Channel list updated (' . count($out) . ' channels)');
    return $out;
}

function get_channel_by_id($id) {
    foreach (mac_get_channels() as $ch) {
        if ((string)$ch['id'] === (string)$id) return $ch;
    }
    return [];
}

// ── Get stream URL ────────────────────────────────────────────────────────────
// The correct auth sequence for create_link:
//   1. Fresh handshake  → get token
//   2. get_profile      → portal authorises this token for the MAC
//   3. create_link      → now works because the session is fully authenticated
function mac_get_stream_url($id) {
    $ch = get_channel_by_id($id);
    if (empty($ch)) return '';

    // Step 1: fresh token (never use cached token for stream requests)
    @unlink(DATA_DIR . '/token.json');
    $hs = mac_handshake(true);
    if (empty($hs['token'])) return '';

    // Step 2: authenticate session via get_profile
    mac_get_profile($hs);

    // Step 3: create_link with full MAG250 params + token in cookie
    $qs = 'type=itv&action=create_link'
        . '&cmd='               . urlencode($ch['cmd'])
        . '&series=0'
        . '&forced_storage=undefined'
        . '&disable_ad=0'
        . '&download=0'
        . '&force_ch_link_check=0'
        . '&JsHttpRequest=1-xml';

    $res = stb_get(mac_server_url() . '?' . $qs, stb_headers($hs['token']));
    $j   = json_decode($res['body'], true);

    if (empty($j['js']['cmd'])) {
        app_log('ERROR', 'Stream URL fetch failed: ' . strip_tags($res['body']) . ' (HTTP ' . $res['code'] . ')');
        return '';
    }
    return sanitize_stream_url($j['js']['cmd']);
}
