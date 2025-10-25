<?php
/**
 * WhatsApp Marketing App - PDO Installer
 * Creates DB, tables, and default admin user
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = 'localhost';
$user = 'root';
$pass = 'root';  // change to your MySQL password
$db   = 'whatsapp_auto';

echo "<h2>🚀 WhatsApp Marketing Installer</h2>";

// Connect to MySQL (no DB yet)
try {
    $pdo = new PDO("mysql:host=$host;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e){
    die("<p style='color:red;'>❌ Connection failed: ".$e->getMessage()."</p>");
}

// Create database
try {
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "<p>✅ Database '$db' created or already exists.</p>";
} catch(PDOException $e){
    die("<p style='color:red;'>❌ Error creating DB: ".$e->getMessage()."</p>");
}

// Connect to the database
$pdo->exec("USE `$db`");

// Create users table
$pdo->exec("
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
");
echo "<p>✅ 'users' table ready.</p>";

// Create message_logs table
$pdo->exec("
CREATE TABLE IF NOT EXISTS message_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100),
    name VARCHAR(100),
    contact VARCHAR(50),
    status VARCHAR(50),
    error TEXT,
    sent_at DATETIME,
    FOREIGN KEY (username) REFERENCES users(username) ON DELETE CASCADE
) ENGINE=InnoDB;
");
echo "<p>✅ 'message_logs' table ready.</p>";

// Create default admin
$adminUser = 'admin';
$adminPass = password_hash('1234', PASSWORD_BCRYPT);

// Check if admin exists
$stmt = $pdo->prepare("SELECT id FROM users WHERE username=?");
$stmt->execute([$adminUser]);
if($stmt->rowCount()===0){
    $stmt = $pdo->prepare("INSERT INTO users (username,password,role) VALUES (?,?,?)");
    $stmt->execute([$adminUser, $adminPass, 'admin']);
    echo "<p>✅ Admin created: <b>$adminUser</b> / Password: <b>1234</b></p>";
} else {
    echo "<p>ℹ️ Admin user already exists.</p>";
}

echo "<hr><p style='color:green;font-weight:bold;'>🎉 Installation complete!</p>";
echo "<p>👉 <a href='login.php'>Go to Login Page</a></p>";
?>
