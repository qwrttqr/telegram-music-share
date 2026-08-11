from pydantic import BaseModel


class ResponseFriendshipTokenResponse(BaseModel):
    success: bool
    token: str

