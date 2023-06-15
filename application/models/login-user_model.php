<?php

class LoginUserModel extends \core\Model
{
	private $users;

    public function __construct($CONFIG){
        $this->users = new \core\db\DBUsersTableModel($CONFIG->getDBQueryCtl());
    }

    //***** АУТЕНТИФИКАЦИЯ И АВТОРИЗАЦИЯ ПОЛЬЗОВАТЕЛЯ *****/
    public function getData(){
        session_start();
        if($this->users->existsUser($_POST['email'])){
            $loginUserRslt = $this->users->isAuthentication($_POST['email'], $_POST['password']);
            if($loginUserRslt == 1) {
                $_SESSION['auth'] = 1;
                $_SESSION['email'] = $_POST['email'];
                setcookie('auth', 1, time()+60*60*24, '/');
                setcookie('email', $_POST['email'], time()+60*60*24, '/');
                $data['result'] = 'login_user';
            }
            else{
                $data['result'] = 'login_user_wrong_password';
            }
        }
        else{
            $data['result'] = 'not_user_exists';
        }

        echo json_encode($data);
    }
}

