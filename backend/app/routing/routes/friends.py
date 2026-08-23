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
from services.friends_token_service import FriendsTokenService
from services.user_service import UserService
from utils.dependencies import get_current_user, get_friends_token_service, get_user_service

router = APIRouter(prefix="/friends")


@router.get("/create_friendship_token", tags=["friends"])
async def create_friendship_token(
        telegram_user: User = Depends(get_current_user),
        friends_token_service: FriendsTokenService = Depends(get_friends_token_service)
) -> ResponseFriendshipTokenResponse:
    token = await friends_token_service.create_friendship_token(creator_id=telegram_user.id)
    if token:
        return ResponseFriendshipTokenResponse(success=True, token=token)
    else:
        raise HTTPException(status_code=500, detail="Unexpected error on server")


@router.post("/accept_invite", tags=["friends"])
async def accept_invite(
        body: RequestAcceptFriendRequest,
        telegram_user: User | None = Depends(get_current_user),
        user_service: UserService = Depends(get_user_service),
) -> ResponseFriendRequestAccepted:
    try:
        res = await user_service.accept_friendship_invite(
            token=body.token,
            current_user_id=telegram_user.id,
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
        user_service: UserService = Depends(get_user_service)
) -> ResponseFriendsList:
    friends_list = await user_service.get_friends_list(telegram_user.id)
    return ResponseFriendsList.model_validate({"friends": friends_list})


@router.post("/delete_friend", tags=["friends"])
async def delete_friend(
        body: RequestDeleteFromFriends,
        telegram_user: User = Depends(get_current_user),
        user_service: UserService = Depends(get_user_service),
) -> ResponseDeleteFriend:
    res = await user_service.delete_from_friends(
        friend_id=body.friend_id,
        current_user_id=telegram_user.id,
    )
    if res:
        return ResponseDeleteFriend(success=True, message="Successfully deleted")
    else:
        return ResponseDeleteFriend(success=False, message="Couldn't delete")
