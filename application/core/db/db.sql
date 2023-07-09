# пользователи
drop table if exists users;
create table users(
        user_id int AUTO_INCREMENT PRIMARY KEY,
        user_email varchar(100) UNIQUE,
        user_nickname varchar(100) unique,
        user_password varchar(255) not null,
        user_hash varchar(255),
        user_email_confirmed boolean default false,
        user_hide_email int(1) default 0,
        user_photo varchar(255),
        
);
insert into users(user_email, user_nickname, user_password) values('aladser@mail.ru', 'admin', '$2y$10$H09UQUYdkD3uTmEXQsYQuukJNjF2XA1BGaBF0Deq0mu1qPLSEFZWe');
update users set user_email_confirmed = 1 where user_id = 1;

# виртуальная таблица неподтвержденных пользователей
create view unhidden_emails as
select user_email from users where user_hide_email  = 0;

drop table if exists contacts;
create table contacts(
	id int auto_increment primary key,
	user_id int not null,
	contact_id int not null
);

# соединения
drop table if exists connections;
create table connections(
        connection_id int auto_increment primary key,
        connection_ws_id int not null,
        connection_username varchar(255) not null
);

# список чатов
drop table if exists chat;
create table chat(
	chat_id int auto_increment primary key,
	chat_type varchar(100) check(chat_type = 'dialog' or chat_type = 'discussion')
);

# участники чатов
drop table if exists chat_participant;
create table chat_participant(
	chat_participant_id int auto_increment primary key,
	chat_participant_chatid int references chat(chat_id),
	chat_participant_userid int references user(user_id)
);

# сообщения чатов
drop table if exists chat_message;
create table chat_message(
	chat_message_id int auto_increment primary key,
	chat_message_chatid int references chat(chat_id),
	chat_message_text text not null,
	chat_message_user_creator int references user(user_id),
	#chat_message_date datetime default '2000-01-01 00:00:00'
	chat_message_date datetime default CURRENT_TIMESTAMP()
);