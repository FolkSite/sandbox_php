<?php

namespace woo\mapper;

/*
 * Выполняет задачи кеширования и управляет подключением к БД. 
 * Сборщик доменных объектов, контроллер данных.
 */

class DomainObjectAssembler
{

    protected static $PDO;

    public function __construct(PersistenceFactory $factory)
    {
        $this->factory = $factory;

        if (!isset(self::$PDO)) {
            $dsn = \woo\base\ApplicationRegistry::getDSN();

            if (is_null($dsn)) {
                throw new \woo\base\AppException("DSN не определен");
            }

            self::$PDO = new \PDO($dsn);
            self::$PDO->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        }
    }

    public function getStatement($str)
    {
        if (!isset($this->statements[$str])) {
            $this->statements[$str] = self::$PDO->prepare($str);
        }

        return $this->statements[$str];
    }

    public function findOne(IdentityObject $idobj)
    {
        $collection = $this->find($idobj);
        return $collection->next();
    }

    public function find(IdentityObject $idobj)
    {
        $selfact = $this->factory->getSelectionFactory();
        list($selection, $value) = $selfact->newSelection($idobj);
        $stmt = $this->getStatement($selection);
        $stmt->execute($value);
        $raw = $stmt->fetchAll();
        return $this->factory->getCollection($raw);
    }

    public function insert(\woo\domain\DomainObject $obj)
    {
        $upfact = $this->factory->getUpdateFactory;
        list($update, $values) = $upfact->newUpdate($obj);
        $stmt = $this->getStatement($update);
        $stmt->execute($values);
        
        if ($obj->getId() < 0) {
            $obj->setId(self::$PDO->lastInsertId());
        }
        
        $obj->markClean();
    }

}

// Клиент
$factory = \woo\mapper\PersistenceFactory::getFactory("woo\\domain\\Venue");
$finder = new \woo\mapper\DomainObjectAssembler($factory);

// Либо можно реализовать следующий метод
$finder = \woo\mapper\PersistenceFactory::getFinder("woo\\domain\\Venue");

$idobj = $factory->getIdentityObject()->field("name")->eq("The Eyeball Inn");
$collection = $finder->find($idobj);

foreach ($collection as $venue) {
    (var_dump($venue->getName()));
}