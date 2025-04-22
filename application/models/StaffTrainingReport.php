<?php
class Application_Model_StaffTrainingReport extends Zend_Db_Table_Abstract
{
    protected $_name = 'staff_training_report';

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

        if(isset($params['type']) and $params['type'])
        {
            $select
                ->where('p.type = ?', $params['type']);
        }

        if(isset($params['area']) and $params['area'])
        {
            $select
                ->where('p.area = ?', $params['area']);
        }

        if(isset($params['province']) and $params['province'])
        {
            $select
                ->where('p.province = ?', $params['province']);
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

        if(isset($params['from_date']) and $params['from_date'])
        {
            $select
                ->where('p.from_date >= ?', $params['from_date']);
        }

        if(isset($params['to_date']) and $params['to_date'])
        {
            $select
                ->where('p.to_date <= ?', $params['to_date']);
        }

        if (isset($params['sort']) and $params['sort']) {

            $order_str = ' ';

            $desc = (isset($params['desc']) and $params['desc'] == 1) ? ' DESC ' : ' ASC ';

            if (
                $params['sort'] == 'dealer_id'
                || $params['sort'] == 'store_id'
                || $params['sort'] == 'date'
                || $params['sort'] == 'type'
                || $params['sort'] == 'area'
                || $params['sort'] == 'province'
                || $params['sort'] == 'quantity_participant'
                || $params['sort'] == 'quantity_disqualified'
                || $params['sort'] == 'quantity_remain'
                || $params['sort'] == 'from_date'
                || $params['sort'] == 'to_date'
            ) {

                $order_str = 'p.`'.$params['sort'] . '` ' .$desc;
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

    /*function get dealer */
    public function getDealerArea($content)
    {
        $db              = Zend_Registry::get('db');

        $arrayDealer     = array();

        if(count(array_keys($content))>0)
        {
            $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini');
            $config = $config->toArray();

            $select_dealer  = $db->select()->from(array('w' => $config['resources']['db2']['params']['dbname'] . '.distributor'), array('w.id','w.title'))
                ->where('district IN (?) ',array_keys($content))
                ->where('del = ? OR del IS NULL',0);
            $result  = $db->query($select_dealer)->fetchAll();

            foreach($result as $key => $value)
            {
                $arrayDealer[$value['id']] = $value['title'];
            }

        }
        return $arrayDealer;

    }
}