from unittest.mock import create_autospec, AsyncMock

import pytest

from db.models import User
from db.repositories.friendship_repo import FriendshipRepo
from db.repositories.posts_repo import PostsRepo
from db.repositories.token_repo import FriendshipTokensRepo
from db.repositories.user_repo import UsersRepo
from services.friends_token_service import FriendsTokenService
from services.posts_service import PostsService
from services.user_service import UserService

@pytest.fixture
def fake_posts_repo():
    return create_autospec(PostsRepo, spec_set=True, instance=True)

@pytest.fixture
def fake_users_repo():
    return create_autospec(UsersRepo, spec_set=True, instance=True)

@pytest.fixture
def fake_friendship_repo():
    return create_autospec(FriendshipRepo, spec_set=True, instance=True)

@pytest.fixture
def fake_friendship_tokens_repo():
    return create_autospec(FriendshipTokensRepo, spec_set=True, instance=True)

@pytest.fixture
def fake_db_session():
    return AsyncMock()


@pytest.fixture
def fake_user():
    return User(id=1, telegram_id=123, first_name="Test")

@pytest.fixture
def friends_token_service(fake_users_repo, fake_friendship_tokens_repo, fake_db_session):
    return FriendsTokenService(
        users_repo=fake_users_repo,
        token_repo=fake_friendship_tokens_repo,
        database_session=fake_db_session
    )

@pytest.fixture
def user_service(fake_users_repo, fake_friendship_tokens_repo, fake_friendship_repo, fake_db_session):
    return UserService(
        users_repo=fake_users_repo,
        token_repo=fake_friendship_tokens_repo,
        friendship_repo=fake_friendship_repo,
        database_session=fake_db_session
    )

@pytest.fixture
def posts_service(fake_posts_repo, fake_db_session):
    return PostsService(
        posts_repo=fake_posts_repo,
        database_session=fake_db_session
    )