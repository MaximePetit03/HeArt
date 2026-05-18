-- Photos : ajout description, taken_at, user_id
CREATE TABLE photo (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(100),
    description TEXT,
    filename    VARCHAR(255) NOT NULL,
    taken_at    DATE,
    album_id    INT UNSIGNED NOT NULL,
    user_id     INT UNSIGNED NOT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_photo_album FOREIGN KEY (album_id) REFERENCES albums(id) ON DELETE CASCADE,
    CONSTRAINT fk_photo_user  FOREIGN KEY (user_id)  REFERENCES users(id)  ON DELETE CASCADE
) ENGINE=InnoDB;

-- Comments : ajout updated_at
CREATE TABLE comment (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    content    TEXT NOT NULL,
    user_id    INT UNSIGNED NOT NULL,
    photo_id   INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_comment_user  FOREIGN KEY (user_id)  REFERENCES users(id)  ON DELETE CASCADE,
    CONSTRAINT fk_comment_photo FOREIGN KEY (photo_id) REFERENCES photos(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Album_invitations : ajout permission
CREATE TABLE album_invitations (
    album_id   INT UNSIGNED NOT NULL,
    user_id    INT UNSIGNED NOT NULL,
    permission ENUM('view', 'comment', 'contribute') DEFAULT 'view',
    PRIMARY KEY (album_id, user_id),
    CONSTRAINT fk_invite_album FOREIGN KEY (album_id) REFERENCES albums(id) ON DELETE CASCADE,
    CONSTRAINT fk_invite_user  FOREIGN KEY (user_id)  REFERENCES users(id)  ON DELETE CASCADE
) ENGINE=InnoDB;

-- Nouvelle table pivot : tags sur les albums
CREATE TABLE album_tags (
    album_id INT UNSIGNED NOT NULL,
    tag_id   INT UNSIGNED NOT NULL,
    PRIMARY KEY (album_id, tag_id),
    CONSTRAINT fk_albumtag_album FOREIGN KEY (album_id) REFERENCES albums(id) ON DELETE CASCADE,
    CONSTRAINT fk_albumtag_tag   FOREIGN KEY (tag_id)   REFERENCES tags(id)   ON DELETE CASCADE
) ENGINE=InnoDB;