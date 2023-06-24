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

create table contacts(
	id int auto_increment primary key,
	user_id int not null,
	contact_id int not null
);

select user_nickname from users where user_nickname  != '' and user_nickname is not null and user_nickname  like '%ala%'
and user_email not in (select user_email from users where user_hide_email  = 0 and user_email  like '%ala%')
union 
select user_email from users where user_hide_email  = 0 and user_email  like '%ala%';