from pydantic import BaseModel


class RequestDeleteFromFriends(BaseModel):
    friend_id: int
