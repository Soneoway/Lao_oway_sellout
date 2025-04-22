<?php
class Application_Model_OppoIndividualTarget extends Zend_Db_Table_Abstract
{
    protected $_name = 'oppo_individual_target';

    function getListByPcStore($params) {

        $db = Zend_Registry::get('db');

        $sub_select = $db->select()
            ->from(array('ss2' => 'store_staff'), array(new Zend_Db_Expr('DISTINCT ss2.store_id')))
            ->join(array('s2' => 'staff'), 'ss2.staff_id = s2.id', array())
            ->where('ss2.is_leader = 1')
            ->where('s2.id = ?', $params['sale_id']);

        // $sub_select_01 = $db->select()
        //     ->from(array('i' => WAREHOUSE_DB.'.imei'),array('Activate' => new Zend_Db_Expr("COUNT(i.imei_sn)")))
        //     ->join(array('tms' =>'timing_sale'),'tms.imei = i.imei_sn',array())
        //     ->join(array('tm' => 'timing'),'tm.id = tms.timing_id',array()) 
        //     ->where('tm.staff_id = oit.staff_id')
        //     ->where('tm.created_at >=?',$params['from'])
        //     ->where('tm.created_at <=?',$params['to'])
        //     ->where('i.activated_date IS NOT NULL');

        $get = array(
            'pc_id'         => 's.id',
            'pc_code'       => 's.code',
            'pc_name'       => new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)"),
            'pc_stand_by'   => new Zend_Db_Expr("(CASE WHEN s.pc_stand_by = 1 THEN 'Yes' ELSE 'No' END)"),

            'oit_id'        => 'oit.id',
            'target'        => 'oit.target',
            'target_hero'   => 'oit.target_hero',
            'target_price'  => 'oit.target_price',

            'sellout'       => new Zend_Db_Expr("COUNT(ts.imei)"),
            'sellout_price' => new Zend_Db_Expr("COALESCE(SUM(gkl.price),0)"),
            'Activate'      => new Zend_Db_Expr("COUNT(CASE WHEN im.activated_date IS NOT NULL THEN im.imei_sn END)"),
            'hero_product_sellout'   => new Zend_Db_Expr("COUNT(CASE WHEN g.hero_product = 1 THEN ts.imei END)"),
            'hero_product_activate' => new Zend_Db_Expr("COUNT(CASE WHEN im.activated_date IS NOT NULL AND g.hero_product = 1 THEN im.imei_sn END)"),

            'area_id'       => 'rm.area_id',
            'asm_app_by'    => 'oit.asm_approve_by',
            'asm_app_at'    => 'oit.asm_approve_at',
            'rd_app_by'     => 'oit.rd_approve_by',
            'rd_app_at'     => 'oit.rd_approve_at',
            'pcml_app_by'   => 'oit.pcm_leader_approve_by',
            'pcml_app_at'   => 'oit.pcm_leader_approve_at',
        );

