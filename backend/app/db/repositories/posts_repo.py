from sqlalchemy import select, func, Row, exists, and_, or_
from sqlalchemy.ext.asyncio import AsyncSession

from db.models import Post, User, Friendship, PostSeen
from db.models.Post import TrackVendor


class PostsRepo:
    def __init__(self, database_session: AsyncSession):
        self.db_session = database_session

    async def get_by_user_id(
            self,
            user_id: int,
            page: int,
            per_page: int
    ) -> tuple[list[Row], int]:
        stmt = (
            select(Post.id, Post.vendor, Post.title, Post.comment, Post.link, Post.created_at)
            .join(User, User.id == Post.author)
            .where(User.id == user_id)
            .order_by(Post.created_at.desc())
            .limit(per_page)
            .offset(page * per_page)
        )

        count_stmt = (
            select(func.count())
            .select_from(Post)
            .join(User, User.id == Post.author)
            .where(User.id == user_id)
        )
        total = (await self.db_session.execute(count_stmt)).scalar_one()
        result = await self.db_session.execute(stmt)
        posts = result.all()
        return list(posts), total

    async def get_by_user_id_feed(
            self,
            user_id: int,
            page: int,
            per_page: int
    ) -> tuple[list[Row], int]:
        friendship_subquery = (
            exists()
            .where(
                or_(
                    and_(Friendship.user_1_id == user_id, Friendship.user_2_id == Post.author),
                    and_(Friendship.user_2_id == user_id, Friendship.user_1_id == Post.author),
                )
            )
        )

        seen_subquery = (
            exists()
            .where(PostSeen.post_id == Post.id)
            .where(PostSeen.user_id == user_id)
        )

        base_filters = [friendship_subquery, Post.author != user_id]

        stmt = (
            select(
                User.id.label("author_id"),
                User.telegram_id,
                User.tg_username,
                User.photo_url,
                User.first_name,
                User.last_name,
                Post.id.label("post_id"),
                Post.vendor,
                Post.title,
                Post.comment,
                Post.link,
                Post.created_at,
                seen_subquery.label("seen"),
            )
            .join(User, User.id == Post.author)
            .where(*base_filters)
            .order_by(Post.created_at.desc())
            .limit(per_page)
            .offset(page * per_page)
        )

        count_stmt = (
            select(func.count())
            .select_from(Post)
            .join(User, User.id == Post.author)
            .where(*base_filters)
        )

        total = (await self.db_session.execute(count_stmt)).scalar_one()
        result = await self.db_session.execute(stmt)
        rows = result.all()

        return list(rows), total

    async def mark_as_seen(
            self,
            user_id: int,
            posts: list[Row]
    ) -> None:
        self.db_session.add_all(
            PostSeen(post_id=post.id, user_id=user_id) for post in posts
        )

    async def create(
            self,
            user_id: int,
            title: str,
            comment: str,
            vendor: TrackVendor,
            link: str
    ) -> bool:
        post = Post(
            author=user_id,
            vendor=vendor,
            link=link,
            title=title,
            comment=comment,
        )
        self.db_session.add(post)

        return True

    async def delete(
            self,
            post_id: int,
            user_id: int
    ) -> bool:
        stmt = select(Post).where(and_(Post.author == user_id, Post.id == post_id))
        post = (await self.db_session.execute(stmt)).scalars().first()
        if post:
            await self.db_session.delete(post)
            return True

        return False
