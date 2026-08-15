from datetime import datetime
from pydantic import BaseModel, ConfigDict

from db.models.Post import TrackVendor


class UserPostEntity(BaseModel):
    model_config = ConfigDict(from_attributes=True)
    id: int
    vendor: TrackVendor
    title: str
    comment: str
    link: str
    created_at: datetime

class ResponseUserPosts(BaseModel):
    posts: list[UserPostEntity]
    total: int