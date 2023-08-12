<?php

namespace Aladser\Controllers;

use Aladser\Core\Controller;
use Aladser\Core\DB\DBCtl;
use Aladser\Models\UsersDBTableModel;
use Aladser\Models\ContactsDBTableModel;
use Aladser\Models\MessageDBTableModel;

/** контроллер контактов */
class MessageController extends Controller
{
    public function __construct(DBCtl $dbCtl= null)
    {
        parent::__construct($dbCtl);
    }
}
