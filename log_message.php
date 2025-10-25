<?php
// log_message.php – Accept JSON POST and save into message_logs table using PDO
header('Content-Type: application/json');
require 'db.php';

$data = json_decode(file_get_contents('php://input'), true);
if (!$data) {
    http_response_code(400);
    echo json_encode(['success'=>false,'error'=>'Invalid JSON']);
    exit;
}

// Extract fields safely
$username = $data['username'] ?? '';
$name = $data['name'] ?? '';
$contact = $data['contact'] ?? '';
$status = $data['status'] ?? '';
$error = $data['error'] ?? '';

if (!$username || !$contact) {
    echo json_encode(['success'=>false,'error'=>'Missing required fields']);
    exit;
}

// Insert into database
try {
    $stmt = $pdo->prepare("INSERT INTO message_logs (username, name, contact, status, error, sent_at) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->execute([$username, $name, $contact, $status, $error]);
    echo json_encode(['success'=>true]);
} catch (PDOException $e) {
    echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
}
?>
