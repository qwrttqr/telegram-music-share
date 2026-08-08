from typing import TYPE_CHECKING

from sqlalchemy import ForeignKey
from sqlalchemy.orm import Mapped, mapped_column, relationship
from .Base import Base

if TYPE_CHECKING:
    from .User import User
    from .Post import Post

class PostSeen(Base):
    __tablename__ = "posts_sees"

    id: Mapped[int] = mapped_column(primary_key=True)
    post_id: Mapped[int] = mapped_column(
        ForeignKey("posts.id", ondelete="CASCADE"), nullable=False
    )
    user_id: Mapped[int] = mapped_column(
        ForeignKey("users.id", ondelete="CASCADE"), nullable=False
    )
    seen_by: Mapped[list["User"]] = relationship(back_populates="seen_posts")
    post: Mapped["Post"] = relationship(back_populates="seen_records")