from typing import TYPE_CHECKING

from sqlalchemy import ForeignKey
from sqlalchemy.orm import Mapped, mapped_column, relationship
from .Base import Base

if TYPE_CHECKING:
    from .User import User


class Friendship(Base):
    __tablename__ = "friendships"

    user_1_id: Mapped[int] = mapped_column(
        ForeignKey("users.id", ondelete="CASCADE"), primary_key=True
    )
    user_2_id: Mapped[int] = mapped_column(
        ForeignKey("users.id", ondelete="CASCADE"), primary_key=True
    )

    left_friend: Mapped["User"] = relationship(
        foreign_keys=[user_1_id],
        primaryjoin="Friendship.user_1_id == User.id",
    )
    right_friend: Mapped["User"] = relationship(
        foreign_keys=[user_2_id],
        primaryjoin="Friendship.user_2_id == User.id",
    )