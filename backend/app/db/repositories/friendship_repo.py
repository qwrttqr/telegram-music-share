from sqlalchemy import select, or_, and_
from sqlalchemy.ext.asyncio import AsyncSession

from db.models import Friendship


class FriendshipRepo:
    def __init__(self, database_session: AsyncSession):
        self.db_session = database_session

    async def get_by_user_id_friend_id(
        self,
        friend_id: int,
        user_id: int
    ) -> Friendship | None:
        stmt = select(Friendship).where(
            or_(and_(Friendship.user_1_id == friend_id, Friendship.user_2_id == user_id),
                and_(Friendship.user_2_id == friend_id, Friendship.user_1_id == user_id)))

        return (await self.db_session.execute(stmt)).scalar_one_or_none()

    async def add(
            self,
            friendship: Friendship
    ) -> None:
        self.db_session.add(friendship)

    async def delete(
            self,
            friendship: Friendship
    ):
        await self.db_session.delete(friendship)