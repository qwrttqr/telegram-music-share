from fastapi import APIRouter, Depends, HTTPException
from sqlalchemy.ext.asyncio import AsyncSession

from core.engine import get_db_session
from routing.pydantic.requests.add_user_request import RequestCreateUser
from routing.pydantic.responses.user_created_response import ResponseUserCreation
from services.UserManager import UserManager

router = APIRouter()

@router.post("/add_user", tags=["users"])
async def add_user(user_data: RequestCreateUser, database_session: AsyncSession = Depends(get_db_session)) -> ResponseUserCreation:
    try:
        return await UserManager.create_user(user_data=user_data, database_session=database_session)
    except Exception as e:
        print(e)
        raise HTTPException(status_code=500, detail="Unexpected error on server")