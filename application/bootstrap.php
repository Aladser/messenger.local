<?php

namespace Aladser;

use Aladser\Core\Route;

require __DIR__ . "/vendor/autoload.php";

date_default_timezone_set('Europe/Moscow');

// проверить существование папки data/temp.
$tempDir = __DIR__ . '/data/temp';
if (!is_dir($tempDir)) {
    mkdir($tempDir, 0777);
};

Route::start();
