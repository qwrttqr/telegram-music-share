from fastapi import APIRouter, Depends, HTTPException
from sqlalchemy.ext.asyncio import AsyncSession

from db.core.engine import get_db_session
from db.models import User
from routing.pydantic.requests.friends.friend_delete_from_friends import RequestDeleteFromFriends
from routing.pydantic.requests.friends.friend_invite_accept_request import RequestAcceptFriendRequest
from routing.pydantic.responses.friends.accepted_invite_response import ResponseFriendRequestAccepted
from routing.pydantic.responses.friends.delete_friend_response import ResponseDeleteFriend
from routing.pydantic.responses.friends.friendship_token_response import ResponseFriendshipTokenResponse
from routing.pydantic.responses.friends.get_friends_list import ResponseFriendsList
from services.UserManager import UserManager
from utils.get_current_user import get_current_user

router = APIRouter(prefix="/friends")


@router.get("/create_friendship_token", tags=["friends"])
async def create_friendship_token(
        telegram_user: User | None = Depends(get_current_user),
        database_session: AsyncSession = Depends(get_db_session)
) -> ResponseFriendshipTokenResponse:
    token = await UserManager.create_friendship_token(creator_id=telegram_user.id, database_session=database_session)
    if token:
        return ResponseFriendshipTokenResponse(success=True, token=token)
    else:
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


@router.get("/get_friends", tags=["friends"])
async def get_friends_list(
        telegram_user: User = Depends(get_current_user),
        database_session: AsyncSession = Depends(get_db_session)
) -> ResponseFriendsList:
    friends_list = await UserManager.get_friends_list(telegram_user.id, database_session)
    return ResponseFriendsList.model_validate({"friends": friends_list})


@router.post("/delete_friend", tags=["friends"])
async def delete_friend(
        body: RequestDeleteFromFriends,
        telegram_user: User = Depends(get_current_user),
        database_session: AsyncSession = Depends(get_db_session)
) -> ResponseDeleteFriend:
    res = await UserManager.delete_from_friends(
        friend_id=body.friend_id,
        current_user_id=telegram_user.id,
        database_session=database_session
    )
    if res:
        return ResponseDeleteFriend(success=True, message="Successfully deleted")
    else:
        return ResponseDeleteFriend(success=False, message="Couldn't delete")
