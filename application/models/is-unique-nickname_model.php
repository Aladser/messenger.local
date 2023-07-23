<?php
/** Проверка уникальности никнейма */
class IsUniqueNicknameModel extends \core\Model
{
	private $users;

    public function __construct($CONFIG)
    {
        $this->users = $CONFIG->getUsers();
    }

    public function run()
    {
        echo $this->users->isUniqueNickname($_POST['nickname']) ? 1 : 0;
    }
}
?>


