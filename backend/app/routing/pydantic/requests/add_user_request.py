from pydantic import BaseModel

class RequestCreateUser(BaseModel):
    telegram_id: int
    tg_username: str | None = None