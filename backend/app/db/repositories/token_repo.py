from sqlalchemy import select
from sqlalchemy.ext.asyncio import AsyncSession

from db.models import FriendsToken


class FriendshipTokensRepo:
    def __init__(self, database_session: AsyncSession):
        self.db_session = database_session

    async def get_by_token(
            self,
            token: str
    ) -> FriendsToken | None:
        stmt = select(FriendsToken).where(FriendsToken.token == token)
        result = await self.db_session.execute(stmt)
        return result.scalar_one_or_none()

    async def add(
            self,
            token: FriendsToken
    ) -> None:
        self.db_session.add(token)

    async def delete(
            self,
            token: FriendsToken
    ):
        await self.db_session.delete(token)


