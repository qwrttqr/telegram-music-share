import hashlib
import hmac
import os
import json
from dotenv import load_dotenv
from fastapi import HTTPException, Header
from urllib.parse import parse_qsl

load_dotenv()

def validate_init_data(x_telegram_init_data: str = Header(..., alias="X-Telegram-Init-Data")) -> dict | None:
    bot_token = os.environ["BOT_TOKEN"]
    parsed = dict(parse_qsl(x_telegram_init_data, strict_parsing=True))
    received_hash = parsed.pop("hash", None)
    if not received_hash:
        return None

    data_check_string = "\n".join(f"{k}={v}" for k,v in sorted(parsed.items()))

    secret = hmac.new(b"WebAppData", bot_token.encode(), hashlib.sha256).digest()
    computed_hash = hmac.new(secret, data_check_string.encode(), hashlib.sha256).hexdigest()

    if not hmac.compare_digest(computed_hash, received_hash):
        raise HTTPException(status_code=403, detail="I see u trying to forge request)")
    parsed["user"] = json.loads(parsed["user"])
    return parsed