<?php
class Application_Model_OppoPcTarget extends Zend_Db_Table_Abstract
{
    protected $_name = 'oppo_pc_target';

    function getListByPcStore($params) {

        $db = Zend_Registry::get('db');

        $sub_select = $db->select()
            ->from(array('ss2' => 'store_staff'), array(new Zend_Db_Expr('DISTINCT ss2.store_id')))
            ->join(array('s2' => 'staff'), 'ss2.staff_id = s2.id', array())
            ->where('ss2.is_leader = 1')
            ->where('s2.code = ?', $params['sale_code']);
/*
        $get = array(
            'st_id'         => 'st.id',
            'st_name'       => 'st.name',
            'pc_id'         => 's.id',
            'pc_code'       => 's.code',
            'pc_name'       => new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)"),
            'pc_stand_by'   => new Zend_Db_Expr("(CASE WHEN s.pc_stand_by = 1 THEN 'Yes' ELSE 'No' END)"),
            'opt_id'        => 'opt.id',
            'target'        => 'opt.target',
            'mt_id'         => 'mt.id',
        );
*/
        $get = array(
            'st_id'         => 'st.id',
            'st_code'       => 'st.store_code',
            'st_name'       => 'st.name',
            'pc_id'         => 's.id',
            'pc'            => new Zend_Db_Expr("COUNT(CASE WHEN s.pc_stand_by = 0 THEN s.id END)"),
            'pc_stand_by'   => new Zend_Db_Expr("COUNT(CASE WHEN s.pc_stand_by = 1 THEN s.id END)"),
            'opt_id'        => 'opt.id',
            'target'        => 'opt.target',
            'target_hero'   => 'opt.target_hero',
            'target_price'  => 'opt.target_price',
            'mt_id'         => 'mt.id',
            'area_id'       => 'rm.area_id',
            'asm_app_by'    => 'opt.asm_approve_by',
            'asm_app_at'    => 'opt.asm_approve_at',
            'rd_app_by'     => 'opt.rd_approve_by',
            'rd_app_at'     => 'opt.rd_approve_at',
            'pcml_app_by'   => 'opt.pcm_leader_approve_by',
            'pcml_app_at'   => 'opt.pcm_leader_approve_at',
        );

        $select = $db->select()
            ->from(array('st' => 'store'), $get)
            ->join(array('rm' => 'regional_market') , 'st.regional_market = rm.id'  , array())
            ->join(array('sm' => 'store_market')    , 'st.id = sm.store_id'         , array())
            ->join(array('mn' => 'market_name')     , 'sm.market_name_id = mn.id'   , array())
            ->join(array('mt' => 'market_type')     , 'mn.market_type_id = mt.id'   , array())
            ->joinLeft(array('ss' => 'store_staff') , 'st.id = ss.store_id AND ss.is_leader = 0', array())
            ->joinLeft(array('s'  => 'staff')       , 'ss.staff_id = s.id AND s.group_id = 4'   , array())
            ->joinLeft(array('opt'  => 'oppo_pc_target'), 
                "   st.id = opt.store_id 
                    AND opt.from_date >= '".$params['from']."' 
                    AND opt.to_date <= '".$params['to']."' 
                ", array())
            ->where('st.rank <> 3')
            ->where('st.status = 1')
            ->group('st.id')
            ->order(array('st.id ASC', 's.code ASC'));


        if ( isset($params['sale_code']) && $params['sale_code'] ) { 
            $select->where('st.id IN (?)', $sub_select);
        }


        if ( isset($params['asm']) && $params['asm'] ) {
            $QAsm = new Application_Model_Asm();
            $list_regions = $QAsm->get_cache($params['asm']);
            $list_regions = isset($list_regions['district']) && is_array($list_regions['district']) ? $list_regions['district'] : array();

            if (count($list_regions) > 0)
                $select->where( 'st.district IN (?)', $list_regions);
            else
                $select->where('1=0', 1);
        }

        
        //echo $select; die;
        $result = $db->fetchAll($select);

        return $result;
    }

    function getCurrentSaleTarget($params) {

        $db = Zend_Registry::get('db');

        $get = array(
            'sale_code'  => 's.code',
            'sale_name'  => new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)"),
            'target'     => new Zend_Db_Expr("COALESCE(ost.target, 0)"),
            'target_hero'=> new Zend_Db_Expr("COALESCE(ost.target_hero, 0)"),
        );

        $select = $db->select()
            ->from(array('ost' => 'oppo_sale_target'), $get)
            ->joinLeft(array('s' => 'staff'), 'ost.staff_id = s.id', array())
            ->where('ost.from_date >= ?', $params['from'])
            ->where('ost.to_date <= ?', $params['to'])
            ->where('s.code = ?', $params['sale_code']);
        
        //echo $select; die;
        $result = $db->fetchRow($select);
        return $result;
    }

