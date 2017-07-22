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

    function handleRequest()
    {
        $request = \woo\base\ApplicationRegistry::getRequest();
        $app_c = \woo\base\ApplicationRegistry::appController();

        while ($cmd = $app_c->getCommand($request)) {
            $cmd->execute($request);
        }

        $this->invokeView($app_c->getView($request));
    }

    function invokeView($target)
    {
        include 'woo/view/$target.php';
        exit;
    }

}

namespace woo\command;

abstract class Command
{

    private static $STATUS_STRING = array(
        'CMD_DEFAULT' => 0,
        'CMD_OK' => 1,
        'CMD_ERROR' => 2,
        'CMD_INSUFFICIENT_DATA' => 3
    );

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

namespace woo\controller;

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

namespace woo\controller;

// ...

class ControllerMap
{

    private $viewMap = array();
    private $forwardMap = array();
    private $classrootMap = array();

    public function addClassroot($command, $classroot)
    {
        $this->classrootMap[$command] = $classroot;
    }

    public function getClassroot($command)
    {
        if (isset($this->classrootMap[$command])) {
            return $this->classrootMap[$command];
        }
        return null;
    }

    public function addView($view, $command = 'default', $status = 0)
    {
        $this->viewMap[$command][$status] = $view;
    }

    public function getView($command)
    {
        if (isset($this->viewMap[$command][$status])) {
            return $this->viewMap[$command][$status];
        }
        return null;
    }

    public function addForward($command, $status = 0, $newCommand)
    {
        $this->forwardMap[$command][$status] = $newCommand;
    }

    public function getForward($command)
    {
        if (isset($this->forwardMap[$command][$status])) {
            return $this->forwardMap[$command][$status];
        }
        return null;
    }

}

namespace woo\controller;

// ...

class AppController
{

    private static $base_cmd = null;
    private static $default_cmd = null;
    private $controllerMap;
    private $invoked = array();

    public function __construct(ControllerMap $map)
    {
        $this->controllerMap = $map;

        if (is_null(self::$base_cmd)) {
            // возвращает имя класса
            self::$base_cmd = new \ReflectionClass("\woo\command\Command");
            self::$default_cmd = new \woo\command\DefaultCommand();
        }
    }

    public function reset()
    {
        $this->invoked = array();
    }

    public function getView(Request $reg)
    {
        $view = $this->getResource($reg, "View");
        return $view;
    }

    private function getForward(Request $reg)
    {
        $forward = $this->getResource($reg, "Forward");

        if ($forward) {
            $reg->setProperty('cmd', $val);
        }

        return $forward;
    }

    private function getResource(Request $reg, $res)
    {
        // определим предыдущую команду и её код состояния
        $cmd_str = $reg->getProperty('cmd');
        $previous = $reg->getLastCommand();
        $status = $previous->getStatus();

        if (!isset($status) || !is_int($status)) {
            $status = 0;
        }

        $acquire = "get$res";
        $resource = $this->controllerMap->$acquire($cmd_str, $status);

        // определим альтернативный ресурс для команды и кода состояния 0
        if (is_null($resource)) {
            $resource = $this->controllerMap->$acquire($cmd_str, 0);
        }

        // либо для команды 'default' и текущего кода состояния
        if (is_null($resource)) {
            $resource = $this->controllerMap->$acquire('default', $status);
        }

        // если ничего не найдено, определим ресурс для команды 'default',
        // и кода состояния 0
        if (is_null($resource)) {
            $resource = $this->controllerMap->$acquire('default', 0);
        }

        return $resource;
    }

    public function getCommand(Request $reg)
    {
        $previous = $reg->getLastCommand();

        if (!$previous) {
            // это первая команда текущего запроса
            $cmd = $reg->getProperty('cmd');

            if (is_null($cmd)) {
                // параметр 'cmd' не определен, используем 'default'
                $reg->setProperty('cmd', 'default');
                return self::$default_cmd;
            }
        } else {
            // команда уже запущена в текущем запросе
            $cmd = $this->getForward($reg);
            if (is_null($cmd)) {
                return null;
            }
        }

        // здесь в переменной $cmd находится имя команды
        // преобразуем его в объект типа Command
        $cmd_obj = $this->resolveCommand($cmd);

        if (is_null($cmd_obj)) {
            throw new \woo\base\AppException("Команда '$cmd' не найдена");
        }
        // возвращает имя класса, к которому принадлежит объект
        $cmd_class = get_class($cmd_obj);

        if (isset($this->invoked[$cmd_class])) {
            throw new \woo\base\AppException("Циклический вызов");
        }

        $this->invoked[$cmd_class] = 1;
        // возвращаем объект типа Command
        return $cmd_obj;
    }

    public function resolveCommand($cmd)
    {
        $classroot = $this->controllerMap->getClassroot($cmd);
        $filepath = "woo/command/$classroot.php";
        $classname = "\woo\command\$classroot";

        if (file_exists($filepath)) {
            require_once "$filepath";

            if (class_exists($classname)) {
                $cmd_class = new ReflectionClass($classname);

                if ($cmd_class->isSubClassOf(self::$base_cmd)) {
                    return $cmd_class->newInstance();
                }
            }
        }

        return null;
    }

}

namespace woo\command;

// ...

class AddVenue extends Command
{

    public function doExecute(\woo\controller\Requesr $request)
    {
        $name = $request->getProperty("venue_name");

        if (is_null($name)) {
            $request->addFeedback("Имя не задано");
            return self::statuses('CMD_INSUFFICIENT_DATA');
        } else {
            $venue_obj = new \woo\domain\Venue(null, $name);
            $request->setObject('venue', $venue_obj);
            $request->addFeedback("'$name' добавлено в ({$venue_obj->getId()})");
            return self::statuses('CMD_OK');
        }
    }

}

namespace woo\domain;

class Venue
{
    private $id;
    private $name;
    
    public function __construct($id, $name)
    {
        $this->name = $name;
        $this->id = $id;
    }
    
    public function getName()
    {
        return $this->name;
    }
    
    public function getId()
    {
        return $this->id;
    }
}

