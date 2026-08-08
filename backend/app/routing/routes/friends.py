from fastapi import APIRouter, Depends, HTTPException
from sqlalchemy.ext.asyncio import AsyncSession

from core.engine import get_db_session
from db.models import User
from routing.pydantic.requests.friends.friend_invite_accept_request import RequestAcceptFriendRequest
from routing.pydantic.responses.friends.accepted_invite_response import ResponseFriendRequestAccepted
from routing.pydantic.responses.friends.friendship_token_response import ResponseFriendshipTokenResponse
from services.UserManager import UserManager
from utils.get_current_user import get_current_user

router = APIRouter(prefix="/friends")


@router.get("/create_friendship_token", tags=["friends"])
async def create_friendship_token(
        telegram_user: User | None = Depends(get_current_user),
        database_session: AsyncSession = Depends(get_db_session)
) -> ResponseFriendshipTokenResponse:
    try:
        token = await UserManager.create_friendship_token(creator_id=telegram_user.id, database_session=database_session)
        if token:
            return ResponseFriendshipTokenResponse(success=True, token=token)
        else:
            raise HTTPException(status_code=500, detail="Unexpected error on server")
    except Exception as e:
        print(e)
        raise HTTPException(status_code=500, detail="Unexpected error on server")

@router.post("/accept_invite", tags=["friends"])
async def accept_invite(
        body: RequestAcceptFriendRequest,
        telegram_user: User | None = Depends(get_current_user),
        database_session: AsyncSession = Depends(get_db_session)
) -> ResponseFriendRequestAccepted:
    try:
        res = await UserManager.accept_friendship_invite(
            token=body.token,
            current_user_id=telegram_user.id,
            database_session=database_session,
        )
        if res:
            return ResponseFriendRequestAccepted(success=True, message="Friend request accepted")
        else:
            return ResponseFriendRequestAccepted(success=False, message="This link is actually been used already")
    except ValueError as e:
        raise HTTPException(status_code=409, detail=str(e))
    except Exception as e:
        print(e)
        raise HTTPException(status_code=500, detail="Unexpected error on server")
