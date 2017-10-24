<?php

namespace woo\domain;

//...

/*
 * Задача объекта - следить за всеми объектами системы, чтобы один объект не превратился в два
 */
class ObjectWatcher
{

    private $all = array();
    private static $instance = null;

    private function __construct()
    {
        
    }

    static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new ObjectWatcher();
        }
        
        return self::$instance;
    }
    
    public function globalKey(DomainObject $obj)
    {
        $key = get_class($obj).".".$obj->getId();
        return $key;
    }
    
    static function add(DomainObject $obj)
    {
        $inst = self::$instance();
        $inst->all[$inst->globalKey($obj)] = $obj;
    }
    
    static function exist($classname, $id)
    {
        $inst = self::$instance();
        $key = "{$classname}.{$id}";
        
        if (isset($inst->all[$key])) {
            return $inst->all[$key];
        }
        
        return null;
    }

}

