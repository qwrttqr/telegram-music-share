CREATE TABLE users
(
    id          INTEGER PRIMARY KEY,
    telegram_id INTEGER,
    username    VARCHAR(255),
    firstname   VARCHAR(255),
    lastname    VARCHAR(255)
);

CREATE TABLE friendships
(
    user_id   INTEGER NOT NULL,
    friend_id INTEGER NOT NULL,
    CONSTRAINT `fk_friendships_friend_id_users`
        FOREIGN KEY (user_id) REFERENCES users (id),
    CONSTRAINT `fk_friendships_user_id_users`
        FOREIGN KEY (friend_id) REFERENCES users (id)
);

CREATE TABLE spotify_tracks
(
    id                INTEGER PRIMARY KEY,
    track_id          INTEGER      NOT NULL,
    track_preview_url TEXT(1000)   NOT NULL,
    album_name        VARCHAR(255) NOT NULL,
    album_uri         TEXT(1000)   NOT NULL,
    album_image_url   TEXT(1000)   NOT NULL
);

CREATE TABLE shared_tracks
(
    user_id          INTEGER NOT NULL,
    spotify_track_id INTEGER NOT NULL,
    added_since      DATETIME,
    is_preferred     BOOLEAN DEFAULT FALSE,
    CONSTRAINT `fk_shared_tracks_spotify_track_id_spotify_tracks`
        FOREIGN KEY (spotify_track_id) REFERENCES spotify_tracks (id),
    CONSTRAINT `fk_shared_tracks_id_users`
        FOREIGN KEY (user_id) REFERENCES users (id)
)