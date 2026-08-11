from fastapi import APIRouter, Depends
from sqlalchemy.ext.asyncio import AsyncSession

from db.core.engine import get_db_session
from routing.pydantic.responses.user.user_created_response import ResponseUserCreation
from services.UserManager import UserManager
from utils.validation import validate_init_data

router = APIRouter(prefix="/users")


@router.post("/add_user", tags=["users"])
async def add_user(

        validated_init_data: dict = Depends(validate_init_data),
        database_session: AsyncSession = Depends(get_db_session)
) -> ResponseUserCreation:
    user_data = validated_init_data["user"]
    created = await UserManager.create_user(
        tg_user_id=user_data["id"],
        tg_username=user_data.get("username"),
        tg_first_name=user_data.get("first_name"),
        tg_last_name=user_data.get("last_name"),
        tg_photo_url=user_data.get("photo_url"),
        database_session=database_session,
    )
    if created:
        return ResponseUserCreation(success=True, message="User created")
    else:
        return ResponseUserCreation(success=True, message="User already registered")
