<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of User
 *
 * @author grigory
 */
class User
{

    private $name;
    private $mail;
    private $pass;
    private $failed;

    public function __construct($name, $mail, $pass)
    {
        if (strlen($pass) < 5) {
            throw new Exception("Длина пароля должна быть не менее 5 символов.");
        }

        $this->name = $name;
        $this->mail = $mail;
        $this->pass = $pass;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getMail()
    {
        return $this->mail;
    }

    public function getPass()
    {
        return $this->pass;
    }

    public function failed($time)
    {
        $this->failed = $time;
    }

}
