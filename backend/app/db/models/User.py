from sqlalchemy.orm import Mapped, mapped_column
from sqlalchemy import BigInteger
from sqlalchemy import String
from .Base import Base


class User(Base):
    __tablename__ = "users"
    id: Mapped[int] = mapped_column(primary_key=True)
    telegram_id: Mapped[int] = mapped_column(BigInteger, nullable=False)
    tg_username: Mapped[str | None] = mapped_column(String(255), nullable=True)

    def __repr__(self) -> str:
        return f"User(id={self.id}, telegram_id={self.telegram_id}, tg_username={self.tg_username})"
