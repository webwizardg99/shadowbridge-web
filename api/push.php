<?php
// NOX Lab → ShadowBridge.store live push endpoint
// Each user authenticates with their own push_token (X-NOX-SECRET header)

header('Content-Type: application/json');

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405); echo json_encode(['ok'=>false,'error'=>'POST only']); exit;
}

$token = $_SERVER['HTTP_X_NOX_SECRET'] ?? '';
if (!$token) {
    http_response_code(403); echo json_encode(['ok'=>false,'error'=>'Missing token']); exit;
}

$body = file_get_contents('php://input');
$data = json_decode($body, true);
if (!$data) {
    http_response_code(400); echo json_encode(['ok'=>false,'error'=>'Invalid JSON']); exit;
}

require_once __DIR__ . '/../auth/db_config.php';
$pdo = db_connect();

// Look up user by push_token
$stmt = $pdo->prepare('SELECT id FROM users WHERE push_token=? LIMIT 1');
$stmt->execute([$token]);
$user = $stmt->fetch();
if (!$user) {
    http_response_code(403); echo json_encode(['ok'=>false,'error'=>'Invalid token']); exit;
}
$user_id = (int)$user['id'];

$node = preg_replace('/[^a-z0-9_-]/', '', strtolower($data['node_id'] ?? 'nox'));
$type = $data['type'] ?? 'status';

if ($type === 'status') {
    $stmt = $pdo->prepare('DELETE FROM lab_status WHERE node_id=? AND user_id=?');
    $stmt->execute([$node, $user_id]);
    $stmt = $pdo->prepare('INSERT INTO lab_status (node_id, user_id, payload, pushed_at) VALUES (?,?,?,UTC_TIMESTAMP())');
    $stmt->execute([$node, $user_id, $body]);
    echo json_encode(['ok'=>true,'stored'=>'status','user_id'=>$user_id]);

} elseif ($type === 'event') {
    $etype = preg_replace('/[^a-z0-9_]/', '', strtolower($data['event_type'] ?? 'unknown'));
    $stmt = $pdo->prepare('INSERT INTO lab_events (node_id, user_id, event_type, payload, created_at) VALUES (?,?,?,?,UTC_TIMESTAMP())');
    $stmt->execute([$node, $user_id, $etype, $body]);
    $pdo->prepare('DELETE FROM lab_events WHERE user_id=? AND id NOT IN (SELECT id FROM (SELECT id FROM lab_events WHERE user_id=? ORDER BY id DESC LIMIT 500) t)')
        ->execute([$user_id, $user_id]);
    echo json_encode(['ok'=>true,'stored'=>'event','user_id'=>$user_id]);
} else {
    echo json_encode(['ok'=>false,'error'=>'Unknown type']);
}
