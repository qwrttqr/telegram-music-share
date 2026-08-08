import uuid

from sqlalchemy.exc import IntegrityError
from sqlalchemy.ext.asyncio import AsyncSession
from sqlalchemy import select, Row
from db.models import User, FriendsToken, Friendship


class UserManager:

    @staticmethod
    async def create_user(
            tg_user_id: int,
            tg_username: str | None,
            database_session: AsyncSession,
    ) -> bool:
        """
        Tries to create user in database

        Args:
            tg_user_id: telegram ID of used in touch
            tg_username: username of current telegram user
            database_session:
        Returns:
            True if user created and False if user already in database
        """
        stmt = select(User).where(User.telegram_id == tg_user_id)
        res = (await database_session.execute(stmt)).one_or_none()
        if not res:
            database_session.add(User(telegram_id=tg_user_id, tg_username=tg_username))
            await database_session.commit()
            return True
        return False

    @staticmethod
    async def get_friends_list(
            tg_user_id: int,
            page: int,
            per_page: int,
            database_session: AsyncSession
    ):
        pass

    @staticmethod
    async def create_friendship_token(
            creator_id: int,
            database_session: AsyncSession
    ) -> str | None:
        stmt = select(User).where(User.id == creator_id)
        user: User | None = (await database_session.execute(stmt)).scalar_one_or_none()
        token = str(uuid.uuid4())
        if user:
            friends_token = FriendsToken(token=token, creator_id=user.id)
            database_session.add(friends_token)
            await database_session.commit()
            return token
        else:
            print(f"User not found for {creator_id}")
            return None

    @staticmethod
    async def accept_friendship_invite(
            token: str,
            current_user_id: int,
            database_session: AsyncSession
    ) -> bool:
        stmt = select(FriendsToken).where(FriendsToken.token == token)
        result = await database_session.execute(stmt)
        friend_token = result.scalar_one_or_none()

        if friend_token is None:
            return False  # token not found / already used

        creator_id = friend_token.creator_id

        if creator_id == current_user_id:
            raise ValueError("Cannot accept your own invite")

        id_1, id_2 = min(creator_id, current_user_id), max(creator_id, current_user_id)
        friendship = Friendship(user_1_id=id_1, user_2_id=id_2)

        database_session.add(friendship)
        await database_session.delete(friend_token)  # invalidate token — one-time use

        try:
            await database_session.commit()
        except IntegrityError:
            await database_session.rollback()
            raise ValueError("Already friends")

        return True
