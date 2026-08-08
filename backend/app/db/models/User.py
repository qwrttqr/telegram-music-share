# User.py
from typing import TYPE_CHECKING
from sqlalchemy.orm import Mapped, mapped_column, relationship
from sqlalchemy import BigInteger, String

from db.models.FriendsToken import FriendsToken
from .Base import Base

if TYPE_CHECKING:
    from .Post import Post
    from .PostSeen import PostSeen
    from .Friendship import Friendship


class User(Base):
    __tablename__ = "users"
    id: Mapped[int] = mapped_column(primary_key=True)
    telegram_id: Mapped[int] = mapped_column(BigInteger, nullable=False)
    tg_username: Mapped[str | None] = mapped_column(String(255), nullable=True)

    posts: Mapped[list["Post"]] = relationship(back_populates="author_user")
    seen_posts: Mapped[list["PostSeen"]] = relationship(back_populates="seen_by")
    friend_tokens: Mapped["FriendsToken"] = relationship(back_populates="token_creator")

    friendships_left: Mapped[list["Friendship"]] = relationship(
        foreign_keys="Friendship.user_1_id",
        primaryjoin="User.id == Friendship.user_1_id",
        viewonly=True,
    )
    friendships_right: Mapped[list["Friendship"]] = relationship(
        foreign_keys="Friendship.user_2_id",
        primaryjoin="User.id == Friendship.user_2_id",
        viewonly=True,
    )

    @property
    def friends(self) -> list["User"]:
        return [f.right_friend for f in self.friendships_left] + \
               [f.left_friend for f in self.friendships_right]