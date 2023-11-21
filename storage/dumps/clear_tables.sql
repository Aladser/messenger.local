delete from connections;
ALTER TABLE connections AUTO_INCREMENT = 1;
delete from contacts;
ALTER TABLE contacts AUTO_INCREMENT = 1;

delete from chat;
ALTER TABLE chat AUTO_INCREMENT = 1;
delete from chat_participant;
ALTER TABLE chat_participant AUTO_INCREMENT = 1;
delete from chat_message;
ALTER TABLE chat_message AUTO_INCREMENT = 1;
delete from contacts;
ALTER TABLE contacts AUTO_INCREMENT = 1;

delete from users;
ALTER TABLE users AUTO_INCREMENT = 1;

insert into users(user_email, user_nickname, user_password)
values ('aladser@mail.ru', 'Admin', '$2y$10$H09UQUYdkD3uTmEXQsYQuukJNjF2XA1BGaBF0Deq0mu1qPLSEFZWe');
insert into users(user_email, user_nickname, user_password)
values ('aladser@gmail.com', 'Aladser', '$2y$10$H09UQUYdkD3uTmEXQsYQuukJNjF2XA1BGaBF0Deq0mu1qPLSEFZWe');
insert into users(user_email, user_nickname, user_password)
values ('lauxtec@gmail.com', 'Lauxtec', '$2y$10$H09UQUYdkD3uTmEXQsYQuukJNjF2XA1BGaBF0Deq0mu1qPLSEFZWe');
insert into users(user_email, user_nickname, user_password)
values ('sendlyamobile@gmail.com', 'Barashka', '$2y$10$H09UQUYdkD3uTmEXQsYQuukJNjF2XA1BGaBF0Deq0mu1qPLSEFZWe');
insert into users(user_email, user_password)
values ('denisdyo17@gmail.com', '$2y$10$H09UQUYdkD3uTmEXQsYQuukJNjF2XA1BGaBF0Deq0mu1qPLSEFZWe');
insert into users(user_email, user_password)
values ('aladser@yandex.ru', '$2y$10$H09UQUYdkD3uTmEXQsYQuukJNjF2XA1BGaBF0Deq0mu1qPLSEFZWe');