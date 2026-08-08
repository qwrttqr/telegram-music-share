from sqlalchemy.ext.asyncio import AsyncSession
from sqlalchemy import select, func, Row
from db.models import User, Post


class PostsManager:

    @staticmethod
    async def get_user_posts(
            tg_user_id: int,
            page: int,
            per_page: int,
            database_session: AsyncSession
    ) -> tuple[list[Row], int]:
        """
        Returns all paginated posts by current user

        Args:
            tg_user_id: telegram ID of used in touch
            page:
            per_page:
            database_session:
        Returns:
            List of paginated posts and total posts
        """
        stmt = (
            select(Post.id, Post.content_type, Post.title, Post.comment, Post.content, Post.created_at)
            .join(User, User.id == Post.author)
            .where(User.telegram_id == tg_user_id)
            .order_by(Post.created_at.desc())
            .limit(per_page)
            .offset(page * per_page)
        )

        count_stmt = (
            select(func.count())
            .select_from(Post)
            .join(User, User.id == Post.author)
            .where(User.telegram_id == tg_user_id)
        )
        total = (await database_session.execute(count_stmt)).scalar_one()
        result = await database_session.execute(stmt)
        posts = result.all()
        return list(posts), total
