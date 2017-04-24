<?php

namespace woo\base;

abstract class Registry
{

    abstract protected function get($key);

    abstract protected function set($key, $val);
}

// класс на уровне запроса
class RequestRegistry extends woo\base\Registry
{
    private $values = array();
    private static $instance = null;
    
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
    
    protected function get($key)
    {
        if (isset($this->value($key))) {
            return $this->values[$key];
        };
        
        return null;
    }
    
    protected function set($key, $val)
    {
        $this->values[$key] = $val;
    }
    
    static function getRequest()
    {
        $inst = self::$instance();
        if (is_null($inst->get("request"))) {
            $inst->set('request', new \woo\controller\Request());
        }
        
        return $inst->get("request");
    }
}

// класс на уровне сеанса
class SessionRegistry extends woo\base\Registry
{
    private static $instance = null;
    
    private function __construct()
    {
        // нельзя отпровлять никакой текст в браузер до вызова новой сессии
        session_start();
    }
    
    static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        
        return self::$instance;
    }
    
    protected function get($key)
    {
        if (isset($_SESSION[__CLASS__][$key])) {
            return $_SESSION[__CLASS__][$key];
        };
        
        return null;
    }
    
    protected function set($key, $val)
    {
        $_SESSION[__CLASS__][$key] = $val;
    }
    
    public function setDSN($dsn)
    {
        return self::$instance()->set("dsn", $dsn);
    }
    
    public function getDSN()
    {
        return self::$instance->get("dsn");
    }
}

// класс на уровне приложения
class ApplicationRegistry extends woo\base\Registry
{
    private static $instance = null;
    private $freezedir = "data";
    private $values = array();
    // массив с информацией о времени изменения файлов сохранения
    private $mtime = array();


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
    
    protected function get($key)
    {
        $path = $this->freezedir . DIRECTORY_SEPARATOR . $key;
        if (file_exists($path)) {
            clearstatcache();
            $mtime = filemtime($path);
            
            if (!isset($this->mtime[$key])) {
                $this->mtime[$key] = 0;
            }
            
            // проверяет, был ли файл изменен с момента последнего сохранения
            if ($mtime > $this->mtime[$key]) {
                $data = file_get_contents($path);
                $this->mtime[$key] = $mtime;
                return ($this->values[$key] = unserialize($data));
            }
        }
        
        if (isset($this->values[$key])) {
            return $this->values[$key];
        }
        
        return null;
    }
    
    protected function set($key, $val)
    {
        $this->values[$key] = $val;
        $path = $this->freezedir . DIRECTORY_SEPARATOR . $key;
        file_put_contents($path, serialize($val));
        $this->mtime[$key] = time();
    }
    
    static function setDSN($dsn)
    {
        return self::$instance()->set("dsn", $dsn);
    }
    
    static function getDSN()
    {
        return self::$instance->get("dsn");
    }
    
    // только один эксземпляр объекта Request будет доступен всем элементам приложения
    static function getRequest()
    {
        $inst = self::$instance();
        // блокировка, чтобы существовал только один эксземпляр объекта Reques
        if (is_null($inst->request)) {
            $inst->request = new \woo\controller\Request();
        }
        
        return $inst->request;
    }
}