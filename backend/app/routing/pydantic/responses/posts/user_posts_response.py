from datetime import datetime
from pydantic import BaseModel, ConfigDict


class UserPostEntity(BaseModel):
    model_config = ConfigDict(from_attributes=True)
    id: int
    content_type: str
    title: str
    comment: str
    content: str
    created_at: datetime

class ResponseUserPosts(BaseModel):
    posts: list[UserPostEntity]
    total: int