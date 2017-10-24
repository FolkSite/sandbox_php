<?php

namespace woo\mapper;

//...
abstract class Collection implements \Iterator
{

    protected $mapper;
    protected $total = 0;
    protected $raw = array();
    private $result;
    private $pointer = 0;
    private $objects = array();

    public function __construct(array $raw = null, Mapper $mapper = null)
    {
        if (!is_null($raw) && !is_nan($mapper)) {
            $this->raw = $raw;
            $this->total = count($raw);
        }

        $this->mapper = $mapper;
    }

    public function add(\woo\domain\DomainObject $object)
    {
        $class = $this->targetClass();

        if (!($object instanceof $class)) {
            throw new Exception("Это коллекция {$class}");
        }

        $this->notifyAccess();
        $this->objects[$this->total] = $object;
        $this->total++;
    }

    abstract function targetClass();

    protected function notifyAccess()
    {
        // Специально оставлена пустой!
    }

    private function getRow($num)
    {
        $this->notifyAccess();

        if ($num >= $this->total || $num < 0) {
            return null;
        }

        if (isset($this->objects[$num])) {
            return $this->objects[$num];
        }

        if (isset($this->raw[$num])) {
            $this->objects[$num] = $this->mapper->createObject($this->raw[$num]);
            return $this->objects[$num];
        }
    }

    public function rewind()
    {
        $this->pointer = 0;
    }

    public function current()
    {
        return $this->getRow($this->pointer);
    }

    public function key()
    {
        return $this->pointer;
    }

    public function next()
    {
        $row = $this->getRow(($this->pointer));

        if ($row) {
            $this->pointer++;
        }

        return $row;
    }

    public function valid()
    {
        return (!is_null($this->current()));
    }

}

namespace woo\mapper;

//...

class VenueCollection extends Collection
{

    function targetClass()
    {
        return "\woo\domain\Venue";
    }

}

class EventCollection extends Collection
{

    public function targetClass()
    {
        return "\woo\domain\Event";
    }

}

class DefferedEventCollection extends EventCollection
{

    private $stmt;
    private $valueArray;
    private $run = false;

    public function __construct(Mapper $mapper, \PDOStatement $stmt_handle, array $valueArray)
    {
        parent::__construct(null, $mapper);
        $this->stmt = $stmt_handle;
        $this->valueArray = $valueArray;
    }
    
    public function notifyAccess()
    {
        if (!$this->run) {
            $this->stmt->execute($this->valueArray);
            $this->raw = $this->stmt->fetchAll();
            $this->total = count($this->raw);
        }
        
        $this->run = true;
    }

}
