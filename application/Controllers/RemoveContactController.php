<?php

namespace Aladser\Controllers;

use Aladser\Core\Controller;

/** контроллер удаления группового чата */
class RemoveContactController extends Controller
{
    public function index($getArgs)
    {
        if ($_POST['type'] === 'group') {
            $chatId = $this->dbCtl->getMessageDBTable()->getDiscussionId($_POST['name']);
        } else {
            $clientId = $this->dbCtl->getUsers()->getUserId($_POST['clientName']);
            $contactId = $this->dbCtl->getUsers()->getUserId($_POST['name']);
            $chatId = $this->dbCtl->getMessageDBTable()->getDialogId($clientId, $contactId);
            
            $this->dbCtl->getContacts()->removeContact($clientId, $contactId);
        }
        echo $this->dbCtl->getMessageDBTable()->removeChat($chatId);
    }
}