    function getApproveList($area_id) {

        $db = Zend_Registry::get('db');

        $get = array(
            'staff_id'   => 's.id',
            'staff_code' => 's.code',
            'staff_name' => new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)"),
            'group_id'   => 'g.id',
            'group_name' => 'g.name',
        );

        $select = $db->select()
            ->from(array('a'   => 'area'), $get)
            ->join(array('rm'  => 'regional_market'), 'a.id = rm.area_id'   , array())
            ->join(array('asm' => 'asm'), "(CASE WHEN asm.type = 2 THEN a.id ELSE rm.id END) = asm.area_id", array())
            ->join(array('s'   => 'staff')          , 'asm.staff_id = s.id' , array())
            ->join(array('g'   => 'group')          , 's.group_id = g.id'   , array())
            ->where('a.id = ?', $area_id)
            ->where('s.off_date IS NULL', 1)
            //->where('s.group_id IN (?)', array(5,16,17,36,28,35))
            ->where('s.group_id IN (?)', array(5,16,28,35,36))
            ->group(array('asm.staff_id','asm.area_id'))
            ->order(array("FIND_IN_SET(g.id, '28,35,5,16,36,17')", 's.code ASC'));
        
        //echo $select; die;
        $result = $db->fetchAll($select);
        return $result;
    }

    function getStoreTarget($params) {

        $d = explode('/', $params['from']);
        $from = $d[2].'-'.$d[1].'-'.$d[0];

        $d = explode('/', $params['to']);
        $to = $d[2].'-'.$d[1].'-'.$d[0];

        $db = Zend_Registry::get('db');

        $get = array(
            'st_id'       => 'st.id',
            'st_name'     => 'st.name',
            'target'      => 'opt.target',
            'target_hero' => 'opt.target_hero',
        );

        $select = $db->select()
            ->from(array('st'  => 'store'), $get)
            ->joinLeft(array('opt'  => 'oppo_pc_target'), 'st.id = opt.store_id', array())
            ->where('st.id = ?', $params['store_id'])
            ->where('opt.from_date <= ?', $from)
            ->where('opt.to_date >= ?', $to);
        
        //echo $select; die;
        $result = $db->fetchRow($select);
        return $result;
    }

    function getStoreTargetByStoreSale($params) {

        $db = Zend_Registry::get('db');

        $get = array(
            'sales_id'      => 'ss.staff_id',
            'store_id'      => 'st.id',
            'target_price'  => 'opt.target_price',
        );

        $select = $db->select()
            ->from(array('st'  => 'store'), $get)
            ->join(array('ss'  => 'store_staff')    , 'st.id = ss.store_id'         , array())
            ->join(array('opt' => 'oppo_pc_target') , 'ss.store_id = opt.store_id'  , array())
            ->where('ss.staff_id = ?', $params['sales_id'])
            ->where('opt.from_date <= ?', $params['from'])
            ->where('opt.to_date >= ?', $params['to']);
        
        // echo $select; die;
        $result = $db->fetchAll($select);
        return $result;
    }

