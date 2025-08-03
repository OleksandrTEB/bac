CREATE DATABASE filmosfera;

USE filmosfera;

CREATE TABLE users
(
  id       BIGINT AUTO_INCREMENT PRIMARY KEY,
  email    VARCHAR(250) NOT NULL,
  password VARCHAR(255) NOT NULL,
  username VARCHAR(250) NOT NULL UNIQUE,
  country  VARCHAR(250),
  license  VARCHAR(250),
  token    VARCHAR(250),
  avatar   VARCHAR(250)
);


INSERT INTO users (email, password, username)
VALUES ('admin', 'ZAQ!2wsx', 'Administrator');

CREATE TABLE baza
(
  base_avatar VARCHAR(200)
);

INSERT INTO baza (base_avatar)
VALUES ('../assets/avatars/defaultAvatar.png');


CREATE TABLE films (
  id          BIGINT AUTO_INCREMENT PRIMARY KEY,
  nazwa       VARCHAR(250),
  rok         VARCHAR(250),
  gatunek     VARCHAR(250),
  opis        TEXT,
  obraz_filmu VARCHAR(250),
  trailer     VARCHAR(250),
  video       VARCHAR(250),
  ocena       VARCHAR(250)
);

INSERT INTO `films` (`id`, `nazwa`, `rok`, `gatunek`, `opis`, `obraz_filmu`, `trailer`, `video`) VALUES
  (2, 'Pirates of the Caribbean', '2003', 'Pirats', 'The Curse of the Black Pearl', '../assets/films/piraty1.webp', 'https://player.vimeo.com/video/1104458644', 'https://player.vimeo.com/video/1104458644');


CREATE TABLE review (
  id          BIGINT AUTO_INCREMENT PRIMARY KEY,
  film_id     BIGINT NOT NULL,
  user_id     BIGINT NOT NULL,
  text        TEXT NOT NULL,
  rating      INT  NOT NULL,
  created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT film_id_fk FOREIGN KEY (film_id) REFERENCES users (id) ON DELETE CASCADE,
  CONSTRAINT user_id_fk FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
);


CREATE TABLE comments
(
  id         BIGINT AUTO_INCREMENT PRIMARY KEY,
  film_id    BIGINT  NOT NULL,
  user_id    BIGINT  NOT NULL,
  text       TEXT NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT film_id_fk FOREIGN KEY (film_id) REFERENCES users (id) ON DELETE CASCADE,
  CONSTRAINT user_id_fk FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
);
