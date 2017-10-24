<?php

namespace woo\mapper;

/*
 * Использование генератора вместо итератора для сокращения кода.
 */

abstract class Collection
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

    public function getGenerator()
    {
        for ($x = 0; $x < $this->total; $x++) {
            yield ($this->getRow($x));
        }
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

    // Код ниже не требуется при использовании генератора вместо интерфейса итератора.
    /*
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
     * 
     */
}

// Пример использования в клиентском коде.

$get = $collection->getGenerator();

foreach ($gen as $wrapper) {
    print_r($wrapper);
}