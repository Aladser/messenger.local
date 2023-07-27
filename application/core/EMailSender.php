<?php

namespace Aladser\Core;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EMailSender
{
    private $mail;

    /**
     * @throws Exception
     */
    public function __construct($smtpSrv, $username, $password, $smtpSecure, $port, $emailSender, $emailSenderName)
    {
        $this->mail = new PHPMailer();

        $this->mail->isSMTP();
        $this->mail->CharSet = "UTF-8";
        $this->mail->SMTPAuth = true;
        //$this->mail->SMTPDebug = 2; // показ логов
        $this->mail->Debugoutput = function ($str, $level) {
            $GLOBALS['data']['debug'][] = $str;
        };

        $this->mail->Host = $smtpSrv;     // SMTP сервера почты
        $this->mail->Username = $username;    // логин на почте
        $this->mail->Password = $password;    // пароль на почте
        $this->mail->SMTPSecure = $smtpSecure;  // тип шифрования
        $this->mail->Port = $port;        // порт
        $this->mail->setFrom($emailSender, $emailSenderName = 'Месенджер Админ'); // адрес почты и имя отправителя
    }

    /**
     * @throws Exception
     */
    public function send($title, $text, $emailRecipient)
    {
        $this->mail->clearAllRecipients(); // очистка отправителей

        $this->mail->addAddress($emailRecipient); // получатель письма
        $this->mail->isHTML(true);
        $this->mail->Subject = $title; // заголовок
        $this->mail->Body = $text;    // тело письма

        // Проверка отправления сообщения
        if ($this->mail->send()) {
            $data['result'] = "add_user_success";
        } else {
            $data['result'] = "add_user_error";
            $data['desc'] = "Причина ошибки: {$this->mail->ErrorInfo}";
        }

        return json_encode($data);
    }
}
