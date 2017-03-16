<?php

namespace Techart\Mail\Message;

class Field
    extends \Techart\Object\Struct
    implements \Techart\Core\IndexedAccessInterface,
    \Techart\Core\StringifyInterface,
    \Techart\Core\EqualityInterface
{

    static protected $acronyms = array(
        'mime' => 'MIME', 'ldap' => 'LDAP', 'soap' => 'SOAP', 'swe' => 'SWE',
        'bcc' => 'BCC', 'cc' => 'CC', 'id' => 'ID');

    const EMAIL_REGEXP = '#(?:[a-zA-Z0-9_\.\-\+])+\@(?:(?:[a-zA-Z0-9\-])+\.)+(?:[a-zA-Z0-9]{2,4})#';
    const EMAIL_NAME_REGEXP = "{(?:(.+)\s?)?(<?(?:[a-zA-Z0-9_\.\-\+])+\@(?:(?:[a-zA-Z0-9\-])+\.)+(?:[a-zA-Z0-9]{2,4})>?)$}Ui";
    const ATTR_REGEXP = '{;\s*\b([a-zA-Z0-9_\.\-]+)\s*\=\s*(?:(?:"([^"]*)")|(?:\'([^\']*)\')|([^;\s]*))}i';

    protected $name;
    protected $value;
    protected $attrs = array();

    /**
     * Конструктор
     *
     * @param string $name
     * @param string $body
     * @param array $attrs
     */
    public function __construct($name, $body, $attrs = array())
    {
        $this->name = $this->canonicalize($name);
        $this->set_body($body);
    }

    /**
     * Проверяет соответствие имени поля указанному имени
     *
     * @param string $name
     *
     * @return boolean
     */
    public function matches($name)
    {
        return \Techart\Core\Strings::downcase($this->name) ==
        \Techart\Core\Strings::downcase(\Techart\Core\Strings::trim($name));
    }

    /**
     * Возвращает значение атрибута поля
     *
     * @param string $index
     *
     * @return string
     */
    public function offsetGet($index)
    {
        return isset($this->attrs[$index]) ? $this->attrs[$index] : null;
    }

    /**
     * Устанавливает значение атрибута поля
     *
     * @param string $index
     * @param string $value
     *
     * @return string
     */
    public function offsetSet($index, $value)
    {
        $this->attrs[(string)$index] = $value;
        return $this;
    }

    /**
     * Проверяет, установлен ли атрибут поля
     *
     * @param string $index
     *
     * @return boolean
     */
    public function offsetExists($index)
    {
        return isset($this->attrs[$index]);
    }

    /**
     * Удаляет атрибут
     *
     * @param string $index
     */
    public function offsetUnset($index)
    {
        unset($this->attrs[$index]);
    }

    /**
     * Возвращает поле ввиде закодированной строки
     *
     * @return string
     */
    public function as_string()
    {
        return $this->encode();
    }

    /**
     * Возвращает поле ввиде строки
     *
     * @return string
     */
    public function __toString()
    {
        return $this->as_string();
    }


    /**
     * Кодирует поле
     *
     * TODO: iconv_mime_encode не верно кодирует длинные строки
     *
     * @return string
     */
    public function encode()
    {
        $body = $this->name . ': ' . $this->encode_value($this->value, false) . ';';
        foreach ($this->attrs as $index => $value) {
            $attr = $this->encode_attr($index, $value);
            $delim = (($this->line_length($body) + strlen($attr) + 1) >= \Techart\MIME::LINE_LENGTH) ?
                "\n " : ' ';
            $body .= $delim . $attr;
        }
        return substr($body, 0, strlen($body) - 1);
    }

    /**
     * Кодирует аттрибут поля
     *
     * @param string $index
     * @param string $value
     *
     * @return string
     */
    protected function encode_attr($index, $value)
    {
        $value = $this->encode_value($value);
        switch (true) {
            case $index == 'boundary':
            case strpos($value, ' '):
                return "$index=\"$value\";";
            default:
                return "$index=$value;";
        }
    }

    /**
     * Кодирует значение поля или значение аттрибута
     *
     * @param string $value
     * @param boolean $quote
     *
     * @return string
     */
    protected function encode_value($value, $quote = true)
    {
        if ($this->is_address_line($value)) {
            return $this->encode_email($value);
        } else {
            return $this->encode_mime($value, $quote);
        }
    }

    /**
     * Кодирует строку email адресов
     *
     * @param string $value
     *
     * @return string
     */
    protected function encode_email($value)
    {
        $result = array();
        foreach (explode(',', $value) as $k => $v)
            if (preg_match(self::EMAIL_NAME_REGEXP, $v, $m)) {
                $result[] = ($m[1] ? $this->encode_mime(trim($m[1]), false) : '') . ' ' . $m[2];
            } else {
                return $this->encode_mime($value, false);
            }
        return implode(',' . \Techart\MIME::LINE_END . ' ', $result);
    }

    /**
     * Обертка над iconv_mime_encode
     *
     * @param string $value
     * @param boolean $quote
     *
     * @return string
     */
    protected function encode_mime($value, $quote = true)
    {
        $q = $quote ? '"' : '';
        return !\Techart\MIME::is_printable($value) ? $q . preg_replace('{^: }', '', iconv_mime_encode(
                    '',
                    $value,
                    array(
                        'scheme' => 'B',
                        'input-charset' => 'UTF-8',
                        'output-charset' => 'UTF-8',
                        "line-break-chars" => \Techart\MIME::LINE_END
                    )
                )
            ) . $q : $value /*MIME::split($value)*/
            ;
    }

    /**
     * Возвращает последней строки в тексте
     *
     * @param string $txt
     *
     * @return int
     */
    private function line_length($txt)
    {
        return strlen(end(explode("\n", $txt)));
    }

    /**
     * Проверяет, является ли строка tmail адресом
     *
     * @param string $line
     *
     * @return boolean
     */
    protected function is_address_line($line)
    {
        return preg_match(self::EMAIL_REGEXP, $line);
    }

    /**
     * Установка свойства name извне запрещена
     *
     * @param string $name
     *
     * @throws \Techart\Core\ReadOnlyPropertyException
     */
    protected function set_name($name)
    {
        throw new \Techart\Core\ReadOnlyPropertyException('name');
    }

    /**
     * Устанавливает содержимое поля
     *
     * @param string|array|mixed $body
     *
     * @return string
     */
    protected function set_body($body)
    {
        $this->attrs = array();
        if (is_array($body)) {
            foreach ($body as $k => $v)
                switch (true) {
                    case is_string($k):
                        $this[$k] = $v;
                        break;
                    case is_int($k):
                        $this->value = (string)$v;
                }
        } else {
            $this->from_string((string)$body);
        }
        return $this;
    }

    /**
     * Производит разбор строки, извлекая аттрибуты
     *
     * @param string $body
     *
     * @return \Techart\Mail\Message\Field
     */
    public function from_string($body)
    {
        if (preg_match_all(self::ATTR_REGEXP, $body, $m, PREG_SET_ORDER)) {
            foreach ($m as $res) {
                $v = $res[2] ? $res[2] : ($res[3] ? $res[3] : ($res[4] ? $res[4] : null));
                if (isset($res[1]) && $v) {
                    $this[$res[1]] = $v;
                }
            }
            $this->value = trim(substr($body, 0, strpos($body, ';')));
        } else {
            $this->value = $body;
        }
        return $this;
    }

    /**
     * @return string
     */
    protected function get_body()
    {
        return $this->encode();
    }

    /**
     * Устанавливает значение поля
     *
     * @param string $value
     *
     * @return string
     */
    protected function set_value($value)
    {
        ;
        $this->value = (string)$value;
        return $this;
    }

    /**
     * Приводит имя к виду соответствующему почтовому стандарту
     *
     * @param string $name
     *
     * @return string
     */
    protected function canonicalize($name)
    {
        $parts = \Techart\Core\Arrays::map(
            'return strtolower($x);',
            \Techart\Core\Strings::split_by('-', trim($name))
        );

        foreach ($parts as &$part)
            $part = isset(self::$acronyms[$part]) ?
                self::$acronyms[$part] :
                (preg_match('{[aeiouyAEIOUY]}', $part) ? ucfirst($part) : strtoupper($part));

        return \Techart\Core\Arrays::join_with('-', $parts);
    }

    /**
     * @param  $to
     *
     * @return boolean
     */
    public function equals($to)
    {
        return $to instanceof self &&
        $this->value == $to->value &&
        $this->name == $to->name &&
        \Techart\Core::equals($this->attrs, $to->attrs);
    }
}
