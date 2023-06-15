<?php

require_once 'core/db/DBQueryClass.php';
require_once 'core/ConfigClass.php';

require_once 'core/model.php'; 
require_once 'core/view.php'; 
require_once 'core/controller.php'; 
require_once 'core/route.php';

require_once 'core/db/TableDBModel.php';
require_once 'core/db//UsersDBModel.php';

require_once 'core/phpmailer/PHPMailer.php';
require_once 'core/phpmailer/SMTP.php';
require_once 'core/phpmailer/Exception.php';
require_once 'core/phpmailer/EMailSender.php';

Route::start();