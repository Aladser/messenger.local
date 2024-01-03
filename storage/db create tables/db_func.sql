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

# --функция получить публичное имя пользователя
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