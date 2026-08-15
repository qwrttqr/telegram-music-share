from pydantic import BaseModel

from db.models.Post import TrackVendor


class RequestCreatePost(BaseModel):
    vendor: TrackVendor
    link: str
    title: str
    comment: str
