drop table if exists messages;
drop table if exists chat_participants;
drop table if exists chats;
drop table if exists users;

# -- пользователи --
# -- предполагается доработка, что пользователь может сменить почту
create table users
(
    id              int AUTO_INCREMENT PRIMARY KEY,
    email           varchar(100) UNIQUE not null,
    nickname        varchar(100) unique,
    password        varchar(255) not null,
    hash            varchar(255),
    email_confirmed boolean default false,
    hide_email      int(1)  default 0,
    photo           varchar(255)
);
insert into users(email, nickname, password)
values ('aladser@mail.ru', 'Admin', '$2y$10$s8hnPlQkLrwNCA94.j6UoOTjrK6iZqTvP2Mhidgt.i9vA1GbebeXa');
insert into users(email, nickname, password)
values ('aladser@gmail.com', 'Aladser', '$2y$10$s8hnPlQkLrwNCA94.j6UoOTjrK6iZqTvP2Mhidgt.i9vA1GbebeXa');
insert into users(email, nickname, password)
values ('lauxtec@gmail.com', 'Lauxtec', '$2y$10$s8hnPlQkLrwNCA94.j6UoOTjrK6iZqTvP2Mhidgt.i9vA1GbebeXa');
insert into users(email, nickname, password)
values ('sendlyamobile@gmail.com', 'Barashka', '$2y$10$s8hnPlQkLrwNCA94.j6UoOTjrK6iZqTvP2Mhidgt.i9vA1GbebeXa');
update users set email_confirmed = 1 where id < 5;

# --  ЧАТЫ  --
create table chats
(
    id        int auto_increment primary key,
    type      enum('personal', 'group') not null,
    name      varchar(100),
    creator_id int,
    CONSTRAINT check_creator_id foreign key (creator_id) references users (id) ON DELETE cascade
);
insert into chats(type, creator_id) values ('personal', 1);
insert into chats(type, creator_id) values ('personal', 1);
insert into chats(type, name, creator_id) values ('group', 'групповой чат 1', 1);
insert into chats(type, name, creator_id) values ('group', 'групповой чат 2', 2);

create table chat_participants
(
	id int auto_increment primary key,
    chat_id   int,
    user_id   int,
    notice int(1) default 1,
    constraint unique_user_chat unique index (chat_id, user_id),
    CONSTRAINT check_prt_chat_id foreign key (chat_id) references chats (id) ON DELETE CASCADE,
    CONSTRAINT check_prt_user_id foreign key (user_id) references users (id) ON DELETE CASCADE
);
insert into chat_participants(chat_id, user_id) values (1, 1);
insert into chat_participants(chat_id, user_id) values (1, 2);
insert into chat_participants(chat_id, user_id) values (2, 1);
insert into chat_participants(chat_id, user_id) values (2, 3);
insert into chat_participants(chat_id, user_id) values (3, 1);
insert into chat_participants(chat_id, user_id) values (4, 2);
insert into chat_participants(chat_id, user_id) values (4, 3);

create table messages
(
    id        int auto_increment primary key,
    chat_id    int not null,
    creator_user_id int not null,
    content    text not null,
    time      datetime DEFAULT CURRENT_TIMESTAMP,
    forward   int(1) default 0,
    CONSTRAINT check_msg_chat_id foreign key (chat_id) references chats (id) ON DELETE cascade,
    CONSTRAINT check_msg_creator foreign key (creator_user_id) references users (id) ON DELETE cascade
);

insert into messages(chat_id, content, creator_user_id) values (1, 'первое сообщение.', 1);
insert into messages(chat_id, content, creator_user_id) values (1, 'второе сообщение.', 2);

DROP FUNCTION IF EXISTS getPublicUserName; 
DELIMITER //
CREATE FUNCTION getPublicUserName(email varchar(100), nickname varchar(100), hide_email int(1))
    returns varchar(100)
    DETERMINISTIC
begin
    IF hide_email = 1 THEN
        return nickname;
    ELSE
        return email;
    END IF;
END;
//
DELIMITER ;