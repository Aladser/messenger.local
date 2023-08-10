<?php

namespace Aladser\Models;

use Aladser\Core\Model;

/** контакты группового чата */
class GetGroupContactsModel extends Model
{
    public function getDiscussionCreatorId($discussionId)
    {
        return $this->dbCtl->getMessageDBTable()->getDiscussionCreatorId($discussionId);
    }

    public function getGroupContacts($discussionId)
    {
        return $this->dbCtl->getContacts()->getGroupContacts($discussionId);
    }
}
