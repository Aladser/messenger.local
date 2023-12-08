<?php

namespace Aladser\Core;

use Aladser\Config;
use PHPMailer\PHPMailer\PHPMailer;

class EMailSender
{
    private PHPMailer $phpMailer;

    public function __construct()
    {
        $this->phpMailer = new PHPMailer();

        $this->phpMailer->isSMTP();
        $this->phpMailer->CharSet = 'UTF-8';
        $this->phpMailer->SMTPAuth = true;
        // $this->phpMailer->SMTPDebug = 2; // показ логов
        $this->phpMailer->Debugoutput = function ($str, $level) {
            $GLOBALS['data']['debug'][] = $str;
        };
        // SMTP сервера почты
        $this->phpMailer->Host = Config::SMTP_SRV;
        // логин на почте
        $this->phpMailer->Username = Config::EMAIL_USERNAME;
        // пароль на почте
        $this->phpMailer->Password = Config::EMAIL_PASSWORD;
        // тип шифрования
        $this->phpMailer->SMTPSecure = Config::SMTP_SECURE;
        // порт
        $this->phpMailer->Port = Config::SMTP_PORT;
        // адрес почты и имя отправителя
        $this->phpMailer->setFrom(Config::EMAIL_SENDER, Config::EMAIL_SENDER_NAME);
    }

    public function send($title, $text, $emailRecipient)
    {
        $this->phpMailer->isHTML(true);
        // очистка отправителей
        $this->phpMailer->clearAllRecipients();
        // получатель письма
        $this->phpMailer->addAddress($emailRecipient);
        // заголовок
        $this->phpMailer->Subject = $title;
        // тело письма
        $this->phpMailer->Body = $text;
        // Проверка отправления сообщения
        if ($this->phpMailer->send()) {
            $data['result'] = 'add_user_success';
        } else {
            $data['result'] = 'add_user_error';
            $data['desc'] = "Причина ошибки: {$this->phpMailer->ErrorInfo}";
        }

        return json_encode($data);
    }
}
