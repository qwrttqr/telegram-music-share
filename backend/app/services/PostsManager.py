from sqlalchemy.ext.asyncio import AsyncSession
from sqlalchemy import select, func, Row, and_, exists, or_
from db.models import User, Post, PostSeen, Friendship
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
    async def get_feed_posts(
            current_user_id: int,
            page: int,
            per_page: int,
            database_session: AsyncSession
    ) -> tuple[list[Row], int]:
        """
        Returns all paginated posts for current used feed

        Args:
            current_user_id: telegram ID of used in touch
            page:
            per_page:
            database_session:
        Returns:
            List of paginated posts and total posts
        """
        friendship_subquery = (
            exists()
            .where(
                or_(
                    and_(Friendship.user_1_id == current_user_id, Friendship.user_2_id == Post.author),
                    and_(Friendship.user_2_id == current_user_id, Friendship.user_1_id == Post.author),
                )
            )
        )

        seen_subquery = (
            exists()
            .where(PostSeen.post_id == Post.id)
            .where(PostSeen.user_id == current_user_id)
        )

        base_filters = [friendship_subquery, Post.author != current_user_id]

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

        total = (await database_session.execute(count_stmt)).scalar_one()
        result = await database_session.execute(stmt)
        rows = result.all()

        unseen_ids = [row.post_id for row in rows if not row.seen]
        if unseen_ids:
            database_session.add_all(
                PostSeen(post_id=post_id, user_id=current_user_id) for post_id in unseen_ids
            )
            await database_session.commit()

        return list(rows), total

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
