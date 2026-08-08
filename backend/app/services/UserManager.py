from sqlalchemy.ext.asyncio import AsyncSession
from sqlalchemy import select
from routing.pydantic.requests.add_user_request import RequestCreateUser
from db.models import User
from routing.pydantic.responses.user_created_response import ResponseUserCreation


class UserManager:

    @staticmethod
    async def create_user(user_data: RequestCreateUser, database_session: AsyncSession) -> ResponseUserCreation:
        stmt = select(User).where(User.telegram_id == user_data.telegram_id)
        res = (await database_session.execute(stmt)).one_or_none()
        if not res:
            database_session.add(User(**user_data.model_dump()))
            await database_session.commit()
            return ResponseUserCreation(success=True, message="User created")
        else:
            return ResponseUserCreation(success=True, message="User already exists")