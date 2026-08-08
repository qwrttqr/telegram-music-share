from pydantic import BaseModel


class RequestAcceptFriendRequest(BaseModel):
    token: str
