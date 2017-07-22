<?php

namespace woo\process;

// ...

abstract class Base
{

    static $DB;
    static $statements = array();

    public function __construct()
    {
        $dsn = \woo\base\ApplicationRegistry::getDSN();

        if (is_null($dsn)) {
            throw new \woo\base\AppException("DSN не определен");
        }

        self::$DB = new \PDO($dsn);
        self::$DB->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    public function prepareStatement($statement)
    {
        if (isset(self::$statements[$statement])) {
            return self::$statements[$statement];
        }

        $stmt_handle = self::$DB->prepare($statement);
        self::$statements[$statement] = $stmt_handle;
        return $stmt_handle;
    }

    public function doStatement($statement, array $value)
    {
        $sth = $this->prepareStatement($statement);
        /* следующий вызов closeCursor() может быть обязательным для некоторых драйверов.
         * Прочитал мануал, но всё равно не понял зачем. */
        $sth->closeCursor();
        $db_result = $stm->execute($value);
        return $sth;
    }

}

// ...
class VenueManager extends Base
{

    static $add_venue = "INSERT INTO venue "
            . "(name) "
            . "values(?)";
    
    static $add_space = "INSERT INTO space "
            . "(name, venue) "
            . "values(?, ?)";
    
    static $check_slot = "SELECT id, name "
            . "FROM event "
            . "WHERE space = ? "
            . "AND (start+duration) > ? "
            . "AND start < ?";
    
    static $add_event = "INSERT INTO event "
            . "(name, space, start, suration) "
            . "values(?, ?, ?, ?)";
    
    // ...
    
    public function addVenue($name, $space_array)
    {
        $venuedata = array();
        $venuedata['venue'] = array($name);
        $this->doStatement(self::$add_venue, $venuedata['venue']);
        $v_id = self::$DB->lastInsertId();
        
        $venuedata['spaces'] = array();
        
        foreach ($space_array as $space_name) {
            $values = array($space_name, $v_id);
            $this->doStatement(self::$add_space, $values);
            $s_id = self::$DB->lastInsertId();
            // добавляет $s_id в начало массива $values
            array_unshift($values, $s_id);
            $venuedata['spaces'][] = $values;
        }
        
        return $venuedata;
    }
    
    /*
     * Добавляет новое событие или возвращает ошибку если время проведение в
     * конкретном месте совпадает с временем проведения уже добавленного ранее события
     */
    public function bookEvent($space_id, $name, $time, $duration)
    {
        $values = array($space_id, $time, ($time+$duration));
        $stmt = $this->doStatement(self::$check_slot, $values, false);
        
        if ($result = $stmt->fetch()) {
            throw new \woo\base\AppException("Уже зарегистрировано! Попробуйте еще раз");
        }
        $this->doStatement(self::$add_event, array($name, $space_id, $time, $duration));
    }
}

