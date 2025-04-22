<?php
class Application_Model_GrandArea extends Zend_Db_Table_Abstract
{
    protected $_name = 'grand_area';
    
    function fetchPagination($page, $limit, &$total, $params){

    	set_time_limit(0);
        ini_set('memory_limit', '-1');
        error_reporting(~E_ALL);
        ini_set("display_error", '0');

		$db = Zend_Registry::get('db');

		$select = $db->select()
			->from(array('p' => $this->_name),
				array(new Zend_Db_Expr('SQL_CALC_FOUND_ROWS p.id'), 'p.*'));

		
		$result = $db->fetchAll($select);
		$total = $db->fetchOne("select FOUND_ROWS()");
		return $result;
	}
	function getGrandArealist($grand_id){
		$db = Zend_Registry::get('db');

		$select = $db->select()
			->from(array('ga' => $this->_name),
				array( 'ga.*','grand_id'=>'ga.id'));
		$select->joinLeft(array('gl'=>'grand_area_list'),'ga.id=gl.grand_area_id',array('gl.*'));		
		$select->where('ga.id =  ?',$grand_id);
		$result = $db->fetchAll($select);
		
		return $result;
	}

	function getGrandAreaByAsmTable($staff_id) {

		$db = Zend_Registry::get('db');

        $get = array(
            'id'   => 'ga.id',
            'name' => 'ga.name',
        );

        $select = $db->select()
            ->from(array('gar' => 'grand_area_rm'), $get)
            ->join(array('ga'  => 'grand_area'), 'gar.grand_area_id = ga.id', array())
			->where('gar.rm_id = ?', $staff_id)  
			->order(array('ga.name ASC'));

		//echo $select; 
		$data = $db->fetchAll($select);
		return $data;
	}

}