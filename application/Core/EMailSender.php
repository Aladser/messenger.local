<?php

namespace App\Core;

use PHPMailer\PHPMailer\PHPMailer;

use function App\config;

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
        $this->phpMailer->Host = config('SMTP_SRV');
        // логин на почте
        $this->phpMailer->Username = config('EMAIL_USERNAME');
        // пароль на почте
        $this->phpMailer->Password = config('EMAIL_PASSWORD');
        // тип шифрования
        $this->phpMailer->SMTPSecure = config('SMTP_SECURE');
        // порт
        $this->phpMailer->Port = config('SMTP_PORT');
        // адрес почты и имя отправителя
        $this->phpMailer->setFrom(config('EMAIL_SENDER'), config('EMAIL_SENDER_NAME'));
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
