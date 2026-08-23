import uuid

from sqlalchemy.ext.asyncio import AsyncSession

from db.models import FriendsToken
from db.repositories.token_repo import FriendshipTokensRepo
from db.repositories.user_repo import UsersRepo


class FriendsTokenService:
    def __init__(
            self,
            token_repo: FriendshipTokensRepo,
            users_repo: UsersRepo,
            database_session: AsyncSession
    ):
        self.token_repo = token_repo
        self.users_repo = users_repo
        self.db_session = database_session

    async def create_friendship_token(self, creator_id: int) -> str:
        user = await self.users_repo.get_by_user_id(user_id=creator_id)
        token = str(uuid.uuid4())
        await self.token_repo.add(FriendsToken(token=token, creator_id=user.id))
        await self.db_session.commit()
        return token

