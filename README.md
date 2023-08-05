# messenger.local

* Сайт сделан на основе **MVC-фреймворка** в *Linux Ubuntu 22.02*.
* Бэк-часть: **PHP 7.4**.
* Фронт-часть: чистый **JavaScript**, **Bootstrap** CSS и чистый **CSS**.
* В качестве асинхронного общения между клиентом и сервером используется **вебсокет**: встроенная **JS-библиотека** и **PHP Ratchet**.
Обмен сообщениями вебсокета происходит с помощью бэк-класса *Aladser\Core\Chat* и фронт-файла *chats.js*
Есть файл логов вебсокета, где записываются логи последнего запуска вебсокета
* Название БД: **messenger**. Тип БД: *MySQL*. Из-за ограничений MySQL используются триггеры на добавление сообщения и создание чата (проверка пользователя-отправителя и типа чата ). 
* **Дамп БД** находится в корне сайта.
* Для отправки почты используются **phpmailer** и **mail.ru SMTP-сервер**. За отправку писем отвечает класс *Aladser\Core\EMailSender*
* Конфигурация находится в **/application/Core/ConfigClass.php**, в классе *Aladser\Core\ConfigClass*
* Взаимодействие между фронтом и бэком: фронт делает запрос к данным в БД через соответстующие классы моделей таблиц в пространстве имен *Aladser\Core\DB*.
  + DBTableModel - абстрактная модель БД таблицы
  + DBQueryCtl - делает запросы в БД
  + UsersDBTableModel - модель БД таблицы пользователей
  + MessageDBTableModel - модель БД таблицы сообщений
  + ContactsDBTableModel- модель БД таблицы контактов
  + ConnectionsDBTableModel - модель БД таблицы соединений пользователей
* Автозагрузка классов сайта, библиотек Ratchet и phpmailer производится через composer
* boostrap.php - запуск сайта и бэка вебсокета
* chat-server.php работа бэк-вебсокета
* Изображения профилей хранятся в */application/data/profile_photos*. Когда выбирается файл изображения в проводнике, временно файл загружается в */application/data/temp*. При сохранении
  файл перемещается в profile_photos
* Авторизация пользователя сохраняется в куки без возможности отключения
* *НазваниеКласса.php* - файл с классов, *название_файла.php* - файл без класса

Особенности:
* Изменения исходного макета:
  + визуальная шапка сайта;
  + сообщения контакта окрашены в серый цвет;
  + поле поиска пользователей;
  + полоса прокрутки сообщений, контактов и групповых чатов;
  + над кнопкой отправки сообщения пишутся сообщения о состоянии подключений пользователей к серверу;
  + линии, отделяющие контакты, сообщения и настройки, тоньше;
  + линия, отделяющая контакты от групповых чатов, имеет отступы по горизонтали;
  + в заголовке чата выделено жирным название пользователя, с кем открыт диалог, или группового чата;
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
* CSRF-защита только для HTTP-запросов, которые добавляют данные из клиента в БД. В вебсокете нет CSRF-защиты.
* Для защиты от SQL-инъекций используются подготовленные запросы PHP PDO.
* Для защиты от XSS-атак используется экранирование символов при отправке каких-либо данных. В сообщениях можно увидеть HTML-код.

