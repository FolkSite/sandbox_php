<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../UserStore.php';

use PHPUnit\Framework\TestCase;

/**
 * Description of UserStoreTest
 *
 * @author grigory
 */
class UserStoreTest extends TestCase
{

    private $store;

    public function setUp()
    {
        $this->store = new UserStore;
    }

    public function tearDown()
    {
        
    }

    public function testGetUser()
    {
        $this->store->addUser("bob williams", "a@b.com", "12345");
        $user = $this->store->getUser("a@b.com");
        $this->assertEquals($user['mail'], "a@b.com");
        $this->assertEquals($user['name'], "bob williams");
        $this->assertEquals($user['pass'], "12345");
    }
    
    /**
     * Проверяет создает ли метод исключение если пароль меньше 5 символов.
     */
    public function testAddUserShortPass()
    {
        $this->expectException('Exception');
        $this->store->addUser("bob williams", "bob@example.com", "ff");
    }

}
