<?php

namespace Aladser\Controllers;

use Aladser\Core\Controller;

/** контрллер проверки уникальности никнейма */
class IsUniqueNicknameController extends Controller
{
    public function index($getArgs)
    {
        echo $this->dbCtl->getUsers()->isUniqueNickname($_POST['nickname']) ? 1 : 0;
    }
}
