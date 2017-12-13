<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once __DIR__ . '/../vendor/autoload.php';
require_once 'UserStore.php';

/**
 * Description of Validator
 *
 * @author grigory
 */
class Validator
{

    private $store;

    public function __construct(UserStore $store)
    {
        $this->store = $store;
    }

    public function validateUser($mail, $pass)
    {
        if (!is_array($user = $this->store->getUser($mail))) {
            return false;
        }

        if ($user['pass'] == $pass) {
            return true;
        }

        $this->store->notifyPasswordFailure($mail);

        return false;
    }

}

// Проверка реализации.
/*
$store = new UserStore();
$store->addUser("bob williams", "bob@example.com", "12345");

$validator = new Validator($store);
if ($validator->validateUser("bob@example.com", "12345")) {
    var_dump("Привет, друзья!");
}
 * 
 */