<?php
class Application_Model_Dashboard extends Zend_Db_Table_Abstract
{
	protected $_name = 'dashboard';

	function fetchPagination($page, $limit, &$total, $params){
		$db = Zend_Registry::get('db');

		$select = $db->select()
			->from(array('p' => $this->_name),
				array(new Zend_Db_Expr('SQL_CALC_FOUND_ROWS p.id'), 'p.*'));

		if($limit)
			$select->limitPage($page, $limit);

		$result = $db->fetchAll($select);
		$total = $db->fetchOne("select FOUND_ROWS()");
		return $result;
	}
}	
