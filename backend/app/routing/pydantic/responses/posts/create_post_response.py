from pydantic import BaseModel


class ResponseCreatePost(BaseModel):
    success: bool

