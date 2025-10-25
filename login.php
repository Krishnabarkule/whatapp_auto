<?php
session_start();
require 'db.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username=?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        header("Location: index.php");
        exit;
    } else {
        $error = "Invalid username or password.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Login</title>
<style>
body{font-family:Arial;background:#f4f4f4;padding:50px;}
form{width:300px;margin:auto;background:white;padding:20px;border-radius:10px;box-shadow:0 0 8px rgba(0,0,0,0.1);}
input{width:100%;padding:10px;margin:10px 0;}
button{width:100%;padding:10px;background:#28a745;color:white;border:none;border-radius:5px;}
p.error{color:red;text-align:center;}
</style>
</head>
<body>
<form method="post">
<h2>Login</h2>
<?php if($error) echo "<p class='error'>$error</p>"; ?>
<input type="text" name="username" placeholder="Username" required>
<input type="password" name="password" placeholder="Password" required>
<button type="submit">Login</button>
</form>
</body>
</html>
