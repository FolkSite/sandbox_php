<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of FeedbackCommand
 *
 * @author grigory
 */
class FeedbackCommand extends Command
{
    public function execute(\CommandContext $context)
    {
        $msgSystem = Registry::getMessageSystem();
        $email = $context->get('email');
        $msg = $context->get('msg');
        $topic = $context->get('topic');
        $result = $msgSystem->send($email, $msg, $topic);
        
        if (!$result) {
            $context->setError($msgSystem->getError());
            return false;
        }
        
        return true;
    }
}
