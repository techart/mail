<?php

namespace Techart\Mail\Message;

/**
 * Часть почтового сообщения
 *
 * @package Mail\Message
 */
class Part
    implements \Techart\Core\PropertyAccessInterface,
    \Techart\Core\StringifyInterface,
    \Techart\Core\CallInterface,
    \Techart\Core\EqualityInterface,
    \Techart\Core\CloneInterface
{

    protected $head;
    protected $body;

    /**
     * Конструктор
     *
     */
    public function __construct()
    {
        $this->head = \Techart\Mail\Message::Head();
    }

    /**
     * Клонирование
     *
     */
    public function __clone()
    {
        $this->head = clone $this->head;
        if (is_object($this->body)) {
            $this->body = clone $this->body;
            if ($this->body instanceof \ArrayObject) {
                foreach ($this->body as &$part) {
                    $part = clone $part;
                }
            }
        }
    }

    /**
     * Устанавливает заголовок сообщения
     *
     * @param \Techart\Mail\Message\Head $head
     *
     * @return \Techart\Mail\Message\Part
     */
    public function head(\Techart\Mail\Message\Head $head)
    {
        $this->head = $head;
        return $this;
    }

    /**
     * Добавляет новое поля к заголовку письма
     *
     * @param string $name
     * @param string $value
     *
     * @return \Techart\Mail\Message\Part
     */
    public function field($name, $value)
    {
        $this->head[$name] = $value;
        return $this;
    }

    /**
     * Добавляет несколько полей заголовка из массива $headers
     *
     * @param array $headers
     *
     * @return \Techart\Mail\Message\Part
     */
    public function headers(array $headers)
    {
        foreach ($headers as $k => $v)
            $this->head[$k] = $v;
        return $this;
    }

    /**
     * Заполняет письмо из файла, т.е. получаетя attach к письму
     *
     * @param        $file
     * @param string $name
     *
     * @return \Techart\Mail\Message\Part
     */
    public function file($file, $name = '')
    {
        if (!($file instanceof \Techart\IO\FS\File)) {
            $file = \Techart\IO\FS::File((string)$file);
        }
        if (!$file->exists()) {
            throw new \RuntimeException('Attachment File not Found');
        }

        $this->head['Content-Type'] = array($file->content_type, 'name' => ($name ? $name : $file->name));
        $this->head['Content-Transfer-Encoding'] = $file->mime_type->encoding;
        $this->head['Content-Disposition'] = 'attachment';

        $this->body = $file;

        return $this;
    }

    /**
     * Устанавливает содержимае письма
     *
     * @param  $body
     *
     * @return \Techart\Mail\Message\Part
     */
    public function body($body)
    {
        $this->body = $body;
        return $this;
    }

    /**
     * Заполняет письмо из потока
     *
     * @param \Techart\IO\Stream\AbstractStream $stream
     * @param                          $content_type
     *
     * @return \Techart\Mail\Message\Part
     */
    public function stream(\Techart\IO\Stream\AbstractStream $stream, $content_type = null)
    {
        if ($content_type) {
            $this->head['Content-Type'] = $content_type;
            $this->head['Content-Transfer-Encoding'] = \Techart\MIME::type($this->head['Content-Type']->value)->encoding;
        }
        $this->body = $stream;
        return $this;
    }

    /**
     * Заполняет письмо ввиде простого текста
     *
     * @param string $text
     * @param        $content_type
     *
     * @return $this
     */
    public function text($text, $content_type = null)
    {
        $this->head['Content-Type'] = $content_type ?
            $content_type :
            array('text/plain', 'charset' => \Techart\MIME::default_charset());

        $this->head['Content-Transfer-Encoding'] =
            \Techart\MIME::type($this->head['Content-Type']->value)->encoding;

        $this->body = (string)$text;

        return $this;
    }

    /**
     * Заполняет письмо ввиде html
     *
     * @param string $text
     *
     * @return $this
     */
    public function html($text)
    {
        return $this->text(
            $text,
            array('text/html', 'charset' => \Techart\MIME::default_charset())
        );
    }

    /**
     * Доступ на чтение к совйствам объекта
     *
     * @param string $property
     *
     * @return mixed
     * @throws \Techart\Core\MissingPropertyException
     */
    public function __get($property)
    {
        switch ($property) {
            case 'head':
            case 'body':
                return $this->$property;
            default:
                throw new \Techart\Core\MissingPropertyException($property);
        }
    }

    /**
     * Доступ на запись к свойствам объекта
     *
     * @param string $property
     * @param        $value
     *
     * @return mixed
     * @throws \Techart\Core\MissingPropertyException
     * @throws \Techart\Core\ReadOnlyPropertyException
     */
    public function __set($property, $value)
    {
        switch ($property) {
            case 'body':
                $this->body($value);
                return $this;
            case 'head':
                throw new \Techart\Core\ReadOnlyPropertyException($property);
            default:
                throw new \Techart\Core\MissingPropertyException($property);
        }
    }

    /**
     * Проверяет установлено ли свойство
     *
     * @param string $property
     *
     * @return boolean
     */
    public function __isset($property)
    {
        switch ($property) {
            case 'body':
            case 'head':
                return true;
            default:
                return false;
        }
    }

    /**
     * Выкидывает исключение Core.NotImplementedException
     *
     * @param string $property
     *
     * @throws \Techart\Core\NotImplementedException
     */
    public function __unset($property)
    {
        throw new \Techart\Core\NotImplementedException();
    }

    /**
     * Возвращает закодированное письмо ввиде строки
     *
     * @return string
     */
    public function as_string()
    {
        return \Techart\Mail\Serialize::Encoder()->encode($this);
    }

    /**
     * Возвращает закодированное письмо ввиде строки
     *
     * @return string
     */
    public function __toString()
    {
        return $this->as_string();
    }

    /**
     * С помощью вызова метода можно установить/добавить поле к заголовку письма
     *
     * @param string $method
     * @param        $args
     *
     * @return \Techart\Mail\Message\Part
     */
    public function __call($method, $args)
    {
        $this->head[$this->field_name_for_method($method)] = $args[0];
        return $this;
    }

    /**
     * @param string $method
     *
     * @return string
     */
    protected function field_name_for_method($method)
    {
        return \Techart\Core\Strings::replace($method, '_', '-');
    }

    /**
     * @param  $to
     *
     * @return boolean
     */
    public function equals($to)
    {
        $r = $to instanceof self &&
            \Techart\Core::equals($this->head, $to->head);

        $this_body = ($this->body instanceof \Techart\IO\Stream\AbstractStream ||
            $this->body instanceof \Techart\IO\FS\File) ?
            $this->body->load() :
            $this->body;

        $to_body = ($to->body instanceof \Techart\IO\Stream\AbstractStream ||
            $to->body instanceof \Techart\IO\FS\File) ?
            $to->body->load() :
            $to->body;

        return $r && \Techart\Core::equals($this_body, $to_body);
    }
}
