from pydantic import BaseModel


class ResponseDeletePost(BaseModel):
    success: bool

