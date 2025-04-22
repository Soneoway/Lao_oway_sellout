<?php
class Application_Model_PunishMemoStore extends Zend_Db_Table_Abstract
{
    protected $_name = 'punish_memo_store';

    function getList($params) {

    	$d = explode('/', $params['from']);
		$from = $d[2].'-'.$d[1].'-'.$d[0];

		$d = explode('/', $params['to']);
		$to = $d[2].'-'.$d[1].'-'.$d[0];

    	$db = Zend_Registry::get('db');

    	$get = array('pms.*');

        $select = $db->select()
            ->from(array('pms' => 'punish_memo_store'), $get)
            ->order('pms.created_at DESC');

		if (isset($params['from']) && $params['from']) {
			$select->where('pms.from_date <= ?', $from);
		}

		if (isset($params['to']) && $params['to']) {
			$select->where('pms.to_date >= ?', $to);
		}

        //echo $select; die;
        $result = $db->fetchAll($select);
        return $result;

    }

    function store_list($store_list) {
        $db = Zend_Registry::get('db');

        $get = array(
            'st_area'    => 'a.name',
            'st_id'      => 'st.id',
            'st_name'    => 'st.name',
            'st_type'    => 'o.org_name', 
            'st_status'  => 'st.del',
            'sale_id'    => 's.id',
            'sale_code'  => 's.code',
            'sale_name'  => new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)"),
            'sale_group' => 'g.name',
        );

        $select = $db->select()->from(array('st' => 'store'), $get)
            ->join(array('o'    => 'org')               , 'st.org_dealer = o.org_id'    , array())
            ->join(array('rm'   => 'regional_market')   , 'st.regional_market = rm.id'  , array())
            ->join(array('a'    => 'area')              , 'rm.area_id = a.id'           , array())
            ->joinLeft(array('ss' => 'store_staff')     , 'st.id = ss.store_id AND ss.is_leader = 1', array())
            ->joinLeft(array('s' => 'staff')            , 'ss.staff_id = s.id'          , array())
            ->joinLeft(array('g' => 'group')            , 's.group_id = g.id'           , array())
            ->where('st.id IN (?)', explode(",", $store_list) )
            ->group('st.id')
            ->order(array('st_status ASC', 'a.name ASC','st.id ASC'));

        //echo $select; die;
        $result = $db->fetchAll($select);
        return $result;

    }

}