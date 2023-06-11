<?php
require_once(dirname(__DIR__, 1).'/core/ConfigClass.php');
require_once('UsersDBModel.php');

// функция авторизации
function logIn($usersModel, $login){
    // добавить хэш пользователю
    $usersModel->addUserHash($login); 
    $_SESSION['auth'] = 1;
    $_SESSION['login'] = $login;
}

$users = new UsersDBModel($CONFIG->getDBQueryClass());

//***** РЕГИСТРАЦИЯ ПОЛЬЗОВАТЕЛЯ *****/
if(isset($_POST['registration'])){
    if(!$users->existsUser($_POST['email'])){
        $addUserRslt = $users->addUser($_POST['email'], $_POST['password']); 
        if($addUserRslt === 1) {
            logIn($users, $_POST['email']);
            echo 'user_reg';
        }
        else{
            echo "add_user_error";
        }
    }
    else{
        echo 'user_exists';
    }
}