<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../UserStore.php';
require_once __DIR__ . '/../Validator.php';

use PHPUnit\Framework\TestCase;

/**
 * Description of ValidatorTest
 *
 * @author grigory
 */
class ValidatorTest extends TestCase
{

    private $validator;

    public function setUp()
    {
        $store = new UserStore;
        $store->addUser("bob williams", "bob@example.com", "12345");
        $this->validator = new Validator($store);
    }

    public function tearDown()
    {
        
    }

    public function testValidateCurrectPass()
    {
        $this->assertTrue(
                $this->validator->validateUser("bob@example.com", "12345"), "Ожидалась успешная проверка."
        );
    }

    /*
     * Проверка метода, который должен вызываться объектом Validator при вводе
     *  пользователем неправильного пароля.
     */

    public function testValidateFalsePass()
    {
        $store = $this->getMock("UserStore");
        $this->validator = new Validator($store);
        $store->expects($this->once())
                ->method('notifyPasswordFailure')
                ->with($this->equalTo('bob@example.com'));
        $store->expects($this->any())
                ->method("getUser")
                ->will($this->returnValue(array(
                            "name" => "bob@example.com",
                            "pass" => "right")));
        $this->validator->validateUser("bob@example.com", "wrong");
    }

}
