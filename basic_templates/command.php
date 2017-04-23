<?php

abstract class Command
{

    abstract function execute(CommandContext $context);
}

class LoginCommand extends Command
{

    function execute(\CommandContext $context)
    {
        $manager = Registry::getAccessManager();
        $user = $context->get('username');
        $pass = $context->get('pass');
        $user_obj = $manager->login($user, $pass);

        if (is_null($user_obj)) {
            $context->setError($manager->getError());
            return false;
        }

        $context->addParam('user', $user_obj);
        return true;
    }

}

/*
 * Объект-оболоска для ассоциативного массива переменных
 */

class CommandContext
{

    private $params = array();
    private $error = "";

    public function __construct()
    {
        $this->params = $_REQUEST;
    }

    public function addParam($key, $val)
    {
        $this->params[$key] = $val;
    }

    public function get($key)
    {
        if (isset($this->params[$key])) {
            return $this->params[$key];
        }
        return null;
    }

    public function setError($error)
    {
        $this->error = $error;
    }

    public function getError()
    {
        return $this->error;
    }

}

/*
 * пример клиента
 */

class CommandNotFound extends Exception
{
    
}

class CommandFactory
{

    private static $dir = 'commands';

    static function getCommand($action = 'Default')
    {
        if (preg_match('/\W/', $action)) {
            throw new Exception("Недопустимые символы в команде");
        }
        
        // преобразует первый символ строки в верхний регистр
        $class = UCFirst(strtolower($action)) . "Command";
        $file = self::$dir . DIRECTORY_SEPARATOR . "{$class}.php";
        
        if (!file_exists($file)) {
            throw new CommandNotFound("Файл '$file' не найден");
        }
        
        require_once($file);
        
        if (!class_exists($class)) {
            throw new CommandNotFound("Класс '$class' не найден");
        }
        
        $cmd = new $class();
        return $cmd;
    }

}

/*
 * вызывающий объект
 */
class Controller
{
    private $context;
    
    public function __construct()
    {
        $this->context = new CommandContext();
    }
    
    public function getContext()
    {
        return $this->context;
    }
    
    public function  process()
    {
        $action = $this->context->get('action');
        $action = (is_null($action)) ? "default" : $action;
        $cmd = CommandFactory::getCommand($action);
        
        if (!$cmd->execute($this->context)) {
            echo 'Обработка ошибки.'; 
        } else {
            echo 'Все прошло успешно. Отображает результат.'; 
        }
    }
}

/*
$controller = new Controller();
// Эмулирует запрос пользователя
$context = $controller->getContext();
$context->addParam('action', 'login');
$context->addParam('username', 'bob');
$context->addParam('pass', 'tiddles');
$controller->process();
*/

$controller = new Controller();
// Эмулирует запрос пользователя
$context = $controller->getContext();
$context->addParam('action', 'feedback');
$context->addParam('username', 'bob');
$context->addParam('pass', 'tiddles');
$controller->process();