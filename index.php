<?php
session_start();
if(!isset($_SESSION['username'])){ header("Location: login.php"); exit; }
$username = $_SESSION['username'];
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>WhatsApp Sender</title>
<style>
body{font-family:Arial;background:#f4f4f4;padding:40px;}
form, .status-box{background:white;padding:25px;border-radius:10px;width:700px;margin:auto;box-shadow:0 0 8px rgba(0,0,0,0.1);}
h2{text-align:center;color:#2d7a46;}
textarea,input,button{width:100%;padding:10px;margin:10px 0;border-radius:6px;border:1px solid #ccc;}
button{background:#28a745;color:white;font-size:16px;border:none;cursor:pointer;}
button:hover{background:#218838;}
#progressBar{width:100%;height:20px;background:#eee;border-radius:10px;overflow:hidden;margin-top:10px;}
#progress{height:100%;width:0%;background:#28a745;transition:width 0.5s;}
#consoleOutput{background:#000;color:#0f0;height:200px;overflow:auto;padding:10px;border-radius:8px;}
.top-links{text-align:right;width:700px;margin:auto;margin-bottom:10px;}
.top-links a{margin-left:10px;text-decoration:none;color:#2d7a46;font-weight:600;}
</style>
</head>
<body>

<div class="top-links">
Welcome, <b><?=htmlspecialchars($username)?></b>
<a href="dashboard.php">Dashboard</a>
<?php if($_SESSION['role']==='admin'): ?><a href="admin_dashboard.php">Admin Analytics</a><?php endif; ?>
<a href="logout.php">Logout</a>
</div>

<h2>📲 WhatsApp Message Sender</h2>

<form id="uploadForm" enctype="multipart/form-data">
<label>Upload CSV (columns: name, contact)</label>
<input type="file" name="csv_file" accept=".csv" required>
<label>Country Code:</label>
<input type="text" name="country_code" value="+91" required>
<label>Delay (seconds):</label>
<input type="number" name="delay" value="15" required>
<label>Image Path (optional):</label>
<input type="text" name="image_path" placeholder="/path/to/image.jpg">
<label>Status Log File:</label>
<input type="text" name="status_log_file" value="status_log.csv" required>
<label>Message Template:</label>
<textarea name="message_template" rows="10" required>Hi {name}, Your message here.</textarea>
<input type="hidden" name="username" value="<?=htmlspecialchars($username)?>">
<button type="submit">🚀 Upload & Start Sending</button>
</form>

<div id="upload-status"></div>
<div class="status-box" id="progressBox" style="display:none;">
<h3>📊 Sending Progress</h3>
<div id="progressBar"><div id="progress"></div></div>
<p id="progressText">Waiting to start...</p>
<pre id="consoleOutput"></pre>
</div>

<script>
let paused=false, stopped=false;
document.getElementById('uploadForm').addEventListener('submit', async e=>{
    e.preventDefault();
    const formData = new FormData(e.target);
    document.getElementById('upload-status').innerText='⏳ Uploading...';
    const res = await fetch('run_script.php',{method:'POST',body:formData});
    const data = await res.json();
    if(data.success){
        document.getElementById('upload-status').innerText='✅ File uploaded successfully!';
        document.getElementById('progressBox').style.display='block';
        startMonitoring();
    } else {
        document.getElementById('upload-status').innerText='❌ Upload failed: '+(data.error||'');
    }
});

async function startMonitoring(){
    const interval = setInterval(async()=>{
        if(stopped){ clearInterval(interval); return; }
        const res = await fetch('progress.json?_='+Date.now());
        if(!res.ok) return;
        const prog = await res.json();
        const total = prog.total || 0;
        const sent = prog.sent || 0;
        const percent = total? (sent/total*100).toFixed(1):0;
        document.getElementById('progress').style.width = percent+'%';
        document.getElementById('progressText').innerText=`${sent} / ${total} sent (${percent}%)`;
        document.getElementById('consoleOutput').textContent = (prog.log||[]).join("\n");
        if(prog.done) clearInterval(interval);
    },1000);
}
</script>

</body>
</html>
