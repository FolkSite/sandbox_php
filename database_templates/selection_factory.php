<?php

namespace woo\mapper;

abstract class SelectionFactory
{
    abstract function newSelection(IdentityObject $obj);
    
    public function buildWhere(IdentityObject $obj)
    {
        if ($obj->isVoid()) {
            return array("", array());
        }
        
        $compstrings = array();
        $values = array();
        
        foreach ($obj->getComps() as $comp) {
            $compstrings[] = "{$comp['name']} {$comp['operator']} ?";
            $values[] = $comp['value'];
        }
        
        $where = "WHERE " . implode(" ANS ", $compstrings);
        return array($where, $values);
    }
}

class VenueSelectionFactory extends SelectionFactory
{
    public function newSelection(IdentityObject $obj)
    {
        $fields = implode(',', $obj->getObjectFields());
        $core = "SELECT $fields FROM venue";
        
        var_dump($this->buildWhere($obj));
        
        list($where, $values) = $this->buildWhere($obj);
        
        var_dump($where);
        var_dump($values);
        
        return array($core." ".$where, $values);
    }
}

var_dump(file_exists(__DIR__ . '/../corp_sys_templates/DomainModel.php'));

require_once __DIR__ . '/fluent_interface.php';

$vio = new VenueIdentityObject();
$vio->field("name")->eq("The Happy Hairband");

$vsf = new VenueSelectionFactory();
var_dump($vsf->newSelection($vio));