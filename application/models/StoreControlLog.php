<?php
class Application_Model_StoreControlLog extends Zend_Db_Table_Abstract
{
    protected $_name = 'store_control_log';

    function short_report_store_control_log($params) {

        $db = Zend_Registry::get('db');

        $get = array(
        	'staff_code' 	=> 's.code',
        	'staff_name' 	=> new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)"), 
        	'staff_group'	=> 'g.name',
        	'off_date'		=> 's.off_date',
        	'from_date' 	=> 'scl.from_date',
        	'to_date'		=> 'scl.to_date',
        );

        $select = $db->select()
            ->from(array('scl' => 'store_control_log'), $get)
            ->joinLeft(array('s' => 'staff'), 'scl.staff_id = s.id'	, array())
            ->joinLeft(array('g' => 'group'), 's.group_id = g.id'	, array())
            ->where('scl.store_control_id = ?', $params['store_control_id'])
            ->order('from_date ASC');

        //echo $select;
        $result = $db->fetchAll($select);
        return $result;
    }

}