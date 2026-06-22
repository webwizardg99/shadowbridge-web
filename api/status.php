<?php
// Live lab status API — dashboard JS polls this
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: https://shadowbridge.store');
header('Cache-Control: no-store');

require_once __DIR__ . '/../auth/db_config.php';

// Auth: require logged-in session
session_start_secure();
if (!is_logged_in()) { http_response_code(401); echo json_encode(['ok'=>false,'error'=>'Unauthorized']); exit; }

$pdo     = db_connect();
$user_id = (int)($_SESSION['user_id'] ?? 0);
$node    = isset($_GET['node']) ? preg_replace('/[^a-z0-9_-]/', '', strtolower($_GET['node'])) : '';

// Latest status — scoped to the logged-in user
// If no specific node requested, return the most recently pushed node
if ($node) {
    $stmt = $pdo->prepare('SELECT node_id, payload, pushed_at FROM lab_status WHERE node_id=? AND user_id=? LIMIT 1');
    $stmt->execute([$node, $user_id]);
} else {
    $stmt = $pdo->prepare('SELECT node_id, payload, pushed_at FROM lab_status WHERE user_id=? ORDER BY pushed_at DESC LIMIT 1');
    $stmt->execute([$user_id]);
}
$row  = $stmt->fetch();

if (!$row) {
    echo json_encode(['ok'=>true,'connected'=>false,'node'=>$node,'last_push'=>null,'data'=>null]);
    exit;
}

$age  = (time() - strtotime($row['pushed_at'] . ' UTC'));
$data = json_decode($row['payload'], true);

echo json_encode([
    'ok'        => true,
    'connected' => $age < 300,
    'node'      => $row['node_id'],
    'last_push' => $row['pushed_at'],
    'age_sec'   => $age,
    'data'      => $data,
]);
