<?php

namespace woo\mapper;

//...

abstract class DomainObjectFactory 
{
    abstract function createObject(array $array);
}

namespace woo\mapper;

//...

class VenueObjectFactory extends DomainObjectFactory
{
    public function createObject(array $array)
    {
        $obj = new \woo\domain\Venue($array['id']);
        $obj->setName($array['name']);
        return $obj;
    }
}

