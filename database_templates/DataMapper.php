<?php

namespace woo\mapper;

//...

abstract class Mapper
{

    protected static $PDO;

    public function __construct()
    {
        if (!isset(self::$PDO)) {
            $dsn = \woo\base\ApplicationRegistry::getDSN();

            // DSN - Data Source Name - имя источника данных. 
            // Пример 'mysql:host=localhost;dbname=testdb'

            if (is_null($dsn)) {
                throw new \woo\base\AppEception("DSN не определен");
            }

            self::$PDO = new \PDO($dsn);
            self::$PDO->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        }
    }

    public function find($id)
    {
        // Проверка, чтобы один объект не стал двумя.
        $old = $this->getFromMap($id);

        if (!is_null($old)) {
            return $old;
        }

        $this->selectStmt()->execute(array($id));
        $array = $this->selectStmt()->fetch();
        $this->selectStmt()->closeCursor();

        if (!is_array($array)) {
            return null;
        }

        if (!isset($array['id'])) {
            return null;
        }

        $object = $this->createObject($array);

        return $object;
    }

    public function insert(\woo\domain\DomainObject $obj)
    {
        // Обрабатываем вставку. $obj нужно пометить новым идентификатором
        $this->addToMap($obj);
    }

    abstract function update(\woo\domain\DomainObject $object);

    protected abstract function doCreateObject(array $array);

    protected abstract function doInsert(\woo\domain\DomainObject $object);

    protected abstract function selectStmt();

    // Проверка, чтобы один объект не стал двумя.

    private function getFromMap($id)
    {
        return \woo\domain\ObjectWatcher::exist($this->targetClass(), $id);
    }

    private function addToMap(\woo\domain\ObjectWatcher $obj)
    {
        return \woo\domain\ObjectWatcher::add($obj);
    }
    
    protected function targetClass()
    {
        return \woo\domain\Space::class;
    }
    
    // Вспомогательные методы для шаблона Unit of Work, который призван уменьшить 
    // количество обращений к БД
    
    public function createObject($array)
    {
        // Проверка, чтобы один объект не стал двумя.
        $old = $this->getFromMap($array['id']);
        
        if (!is_null($old)) {
            return $old;
        }
        
        $obj = $this->doCreateObject($array);
        $this->addToMap($obj);
        $obj->markClean();
        return $obj;
    }

}

class VenueMapper extends Mapper
{

    public function __construct()
    {
        parent::__construct();

        $this->selectStmt = self::$PDO->prepare("SELECT * FROM venue WHERE id= ? ");

        $this->updateStmt = self::$PDO->prepare("UPDATE venue SET name = ?, id = ? WHERE id = ?");
        $this->insertStmt = self::$PDO->prepare("INSERT into venue (name) values(?)");
    }

    public function getCollection(array $raw)
    {
        return new SpaceCollection($raw, $this);
    }

    protected function doCreateObject(array $array)
    {
        $obj = new \woo\domain\Venue($array['id']);
        $obj->setName($array['name']);
        return $obj;
    }

    protected function doInsert(\woo\domain\DomainObject $object)
    {
        $values = array($object->getName());
        $this->insertStmt->execute($valuse);
        $id = self::$PDO->lastInsertId();
        $object->setID($id);
    }

    public function update(\woo\domain\DomainObject $object)
    {
        $values = array($object->getName(), $object->getId(), $object->getId());
        $this->updateStmt->execute($values);
    }

    public function selectStmt()
    {
        return $this->selectStmt;
    }

}

class SpaceMapper extends Mapper
{
    //...
    
    protected function doCreateObject(array $array)
    {
        $obj = new \woo\domain\Space($array['id']);
        $obj->setName($array['name']);
        $ven_mapper = new VenueMapper();
        $venue = $ven_mapper->find($array['venue']);
        $obj->setVenue($venue);
        $event_mapper = new EventMapper();
        $event_collection = $event_mapper->findBySpaceId($array['id']);
        $obj->setEvents($event_collection);
        return $obj;
    }

    protected function doInsert(\woo\domain\DomainObject $object)
    {
        
    }

    protected function selectStmt()
    {
        
    }

    public function update(\woo\domain\DomainObject $object)
    {
        
    }

}

class EventMapper extends Mapper
{
    public function findBySpace($s_id)
    {
        return new DefferedEventCollection($this, $this->selectBySpaceStmt(), array($s_id));
    }

    protected function doCreateObject(array $array)
    {
        
    }

    protected function doInsert(\woo\domain\DomainObject $object)
    {
        
    }

    protected function selectStmt()
    {
        
    }

    public function update(\woo\domain\DomainObject $object)
    {
        
    }

}

// На клиенте

$venue = new \woo\domain\Venue();
$venue->setName("The Likey Lounge-yy");

// Добавим объект в БД
$mapper = new VenueMapper();
$mapper->insert($venue);

// Найдём объект для проверки
$venue = $mapper->find($venue->getId());
print_r($venue);

// Изменим объект
$venue->setName("The Bibble Beer Likey Lounge-yy");

// Операция обновления измененных данных
$mapper->update($venue);

// Проверка
$venue = $mapper->find($venue->getId());
print_r($venue);