        $select = $db->select()
            ->from(array('st' => 'store'), $get)
            ->join(array('rm' => 'regional_market') , 'st.regional_market = rm.id'              , array())
            ->join(array('ss' => 'store_staff')     , 'st.id = ss.store_id AND ss.is_leader = 0', array())
            ->join(array('s'  => 'staff')           , 'ss.staff_id = s.id AND s.group_id = 4'   , array())
            ->joinLeft(array('oit'  => 'oppo_individual_target'), 
                "   s.id = oit.staff_id 
                    AND oit.from_date >= '".$params['from']."' 
                    AND oit.to_date <= '".$params['to']."' 
                ", array())
            ->joinLeft(array('t' => 'timing'), 
                "   s.id = t.staff_id 
                    AND st.id = t.store 
                    AND t.created_at >= '".$params['from']." 00:00:00' 
                    AND t.created_at <= '".$params['to']." 23:59:59' 
                ", array())
            ->joinLeft(array('ts'=> 'timing_sale')  , 't.id = ts.timing_id', array())
            ->joinLeft(array('g' => WAREHOUSE_DB.'.good'),'g.id = ts.product_id',array())
            ->joinLeft(array('im' => WAREHOUSE_DB.'.imei'),'im.imei_sn = ts.imei',array())
            ->joinLeft(array('gkl' => 'good_kpi_log'), 
                "   gkl.good_id = ts.product_id 
                    AND gkl.color_id = ts.model_id 
                    AND t.created_at >= CONCAT(gkl.from_date,' 00:00:00') 
                    AND t.created_at <= CONCAT(gkl.to_date,' 23:59:59')
                ", array())

            // ->where('st.rank <> 3')
            // ->where('st.del IS NULL')
            ->group('s.id')
            ->order(array('s.code ASC'));

        if ( isset($params['sale_id']) && $params['sale_id'] ) { 
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
        
        // echo $select; die;
        $result = $db->fetchAll($select);

        return $result;
    }

    function getLast3MonthByStore($params, $pc_id) {

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

        $get = array(
            'store_id'     => 'st.id',
            'store_name'   => 'st.name',

            'last_03'      => new Zend_Db_Expr("COALESCE( SUM( CASE WHEN oit.from_date >= '".$last_start_03." 00:00:00' AND oit.to_date <= '".$last_end_03." 23:59:59' THEN oit.target_price END ), 0)"),
            'last_02'      => new Zend_Db_Expr("COALESCE( SUM( CASE WHEN oit.from_date >= '".$last_start_02." 00:00:00' AND oit.to_date <= '".$last_end_02." 23:59:59' THEN oit.target_price END ), 0)"),
            'last_01'      => new Zend_Db_Expr("COALESCE( SUM( CASE WHEN oit.from_date >= '".$last_start_01." 00:00:00' AND oit.to_date <= '".$last_end_01." 23:59:59' THEN oit.target_price END ), 0)"),

            // 'sellout'      => new Zend_Db_Expr("COUNT( CASE WHEN t.created_at >= '".$params['from']." 00:00:00' AND t.created_at <= '".$params['to']." 23:59:59' THEN ts.imei END )"),
            // 'total_price'  => new Zend_Db_Expr("COALESCE( SUM( CASE WHEN t.created_at >= '".$params['from']." 00:00:00' AND t.created_at <= '".$params['to']." 23:59:59' THEN gkl.price END ), 0)"),
        );

        $select = $db->select()
            ->from(array('st' => 'store'), $get)
            // ->join(array('rm' => 'regional_market') , 'st.regional_market = rm.id', array())
            ->joinLeft(array('ss' => 'store_staff') , 'st.id = ss.store_id AND ss.is_leader = 0', array())
            ->joinLeft(array('s'  => 'staff')       , 'ss.staff_id = s.id', array())
            // ->joinLeft(array('t'  => 'timing')      , 
            //     "   st.id = t.store 
            //         AND t.created_at >= '".$last_start_03." 00:00:00' 
            //         AND t.created_at <= '".$params['to']." 23:59:59' 
            //     ", array())
            // ->joinLeft(array('ts'  => 'timing_sale') , 't.id = ts.timing_id', array())
            // ->joinLeft(array('gkl' => 'good_kpi_log'), 
            //     "   gkl.good_id = ts.product_id 
            //         AND gkl.color_id = ts.model_id 
            //         AND t.created_at >= CONCAT(gkl.from_date,' 00:00:00') 
            //         AND t.created_at <= CONCAT(gkl.to_date,' 23:59:59')
            //     ", array())
            ->joinLeft(array('stl' => 'store_staff_log'),'stl.store_id = st.id',array())
            ->joinLeft(array('oit' => 'oppo_individual_target'),'oit.staff_id = stl.staff_id',array())
            ->where('s.id = ?', $pc_id)
            ->group('st.id')
            ->order(array('st.id ASC'));
        
        // echo $select; echo "<br/><br/>"; //die;
        $result = $db->fetchAll($select);

        return $result;
    }

    public function getAchieveRate($score) {

        if ($score >= 100) { return 1.2; }
        if ($score >= 80) { return 1; }
        if ($score >= 60) { return 0.8; }
        return 0;
    
    }

}