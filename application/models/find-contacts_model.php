<?php

/** поиск контактов */
class FindContactsModel extends \core\Model
{
	private $users;

    public function __construct($CONFIG){
        $this->users = $CONFIG->getUsers();
    }

    public function run(){
        echo json_encode($this->users->getUsers($_GET['user']));
    }
}

