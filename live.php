<?php
require_once __DIR__ . '/config.php';

$id      = trim(strip_tags($_GET['id']      ?? ''));
$chunks  = trim(strip_tags($_GET['chunks']  ?? ''));
$segment = trim(strip_tags($_GET['segment'] ?? ''));

$EXT_M3U = '.m3u8';
$EXT_TS  = '.ts';
$EXT_KEY = '.key';

// If accessed without extension (plain .php URL), keep .php so rewrites stay consistent
if (stripos($_SERVER['REQUEST_URI'], '.php?') !== false) {
    $EXT_M3U = $EXT_TS = $EXT_KEY = '.php';
}

$streamHeaders = [
    'User-Agent: Mozilla/5.0 (QtEmbedded; U; Linux; C) AppleWebKit/533.3 (KHTML, like Gecko) MAG200 stbapp ver: 2 rev: 250 Safari/533.3',
];

// ── Channel ID → get stream URL and either redirect or proxy ──────────────────
if (!empty($id)) {
    if (empty(mac_server_url())) { http_response_code(503); exit; }

    $ch = get_channel_by_id($id);
    if (empty($ch)) { http_response_code(404); exit; }

    $streamURL = mac_get_stream_url($ch['id']);
    if (empty($streamURL)) { http_response_code(502); exit; }

    if (proxy_get() === 'OFF') {
        header('Location: ' . $streamURL);
        exit;
    }

    // Proxy mode: fetch and rewrite M3U8
    $res = http_get($streamURL, $streamHeaders);
    if (stripos($res['body'], '#EXTM3U') === false) { http_response_code(502); exit; }

    header('Content-Type: application/vnd.apple.mpegurl');
    echo rewrite_m3u8($res['body'], $res['url'], $EXT_M3U, $EXT_TS, '');
    exit;
}

// ── Sub-playlist (chunks) ─────────────────────────────────────────────────────
if (!empty($chunks)) {
    $streamURL = xor_enc('decrypt', $chunks);
    if (!filter_var($streamURL, FILTER_VALIDATE_URL)) { http_response_code(400); exit; }
    $res = http_get($streamURL, $streamHeaders);
    if (stripos($res['body'], '#EXTM3U') === false) { http_response_code(404); exit; }
    header('Content-Type: application/vnd.apple.mpegurl');
    echo rewrite_m3u8($res['body'], $res['url'], $EXT_M3U, $EXT_TS, $_GET['vtoken'] ?? '', true);
    exit;
}

// ── TS Segment ───────────────────────────────────────────────────────────────
if (!empty($segment)) {
    $streamURL = xor_enc('decrypt', $segment);
    if (!filter_var($streamURL, FILTER_VALIDATE_URL)) { http_response_code(400); exit; }
    $res = http_get($streamURL, $streamHeaders);
    if ($res['code'] == 200 || $res['code'] == 206) {
        header('Content-Type: video/mp2t');
        echo $res['body'];
        exit;
    }
    http_response_code(410);
    exit;
}

http_response_code(400);
exit;

// ── M3U8 rewriter ─────────────────────────────────────────────────────────────
function rewrite_m3u8($body, $effective_url, $ext_m3u, $ext_ts, $vtoken, $is_sub = false) {
    global $EXT_KEY;
    $out   = '';
    $lines = explode("\n", $body);
    $vt    = $vtoken ? '&vtoken=' . urlencode($vtoken) : '';

    foreach ($lines as $line) {
        $line = rtrim($line);

        // Encryption key URI
        if (stripos($line, 'URI="') !== false) {
            $orgURI  = extract_uri($line);
            $base    = ($orgURI[0] ?? '') === '/' ? url_root($effective_url) : url_base($effective_url);
            if (!$orgURI || stripos($orgURI, 'http') === 0) $base = '';
            $newURI  = 'live' . $EXT_KEY . '?chunks=' . xor_enc('encrypt', $base . $orgURI) . $vt;
            $out    .= str_replace($orgURI, $newURI, $line) . "\n";
        }
        // TS segment
        elseif (stripos($line, '.ts') !== false && stripos($line, 'URI="') === false) {
            $base = ($line[0] ?? '') === '/' ? url_root($effective_url) : url_base($effective_url);
            if (stripos($line, 'http') === 0) $base = '';
            $out .= 'live' . $ext_ts . '?segment=' . xor_enc('encrypt', $base . $line) . $vt . "\n";
        }
        // Sub-playlist
        elseif (stripos($line, '.m3u8') !== false || (!$is_sub && stripos($line, '/hls') !== false)) {
            if (stripos($line, 'URI="') !== false) { $out .= $line . "\n"; continue; }
            $base = ($line[0] ?? '') === '/' ? url_root($effective_url) : url_base($effective_url);
            if (stripos($line, 'http') === 0) $base = '';
            $out .= 'live' . $ext_m3u . '?chunks=' . xor_enc('encrypt', $base . $line) . $vt . "\n";
        }
        else {
            $out .= $line . "\n";
        }
    }
    return trim($out);
}
