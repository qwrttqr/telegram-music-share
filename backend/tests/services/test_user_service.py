from unittest.mock import Mock

import pytest
from sqlalchemy.exc import IntegrityError


async def test_user_creation_but_user_exists(
        fake_users_repo,
        fake_user,
        user_service
):
    fake_users_repo.get_by_tg_id.return_value = fake_user

    res = await user_service.create_user(
        tg_user_id=123,
        tg_username="123",
        tg_first_name="John",
        tg_last_name="Doe",
        tg_photo_url="https://helloworld"
    )
    assert res == False


async def test_user_creation_but_user_not_exists(
        fake_users_repo,
        user_service
):
    fake_users_repo.get_by_tg_id.return_value = None

    res = await user_service.create_user(
        tg_user_id=123,
        tg_username="123",
        tg_first_name="John",
        tg_last_name="Doe",
        tg_photo_url="https://helloworld"
    )
    assert res == True
    call_args = fake_users_repo.add.call_args
    created_user = call_args.args[0]
    assert created_user.telegram_id == 123
    assert created_user.tg_username == "123"
    assert created_user.first_name == "John"
    assert created_user.last_name == "Doe"
    assert created_user.photo_url == "https://helloworld"


class TestAcceptFriendshipInvite:

    async def test_token_not_found(self, user_service, fake_friendship_tokens_repo):
        fake_friendship_tokens_repo.get_by_token.return_value = None

        res = await user_service.accept_friendship_invite(token="some-token", current_user_id=42)

        assert res == False

    async def test_token_found_but_user_accepting_its_own(self, user_service, fake_friendship_tokens_repo,
                                                          fake_db_session):
        fake_friendship_tokens_repo.get_by_token.return_value = Mock(creator_id=42)

        with pytest.raises(ValueError, match="Cannot accept your own invite"):
            await user_service.accept_friendship_invite(token="some-token", current_user_id=42)

    async def test_token_found_but_already_friends(self, user_service, fake_friendship_tokens_repo, fake_db_session):
        fake_friendship_tokens_repo.get_by_token.return_value = Mock(creator_id=42)

        fake_db_session.commit.side_effect = IntegrityError(None, None, None)

        with pytest.raises(ValueError, match="Already friends"):
            await user_service.accept_friendship_invite(token="some-token", current_user_id=41)

        fake_db_session.rollback.assert_awaited_once()

    async def test_token_found_friendship_created(self, user_service, fake_friendship_tokens_repo, fake_friendship_repo,
                                                  fake_db_session):
        fake_token = Mock(creator_id=41)
        fake_friendship_tokens_repo.get_by_token.return_value = fake_token

        res = await user_service.accept_friendship_invite(token="some-token", current_user_id=42)

        fake_friendship_repo.add.assert_awaited_once()
        fake_friendship_tokens_repo.delete.assert_awaited_once_with(fake_token)

        assert res == True


async def test_get_friends_list(user_service, fake_users_repo):
    await user_service.get_friends_list(current_user_id=42)
    fake_users_repo.get_friends_by_user_id.assert_awaited_once()


async def test_delete_from_friends_but_friend_not_found(user_service, fake_friendship_repo):
    fake_friendship_repo.get_by_user_id_friend_id.return_value = None
    res = await user_service.delete_from_friends(friend_id=22, current_user_id=42)

    assert res == False


async def test_delete_from_friends_friend_found(user_service, fake_friendship_repo, fake_db_session):
    fake_friendship = Mock(id=11)
    fake_friendship_repo.get_by_user_id_friend_id.return_value = fake_friendship
    res = await user_service.delete_from_friends(friend_id=22, current_user_id=42)

    assert res == True
    fake_friendship_repo.delete.assert_awaited_once_with(fake_friendship)
    fake_db_session.commit.assert_awaited_once()
