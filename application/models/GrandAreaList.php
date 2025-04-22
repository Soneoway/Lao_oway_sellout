<?php
class Application_Model_GrandAreaList extends Zend_Db_Table_Abstract
{
    protected $_name = 'grand_area_list';

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

	function fetchAreaAll($area){
		$db = Zend_Registry::get('db');

		$select = $db->select()
			->from(array('p' => $this->_name),
				array('p.id','p.area'));
		$select->where('p.area = ? ',$area);		
		$result = $db->fetchAll($select);
		$total = $db->fetchOne("select FOUND_ROWS()");
		return $result;
	}
}