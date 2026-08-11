import enum
from datetime import datetime
from typing import TYPE_CHECKING

from sqlalchemy import (
    ForeignKey,
    String,
    func, DateTime
)
from sqlalchemy.dialects.postgresql import ENUM as PG_ENUM
from sqlalchemy.orm import Mapped, mapped_column, relationship
from .Base import Base

if TYPE_CHECKING:
    from .User import User
    from .PostSeen import PostSeen

class TrackVendor(str, enum.Enum):
    spotify = "spotify"
    yandex = "yandex"
    vk = "vk"


class ContentType(str, enum.Enum):
    direct_link = "direct_link"
    iframe = "iframe"

track_vendor_pg = PG_ENUM(TrackVendor, name="track_vendor", create_type=False)
content_type_pg = PG_ENUM(ContentType, name="content_type", create_type=False)

class Post(Base):
    __tablename__ = "posts"

    id: Mapped[int] = mapped_column(primary_key=True)
    author: Mapped[int] = mapped_column(
        ForeignKey("users.id", ondelete="CASCADE"), nullable=False
    )
    type: Mapped[TrackVendor] = mapped_column(track_vendor_pg, nullable=False)
    content_type: Mapped[ContentType] = mapped_column(content_type_pg, nullable=False)
    title: Mapped[str] = mapped_column(String(255), nullable=False)
    comment: Mapped[str] = mapped_column(String(500), nullable=False)
    content: Mapped[str] = mapped_column(String(600), nullable=False)
    created_at: Mapped[datetime] = mapped_column(
        DateTime(timezone=True), server_default=func.current_timestamp(), nullable=False
    )

    author_user: Mapped["User"] = relationship(back_populates="posts")
    seen_records: Mapped[list["PostSeen"]] = relationship(back_populates="post")