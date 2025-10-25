import sys
import pandas as pd 
import pywhatkit as kit 
import time 
import re 
import pyautogui 
from datetime import datetime

# Accept command-line args from PHP
if len(sys.argv) < 7:
    print("Usage: python send_whatsapp.py <csv_file> <country_code> <delay> <image_path> <status_log_file> <message_template>")
    sys.exit(1)

CSV_FILE = sys.argv[1]
COUNTRY_CODE = sys.argv[2]
DELAY_BETWEEN_MESSAGES = int(sys.argv[3])
IMAGE_PATH = sys.argv[4] if sys.argv[4] != "" else None
STATUS_LOG_FILE = sys.argv[5]
MESSAGE_TEMPLATE = sys.argv[6]




def clean_phone_number(number, country_code="+91"):
    """Clean and format the phone number to WhatsApp international format."""
    number = str(number).strip()
    number = re.sub(r'\D', '', number)  # Remove everything except digits

    if not number:
        return None

    if not number.startswith(country_code.replace("+", "")):
        number = number.lstrip("0")
        number = country_code + number
    else:
        number = "+" + number

    return number


def close_browser_tab():
    """Close the Chrome/WhatsApp Web tab."""
    try:
        time.sleep(5)  # allow WhatsApp Web to finish sending
        pyautogui.hotkey("command", "w")  # macOS
        pyautogui.hotkey("ctrl", "w")     # Windows/Linux
        print("🪟 Closed WhatsApp tab.")
    except Exception as e:
        print(f"⚠️ Could not close tab: {e}")


def send_whatsapp_message(phone, message, image_path=None):
    """Send a WhatsApp message with image and handle exceptions properly."""
    try:
        if image_path:
            kit.sendwhats_image(
                receiver=phone,
                img_path=image_path,
                caption=message,
                wait_time=15
            )
        else:
            kit.sendwhatmsg_instantly(
                phone_no=phone,
                message=message,
                wait_time=10,
                tab_close=True
            )

        print(f"✅ Message sent successfully to {phone}")
        time.sleep(5)
        close_browser_tab()
        return "Success", None

    except Exception as e:
        print(f"❌ Failed to send message to {phone}: {e}")
        close_browser_tab()
        return "Failed", str(e)


def log_status(results):
    """Write results to CSV log file (append if already exists)."""
    df_log = pd.DataFrame(results)
    try:
        existing = pd.read_csv(STATUS_LOG_FILE)
        df_log = pd.concat([existing, df_log], ignore_index=True)
    except FileNotFoundError:
        pass

    df_log.to_csv(STATUS_LOG_FILE, index=False)
    print(f"\n🧾 Log saved/updated at '{STATUS_LOG_FILE}'")


def main():
    df = pd.read_csv(CSV_FILE)

    if 'contact' not in df.columns:
        raise ValueError("CSV must contain a 'contact' column")
    if 'name' not in df.columns:
        df['name'] = ""  # fallback if missing

    results = []

    for i, row in enumerate(df.itertuples(), 1):
        raw_contact = getattr(row, "contact")
        name = getattr(row, "name", "")
        phone = clean_phone_number(raw_contact, COUNTRY_CODE)

        if not phone:
            print(f"⚠️ Skipping invalid number: {raw_contact}")
            results.append({
                "name": name,
                "contact": raw_contact,
                "status": "Invalid",
                "error": "Invalid number format",
                "timestamp": datetime.now().strftime("%Y-%m-%d %H:%M:%S")
            })
            continue

        message = MESSAGE_TEMPLATE.format(name=name.strip() or "there")

        print(f"\n[{i}/{len(df)}] Sending to {name} ({phone})...")
        status, error = send_whatsapp_message(phone, message, IMAGE_PATH)

        results.append({
            "name": name,
            "contact": phone,
            "status": status,
            "error": error if error else "",
            "timestamp": datetime.now().strftime("%Y-%m-%d %H:%M:%S")
        })

        time.sleep(DELAY_BETWEEN_MESSAGES)

    log_status(results)
    print("\n🎉 All messages processed! Check status_log.csv for details.")


if __name__ == "__main__":
    main()
