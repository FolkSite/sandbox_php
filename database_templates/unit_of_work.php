<?php

namespace woo\domain;

//...

/*
 * Задача объекта - следить за всеми объектами системы, чтобы один объект не превратился в два
 */
class ObjectWatcher
{

    private $all = array();
    private $dirty = array();
    private $new = array();
    private $delete = array();
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
    
    static function addDelete(DomainObject $obj)
    {
        $self = self::instance();
        $self->delete[$self->globalKey($obj)] = $obj;
    }
    
    static function addDirty(DomainObject $obj)
    {
        $inst = self::instance();
        
        if (!in_array($obj, $inst->new, true)) {
            $inst->dirty[$inst->globalKey($obj)] = $obj;
        }
    }
    
    static function addNew(DomainObject $obj)
    {
        $inst = self::instance();
        // У нас еще нет идентификатора id
        $inst->new[] = $obj;
    }
    
    static function addClean(DomainObject $obj)
    {
        $self = self::instance();
        unset($self->delete[$self->globalKey($obj)]);
        unset($self->dirty[$self->globalKey($obj)]);
    }
    
    public function performOperations()
    {
        
        foreach ($this->dirty as $key=>$obj) {
            $obj->finder()->update($obj);
        }
        
        foreach ($this->new as $key=>$obj) {
            $obj->finder()->insert($obj);
        }
        
        $this->dirty = array();
        $this->new = array();
    }

}

