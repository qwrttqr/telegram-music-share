from fastapi import APIRouter, Depends

from db.models import User
from routing.pydantic.requests.posts.create_post import RequestCreatePost
from routing.pydantic.responses.posts.delete_post_response import ResponseDeletePost
from routing.pydantic.responses.posts.get_user_posts_response import ResponseUserPosts, UserPostEntity

from routing.pydantic.responses.posts.create_post_response import ResponseCreatePost
from routing.pydantic.responses.posts.posts_for_feed_response import FeedSchema, AuthorSchema, ResponseFeed
from services.posts_service import PostsService
from utils.dependencies import get_posts_service, get_current_user

router = APIRouter(prefix="/posts")


@router.get("/get_my_posts", tags=["posts"])
async def get_user_posts(
        page: int,
        per_page: int,
        telegram_user: User = Depends(get_current_user),
        posts_service: PostsService = Depends(get_posts_service)
) -> ResponseUserPosts:
    posts, total = await posts_service.get_user_posts(
        current_user_id=telegram_user.id,
        page=page,
        per_page=per_page,
    )
    posts = [UserPostEntity.model_validate(row) for row in posts]
    return ResponseUserPosts(posts=posts, total=total)


@router.get("/get_feed_posts", tags=["posts"])
async def get_feed_posts(
        page: int,
        per_page: int,
        telegram_user: User = Depends(get_current_user),
        posts_service: PostsService = Depends(get_posts_service)
) -> ResponseFeed:
    rows, total = await posts_service.get_feed_posts(
        current_user_id=telegram_user.id,
        page=page,
        per_page=per_page,
    )
    posts = [
        FeedSchema(
            id=row.post_id,
            vendor=row.vendor,
            title=row.title,
            comment=row.comment,
            link=row.link,
            created_at=row.created_at,
            author=AuthorSchema(
                id=row.author_id,
                telegram_id=row.telegram_id,
                tg_username=row.tg_username,
                photo_url=row.photo_url,
                first_name=row.first_name,
                last_name=row.last_name,
            ),
            seen=row.seen
        )
        for row in rows
    ]
    return ResponseFeed(posts=posts, total=total)


@router.post("/create_post", tags=["posts"])
async def create_post(
        data: RequestCreatePost,
        telegram_user: User = Depends(get_current_user),
        posts_service: PostsService = Depends(get_posts_service)
) -> ResponseCreatePost:
    res = await posts_service.create(
        current_user_id=telegram_user.id,
        **data.model_dump(),
    )
    return ResponseCreatePost(success=True) if res else ResponseCreatePost(success=False)


@router.delete("/delete_post/{post_id}", tags=["posts"])
async def delete_post(
        post_id: int,
        telegram_user: User = Depends(get_current_user),
        posts_service: PostsService = Depends(get_posts_service)
) -> ResponseDeletePost:
    res = await posts_service.delete(
        post_id=post_id,
        current_user_id=telegram_user.id,
    )
    if res:
        return ResponseDeletePost(success=True)
    else:
        return ResponseDeletePost(success=False)
