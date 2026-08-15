from fastapi import APIRouter, Depends
from sqlalchemy.ext.asyncio import AsyncSession

from db.core.engine import get_db_session
from db.models import User
from routing.pydantic.requests.posts.create_post import RequestCreatePost
from routing.pydantic.responses.posts.get_user_posts_response import ResponseUserPosts, UserPostEntity

from routing.pydantic.responses.posts.create_post_response import ResponseCreatePost
from services.PostsManager import PostsManager
from utils.get_current_user import get_current_user

router = APIRouter(prefix="/posts")


@router.get("/get_my_posts", tags=["posts"])
async def get_user_posts(
        page: int,
        per_page: int,
        telegram_user: User = Depends(get_current_user),
        database_session: AsyncSession = Depends(get_db_session)
) -> ResponseUserPosts:
    posts, total = await PostsManager.get_user_posts(
        current_user_id=telegram_user.id,
        page=page,
        per_page=per_page,
        database_session=database_session
    )
    posts = [UserPostEntity.model_validate(row) for row in posts]
    return ResponseUserPosts(posts=posts, total=total)


@router.post("/create_post", tags=["posts"])
async def create_post(
        data: RequestCreatePost,
        telegram_user: User = Depends(get_current_user),
        database_session: AsyncSession = Depends(get_db_session)
) -> ResponseCreatePost:
    res = await PostsManager.create_post(
        current_user_id=telegram_user.id,
        **data.model_dump(),
        database_session=database_session
    )
    return ResponseCreatePost(success=True) if res else ResponseCreatePost(success=False)
