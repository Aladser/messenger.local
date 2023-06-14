<?php

require_once('UsersDBModel.php');
require_once(dirname(__DIR__, 1).'/core/ConfigClass.php');

$users = new UsersDBModel($CONFIG->getDBQueryClass());
$modelData = array();

if(isset($_GET['hash'])){
    if($users->checkUserHash($_GET['email'], $_GET['hash'])){
        $users->confirmEmail($_GET['email']);
        $modelData['srvResponse'] = 'Электронная почта подтверждена';
        $modelData['refCSSStyle'] = 'link-success nav-link bg-success w-25 text-center text-light mx-auto';
    }
    else
    {
        $modelData['srvResponse'] = 'Ссылка недействительная или некорректная';
        $modelData['refCSSStyle'] = 'link-success nav-link bg-warning w-25 text-center text-light mx-auto';
    }
}