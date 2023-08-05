<?php

namespace Aladser;

use Aladser\Core\Route;
use Aladser\Core\ConfigClass;

require __DIR__ . '/vendor/autoload.php';
date_default_timezone_set('Europe/Moscow');

$CONFIG = new ConfigClass();
$process = $CONFIG->getWebsocketProcess();
if (!$process->isActive()){
    $process->enable();
}

Route::start();