<?php
// ShadowBridge.Store Contact Form → HubSpot Integration
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: https://shadowbridge.store');
header('Access-Control-Allow-Methods: POST');

require_once __DIR__ . '/../auth/db_config.php';

// Load .env
$env_file = __DIR__ . '/../.env';
if (file_exists($env_file)) {
    $env = parse_ini_file($env_file);
} else {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => '.env not found']);
    exit;
}

$hubspot_key = $env['HUBSPOT_API_KEY'] ?? null;
if (!$hubspot_key) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'HubSpot API key not configured']);
    exit;
}

// Parse input
$input = json_decode(file_get_contents('php://input'), true);
$email = trim($input['email'] ?? $_POST['email'] ?? '');
$name = trim($input['name'] ?? $_POST['name'] ?? 'Unknown');
$message = trim($input['message'] ?? $_POST['message'] ?? '');
$phone = trim($input['phone'] ?? $_POST['phone'] ?? '');
$company = trim($input['company'] ?? $_POST['company'] ?? '');

// Validation
if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Invalid email']);
    exit;
}
if (strlen($message) < 10) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Message too short']);
    exit;
}

// 1. Create/Update contact in HubSpot
$contact_data = [
    'properties' => [
        'email' => $email,
        'firstname' => explode(' ', $name)[0] ?? 'Unknown',
        'lastname' => isset(explode(' ', $name)[1]) ? implode(' ', array_slice(explode(' ', $name), 1)) : '',
        'phone' => $phone,
        'company' => $company,
        'hs_lead_status' => 'NEW',
        'shadowbridge_contact_source' => 'contact_form',
        'shadowbridge_inquiry_message' => $message
    ]
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://api.hubapi.com/crm/v3/objects/contacts');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $hubspot_key,
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($contact_data));
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

$contact_response = curl_exec($ch);
$contact_http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($contact_http_code !== 200 && $contact_http_code !== 201) {
    error_log("HubSpot contact creation failed: $contact_http_code - $contact_response");
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Contact creation failed', 'http_code' => $contact_http_code, 'response' => $contact_response]);
    exit;
}

$contact_json = json_decode($contact_response, true);
$contact_id = $contact_json['id'] ?? null;

if (!$contact_id) {
    error_log("No contact ID in response: $contact_response");
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'No contact ID returned']);
    exit;
}

// 2. Create deal: "ShadowBridge Inquiry"
$deal_data = [
    'associations' => [
        [
            'types' => [['associationType' => 'contact_to_deal', 'direction' => 'FORWARD']],
            'id' => $contact_id
        ]
    ],
    'properties' => [
        'dealname' => "ShadowBridge Inquiry from " . htmlspecialchars($name),
        'dealstage' => 'negotiation',
        'pipeline' => 'default',
        'amount' => '0',
        'description' => htmlspecialchars($message)
    ]
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://api.hubapi.com/crm/v3/objects/deals');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $hubspot_key,
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($deal_data));
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

$deal_response = curl_exec($ch);
$deal_http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$deal_id = null;
if ($deal_http_code === 200 || $deal_http_code === 201) {
    $deal_json = json_decode($deal_response, true);
    $deal_id = $deal_json['id'] ?? null;
}

// 3. Create task: "Follow up ShadowBridge inquiry"
$task_data = [
    'properties' => [
        'hs_task_subject' => 'Follow up: ' . htmlspecialchars($name),
        'hs_task_body' => "Email: $email\nMessage: " . htmlspecialchars($message) . ($phone ? "\nPhone: $phone" : ""),
        'hs_task_status' => 'NOT_STARTED',
        'hs_task_priority' => 'HIGH',
        'hs_timestamp' => intval(microtime(true) * 1000)
    ],
    'associations' => [
        [
            'types' => [['associationType' => 'task_to_contact', 'direction' => 'FORWARD']],
            'id' => $contact_id
        ]
    ]
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://api.hubapi.com/crm/v3/objects/tasks');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $hubspot_key,
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($task_data));
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

$task_response = curl_exec($ch);
$task_http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$task_created = ($task_http_code === 200 || $task_http_code === 201);

// Log event
error_log("ShadowBridge Contact: email=$email, name=$name, contact_id=$contact_id, deal_id=$deal_id, task=$task_created");

http_response_code(200);
echo json_encode([
    'ok' => true,
    'contact_id' => $contact_id,
    'deal_id' => $deal_id,
    'task_created' => $task_created,
    'message' => 'Your inquiry has been submitted to HubSpot. We will get back to you soon.'
]);
?>
