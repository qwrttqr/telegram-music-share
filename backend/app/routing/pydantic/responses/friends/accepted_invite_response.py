from pydantic import BaseModel


class ResponseFriendRequestAccepted(BaseModel):
    success: bool
    message: str
