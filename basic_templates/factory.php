<?php

abstract class ApptEncoder
{

    abstract function encode();
}

class BloggsApptEncode extends ApptEncoder
{

    function encode()
    {
        return "<p>Данные о встрече закодированы в формате BloggsCal</p>";
    }

}

class MegaApptEncode extends ApptEncoder
{

    function encode()
    {
        return "<p>Данные о встрече закодированы в формате MegaCal</p>";
    }

}

abstract class CommsManager
{

    abstract function getHeaderText();

    abstract function getApptEncoder();

    abstract function getFooterText();
}

class BloggsCommsManager extends CommsManager
{

    function getHeaderText()
    {
        return "<p>BloggsCal верхний колонтитул</p>";
    }

    function getApptEncoder()
    {
        return new BloggsApptEncode();
    }

    function getFooterText()
    {
        return "<p>BloggsCal нижний колонтитул</p>";
    }

}

class MegaCommsManager extends CommsManager
{

    function getHeaderText()
    {
        return "<p>MegaCal верхний колонтитул</p>";
    }

    function getApptEncoder()
    {
        return new MegaApptEncode();
    }

    function getFooterText()
    {
        return "<p>MegaCal нижний колонтитул</p>";
    }

}

$mgr = new BloggsCommsManager();
echo $mgr->getHeaderText();
echo $mgr->getApptEncoder()->encode();
echo $mgr->getFooterText();

$mgr2 = new MegaCommsManager();
echo $mgr2->getHeaderText();
echo $mgr2->getApptEncoder()->encode();
echo $mgr2->getFooterText();

/*
 * пример способа установки и получения настроик для создания классов в 
 * абстрактной фабрике
 */

/**
 * класс с настройками
 */
class Settings{

    static $COMMSTYPE = 'Bloggs';

}

/**
 * абстрактная фабрика
 */
class AppConfig
{
    private static $instance;
    private $commsManager;
    
    function __construct()
    {
        $this->init();
    }
    
    private function init() 
    {
        switch (Settings::$COMMSTYPE) {
            case 'Mega':
                $this->commsManager = new MegaCommsManager();
                break;
            default:
                $this->commsManager = new BloggsCommsManager();
        }
    }
    
    public static function getInstance() 
    {
        if (empty(self::$instance)) {
            // возвращает объект, созданный из класса AppConfig
            self::$instance = new self();
        }
        
        return self::$instance;
    }
    
    public function getCommsManager() {
        return $this->commsManager;
    }

}

$commsMgr = AppConfig::getInstance()->getCommsManager();
echo "<p>".$commsMgr->getApptEncoder()->encode()."</p>";