<?php
// Email account list — migrated to Zoho Mail (2026-06-22)
header('Content-Type: application/json');
header('Cache-Control: no-store');
require_once __DIR__ . '/../auth/db_config.php';
session_start_secure();
if (!is_logged_in()) { http_response_code(401); echo json_encode(['ok'=>false]); exit; }

$accounts = [
    [
        'email'      => 'security@shadowbridge.store',
        'user'       => 'security',
        'disk_used'  => '—',
        'disk_quota' => '5 GB',
        'disk_pct'   => 0,
        'disk_bytes' => 0,
        'suspended'  => false,
        'last_login' => null,
    ],
    [
        'email'      => 'info@shadowbridge.store',
        'user'       => 'info',
        'disk_used'  => '—',
        'disk_quota' => '5 GB',
        'disk_pct'   => 0,
        'disk_bytes' => 0,
        'suspended'  => false,
        'last_login' => null,
    ],
    [
        'email'      => 'info.team@shadowbridge.store',
        'user'       => 'info.team',
        'disk_used'  => '—',
        'disk_quota' => '5 GB',
        'disk_pct'   => 0,
        'disk_bytes' => 0,
        'suspended'  => false,
        'last_login' => null,
    ],
];

echo json_encode(['ok'=>true,'accounts'=>$accounts,'webmail_url'=>'https://mail.zoho.eu','provider'=>'Zoho Mail']);
