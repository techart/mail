<?php

namespace Techart\Mail;

    /**
     * Transport
     *
     * Модуль предоставляет классы для отправки сообщений
     *
     * @package \Techart\Mail\Transport
     * @version 0.2..0
     */


/**
 * @package \Techart\Mail\Transport
 */
class Transport
{
    /**
     * Фабричный метод, возвращает объект класса Mail.Transport.Sendmail.Sender
     *
     * @param array $options
     *
     * @return \Techart\Mail\Transport\SendmailSender
     */
    static public function sendmail(array $options = array())
    {
        return new \Techart\Mail\Transport\SendmailSender($options);
    }

    /**
     * Фабричный метод, возвращает объект класса Mail.Transport.PHP.Sender
     *
     * @return \Techart\Mail\Transport\PHPSender
     */
    static public function php()
    {
        return new \Techart\Mail\Transport\PHPSender();
    }

}
