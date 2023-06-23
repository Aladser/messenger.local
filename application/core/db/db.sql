drop table if exists users;
create table users(
        user_id int AUTO_INCREMENT PRIMARY KEY,
        user_email varchar(100) UNIQUE,
        user_nickname varchar(100) UNIQUE,
        user_password varchar(255) not null,
        user_hash varchar(255),
        user_email_confirmed boolean default false,
        user_hide_email int(1) default 0,
        user_photo varchar(255);
);

insert into users(user_email, user_nickname, user_password) values('aladser@mail.ru', 'admin', '@admin@');
update users set user_email_confirmed = 1 where user_nickname = 'admin';