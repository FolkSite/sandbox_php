<?php

namespace woo\domain;

abstract class DomainObject
{

    private $id;

    public function __construct($id = null)
    {
        if (is_null($id)) {
            $this->markNew();
        } else {
            $this->id = $id;
        }
    }

    public static function getCollection($type)
    {
        return array(); // Заглушка
    }

    public function collection()
    {
        return self::getCollection(get_class($this));
    }

    // Вспомогательные методы для шаблона Unit of Work, который призван уменьшить 
    // количество обращений к БД

    public function markNew()
    {
        ObjectWatcher::addNew($this);
    }

    public function markDeleted()
    {
        ObjectWatcher::addDelete($this);
    }

    public function markDirty()
    {
        ObjectWatcher::addDirty($this);
    }

    public function markClean()
    {
        ObjectWatcher::addClean($this);
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function finder()
    {
        return self::getFinder(get_class($this));
    }

    static function getFinder($type = null)
    {

        if (is_null($type)) {
            return HelperFactory::getFinder(get_called_class());
        }

        return HelperFactory::getFinder($type);
    }

}

class Venue extends DomainObject
{

    private $name;
    private $spaces;

    public function __construct($id = null, $name = null)
    {
        $this->name = $name;
        $this->spaces = self::getCollection("\\woo\\domain\\Space");
        parent::__construct($id);
    }

    public function setSpaces(SpaceCollection $space)
    {
        $this->spaces = $space;
    }

    public function getSpaces()
    {
        return $this->spaces;
    }

    public function addSpace(Space $space)
    {
        $this->spaces->add($space);
        $space->setVenue($this);
    }

    public function setName($name_s)
    {
        $this->name = $name_s;
        $this->markDirty();
    }

    public function getName()
    {
        return $this->name;
    }

}

class Space extends DomainObject 
{
    private $events;
    
    public function getEvents()
    {
        if (is_null($this->events)) {
            $this->events = self::getFinder(Event::class)->findBySpaceId($this->getId());
        }
        
        return $this->events;
    }
}