<?php

abstract class Unit
{
    
    protected $depth = 0;

    public function getComposite()
    {
        return null;
    }

    abstract function bombardStrength();
    
    public function accept(ArmyVisitor $visitor)
    {
        $method = "visit" . get_class($this);
        $visitor->$method($this);
    }
    
    protected function setDepth($depth)
    {
        $this->depth = $depth;
    }
    
    public function getDepth()
    {
        return $this->depth;
    }
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

    public function removeUnit(Unit $unit)
    {
        $this->units = array_udiff($this->units, array($unit), function ($a, $b) {
            return ($a === $b) ? 0 : 1;
        });
    }

    public function addUnit(Unit $unit)
    {
        foreach ($this->units as $thisunit) {
            if ($unit === $thisunit) {
                return;
            }
        }
        $unit->setDepth($this->depth+1);
        $this->units[] = $unit;
    }
    
    public function accept(ArmyVisitor $visitor)
    {
        parent::accept($visitor);
        foreach ($this->units as $thisunit) {
            $thisunit->accept($visitor);
        }
    }

}

abstract class ArmyVisitor 
{
    abstract function visit(Unit $node);
    
    public function visitArcher(Archer $node) 
    {
        $this->visit($node);
    }
    
    public function visitCavalry(Cavalry $node) 
    {
        $this->visit($node);
    }
    
    public function visitLaserCannonUnit(LaserCannonUnit $node) 
    {
        $this->visit($node);
    }
    
    public function visitTroopCarrier(TroopCarrier $node) 
    {
        $this->visit($node);
    }
    
    public function visitArmy(Army $node) 
    {
        $this->visit($node);
    }
}

class TextDumpVisitor extends ArmyVisitor
{
    private $text = "";
    
    public function visit(\Unit $node)
    {
        $txt = "<pre>";
        $pad = 4 * $node->getDepth();
        // устанавливает количество пробелов перед строкой (глубина)
        $txt .= sprintf("%{$pad}s", "");
        $txt .= get_class($node) . ": ";
        $txt .= "Огненная мощь: " . $node->bombardStrength() . "<br>";
        $txt .= "</pre>";
        $this->text .=$txt;
    }
    
    public function getText()
    {
        return $this->text;
    }
}

class TaxCollectionVisitor extends ArmyVisitor
{
    private $due = 0;
    private $report = "";
    
    public function visit(Unit $node)
    {
        $this->levy($node, 1);
    }
    
    public function visitArcher(Archer $node)
    {
        $this->levy($node, 2);
    }
    
    public function visitCavalry(Cavalry $node)
    {
        $this->levy($node, 3);
    }
    
    public function visitTroopCarrier(TroopCarrier $node)
    {
        $this->levy($node, 5);
    }
    
    private function levy(Unit $unit, $amount)
    {
        $this->report .= "Налог для " . get_class($unit);
        $this->report .= ": $amount<br>";
        $this->due += $amount;
    }
    
    public function getReport()
    {
        return $this->report;
    }
    
    public function getTax() 
    {
        return $this->due;
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

$main_army = new Army;
$main_army->addUnit(new Archer);
$main_army->addUnit(new LaserCannonUnit);
$main_army->addUnit(new Cavalry);
$textdump = new TextDumpVisitor;
//$main_army->accept($textdump);
$taxcollector = new TaxCollectionVisitor;
$main_army->accept($taxcollector);
//echo $textdump->getText();
echo $taxcollector->getReport() . "<br>";
echo $taxcollector->getTax() . "<br>";

