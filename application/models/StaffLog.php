<?php
class Application_Model_StaffLog extends Zend_Db_Table_Abstract
{
	protected $_name = 'staff_log';

	function fetchPagination($page, $limit, &$total, $params){
        $db = Zend_Registry::get('db');

        $select = $db->select()
            ->from(array('p' => $this->_name),
                array(new Zend_Db_Expr('SQL_CALC_FOUND_ROWS p.id'), 'p.*'));

        if (isset($params['id']) and $params['id'])
            $select->where('p.id = ?', $params['id']);

        if (isset($params['object']) and $params['object'])
            $select->where('p.object = ?', $params['object']);

        $select->order('p.time DESC');

        if ($limit)
        	$select->limitPage($page, $limit);

        $result = $db->fetchAll($select);
        $total = $db->fetchOne("select FOUND_ROWS()");
        return $result;
    }
}
