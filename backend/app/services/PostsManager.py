from sqlalchemy.ext.asyncio import AsyncSession
from sqlalchemy import select, func, Row, and_
from db.models import User, Post
from db.models.Post import TrackVendor


class PostsManager:

    @staticmethod
    async def get_user_posts(
            current_user_id: int,
            page: int,
            per_page: int,
            database_session: AsyncSession
    ) -> tuple[list[Row], int]:
        """
        Returns all paginated posts by current user

        Args:
            current_user_id: telegram ID of used in touch
            page:
            per_page:
            database_session:
        Returns:
            List of paginated posts and total posts
        """
        stmt = (
            select(Post.id, Post.vendor, Post.title, Post.comment, Post.link, Post.created_at)
            .join(User, User.id == Post.author)
            .where(User.id == current_user_id)
            .order_by(Post.created_at.desc())
            .limit(per_page)
            .offset(page * per_page)
        )

        count_stmt = (
            select(func.count())
            .select_from(Post)
            .join(User, User.id == Post.author)
            .where(User.id == current_user_id)
        )
        total = (await database_session.execute(count_stmt)).scalar_one()
        result = await database_session.execute(stmt)
        posts = result.all()
        return list(posts), total

    @staticmethod
    async def create_post(
            current_user_id: int,
            title: str,
            comment: str,
            vendor: TrackVendor,
            link: str,
            database_session: AsyncSession
    ) -> bool:
        post = Post(
            author=current_user_id,
            vendor=vendor,
            link=link,
            title=title,
            comment=comment,
        )
        database_session.add(post)
        await database_session.commit()
        return True

    @staticmethod
    async def delete_post(
            post_id: int,
            current_user_id: int,
            database_session: AsyncSession
    ) -> bool:
        stmt = select(Post).where(and_(Post.author == current_user_id, Post.id == post_id))
        post = (await database_session.execute(stmt)).scalars().first()
        if post:
            await database_session.delete(post)
            await database_session.commit()
            return True

        return False
