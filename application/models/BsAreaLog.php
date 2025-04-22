<?php
class Application_Model_BsAreaLog extends Zend_Db_Table_Abstract
{
    protected $_name = 'bs_area_log';

    function short_report_bs_area_control_log($params) {

        $db = Zend_Registry::get('db');

        $get = array(
        	'staff_code' 	=> 's.code',
        	'staff_name' 	=> new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)"), 
        	'staff_group'	=> 'g.name',
        	'off_date'		=> 's.off_date',
        	'from_date' 	=> 'bal.from_date',
        	'to_date'		=> 'bal.to_date',
        );

        $select = $db->select()
            ->from(array('bal' => 'bs_area_log'), $get)
            ->joinLeft(array('s' => 'staff'), 'bal.staff_id = s.id'	, array())
            ->joinLeft(array('g' => 'group'), 's.group_id = g.id'	, array())
            ->where('bal.bs_area_id = ?', $params['bs_area_id'])
            ->order('from_date ASC');

        //echo $select;
        $result = $db->fetchAll($select);
        return $result;
    }

}