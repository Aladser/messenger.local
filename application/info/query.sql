insert into chat(chat_type) values('dialog');
select last_insert_id() into @chat_last_index;
insert into chat_participant(chat_participant_chatid, chat_participant_userid) values(@chat_last_index, 1), (@chat_last_index, 4);
insert into chat_message(chat_message_chatid, chat_message_text, chat_message_user_creatorid) values(1, 'Привет', 4);