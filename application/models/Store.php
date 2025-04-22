<?php
class Application_Model_Store extends Zend_Db_Table_Abstract
{
	protected $_name = 'store';

    function fetchPagination($page, $limit, &$total, $params){
        $db = Zend_Registry::get('db');

        $select = $db->select()
            ->from(array('p' => $this->_name),
                array(new Zend_Db_Expr('SQL_CALC_FOUND_ROWS p.id'), 'p.*'));

        $select->joinLeft(array('g' => 'store_staff'), 'p.id = g.store_id AND g.is_leader = 1', array('store_status' => 'p.status', 'latitude' => 'p.lat', 'longtitude' => 'p.lng', ));
        $select->joinLeft(array('s' => 'staff'), 'g.staff_id = s.id', array('sale_code' => 's.code','sale_fname' => 's.firstname', 'sale_name' => new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)") ));


        // Range of Last 1 Month
        $tmp_start_01 = new DateTime( $params['from'] );
        $tmp_start_01->modify( 'first day of previous month' );
        $last_start_01 = $tmp_start_01->format( 'Y-m-d' );

        $tmp_end_01 = new DateTime( $params['from'] );
        $tmp_end_01->modify( 'last day of previous month' );
        $last_end_01 = $tmp_end_01->format( 'Y-m-d' );

        $start_day_month = date('Y-m-01');
        $end_day_month = date('Y-m-t');
        

        // Store Visit
	    $select->joinLeft(array('sv' => 'store_visit'), 'p.store_code = sv.store_code', array(
            'last_total_visit'   => new Zend_Db_Expr("COUNT( CASE WHEN sv.created_at >= '".$last_start_01." 00:00:00' AND sv.created_at <= '".$last_end_01." 23:59:59' THEN sv.num END )"),
            'this_total_visit'   => new Zend_Db_Expr("COUNT( CASE WHEN sv.created_at >= '".$start_day_month." 00:00:00' AND sv.created_at <= '".$end_day_month." 23:59:59' THEN sv.num END )"),
            'total_visit'      => new Zend_Db_Expr("COUNT( CASE WHEN sv.created_at THEN sv.num END  )")));

        $select->joinLeft(array('f7' => 'store_staff'), 'p.id = f7.store_id AND f7.is_leader = 4',array('asm_code' => 's7.code','asm_fname' => 's7.firstname', 'asm_name' => 'CONCAT(s7.firstname, " ",s7.lastname)'));
        $select->joinLeft(array('f10' => 'store_staff'), 'p.id = f10.store_id AND f10.is_leader = 7',array('pcdb_code' => 's10.code','pcdb_name' => 'CONCAT(s10.firstname)'));
 	    $select->joinLeft(array('f11' => 'store_staff'), 'p.id = f11.store_id AND f11.is_leader = 6',array('rd_code' => 's11.code','rd_name' => new Zend_Db_Expr("CONCAT(s11.firstname, ' ', s11.lastname)") ));
        $select->joinLeft(array('s11' => 'staff'),'s11.id = f11.staff_id',array());
        $select->joinLeft(array('s10' => 'staff'),'s10.id = f10.staff_id',array());
        $select->joinLeft(array('f8' => 'store_staff'), 'p.id = f8.store_id AND f8.is_leader = 5',array('sale_mkt_code' => 's8.code', 'sale_mkt_fname' => 's8.firstname', 'sale_mkt_name' => 'CONCAT(s8.firstname, " ",s8.lastname)'));
        $select->joinLeft(array('s7' => 'staff'),'s7.id = f7.staff_id',array());
        $select->joinLeft(array('s8' => 'staff'),'s8.id = f8.staff_id',array());
        $select->joinLeft(array('g2' => 'store_staff'), 'p.id = g2.store_id AND g2.is_leader = 2', array('pcm_code'=>'s3.code', 'pcm_fname' => 's8.firstname', 'pcm_name'=>'CONCAT(s8.firstname, " ",s8.lastname)'));
        $select->joinLeft(array('g3' => 'store_staff'), 'p.id = g3.store_id AND g3.is_leader = 0', array());
        $select->joinLeft(array('s3' => 'staff'), 'g2.staff_id = s3.id', array('pcm_name' => new Zend_Db_Expr("CONCAT(s3.firstname, ' ', s3.lastname)") ));
        $select->joinLeft(array('f4' => 'store_staff'), 'p.id = f4.store_id AND f4.is_leader = 3',array('stock' => 'f4.staff_id'));
        $select->joinLeft(array('so'=> 'store_operation'), 'p.operation_id = so.id', array('store_operation'=>'so.name'));
        $select->joinLeft(array('r' => 'regional_market'), 'p.regional_market = r.id', array('regional_market_name'=>'r.name'));
        $select->joinLeft(array('d' => 'regional_market'), 'd.id = p.district', array('district_name'=>'d.name'));
        $select->joinLeft(array('a' => 'area'), 'r.area_id = a.id', array('area_name'=>'a.name'));
        $select->joinLeft(array('dis' => WAREHOUSE_DB.'.distributor'), 'p.d_id = dis.id', array('d_name' => 'dis.title','w_id' => 'dis.warehouse_id','phone_number' => 'p.phone_number','d_status' => 'dis.del','owner' => 'p.leader','distributor_code' => 'dis.distributor_code'));
        $select->joinLeft(array('war' => WAREHOUSE_DB.'.warehouse'), 'dis.warehouse_id = war.id', array('w_name' => 'war.name','w_id' => 'war.id'));

        $select->joinLeft(array('o' => 'org'), 'p.org_dealer = o.org_id', array('store_type' => 'o.org_name'));
        $select->joinLeft(array('sd'=> 'sub_district'), 'p.sub_district = sd.id', array('sd_name' => 'sd.name'));
        $select->joinLeft(array('ac'=> 'area_control'), 'sd.id = ac.sub_district', array());
        $select->joinLeft(array('sa'=> 'sub_area'), 'p.agency = sa.id', array('sa_name' => 'sa.name'));
        $select->order(array('p.created_at DESC'));

        if ((isset($params['staff_email']) and $params['staff_email']) || (isset($params['export']) and $params['export'])){

            $select->joinLeft(array('ss' => 'store_staff'), 'p.id = ss.store_id', array());
            $select->joinLeft(array('s2' => 'staff'), 'ss.staff_id = s2.id', array());

            if (isset($params['staff_email']) and $params['staff_email'])
                $select->where('s2.email LIKE ?', '%'.$params['staff_email'].'%');
        }

        if (isset($params['asm']) and $params['asm']) {
            $QAsm = new Application_Model_Asm();
            $list_regions = $QAsm->get_cache($params['asm']);
            $list_regions = isset($list_regions['province']) && is_array($list_regions['province']) ? $list_regions['province'] : array();

            if (count($list_regions))    
                $select->where('p.province_id IN (?)', $list_regions); // l?c staff thu?c regional_market trên
            else
                $select->where('1=0', 1);
        }

        // check Permission AM
        if ( isset($params['am']) && $params['am'] ) {
            $QAm = new Application_Model_Am();
            $list_org = $QAm->get_cache($params['am']);
            $list_org = isset($list_org['store_type']) && is_array($list_org['store_type']) ? $list_org['store_type'] : array();

            if (count($list_org) > 0)
                $select->where( 'p.org_dealer IN (?)', $list_org);
            else
                $select->where('1=0', 1);
        }

        if (isset($params['agency']) && $params['agency']) {
            $select->where('p.agency IN (?)',$params['agency']);
        }

        if (isset($params['province_id']) && $params['province_id']) {
            $select->where('p.province_id IN (?)',$params['province_id']);
        }

        if (isset($params['area_array']) && $params['area_array']){
            $select->where('p.area_id IN (?)',$params['area_array']);
        }

        // check Permission Admin Brandshop
        if ( isset($params['admin_bs']) && $params['admin_bs'] ) {
            $select->where('(p.org_dealer = ?', 18);
            $select->orWhere('o.store_type_id = ?)', 3);
        }

        if (isset($params['address']) and $params['address'])
            $select->where('p.company_address LIKE ? OR p.shipping_address LIKE ? ', '%'.$params['address'].'%');

        if (isset($params['id']) and $params['id'])
            $select->where('p.store_code LIKE ?', '%'.$params['id'].'%');

        if (isset($params['name']) and $params['name'])
            $select->where('p.name LIKE ?', '%'.$params['name'].'%');

        if(isset($params['oppo_id']) and $params['oppo_id']);
            $select->where('p.oppo_id LIKE ?', '%'.$params['oppo_id'].'%');

        if (isset($params['d_id']) and $params['d_id'])
            $select->where('dis.distributor_code LIKE ?', '%'.$params['d_id'].'%');

        if (isset($params['d_name']) and $params['d_name'])
            $select->where('dis.title LIKE ?', '%'.$params['d_name'].'%');
        

        if ( (isset($params['market_type']) and $params['market_type']) || (isset($params['market_name']) && $params['market_name']) ) {

            $select->joinLeft( array('sm' => 'store_market'), 'p.id = sm.store_id'          , array());
            $select->joinLeft( array('mn' => 'market_name') , 'sm.market_name_id = mn.id'   , array('market_name' => 'mn.name'));
            //$select->joinLeft( array('mt' => 'market_type') , 'mn.market_type_id = mt.id'   , array('market_type' => 'mt.name'));

            // filter Market Type
            if (isset($params['market_type']) and $params['market_type']) {
                if (is_array($params['market_type']) && count($params['market_type'])) { 

                    $select->where('mn.market_type_id IN (?)', $params['market_type']);

                } elseif (is_numeric($params['market_type'])) {
                    
                    $select->where('mn.market_type_id = ?', intval($params['market_type']));

                } else {
                    $select->where('1=0', 1);
                }
            }

            // Filter Market Name
            if (isset($params['market_name']) and $params['market_name']) {
                if (is_array($params['market_name']) && count($params['market_name'])) { 

                    $select->where('mn.id IN (?)', $params['market_name']);

                } elseif (is_numeric($params['market_name'])) {
                    
                    $select->where('mn.id = ?', intval($params['market_name']));

                } else {
                    $select->where('1=0', 1);
                }
            }

        }

        // Add Filter Store Type
        if (isset($params['store_type']) && $params['store_type']) {
            if (is_array($params['store_type']) && count($params['store_type']))
                $select->where('p.org_dealer IN (?)', $params['store_type']);
            elseif (is_numeric($params['store_type']))
                $select->where('p.org_dealer = ?', intval($params['store_type']));
            else
                $select->where('1=0', 1);
        }

        // Add Filter Store Level
        if (isset($params['store_level']) && $params['store_level']) {
            if (is_array($params['store_level']) && count($params['store_level']))
                $select->where('p.store_grade IN (?)', $params['store_level']);
            else
                $select->where('1=0', 1);
        }

/*
        if (isset($params['area_id']) and $params['area_id'])
            $select->where('a.id = ?', $params['area_id']);

        if (isset($params['regional_market']) and $params['regional_market'])
            $select->where('p.regional_market = ?', $params['regional_market']);

        if (isset($params['district']) and $params['district'])
            $select->where('p.district = ?', $params['district']);
*/

        // Add Filter Area
        if (isset($params['area_id']) && $params['area_id']) {
            if (is_array($params['area_id']) && count($params['area_id']))
                $select->where('r.area_id IN (?)', $params['area_id']);
            elseif (is_numeric($params['area_id']))
                $select->where('r.area_id = ?', intval($params['area_id']));
            else
                $select->where('1=0', 1);
        }

        // Add Filter Province
        if (isset($params['regional_market']) && $params['regional_market']) {
            if (is_array($params['regional_market']) && count($params['regional_market']))
                $select->where('p.regional_market IN (?)', $params['regional_market']);
            elseif (is_numeric($params['regional_market']))
                $select->where('p.regional_market = ?', intval($params['regional_market']));
            else
                $select->where('1=0', 1);
        }

        // Add Filter District
        if (isset($params['district']) && $params['district']) {
            if (is_array($params['district']) && count($params['district']))
                $select->where('p.district IN (?)', $params['district']);
            elseif (is_numeric($params['district']))
                $select->where('p.district = ?', intval($params['district']));
            else
                $select->where('1=0', 1);
        }

        
        // Check Location
        if ( isset( $params['location_checked'] ) and $params['location_checked'] == 1 ) {

            $select->where('p.lng IS NOT NULL');
        }

        if ( isset( $params['location_not_check'] ) and $params['location_not_check'] == 1 ) {

            $select->where('p.lng IS NULL');
        }


        if ( isset( $params['have_pg'] ) and $params['have_pg'] == 1 ) {

            $select->where('g3.staff_id IS NOT NULL');
        }

         if ( isset( $params['have_asm'] ) and $params['have_asm'] == 1 ) {
            $select->where('f7.staff_id IS NOT NULL');
        }

        if(isset($params['have_pcdb']) and $params['have_pcdb'] == 1){
            $select->where('f10.staff_id IS NOT NULL');
        }

        if(isset($params['have_mkt']) and $params['have_mkt'] == 1){
            $select->where('f8.staff_id IS NOT NULL');
        }

        if ( isset( $params['have_sales'] ) and $params['have_sales'] == 1 ) {
            $select->where('g.staff_id IS NOT NULL');
        }
        if(isset($params['have_rd']) and $params['have_rd'] == 1){
            $select->where('f11.staff_id IS NOT NULL');
        }

        if ( isset( $params['no_pc'] ) and $params['no_pc'] == 1 ) {      
            $select->where('g3.staff_id IS NULL');
        }

        if ( isset( $params['have_pcm'] ) and $params['have_pcm'] == 1 ) {      
            $select->where('g2.staff_id IS NOT NULL');
        }

        if ( isset( $params['have_stock'] ) and $params['have_stock'] == 1 ) {      
            $select->where('f4.staff_id IS NOT NULL');
        }

        if ( isset( $params['no_staff'] ) and $params['no_staff'] == 1 ) {
            $select->where('g.staff_id IS NULL');
            $select->where('f7.staff_id IS NULL');
            $select->where('f8.staff_id IS NULL');
            $select->where('g3.staff_id IS NULL');
            $select->where('f4.staff_id IS NULL');
            $select->where('g2.staff_id IS NULL');
        }


        if(isset($params['store_status']) and $params['store_status']) {
            $select->where('p.status =?',$params['store_status']);
        }else{
            $select->where('p.status =?',1);
        }

        if (isset($params['sort']) and $params['sort']) {
            $order_str = $collate = ' ';
            $desc = (isset($params['desc']) and $params['desc'] == 1) ? ' DESC ' : ' ASC ';

            if ($params['sort'] == 'name'){
                $collate = ' COLLATE utf8_unicode_ci ';
                $order_str .= ' p.name '.$collate . $desc;
            } elseif ( $params['sort'] == 'area' ) {
                $collate = ' COLLATE utf8_unicode_ci ';
                $order_str .= 'a.name' . $collate . $desc;
            } elseif ( $params['sort'] == 'district' ) {
                $collate = ' COLLATE utf8_unicode_ci ';
                $order_str .= 'd.name' . $collate . $desc;
            } elseif ( $params['sort'] == 'regional_market' ) {
                $collate = ' COLLATE utf8_unicode_ci ';
                $order_str =  ' r.name ' . $collate . $desc;
            } elseif ( $params['sort'] == 'retailer_id' ) {
                $collate = ' COLLATE utf8_unicode_ci ';
                $order_str =  ' dis.id ' . $collate . $desc;
            } elseif ( $params['sort'] == 'retailer_name' ) {
                $collate = ' COLLATE utf8_unicode_ci ';
                $order_str =  ' dis.title ' . $collate . $desc;
            } elseif ( $params['sort'] == 'sale_name' ) {
                $collate = ' COLLATE utf8_unicode_ci ';
                $order_str =  ' sale_name ' . $collate . $desc;
            } elseif ( $params['sort'] == 'store_type' ) {
                $collate = ' COLLATE utf8_unicode_ci ';
                $order_str =  ' o.org_name ' . $collate . $desc;
            } elseif ( $params['sort'] == 'market_type' ) {
                $collate = ' COLLATE utf8_unicode_ci ';
                $order_str =  ' mt.name ' . $collate . $desc;
            } elseif ( $params['sort'] == 'market_name' ) {
                $collate = ' COLLATE utf8_unicode_ci ';
                $order_str =  ' mn.name ' . $collate . $desc;
            } else
                $order_str =  ' p.'.$params['sort'].' ' . $collate . $desc;

            if ($order_str)
                $select->order(new Zend_Db_Expr($order_str));
        }


        $select->group('p.id');

              if ( $limit && ! ( isset($params['export']) && $params['export'] ) )
            $select->limitPage($page, $limit);
        // else
        //     $select->where('p.del IS NULL OR p.del <> ?', 1);

        // echo $select;
        $result = $db->fetchAll($select);
        $total = $db->fetchOne("select FOUND_ROWS()");
        return $result;
    }

    function fetchPaginationLeader($page, $limit, &$total, $params){
        $db = Zend_Registry::get('db');

        $select = $db->select()
            ->from(array('p' => $this->_name),
                array(new Zend_Db_Expr('SQL_CALC_FOUND_ROWS p.id'), 'p.*'));

        $select->joinLeft(array('g' => 'store_leader'),
            'p.id = g.store_id',
            array('status' => 'g.status', 'staff_id' => 'g.staff_id'));

        $select->joinLeft(array('lg' => 'store_leader_log'),
            'g.staff_id = lg.staff_id AND g.store_id = lg.store_id AND released_at IS NULL',
            array('joined_at' => 'lg.joined_at', 'log_id' => 'lg.id'));

        $select->join(array('r' => 'regional_market'),
            'p.regional_market = r.id',
            array('regional_market_name'=>'r.name'));

        $select->join(array('a' => 'area'),
            'r.area_id = a.id',
            array('area_name'=>'a.name'));

        $select->joinLeft(array('s' => 'staff'),
                'g.staff_id = s.id',
                array( 's.firstname', 's.lastname', 's.email'));

        if (isset($params['staff_email']) and $params['staff_email'])
            $select->where('s.email LIKE ?', '%'.$params['staff_email'].'%');

        if (isset($params['address']) and $params['address'])
            $select->where('p.company_address LIKE ? OR p.shipping_address LIKE ? ', '%'.$params['address'].'%');

        if (isset($params['name']) and $params['name'])
            $select->where('p.name LIKE ?', '%'.$params['name'].'%');

        if (isset($params['area_id']) and $params['area_id'])
            $select->where('a.id = ?', $params['area_id']);

        if (isset($params['regional_market']) and $params['regional_market'])
            $select->where('p.regional_market = ?', $params['regional_market']);

        if ( isset( $params['have_leader'] ) and $params['have_leader'] == 1 ) {
            $select
                ->where('g.status = 1')
                ->where('g.staff_id IS NOT NULL');
            
        }

        elseif ( isset( $params['no_leader'] ) and $params['no_leader'] == 1 )
            $select->where('g.staff_id IS NULL');

        elseif ( isset( $params['pending'] ) and $params['pending'] == 1 ) {
            $select
                ->where('g.status = 0')
                ->where('g.staff_id IS NOT NULL');
        }

        $QRegion = new Application_Model_RegionalMarket();
        $userStorage = Zend_Auth::getInstance()->getStorage()->read();

        if (isset($params['for_asm']) && $params['for_asm']) {
            $QAsm = new Application_Model_Asm();
            $list_regions = $QAsm->get_cache();
            $list_regions = isset($list_regions['district']) && is_array($list_regions['district']) ? $list_regions['district'] : array();

            if (count($list_regions))
                $select->where('p.district IN (?)', $list_regions);
            else
                $select->where("1=0", 1);
        }

        if (isset($params['for_leader']) && $params['for_leader']) {
            $region = $QRegion->find($userStorage->regional_market);
            $region = $region->current();

            if ($region) {
                $where = $QRegion->getAdapter()->quoteInto('area_id = ?', $region['area_id']);
                $regions = $QRegion->fetchAll($where);
                
                $region_list = array();

                foreach ($regions as $reg)
                    $region_list[] = $reg['id'];
                
                if (is_array($region_list) && count($region_list) > 0)
                    $select->where('p.regional_market IN (?)', $region_list);
                else
                    $select->where("1=0", 1);
            } else {
                $select->where("1=0", 1);
            }
        }

        $select->where('p.del = 0 OR p.del IS NULL');

        if (isset($params['sort']) and $params['sort']) {
            $order_str = $collate = ' ';
            $desc = (isset($params['desc']) and $params['desc'] == 1) ? ' DESC ' : ' ASC ';

            if ($params['sort'] == 'name'){
                $collate = ' COLLATE utf8_unicode_ci ';
                $order_str .= ' p.name '.$collate . $desc;

            } elseif ( $params['sort'] == 'area' ) {
                $collate = ' COLLATE utf8_unicode_ci ';
                $order_str .= 'a.name' . $collate . $desc;

            } elseif ( $params['sort'] == 'leader' ) {
                $collate = ' COLLATE utf8_unicode_ci ';
                $order_str .= ' CONCAT(s.firstname, " ",s.lastname) ' . $collate . $desc;

            } elseif ( $params['sort'] == 'company_address' ) {
                $collate = ' COLLATE utf8_unicode_ci ';
                $order_str .= ' p.company_address ' . $collate . $desc;

            } elseif ( $params['sort'] == 'regional_market' ) {
                $order_str =  ' r.name ' . $collate . $desc;
                
            } else
                $order_str =  ' p.'.$params['sort'].' ' . $collate . $desc;

            if ($order_str)
                $select->order(new Zend_Db_Expr($order_str));
        }

        $select->group('p.id');

        if ( $limit && ! ( isset($params['export']) && $params['export'] ) )
            $select->limitPage($page, $limit);
        else
            $select->where('p.del IS NULL OR p.del <> ?', 1);

        $result = $db->fetchAll($select);
        $total = $db->fetchOne("select FOUND_ROWS()");
        return $result;
    }

    function get_cache(){
        $cache      = Zend_Registry::get('cache');
        $result     = $cache->load($this->_name.'_cache');

        if ($result === false) {

            $data = $this->fetchAll();

            $result = array();
            if ($data){
                foreach ($data as $item){
                    $result[$item->id] = $item->name;
                }
            }
            $cache->save($result, $this->_name.'_cache', array(), null);
        }
        return $result;
    }

    function get_assigned_cache(){
        $cache      = Zend_Registry::get('cache');
        $result     = $cache->load($this->_name.'_assigned_cache');

        if ($result === false) {

            $data = $this->fetchAll();

            $result = array();

            $db = Zend_Registry::get('db');


            if ($data){
                foreach ($data as $item){
                    $select = $db->select()
                        ->from(array('p' => 'store_staff'),
                            array('p.*'))
                        ->join(array('s' => 'staff'),
                            's.id = p.staff_id',
                            array('s.firstname', 's.lastname', 's.group_id'));

                    $select->where('p.store_id = ?', $item->id);

                    $staffs = $db->fetchAll($select);

                    $sales = $PGs = '';

                    //get staffs
                    if ($staffs){
                        foreach ($staffs as $staff){
                            if ($staff['group_id']==SALES_ID){
                                $break_line = $sales !== '' ? "\n" : '';
                                $sales .= $break_line . $staff['firstname'].' '.$staff['lastname'];

                            }elseif ($staff['group_id']==PGPB_ID){

                                $break_line = $PGs !== '' ? "\n" : '';
                                $PGs .= $break_line . $staff['firstname'].' '.$staff['lastname'];
                            }

                        }
                    }

                    $result[$item->id] = array(
                        'sales' => $sales,
                        'PGs' => $PGs,
                    );
                }
            }
            $cache->save($result, $this->_name.'_assigned_cache', array(), null);
        }
        return $result;
    }

    function compare($full_store_name)
    {
        $full_store_name = My_String::trim($full_store_name);
        $check_list = array();

        $db = Zend_Registry::get('db');

        $store_name_split = explode('-', $full_store_name);

        if (is_array($store_name_split) && !empty($store_name_split[0])) {
            $store_name = $store_name_split[0];
        } else {
            $store_name = $full_store_name;
        }
        
        $where = array();
        $where[] = $this->getAdapter()->quoteInto('del = 0 OR del IS NULL', 1);
        $where[] = $this->getAdapter()->quoteInto('name LIKE ?', '%'.$store_name.'%');
        $check_store_name = $this->fetchAll($where, 'name', 5);

        foreach ($check_store_name as $key => $store) {
            $check_list[ $store['id'] ] = $store['name'];
        }

        //
        
        $full_store_name_like = str_replace(array('Ä‘Æ°á»ng', 'sá»‘', 'ngÃµ', 'háº»m', 'huyá»‡n', 'quáº­n', 'xÃ£', 'phÆ°á»ng', 'thÃ´n', 'áº¥p', 'xÃ³m', 'thÃ nh phá»‘', 'tp', 'tá»‰nh', ',', '-', '(', ')'), 
                                                '', mb_strtolower($full_store_name, 'UTF-8'));
        
        $full_store_name_like = preg_replace('/[\s]+/', '%', $full_store_name_like);
        $full_store_name_like = preg_replace('/[\%]+/', '%', $full_store_name_like);

        $where = array();
        $where[] = $this->getAdapter()->quoteInto('del = 0 OR del IS NULL', 1);
        $where[] = $this->getAdapter()->quoteInto('name LIKE ?', '%'.$full_store_name_like.'%');

        $check_store_name = $this->fetchAll($where);

        $count = 0;
        foreach ($check_store_name as $key => $store) {
            if ($count++ > 10) break;

            if (!isset($check_list[ $store['id'] ]))
                $check_list[ $store['id'] ] = $store['name'];
        }

        //

        $store_list = $this->get_cache();

        $count = 0;
        foreach ($store_list as $_store_id => $_store_name) {
            if ($count++ > 10) break;

            if ( similar_text($_store_name, $full_store_name) > 75 && !isset($check_list[ $_store_id ]))
                $check_list[ $_store_id ] = $_store_name;
        }

        
        return $check_list;
    }

    // NEW FUNCTION Pungpond //
    public function getDitributor($id)
    {
        $db = Zend_Registry::get('db');
        $select = $db->select()
            ->from(array('p' => $this->_name),
                array('p.*'));
            $select->where('p.d_id = ?', $id); 
            $result = $db->fetchAll($select);
           
        return $result;

    } 
    public function getStoreList($id)
    {
        $db = Zend_Registry::get('db');
        $select = $db->select()
            ->from(array('p' => $this->_name),
                array('p.*'));
        
            $select->where('p.id = ?', $id); 
            $result = $db->fetchAll($select);
            foreach ($result as $k => $item) {
                            $item = $item['d_id'];
                        }
           
        return $item;

    } 
    
    public function getStoreListAll()
    {
        $db = Zend_Registry::get('db');
        $select = $db->select()
            ->from(array('p' => $this->_name),
                array('p.id','p.name'));
            //$select->limitPage(0, 8000);
            $result = json_encode($db->fetchAll($select));
            
            
        return $result;

    } 
    public function getStoreAddress($id)
    {
        $db = Zend_Registry::get('db');
        $select = $db->select()
            ->from(array('p' => $this->_name),
                array('p.*'));
        
            $select->where('p.id = ?', $id); 
            $result = $db->fetchAll($select);
            foreach ($result as $k => $item) {
                            $item = $item['shipping_address'];
                        }
           
        return $item;
            

    }

    public function getShopInventory($page, $limit, &$total, $params)
    {
        $db = Zend_Registry::get('db');
        $select = $db->select()
        ->from(array('st' => $this->_name),
                array(new Zend_Db_Expr('SQL_CALC_FOUND_ROWS st.id'), 'store_name'=>'st.name','quantity' => 'count(i.imei_sn)','good_name'=>'g.name','good_color'=>'gc.name','org_name'=>'o.org_name'));

        $select->join(array('i' => 'warehouse_live.imei'), 'st.id = i.stock_shop_id AND i.stock_shop_status = 1', array());
        $select->join(array('g' => 'warehouse_live.good'), 'i.good_id = g.id', array());
        $select->join(array('gc' => 'warehouse_live.good_color'), 'i.good_color = gc.id', array());
        $select->join(array('o' => HR_DB.'.org'), 'st.org_dealer = o.org_id', array());
            
        if (isset($params['store_list']) and $params['store_list'])
            $select->where('st.id = ? ', $params['store_list']);
  
        $result = $db->fetchAll($select);
        // echo $select;die;
        $total = $db->fetchOne("select FOUND_ROWS()");  
        return $item;
            

    }


     function getAll_StoreWsCli($params) {
        $db = Zend_Registry::get('db');
        $select = $db->select()
                ->from(array('p' => $this->_name),array('p.*'));

        if($params['limit']) { 
            $select->where('p.del IS NULL OR p.del <> ?', 1);
            $select->limit($params['limit'],$params['offset']);
         }
        else { 
            $result = $db->fetchAll($select);
            $total = $db->fetchOne("select FOUND_ROWS()"); 
            return $total;
        }

        $result = $db->fetchAll($select);
        return $result;
    } 

    function getStoreMarketType($params) {
        $db = Zend_Registry::get('db');
        $select = $db->select()
                ->from(array('s' => $this->_name),array('store_id'=>'s.id','store_name'=>'s.name'));
        $select->join(array('sm' => HR_DB.'.store_market'), 's.id=sm.store_id', array());
        $select->join(array('mn' => HR_DB.'.market_name'), 'sm.market_name_id=mn.id', array('market_name'=>'mn.name'));
        $select->join(array('mt' => HR_DB.'.market_type'), 'mt.id=mn.market_type_id', array('market_type'=>'mt.name'));
        $select->join(array('r'  => HR_DB.'.regional_market'),'s.regional_market = r.id',array());
        $select->join(array('a'  => HR_DB.'.area'),'r.area_id = a.id',array('area_id' => 'a.id','area' =>'a.name'));
        $select->where('s.id in (?)', $params);
        $result = $db->fetchAll($select);
        return $result;
    } 

    public function getStoreByAreaSellOutForWebService($params) {

        $tmp_date = date('Y-m-d H:i:s', strtotime("-30 day"));

        $db = Zend_Registry::get('db');
        $select = $db->select()
            ->from(array('s'=> $this->_name),array(
                    'store_id' =>'s.id','store_name'=>'s.name','address' => 's.shipping_address', 'created_at' => 's.created_at'))
            ->join(array('r'=> HR_DB.'.regional_market'),'s.regional_market = r.id',array())
            ->join(array('a'=> HR_DB.'.area'),'r.area_id = a.id',array('area_id' => 'a.id','area' =>'a.name'))
            ->joinLeft(array('t'=> HR_DB.'.timing'),'s.id = t.store 
                     AND t.created_at >= "'.$params['one'].'"
                     AND t.created_at <= "'.$params['one_end'].'"',array('sell_out' => 'COUNT(ts.imei)'))
            ->joinLeft(array('ts'=> HR_DB.'.timing_sale'),'t.id = ts.timing_id ',array())
            ->where('a.id in (?)',$params['area'])
            ->where('s.del is null')
            ->group('s.id')
            ->having("COUNT(ts.imei) >= 30 OR s.created_at >= '".$tmp_date."' ")

            ->order('sell_out DESC');
        
        //echo $select; die;
        $sellout = $db->fetchAll($select); 
        return $sellout;
    }

    public function getAllStoreByArea($area_id) {

        $db = Zend_Registry::get('db');

        $get = array(
            'area_id'   => 'a.id',
            'area_name' => 'a.name',
            'st_id'     => 'st.id',
            'st_name'   => 'st.name',
        );

        $select = $db->select()
            ->from(array('st' => $this->_name), $get)
            ->join(array('rm' => 'regional_market'), 'st.regional_market = rm.id', array())
            ->join(array('a'  => 'area'), 'rm.area_id = a.id', array()) 
            ->where('a.id IN (?)', $area_id)
            ->order(array('a.name ASC', 'st.id ASC'));
        
        //echo $select; die;
        $result = $db->fetchAll($select); 
        return $result;

    }

    public function getAllStoreBySale($staff_id) {

        $db = Zend_Registry::get('db');

        $get = array(
            'st_id'     => 'st.id',
            'st_name'   => 'st.name',
        );

        $select = $db->select()
            ->from(array('st' => $this->_name), $get)
            ->join(array('ss' => 'store_staff'), 'st.id = ss.store_id AND ss.is_leader = 1', array())
            ->join(array('s'  => 'staff'), 'ss.staff_id = s.id AND s.off_date IS NULL', array())
            ->where('s.id IN (?)', $staff_id)
            ->order(array('st.id ASC'));
        
        //echo $select; die;
        $result = $db->fetchAll($select); 
        return $result;

    }

    function getStoreById($store_id) {

        $db = Zend_Registry::get('db');

        $get = array(
            'area_id'         => 'a.id',
            'area_name'       => 'a.name',
            'province_id'     => 'rm.id',
            'province_name'   => 'rm.name',
            'store_id'        => 'st.id',
            'store_name'      => 'st.name',
            'store_type_id'   => 'o.org_id',
            'store_type_name' => 'o.org_name',
        );

        $select = $db->select()
            ->from(array('st' => 'store'), $get)
            ->join(array('o'  => 'org')             ,'st.org_dealer = o.org_id'     ,array())
            ->join(array('rm' => 'regional_market') ,'st.regional_market = rm.id'   ,array())
            ->join(array('a'  => 'area')            ,'rm.area_id = a.id'            ,array())
            ->where('st.id = ?', $store_id);

        //echo $select;
        $result = $db->fetchAll($select);
        return json_encode($result);

    }

    function getStorePcList($params) {

        $db = Zend_Registry::get('db');

        $get = array(
            'area_name'   => 'a.name',
            'st_id'       => 'st.id',
            'st_name'     => 'st.name',
            'st_type'     => 'o.org_name',
            'st_level'    => 'st.store_grade',
            'staff_code'  => 's.code',
            'staff_name'  => new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)"),
            'group_name'  => 'g.name',
            'pc_stand_by' => new Zend_Db_Expr("(CASE WHEN s.id IS NOT NULL THEN (CASE WHEN s.pc_stand_by = 1 THEN 'Yes' ELSE 'No' END) ELSE '' END)"),
            'joined_at'   => 's.joined_at',
            'work_month'  => new Zend_Db_Expr("TIMESTAMPDIFF(MONTH, DATE(s.joined_at), NOW())"),
            'work_day'    => new Zend_Db_Expr("TIMESTAMPDIFF(DAY, DATE(s.joined_at), NOW())"),
        );

        $select = $db->select()
            ->from(array('st' => $this->_name), $get)
            ->join(array('o'  => 'org')             , 'st.org_dealer = o.org_id'    , array())
            ->join(array('rm' => 'regional_market') , 'st.regional_market = rm.id'  , array())
            ->join(array('a'  => 'area')            , 'rm.area_id = a.id'           , array())
            ->joinLeft(array('ss' => 'store_staff') , 'st.id = ss.store_id AND ss.is_leader = 0', array())
            ->joinLeft(array('s'  => 'staff')       , 'ss.staff_id = s.id'          , array())
            ->joinLeft(array('g'  => 'group')       , 's.group_id = g.id'           , array())
            ->where('st.del IS NULL', 1)
            ->order(array('a.name ASC', 'st.id ASC', 's.code ASC'));
       
        if (isset($params['staff_email']) and $params['staff_email']){
            $select->where('s.email LIKE ?', '%'.$params['staff_email'].'%');
        }

        if (isset($params['asm']) and $params['asm']) {
            $QAsm = new Application_Model_Asm();
            $list_regions = $QAsm->get_cache($params['asm']);
            $list_regions = isset($list_regions['district']) && is_array($list_regions['district']) ? $list_regions['district'] : array();

            if (count($list_regions))    
                $select->where('st.district IN (?)', $list_regions); // lá»c staff thuá»™c regional_market trÃªn
            else
                $select->where('1=0', 1);
        }

        // check Permission AM
        if ( isset($params['am']) && $params['am'] ) {
            $QAm = new Application_Model_Am();
            $list_org = $QAm->get_cache($params['am']);
            $list_org = isset($list_org['store_type']) && is_array($list_org['store_type']) ? $list_org['store_type'] : array();

            if (count($list_org) > 0)
                $select->where( 'st.org_dealer IN (?)', $list_org);
            else
                $select->where('1=0', 1);
        }

        // check Permission Admin Brandshop
        if ( isset($params['admin_bs']) && $params['admin_bs'] ) {
            $select->where('(st.org_dealer = ?', 18);
            $select->orWhere('o.store_type_id = ?)', 3);
        }


        if (isset($params['d_whid']) and $params['d_whid']) {
            $select->joinLeft(array('d'  => WAREHOUSE_DB.'.distributor'), 'st.d_id = d.id', array());
            $select->where('d.warehouse_id LIKE ?', '%'.$params['d_whid'].'%');
        }

        if (isset($params['address']) and $params['address'])
            $select->where('st.company_address LIKE ? OR st.shipping_address LIKE ? ', '%'.$params['address'].'%');

        if (isset($params['id']) and $params['id'])
            $select->where('st.id = ?', $params['id']);

        if (isset($params['name']) and $params['name'])
            $select->where('st.name LIKE ?', '%'.$params['name'].'%');

        if (isset($params['d_id']) and $params['d_id'])
            $select->where('st.d_id = ?', $params['d_id']);

        if (isset($params['d_name']) and $params['d_name']) {
            $select->joinLeft(array('d'  => WAREHOUSE_DB.'.distributor'), 'st.d_id = d.id', array());
            $select->where('d.title LIKE ?', '%'.$params['d_name'].'%');
        }
        
        if ( (isset($params['market_type']) and $params['market_type']) || (isset($params['market_name']) && $params['market_name']) ) {

            $select->joinLeft( array('sm' => 'store_market'), 'st.id = sm.store_id'         , array());
            $select->joinLeft( array('mn' => 'market_name') , 'sm.market_name_id = mn.id'   , array('market_name' => 'mn.name'));

            // filter Market Type
            if (isset($params['market_type']) and $params['market_type']) {
                if (is_array($params['market_type']) && count($params['market_type'])) { 

                    $select->where('mn.market_type_id IN (?)', $params['market_type']);

                } elseif (is_numeric($params['market_type'])) {
                    
                    $select->where('mn.market_type_id = ?', intval($params['market_type']));

                } else {
                    $select->where('1=0', 1);
                }
            }

            // Filter Market Name
            if (isset($params['market_name']) and $params['market_name']) {
                if (is_array($params['market_name']) && count($params['market_name'])) { 

                    $select->where('mn.id IN (?)', $params['market_name']);

                } elseif (is_numeric($params['market_name'])) {
                    
                    $select->where('mn.id = ?', intval($params['market_name']));

                } else {
                    $select->where('1=0', 1);
                }
            }

        }

        // Add Filter Store Type
        if (isset($params['store_type']) && $params['store_type']) {
            if (is_array($params['store_type']) && count($params['store_type']))
                $select->where('st.org_dealer IN (?)', $params['store_type']);
            elseif (is_numeric($params['store_type']))
                $select->where('st.org_dealer = ?', intval($params['store_type']));
            else
                $select->where('1=0', 1);
        }

        // Add Filter Store Level
        if (isset($params['store_level']) && $params['store_level']) {
            if (is_array($params['store_level']) && count($params['store_level']))
                $select->where('st.store_grade IN (?)', $params['store_level']);
            elseif (is_numeric($params['store_level']))
                $select->where('st.store_grade = ?', intval($params['store_level']));
            else
                $select->where('1=0', 1);
        }

        // Add Filter Area
        if (isset($params['area_id']) && $params['area_id']) {
            if (is_array($params['area_id']) && count($params['area_id']))
                $select->where('rm.area_id IN (?)', $params['area_id']);
            elseif (is_numeric($params['area_id']))
                $select->where('rm.area_id = ?', intval($params['area_id']));
            else
                $select->where('1=0', 1);
        }

        // Add Filter Province
        if (isset($params['regional_market']) && $params['regional_market']) {
            if (is_array($params['regional_market']) && count($params['regional_market']))
                $select->where('st.regional_market IN (?)', $params['regional_market']);
            elseif (is_numeric($params['regional_market']))
                $select->where('st.regional_market = ?', intval($params['regional_market']));
            else
                $select->where('1=0', 1);
        }

        // Add Filter District
        if (isset($params['district']) && $params['district']) {
            if (is_array($params['district']) && count($params['district']))
                $select->where('st.district IN (?)', $params['district']);
            elseif (is_numeric($params['district']))
                $select->where('st.district = ?', intval($params['district']));
            else
                $select->where('1=0', 1);
        }

        if ( isset( $params['have_pg'] ) and $params['have_pg'] == 1 ) {
            $select->where('s.id IS NOT NULL', 1);
        }

        if ( isset( $params['have_sales'] ) and $params['have_sales'] == 1 ) {
            // $select->where('g.staff_id IS NOT NULL');
        }

        if ( isset( $params['no_staff'] ) and $params['no_staff'] == 1 ) {
            $select->where('s.id IS NULL', 1);
        }

        if ( isset( $params['st_active'] ) and $params['st_active'] == 1 ) {
            $select->where('st.del IS NULL', 1);
        }

        if ( isset( $params['st_disable'] ) and $params['st_disable'] == 1 ) {
            $select->where('st.del = ?', 1);
        }

        //echo $select;
        $result = $db->fetchAll($select);

        return $result;
    }

    function fetchStoreData($params) {
        $db = Zend_Registry::get('db');
        $select = $db->select()
                ->from(array('s' => $this->_name),array('s.*'));
        $select->joinLeft(array('d' => WAREHOUSE_DB.'.distributor'), 's.d_id = d.id', array('d_name' => 'd.title','w_id' => 'd.warehouse_id', 'phone_number' => 'd.tel', 'address' => 'd.add'));
        $select->where('s.id in (?)', $params);
        $result = $db->fetchAll($select);
        return $result;
    }


    function getClientByStoreID($id) {
        $db = Zend_Registry::get('db');
        $select = $db->select()
                ->from(array('s' => $this->_name),array('c.client_name'));
        $select->joinLeft(array('d' => WAREHOUSE_DB.'.distributor'),'d.id = s.d_id',array());
        $select->joinLeft(array('c' => WAREHOUSE_DB.'.client'),'c.customer_code = d.client_code',array());
        $select->where('s.id =?',$id);

        return $db->fetchAll($select);
    }


    // Store Visit
    function fetchStoreVisit($page, $limit, &$total, $params){
        $tmp_from = explode('/', $params['from']);
        $tmp_to = explode('/', $params['to']);

        $from = $tmp_from[2]."-".$tmp_from[1]."-".$tmp_from[0];
        $to = $tmp_to[2]."-".$tmp_to[1]."-".$tmp_to[0];
        
        $db = Zend_Registry::get('db');

        $select = $db->select()
            ->from(array('p' => $this->_name), array(new Zend_Db_Expr('SQL_CALC_FOUND_ROWS p.id'), 'p.*'));

        $select->joinLeft(array('g' => 'store_staff'), 'p.id = g.store_id AND g.is_leader = 1', array('store_status' => 'p.status'));

        $select->joinLeft(array('s' => 'staff'), 'g.staff_id = s.id', array('sale_code' => 's.code','sale_fname' => 's.firstname', 'sale_name' => new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)") ));

        $select->joinLeft(array('sv' => 'store_visit'), 'p.id = sv.store', array('total_visit' => new Zend_Db_Expr("COUNT(sv.num)")));
        // $select->joinLeft(array('sv' => 'store_visit'), 'p.id = sv.store', array('total_visit'=> new Zend_Db_Expr("COUNT(CASE WHEN sv.num AND sv.created_at >='".$params['from']."' AND sv.created_at <='".$params['to']."' THEN sv.id END)")));

        $select->joinLeft(array('r' => 'regional_market'), 'p.regional_market = r.id', array('regional_market_name'=>'r.name'));
        $select->joinLeft(array('a' => 'area'), 'r.area_id = a.id', array('area_name'=>'a.name'));
        $select->joinLeft(array('sa'=> 'sub_area'), 'p.agency = sa.id', array('sa_name' => 'sa.name'));
        $select->group(array('p.name'));
        $select->order(array('p.name ASC'));

        if(isset($params['store_status']) and $params['store_status']) {
            $select->where('p.status =?',$params['store_status']);
        }else{
            $select->where('p.status =?',1);
        }


          // Add Filter Area
          if (isset($params['area_id']) && $params['area_id']) {
            if (is_array($params['area_id']) && count($params['area_id']))
                $select->where('r.area_id IN (?)', $params['area_id']);
            elseif (is_numeric($params['area_id']))
                $select->where('r.area_id = ?', intval($params['area_id']));
            else
                $select->where('1=0', 1);
        }

        // Add Filter Province
        if (isset($params['regional_market']) && $params['regional_market']) {
            if (is_array($params['regional_market']) && count($params['regional_market']))
                $select->where('p.regional_market IN (?)', $params['regional_market']);
            elseif (is_numeric($params['regional_market']))
                $select->where('p.regional_market = ?', intval($params['regional_market']));
            else
                $select->where('1=0', 1);
        }

        $select->limitPage($page, $limit);

        // echo $select;
        $result = $db->fetchAll($select);
        $total = $db->fetchOne("select FOUND_ROWS()");
        return $result;
    }

}



