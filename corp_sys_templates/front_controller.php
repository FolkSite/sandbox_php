<?php

namespace woo\controller;

class Controller
{

    private $applicationHelper;

    // закрытый конструктор
    private function __construct()
    {
        
    }

    static function run()
    {
        $instance = new Controller();
        $instance->init();
        $instance->handleRequest();
    }

    public function init()
    {
        // в этом классе хранятся данные конфигурации для всего приложения
        $this->applicationHelper = ApplicationHelper::instance();
        $this->applicationHelper->init();
    }

    public function handleRequest()
    {
        $request = \woo\base\ApplicationRegistry::getRequest();
        $cmd_r = new woo\command\CommandResolve();
        $cmd = $cmd_r0 > getCommand($request);
        $cmd->execute($request);
    }

}

// в этом классе хранятся данные конфигурации для всего приложения
// необязательный для шаблона фронт контроллер
// провел рефракторинг, чтобы не создавать два синглтона, убрал зависимость 
// от ApplicationRegistry сделал класс системным реестром
// кажется, так себе реализация, можно было вместо копирования кода сделать наследование
// но я не понял, будет ли по задумке автора в приложении вообще использоваться 
// файл registry.php
class ApplicationHelper extends \woo\base\Registry
{

    private static $instance = null;
    private $config = "data/woo_options.xml";
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

    public function init()
    {
        $dsn = self::getDSN();

        // если настройки уже получены, то $this->getOptions() не будет вызываться повторно
        if (!is_null($dsn)) {
            return;
        }

        $this->getOptions();
    }

    private function getOptions()
    {
        $this->ensure(file_exists($this->config), "Файл конфигурации не найден");
        // возвращает объект
        $options = @SimpleXml_load_file($this->config);
        // приведение типа ответа к строке
        $dsn = (string) $options->dsn;
        $this->ensure($options instanceof \SimpleXMLElement, "Файл конфигурации испорчен");
        $this->ensure($dsn, "DSN не найден");
        self::setDSN($dsn);
        // Установите другие значения
    }

    // централизованная проверка условия и вызов исключения
    private function ensure($expr, $message)
    {
        if (!$expr) {
            throw new \woo\base\AppException($message);
        }
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

namespace woo\command;

// решает, как интерпретировать HTTP запрос
class CommandResolve
{

    private static $base_cmd = null;
    private static $default_cmd = null;

    public function __construct()
    {
        if (is_null(self::$base_cmd)) {
            // ReflectionClass возвращает информацию о классе, 
            // вернет объект из которого можно получить имя класса $base_cmd->name
            self::$base_cmd = new ReflectionClass("\woo\command\Command");
            self::$default_cmd = new DefaultCommand();
        };
    }

    public function getCommand(\woo\controller\Request $request)
    {
        $cmd = $request->getProperty('cmd');
        $sep = DIRECTORY_SEPARATOR;

        if (!$cmd) {
            return self::$default_cmd;
        }

        // удаляет элементы пути. Защита, чтобы нельзя было получит доступ к файлам
        // из других директорий
        $cmd = str_replace(array('.', $sep), "", $cmd);
        $filepath = "woo{$sep}command{$sep}{$cmd}.php";
        $classname = "woo\\command\\$cmd";

        if (file_exists($filepath)) {
            @require_once($filepath);

            if (class_exists($class_name)) {
                $cmd_class = new ReflectionClass($classname);

                // isSubClassOf проверяет является ли класс подклассом
                if ($cmd_class->isSubClassOf(self::$base_cmd)) {
                    // newInstance (метод объекта ReflectionClass) создает экземпляр класса $classname
                    return $cmd_class->newInstance();
                } else {
                    $request->addFeedback("Объект Command команды '$cmd' не найден");
                }
            }
        }

        $request->addFeedback("Команда '$cmd' не найден");
        return clone self::$default_cmd;
    }

}

namespace woo\command;

abstract class Command
{

    // объявляя метод конструктора как final, мы не даем дочерним класс его переопределять
    final function __construct()
    {
        
    }

    function exexute(\woo\controller\Requesr $request)
    {
        $this->doExecute($request);
    }

    abstract function doExecute(\woo\controller\Requesr $request);
}

// автоматический выбирается классом CommandResolver, если нет явного запроса
class DefaultCommand extends woo\command\Command
{
    public function doExecute(\woo\controller\Requesr $request)
    {
        $request->addFeedback("Добро пожаловать в Woo!");
        include ('woo/view/main.php');
    }
}

namespace woo\controller;

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
