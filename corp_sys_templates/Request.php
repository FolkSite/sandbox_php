<?php

// хранилище данных для уровня представления и генерации ответов
// не позволяет другим классам напрямую обращаться к суперглобальным массивам
// чтобы централизовать работу с ними в одном классе, где можно применить к ним 
// фильтры и другии функции обработки
class Request
{

    private $properties;
    // канал связи для передачии информации из классов-контроллеров пользователю
    private $feedback = array();

    public function __construct()
    {
        $this->init();
    }

    public function init()
    {
        if (isset($_SERVER['REQUEST_METHOD'])) {
            // $_REQUEST поумолчанию содержит данные суперглобальных переменных
            $this->properties = $_REQUEST;
            return;
        }

        foreach ($_SERVER['argv'] as $arg) {
            if (strpos($arg, "=")) {
                // присваивает переменным из списка значения
                list($key, $val) = explode("=", $arg);
                $this->setProperty($key, $val);
            }
        }
    }

    public function getProperty($key)
    {
        if (isset($this->properties[$key])) {
            return $this->properties[$key];
        }

        return null;
    }

    public function setProperty($key, $val)
    {
        $this->properties[$key] = $val;
    }

    public function addFeedback($msg)
    {
        array_push($this->feedback, $msg);
    }

    public function getFeedbackString($separator = "<br>")
    {
        return implode($separator, $this->feedback);
    }

}