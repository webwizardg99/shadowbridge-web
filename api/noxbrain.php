<?php
// NOX-BRAIN proxy — forwards chat to NOX Lab port 8484 via pushed endpoint
header('Content-Type: application/json');
require_once __DIR__ . '/../auth/db_config.php';
session_start_secure();
if (!is_logged_in()) { http_response_code(401); echo json_encode(['ok'=>false]); exit; }
if (!is_admin())     { http_response_code(403); echo json_encode(['ok'=>false,'error'=>'Access denied']); exit; }

$body = json_decode(file_get_contents('php://input'), true);
$msg  = trim($body['message'] ?? '');
if (!$msg) { echo json_encode(['response'=>'Empty message.']); exit; }

// Get NOX-BRAIN endpoint from latest push data
$pdo  = db_connect();
$stmt = $pdo->prepare('SELECT payload FROM lab_status WHERE node_id=? LIMIT 1');
$stmt->execute(['nox']);
$row  = $stmt->fetch();
$endpoint = 'http://192.168.1.103:8484/api/chat'; // fallback direct
if ($row) {
    $d = json_decode($row['payload'], true);
    if (!empty($d['noxbrain_url'])) $endpoint = $d['noxbrain_url'];
}

$ch = curl_init($endpoint);
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => json_encode(['message'=>$msg,'session_id'=>'sb_web_'.($_SESSION['user_id']??0)]),
    CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 30,
]);
$res  = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($res && $code === 200) {
    $data = json_decode($res, true);
    echo json_encode(['response' => $data['response'] ?? $data['reply'] ?? $res]);
} else {
    echo json_encode(['response' => 'NOX-BRAIN is offline or unreachable. Start it on the NOX node: port 8484.']);
}
