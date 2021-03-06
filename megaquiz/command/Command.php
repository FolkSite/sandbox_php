<?php
/**
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace megaquiz\command;
/**
 * Description of Command
 *
 * @author grigory
 */
abstract class Command
{
    //put your code here
    
    /**
     * Выполняет основную операцию для этого класса. 
     * В классах Command выполняется одна операция. Их легко добавлять в проект
     * и удалять из него, экземпляры класса можно сохранять после создания и 
     * запуска метода execute() на досуге
     * 
     * @param $context CommandContext Общие данные приложения
     * @return bool false при ошибке, true при успехе
     * @link http://www.example.com More info
     * @uses \megaquiz\command\CommandContext
     */
    abstract function execute(\CommandContext $context);
}
