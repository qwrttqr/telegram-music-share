async def test_marking_seen_logic(fake_posts_repo,posts_service):
    fake_posts_repo.get_by_user_id_feed.return_value = ([{"post_id": 1}], 1)

    posts, total = await posts_service.get_feed_posts(current_user_id=1, page=1, per_page=1)
    assert total == 1
    fake_posts_repo.mark_as_seen.assert_awaited_once_with(user_id=1, posts=posts)