<?php

namespace woo\mapper;

abstract class UpdateFactory
{
    abstract function newUpdate(\woo\domain\DomainObject $obj);

    protected function buildStatement($table, array $fields, array $conditions = null)
    {
        $items = array();
        
        if (!is_null($conditions)) {
            $query = "UPDATE {$table} SET ";
            $query .= implode(" = ?,", array_keys($fields)) . " = ?";
            $terms = array_values($fields);
            $cond = array();
            $query .= " WHERE ";
            
            foreach ($conditions as $key => $value) {
                $cond[] = "$key = ?";
                $terms[] = $value;
            }
            
            $query .= implode(" AND ", $cond);
        } else {
            $query = "INSERT INTO {$table} (";
            $query .= implode(",", array_keys($fields));
            $query .= ") VALUES (";
            
            foreach ($fields as $name => $value) {
                $terms[] = $value;
                $qs[] = "?";
            }
            
            $query .= implode(",", $qs);
            $query .= ")";
        }
        
        return array($query, $terms);
    }

}
class VenueUpdateFactory extends UpdateFactory {
     public function newUpdate(\woo\domain\DomainObject $obj)
     {
         // Проверка тиво удалена
         $id = $obj->getId();
         $cond = null;
         $values['name'] = $obj->getName();
         
         if ($id > -1) {
             $cond['id'] = $id;
         }
         
         return $this->buildStatement("venue", $values, $cond);
     }
}

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
            $values[] = $comp['values'];
        }
        
        $where = "WHERE " . implode(" ANS ", $compstrings);
        return array($where, $values);
    }
}

var_dump(file_exists(__DIR__ . '/../corp_sys_templates/DomainModel.php'));

require_once __DIR__ . '/../corp_sys_templates/DomainModel.php';

$vuf = new VenueUpdateFactory();
var_dump($vuf->newUpdate(new \woo\domain\Venue(334, "TheHappy Haorband")));