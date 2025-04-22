<?php

class Application_Model_OffHistory extends Zend_Db_Table_Abstract
{
    protected $_name = 'off_history';

    function fetchPagination($page, $limit, &$total, $params)
    {
        $db = Zend_Registry::get('db');

        $select = $db->select()->from(array('p' => $this->_name), array(new Zend_Db_Expr
                ('SQL_CALC_FOUND_ROWS p.id'), 'p.*'));

        $select->joinLeft(array('s' => 'staff'), 'p.staff_id = s.id', array(
            's.email',
            's.firstname',
            's.lastname',
            's.department',
            's.team',
            's.regional_market',
            's.phone_number'));

        $select->joinLeft(array('t' => 'time'), 'p.time_id = t.id', array(
            's.email',
            's.firstname',
            's.lastname',
            's.department',
            's.team',
            's.regional_market',
            's.phone_number'));

        if (isset($params['name']) and $params['name'])
            $select->where('p.staff_id = ?', $params['name']);


        $select->order('p.id', 'COLLATE utf8_unicode_ci ASC');

        $select->limitPage($page, $limit);

        $result = $db->fetchAll($select);
        $total = $db->fetchOne("select FOUND_ROWS()");
        return $result;
    }

    public function getDate($limit, $staff_id)
    {
        $month = date('m', strtotime($limit));

        if (isset($limit) and $limit)
        {
            $limit = $limit . ' 0:0:0';
        }

        $db = Zend_Registry::get('db');
        if (isset($staff_id) and isset($month))
        {
            $select = $db->select()->from(array('p' => $this->_name), array('p.*'));
            $select->where('staff_id = ? ', $staff_id);
            $select->where('date >=  ?', $limit);
            $select->where('status <>  0', null);
            // $select->where('status = ?', '1');
            $result = $db->fetchAll($select);
            $total_off = 0;
            foreach ($result as $item)
            {
                if (isset($item['shift']) and $item['shift'] == 1)
                {
                    $total_off += 0.5;
                } else
                {
                    $total_off += 1;
                }
            }


            return $total_off;
        } else
            return 0;
    }
    /*  */
    public function checkOff($staff_id, $month, $allow_off)
    {

        $QOffHistory = new Application_Model_OffHistory();
        $db = Zend_Registry::get('db');
        $month = intval($month);
        $select = $db->select()->from(array('p' => $this->_name), array('p.*'));
        $select->where('staff_id = ? ', $staff_id);
        $select->where('MONTH(date) =  ?', $month);
        $select->where('status = ?', '0');
        $result = $db->fetchAll($select);


        $total_request_off = count($result);

        $allow_off_accept = 0;

        foreach ($result as $k => $v)
        {
            // trong thang xin nghi nua ngay
            if ($v['shift'] != 0)
            {
                if ($allow_off_accept < $allow_off)
                {
                    $where = array();
                    $where[] = $QOffHistory->getAdapter()->quoteInto('id = ? ', $v['id']);
                    $where[] = $QOffHistory->getAdapter()->quoteInto('shift = ? ', 1);
                    $data = array('status' => 1, );
                    $QOffHistory->update($data, $where);
                    $allow_off_accept = $allow_off_accept + 0.5;
                }

            } else
            {
                if ($allow_off_accept < $allow_off)
                {
                    $where = array();
                    $where[] = $QOffHistory->getAdapter()->quoteInto('id = ? ', $v['id']);
                    $where[] = $QOffHistory->getAdapter()->quoteInto('shift <> ? ', 1);
                    $data = array('status' => 1, );
                    $QOffHistory->update($data, $where);
                    $allow_off_accept++;
                }
            }

        }

    }


}
