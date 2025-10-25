<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php"); exit;
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Admin Dashboard | WhatsApp Marketing</title>
<style>
  body{font-family:'Segoe UI',sans-serif;background:#f0f4f3;padding:40px}
  .card{background:white;padding:36px;border-radius:14px;width:820px;margin:auto;box-shadow:0 8px 24px rgba(0,0,0,0.06)}
  h2{color:#2d7a46;text-align:center}
  .links{display:flex;gap:12px;justify-content:center;margin-top:18px}
  a{background:#28a745;color:white;padding:12px 18px;border-radius:9px;text-decoration:none;font-weight:600}
</style>
</head>
<body>
  <div class="card">
    <h2>👨‍💼 Admin Dashboard</h2>
    <p style="text-align:center">Welcome, <b><?=htmlspecialchars($_SESSION['username'])?></b></p>
    <div class="links">
      <a href="manage_users.php">Manage Users</a>
      <a href="admin_dashboard.php">Analytics</a>
      <a href="index.php">WhatsApp Tool</a>
      <a href="logout.php">Logout</a>
    </div>
  </div>
</body>
</html>
