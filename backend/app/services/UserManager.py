import uuid
from typing import Optional

from sqlalchemy.exc import IntegrityError
from sqlalchemy.ext.asyncio import AsyncSession
from sqlalchemy import select, and_, or_, Row
from db.models import User, FriendsToken, Friendship


class UserManager:

    @staticmethod
    async def create_user(
            tg_user_id: int,
            tg_username: str | None,
            tg_first_name: str,
            tg_last_name: str,
            tg_photo_url: str,
            database_session: AsyncSession,
    ) -> bool:
        """
        Tries to create user in database

        Args:
            tg_user_id: telegram ID of used in touch
            tg_username: username of current telegram user
            tg_first_name:
            tg_last_name:
            tg_photo_url:
            database_session:
        Returns:
            True if user created and False if user already in database
        """
        stmt = select(User).where(User.telegram_id == tg_user_id)
        res = (await database_session.execute(stmt)).one_or_none()
        if not res:
            database_session.add(
                User(telegram_id=tg_user_id,
                     tg_username=tg_username,
                     first_name=tg_first_name,
                     last_name=tg_last_name,
                     photo_url=tg_photo_url))
            await database_session.commit()
            return True
        return False

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

    @staticmethod
    async def get_friends_list(
            current_user_id: int,
            database_session: AsyncSession
    ) -> list[User]:
        stmt = (select(User).join(Friendship, or_(
            and_(Friendship.user_1_id == current_user_id, Friendship.user_2_id == User.id),
            and_(Friendship.user_2_id == current_user_id, Friendship.user_1_id == User.id)
        )))
        result = await database_session.execute(stmt)
        return list(result.scalars().all())

    @staticmethod
    async def delete_from_friends(
            friend_id: int,
            current_user_id: int,
            database_session: AsyncSession
    ) -> bool:
        stmt = select(Friendship).where(
            or_(and_(Friendship.user_1_id == friend_id, Friendship.user_2_id == current_user_id),
                and_(Friendship.user_2_id == friend_id, Friendship.user_1_id == current_user_id)))
        result = await database_session.execute(stmt)
        friendship: Optional[Friendship] = result.scalar_one_or_none()

        if friendship is None:
            return False

        await database_session.delete(friendship)

        try:
            await database_session.commit()
        except IntegrityError:
            await database_session.rollback()
            raise ValueError("Couldn't delete friendship connection")

        return True
