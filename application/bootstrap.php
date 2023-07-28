<?php

namespace Aladser;

use Aladser\Core\Route;

require __DIR__ . "/vendor/autoload.php";

date_default_timezone_set('Europe/Moscow');

// проверить существование папки data/temp.
$tempDir = dirname(__DIR__)
    . DIRECTORY_SEPARATOR
    . 'application' . DIRECTORY_SEPARATOR
    . 'data' . DIRECTORY_SEPARATOR
    . 'temp';

if (!is_dir($tempDir)) {
    mkdir($tempDir, 0777);
};

Route::start();
