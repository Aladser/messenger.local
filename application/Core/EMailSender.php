<?php

namespace Aladser\Core;

use PHPMailer\PHPMailer\PHPMailer;

class EMailSender
{
    private $mail;

    public function __construct()
    {
        $this->mail = new PHPMailer();

        $this->mail->isSMTP();
        $this->mail->CharSet = 'UTF-8';
        $this->mail->SMTPAuth = true;
        // $this->mail->SMTPDebug = 2; // показ логов
        $this->mail->Debugoutput = function ($str, $level) {
            $GLOBALS['data']['debug'][] = $str;
        };

        $this->mail->Host = Config::SMTP_SRV;           // SMTP сервера почты
        $this->mail->Username = Config::EMAIL_USERNAME; // логин на почте
        $this->mail->Password = Config::EMAIL_PASSWORD; // пароль на почте
        $this->mail->SMTPSecure = Config::SMTP_SECURE;  // тип шифрования
        $this->mail->Port = Config::SMTP_PORT;          // порт
        $this->mail->setFrom(Config::EMAIL_SENDER, Config::EMAIL_SENDER_NAME); // адрес почты и имя отправителя
    }

    public function send($title, $text, $emailRecipient)
    {
        $this->mail->clearAllRecipients(); // очистка отправителей

        $this->mail->addAddress($emailRecipient); // получатель письма
        $this->mail->isHTML(true);
        $this->mail->Subject = $title; // заголовок
        $this->mail->Body = $text;    // тело письма

        // Проверка отправления сообщения
        if ($this->mail->send()) {
            $data['result'] = 'add_user_success';
        } else {
            $data['result'] = 'add_user_error';
            $data['desc'] = "Причина ошибки: {$this->mail->ErrorInfo}";
        }

        return json_encode($data);
    }
}
