
async def test_token_creating_logic(fake_friendship_tokens_repo, fake_users_repo, friends_token_service, fake_user):
    fake_users_repo.get_by_user_id.return_value = fake_user

    token = await friends_token_service.create_friendship_token(creator_id=1)

    assert isinstance(token, str)
    call_args = fake_friendship_tokens_repo.add.call_args
    added_token = call_args.args[0]
    assert added_token.token == token
    assert added_token.creator_id == fake_user.id
