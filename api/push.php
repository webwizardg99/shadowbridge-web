<?php
// NOX Lab → ShadowBridge.store live push endpoint
// Called every 30s by ~/scripts/sb_push.py on the NOX Lab node

header('Content-Type: application/json');

define('PUSH_SECRET', 'NOX_PUSH_S3CR3T_2026_SB');

$method = $_SERVER['REQUEST_METHOD'] ?? '';
if ($method !== 'POST') { http_response_code(405); echo json_encode(['ok'=>false,'error'=>'POST only']); exit; }

$auth = $_SERVER['HTTP_X_NOX_SECRET'] ?? '';
if ($auth !== PUSH_SECRET) { http_response_code(403); echo json_encode(['ok'=>false,'error'=>'Forbidden']); exit; }

$body = file_get_contents('php://input');
$data = json_decode($body, true);
if (!$data) { http_response_code(400); echo json_encode(['ok'=>false,'error'=>'Invalid JSON']); exit; }

require_once __DIR__ . '/../auth/db_config.php';
$pdo = db_connect();

$node   = preg_replace('/[^a-z0-9_-]/', '', strtolower($data['node_id'] ?? 'nox'));
$type   = $data['type'] ?? 'status';

if ($type === 'status') {
    // Upsert — keep only latest per node
    $stmt = $pdo->prepare('DELETE FROM lab_status WHERE node_id=?');
    $stmt->execute([$node]);
    $stmt = $pdo->prepare('INSERT INTO lab_status (node_id, payload, pushed_at) VALUES (?,?,UTC_TIMESTAMP())');
    $stmt->execute([$node, $body]);
    echo json_encode(['ok'=>true,'stored'=>'status']);

} elseif ($type === 'event') {
    $etype = preg_replace('/[^a-z0-9_]/', '', strtolower($data['event_type'] ?? 'unknown'));
    $stmt = $pdo->prepare('INSERT INTO lab_events (node_id, event_type, payload, created_at) VALUES (?,?,?,UTC_TIMESTAMP())');
    $stmt->execute([$node, $etype, $body]);
    // Keep only last 500 events per node
    $pdo->prepare('DELETE FROM lab_events WHERE node_id=? AND id NOT IN (SELECT id FROM (SELECT id FROM lab_events WHERE node_id=? ORDER BY id DESC LIMIT 500) t)')->execute([$node, $node]);
    echo json_encode(['ok'=>true,'stored'=>'event']);
} else {
    echo json_encode(['ok'=>false,'error'=>'Unknown type']);
}
