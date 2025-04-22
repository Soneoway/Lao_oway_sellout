<?php
class Application_Model_CheckImeiToolLog extends Zend_Db_Table_Abstract
{
    protected $_name = 'check_imei_tool_log';

    function log($list_imei, $type = CheckImeiLogType::Valid)
    {
        $userStorage = Zend_Auth::getInstance()->getStorage()->read();
        $ip = '';

        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        $data = array(
            'staff_id'   => $userStorage->id,
            'ip_address' => $ip,
            'time'       => time(),
            'list_imei'  => serialize($list_imei),
            'quantity'   => count($list_imei),
            'type'       => $type,
            );

        $this->insert($data);
    }
}

include_once APPLICATION_PATH.'/../library/My/Class/Enum.php';

class CheckImeiLogType extends CosEnum {    
    const Valid    = 1;
    const Customer = 2;
}