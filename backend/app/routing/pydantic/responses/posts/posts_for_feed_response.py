from pydantic import BaseModel
from datetime import datetime


class AuthorSchema(BaseModel):
    id: int
    telegram_id: int
    tg_username: str | None
    photo_url: str | None
    first_name: str | None
    last_name: str | None


class FeedSchema(BaseModel):
    id: int
    vendor: str
    title: str
    comment: str
    link: str
    created_at: datetime
    author: AuthorSchema
    seen: bool

class ResponseFeed(BaseModel):
    posts: list[FeedSchema]
    total: int