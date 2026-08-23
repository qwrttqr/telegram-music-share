from sqlalchemy.exc import IntegrityError
from sqlalchemy.ext.asyncio import AsyncSession

from db.models import User, Friendship
from db.repositories.friendship_repo import FriendshipRepo
from db.repositories.token_repo import FriendshipTokenRepo
from db.repositories.user_repo import UsersRepo


class UserService:

    def __init__(
            self,
            users_repo: UsersRepo,
            token_repo: FriendshipTokenRepo,
            friendship_repo: FriendshipRepo,
            database_session: AsyncSession
    ):
        self.users_repo = users_repo
        self.token_repo = token_repo
        self.friendship_repo = friendship_repo
        self.database_session = database_session

    async def create_user(
            self,
            tg_user_id: int,
            tg_username: str | None,
            tg_first_name: str,
            tg_last_name: str,
            tg_photo_url: str,
    ) -> bool:
        """
        Tries to create user in database

        Args:
            tg_user_id: telegram ID of used in touch
            tg_username: username of current telegram user
            tg_first_name:
            tg_last_name:
            tg_photo_url:
        Returns:
            True if user created and False if user already in database
        """
        existing = await self.users_repo.get_by_tg_id(tg_user_id=tg_user_id)
        if existing:
            return False

        await self.users_repo.add(User(
            telegram_id=tg_user_id,
            tg_username=tg_username,
            first_name=tg_first_name,
            last_name=tg_last_name,
            photo_url=tg_photo_url
        ))
        await self.database_session.commit()
        return True

    async def accept_friendship_invite(
            self,
            token: str,
            current_user_id: int,
    ) -> bool:
        friend_token = await self.token_repo.get_by_token(token=token)

        if friend_token is None:
            return False  # token not found / already used

        creator_id = friend_token.creator_id

        if creator_id == current_user_id:
            raise ValueError("Cannot accept your own invite")

        id_1, id_2 = min(creator_id, current_user_id), max(creator_id, current_user_id)
        await self.friendship_repo.add(Friendship(user_1_id=id_1, user_2_id=id_2))

        await self.token_repo.delete(friend_token)
        try:
            await self.database_session.commit()
        except IntegrityError:
            await self.database_session.rollback()
            raise ValueError("Already friends")

        return True

    async def get_friends_list(
            self,
            current_user_id: int
    ) -> list[User]:
        return await self.users_repo.get_friends_by_user_id(user_id=current_user_id)

    async def delete_from_friends(
            self,
            friend_id: int,
            current_user_id: int,
    ) -> bool:
        friendship = await self.friendship_repo.get_by_user_id_friend_id(
            user_id=current_user_id,
            friend_id=friend_id
        )
        if friendship is None:
            return False

        await self.friendship_repo.delete(friendship)

        try:
            await self.database_session.commit()
        except IntegrityError:
            await self.database_session.rollback()
            raise ValueError("Couldn't delete friendship connection")

        return True
