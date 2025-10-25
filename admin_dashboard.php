<?php
session_start();
require 'db.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { header("Location: login.php"); exit; }

$users_q = $conn->query("SELECT username, COUNT(*) as total, SUM(status='Success') as success, SUM(status='Failed') as failed FROM message_logs GROUP BY username ORDER BY total DESC");
$daily_q = $conn->query("SELECT DATE(timestamp) as day, SUM(status='Success') as success, SUM(status='Failed') as failed FROM message_logs GROUP BY day ORDER BY day DESC LIMIT 30");
$daily = $daily_q->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Admin Analytics</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
  body{font-family:'Segoe UI',sans-serif;background:#f0f4f3;padding:30px}
  .container{max-width:1100px;margin:auto}
  .card{background:#fff;padding:20px;border-radius:12px;margin-bottom:18px;box-shadow:0 6px 18px rgba(0,0,0,0.06)}
  h2{color:#2d7a46;text-align:center}
  table{width:100%;border-collapse:collapse;margin-top:12px}
  th,td{padding:8px;border:1px solid #e6e6e6;text-align:center}
  th{background:#2d7a46;color:#fff}
  a.btn{background:#28a745;color:#fff;padding:8px 12px;border-radius:8px;text-decoration:none}
</style>
</head>
<body>
<div class="container">
  <div class="card">
    <h2>📈 Admin Analytics</h2>
    <div style="text-align:center"><a class="btn" href="manage_users.php">Manage Users</a> <a class="btn" href="logout.php">Logout</a></div>
  </div>

  <div class="card"><h3>Global Daily Trend</h3><canvas id="globalChart"></canvas></div>

  <div class="card">
    <h3>Per-User Summary</h3>
    <table>
      <tr><th>User</th><th>Total</th><th>Success</th><th>Failed</th><th>View</th></tr>
      <?php while($u = $users_q->fetch_assoc()): ?>
      <tr>
        <td><?=htmlspecialchars($u['username'])?></td>
        <td><?=intval($u['total'])?></td>
        <td style="color:green"><?=intval($u['success'])?></td>
        <td style="color:red"><?=intval($u['failed'])?></td>
        <td><a class="btn" href="dashboard.php?user=<?=urlencode($u['username'])?>">View</a></td>
      </tr>
      <?php endwhile; ?>
    </table>
  </div>
</div>

<script>
const globalData = <?= json_encode($daily) ?>;
new Chart(document.getElementById('globalChart'), {
  type: 'line',
  data: {
    labels: globalData.map(r => r.day).reverse(),
    datasets: [
      { label: 'Success', data: globalData.map(r => parseInt(r.success)).reverse(), borderColor:'#28a745', fill:false },
      { label: 'Failed', data: globalData.map(r => parseInt(r.failed)).reverse(), borderColor:'#d93025', fill:false }
    ]
  },
  options: { responsive:true, plugins:{title:{display:true,text:'Daily Message Trends (All Users)'}}}
});
</script>
</body>
</html>
