<?php
require_once('core/db/DBQueryClass.php');
require_once('core/phpmailer/EMailSender.php');
require_once 'core/ConfigClass.php';

require_once 'core/model.php'; 
require_once 'core/view.php'; 
require_once 'core/controller.php'; 
require_once 'core/route.php';

require_once('core/db/TableDBModel.php');
require_once('core/db//UsersDBModel.php');

Route::start(); // запускаем маршрутизатор