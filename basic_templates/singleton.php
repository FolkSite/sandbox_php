<?php
/**
 * синглтон позволяет избежать использования глобальных переменных
 */
class Preference{
    private $props = array();
    private static $instance;
    
    // если у класса приватный конструктор, то снаружи его невозможно создать
    private function __construct()
    {
        
    }
    
    public static function getInstance() {
        if (empty(self::$instance)) {
            // класс с закрытым конструктором может сам
            // себя создать
            self::$instance = new Preference();
        }
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

unset($pref);

$pref2 = Preference::getInstance();
var_dump($pref2->getProperty("name"));

$pref3 = Preference::getInstance();
$pref3->setProperty("city", "Краснодар");

unset($pref3);

$pref4 = Preference::getInstance();
var_dump($pref4->getProperty("city"));
var_dump($pref4->getProperty("name"));

