from fastapi import Header, HTTPException, Depends
from sqlalchemy import select
from sqlalchemy.ext.asyncio import AsyncSession
from db.core.engine import get_db_session
from db.models import User
from db.repositories.friendship_repo import FriendshipRepo
from db.repositories.posts_repo import PostsRepo
from db.repositories.token_repo import FriendshipTokenRepo
from db.repositories.user_repo import UsersRepo
from services.friends_token_service import FriendsTokenService
from services.posts_service import PostsService
from services.user_service import UserService
from utils.validation import validate_init_data
from dotenv import load_dotenv

load_dotenv()


def get_users_repo(database_session: AsyncSession = Depends(get_db_session)) -> UsersRepo:
    return UsersRepo(database_session=database_session)


def get_friends_token_repo(database_session: AsyncSession = Depends(get_db_session)) -> FriendshipTokenRepo:
    return FriendshipTokenRepo(database_session=database_session)


def get_posts_repo(database_session: AsyncSession = Depends(get_db_session)) -> PostsRepo:
    return PostsRepo(database_session=database_session)


def get_friendship_repo(database_session: AsyncSession = Depends(get_db_session)) -> FriendshipRepo:
    return FriendshipRepo(database_session=database_session)


def get_friends_token_service(
        users_repo: UsersRepo = Depends(get_users_repo),
        token_repo: FriendshipTokenRepo = Depends(get_friends_token_repo),
        database_session: AsyncSession = Depends(get_db_session),
) -> FriendsTokenService:
    return FriendsTokenService(users_repo=users_repo, token_repo=token_repo, database_session=database_session)


def get_posts_service(
        posts_repo: PostsRepo = Depends(get_posts_repo),
        database_session: AsyncSession = Depends(get_db_session),
) -> PostsService:
    return PostsService(posts_repo=posts_repo, database_session=database_session)


def get_user_service(
        users_repo: UsersRepo = Depends(get_users_repo),
        token_repo: FriendshipTokenRepo = Depends(get_friends_token_repo),
        friendship_repo: FriendshipRepo = Depends(get_friendship_repo),
        database_session: AsyncSession = Depends(get_db_session)
) -> UserService:
    return UserService(
        users_repo=users_repo,
        token_repo=token_repo,
        friendship_repo=friendship_repo,
        database_session=database_session
    )


async def get_current_user(
        x_telegram_init_data: str = Header(...),
        users_repo: UsersRepo = Depends(get_users_repo)
) -> User:
    parsed = validate_init_data(x_telegram_init_data)
    if parsed is None:
        raise HTTPException(status_code=401, detail="Invalid Telegram init data")

    telegram_user: dict = parsed["user"]
    user = await users_repo.get_by_tg_id(tg_user_id=telegram_user["id"])
    if user is None:
        raise HTTPException(status_code=404, detail="User not registered")

    return user
