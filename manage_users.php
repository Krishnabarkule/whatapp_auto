<?php
session_start();
require 'db.php';
if(!isset($_SESSION['role']) || $_SESSION['role']!=='admin'){ header("Location: login.php"); exit; }

$msg = '';
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['action'])){
    $action = $_POST['action'];
    $username = trim($_POST['username'] ?? '');
    if($action==='add'){
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'user';
        if($username && $password){
            $hash = password_hash($password,PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username,password,role) VALUES (?,?,?)");
            try{
                $stmt->execute([$username,$hash,$role]);
                $msg="User added successfully.";
            } catch(Exception $e){ $msg="Error: ".$e->getMessage(); }
        } else $msg="Provide username & password.";
    } elseif($action==='delete'){
        if($username){
            $stmt = $pdo->prepare("DELETE FROM users WHERE username=?");
            try{
                $stmt->execute([$username]);
                $msg="User deleted successfully.";
            } catch(Exception $e){ $msg="Error: ".$e->getMessage(); }
        } else $msg="Provide username to delete.";
    }
}

$users = $pdo->query("SELECT id,username,role,created_at FROM users ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Manage Users</title>
<style>
body{font-family:'Segoe UI',sans-serif;background:#e9f5ee;padding:40px;}
.card{background:#fff;padding:30px;border-radius:12px;width:900px;margin:auto;box-shadow:0 6px 18px rgba(0,0,0,0.06);}
h2{text-align:center;color:#2d7a46;}
form{display:flex;gap:8px;flex-wrap:wrap;justify-content:center;margin-bottom:12px;}
input,select,button{padding:10px;border-radius:8px;border:1px solid #ccc;}
button{background:#28a745;color:#fff;border:none;cursor:pointer;}
table{width:100%;border-collapse:collapse;margin-top:12px;}
th,td{padding:10px;border:1px solid #ddd;text-align:center;}
th{background:#2d7a46;color:#fff;}
.msg{text-align:center;color:#2d7a46;margin-bottom:8px;}
</style>
</head>
<body>
<div class="card">
<a href="admin_dashboard.php" style="color:#2d7a46;text-decoration:none;">⬅ Back to Dashboard</a>
<h2>👥 Manage Users</h2>
<?php if($msg) echo "<div class='msg'>".htmlspecialchars($msg)."</div>"; ?>
<form method="post">
<input name="username" placeholder="Username" required>
<input name="password" placeholder="Password" type="password" required>
<select name="role"><option value="user">User</option><option value="admin">Admin</option></select>
<button name="action" value="add">Add</button>
<button name="action" value="delete">Delete</button>
</form>

<table>
<tr><th>Username</th><th>Role</th><th>Created At</th></tr>
<?php foreach($users as $u): ?>
<tr>
<td><?=htmlspecialchars($u['username'])?></td>
<td><?=htmlspecialchars($u['role'])?></td>
<td><?=htmlspecialchars($u['created_at'])?></td>
</tr>
<?php endforeach; ?>
</table>
</div>
</body>
</html>
