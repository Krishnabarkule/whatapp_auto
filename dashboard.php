<?php
session_start();
require 'db.php';
if(!isset($_SESSION['username'])){ header("Location: login.php"); exit; }

$user = $_SESSION['username'];
$viewUser = $user;
if(isset($_GET['user']) && $_SESSION['role']==='admin'){ $viewUser = $_GET['user']; }

// Daily
$daily_q = $pdo->prepare("SELECT DATE(sent_at) as day, COUNT(*) as total, SUM(status='Success') as success, SUM(status='Failed') as failed FROM message_logs WHERE username=? GROUP BY day ORDER BY day DESC LIMIT 30");
$daily_q->execute([$viewUser]);
$daily = $daily_q->fetchAll(PDO::FETCH_ASSOC);

// Monthly
$monthly_q = $pdo->prepare("SELECT DATE_FORMAT(sent_at,'%Y-%m') as month, COUNT(*) as total, SUM(status='Success') as success, SUM(status='Failed') as failed FROM message_logs WHERE username=? GROUP BY month ORDER BY month DESC LIMIT 12");
$monthly_q->execute([$viewUser]);
$monthly = $monthly_q->fetchAll(PDO::FETCH_ASSOC);

// Yearly
$yearly_q = $pdo->prepare("SELECT YEAR(sent_at) as year, COUNT(*) as total, SUM(status='Success') as success, SUM(status='Failed') as failed FROM message_logs WHERE username=? GROUP BY year ORDER BY year DESC LIMIT 5");
$yearly_q->execute([$viewUser]);
$yearly = $yearly_q->fetchAll(PDO::FETCH_ASSOC);

// Recent
$recent_q = $pdo->prepare("SELECT name,contact,status,error,sent_at FROM message_logs WHERE username=? ORDER BY id DESC LIMIT 50");
$recent_q->execute([$viewUser]);
$recent = $recent_q->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Dashboard</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
body{font-family:'Segoe UI',sans-serif;background:#e9f5ee;padding:30px;}
.container{max-width:1100px;margin:auto;}
.card{background:#fff;padding:22px;border-radius:12px;margin-bottom:20px;box-shadow:0 6px 18px rgba(0,0,0,0.06);}
canvas{width:100% !important;height:320px !important;}
table{width:100%;border-collapse:collapse;margin-top:14px;}
th,td{padding:8px;border:1px solid #e6e6e6;text-align:center;}
th{background:#2d7a46;color:#fff;}
a.btn{background:#28a745;color:#fff;padding:8px 12px;border-radius:8px;text-decoration:none;}
.toplinks{display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;}
</style>
</head>
<body>
<div class="container">
<div class="card">
<div class="toplinks">
<h2>📊 Message Analytics - <?=htmlspecialchars($viewUser=== $user?"My Account":$viewUser)?></h2>
<div><a class="btn" href="index.php">Send Messages</a> <a class="btn" href="logout.php">Logout</a></div>
</div>
</div>

<div class="card"><h3>Daily</h3><canvas id="dailyChart"></canvas></div>
<div class="card"><h3>Monthly</h3><canvas id="monthlyChart"></canvas></div>
<div class="card"><h3>Yearly</h3><canvas id="yearlyChart"></canvas></div>

<div class="card"><h3>Recent Messages</h3>
<table>
<tr><th>Name</th><th>Contact</th><th>Status</th><th>Error</th><th>Time</th></tr>
<?php foreach($recent as $r): ?>
<tr>
<td><?=htmlspecialchars($r['name'])?></td>
<td><?=htmlspecialchars($r['contact'])?></td>
<td style="color:<?= $r['status']=='Success'?'green':'red' ?>"><?=htmlspecialchars($r['status'])?></td>
<td><?=htmlspecialchars($r['error'])?></td>
<td><?=htmlspecialchars($r['sent_at'])?></td>
</tr>
<?php endforeach; ?>
</table>
</div>
</div>

<script>
const daily = <?=json_encode($daily)?>;
const monthly = <?=json_encode($monthly)?>;
const yearly = <?=json_encode($yearly)?>;

function drawBar(id,data,labelKey,title){
const ctx=document.getElementById(id);
new Chart(ctx,{type:'bar',data:{labels:data.map(r=>r[labelKey]),datasets:[{label:'Success',data:data.map(r=>parseInt(r.success)),backgroundColor:'#28a745'},{label:'Failed',data:data.map(r=>parseInt(r.failed)),backgroundColor:'#d93025'}]},options:{responsive:true,plugins:{title:{display:true,text:title}}}});
}

drawBar('dailyChart',daily,'day','Daily Sent');
drawBar('monthlyChart',monthly,'month','Monthly Sent');
drawBar('yearlyChart',yearly,'year','Yearly Sent');
</script>
</body>
</html>
