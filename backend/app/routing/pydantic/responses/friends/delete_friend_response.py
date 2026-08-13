from pydantic import BaseModel


class ResponseDeleteFriend(BaseModel):
    success: bool
    message: str
