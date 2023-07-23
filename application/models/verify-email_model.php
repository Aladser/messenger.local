<?php

/** Подтверждение почты */
class VerifyEmailModel extends \core\Model
{
	private $users;

    public function __construct($CONFIG)
    {
        $this->users = $CONFIG->getUsers();
    }

    public function run()
    {
        $modelData = array();
        if($this->users->checkUserHash($_GET['email'], $_GET['hash'])){
            $this->users->confirmEmail($_GET['email']);
            return 'Электронная почта подтверждена';
        }
        else
        {
            return 'Ссылка недействительная или некорректная';
        }
    }
}
?>

