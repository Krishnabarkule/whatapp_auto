<?php
header('Content-Type: application/json');

$uploadDir = __DIR__ . '/uploads/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'error' => 'File upload error']);
    exit;
}

$tmpName = $_FILES['csv_file']['tmp_name'];
$fileName = basename($_FILES['csv_file']['name']);
$targetPath = $uploadDir . $fileName;
if (!move_uploaded_file($tmpName, $targetPath)) {
    echo json_encode(['success' => false, 'error' => 'Failed to move uploaded file']);
    exit;
}

$countryCode = $_POST['country_code'] ?? '+91';
$delay = intval($_POST['delay'] ?? 15);
$imagePath = $_POST['image_path'] ?? '';
$statusLog = $_POST['status_log_file'] ?? 'status_log.csv';
$messageTemplate = $_POST['message_template'] ?? '';
$username = $_POST['username'] ?? 'unknown';

$pythonScript = __DIR__ . '/send_whatsapp.py';

// Remove any previous stop/pause/progress flags
@unlink(__DIR__ . '/stop.flag');
@unlink(__DIR__ . '/pause.flag');
@unlink(__DIR__ . '/progress.json');

// Build command with arguments safely
$args = [
    escapeshellarg($targetPath),
    escapeshellarg($countryCode),
    escapeshellarg($delay),
    escapeshellarg($imagePath),
    escapeshellarg($statusLog),
    escapeshellarg($messageTemplate),
    escapeshellarg($username)
];

$cmd = "python3 " . escapeshellarg($pythonScript) . " " . implode(' ', $args) . " > " . __DIR__ . "/send.log 2>&1 &";
exec($cmd);

echo json_encode(['success' => true, 'message' => 'Script started. Check progress in UI.']);
