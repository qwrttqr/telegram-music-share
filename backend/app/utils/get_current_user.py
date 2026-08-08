import json
import os

from fastapi import Header, HTTPException, Depends
from sqlalchemy import select
from sqlalchemy.ext.asyncio import AsyncSession
from core.engine import get_db_session
from db.models import User
from utils.validation import validate_init_data
from dotenv import load_dotenv

load_dotenv()

async def get_current_user(
    x_telegram_init_data: str = Header(...),
    database_session: AsyncSession = Depends(get_db_session),
) -> User | None:
    parsed = validate_init_data(x_telegram_init_data)
    if parsed is None:
        raise HTTPException(status_code=401, detail="Invalid Telegram init data")

    telegram_user = parsed["user"]
    stmt = select(User).where(User.telegram_id == telegram_user["id"])
    result = await database_session.execute(stmt)
    user = result.scalar_one_or_none()
    if user is None:
        raise HTTPException(status_code=404, detail="User not registered")

    return user