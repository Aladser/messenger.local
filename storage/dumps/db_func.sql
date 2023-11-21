# --триггер проверки тип чата: dialog или discussion--
drop trigger if exists check_chat_type;
delimiter //
CREATE TRIGGER check_chat_type
    BEFORE INSERT
    ON chat
    FOR EACH ROW
begin
    IF NEW.chat_type not in ('dialog', 'discussion') then
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'chat_type не равен dialog или discussion';
    END if;
end //
delimiter ;

# --триггер на добавление сообщений. Проверка существования пользователя--
drop trigger if exists check_message;
delimiter //
CREATE TRIGGER check_message
    BEFORE INSERT
    ON chat_message
    FOR EACH ROW
BEGIN
    if new.chat_message_creatorid not in (select chat_participant_userid
                                          from chat_participant
                                          where chat_participant_chatid = new.chat_message_chatid) then
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'пользователя нет в данном чате';
    END IF;
END //
delimiter ;

# --функция получить публчное имя пользователя
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


# --процедура создать чат 
DROP PROCEDURE if exists create_dialog;
DELIMITER //
CREATE PROCEDURE create_dialog(
    in user1 int,
    in user2 int,
    out chatid int
)
begin
    insert into chat(chat_type, chat_creatorid) values ('dialog', user1);
    select last_insert_id() into chatid;
    insert into chat_participant(chat_participant_chatid, chat_participant_userid)
    values (chatid, user1),
           (chatid, user2);
END//
DELIMITER ;

# --процедура создать групповой чат--
DROP PROCEDURE if exists create_discussion;
DELIMITER //
CREATE PROCEDURE create_discussion(
    in userhost int,
    out chatid int
)
begin
    insert into chat(chat_type, chat_creatorid) values ('discussion', userhost);
   
    select last_insert_id() into chatid;
   
    insert into chat_participant(chat_participant_chatid, chat_participant_userid) values (chatid, userhost); # пользователи чата
    
    update chat set chat_name = concat('Групповой чат ', userhost, chatid) where chat_id = chatid; # название группового чата
END//
DELIMITER ;

# --процедура создать сообщение--
DROP PROCEDURE if exists add_message;
DELIMITER //
CREATE PROCEDURE add_message(
    in chat_chatid int,
    in chat_text text,
    in chat_user varchar(100),
    in chat_time datetime,
    out msg_id int
)
begin
    select user_id into @userid from users where user_email = chat_user or user_nickname = chat_user;
    insert into chat_message(chat_message_chatid, chat_message_text, chat_message_creatorid, chat_message_time)
    values (chat_chatid, chat_text, @userid, chat_time);
    select last_insert_id() into msg_id;
END//
DELIMITER ;

# --процедура создать пересылаемое сообщение--
DROP PROCEDURE if exists add_forwarded_message;
DELIMITER //
CREATE PROCEDURE add_forwarded_message(
    in msg_creatorid int,
    in message_id int,
    in chat_id int,
    in msg_time datetime,
    out new_msg_id int
)
begin
    # копируем сообщение
    insert into chat_message(chat_message_chatid, chat_message_text, chat_message_creatorid, chat_message_time)
    select chat_message_chatid, chat_message_text, chat_message_creatorid, chat_message_time
    from chat_message
    where chat_message_id = message_id;

    select last_insert_id() into new_msg_id;

    # обновляем чат и время строки
    update chat_message
    set chat_message_chatid    = chat_id,
        chat_message_time      = msg_time,
        chat_message_creatorid = msg_creatorid,
        chat_message_forward   = 1
    where chat_message_id = new_msg_id;
END//
DELIMITER ;