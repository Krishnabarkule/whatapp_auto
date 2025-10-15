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
move_uploaded_file($tmpName, $targetPath);

$countryCode = $_POST['country_code'] ?? '+91';
$delay = intval($_POST['delay'] ?? 15);
$imagePath = $_POST['image_path'] ?? '';
$statusLog = $_POST['status_log_file'] ?? 'status_log.csv';
$messageTemplate = $_POST['message_template'] ?? '';

$pythonScript = __DIR__ . '/send_whatsapp.py';

// Clean args
$args = [
    escapeshellarg($targetPath),
    escapeshellarg($countryCode),
    escapeshellarg($delay),
    escapeshellarg($imagePath),
    escapeshellarg($statusLog),
    escapeshellarg($messageTemplate)
];

$cmd = "nohup python3 $pythonScript " . implode(' ', $args) . " > /dev/null 2>&1 &";
exec($cmd);

echo json_encode(['success' => true, 'message' => 'Script started']);
?>
