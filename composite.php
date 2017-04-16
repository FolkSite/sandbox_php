<?php

class composite
{
    
}

abstract class Unit
{

    public function getComposite()
    {
        return null;
    }

    abstract function bombardStrength();
}

abstract class CompositeUnit extends Unit
{

    protected $units = array();

    public function getComposite()
    {
        return $this;
    }

    public function units()
    {
        return $this->units;
    }

    function removeUnit(Unit $unit)
    {
        $this->units = array_udiff($this->units, array($unit), function ($a, $b) {
            return ($a === $b) ? 0 : 1;
        });
    }

    function addUnit(Unit $unit)
    {
        if (in_array($unit, $this->units, true)) {
            return;
        };
        $this->units[] = $unit;
    }

}

class Army extends CompositeUnit
{

    function bombardStrength()
    {
        $ret = 0;
        foreach ($this->units as $unit) {
            $ret += $unit->bombardStrength();
        }
        return $ret;
    }

}

class UnitException extends Exception
{
    
}

class Archer extends Unit
{

    function bombardStrength()
    {
        return 4;
    }

}

class LaserCannonUnit extends Unit
{

    function bombardStrength()
    {
        return 8;
    }

}

class Cavalry extends Unit
{

    function bombardStrength()
    {
        return 6;
    }

}

class UnitScript
{

    static function joinExisting(Unit $newUnit, Unit $occupyingUnit)
    {
        if (!is_null($comp = $occupyingUnit->getComposite())) {
            $comp->addUnit($newUnit);
        } else {
            $comp = new Army();
            $comp->addUnit($occupyingUnit);
            $comp->addUnit($newUnit);
        }
        
        return $comp;
    }

}

class TroopCarrier extends CompositeUnit{

    function addUnit(Unit $unit)
    {
        if ($unit instanceof Cavalry) {
            throw new UnitException("Нельзя помещать лошадь на бронетранспортер");
        }
        
        parent::addUnit($unit);
    }
    
    function bombardStrength()
    {
        return 0;
    }

}

// Создадим армию
$main_army = new Army();

// Добавим боевые единицы
$main_army->addUnit(new Archer());
$main_army->addUnit(new LaserCannonUnit());

// Еще одна армия
$sub_army = new Army();

// Добавим боевые единицы
$sub_army->addUnit(new Archer());
$sub_army->addUnit(new Archer());
$sub_army->addUnit(new Archer());

// Добавим вторую армию к первой
$main_army->addUnit($sub_army);

// Вычисление выполняются за кулисами
var_dump("Атакующая сила: " . $main_army->bombardStrength());

UnitScript::joinExisting(new Archer(), $main_army);

var_dump("Атакующая сила: " . $main_army->bombardStrength());

// добавляю юнит в перевозчик войск
$troopCarrier = new TroopCarrier();
$troopCarrier->addUnit(new Archer);
var_dump("Атакующая сила: " . $troopCarrier->bombardStrength());
// получает список юнитов в перевозчике
$troopUnits = $troopCarrier->units();
var_dump($troopUnits[0]->bombardStrength());

/*
$archer = new Archer;
var_dump($archer->bombardStrength());
$archer->addUnit(new Archer);
 * 
 */