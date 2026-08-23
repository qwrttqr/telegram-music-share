from sqlalchemy import select, or_, and_
from sqlalchemy.ext.asyncio import AsyncSession

from db.models import User, Friendship


class UsersRepo:
    def __init__(self, database_session: AsyncSession):
        self.db_session = database_session

    async def get_by_tg_id(
            self,
            tg_user_id: int,
    ) -> User | None:
        stmt = select(User).where(User.telegram_id == tg_user_id)
        return (await self.db_session.execute(stmt)).scalar_one_or_none()

    async def get_by_user_id(
            self,
            user_id: int
    ) -> User:
        stmt = select(User).where(User.id == user_id)
        return (await self.db_session.execute(stmt)).scalar_one()

    async def get_friends_by_user_id(
            self,
            user_id: int
    ) -> list[User]:
        stmt = (select(User).join(Friendship, or_(
            and_(Friendship.user_1_id == user_id, Friendship.user_2_id == User.id),
            and_(Friendship.user_2_id == user_id, Friendship.user_1_id == User.id)
        )))

        result = await self.db_session.execute(stmt)
        return list(result.scalars().all())

    async def add(
            self,
            user: User
    ) -> None:
        self.db_session.add(user)
