<?php

class Application_Model_OffDateAdd extends Zend_Db_Table_Abstract
{
    protected $_name = 'off_date_add';

    public function getDate($limit, $staff_id)
    {
        $month = date('m', strtotime($limit));

        if (isset($limit) and $limit)
        {
            $limit = $limit . ' 0:0:0';
        }
        $db = Zend_Registry::get('db');
        if (isset($staff_id) and isset($staff_id))
        {
            $select = $db->select()->from(array('p' => $this->_name), array('p.*'));
            $select->where('staff_id = ? ', $staff_id);
            $select->where('date >=  ?', $limit);
            // $select->where('status = ?', '1');
            $result = count($db->fetchAll($select));
            //   echo (count($db->fetchAll($select)));exit;
            if (isset($result) and $result)
                return $result;
            else
                return 0;


        } else
            return - 1;
    }

    public function getTotalOff($staff_id)
    {
        $QOff_Decrease = new Application_Model_OffHistory();
        $result = intval($this->getDate(DAY_OFF_BEGIN, $staff_id) - $QOff_Decrease->
            getDate(DAY_OFF_BEGIN, $staff_id));
        if (isset($result) and $result)
        {
            return $result;
        } else
        {
            return 0;
        }
    }
}
