<?php
class Application_Model_StoreStaff extends Zend_Db_Table_Abstract
{
	protected $_name = 'store_staff';

    function getStore($staff_id)
    {
        $db = Zend_Registry::get('db');
        $select = $db->select()
        ->from(array('p' => $this->_name),
            array(new Zend_Db_Expr('SQL_CALC_FOUND_ROWS p.id'), 'p.*', 'pid' => 'p.id', ));


        if (isset($staff_id) && $staff_id) {
            $select->where('p.staff_id = ?', $staff_id);
        }

        $result = $db->fetchRow($select);

        return $result['store_id'];
    }

    function getStock($store_id){
        $db = Zend_Registry::get('db');

        $select = $db->select()
        ->from(array('p' => $this->_name),
            array(new Zend_Db_Expr('SQL_CALC_FOUND_ROWS p.id'), 'p.*', 'pid' => 'p.id' ));

        $select->joinLeft(array('s' => 'staff'), 'p.staff_id = s.id', array('stock_code' => 's.code', 'stock_name' => 's.firstname', 'pcm_name' => new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)")));

        if (isset($store_id) && $store_id) {
            $select->where('p.store_id = ?', $store_id);
            $select->where('p.is_leader = 3');
        }

        $result = $db->fetchAll($select);

        return $result;
    }

   function getPC($store_id, $status = null) {

        $db = Zend_Registry::get('db');

        $select = $db->select()
        ->from(array('p' => $this->_name),
            array(new Zend_Db_Expr('SQL_CALC_FOUND_ROWS p.id'), 'p.*', 'pid' => 'p.id' ));

        $select->joinLeft(array('s' => 'staff'), 'p.staff_id = s.id', array('pc_name' => new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)"), 'pc_code' => 's.code', 'pc_phone' => 's.phone_number' ));
        $select->joinLeft(array('g' => 'group'), 's.group_id = g.id', array('group_name' => 'g.name' ));

        if (isset($store_id) && $store_id) {
            $select->where('p.store_id = ?', $store_id);
            $select->where('p.is_leader = 0');
        }

        if (isset($status)) { 
            $select->where('s.pc_stand_by = ?', $status);
        }

        $result = $db->fetchAll($select);

        return $result;
    }

    function getPCBySale($sales_id, $status = null) {

        $db = Zend_Registry::get('db');

        $get = array(
            'pc_code'   => 's.code', 
            'pc_name'   => new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)"),
        );

        $select = $db->select()
        ->from(array('s'   => 'staff'), $get)
        ->join(array('ss'  => 'store_staff')    , 's.id = ss.staff_id AND ss.is_leader = 1'     , array())
        ->join(array('ss2' => 'store_staff')    , 'ss.store_id = ss2.store_id'                  , array())
        ->join(array('s2'  => 'staff')          , 'ss2.staff_id = s2.id AND ss2.is_leader = 0'  , array())
        ->where('s.id = ?', $sales_id);

        if (isset($status)) { 
            $select->where('s2.pc_stand_by = ?', $status);
        } 

        // echo $select;
        $result = $db->fetchAll($select);
        return $result;
    }

    function getSale($store_id) {

        $db = Zend_Registry::get('db');

        $select = $db->select()
        ->from(array('p' => $this->_name),
            array(new Zend_Db_Expr('SQL_CALC_FOUND_ROWS p.id'), 'p.*', 'pid' => 'p.id' ));

        $select->joinLeft(array('s' => 'staff'), 'p.staff_id = s.id', array('sale_name' => new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)"), 'sale_code' => 's.code' ));
        $select->joinLeft(array('g' => 'group'), 's.group_id = g.id', array('group_name' => 'g.name' ));

        if (isset($store_id) && $store_id) {
            $select->where('p.store_id = ?', $store_id);
            $select->where('p.is_leader = 1');
        }

        $result = $db->fetchRow($select);

        return $result;
    }

    function getPCM($store_id) {

        $db = Zend_Registry::get('db');

        $select = $db->select()
        ->from(array('p' => $this->_name),
            array(new Zend_Db_Expr('SQL_CALC_FOUND_ROWS p.id'), 'p.*', 'pid' => 'p.id' ));

        $select->joinLeft(array('s' => 'staff'), 'p.staff_id = s.id', array('pcm_code' => 's.code', 'pcm_fname' => 's.firstname', 'pcm_name' => new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)")));

        if (isset($store_id) && $store_id) {
            $select->where('p.store_id = ?', $store_id);
            $select->where('p.is_leader = 2');
        }

        $result = $db->fetchAll($select);

        return $result;

    }
    function getBM($store_id) {

        $db = Zend_Registry::get('db');

        $select = $db->select()
        ->from(array('p' => $this->_name),
            array(new Zend_Db_Expr('SQL_CALC_FOUND_ROWS p.id'), 'p.*', 'pid' => 'p.id' ));

        $select->joinLeft(array('s' => 'staff'), 'p.staff_id = s.id', array('sale_name' => new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)"), 'sale_code' => 's.code' ));
        $select->joinLeft(array('g' => 'group'), 's.group_id = g.id', array('group_name' => 'g.name' ));

        if (isset($store_id) && $store_id) {
            $select->where('p.store_id = ?', $store_id);
            $select->where('p.is_leader = 3');
        }

        $result = $db->fetchAll($select);

        return $result;
    }
    
    function getStoreList($params) {

        $db = Zend_Registry::get('db');

        $select = $db->select()
        ->from(array('ss' => 'store_staff'), array())
        ->join(array('st' => 'store'), 'ss.store_id = st.id', array('store_id' => 'st.id', 'store_code' => 'st.store_code', 'store_name' => 'st.name'))
        ->joinLeft(array('d' => WAREHOUSE_DB.".distributor"), 'st.d_id = d.id', array('d_id' => 'd.id', 'd_code' => 'd.distributor_code', 'd_name' => 'd.title'))
        ->where('ss.staff_id = ?', $params['staff_id'])
        ->where('st.status = 1')
        ->order('st.name ASC');

        if ( isset($params['group_id']) && $params['group_id'] ) {

            if (isset($params['from']) && $params['from']) {

                $start = $params['from'];
                $end = $params['to'];

            } else {

                $start = date('Y-m-01');
                $end = date('Y-m-t');

            }
            

            $next_start = date('Y-m-01', strtotime("+1 Month", strtotime($start)));
            $next_end = date('Y-m-t', strtotime("+1 Month", strtotime($start)));
            
            $last_start = date('Y-m-01', strtotime("-1 Month", strtotime($start)));
            $last_end = date('Y-m-t', strtotime("-1 Month", strtotime($start)));

            switch ($params['group_id']) {

                case PGPB_ID:

                    // Target Next Month 
                $sub_select_01 = $db->select()
                ->from(array('opt2' => 'oppo_pc_target'), array('opt2.target','opt2.target_hero', 'opt2.store_id'))
                ->where('opt2.from_date >= ?', $next_start)
                ->where('opt2.to_date <= ?', $next_end);

                $select->joinLeft(array('opt' => 'oppo_pc_target'), 
                    "   ss.store_id = opt.store_id 
                    AND opt.from_date >= '".$start."'
                    AND opt.to_date <= '".$end."'
                    ", array(
                        'pc_target' => 'opt.target', 
                        'pc_target_hero' => 'opt.target_hero', 
                        'pc_target_price' => 'opt.target_price'
                    ));

                $select->joinLeft(array('AAA' => $sub_select_01), 
                    "   AAA.store_id = ss.store_id 
                    ", array(
                        'pc_target_next'        => new Zend_Db_Expr("AAA.target"),
                        'pc_target_hero_next'   => new Zend_Db_Expr("AAA.target_hero"),
                    ));

                $select->where('ss.is_leader = 0');

                break;

                case PCDB_ID:

                  // Target Next Month 
                $sub_select_01 = $db->select()
                ->from(array('opt2' => 'oppo_pc_target'), array('opt2.target','opt2.target_hero', 'opt2.store_id'))
                ->where('opt2.from_date >= ?', $next_start)
                ->where('opt2.to_date <= ?', $next_end);

                $select->joinLeft(array('opt' => 'oppo_pc_target'), 
                    "   ss.store_id = opt.store_id 
                    AND opt.from_date >= '".$start."'
                    AND opt.to_date <= '".$end."'
                    ", array(
                        'pcdb_target' => 'opt.target', 
                        'pcdb_target_hero' => 'opt.target_hero', 
                        'pc_target_price' => 'opt.target_price'
                    ));

                $select->joinLeft(array('AAA' => $sub_select_01), 
                    "   AAA.store_id = ss.store_id 
                    ", array(
                        'pc_target_next'        => new Zend_Db_Expr("AAA.target"),
                        'pc_target_hero_next'   => new Zend_Db_Expr("AAA.target_hero"),
                    ));

                    // Target Last Month 
                $select->joinLeft(array('opt2' => 'oppo_pc_target'),"
                    opt2.store_id = st.id  
                    AND opt2.from_date >= '".$last_start."' 
                    AND opt2.to_date <= '".$last_end."'
                    ",array(
                        'pcdb_target_last' => 'opt2.target',
                        'pcdb_target_hero_last' => 'opt2.target_hero'
                    ));

                $select->where('ss.is_leader = 7');

                break;

                case RM_ID:
                case ASM_ID:
                case ASMSTANDBY_ID:
                case SALES_ID:

                    // Sale Target Next Month 
                $sub_select_01 = $db->select()
                ->from(array('ost2' => 'oppo_sale_target'), array('ost2.target','ost2.target_hero', 'ost2.staff_id', 'ost2.area_id'))
                ->where('ost2.from_date >= ?', $next_start)
                ->where('ost2.to_date <= ?', $next_end);

                    // PC Target Next Month 
                $sub_select_02 = $db->select()
                ->from(array('opt2' => 'oppo_pc_target'), array('opt2.target','opt2.target_hero', 'opt2.store_id'))
                ->where('opt2.from_date >= ?', $next_start)
                ->where('opt2.to_date <= ?', $next_end);

                $select->join(array('rm' => 'regional_market'), 'st.regional_market = rm.id', array('area_id' => 'rm.area_id'));

                    // Last Month Sale Target
                $select->joinLeft(array('ost' => 'oppo_sale_target'), "  ss.staff_id = ost.staff_id AND ost.from_date >= '".$start."' AND ost.to_date <= '".$end."'
                    ", array(
                        'sale_target' => new Zend_Db_Expr("SUM(ost.target)"), 
                        'sale_target_hero' =>  new Zend_Db_Expr("SUM(ost.target_hero)"),
                    ));

                    // Last Month Sale Target
                // $select->joinLeft(array('ost3' => 'oppo_sale_target'), "  ss.staff_id = ost3.staff_id AND ost3.from_date <= '".$last_start."' AND ost3.to_date >= '".$last_end."'
                //     ", array(
                //         'last_sale_target' => new Zend_Db_Expr("SUM(ost3.target)"), 
                //         'last_sale_target_hero' =>  new Zend_Db_Expr("SUM(ost3.target_hero)"),
                //     ));

                $select->joinLeft(array('opt' => 'oppo_pc_target'), 
                    "   ss.store_id = opt.store_id 
                    AND opt.from_date >= '".$start."'
                    AND opt.to_date <= '".$end."'
                    ", array(
                        'pc_target' => new Zend_Db_Expr("opt.target"),
                        'pc_target_hero' => new Zend_Db_Expr("opt.target_hero"),
                    ));

