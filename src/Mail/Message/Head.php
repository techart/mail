<?php

namespace Techart\Mail\Message;

/**
 * Заголовок сообщения
 *
 * @package Mail\Message
 */
class Head
    implements
    \IteratorAggregate,
    \Techart\Core\IndexedAccessInterface,
    \Techart\Core\EqualityInterface,
    \Techart\Core\CloneInterface
{

    protected $fields;

    /**
     * Парсит и декодирует поля заголовка из строки
     *
     * @param string $data
     *
     * @return \Techart\Mail\Message\Head
     */
    static public function from_string($data)
    {
        $head = new \Techart\Mail\Message\Head();
        foreach (\Techart\MIME::decode_headers($data) as $k => $f)
            foreach ((array)$f as $v)
                $head->field($k, $v);
        return $head;
    }

    /**
     * Конструктор
     *
     */
    public function __construct()
    {
        $this->fields = new \ArrayObject();
    }

    /**
     * Клонирование
     *
     */
    public function __clone()
    {
        $this->fields = clone $this->fields;
        foreach ($this->fields as &$field) {
            $field = clone $field;
        }
    }

    /**
     * Возвращает итератор по полям заголовка
     *
     * @return \Iterator
     */
    public function getIterator()
    {
        return $this->fields->getIterator();
    }

    /**
     * Добавляет к заголовку новое поле
     *
     * @param string $name
     * @param string $value
     *
     * @return \Techart\Mail\Message\Head
     */
    public function field($name, $value)
    {
        $this->fields[] = new \Techart\Mail\Message\Field($name, $value);
        return $this;
    }

    /**
     * Добавляет к заголовку поля из массива $values
     *
     * @param  $values
     *
     * @return \Techart\Mail\Message\Head
     */
    public function fields($values)
    {
        foreach ($values as $name => $value)
            $this->field($name, $value);
        return $this;
    }

    /**
     * Возвращает поле заголовка
     *
     * @param string $index
     *
     * @return string
     */
    public function offsetGet($index)
    {
        return is_int($index) ? $this->fields[$index] : $this->get($index);
    }

    /**
     * Устанавливает или добаляет поле к заголовку
     *
     * @param string $index
     * @param string $value
     *
     * @return string
     * @throws \Techart\Core\MissingIndexedPropertyException
     * @throws \Techart\Core\ReadOnlyIndexedPropertyException
     */
    public function offsetSet($index, $value)
    {
        if (is_int($index)) {
            throw isset($this[$index]) ?
                new \Techart\Core\ReadOnlyIndexedPropertyException($index) :
                new \Techart\Core\MissingIndexedPropertyException($index);
        } else {
            if ($this->offsetExists($index)) {
                $this[$index]->body = $value;
            } else {
                $this->field($index, $value);
            }
        }
        return $this;
    }

    /**
     * Проверяет установелнно ли поле с именем $index
     *
     * @param string $index
     *
     * @return boolean
     */
    public function offsetExists($index)
    {
        return is_int($index) ?
            isset($this->fields[$index]) :
            ($this->get($index) ? true : false);
    }

    /**
     * Выбрасывает исключение Core.NotImplementedException
     *
     * @param string $index
     *
     * @throws \Techart\Core\NotImplementedException
     */
    public function offsetUnset($index)
    {
        throw new \Techart\Core\NotImplementedException();
    }

    /**
     * Проверяет установелнно ли поле с именем $name
     *
     * @param string $name
     *
     * @return \Techart\Mail\Message\Field
     */
    public function get($name)
    {
        foreach ($this->fields as $field)
            if ($field->matches((string)$name)) {
                return $field;
            }
        return null;
    }

    /**
     * Возвращает ArrayObject всех полей заголовка с именем $name
     *
     * @param string $name
     *
     * @return \ArrayObject
     */
    public function get_all($name)
    {
        $result = new \ArrayObject();
        foreach ($this->fields as $field)
            if ($field->matches((string)$name)) {
                $result[] = $field;
            }
        return $result;
    }

    /**
     * Возвращает количество полей с именем $name
     *
     * @param string $name
     *
     * @return int
     */
    public function count_for($name)
    {
        $count = 0;
        foreach ($this->fields as $field)
            $count += $field->matches((string)$name) ? 1 : 0;
        return $count;
    }

    /**
     * Кодирует заголовок
     *
     * @return string
     */
    public function encode()
    {
        $encoded = '';
        foreach ($this->fields as $field)
            $encoded .= $field->encode() . \Techart\MIME::LINE_END;
        return $encoded;
    }

    /**
     * @param  $to
     *
     * @return boolean
     */
    public function equals($to)
    {
        $r = $to instanceof self;
        $ar1 = $this->getIterator()->getArrayCopy();
        $ar2 = $to->getIterator()->getArrayCopy();
        $r = $r && (count($ar1) == count($ar2));
        foreach ($ar1 as $v) {
            $r = $r && (\Techart\Core::equals($v, $to->get($v->name)));
        }
        return $r;
    }
}