//check target
     function getSaleByArea($params) { 

        $db = Zend_Registry::get('db');

        $get = array(
            'area_id'   => 'a.id',
            'area_name' => 'a.name', 
            'id'        => new Zend_Db_Expr("(CASE WHEN s.id IS NULL THEN CONCAT(0,'|',a.id) ELSE s.id END)"),
            'name'      => new Zend_Db_Expr("(CASE WHEN s.id IS NULL THEN CONCAT(a.name,' | No Sale') ELSE CONCAT(a.name, ' | ', s.code,' | ', s.firstname, ' ', s.lastname) END)"),
        );

        $select = $db->select()
            ->from(array('st' => 'store'), $get)
            ->join(array('rm' => 'regional_market') , 'st.regional_market = rm.id'  , array())
            ->join(array('a'  => 'area')            , 'rm.area_id = a.id'           , array())
            ->joinLeft(array('ss' => 'store_staff') , 'st.id = ss.store_id AND ss.is_leader = 1', array())
            ->joinLeft(array('s'  => 'staff')       , 'ss.staff_id = s.id'          , array())
            ->group(array('s.id','a.id'))
            ->order(array('a.name ASC','s.id ASC'));

        // Check Province Permission
        if ( isset($params['province_id']) &&  $params['province_id'] ) {
            $select->where('rm.id IN (?)', $params['province_id']);
        } else {
            $select->where('a.id IN (?)', $params['area_id']);
        }

        // Filter Sale Name
        if ( isset($params['sale_id']) &&  $params['sale_id'] ) {

            $chk_string = '|';
            $arr_area = $arr_sale = array();
            foreach ($params['sale_id'] as $key => $value) {
                if ( strpos($value, $chk_string ) != false ) {
                    $tmp = explode("|", $value);
                    $arr_area[] = $tmp[1];
                } else {
                    $arr_sale[] = $value;
                }
            }

            if ( !empty($arr_area) ) {
                if ( !empty($arr_sale) ) {
                    $select->where('( s.id IN (?)', $arr_sale);
                    $select->orWhere('ss.id IS NULL', 1);
                    $select->where('a.id IN (?) )', $arr_area);
                } else {
                    $select->where('ss.id IS NULL', 1);
                    $select->where('a.id IN (?)', $arr_area);
                }
            } else {
                $select->where('s.id IN (?)', $params['sale_id']);
            }

        }
        
        $result = $db->fetchAll($select);

        return $result;

    }

     function getSaleInfo($sale_id) {

        $db = Zend_Registry::get('db');

        $get = array(
            'area_id'   => 'a.id',
            'area_name' => 'a.name',
            'staff_id'  => 's.id',
            'staff_code'=> 's.code',
            'staff_name'=> new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)"),
        );

        $select = $db->select()
            ->from(array('st' => 'store'), $get)
            // ->join(array('o'  => 'org')             , 'st.org_dealer = o.org_id'    , array())
            ->join(array('rm' => 'regional_market') , 'st.regional_market = rm.id'  , array())
            ->join(array('a'  => 'area')            , 'rm.area_id = a.id'           , array())
            ->join(array('ss' => 'store_staff')     , 'st.id = ss.store_id'         , array())
            ->join(array('s'  => 'staff')           , 'ss.staff_id = s.id'          , array())
            ->where('ss.is_leader = ?', 1)
            ->where('s.id = ?', $sale_id)
            ->order(array('s.code ASC'));

        // echo $select; die;
        $result = $db->fetchRow($select);

        return $result;
    }

