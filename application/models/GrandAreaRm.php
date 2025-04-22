<?php
class Application_Model_GrandAreaRm extends Zend_Db_Table_Abstract
{
    protected $_name = 'grand_area_rm';
    
    function fetchPagination($page, $limit, &$total, $params){
		$db = Zend_Registry::get('db');

		$select = $db->select()
			->from(array('p' => $this->_name),
				array(new Zend_Db_Expr('SQL_CALC_FOUND_ROWS p.id'), 'p.*'));

		$select->where('p.grand_area_id = ? ',$params['grand_id']);
		$result = $db->fetchAll($select);
		$total = $db->fetchOne("select FOUND_ROWS()");
		return $result;
	}

	function getGrandAreaRM($grand_id){
		$db = Zend_Registry::get('db');

		$select = $db->select()
			->from(array('rm' => $this->_name),
				array('rm.*'));		
		$select->where('rm.grand_area_id =  ?',$grand_id);
		$result = $db->fetchAll($select);
		return $result;
	}

	function getGrandAreaByArea($area_id) {

		$db = Zend_Registry::get('db');

		$get = array(
            'ga_name'   => 'ga.name',
        );

        $select = $db->select()
            ->from(array('gal' => 'grand_area_list'), $get)
            ->join(array('ga' => 'grand_area'), 'gal.grand_area_id = ga.id', array())
            ->where('gal.area = ?', $area_id);

        //echo $select; die;
        $result = $db->fetchRow($select);
        return $result;

	}

}