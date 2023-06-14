<?php

class RegUserModel extends Model
{
    function run(){
        echo 'run() модели';
    }
}

//***** РЕГИСТРАЦИЯ ПОЛЬЗОВАТЕЛЯ *****/
/*
if(isset($_POST['registration']) && $users){
    if(!$users->existsUser($_POST['email'])){
        $email = $_POST['email'];
        $addUserRslt = $users->addUser($email, $_POST['password']);
        if($addUserRslt === 1) {
            // хэш
            $hash = md5($email . time());
            $addHashRslt = $users->addUserHash($email, $hash);
            //
            $_SESSION['auth'] = 1;
            $_SESSION['email'] = $email;
            $text = '
            <body>
            <p>Для подтверждения электронной почты перейдите по <a href="http://messenger.local/verify-email?email='.$email.'&hash='.$hash.'">ссылке</a></p>
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
*/