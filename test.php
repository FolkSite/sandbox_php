<?php

class Sartre
{
    public function __construct($number)
    {
        $this->number = $number;
    }
    
    public function getNumber()
    {
        return $this->number;
    }
}

$className = 'Sartre';

$sartre = new $className(10);
var_dump($sartre->getNumber());