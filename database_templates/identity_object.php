<?php

namespace woo\mapper;

//...

class IdentityObject
{
    private $name = null;
    
    public function setName($name)
    {
        $this->name = $name;
    }
    
    function getName()
    {
        return $this->name;
    }
}

class EventIdentityObject extends \woo\mapper\IdentityObject
{
    private $start = null;
    private $minstart = null;
    
    public function setMinimumStart($minstart)
    {
        $this->minstart = $minstart;
    }
    
    public function getMinimumStart()
    {
        return $this->minstart;
    }
    
    public function setStart($start)
    {
        $this->start = $start;
    }
    
    public function getStart()
    {
        return $this->start;
    }
}

$idobj = new EventIdentityObject();
$idobj->setMinimumStart(time());
$idobj->setName("A FineShow");

$comps = array();
$name = $idobj->getName();

if (!is_null($name)) {
    $comps[] = "name = '{$name}'";
}

$minstart = $idobj->getMinimumStart();

if (!is_null($minstart)) {
    $comps[] = "minstart > '{$minstart}'";
}

$start = $idobj->getStart();

if (!is_null($start)) {
    $comps[] = "start > '{$start}'";
}

$clause = " WHERE " . implode(" and ", $comps);

var_dump($clause);