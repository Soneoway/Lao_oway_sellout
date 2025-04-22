<?php
class Application_Model_StaffPoint extends Zend_Db_Table_Abstract
{
    protected $_name = 'staff_point';

    function fetchPagination($page, $limit, &$total, $params)
    {
        $db = Zend_Registry::get('db');

        $select = $db->select();

        $select->from(array('p' => $this->_name),array(new Zend_Db_Expr(
            'SQL_CALC_FOUND_ROWS
             p.*'
        )));

        if(isset($params['staff_id']) and $params['staff_id'])
        {
            $select
                ->where('p.staff_id = ?', $params['staff_id']);
        }


        $select->order(array('p.month DESC'));
        $select->order(array('p.year DESC'));

        if ($limit)
            $select->limitPage($page, $limit);
        $result = $db->fetchAll($select);


        $total = $db->fetchOne("select FOUND_ROWS()");

        return $result;
    }

    function getInfoStaff($staff_id)
    {
        $db = Zend_Registry::get('db');

        $select = $db->select();

        $select->from(array('p' => $this->_name),array('p.point','p.month','p.year'));

        $select ->where('p.staff_id = ?', $staff_id);

        $select->order(array('p.month DESC'));

        $select->order(array('p.year DESC'));

        $result = $db->fetchAll($select);

        return $result;

    }
}