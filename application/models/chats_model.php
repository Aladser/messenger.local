<?php

/** Добавить контакт(чат) для пользователя */
class ChatsModel extends \core\Model
{
    public function run(){
        session_start();
        return isset($_COOKIE['auth']) ?  $_COOKIE['email'] : $_SESSION['email'];
    }
}