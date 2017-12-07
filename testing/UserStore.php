<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of UserStore
 *
 * @author grigory
 */
class UserStore
{

    private $users = array();

    public function addUser($name, $mail, $pass)
    {
        if (isset($this->users[$mail])) {
            throw new Exception("Пользователь {$mail} уже зарегистрирован.");
        }

        if (strlen($pass) < 5) {
            throw new Exception("Длина пароля должна быть не менее 5 мисволов.");
        }

        $this->users[$mail] = array(
            'pass' => $pass,
            'mail' => $mail,
            'name' => $name
        );
        
        return true;
    }
    
    public function notifyPasswordFailure($mail)
    {
        if (isset($this->users[$mail])) {
            $this->users[$mail]['failed'] = time();
        }
    }
    
    public function getUser($mail) 
    {
        return ($this->users[$mail]);
    }

}

// Пример реализации.
$store = new UserStore();
$store->addUser("bob williams", "bob@example.com", "12345");
$user = $store->getUser("bob@example.com");
var_dump($user);
