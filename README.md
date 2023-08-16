# Мессенджер

### Сайт разворачивается на Ubuntu + PHP7.4 + Apache + MySQL 8. Настройка запуска

* Записать в etc/apache2/sites-available/messenger.local.conf

```
<VirtualHost *:80>
        ServerName messenger.local
        DocumentRoot /var/www/messenger.local
        ErrorLog /var/www/messenger.local/logs/error.log
        CustomLog /var/www/messenger.local/logs/access.log combined
        <Directory /var/www/messenger.local>    
                Options Indexes FollowSymLinks               
                AllowOverride All               
                Require all granted    
        </Directory>      
</VirtualHost>
```

* Установить модуль:
``apt-get install php8.1-mysql``

* Включить перенаправление URL-адресов
``sudo a2enmod rewrite``

* права на запись в файлы
``chmod 777 -R var/messenger.local``

* Сайт сделан на основе **MVC-фреймворка** в *Linux Ubuntu 22.02*.
* В качестве асинхронного общения между клиентом и сервером используется **вебсокет**: встроенная **JS-библиотека** и **PHP Ratchet**.
Обмен сообщениями вебсокета происходит с помощью бэк-класса *Aladser\Core\Chat* и фронт-файла *chats.js*
Есть файл логов вебсокета, где записываются логи последнего запуска вебсокета
* Название БД: **messenger**. Тип БД: *MySQL*. Из-за ограничений MySQL используются триггеры на добавление сообщения и создание чата (проверка пользователя-отправителя и типа чата ). 
* Для отправки почты используются **phpmailer** и **mail.ru SMTP-сервер**. За отправку писем отвечает класс *Aladser\Core\EMailSender*
* Конфигурация находится в **/application/Core/ConfigClass.php**, в классе *Aladser\Core\ConfigClass*
* Взаимодействие между фронтом и бэком: фронт делает запрос к данным в БД через соответстующие классы моделей таблиц в пространстве имен *Aladser\Models*.
  + DBTableModel - абстрактная модель БД таблицы
  + DBQueryCtl - делает запросы в БД
  + UsersDBTableModel - модель БД таблицы пользователей
  + MessageDBTableModel - модель БД таблицы сообщений
  + ContactsDBTableModel- модель БД таблицы контактов
  + ConnectionsDBTableModel - модель БД таблицы соединений пользователей
* *boostrap.php* - запуск сайта и бэка вебсокета, *chat-server.php* - работа бэк-вебсокета
* Изображения профилей хранятся в */application/data/profile_photos*. Когда выбирается файл изображения в проводнике, временно файл загружается в */application/data/temp*. При сохранении
  файл перемещается в profile_photos
* Авторизация пользователя сохраняется в куки без возможности отключения
* в папке info SQL-код для создания таблиц и процедур

Особенности:
* БД таблицы:
  + пользователи **users**
  + контакты пользователей **contacts**
  + соединения **connections**
  + чаты (личные диалоги и групповые чаты) **chat**
  + участники чатов **chat_participant**
  + сообщения чатов **chat_message**
* При получении нового сообщения пользователем чат отправителя подсвечивается серым цветом. При отключении звуковых уведомлений визуальные уведомления не откючаются.
* Принятие, изменение, пересылка и удаление сообщений происходит через вебсокет. У каждой операции есть свой заголовок в вебсокете.
* Добавлена возможность пересылки не своих сообщений из чата
* Если вебсокет недоступен, то перед отправкой сообщения вылезет предупреждение. По умолчанию вебсокет висит на localhost
* Часть индексов - первичные ключи. Ручные индексы созданы для условий where в SQL-запросах.
* Создание чатов контактов и групповых чатов, добавление сообщений в БД совершается через процедуры.
* В групповой чат любой участник может добавить нового участника.
* Соединения клиентов добавляются в БД таблицу соединений connections.
* Пользователь в БД ищется по публичной почте или никнейму.
* Для всех-запросов с передачей POST-данных вставлена проверка CSRF-токена
* SQL-injection защита реализована через подготовленные запросы PDO
* XSS защита реализована через эканирование символов GET- и POST-запросов
* как дополнительный функционал можно удалять группы и контакты
