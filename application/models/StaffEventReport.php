<?php
class Application_Model_StaffEventReport extends Zend_Db_Table_Abstract
{
    protected $_name = 'staff_event_report';

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

        if(isset($params['date']) and $params['date'])
        {
            $select
                ->where('p.date = ?', $params['date']);
        }

        if(isset($params['dealer']) and $params['dealer'])
        {
            $select
                ->where('p.dealer_id = ?', $params['dealer']);
        }

        if(isset($params['store']) and $params['store'])
        {
            $select
                ->where('p.store_id = ?', $params['store']);
        }

        if (isset($params['sort']) and $params['sort']) {

            $order_str = ' ';

            $desc = (isset($params['desc']) and $params['desc'] == 1) ? ' DESC ' : ' ASC ';

            if ($params['sort'] == 'dealer_id' || $params['sort'] == 'store_id' ) {

                $order_str = 'p.`'.$params['sort'] . '` ' .$desc;
            }

            if ($params['sort'] == 'visitors' || $params['sort'] == 'visitors' ) {

                $order_str = 'p.`'.$params['sort'] . '` ' .$desc;
            }

            if ($params['sort'] == 'date') {

                $order_str = 'p.`'.$params['sort'] . '` ' . $desc;
            }

            if ($params['sort'] == 'visitors') {

                $order_str = 'p.`'.$params['sort'] . '` ' . $desc;
            }

            $select->order(new Zend_Db_Expr($order_str));
        }

        $select->where('p.del = ? OR del IS NULL', 0);

        $select->order('p.date DESC');

        if ($limit)
            $select->limitPage($page, $limit);

        $result = $db->fetchAll($select);


        $total = $db->fetchOne("select FOUND_ROWS()");

        return $result;
    }

}