from typing import TYPE_CHECKING

from sqlalchemy import ForeignKey, String, Boolean
from sqlalchemy.orm import Mapped, mapped_column, relationship
from .Base import Base

if TYPE_CHECKING:
    from .User import User


class FriendsToken(Base):
    __tablename__ = "friends_tokens"
    id: Mapped[int] = mapped_column(primary_key=True)
    token: Mapped[str] = mapped_column(String(255), nullable=False)
    creator_id: Mapped[int] = mapped_column(
        ForeignKey("users.id", ondelete="CASCADE")
    )
    is_used: Mapped[bool] = mapped_column(Boolean, nullable=False, default=False)

    token_creator: Mapped["User"] = relationship(back_populates="friend_tokens")