<?php

class Zend_View_Helper_Access extends Zend_View_Helper_Abstract
{
    public function access($controller, $action, $module = 'default')
    {
        $userStorage = Zend_Auth::getInstance()->getStorage()->read();

        if ($userStorage && is_array($userStorage->accesses)) {

            if ( ! ( $userStorage->id == SUPERADMIN_ID || $userStorage->group_id == ADMINISTRATOR_ID ) ) {
                if ( in_array( ( $module . '::' . $controller . '::' . $action ) , $userStorage->accesses ) ) 
                    return true;
                
            } else {
                return true;
            }
        }

        return false;

    }
}