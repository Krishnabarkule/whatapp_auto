<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>WhatsApp Message Automation</title>
  <style>
    body { font-family: Arial; background: #f4f4f4; padding: 40px; }
    form, .status-box { background: white; padding: 25px; border-radius: 10px; width: 700px; margin: auto; box-shadow: 0 0 8px rgba(0,0,0,0.1); }
    h2 { text-align: center; color: #2d7a46; }
    textarea, input, button { width: 100%; padding: 10px; margin: 10px 0; border-radius: 6px; border: 1px solid #ccc; }
    button { background: #28a745; color: white; font-size: 16px; border: none; cursor: pointer; }
    button:hover { background: #218838; }
    #progressBar { width: 100%; height: 20px; background: #eee; border-radius: 10px; margin-top: 10px; overflow: hidden; }
    #progress { height: 100%; width: 0%; background: #28a745; transition: width 0.5s; }
    #upload-status { text-align: center; font-weight: bold; }
    .control-buttons button { width: 32%; margin-right: 1%; }
  </style>
</head>
<body>

<h2>📲 WhatsApp Message Sender</h2>

<form id="uploadForm" enctype="multipart/form-data">
  <label>Upload CSV file (with 'name' and 'contact' columns):</label>
  <input type="file" name="csv_file" accept=".csv" required>

  <label>Country Code:</label>
  <input type="text" name="country_code" value="+91" required>

  <label>Delay Between Messages (seconds):</label>
  <input type="number" name="delay" value="15" required>

  <label>Image Path (optional):</label>
  <input type="text" name="image_path" placeholder="/path/to/image.jpg">

  <label>Status Log File Name:</label>
  <input type="text" name="status_log_file" value="status_log.csv" required>

  <label>Enter Message Template:</label>
  <textarea name="message_template" rows="12" required>🌿 *Hi {name}!* 

✨ SHREE ENTERPRISES AND DISTRIBUTORS ✨
आपल्या Home, Office किंवा Showroom ला द्या एक Stylish & Premium Look! 😍

🔥 Limited Time Offer! 🔥

💚 Green Grass Mat — फक्त ₹19.5 /sq.ft
🌀 Noodle Mat — फक्त ₹24.5 /sq.ft
🌾 Kata Turf Mat — फक्त ₹24.5 /sq.ft

🪄 High Quality • Long Lasting • Easy to Clean
👉 Perfect for Home, Office, Garden & Gym!

📞 More Info साठी Call किंवा WhatsApp करा
📲 7378326062

📍 Shop No. 02, DTC Complex, Chikalthana MIDC,
Ch. Sambhajinagar (431001)</textarea>

  <button type="submit">🚀 Upload & Start Sending</button>
</form>

<div id="upload-status"></div>

<div class="status-box" id="progressBox" style="display:none;">
  <h3>📊 Sending Progress</h3>
  <div id="progressBar"><div id="progress"></div></div>
  <p id="progressText">Waiting to start...</p>

  <div class="control-buttons">
    <button id="pauseBtn">⏸ Pause</button>
    <button id="resumeBtn">▶ Resume</button>
    <button id="stopBtn">⛔ Stop</button>
  </div>

  <pre id="consoleOutput" style="background:#000;color:#0f0;height:200px;overflow:auto;padding:10px;border-radius:8px;"></pre>
</div>

<script>
let paused = false, stopped = false;
let totalRecords = 0, sentCount = 0;

// Handle upload form
document.getElementById('uploadForm').addEventListener('submit', async (e) => {
  e.preventDefault();

  const formData = new FormData(e.target);
  document.getElementById('upload-status').innerText = '⏳ Uploading...';
  const res = await fetch('run_script.php', { method: 'POST', body: formData });
  const data = await res.json();

  if (data.success) {
    document.getElementById('upload-status').innerText = '✅ File uploaded successfully!';
    document.getElementById('progressBox').style.display = 'block';
    startMonitoring();
  } else {
    document.getElementById('upload-status').innerText = '❌ Upload failed.';
  }
});

// Control buttons
document.getElementById('pauseBtn').onclick = () => paused = true;
document.getElementById('resumeBtn').onclick = () => paused = false;
document.getElementById('stopBtn').onclick = async () => {
  stopped = true;
  await fetch('stop_process.php'); // signal to stop
};

// Poll progress
async function startMonitoring() {
  const interval = setInterval(async () => {
    if (stopped) { clearInterval(interval); return; }

    const res = await fetch('progress.json?_=' + Date.now());
    if (!res.ok) return;
    const prog = await res.json();

    totalRecords = prog.total;
    sentCount = prog.sent;

    const percent = totalRecords ? (sentCount / totalRecords * 100).toFixed(1) : 0;
    document.getElementById('progress').style.width = percent + '%';
    document.getElementById('progressText').innerText = `${sentCount} / ${totalRecords} sent (${percent}%)`;

    const consoleEl = document.getElementById('consoleOutput');
    consoleEl.textContent = prog.log.join("\n");
    consoleEl.scrollTop = consoleEl.scrollHeight;

    if (prog.done) clearInterval(interval);
  }, 3000);
}
</script>

</body>
</html>
