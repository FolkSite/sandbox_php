<?php
/**
 * синглтон позволяет избежать использования глобальных переменных
 * данные хронятся как бы в кэше
 */
class Preference{
    private $props = array();
    private static $instance;
    
    // если у класса приватный конструктор, то снаружи его невозможно создать
    private function __construct()
    {
        
    }
    
    public static function getInstance() {
        // проверяет, был ли уже создан объект и если нет, то создает его
        if (empty(self::$instance)) {
            // класс с закрытым конструктором может сам
            // себя создать
            self::$instance = new Preference();
        }
        // возвращает ссылку на созданный объект
        return self::$instance;
    }
    
    public function setProperty($key, $val) {
        $this->props[$key] = $val;
    }
    
    public function getProperty($key) {
        return $this->props[$key];
    }
}

$pref = Preference::getInstance();
$pref->setProperty("name", "Иван");

// объект получен по ссылке, поэтому, при уничтожении переменной - объект остается
unset($pref);

$pref2 = Preference::getInstance();
var_dump($pref2->getProperty("name"));

$pref3 = Preference::getInstance();
$pref3->setProperty("city", "Краснодар");

unset($pref3);

$pref4 = Preference::getInstance();
var_dump($pref4->getProperty("city"));
var_dump($pref4->getProperty("name"));

