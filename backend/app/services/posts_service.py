from sqlalchemy.ext.asyncio import AsyncSession
from sqlalchemy import Row
from db.models.Post import TrackVendor
from db.repositories.posts_repo import PostsRepo


class PostsService:

    def __init__(self, posts_repo: PostsRepo, database_session: AsyncSession):
        self.posts_repo = posts_repo
        self.database_session = database_session

    async def get_user_posts(
            self,
            current_user_id: int,
            page: int,
            per_page: int,
    ) -> tuple[list[Row], int]:
        """
        Returns all paginated posts by current user

        Args:
            current_user_id: telegram ID of used in touch
            page:
            per_page:
        Returns:
            List of paginated posts and total posts
        """
        return await self.posts_repo.get_by_user_id(
            user_id=current_user_id, page=page, per_page=per_page
        )

    async def get_feed_posts(
            self,
            current_user_id: int,
            page: int,
            per_page: int,
    ) -> tuple[list[Row], int]:
        """
        Returns all paginated posts for current used feed

        Args:
            current_user_id: telegram ID of used in touch
            page:
            per_page:
        Returns:
            List of paginated posts and total posts
        """
        posts, total = await self.posts_repo.get_by_user_id_feed(
            user_id=current_user_id, page=page, per_page=per_page
        )
        if posts:
            await self.posts_repo.mark_as_seen(user_id=current_user_id, posts=posts)
            await self.database_session.commit()
        return posts, total

    async def create(
            self,
            current_user_id: int,
            title: str,
            comment: str,
            vendor: TrackVendor,
            link: str,
    ) -> bool:
        res = await self.posts_repo.create(
            user_id=current_user_id, title=title, comment=comment, vendor=vendor, link=link
        )
        await self.database_session.commit()
        return res

    async def delete(
            self,
            post_id: int,
            current_user_id: int,
    ) -> bool:
        res = await self.posts_repo.delete(post_id=post_id, user_id=current_user_id)
        await self.database_session.commit()
        return res
