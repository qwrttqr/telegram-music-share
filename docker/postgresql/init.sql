CREATE TABLE users
(
    id          INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    telegram_id BIGINT UNIQUE NOT NULL,
    tg_username VARCHAR(255)
);

CREATE TABLE friends_tokens
(
    id         INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    token      VARCHAR(255)          NOT NULL,
    creator_id INT                   NOT NULL REFERENCES users (id) ON DELETE CASCADE,
    is_used    BOOLEAN DEFAULT FALSE NOT NULL
);

CREATE TABLE friendships
(
    user_1_id INT NOT NULL REFERENCES users (id) ON DELETE CASCADE,
    user_2_id INT NOT NULL REFERENCES users (id) ON DELETE CASCADE,
    PRIMARY KEY (user_1_id, user_2_id),
    CONSTRAINT no_duplicates_friendships CHECK ( user_2_id > user_1_id )
);

CREATE TYPE track_vendor AS ENUM ('spotify', 'yandex', 'vk');
CREATE TYPE content_type AS ENUM ('direct_link', 'iframe');

CREATE TABLE posts
(
    id           INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    author       INT          NOT NULL REFERENCES users (id) ON DELETE CASCADE,
    type         track_vendor NOT NULL,
    content_type content_type NOT NULL,
    title        VARCHAR(255) NOT NULL,
    comment      VARCHAR(500) NOT NULL,
    created_at   TIMESTAMPTZ  NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE posts_sees
(
    id      INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    post_id INT NOT NULL REFERENCES posts (id) ON DELETE CASCADE,
    user_id INT NOT NULL REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT unique_user_post_view UNIQUE (post_id, user_id)
);




