<?php
class Application_Model_ImeiKpiLog extends Zend_Db_Table_Abstract
{
	protected $_name = 'imei_kpi_log';

	function get_lastupdate() {
		$db = Zend_Registry::get('db');

        $select = $db->select()
            ->from(array('ikl' => 'imei_kpi_log'), array('created_log' => 'ikl.created_date') )
            ->join(array('s' => 'staff'), 'ikl.staff_id = s.id', array('staff_id' => 's.id', 'staff_name' => new Zend_Db_Expr("CONCAT(s.firstname,' ',s.lastname)")))
          	->order('ikl.created_date desc');

        //echo $select;die;
		$result = $db->fetchRow($select);
		//print_r($result);die;
		return $result;
	}
	
}                                                      
