<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use Facebook\WebDriver\WebDriver;

/**
 * Description of SeleniumTest
 *
 * @author grigory
 */
class SeleniumTest extends TestCase
{

    protected function setUp()
    {
        $host = 'http://localhost:4444/wd/hub';
        $this->driver = Facebook\WebDriver\Remote\RemoteWebDriver::create(
                        $host, Facebook\WebDriver\Remote\DesiredCapabilities::chrome()
        );
    }

    public function testAddVenue()
    {
        
    }

}
