<?php
/**
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace megaquiz\command;
/**
 * Описание класса CommandContext.
 * @see \megaquiz\command\Command::execute() the execute() method
 */
class CommandContext
{
    /**
     * The application name.
     * 
     * Имя приложения
     * 
     * @var string Description
     */
    public $applivationName;
    
    /**
     * Encapsulated Keys/values.
     * 
     * Содержит данные в формате "ключ-значение"
     * 
     * @var array Description
     */
    private $params = array();
    
    /**
     * An error message.
     * Сообщение об ошибке.
     * @var string Description
     */
    
    private $error = "";
}
