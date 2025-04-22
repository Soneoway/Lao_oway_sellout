<?php
class Application_Model_StoreOppo extends Zend_Db_Table_Abstract
{
	// protected $_name = hr.'.store';


    function getStoreFromHr(){
        $db = Zend_Registry::get('db');
        $select = $db->select()
            ->from(array('sto' => HR_DB.'.store'),array('sto.*'));
        $select->where('sto.del IS NULL');
        $result = $db->fetchAll($select);
        return $result;
    }

    function getSingStoreFromHr($store_id){
        $db = Zend_Registry::get('db');

        $select = $db->select()
            ->from(array('sto' => HR_DB.'.store'),array('sto.name'));
        $select->where('sto.id =?',$store_id);
        $result = $db->fetchRow($select);

        return $result;

    }

}