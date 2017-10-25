<?php

namespace woo\mapper;

class Field
{

    protected $name = null;
    protected $operator = null;
    protected $comps = array();
    protected $incomplete = false;

    // Устанавливает имя поля, например age
    public function __construct($name)
    {
        $this->name = $name;
    }

    // Добавляет оператор и значение для проверки
    // (> 40, например) и помещает его в свойство $comps
    public function addTest($operator, $value)
    {
        $this->comps[] = array(
            'name' => $this->name,
            'operator' => $operator,
            'value' => $value
        );
    }

    // $comps - это массив, поэтому мы можем сравнить одно поле с другим
    // несколькими способами
    public function getComps()
    {
        return $this->comps;
    }

    // Если массив $comps не содержит элементов, значит, у нас есть данные
    // для сравнения и это поле не готово для использования в запросе
    function isIncomplete()
    {
        return empty($this->comps);
    }

}

class IdentityObject
{
    protected $currentfield = null;
    protected $fields = array();
    private $and = null;
    private $enforce = array();
    
    // Конструктор identity object может запускаться без параметров 
    // или с именем поля
    public function __construct($field = null, array $enforce = null)
    {
        if (!is_null($enforce)) {
            $this->enforce = $enforce;
        }
        
        if (!is_null($field)) {
            $this->field($field);
        }
    }
    
    // Имена полей, на которые наложено это ограничение
    public function getObjectFields()
    {
        return $this->enforce;
    }
    
    // Вводим новое поле.
    // Генерируется ошибка, если текущее поле неполное
    // (т.е. age, а не age > 40)
    // Этот метод возващает ссылку на текущий объект
    // и тем самым разрешает свободный синтаксис 
    public function field($fiefname)
    {
        if (!$this->isVoid() && $this->currentfield->isIncomplete()) {
            throw new \Exception("Неполное поле");
        }
        
        $this->enforceField($fiefname);
        
        if (isset($this->fields[$fiefname])) {
            $this->currentfield = $this->fields[$fiefname];
        } else {
            $this->currentfield = new Field($fiefname);
            $this->fields[$fiefname] = $this->currentfield;
        }
        
        return $this;
    }
    
    // Есть ли уже поля у identity object
    public function isVoid()
    {
        return empty($this->fields);
    }
    
    // Заданное имя поля допустимо?
    public function enforceField($fieldname)
    {
        if (!in_array($fieldname, $this->enforce) && !empty($this->enforce)) {
            $forcelist = implode(', ', $this->enforce);
            throw new \Exception("{$fieldname} не является корректным полем {$forcelist}");
        }
    }
    
    // Добавим оператор равенства к текущему полю
    // т.е. 'age' становится age = 40.
    // Возвращает ссылку на текующий объект (с помощью operator())
    public function eq($value)
    {
        return $this->operator("=", $value);
    }
    
    // Меньше чем
    public function lt($value)
    {
        return $this->operator("<", $value);
    }
    
    //Больше чем
    public function gt($value)
    {
        return $this->operator(">", $value);
    }
    
    // Выполняет работу для методов operator.
    // Получает текущее поле и добавляет значение оператора
    // и результаты проверки к нему
    private function operator($symbol, $value)
    {
        if ($this->isVoid()) {
            throw new \Exception("Поле не определено");
        }
        
        //var_dump($this->currentfield);
        
        $this->currentfield->addTest($symbol, $value);
        return $this;
    }
    
    // Возвращает все сравнения, созданные до сих пор в ассоцитивном массиве
    public function getComps()
    {
        $comparisons = array();
        
        foreach ($this->fields as $key => $field) {
            $comparisons = array_merge($comparisons, $field->getComps());
        }
        
        return $comparisons;
    }
}

$idobj = new IdentityObject;
$idobj->field("name")->eq("The Good Show")->field("start")->gt(time())->lt(time() + (26*60*60));
var_dump($idobj->getComps());

// Подкласс с набором ограничений

class EventIdentityObject extends \woo\mapper\IdentityObject
{
    public function __construct($field = null)
    {
        parent::__construct($field, array('name', 'id', 'start', 'duration', 'space'));
    }
}

class VenueIdentityObject extends IdentityObject
{
    
}

$idobjEvent = new EventIdentityObject;
$idobjEvent->field("name")->eq("The Good Show")->field("start")->gt(time())->lt(time() + (26*60*60));
var_dump($idobjEvent->getComps());