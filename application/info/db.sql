# ----- пользователи -----
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

# -----контакты, с кем есть диалог-----
drop table if exists contacts;
create table contacts(
	id int auto_increment primary key,
	user_id int not null,
	contact_id int not null,
	CONSTRAINT contacts_fk_userid foreign key (user_id) references users(user_id) ON DELETE cascade,
	CONSTRAINT contacts_fk_contactid foreign key (contact_id) references users(user_id) ON DELETE CASCADE
);

# -----соединения-----
drop table if exists connections;
create table connections(
	connection_id int auto_increment primary key,
	connection_ws_id int not null,
	connection_userid int,
	CONSTRAINT fk_userid foreign key (connection_userid) references users(user_id) ON DELETE CASCADE
);

# ---- чаты ----
drop table if exists chat_message;
drop table if exists chat_participant;
drop table if exists chat;

# список чатов
create table chat(
	chat_id int auto_increment primary key,
	chat_type varchar(10)
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
	chat_message_date not null,
	CONSTRAINT check_message_chatid foreign key (chat_message_chatid) references chat(chat_id) ON DELETE cascade,
	CONSTRAINT check_user_creator foreign key (chat_message_user_creatorid) references users(user_id) ON DELETE cascade
);

# триггер на тип чата. значение: "dialog" или "discussion"
delimiter //
CREATE TRIGGER check_type BEFORE INSERT ON chat
FOR EACH ROW
begin
   IF NEW.chat_type not in ('dialog', 'discussion') then
	SIGNAL SQLSTATE '45000'
	SET MESSAGE_TEXT = 'chat_type не равен dialog или discussion';
   END if;
end //
delimiter ;

# триггер на добавление сообщений. Проверяется, правильный ли пользователь
delimiter //
CREATE TRIGGER check_message BEFORE INSERT ON chat_message
FOR EACH ROW
	BEGIN
		if new.chat_message_user_creatorid not in (select chat_participant_userid from chat_participant where chat_participant_chatid=new.chat_message_chatid) then
		SIGNAL SQLSTATE '45000'
		SET MESSAGE_TEXT = 'пользователя нет в данном чате';
	END IF;
END //
delimiter ;

