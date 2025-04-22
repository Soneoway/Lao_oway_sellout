<?php
class Application_Model_ImeiDistributorReceive extends Zend_Db_Table_Abstract
{
	protected $_name = 'imei_distributor_receive';
    protected $_schema = WAREHOUSE_DB;


    function getImeiBySN($sn_ref) {

    	$db = Zend_Registry::get('db');

        $select = $db->select()
            ->from(array('i' => WAREHOUSE_DB.'.imei'), array('imei_sn' => 'i.imei_sn'))
            ->join(array('m' => WAREHOUSE_DB.'.market'), 'i.sales_sn = m.sn', array());

        $select->where('m.sn_ref = ?', $sn_ref);
        $select->group(array('i.sales_sn','i.imei_sn'));

        $result = $db->fetchAll($select);
        return $result;
    }
}