                $select->joinLeft(array('AAA' => $sub_select_01), 
                    "   AAA.area_id = rm.area_id 
                    AND AAA.staff_id = ss.staff_id
                    ", array(
                        'sale_target_next'      => 'AAA.target', 
                        'sale_target_hero_next' => 'AAA.target_hero'
                    ));

                $select->joinLeft(array('BBB' => $sub_select_02), 
                    "   BBB.store_id = ss.store_id 
                    ", array(
                        'pc_target_next'        => new Zend_Db_Expr("SUM(BBB.target)"),
                        'pc_target_hero_next'   => new Zend_Db_Expr("SUM(BBB.target_hero)"),
                    ));


                $select->where('ss.is_leader = 1');
                $select->group('st.id');

                break;

                default:
                break;
            }

        }

        //echo $select; die;
        $result = $db->fetchAll($select);
        return $result;
    }

    function getStoreListByPC($params) {

        $db = Zend_Registry::get('db');

        $select = $db->select()
        ->from(array('ss' => 'store_staff'), array())
        ->join(array('st' => 'store'), 'ss.store_id = st.id', array('store_id' => 'st.id', 'store_code' => 'st.store_code', 'store_name' => 'st.name'))
        ->joinLeft(array('d' => WAREHOUSE_DB.".distributor"), 'st.d_id = d.id', array('d_id' => 'd.id', 'd_code' => 'd.distributor_code', 'd_name' => 'd.title'))
        ->where('ss.staff_id = ?', $params['staff_id'])
        ->order('st.name ASC');

        if ( isset($params['group_id']) && $params['group_id'] ) {

            $start = date('Y-m-01');
            $end = date('Y-m-t');
            $last_start = date('Y-m-01', strtotime("-1 Month", strtotime($start)));
            $last_end = date('Y-m-t', strtotime("-1 Month", strtotime($start)));

            // PC Target Next Month 

            $select->joinLeft(array('oit' => 'oppo_individual_target'), 
                "   ss.staff_id = oit.staff_id 
                AND oit.from_date >= '".$start."'
                AND oit.to_date <= '".$end."'
                ", array('pc_target' => 'oit.target', 'pc_target_hero' => 'oit.target_hero'));

            $select->joinLeft(array('opt' => 'oppo_pc_target'),"
                opt.store_id = st.id  
                AND opt.from_date >= '".$start."' 
                AND opt.to_date <= '".$end."'
                ",array('Target_Store_PC' => 'opt.target', 'Hero_Store_PC' => 'opt.target_hero'));

            $select->joinLeft(array('opt2' => 'oppo_pc_target'),"
                opt2.store_id = st.id  
                AND opt2.from_date >= '".$last_start."' 
                AND opt2.to_date <= '".$last_end."'
                ",array('Target_Store_PC2' => 'opt2.target', 'Hero_Store_PC2' => 'opt2.target_hero'));

            $select->joinLeft(array('stl' => 'store_staff_log'),'stl.store_id = opt.store_id',array('pc' => new Zend_Db_Expr("COUNT(stl.id)")));
            $select->where('stl.is_leader = 0');
            $select->where('stl.released_at IS NULL');

            $select->where('ss.is_leader = 0');

        }

        // echo $select; die;
        $result = $db->fetchAll($select);
        return $result;
    }

    function getPCList($params) {

        $db = Zend_Registry::get('db');

        $start = date('Y-m-01');
        $end = date('Y-m-t');

        $sub_select = $db->select()
        ->from(array('ss2' => 'store_staff'), array('st_id'=>'ss2.store_id'))
        ->where('ss2.staff_id = ?', $params['staff_id']);

        $get = array(
            'staff_id'      => 's.id',
            'staff_code'    => 's.code',
            'staff_name'    => new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)"),
            'sellout'       => new Zend_Db_Expr("COUNT(ts.imei)"),
            'pc_stand_by'   => new Zend_Db_Expr("(CASE WHEN s.pc_stand_by = 1 THEN 'Yes' ELSE 'No' END)"),

            'pc_target'     => 'oit.target',
            'pc_target_hero'=> 'oit.target_hero',
        );

        $select = $db->select()
        ->from(array('ss' => 'store_staff'), $get)
        ->join(array('st' => 'store'), 'ss.store_id = st.id', array())
        ->join(array('s'  => 'staff'), 'ss.staff_id = s.id AND ss.is_leader = 0', array())
        ->joinLeft(array('t' => 'timing'), 
            "   t.staff_id = s.id 
            AND t.created_at >= '".$start." 00:00:00' 
            AND t.created_at <= '".$end." 23:59:59' 
            ", array())
        ->joinLeft(array('ts'=> 'timing_sale'), 't.id = ts.timing_id' , array())
        ->joinLeft(array('oit' => 'oppo_individual_target'), 
            "   ss.staff_id = oit.staff_id 
            AND oit.from_date >= '".$start."'
            AND oit.to_date <= '".$end."'
            ", array())
        ->where('ss.store_id IN (?)', $sub_select)
        ->group('s.id')
        ->order('s.code ASC');

        // echo $select; die;
        $result = $db->fetchAll($select);
        return $result;
    }

    function getPCDBList($params){
        $db = Zend_Registry::get('db');

        $start = date('Y-m-01');
        $end = date('Y-m-t');

        $sub_select = $db->select()
        ->from(array('ss2' => 'store_staff'), array('st_id'=>'ss2.store_id'))
        ->where('ss2.staff_id = ?', $params['staff_id']);

        $get = array(
            'staff_id'      => 's.id',
            'staff_code'    => 's.code',
            'staff_name'    => new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)"),
            'sellout'       => new Zend_Db_Expr("COUNT(ts.imei)"),

            'pcdb_target'     => 'oit.target',
            'pcdb_target_hero'=> 'oit.target_hero',
        );

        $select = $db->select()
        ->from(array('ss' => 'store_staff'), $get)
        ->join(array('st' => 'store'), 'ss.store_id = st.id', array())
        ->join(array('s'  => 'staff'), 'ss.staff_id = s.id AND ss.is_leader = 7', array())
        ->joinLeft(array('t' => 'timing'), 
            "   t.staff_id = s.id 
            AND t.created_at >= '".$start." 00:00:00' 
            AND t.created_at <= '".$end." 23:59:59' 
            ", array())
        ->joinLeft(array('ts'=> 'timing_sale'), 't.id = ts.timing_id' , array())
        ->joinLeft(array('oit' => 'oppo_individual_target'), 
            "   ss.staff_id = oit.staff_id 
            AND oit.from_date >= '".$start."'
            AND oit.to_date <= '".$end."'
            ", array())
        ->where('ss.store_id IN (?)', $sub_select)
        ->group('s.id')
        ->order('s.code ASC');

        // echo $select; die;
        $result = $db->fetchAll($select);
        return $result;
    }

    function getPCByArea($area_id = null, $params = null, $type = null) {

        $db = Zend_Registry::get('db');

        $get = array(
            'area_name'     => 'a.name',
            'staff_code'    => 's.code',
            'staff_name'    => new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)"),
            'staff_group'   => 'g.name',
            'pc_stand_by'   => new Zend_Db_Expr("CASE WHEN s.pc_stand_by = 1 THEN 'Yes' ELSE 'No' END"),
            'staff_created_at' => 's.created_at',
            'staff_off_date'=> 's.off_date',
        );

        if ( !is_null($type) && $type == 1) { $table = 'store_staff_log'; } 
        else { $table = 'store_staff'; }

        $select = $db->select()
        ->from(array('st' => 'store'), $get)
        ->join(array('rm' => 'regional_market') ,   'st.regional_market = rm.id', array())
        ->join(array('a'  => 'area')            ,   'rm.area_id = a.id'         , array())
        ->join(array('ss' => $table)            ,   'st.id = ss.store_id'       , array())
        ->join(array('s'  => 'staff')           ,   'ss.staff_id = s.id'        , array())
        ->join(array('g'  => 'group')           ,   's.group_id = g.id'         , array())
        ->where('ss.is_leader = 0')
        ->group(array('a.id','s.id'))
        ->order(array('a.name ASC', 's.code ASC'));

        if (isset($area_id) && $area_id) {
            $select->where('a.id IN (?)', $area_id);
        } 

        if ( !is_null($type) ) {

            $d = explode('/', $params['from']);
            $from = $d[2].'-'.$d[1].'-'.$d[0];

            $d = explode('/', $params['to']);
            $to = $d[2].'-'.$d[1].'-'.$d[0]; 

            // New PC Condition
            if ($type == 0) {
                $select->where('s.created_at >= ?', $from." 00:00:00");
                $select->where('s.created_at <= ?', $to." 23:59:59");
            }

            // Quit PC Condition
            if ($type == 1) {
                $select->where('s.off_date >= ?', $from);
                $select->where('s.off_date <= ?', $to);
            }

            
        }

        //echo $select; die;
        $result = $db->fetchAll($select);

        return $result;
    }

    function getSaleByArea($area_id = null) {

        $db = Zend_Registry::get('db');

        $get = array(
            'area_name'     => 'a.name',
            'staff_code'    => 's.code',
            'staff_name'    => new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)"),
            'staff_group'   => 'g.name',
        );

        $select = $db->select()
        ->from(array('ss' => 'store_staff'), $get)
        ->join(array('st' => 'store')           ,   'ss.store_id = st.id'       , array())
        ->join(array('rm' => 'regional_market') ,   'st.regional_market = rm.id', array())
        ->join(array('a'  => 'area')            ,   'rm.area_id = a.id'         , array())
        ->join(array('s'  => 'staff')           ,   'ss.staff_id = s.id'        , array())
        ->join(array('g'  => 'group')           ,   's.group_id = g.id'         , array())
        ->where('ss.is_leader = 1')
        ->where('s.off_date IS NULL')
        ->where('s.group_id <> 27')
        ->group(array('a.id','s.id'))
        ->order(array('a.name ASC', 's.code ASC'));

        if (isset($area_id) && $area_id) {
            $select->where('a.id IN (?)', $area_id);
        }

        //echo $select; die;
        $result = $db->fetchAll($select);

        return $result;
    }

    function getPCShopLevelByArea($params) {

        $db = Zend_Registry::get('db');

        $get = array(
            'region' => new Zend_Db_Expr(
                "   (CASE 
                WHEN a.name LIKE 'BKK-E1%'  THEN 'East-1' 
                WHEN a.name LIKE 'BKK-E2%'  THEN 'East-2' 
                WHEN a.name LIKE 'BKK-E3%'  THEN 'East-3' 
                WHEN a.name LIKE 'BKK-E4%'  THEN 'East-4' 
                WHEN a.name LIKE 'BKK-E5%'  THEN 'East-5' 
                WHEN a.name LIKE 'BKK-W1%'  THEN 'West-1' 
                WHEN a.name LIKE 'BKK-W2%'  THEN 'West-2' 
                WHEN a.name LIKE 'BKK-W3%'  THEN 'West-3' 
                WHEN a.id IN (18,41,66)     THEN 'U-South' 
                WHEN a.id IN (40,44,62,63)  THEN 'U-East' 
                WHEN a.id IN (24,25,59)     THEN 'U-Central' 
                WHEN a.id IN (46,47,60)     THEN 'U-NE' 
                WHEN a.id IN (20,67,68)     THEN 'L-South' 
                WHEN a.id IN (45,69)        THEN 'M-East' 
                WHEN a.id IN (37,52,58)     THEN 'M-Central' 
                WHEN a.id IN (14,42,65)     THEN 'M-South' 
                WHEN a.id IN (13,56)        THEN 'CR' 
                WHEN a.id IN (53)           THEN 'L-Central' 
                WHEN a.id IN (33,54,55,57)  THEN 'CM' 
                WHEN a.id IN (38,61,64)     THEN 'L-NE' 
                WHEN a.id IN (39,50,51)     THEN 'L-East' 
                ELSE a.name 
                END)
                "),

            'area_id'       => 'a.id',
            'area_name'     => 'a.name',
            'cnt_store_s'   => new Zend_Db_Expr("COUNT(DISTINCT CASE WHEN st.store_grade = 'S' THEN st.id END)"),
            'cnt_pc_s'      => new Zend_Db_Expr("COUNT(DISTINCT CASE WHEN st.store_grade = 'S' THEN s.id END)"),
            'cnt_store_a'   => new Zend_Db_Expr("COUNT(DISTINCT CASE WHEN st.store_grade = 'A' THEN st.id END)"),
            'cnt_pc_a'      => new Zend_Db_Expr("COUNT(DISTINCT CASE WHEN st.store_grade = 'A' THEN s.id END)"),
        );

        $select = $db->select()
        ->from(array('a' => 'area'), $get)
        ->join(array('rm' => 'regional_market') ,'a.id = rm.area_id', array())
        ->joinLeft(array('st' => 'store'), 
            "   rm.id = st.regional_market 
            AND st.store_grade IN ('S','A') 
            ", array())
        ->joinLeft(array('ss' => 'store_staff'), 
            "   st.id = ss.store_id 
            AND ss.is_leader = 0 
            ", array())
        ->joinLeft(array('s' => 'staff'), 
            "   ss.staff_id = s.id 
            AND s.pc_stand_by = 0 
            ", array())
        ->where('a.id NOT IN (?)', array(48,49,72))
        ->group(array('a.id'))
        ->order(array('region ASC', 'a.name ASC'));

        if ( isset($params['area_id']) && $params['area_id'] ) {
            $select->where('a.id IN (?)', $params['area_id']);
        }

        if ( isset($params['store_type']) && $params['store_type'] ) {
            $select->where('o.org_id IN (?)', $params['store_type']);
        }

        // check permission ASM, ASM Stand by, Sale Admin, Traning
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

    function fetchPaginationStoreStaff($params)
    {
        $db = Zend_Registry::get('db');

        $select = $db->select()->from(array('p' => 'store_staff'), array('ss_id' => 'p.id', 'leader' => 'p.is_leader'));

        $select->join(array('a' => 'staff'), 'p.staff_id = a.id', array('staff_code' => 'a.code','staff_name' => new Zend_Db_Expr("CONCAT(a.firstname, ' ', a.lastname)")));
        $select->join(array('s' => 'store'), 'p.store_id = s.id', array('st_id' => 's.id', 'store_code' => 'st.store_code', 'store_name' => 's.name'));

        $select->join(array('r' => 'regional_market'), 's.regional_market = r.id', array('region' => 'r.name'));

        if (isset($params['store_name']) and $params['store_name'])
            $select->where('s.name LIKE ?', '%' . $params['store_name'] . '%');

        if (isset($params['staff_code']) && $params['staff_code'])
            $select->where('a.code IN (?)', $params['staff_code']);

        if (isset($params['store_id']) and $params['store_id'])
            $select->where('s.id IN (?)', $params['store_id']);

        if (isset($params['is_leader']) && $params['is_leader'])
            $select->where('p.is_leader =?', $params['is_leader']);

        if (isset($params['region_id']) && $params['region_id'])
            $select->where('s.regional_market LIKE ?', '%'.$params['region_id'].'%');
        $select->order('p.id DESC');
        $select->group('p.id');
        

        $result = $db->fetchAll($select);
        return $result;
    }

    function getPCDB($store_id, $status = null) {

        $db = Zend_Registry::get('db');

        $select = $db->select()
        ->from(array('p' => $this->_name),
            array(new Zend_Db_Expr('SQL_CALC_FOUND_ROWS p.id'), 'p.*', 'pid' => 'p.id' ));

        $select->joinLeft(array('cp' => 'staff'), 'p.staff_id = cp.id', array('cp_code' => 'cp.code'));
        $select->joinLeft(array('s' => 'staff'), 'p.staff_id = s.id', array('pcdb_name' => new Zend_Db_Expr("CONCAT(s.firstname)"), 'pcdb_code' => 's.code', 'pc_phone' => 's.phone_number' ));
        $select->joinLeft(array('g' => 'group'), 's.group_id = g.id', array('group_name' => 'g.name' ));

        if (isset($store_id) && $store_id) {
            $select->where('p.store_id = ?', $store_id);
            $select->where('p.is_leader = 7');
        }

        if (isset($status)) { 
            $select->where('s.pc_stand_by = ?', $status);
        } 

        $result = $db->fetchAll($select);

        return $result;
    }
    function getRD($store_id, $status = null) {

        $db = Zend_Registry::get('db');

        $select = $db->select()
        ->from(array('p' => $this->_name),
            array(new Zend_Db_Expr('SQL_CALC_FOUND_ROWS p.id'), 'p.*', 'pid' => 'p.id' ));

        $select->joinLeft(array('cp' => 'staff'), 'p.staff_id = cp.id', array('cp_code' => 'cp.code'));
        $select->joinLeft(array('s' => 'staff'), 'p.staff_id = s.id', array('rd_name' => new Zend_Db_Expr("CONCAT(s.firstname)"), 'rd_code' => 's.code', 'pc_phone' => 's.phone_number' ));
        $select->joinLeft(array('g' => 'group'), 's.group_id = g.id', array('group_name' => 'g.name' ));

        if (isset($store_id) && $store_id) {
            $select->where('p.store_id = ?', $store_id);
            $select->where('p.is_leader = 6');
        }

        $result = $db->fetchAll($select);

        return $result;
    }
}
