<?php

namespace Techart\Mail\Transport;

/**
 * @package Mail\Transport\Sendmail
 */
class SendmailSender
{

    protected $options;

    /**
     * Конструктор
     *
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        \Techart\Core\Arrays::update($this->options, $options);
    }

    /**
     * Отправляет сообщение
     *
     * @param \Techart\Mail\Message\Message $message
     *
     * @return boolean
     */
    public function send(\Techart\Mail\Message\Message $message)
    {
        $pipe = \Techart\Proc::Pipe($this->sendmail_command(), 'wb');

        \Techart\Mail\Serialize::Encoder()->
        to_stream($pipe)->
        encode($message);

        return $pipe->close()->exit_status ? false : true;
    }

    /**
     * Возвращает команду для вызова sendmail
     *
     * @return string
     */
    protected function sendmail_command()
    {
        return $this->options['binary'] . ' ' . $this->options['flags'];
    }

}

