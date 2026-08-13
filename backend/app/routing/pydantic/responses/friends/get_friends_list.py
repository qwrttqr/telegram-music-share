from pydantic import BaseModel, ConfigDict


class FriendsListItem(BaseModel):
    model_config = ConfigDict(from_attributes=True)
    id: int
    telegram_id: int
    tg_username: str | None
    photo_url: str | None
    first_name: str | None
    last_name: str | None

class ResponseFriendsList(BaseModel):
    friends: list[FriendsListItem]