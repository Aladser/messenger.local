create index i_user_publicname on users (user_nickname, user_email);
create index i_chat_participant_user on chat_participant (chat_participant_userid);
create index i_chat_participant_chat on chat_participant (chat_participant_chatid);
create index i_ws_id on connections (connection_ws_id);
create index i_addmsg on chat_message (chat_message_chatid, chat_message_time);
create index i_chat_message_chatid on chat_message (chat_message_chatid);
create index i_chat_type on chat (chat_type);
create index i_getdialogid on chat (chat_id, chat_type);