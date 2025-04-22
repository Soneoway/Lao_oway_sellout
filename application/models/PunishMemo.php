<?php
class Application_Model_PunishMemo extends Zend_Db_Table_Abstract
{
    protected $_name = 'punish_memo';

    function getList($params) {

    	$d = explode('/', $params['from']);
		$from = $d[2].'-'.$d[1].'-'.$d[0];

		$d = explode('/', $params['to']);
		$to = $d[2].'-'.$d[1].'-'.$d[0];

    	$db = Zend_Registry::get('db');

    	$get = array(
            'pm_id'         => 'pm.id',
            'from_date'     => 'pm.from_date',
            'to_date'       => 'pm.to_date',
            'price'         => 'pm.price',
            'pm_remark'     => 'pm.remark',
            'punish_type'   => 'pl.name',

            'area_name'     => 'a.name',
            'staff_code'    => 's.code',
            'staff_name'    => new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)"),
            'staff_group'   => 'g.name',     
        );

        $select = $db->select()
            ->from(array('pm' => 'punish_memo'), $get)
            ->joinLeft(array('pl'=> 'punish_list')  , 'pm.punish_id = pl.id', array())
            ->joinLeft(array('a' => 'area')         , 'pm.area_id = a.id'   , array())
            ->joinLeft(array('s' => 'staff')        , 'pm.staff_id = s.id'  , array())
            ->joinLeft(array('g' => 'group')        , 's.group_id = g.id'   , array())
            ->order('pm.created_at ASC');

		if (isset($params['from']) && $params['from']) {
			$select->where('pm.from_date <= ?', $from);
		}

		if (isset($params['to']) && $params['to']) {
			$select->where('pm.to_date >= ?', $to);
		}

        //echo $select; //die;
        $result = $db->fetchAll($select);
        return $result;

    }

    function getDetail($id) {

        $db = Zend_Registry::get('db');

        $get = array(
            'pm_id'         => 'pm.id',
            'pm_area_id'    => 'pm.area_id',
            'from_date'     => new Zend_Db_Expr("DATE_FORMAT(pm.from_date, '%d/%m/%Y')"),  
            'to_date'       => new Zend_Db_Expr("DATE_FORMAT(pm.to_date, '%d/%m/%Y')"),  
            'price'         => 'pm.price',
            'pm_remark'     => 'pm.remark',
            'punish_id'     => 'pm.punish_id',

            'staff_province'=> 'rm.name',
            'staff_id'      => 's.id',
            'staff_name'    => new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)"),
            'staff_email'   => 's.email',
        );

        $select = $db->select()
            ->from(array('pm' => 'punish_memo'), $get)
            ->joinLeft(array('s' => 'staff')            , 'pm.staff_id = s.id'          , array())
            ->joinLeft(array('rm'=> 'regional_market')  , 's.regional_market = rm.id'   , array())
            ->joinLeft(array('a' => 'area')             , 'rm.area_id = a.id'           , array())
            ->where('pm.id = ?', $id);

        //echo $select; //die;
        $result = $db->fetchRow($select);
        return $result;

    }

}