function getLast3MonthBySaleStore($params, $sale_id, $area_id) {

        $db = Zend_Registry::get('db');

        // Range of Last 1 Month
        $tmp_start_01 = new DateTime( $params['from'] );
        $tmp_start_01->modify( 'first day of previous month' );
        $last_start_01 = $tmp_start_01->format( 'Y-m-d' );

        $tmp_end_01 = new DateTime( $params['from'] );
        $tmp_end_01->modify( 'last day of previous month' );
        $last_end_01 = $tmp_end_01->format( 'Y-m-d' );

        // Range of Last 2 Month
        $tmp_start_02 = new DateTime( $last_start_01 );
        $tmp_start_02->modify( 'first day of previous month' );
        $last_start_02 = $tmp_start_02->format( 'Y-m-d' );
       
        $tmp_end_02 = new DateTime( $last_end_01 );
        $tmp_end_02->modify( 'last day of previous month' );
        $last_end_02 = $tmp_end_02->format( 'Y-m-d' );

        // Range of Last 3 Month
        $tmp_start_03 = new DateTime( $last_start_02 );
        $tmp_start_03->modify( 'first day of previous month' );
        $last_start_03 = $tmp_start_03->format( 'Y-m-d' );
       
        $tmp_end_03 = new DateTime( $last_end_02 );
        $tmp_end_03->modify( 'last day of previous month' );
        $last_end_03 = $tmp_end_03->format( 'Y-m-d' );

        $sub_select_01 = $db->select()
            ->from(array('opt' => 'oppo_pc_target'), array('opt.target'))
            ->where('opt.from_date >= ?', $last_start_01)
            ->where('opt.to_date <= ?', $last_end_01)
            ->where('opt.store_id = ss.store_id');

        $sub_select_02 = $db->select()
         ->from(array('opt' => 'oppo_pc_target'), array('opt.target'))
            ->where('opt.from_date >= ?', $last_start_02)
            ->where('opt.to_date <= ?', $last_end_02)
            ->where('opt.store_id = ss.store_id');

        // Sellout of Last 1 Month
        $tmp_start_01 = new DateTime( $from );
        $tmp_start_01->modify( 'first day of previous month' );
        $last_start_01 = $tmp_start_01->format( 'Y-m-d' );
        $tmp_end_01 = new DateTime( $from );
        $tmp_end_01->modify( 'last day of previous month' );
        $last_end_01 = $tmp_end_01->format( 'Y-m-d' );

        $get = array(
            'store_id'     => 'st.id',
            'store_code'   => 'st.store_code',
            'store_name'   => 'st.name',
	        'oppo_id'      => 'st.oppo_id',

            'last_01'       => new Zend_Db_Expr('('.$sub_select_01.')'),
            'last_02'       => new Zend_Db_Expr('('.$sub_select_02.')'),

            'sellout'      => new Zend_Db_Expr("COUNT( CASE WHEN t.created_at >= '".$params['from']." 00:00:00' AND t.created_at <= '".$params['to']." 23:59:59' AND NOT g.cat_id=15 THEN ts.imei END )"),
            'sellout_last_01'   => new Zend_Db_Expr("COUNT( CASE WHEN t.created_at >= '".$last_start_01." 00:00:00' AND t.created_at <= '".$last_end_01." 23:59:59' AND NOT g.cat_id=15 THEN ts.imei END )"),
            'sellout_activate'  => new Zend_Db_Expr("COUNT(CASE WHEN t.created_at >= '".$params['from']." 00:00:00' AND t.created_at <= '".$params['to']." 23:59:59' AND NOT g.cat_id=15 AND i.activated_date IS NULL THEN i.imei_sn END )"),
            'hero_sellout' => new Zend_Db_Expr("COUNT(CASE WHEN t.created_at >= '".$params['from']." 00:00:00' AND t.created_at <= '".$params['to']." 23:59:59' AND NOT g.cat_id=15 AND g.hero_product = 1 THEN i.imei_sn END )"),
            'hero_activate' => new Zend_Db_Expr("COUNT(CASE WHEN t.created_at >= '".$params['from']." 00:00:00' AND t.created_at <= '".$params['to']." 23:59:59' AND NOT g.cat_id=15 AND g.hero_product = 1 AND i.activated_date IS NOT NULL THEN i.imei_sn END )"),
        );

        $select = $db->select()
            ->from(array('st' => 'store'), $get)
            ->joinLeft(array('rm' => 'regional_market') , 'st.regional_market = rm.id', array())
            ->joinLeft(array('ss' => 'store_staff') , 'st.id = ss.store_id AND ss.is_leader = 1', array())
            ->joinLeft(array('s'  => 'staff')       , 'ss.staff_id = s.id', array())
            ->joinLeft(array('t'  => 'timing')      , 
                "   st.id = t.store 
                    AND t.created_at >= '".$last_start_03." 00:00:00' 
                    AND t.created_at <= '".$params['to']." 23:59:59' 
                ", array())
            ->joinLeft(array('ts'  => 'timing_sale') , 't.id = ts.timing_id', array())
            ->joinLeft(array('opt' => 'oppo_pc_target'),'opt.store_id = st.store_id',array())
            ->joinLeft(array('i' => WAREHOUSE_DB.'.imei'),'i.imei_sn = ts.imei',array())
            ->joinLeft(array('g' => WAREHOUSE_DB.'.good'),'g.id = i.good_id',array())
            ->where('st.status = 1')
            ->group('st.id')
            ->order(array('st.id ASC'));

        if ( $sale_id == 0 ) {
            $select->where('ss.id IS NULL', 1);
            $select->where('rm.area_id = ?', $area_id);
        } else {
            $select->where('s.id = ?', $sale_id);
            $select->where('rm.area_id = ?', $area_id);
        }
        
        //echo $select; echo "<br/><br/>"; //die;
        $result = $db->fetchAll($select);

        return $result;
    }


     function TargetByStore($params) {

        $db = Zend_Registry::get('db');

        $get = array(
            'spt_id'        => 'spt.id',
            'store_id'      => 'spt.store_id',
            'target'  => 'spt.target',
            'target_hero'   => 'spt.target_hero',
        );

        $select = $db->select()
            ->from(array('spt' => 'oppo_pc_target'), $get)
            ->where('spt.from_date >= ?', $params['from'])
            ->where('spt.to_date <= ?', $params['to']);

        $result_raw = $db->fetchAll($select);
        $result = array();

        for ($i=0;$i<count($result_raw);$i++) {
            $result[ $result_raw[$i]['store_id'] ]['spt_id']        = $result_raw[$i]['spt_id'];
            $result[ $result_raw[$i]['store_id'] ]['target']        = $result_raw[$i]['target'];
            $result[ $result_raw[$i]['store_id'] ]['target_hero']   = $result_raw[$i]['target_hero'];
            $result[ $result_raw[$i]['store_id'] ]['target_price']  = $result_raw[$i]['target_price'];
        }

        return $result;
    }


}