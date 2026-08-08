from fastapi import APIRouter, Depends, HTTPException
from sqlalchemy.ext.asyncio import AsyncSession

from core.engine import get_db_session
from routing.pydantic.responses.user.user_created_response import ResponseUserCreation
from services.UserManager import UserManager
from utils.validation import validate_init_data

router = APIRouter(prefix="/users")


@router.post("/add_user", tags=["users"])
async def add_user(
        validated_init_data: dict = Depends(validate_init_data),
        database_session: AsyncSession = Depends(get_db_session)
) -> ResponseUserCreation:
    try:
        created = await UserManager.create_user(
            tg_user_id=validated_init_data["user"]["id"],
            tg_username=validated_init_data["user"].get("username"),
            database_session=database_session,
        )
        if created:
            return ResponseUserCreation(success=True, message="User created")
        else:
            return ResponseUserCreation(success=True, message="User already registered")
    except Exception as e:
        print(e)
        raise HTTPException(status_code=500, detail="Unexpected error on server")
