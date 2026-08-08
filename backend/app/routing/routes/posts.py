from fastapi import APIRouter, Depends, HTTPException
from sqlalchemy.ext.asyncio import AsyncSession

from core.engine import get_db_session
from routing.pydantic.responses.posts.user_posts_response import ResponseUserPosts, UserPostEntity
from services.PostsManager import PostsManager
from utils.get_current_user import get_current_user

router = APIRouter(prefix="/posts")


@router.get("/get_user_posts", tags=["posts"])
async def add_user(
        page: int,
        per_page: int,
        telegram_user: dict = Depends(get_current_user),
        database_session: AsyncSession = Depends(get_db_session)
) -> ResponseUserPosts:
    try:
        posts, total = await PostsManager.get_user_posts(tg_user_id=telegram_user["id"], page=page, per_page=per_page, database_session=database_session)
        posts = [UserPostEntity.model_validate(row) for row in posts]
        return ResponseUserPosts(posts = posts, total=total)
    except Exception as e:
        print(e)
        raise HTTPException(status_code=500, detail="Unexpected error on server")
