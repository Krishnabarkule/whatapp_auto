from flask import Flask, jsonify
import subprocess

app = Flask(__name__)

@app.route("/")
def home():
    return "🚀 WhatsApp Auto is running successfully!"

@app.route("/run")
def run_script():
    try:
        subprocess.run(["python3", "send_whatsapp.py"], check=True)
        return jsonify({"status": "success", "message": "Python script executed successfully."})
    except subprocess.CalledProcessError as e:
        return jsonify({"status": "error", "message": str(e)})

if __name__ == "__main__":
    app.run(host="0.0.0.0", port=10000)
