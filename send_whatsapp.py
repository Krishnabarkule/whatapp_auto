#!/usr/bin/env python3
import sys, os, time, re, json, csv
from datetime import datetime

# third-party libs
try:
    import pandas as pd
    import pywhatkit as kit
    import pyautogui
    import requests
except ImportError:
    print("Missing dependencies. Run: pip3 install pandas pywhatkit pyautogui requests")
    sys.exit(1)

if len(sys.argv) < 8:
    print("Usage: python send_whatsapp.py <csv_file> <country_code> <delay> <image_path> <status_log_file> <message_template> <username>")
    sys.exit(1)

CSV_FILE, COUNTRY_CODE, DELAY_BETWEEN_MESSAGES, IMAGE_PATH, STATUS_LOG_FILE, MESSAGE_TEMPLATE, USERNAME = sys.argv[1:8]
DELAY_BETWEEN_MESSAGES = int(DELAY_BETWEEN_MESSAGES)
IMAGE_PATH = IMAGE_PATH if IMAGE_PATH else None

ROOT_DIR = os.path.dirname(__file__)
PROGRESS_FILE = os.path.join(ROOT_DIR, "progress.json")
STOP_FILE = os.path.join(ROOT_DIR, "stop.flag")
PAUSE_FILE = os.path.join(ROOT_DIR, "pause.flag")
LOG_API = "http://localhost/log_message.php"  # Adjust domain if needed

def write_progress(sent, total, logs, done=False):
    data = {"sent": sent, "total": total, "done": done, "log": logs[-200:]}
    try:
        with open(PROGRESS_FILE, "w") as f:
            json.dump(data, f)
    except Exception as e:
        print("Failed writing progress.json:", e)

def clean_phone(number, country_code="+91"):
    number = str(number or "").strip()
    number = re.sub(r'\D', '', number)
    if not number:
        return None
    if not number.startswith("+"):
        number = country_code + number.lstrip("0")
    return "+" + number if not number.startswith("+") else number

def close_browser_tab():
    try:
        time.sleep(3)
        pyautogui.hotkey("command", "w")  # macOS
        pyautogui.hotkey("ctrl", "w")     # fallback
    except:
        pass

def send_whatsapp(phone, message, image_path=None):
    try:
        if image_path and os.path.exists(image_path):
            kit.sendwhats_image(receiver=phone, img_path=image_path, caption=message, wait_time=15)
        else:
            kit.sendwhatmsg_instantly(phone_no=phone, message=message, wait_time=10, tab_close=True)
        time.sleep(4)
        close_browser_tab()
        return "Success", ""
    except Exception as e:
        close_browser_tab()
        return "Failed", str(e)

def log_to_php(payload):
    try:
        requests.post(LOG_API, json=payload, timeout=8)
    except:
        pass

# --- Load CSV ---
try:
    df = pd.read_csv(CSV_FILE)
except Exception as e:
    write_progress(0, 0, [f"❌ Failed to read CSV: {e}"], done=True)
    sys.exit(1)

if 'contact' not in df.columns:
    write_progress(0, 0, ["❌ CSV must contain 'contact' column"], done=True)
    sys.exit(1)
if 'name' not in df.columns:
    df['name'] = ""

total = len(df)
sent = 0
logs = []

write_progress(sent, total, logs)

# --- Sending loop ---
for idx, row in enumerate(df.itertuples(), start=1):
    if os.path.exists(STOP_FILE):
        logs.append("🛑 Stop detected. Exiting.")
        write_progress(sent, total, logs, done=True)
        try: os.remove(STOP_FILE)
        except: pass
        break

    while os.path.exists(PAUSE_FILE):
        logs.append("⏸ Paused. Waiting...")
        write_progress(sent, total, logs)
        time.sleep(3)

    phone = clean_phone(getattr(row, "contact", ""), COUNTRY_CODE)
    name = getattr(row, "name", "").strip() or "there"

    if not phone:
        logs.append(f"⚠️ Skipping invalid number: {getattr(row,'contact','')}")
        write_progress(sent, total, logs)
        log_to_php({"username": USERNAME, "name": name, "contact": getattr(row,'contact',''), "status":"Invalid", "error":"Invalid number"})
        continue

    message = MESSAGE_TEMPLATE.replace("{name}", name)
    logs.append(f"[{idx}/{total}] Sending to {name} ({phone})...")
    write_progress(sent, total, logs)

    status, error = send_whatsapp(phone, message, IMAGE_PATH)
    timestamp = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
    write_progress(sent, total, logs)
    log_to_php({"username": USERNAME, "name": name, "contact": phone, "status": status, "error": error})

    if status == "Success": sent += 1
    logs.append(f"{'✅' if status=='Success' else '❌'} {name} ({phone}) - {status}")
    write_progress(sent, total, logs)

    # Save to local CSV log
    try:
        with open(STATUS_LOG_FILE, 'a', newline='') as f:
            writer = csv.writer(f)
            writer.writerow([USERNAME, name, phone, status, error, timestamp])
    except: pass

    time.sleep(DELAY_BETWEEN_MESSAGES)

logs.append("🎉 All messages processed.")
write_progress(sent, total, logs, done=True)
