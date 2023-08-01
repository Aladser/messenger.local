drop table if exists connections;
drop table if exists chat_message;
drop table if exists chat_participant;
drop table if exists chat;
drop table if exists contacts;
drop view if exists unhidden_emails;
DROP VIEW IF EXISTS extended_chat;
drop table if exists users;

# -- пользователи --
# -- предполагается доработка, что пользователь может сменить почту
create table users
(
    user_id              int AUTO_INCREMENT PRIMARY KEY,
    user_email           varchar(100) UNIQUE,
    user_nickname        varchar(100) unique,
    user_password        varchar(255) not null,
    user_hash            varchar(255),
    user_email_confirmed boolean default false,
    user_hide_email      int(1)  default 0,
    user_photo           varchar(255)
);
insert into users(user_email, user_nickname, user_password)
values ('aladser@mail.ru', 'Admin', '$2y$10$H09UQUYdkD3uTmEXQsYQuukJNjF2XA1BGaBF0Deq0mu1qPLSEFZWe');
insert into users(user_email, user_nickname, user_password)
values ('aladser@gmail.com', 'Aladser', '$2y$10$H09UQUYdkD3uTmEXQsYQuukJNjF2XA1BGaBF0Deq0mu1qPLSEFZWe');
insert into users(user_email, user_nickname, user_password)
values ('lauxtec@gmail.com', 'Lauxtec', '$2y$10$H09UQUYdkD3uTmEXQsYQuukJNjF2XA1BGaBF0Deq0mu1qPLSEFZWe');
insert into users(user_email, user_nickname, user_password)
values ('sendlyamobile@gmail.com', 'Barashka', '$2y$10$H09UQUYdkD3uTmEXQsYQuukJNjF2XA1BGaBF0Deq0mu1qPLSEFZWe');
update users set user_email_confirmed = 1 where user_id < 5;

# -- контакты пользователя --
create table contacts
(
    cnt_id         int auto_increment primary key,
    cnt_user_id    int not null,
    cnt_contact_id int not null,
    CONSTRAINT contacts_fk_userid foreign key (cnt_user_id) references users (user_id) ON DELETE cascade,
    CONSTRAINT contacts_fk_contactid foreign key (cnt_contact_id) references users (user_id) ON DELETE CASCADE
);

# -- соединения --
create table connections
(
    connection_ws_id  int not null primary key,
    connection_userid int unique,
    CONSTRAINT fk_userid foreign key (connection_userid) references users (user_id) ON DELETE CASCADE
);

# --  ЧАТЫ  --
create table chat
(
    chat_id        int auto_increment primary key,
    chat_type      varchar(10) not null,
    chat_name      varchar(30),
    chat_creatorid int,
    CONSTRAINT check_creatorid foreign key (chat_creatorid) references users (user_id) ON DELETE cascade
);
create table chat_participant
(
    chat_participant_chatid   int,
    chat_participant_userid   int,
    chat_participant_isnotice int(1) default 1,
    PRIMARY KEY (chat_participant_chatid, chat_participant_userid),
    CONSTRAINT check_participant_chatid foreign key (chat_participant_chatid) references chat (chat_id) ON DELETE CASCADE,
    CONSTRAINT check_participant_userid foreign key (chat_participant_userid) references users (user_id) ON DELETE CASCADE
);
create table chat_message
(
    chat_message_id        int auto_increment primary key,
    chat_message_chatid    int,
    chat_message_text      text not null,
    chat_message_creatorid int,
    chat_message_time      datetime,
    chat_message_forward   int(1) default 0,
    CONSTRAINT check_message_chatid foreign key (chat_message_chatid) references chat (chat_id) ON DELETE cascade,
    CONSTRAINT check_message_creator foreign key (chat_message_creatorid) references users (user_id) ON DELETE cascade
);

# -- виртуальная таблица неподтвержденных пользователей --
create view unhidden_emails as
select user_email
from users
where user_hide_email = 0;