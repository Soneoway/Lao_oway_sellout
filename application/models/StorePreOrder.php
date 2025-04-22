<?php
class Application_Model_StorePreOrder extends Zend_Db_Table_Abstract
{
	protected $_name = 'store_pre_order';

    function fetchPagination($page, $limit, &$total, $params) {

        $d = explode('/', $params['from']);
        $from = $d[2].'-'.$d[1].'-'.$d[0];

        $d = explode('/', $params['to']);
        $to = $d[2].'-'.$d[1].'-'.$d[0];

        $db = Zend_Registry::get('db');

        if ( (isset($params['export']) and $params['export']) || (isset($params['get_total_count']) and $params['get_total_count'] == 0) ) {
            $get_01['sp_id'] = 'sp.id';
        } else {
            $get_01['sp_id'] = new Zend_Db_Expr('SQL_CALC_FOUND_ROWS sp.id');
        }

        $get_02 = array(
        	'area_id'	=> 'a.id',
            'area_name' => new Zend_Db_Expr(
                "   (CASE 
                        WHEN a.name like 'BKK-E1%' THEN 'BKK East-1'
                        WHEN a.name like 'BKK-E2%' THEN 'BKK East-2'
                        WHEN a.name like 'BKK-E3%' THEN 'BKK East-3'
                        WHEN a.name like 'BKK-E4%' THEN 'BKK East-4'
                        WHEN a.name like 'BKK-E5%' THEN 'BKK East-5'
                        WHEN a.name like 'BKK-W1%' THEN 'BKK West-1'
                        WHEN a.name like 'BKK-W2%' THEN 'BKK West-2'
                        WHEN a.name like 'BKK-W3%' THEN 'BKK West-3'
                        ELSE a.name 
                    END)
                "),

            'st_id' 	=> 'st.id',
            'st_name' 	=> 'st.name',
            'st_type'   => 'o.org_name',
            'st_status' => new Zend_Db_Expr("(CASE WHEN st.del = 1 THEN 'Disabled' ELSE 'Active' END)"),
            'good_name' => new Zend_Db_Expr("CONCAT('[',g.name,'] ', g.desc)"),
            'unit'  	=> 'sp.unit',
            'sellout'   => new Zend_Db_Expr("COUNT(ts.imei)"),
            'sellout_pre' => new Zend_Db_Expr("COUNT(CASE WHEN ts.pre_order_status = 1 THEN ts.imei END)"),
            'pre_order_type' => 'sp.pre_order_type',
        );

        $get = $get_01 + $get_02;

        $select = $db->select()
            ->from(array('sp' => 'store_pre_order'), $get)
            ->join(array('st' => 'store')				, 'sp.store_id = st.id'			, array())
            ->join(array('o'  => 'org')                 , 'st.org_dealer = o.org_id'    , array())
            ->join(array('rm' => 'regional_market')		, 'st.regional_market = rm.id'	, array())
            ->join(array('a'  => 'area')				, 'rm.area_id = a.id'			, array())
            ->join(array('g'  => WAREHOUSE_DB.'.good')	, 'sp.product_id = g.id'		, array())
            ->joinLeft(array('t'  => 'timing')          , 
                "   st.id = t.store 
                    AND t.created_at >= '".$from." 00:00:00' 
                    AND t.created_at <= '".$to." 23:59:59' 
                ", array())
            ->joinLeft(array('ts' => 'timing_sale')     , 
                "   t.id = ts.timing_id 
                    AND g.id = ts.product_id 
                ", array())
            ->group('sp.id')
            ->order(array('a.name ASC','st.id ASC'));

        // Add Filter Store ID
        if ( isset($params['st_id']) && $params['st_id'] ) {
            $select->where('st.id = ?', $params['st_id']);
        }

        // Add Filter Store Name
        if ( isset($params['st_name']) && $params['st_name'] ) {
            $select->where('st.name LIKE ?', "%".$params['st_name']."%");
        }

        // Add Filter Area
        if (isset($params['area_id']) && $params['area_id']) {

            $select->where('(a.id IN (?)', $params['area_id']);
            $select->orWhere(
                "(  CASE 
                        WHEN a.name like 'BKK-E1%' THEN 'BKK East-1'
                        WHEN a.name like 'BKK-E2%' THEN 'BKK East-2'
                        WHEN a.name like 'BKK-E3%' THEN 'BKK East-3'
                        WHEN a.name like 'BKK-E4%' THEN 'BKK East-4'
                        WHEN a.name like 'BKK-E5%' THEN 'BKK East-5'
                        WHEN a.name like 'BKK-W1%' THEN 'BKK West-1'
                        WHEN a.name like 'BKK-W2%' THEN 'BKK West-2'
                        WHEN a.name like 'BKK-W3%' THEN 'BKK West-3'
                        ELSE a.name 
                    END
                ) IN (?))", $params['area_id']);
        }

        // Add Filter Product 
        if (isset($params['good_id']) && $params['good_id']) {
            if (is_array($params['good_id']) && count($params['good_id']))
                $select->where('g.id IN (?)', $params['good_id']);
            elseif (is_numeric($params['area_id']))
                $select->where('g.id = ?', intval($params['good_id']));
            else
                $select->where('1=0', 1);
        }

        // check ermission ASM, ASM Stand by, Sale Admin, Traning
        if ( isset($params['asm']) && $params['asm'] ) {
            $QAsm = new Application_Model_Asm();
            $list_regions = $QAsm->get_cache($params['asm']);
            $list_regions = isset($list_regions['district']) && is_array($list_regions['district']) ? $list_regions['district'] : array();

            if (count($list_regions) > 0)
                $select->where( 'st.district IN (?)', $list_regions);
            else
                $select->where('1=0', 1);
        }

        // Get Total 
        if (isset($params['get_total_count']) && $params['get_total_count'] == 0) { 
            $select_total = $db->select()
                ->from(array('pa' => $select), array( 'total_unit' => new Zend_Db_Expr("SUM(pa.unit)") ));

            //echo $select_total; die;
            return $db->fetchRow($select_total);
        }

        if ($limit)
            $select->limitPage($page, $limit);

        // echo $select; die;
        $result = $db->fetchAll($select);

        if ($limit)
            $total = $db->fetchOne("select FOUND_ROWS()");

        return $result;

    }
}
