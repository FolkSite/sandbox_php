<?php
/*
 * Проблема: ввод новых классов для генерации территории приводит к дублированию кода
 */
/*
abstract class Tile{

    abstract public function getWealthFactor();

}

class Plains extends Tile 
{
    private $wealthfactor = 2;
    
    public function getWealthFactor()
    {
        return $this->wealthfactor;
    }
}

class DiamondPlains extends Plains
{
    public function getWealthFactor()
    {
        return parent::getWealthFactor() + 2;
    }
}

class PullutedPlains extends Plains
{
    public function getWealthFactor()
    {
        return parent::getWealthFactor() - 4;
    }
}

$tile = new PullutedPlains();
var_dump($tile->getWealthFactor());
var_dump($tile);
 * 
 */

/*
 * Реализация
 */

abstract class Tile{

    abstract public function getWealthFactor();

}

class Plains extends Tile
{
    private $wealthfactor = 2;
    
    public function getWealthFactor()
    {
        return $this->wealthfactor;
    }
}

abstract class TileDecorator extends Tile
{
    protected $tile;
    
    public function __construct(Tile $tile)
    {
        $this->tile = $tile;
    }
}

class DiamondDecorator extends TileDecorator
{
    public function getWealthFactor()
    {
        return $this->tile->getWealthFactor() + 2;
    }
}

class PollutionDecorator extends TileDecorator
{
    public function getWealthFactor()
    {
        return $this->tile->getWealthFactor() - 4;
    }
}

$tile = new Plains();
var_dump($tile->getWealthFactor());

$tile = new DiamondDecorator(new Plains());
var_dump($tile->getWealthFactor());

$tile = new PollutionDecorator(
            new DiamondDecorator(new Plains())
        );
var_dump($tile->getWealthFactor());

/*
 * Еще один пример реализации
 */

class RequestHelper
{
    
}

abstract class ProcessRequest
{
    abstract public function process(RequestHelper $reg);
}

class MainProcess extends ProcessRequest
{
    public function process(RequestHelper $reg) 
    {
        echo __CLASS__ . ": выполнение запроса<br>";
    }
}

abstract class DecorateProcess extends ProcessRequest
{
    protected $processrequest;
    
    function __construct(ProcessRequest $pr)
    {
        $this->processrequest = $pr;
    }
}

class LogRequest extends DecorateProcess
{
    public function process(\RequestHelper $reg)
    {
        echo __CLASS__ . ": регистрация запроса<br>";
        $this->processrequest->process($reg);
    }
}

class AuthenticateRequest extends DecorateProcess
{
    public function process(\RequestHelper $reg)
    {
        echo __CLASS__ . ": аутентификация запроса<br>";
        $this->processrequest->process($reg);
    }
}

class StructureRequest extends DecorateProcess
{
    public function process(\RequestHelper $reg)
    {
        echo __CLASS__ . ": упорядочение данных запроса<br>";
        $this->processrequest->process($reg);
    }
}

$process = new AuthenticateRequest(
                new StructureRequest(
                        new LogRequest(
                                new MainProcess()
            )));

$process->process(new RequestHelper());