<?php

class RegUserModel extends \core\Model
{
	private $users;
    private $eMailSender;

    public function __construct($CONFIG){
        $this->users = new \core\db\DBUsersTableModel($CONFIG->getDBQueryCtl());
        $this->eMailSender = $CONFIG->getEmailSender();
    }

    //***** ДОБАВИТЬ ПОЛЬЗОВАТЕЛЯ *****/
    public function getData(){
        if(!$this->users->existsUser($_POST['email'])){
            $email = $_POST['email'];
            $addUserRslt = $this->users->addUser($email, $_POST['password']);
            if($addUserRslt === 1) {
                $hash = md5($email . time());
                $this->users->addUserHash($email, $hash);
                $text = '
                <body>
                <p>Для подтверждения электронной почты перейдите по <a href="http://messenger.local/verify-email?email='.$email.'&hash='.$hash.'">ссылке</a></p>
                </body>
                ';
                $data['result'] = $this->eMailSender->send('Месенджер: подтвердите e-mail', $text, $email);
            }
            else{
                $data['result'] = 'add_user_error';
            }
        }
        else{
            $data['result'] = 'user_exists';
        }

        echo json_encode($data);
    }
}

