<?php
// Email account stats via cPanel UAPI
header('Content-Type: application/json');
header('Cache-Control: no-store');
require_once __DIR__ . '/../auth/db_config.php';
session_start_secure();
if (!is_logged_in()) { http_response_code(401); echo json_encode(['ok'=>false]); exit; }

define('CPANEL_HOST',  'shadowbridge.store');
define('CPANEL_USER',  'ghostwizardg');
define('CPANEL_TOKEN', 'WW2C16O4XHEBR6SC2J1UV9K4V8GBMU44');

$ch = curl_init('https://'.CPANEL_HOST.':2083/execute/Email/list_pops_with_disk');
curl_setopt_array($ch, [
    CURLOPT_HTTPHEADER     => ['Authorization: cpanel '.CPANEL_USER.':'.CPANEL_TOKEN],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_TIMEOUT        => 8,
]);
$res  = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if (!$res || $code !== 200) {
    echo json_encode(['ok'=>false,'error'=>'cPanel API unreachable']);
    exit;
}

$data = json_decode($res, true);
$accounts = [];
foreach ($data['data'] ?? [] as $acc) {
    $accounts[] = [
        'email'       => $acc['email']       ?? '',
        'user'        => $acc['user']         ?? '',
        'disk_used'   => $acc['humandiskused'] ?? '0 KB',
        'disk_quota'  => $acc['humandiskquota'] ?? '—',
        'disk_pct'    => round((float)($acc['diskusedpercent_float'] ?? 0), 1),
        'disk_bytes'  => (int)($acc['_diskused'] ?? 0),
        'suspended'   => (bool)($acc['suspended_login'] ?? false),
        'last_login'  => $acc['mtime'] ? date('Y-m-d H:i', $acc['mtime']) : null,
    ];
}

echo json_encode(['ok'=>true,'accounts'=>$accounts,'webmail_url'=>'https://webmail.shadowbridge.store']);
