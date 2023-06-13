<?php
require_once(dirname(__DIR__, 1).'/core/ConfigClass.php');
require_once(dirname(__DIR__, 1).'/core/phpmailer/EMailSender.php');
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
        $email = $_POST['email'];
        //$addUserRslt = $users->addUser($email, $_POST['password']);
        $addUserRslt = 1; 
        if($addUserRslt === 1) {
            logIn($users, $email);
            $text = '
            <body>
            <p>Что бы подтвердить Email, перейдите по <a href="http://example.com/confirmed.php?hash=' . 1234 . '">ссылка</a></p>
            </body>
            ';
            echo $CONFIG->getEmailSender()->send('Месенджер: подтвердите e-mail', $text, $email);
        }
        else{
            $data['result'] = 'add_user_error';
            echo json_encode($data);
        }
    }
    else{
        $data['result'] = 'user_exists';
        echo json_encode($data);
    }
}