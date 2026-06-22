<?php
// Recent lab events (SENTINEL, ATLAS, etc.)
header('Content-Type: application/json');
header('Cache-Control: no-store');

require_once __DIR__ . '/../auth/db_config.php';
session_start_secure();
if (!is_logged_in()) { http_response_code(401); echo json_encode(['ok'=>false]); exit; }

$pdo   = db_connect();
$node  = preg_replace('/[^a-z0-9_-]/', '', strtolower($_GET['node'] ?? 'nox'));
$type  = preg_replace('/[^a-z0-9_]/', '', strtolower($_GET['type'] ?? ''));
$limit = min((int)($_GET['limit'] ?? 50), 200);

if ($type) {
    $stmt = $pdo->prepare('SELECT event_type, payload, created_at FROM lab_events WHERE node_id=? AND event_type=? ORDER BY id DESC LIMIT ?');
    $stmt->execute([$node, $type, $limit]);
} else {
    $stmt = $pdo->prepare('SELECT event_type, payload, created_at FROM lab_events WHERE node_id=? ORDER BY id DESC LIMIT ?');
    $stmt->execute([$node, $limit]);
}

$events = [];
foreach ($stmt->fetchAll() as $row) {
    $p = json_decode($row['payload'], true) ?? [];
    $events[] = ['type'=>$row['event_type'], 'at'=>$row['created_at'], 'data'=>$p];
}

echo json_encode(['ok'=>true,'events'=>$events]);
