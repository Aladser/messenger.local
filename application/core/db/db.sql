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
insert into users(user_email, user_nickname, user_password) values('aladser@mail.ru', 'admin', '$2y$10$H09UQUYdkD3uTmEXQsYQuukJNjF2XA1BGaBF0Deq0mu1qPLSEFZWe');
update users set user_email_confirmed = 1 where user_id = 1;

create view unhidden_emails as
select user_email from users where user_hide_email  = 0;

drop table if exists contacts;
create table contacts(
	id int auto_increment primary key,
	user_id int not null,
	contact_id int not null
);

"
select user_nickname from users where user_nickname  != '' and user_nickname is not null and user_nickname  like '%ala%'
and user_email not in (select user_email from users where user_hide_email  = 0 and user_email  like '%ala%')
union 
select user_email from users where user_hide_email  = 0 and user_email  like '%ala%';

select user_nickname as username, user_photo from users 
where user_nickname!='' 
and user_nickname is not null 
and user_email!='sendlyamobile@gmail.com'
and user_email not in (select * from unhidden_emails)
and user_id in (select contact_id from contacts where user_id=(select user_id from users where user_email='sendlyamobile@gmail.com'))
union 
select user_email as username, user_photo from users 
where user_hide_email=0 
and user_email!='sendlyamobile@gmail.com'
and user_id in (select contact_id from contacts where user_id=(select user_id from users where user_email='sendlyamobile@gmail.com'));
"