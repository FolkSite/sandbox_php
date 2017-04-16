<?php

abstract class Expression
{

    private static $keycount = 0;
    private $key;

    abstract function interpret(InterpreterContext $context);

    function getKey()
    {
        if (!isset($this->key)) {
            self::$keycount++;
            $this->key = self::$keycount;
        }
        return $this->key;
    }

}

class LiteralExpression extends Expression
{

    private $value;

    function __construct($value)
    {
        $this->value = $value;
    }

    function interpret(\InterpreterContext $context)
    {
        $context->replace($this, $this->value);
    }

}

/*
 * Совместно используемое хранилище данных
 */
class InterpreterContext
{

    private $expressionstore = array();

    function replace(Expression $exp, $value)
    {
        $this->expressionstore[$exp->getKey()] = $value;
    }

    // получает объект и возвращает элемент в массииве, который хранит именно его данные
    function lookup(Expression $exp)
    {
        return $this->expressionstore[$exp->getKey()];
    }
    
    function getData()
    {
        return $this->expressionstore;
    }

}

class VariableExpresion extends Expression
{

    private $name;
    private $val;

    function __construct($name, $val = null)
    {
        $this->name = $name;
        $this->val = $val;
    }

    function interpret(\InterpreterContext $context)
    {
        if (!is_null($this->val)) {
            $context->replace($this, $this->val);
            $this->val = null;
        }
    }

    function setValue($value)
    {
        $this->val = $value;
    }

    function getKey()
    {
        return $this->name;
    }

}

abstract class OperatorExpression extends Expression
{

    protected $l_op;
    protected $r_op;

    public function __construct(Expression $l_op, Expression $r_op)
    {
        $this->l_op = $l_op;
        $this->r_op = $r_op;
    }

    function interpret(\InterpreterContext $context)
    {
        $this->l_op->interpret($context);
        $this->r_op->interpret($context);
        $result_l = $context->lookup($this->l_op);
        $result_r = $context->lookup($this->r_op);
        $this->doInterpret($context, $result_l, $result_r);
    }

    protected abstract function doInterpret(InterpreterContext $context, $result_l, $result_r);
}

class EqualsExpression extends OperatorExpression
{

    protected function doInterpret(InterpreterContext $context, $result_l, $result_r)
    {
        $context->replace($this, $result_l == $result_r);
    }

}

class BoleanOrExpression extends OperatorExpression
{

    protected function doInterpret(InterpreterContext $context, $result_l, $result_r)
    {
        $context->replace($this, $result_l || $result_r);
    }

}

class BoleanAndExpression extends OperatorExpression
{

    protected function doInterpret(InterpreterContext $context, $result_l, $result_r)
    {
        $context->replace($this, $result_l && $result_r);
    }

}

$context = new InterpreterContext();
$input = new VariableExpresion('input');
$statement = new BoleanOrExpression(
        new EqualsExpression($input, new LiteralExpression('Четыре')), 
        new EqualsExpression($input, new LiteralExpression('4'))
);

foreach (array("Четыре", "4", "52") as $val) {
    $input->setValue($val);
    var_dump("val: " . $val);
    $statement->interpret($context);
    var_dump($context->lookup($statement));
    if ($context->lookup($statement)) {
        var_dump("соответствует");
    } else {
        var_dump("не соответствует");
    }
    //var_dump($context->getData());
}



/*
$context = new InterpreterContext();
$literal = new LiteralExpression('Четыре');
$literal->interpret($context);
var_dump($literal->getKey());
var_dump($context->lookup(($literal)));

$context = new InterpreterContext;
$myvar = new VariableExpresiion('input', 'Четыре');
$myvar->interpret($context);
var_dump($context->lookup($myvar));

$newmyvar = new VariableExpresiion('input');
$newmyvar->interpret($context);
var_dump($context->lookup($myvar));

$myvar->setValue('Пять');
$myvar->interpret($context);
var_dump($context->lookup($myvar));
var_dump($context->lookup($newmyvar));
 * 
 */
