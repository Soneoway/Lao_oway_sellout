<?php
class Application_Model_LockedImei extends Zend_Db_Table_Abstract
{
	protected $_name = 'imei_lock';
	protected $_schema = WAREHOUSE_DB;

    public function getLockImei($imei)
    {
        $db = Zend_Registry::get('db');
        $select = $db->select()
            ->from(array('p' => WAREHOUSE_DB.'.'.$this->_name),
                array('p.*'));
            $select->where('p.imei_log = ?', $imei); 
            $result = $db->fetchAll($select);
           
        return $result;

    } 
}
