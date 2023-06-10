<?php
require_once(dirname(__DIR__, 1).'/core/ConfigClass.php');
require_once('UsersDBModel.php');

$users = new UsersDBModel($CONFIG->getDBQueryClass());

// проверка сущестования пользователя
if(isset($_POST['registration'])){
    echo $users->existsUser($_POST['email']) ? 1 : 0;
}