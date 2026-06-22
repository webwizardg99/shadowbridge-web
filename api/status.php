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
$node    = $_GET['node'] ?? 'nox';
$node    = preg_replace('/[^a-z0-9_-]/', '', strtolower($node));

// Latest status — scoped to the logged-in user
$stmt = $pdo->prepare('SELECT payload, pushed_at FROM lab_status WHERE node_id=? AND user_id=? LIMIT 1');
$stmt->execute([$node, $user_id]);
$row  = $stmt->fetch();

if (!$row) {
    echo json_encode(['ok'=>true,'connected'=>false,'node'=>$node,'last_push'=>null,'data'=>null]);
    exit;
}

$age  = (time() - strtotime($row['pushed_at'] . ' UTC'));
$data = json_decode($row['payload'], true);

echo json_encode([
    'ok'        => true,
    'connected' => $age < 300,   // stale if > 5 min
    'node'      => $node,
    'last_push' => $row['pushed_at'],
    'age_sec'   => $age,
    'data'      => $data,
]);
