<?php

namespace Techart\Mail;

class Message
{
    /**
     * @return object
     */
    public static function transport()
    {
        $class = \Techart\Core::config('mail:transport', '\\Techart\\Mail\\Transport\\PHPSender');
        return \Techart\Core::make($class);
    }

    /**
     * @param $msg
     */
    public static function send($msg)
    {
        $transport = self::transport();
        if ($transport) {
            $transport->send($msg);
        }
    }

    /**
     * фабричный метод, возвращает объект класса Mail.Message.Field
     *
     * @param string $name
     * @param string $body
     *
     * @return \Techart\Mail\Message\Field
     */
    static public function Field($name, $body)
    {
        return new \Techart\Mail\Message\Field($name, $body);
    }

    /**
     * фабричный метод, возвращает объект класса Mail.Message.Head
     *
     * @return \Techart\Mail\Message\Head
     */
    static public function Head()
    {
        return new \Techart\Mail\Message\Head();
    }

    /**
     * фабричный метод, возвращает объект класса Mail.Message.Part
     *
     * @return \Techart\Mail\Message\Part
     */
    static public function Part()
    {
        return new \Techart\Mail\Message\Part();
    }

    /**
     * фабричный метод, возвращает объект класса Mail.Message.Message
     *
     * @param boolean $nested
     *
     * @return \Techart\Mail\Message\Message
     */
    static public function Message($nested = false)
    {
        return new \Techart\Mail\Message\Message($nested);
    }

}
