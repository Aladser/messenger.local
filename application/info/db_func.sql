# --триггер проверки тип чата: dialog или discussion--
delimiter //
CREATE TRIGGER check_chat_type BEFORE INSERT ON chat
FOR EACH ROW
begin
   IF NEW.chat_type not in ('dialog', 'discussion') then
	SIGNAL SQLSTATE '45000'
	SET MESSAGE_TEXT = 'chat_type не равен dialog или discussion';
   END if;
end //
delimiter ;


# --триггер на добавление сообщений. Проверка существования пользователя--
delimiter //
CREATE TRIGGER check_message BEFORE INSERT ON chat_message
FOR EACH ROW
	BEGIN
		if new.chat_message_creatorid not in (select chat_participant_userid from chat_participant where chat_participant_chatid=new.chat_message_chatid) then
		SIGNAL SQLSTATE '45000'
		SET MESSAGE_TEXT = 'пользователя нет в данном чате';
	END IF;
END //
delimiter ;


# --процедура создать чат 
DELIMITER //
CREATE PROCEDURE create_chat(
	in user1 int,
	in user2 int,
	out chatid int
)
begin
	insert into chat(chat_type) values('dialog');
	select last_insert_id() into chatid;
	insert into chat_participant(chat_participant_chatid, chat_participant_userid) values(chatid, user1), (chatid, user2);
END//
DELIMITER ;

# --функция получить публчное имя пользователя
DELIMITER //
CREATE FUNCTION getPublicUserName ( email varchar(100), nickname varchar(100), hide_email int(1) )
returns varchar(100)
DETERMINISTIC
begin
	IF hide_email = 1 THEN
		return nickname;
	ELSE
	   	return email;
	END IF;
END; //
DELIMITER ;