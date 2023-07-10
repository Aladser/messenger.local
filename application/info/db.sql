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
insert into users(user_email, user_nickname, user_password) values('aladser@mail.ru', 'Admin', '$2y$10$H09UQUYdkD3uTmEXQsYQuukJNjF2XA1BGaBF0Deq0mu1qPLSEFZWe');
insert into users(user_email, user_nickname, user_password) values('aladser@gmail.com', 'Aladser', '$2y$10$H09UQUYdkD3uTmEXQsYQuukJNjF2XA1BGaBF0Deq0mu1qPLSEFZWe');
insert into users(user_email, user_nickname, user_password) values('lauxtec@gmail.com', 'Lauxtec', '$2y$10$H09UQUYdkD3uTmEXQsYQuukJNjF2XA1BGaBF0Deq0mu1qPLSEFZWe');
insert into users(user_email, user_nickname, user_password) values('sendlyamobile@gmail.com', 'Barashka', '$2y$10$H09UQUYdkD3uTmEXQsYQuukJNjF2XA1BGaBF0Deq0mu1qPLSEFZWe');
update users set user_email_confirmed = 1 where user_id < 5;


# виртуальная таблица неподтвержденных пользователей
create view unhidden_emails as select user_email from users where user_hide_email  = 0;


drop table if exists contacts;
#контакты, с кем есть диалог
create table contacts(
	id int auto_increment primary key,
	user_id int not null,
	contact_id int not null
);


drop table if exists connections;
# соединения
create table connections(
        connection_id int auto_increment primary key,
        connection_ws_id int not null,
        connection_username varchar(255) not null
);


drop table if exists chat_message;
drop table if exists chat_participant;
drop table if exists chat;
# список чатов
create table chat(
	chat_id int auto_increment primary key,
	chat_user_count int
);
# участники чатов
create table chat_participant(
	chat_participant_chatid int,
	chat_participant_userid int,
	PRIMARY KEY (chat_participant_chatid, chat_participant_userid),
	CONSTRAINT check_participant_chatid foreign key (chat_participant_chatid) references chat(chat_id) ON DELETE CASCADE,
	CONSTRAINT check_participant_userid foreign key (chat_participant_userid) references users(user_id) ON DELETE CASCADE
);
# сообщения чатов
create table chat_message(
	chat_message_id int auto_increment primary key,
	chat_message_chatid int,
	chat_message_text text not null,
	chat_message_user_creatorid int,
	#chat_message_date datetime default '2000-01-01 00:00:00'
	chat_message_date datetime default CURRENT_TIMESTAMP(),
	CONSTRAINT check_message_chatid foreign key (chat_message_chatid) references chat(chat_id) ON DELETE cascade,
	CONSTRAINT check_user_creator foreign key (chat_message_user_creatorid) references users(user_id) ON DELETE cascade
);