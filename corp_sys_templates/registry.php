<?php
// пример синглтона
class Registry
{
    private static $instance = null;
    private $value = array();
    
    private function __construct()
    {
        
    }
    
    static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        
        return self::$instance;
    }
    
    public function get($key)
    {
        if (isset($this->value[$key])) {
            return $this->value[$key];
        }
        
        return null;
    }
    
    public function set($key, $value)
    {
        $this->values[$key] = $value;
    }
}

$reg = Registry::instance();
$reg->set('one', 'Is key = one');
var_dump($reg->get('one'));

$reg0 = Registry::instance();
var_dump($reg0->get('one'));