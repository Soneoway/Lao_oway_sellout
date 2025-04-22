<?php
class Application_Model_WebImei extends Zend_Db_Table_Abstract
{
	protected $_name = 'imei';
    protected $_schema = WAREHOUSE_DB;

    function fetchPagination($page, $limit, &$total, $params){
        $db = Zend_Registry::get('db');

        $select = $db->select()
        ->from(array('p' => WAREHOUSE_DB.'.'.$this->_name),
            array(new Zend_Db_Expr('SQL_CALC_FOUND_ROWS p.id'), 'p.*'));

        $select->join(array('d'=>WAREHOUSE_DB.'.'.$this->_name), 'p.distributor_id = d.id', array('d.title'))
        ->join(array('g' => WAREHOUSE_DB.'.'.'good'), 'p.good_id = g.id', array('model'=>'g.name'))
        ->join(array('c' => WAREHOUSE_DB.'.'.'good_color'), 'p.good_color = c.id', array('color'=>'c.name'));

        if (isset($params['dealer_id']) and $params['dealer_id'])
            $select->where('d.id = ?', $params['dealer_id']);



        if (isset($params['from']) && $params['from'] && isset($params['to']) && $params['to']) {
            $from = explode('/', $params['from']);
            $from = $from[2].'-'.$from[1].'-'.$from[0] . ' 00:00:00';
            $to = explode('/', $params['to']);
            $to = $to[2].'-'.$to[1].'-'.$to[0] . ' 23:59:59';       
        } else {
            return false;
        }

        if (isset($from) and $from)
            $select->where('p.out_date >= ?',$from);

        if (isset($to) and $to)
            $select->where('p.out_date <= ?', $to);

        $select->order('p.out_date', 'ASC');

        if ($limit)
            $select->limitPage($page, $limit);

        $result = $db->fetchAll($select);
        $total = $db->fetchOne("select FOUND_ROWS()");
        return $result;
    }

    function checkImei($imei, $timing_sales_id, &$info, $tool = null){
        /**
         * Note: by phamquocbuu
         * Check IMEI theo báº£ng imei_exception
         *      co trong bang exception thi uu tien lay trong do de check co fai imei demo hay khong
         * Check IMEI theo báº£ng imei
         *      KhÃ´ng tá»“n táº¡i -> thÃ´ng bÃ¡o
         *      Tá»“n táº¡i -> bÆ°á»›c sau
         * Check trong báº£ng acti
         *      NgÃ y acti trÆ°á»›c 30 ngÃ y -> tá»« chá»‘i cháº¥m cÃ´ng
         *      NgÆ°á»£c láº¡i, check trong pháº§n timing
         *          Tá»“n táº¡i
         *              So sÃ¡nh thá»i gian cháº¥m cÃ´ng
         *                  Cháº¥m hÆ¡n 30 ngÃ y -> tá»« chá»‘i
         *                  NgÆ°á»£c láº¡i -> thÃ´ng bÃ¡o
         *          KhÃ´ng tá»“n táº¡i -> cháº¥m cÃ´ng
         */

        // Check IMEI theo báº£ng imei
        $where = array();
        // $where[] = $QWebImei->getAdapter()->quoteInto('out_date > ?', 0);
        $where[] = $this->getAdapter()->quoteInto('into_date > ?', 0);
        $where[] = $this->getAdapter()->quoteInto('imei_sn = ?', $imei);

        $result = $this->fetchRow($where);
        if (!$result || !preg_match('/^[0-9]{15}$/', $imei)){
            //not existed in list sales out
            return 2;
        }

        $info['activated_at'] = $result['activated_date'];
        $info['out_date'] = $result['out_date'];
        //

        //Check trong báº£ng acti
        if (!$tool && $this->checkImei30DaysActivated($imei)) {
            return 4;
        }

        //check trong b?ng lock_imei
        // $QLockedImei = new Application_Model_LockedImei();
        // $where = $QLockedImei->getAdapter()->quoteInto('imei_log = ?', $imei);
        // $locked = $QLockedImei->fetchRow($where);

        // if ($locked) {
        //     return 17; // hàng c?m, éo tính
        // }

        //check trong báº£ng imei_demo_fpt
        $QDemo = new Application_Model_ImeiDemoFpt();
        $where = $QDemo->getAdapter()->quoteInto('imei_sn = ?', $imei);
        $locked = $QDemo->fetchRow($where);

        if ($locked) {
            return 7; // hÃ ng demo cá»§a fpt, Ã©o tÃ­nh
        }

        //check hÃ ng demo, for staff, for lending
        // check trong bang imei_exception
        $QImeiException = new Application_Model_ImeiException();
        $where = $QImeiException->getAdapter()->quoteInto('imei_sn = ?', $imei);
        $imei_exception = $QImeiException->fetchRow($where);
        //check hÃ ng demo
        if ($imei_exception){ // neu co trong exception thi uu tien lay trong do

            // kiem tra type cua exception: 1: xuat cho retailer; 2: xuat demo ...
            if ($imei_exception['type'] == 2) {
                return 7; // hÃ ng demo, Ã©o tÃ­nh
            }

            if ($imei_exception['type'] == 3) {
                return 8; // hÃ ng staff, Ã©o tÃ­nh
            }

            if ($imei_exception['type'] == 4) {
                return 9; // hÃ ng lending, Ã©o tÃ­nh
            }

        } else {
            //check trong báº£ng imei_demo_fpt
            $QDemo = new Application_Model_ImeiDemoFpt();
            $where = $QDemo->getAdapter()->quoteInto('imei_sn = ?', $imei);
            $locked = $QDemo->fetchRow($where);

            if ($locked) {
                return 7; // hÃ ng demo, Ã©o tÃ­nh
            }

            //check hÃ ng demo
            $QMarket = new Application_Model_Market();
            $where = array();
            $where[] = $QMarket->getAdapter()->quoteInto('sn = ?', $result['sales_sn']);
            $where[] = $QMarket->getAdapter()->quoteInto('outmysql_time >= ?', '2014-06-01 00:00:00');
            $market = $QMarket->fetchRow($where);

            if ($market) {
                if ($market['type'] == 2) {
                    return 7; // hÃ ng demo, Ã©o tÃ­nh
                }

                if ($market['type'] == 3) {
                    return 8; // hÃ ng staff, Ã©o tÃ­nh
                }

                if ($market['type'] == 4) {
                    return 9; // hÃ ng lending, Ã©o tÃ­nh
                }
            }
        }

        //check trong pháº§n timing
        $QTimingSale = new Application_Model_TimingSale();
        $where = array();
        $where[] = $QTimingSale->getAdapter()->quoteInto('id <> ?', $timing_sales_id);
        $where[] = $QTimingSale->getAdapter()->quoteInto('imei = ?', $imei);

        $ts = $QTimingSale->fetchRow($where);

        // IMEI Ä‘Ã£ cháº¥m cÃ´ng
        if ($ts){
            $QTiming = new Application_Model_Timing();
            $where = $QTiming->getAdapter()->quoteInto('id = ?', $ts['timing_id']);
            $result = $QTiming->fetchRow($where);

            $userStorage = Zend_Auth::getInstance()->getStorage()->read();
            $user_id = $userStorage->id;

            if ($result && $result['staff_id'] == $user_id) {
                $info['date'] = $result['from'];

                $QStore = new Application_Model_Store();
                $store_rs = $QStore->find($result['store']);
                $store = $store_rs->current();

                $info['store'] = $store['name'];

                return 1; // nÃ³ cháº¥m rá»“i
            } else {
                $QStaff = new Application_Model_Staff();
                $staff_rs = $QStaff->find($result['staff_id']);
                $staff = $staff_rs->current();

                $QStore = new Application_Model_Store();
                $store_rs = $QStore->find($result['store']);
                $store = $store_rs->current();

                if ($staff) {
                    $info['staff'] = $staff['firstname'] . ' ' . $staff['lastname'] . ' | ' . preg_replace('/oppomobile.vn/', '', $staff['email']) ;
                    $info['staff_id'] = $staff['id'];
                }

                if ($store)
                 $info['store'] = $store['name'];

             $info['date'] = $result['from'];
             $info['timing_sales_id'] = $ts['id'];

                // kiá»ƒm tra phÃ²ng trÆ°á»ng há»£p báº£ng imei acti ko cÃ³ imei nÃ y
                // bÃ¡n hÆ¡n 1 thÃ¡ng
             if ( strtotime(date('Y-m-d')) - strtotime($result['from']) > 30*24*3600 ) return 5;

                return 3; // tháº±ng khÃ¡c cháº¥m
            }
        }

        // IMEI chÆ°a cháº¥m cÃ´ng
        return 0;
    }

    function checkImei30DaysActivated($imei)
    {
        //Check trong báº£ng acti
        $QImeiActivation = new Application_Model_ImeiActivation();
        $where = array();
        $where[] = $QImeiActivation->getAdapter()->quoteInto('imei_sn LIKE ?', $imei);
        $tmp_date = date_sub(date_create(), new DateInterval('P30D'))->format('Y-m-d 00:00:00');
        $where[] = $QImeiActivation->getAdapter()->quoteInto('activated_at < ?', $tmp_date);
        $imei_activation = $QImeiActivation->fetchRow($where);

        if ($imei_activation) { // IMEI Ä‘Ã£ acti hÆ¡n 30 ngÃ y
            return 1;
        }

        return 0;
        //
    }

    function getImeiInfo($imei) {

        $db = Zend_Registry::get('db');

        $get = array(
            'good_id'   => 'g.id',
            'good_name' => 'g.name',
            'out_price' => 'g.price_9',
            'sales_price'=> 'g.price_3',
            'color_id'  => 'gc.id',
            'color_name'=> 'gc.name',
            'price'     => 'kpi.price',
            'distributor_id' => 'i.distributor_id',
        );

        $select = $db->select()
        ->from(array('i' => WAREHOUSE_DB.'.imei'), $get)
        ->join(array('g' => WAREHOUSE_DB.'.good'), 'i.good_id = g.id', array())
        ->join(array('gc'=> WAREHOUSE_DB.'.good_color'), 'i.good_color = gc.id', array())
        ->joinLeft(array('kpi' => 'good_kpi_log'),'kpi.good_id = i.good_id',array())
        ->where('i.imei_sn = ?', $imei);

        $result = $db->fetchRow($select);
        return $result;

    }

    // Report Online Sellout : List 
    function getOnlineSelloutByChannel($params) {

        $d1 = explode('/', $params['from']);
        $from = $d1[2].'-'.$d1[1].'-'.$d1[0];

        $d2 = explode('/', $params['to']);
        $to = $d2[2].'-'.$d2[1].'-'.$d2[0];

        $channel_list = array('LA','ST','SP','JD','OS','TS');

        $db = Zend_Registry::get('db');

        $get_01 = array(
            'channel'   => new Zend_Db_Expr(
                "   CASE 
                WHEN d.sales_ch = 'LA' THEN 'Lazada' 
                WHEN d.sales_ch = 'ST' THEN '11Street' 
                WHEN d.sales_ch = 'SP' THEN 'Shopee' 
                WHEN d.sales_ch = 'JD' THEN 'JD' 
                WHEN d.sales_ch = 'OS' THEN 'OPPO Official Store' 
                WHEN d.sales_ch = 'TS' THEN 'Thisshop'
                ELSE '-' 
                END
                "),

            'sellin'    => new Zend_Db_Expr("COUNT(i.imei_sn)"),
            'activated' => new Zend_Db_Expr("COUNT(CASE WHEN i.activated_date IS NOT NULL THEN i.imei_sn END)"),
        );

        $period_loop = ( (strtotime($to) - strtotime($from)) / (24*60*60) ) + 1;

        $get_02 = array();

        for ($i=0;$i<$period_loop;$i++) {
            $day =  date('Y-m-d', strtotime("+".$i." Day", strtotime($from)));
            $day_text =  date('d-M', strtotime("+".$i." Day", strtotime($from)));

            $get_02[$day_text] = new Zend_Db_Expr("COUNT(CASE WHEN i.out_date >= '".$day." 00:00:00' AND i.out_date <= '".$day." 23:59:59' THEN i.imei_sn END)");
        }

        $get = $get_01 + $get_02;

        $select = $db->select()
        ->from(array('d' => WAREHOUSE_DB.'.distributor'), $get)
        ->joinLeft(array('i' => WAREHOUSE_DB.'.imei'), 
            "   d.id = i.distributor_id 
            AND i.out_date >= '".$from." 00:00:00' 
            AND i.out_date <= '".$to." 23:59:59' 
            ", array())
        ->where('d.sales_ch IN (?)', $channel_list)
        ->group('channel')
        ->order('channel ASC');

        // Filter Model 
        if (isset($params['good_id']) && $params['good_id']) {
            if (is_array($params['good_id']) && count($params['good_id']))
                $select->where('i.good_id IN (?)', $params['good_id']);
            elseif (is_numeric($params['good_id']))
                $select->where('i.good_id = ?', intval($params['good_id']));
            else
                $select->where('1=0', 1);
        }

        // Filter Color 
        if (isset($params['color_id']) && $params['color_id']) {
            if (is_array($params['color_id']) && count($params['color_id']))
                $select->where('i.good_color IN (?)', $params['color_id']);
            elseif (is_numeric($params['color_id']))
                $select->where('i.good_color = ?', intval($params['color_id']));
            else
                $select->where('1=0', 1);
        }

        // check Permission AM
        if ( isset($params['am_id']) && $params['am_id'] ) {
            $QAm = new Application_Model_Am();
            $list_org = $QAm->get_cache($params['am_id']);
            $list_org = isset($list_org['store_type']) && is_array($list_org['store_type']) ? $list_org['store_type'] : array();

            $chk_online = array_intersect($list_org, array(46));

            // AM Online 
            if ( count($chk_online) > 0 ) { $select->where('1=1', 1); }
            else { $select->where('1=0', 1); } 
        }

        $result = $db->fetchAll($select);
        return $result;

    }

    function checkHeroProduct($product_id){
        $db =Zend_Registry::get('db');

        $select = $db->select()
        ->from(array('g' => WAREHOUSE_DB.'.good'),array('g.*'))
        ->where('g.hero_product = ?',1)
        ->where('g.id =?',$product_id);

        $result = $db->fetchRow($select);
        return $result;
    }

    function InventoryTurnOverByRGM($params)
    {

        $tmp_from = explode('/', $params['from']);
        $tmp_to = explode('/', $params['to']);
        $tmp_date = explode('/', $params['date']);

        $from = $tmp_from[2]."-".$tmp_from[1]."-".$tmp_from[0];
        $to = $tmp_to[2]."-".$tmp_to[1]."-".$tmp_to[0];
        $date = $tmp_date[2]."-".$tmp_date[1]."-".$tmp_date[0];

        $date_count = (strtotime($to) - strtotime($from))/  ( 60 * 60 * 24 );

        $inventory_data = date('Y-m-d');

        $db = Zend_Registry::get('db');

        if(isset($params['invertory_by']) && $params['invertory_by'] == 1){ // timing

            // Realme Pc Timing
            $select_sellout_01 = $db->select()
            ->from(array('t' => HR_DB.'.timing'),array('COUNT(t.id)'))
            ->joinLeft(array('ts' => HR_DB.'.timing_sale'),'ts.timing_id = t.id',array())
            ->joinLeft(array('g' => WAREHOUSE_DB.'.good'),'g.id = ts.product_id',array())
            ->joinLeft(array('s' => HR_DB.'.store'),'s.id = t.store',array())
            ->where('t.created_by != 6535')
            ->where('t.created_by != 18')
            ->where('s.regional_market = rm.id')
            ->where('ts.time_add >= ?',$from.' 00:00:00')
            ->where('ts.time_add <= ?', $to.' 23:59:59'); // check


            // Oppo Pc timing
            $select_sellout_02 = $db->select()
            ->from(array('t' => HR_DB.'.timing'),array('COUNT(t.id)'))
            ->joinLeft(array('ts' => HR_DB.'.timing_sale'),'ts.timing_id = t.id',array())
            ->joinLeft(array('i' => WAREHOUSE_DB.'.imei'),'i.imei_sn = ts.imei',array())
            ->joinLeft(array('g' => WAREHOUSE_DB.'.good'),'g.id = ts.product_id',array())
            ->joinLeft(array('d' => WAREHOUSE_DB.'.distributor'),'i.distributor_id = d.id',array())
            ->where('t.created_by = 6535')
            ->where('d.region = rm.id')
            ->where('ts.time_add >= ?',$from.' 00:00:00')
            ->where('ts.time_add <= ?', $to.' 23:59:59'); // check


            $select_total_sale = $db->select()
            ->from(array('t' => HR_DB.'.timing'),array('COUNT(t.id)'))
            ->joinLeft(array('ts' => HR_DB.'.timing_sale'),'ts.timing_id = t.id',array())
            ->joinLeft(array('i' => WAREHOUSE_DB.'.imei'),'i.imei_sn = ts.imei',array())
            ->joinLeft(array('g' => WAREHOUSE_DB.'.good'),'g.id = ts.product_id',array())
            ->joinLeft(array('d' => WAREHOUSE_DB.'.distributor'),'i.distributor_id = d.id',array())
            ->where('t.created_by != 18')
            ->where('ts.time_add >= ?',$from.' 00:00:00')
            ->where('ts.time_add <= ?', $to.' 23:59:59');// check

            if (isset($params['good']) && $params['good']) {
                if (is_array($params['good']) && count($params['good']))
                    $select_sellout_01->where('g.id IN (?)', $params['good']);
                elseif (is_numeric($params['good'])) 
                    $select_sellout_01->where('g.id = ?', intval($params['good']));
                else
                    $select_sellout_01->where('1=0', 1);
            }

            if (isset($params['good']) && $params['good']) {
                if (is_array($params['good']) && count($params['good']))
                    $select_sellout_02->where('g.id IN (?)', $params['good']);
                elseif (is_numeric($params['good'])) 
                    $select_sellout_02->where('g.id = ?', intval($params['good']));
                else
                    $select_sellout_02->where('1=0', 1);
            }

            if (isset($params['good']) && $params['good']) {
                if (is_array($params['good']) && count($params['good']))
                    $select_total_sale->where('g.id IN (?)', $params['good']);
                elseif (is_numeric($params['good'])) 
                    $select_total_sale->where('g.id = ?', intval($params['good']));
                else
                    $select_total_sale->where('1=0', 1);
            }

            if (isset($params['brand']) && $params['brand']) {
                if (is_array($params['brand']) && count($params['brand']))
                    $select_sellout_01->where('g.brand_id IN (?)', $params['brand']);
                elseif (is_numeric($params['brand']))
                    $select_sellout_01->where('g.brand_id = ?', intval($params['brand']));
                else
                    $select_sellout_01->where('1=0', 1);
            }

            if (isset($params['brand']) && $params['brand']) {
                if (is_array($params['brand']) && count($params['brand']))
                    $select_sellout_02->where('g.brand_id IN (?)', $params['brand']);
                elseif (is_numeric($params['brand']))
                    $select_sellout_02->where('g.brand_id = ?', intval($params['brand']));
                else
                    $select_sellout_02->where('1=0', 1);
            }


            if (isset($params['brand']) && $params['brand']) {
                if (is_array($params['brand']) && count($params['brand']))
                    $select_total_sale->where('g.brand_id IN (?)', $params['brand']);
                elseif (is_numeric($params['brand']))
                    $select_total_sale->where('g.brand_id = ?', intval($params['brand']));
                else
                    $select_total_sale->where('1=0', 1);
            }


            if(isset($params['rd_id']) and $params['rd_id']){ // Adjus
                $select_total_sale->joinLeft(array('rm' => HR_DB.'.regional_market'),'rm.id = d.region',array());
                $select_total_sale->joinLeft(array('asm' => HR_DB.'.asm'),'asm.area_id = rm.area_id',array());
                $select_total_sale->where('asm.staff_id =?',$params['rd_id']);
            }
        }

        if(isset($params['invertory_by']) && $params['invertory_by'] == 2){ // activate

            // Start Case 1 //

            $select_sellout_01 = $db->select()
            ->from(array('i' => WAREHOUSE_DB.'.imei'),array('COUNT(i.imei_sn)'))
            ->joinLeft(array('d' => WAREHOUSE_DB.'.distributor'),'d.id = i.distributor_id',array())
            ->joinLeft(array('g' => WAREHOUSE_DB.'.good'),'g.id = i.good_id',array())
            ->where('i.distributor_id IS NOT NULL')
            ->where('d.region = rm.id')
            ->where('i.activated_date >=?',$from.' 00:00:00')
            ->where('i.activated_date <=?',$to.' 23:59:59'); // check

            if (isset($params['good']) && $params['good']) {
                if (is_array($params['good']) && count($params['good']))
                    $select_sellout_01->where('g.id IN (?)', $params['good']);
                elseif (is_numeric($params['good'])) 
                    $select_sellout_01->where('g.id = ?', intval($params['good']));
                else
                    $select_sellout_01->where('1=0', 1);
            }

            if (isset($params['brand']) && $params['brand']) {
                if (is_array($params['brand']) && count($params['brand']))
                    $select_sellout_01->where('g.brand_id IN (?)', $params['brand']);
                elseif (is_numeric($params['brand']))
                    $select_sellout_01->where('g.brand_id = ?', intval($params['brand']));
                else
                    $select_sellout_01->where('1=0', 1);
            }

            // End Case 1 //

            // Start Case 2 //

            $select_sellout_02 = $db->select()
            ->from(array('i' => WAREHOUSE_DB.'.imei'),array('COUNT(i.imei_sn)'))
            ->joinLeft(array('w' => WAREHOUSE_DB.'.warehouse'),'w.id = i.warehouse_id',array())
            ->joinLeft(array('g' => WAREHOUSE_DB.'.good'),'g.id = i.good_id',array())
            ->where('i.distributor_id IS NULL')
            ->where('w.province_id = rm.id')
            ->where('i.activated_date >=?',$from.' 00:00:00')
            ->where('i.activated_date <=?',$to.' 23:59:59'); // check

            if (isset($params['good']) && $params['good']) {
                if (is_array($params['good']) && count($params['good']))
                    $select_sellout_02->where('g.id IN (?)', $params['good']);
                elseif (is_numeric($params['good'])) 
                    $select_sellout_02->where('g.id = ?', intval($params['good']));
                else
                    $select_sellout_02->where('1=0', 1);
            }

            if (isset($params['brand']) && $params['brand']) {
                if (is_array($params['brand']) && count($params['brand']))
                    $select_sellout_02->where('g.brand_id IN (?)', $params['brand']);
                elseif (is_numeric($params['brand']))
                    $select_sellout_02->where('g.brand_id = ?', intval($params['brand']));
                else
                    $select_sellout_02->where('1=0', 1);
            }

            // End Case 2 //

            // Start Case 3 //

            $select_total_sale = $db->select()
            ->from(array('i' => WAREHOUSE_DB.'.imei'),array('COUNT(i.imei_sn)'))
            ->joinLeft(array('d' => WAREHOUSE_DB.'.distributor'),'d.id = i.distributor_id',array())
            ->joinLeft(array('w' => WAREHOUSE_DB.'.warehouse'),'w.id = i.warehouse_id',array())
            ->joinLeft(array('g' => WAREHOUSE_DB.'.good'),'g.id = i.good_id',array())
            ->where('w.area_id > 0')
            ->where('i.activated_date >=?',$from.' 00:00:00')
            ->where('i.activated_date <=?',$to.' 23:59:59'); // check

            if(isset($params['rd_id']) and $params['rd_id']){ // Adjus
                $select_total_sale->joinLeft(array('rm' => HR_DB.'.regional_market'),'rm.id = d.region',array());
                $select_total_sale->joinLeft(array('asm' => HR_DB.'.asm'),'asm.area_id = rm.area_id',array());
                $select_total_sale->where('asm.staff_id =?',$params['rd_id']);
            }

            if (isset($params['good']) && $params['good']) {
                if (is_array($params['good']) && count($params['good']))
                    $select_total_sale->where('g.id IN (?)', $params['good']);
                elseif (is_numeric($params['good'])) 
                    $select_total_sale->where('g.id = ?', intval($params['good']));
                else
                    $select_total_sale->where('1=0', 1);
            }

            
            if (isset($params['brand']) && $params['brand']) {
                if (is_array($params['brand']) && count($params['brand']))
                    $select_total_sale->where('g.brand_id IN (?)', $params['brand']);
                elseif (is_numeric($params['brand']))
                    $select_total_sale->where('g.brand_id = ?', intval($params['brand']));
                else
                    $select_total_sale->where('1=0', 1);
            }

            // End Case 3 //


        }

        $select_distributor_stock = $db->select()
        ->from(array('d' => WAREHOUSE_DB.'.distributor'),array('COUNT(i.imei_sn)'))
        ->joinLeft(array('i' => WAREHOUSE_DB.'.imei'),'i.distributor_id = d.id',array())
        ->joinLeft(array('g' => WAREHOUSE_DB.'.good'),'g.id = i.good_id',array())
        ->where('i.old_data IS NULL')
        ->where('d.del IS NULL')
        ->where('d.region != 8413')
        ->where('i.imei_sn NOT IN (SELECT imei FROM hr.timing_sale AS ts WHERE ts.time_add <="'.$date.' 23:59:59")')
        ->where('i.out_date <=?',$date.' 23:59:59')
        ->where('d.region = rm.id');

        // Case 1 //

        if (isset($params['good']) && $params['good']) {
            if (is_array($params['good']) && count($params['good']))
                $select_distributor_stock->where('g.id IN (?)', $params['good']);
            elseif (is_numeric($params['good'])) 
                $select_distributor_stock->where('g.id = ?', intval($params['good']));
            else
                $select_distributor_stock->where('1=0', 1);
        }

        if (isset($params['brand']) && $params['brand']) {
            if (is_array($params['brand']) && count($params['brand']))
                $select_distributor_stock->where('g.brand_id IN (?)', $params['brand']);
            elseif (is_numeric($params['brand']))
                $select_distributor_stock->where('g.brand_id = ?', intval($params['brand']));
            else
                $select_distributor_stock->where('1=0', 1);
        }

        // End Case 1 //

        $select_warehouse_stock = $db->select()
        ->from(array('dsn' => WAREHOUSE_DB.'.daily_stock_new'),array('SUM(dsn.imei_storage)'))
        ->joinLeft(array('w' => WAREHOUSE_DB.'.warehouse'),'w.id = dsn.warehouse_id',array())
        ->joinLeft(array('g' => WAREHOUSE_DB.'.good'),'g.id = dsn.good_id',array())
        ->where('w.province_id = rm.id')
        ->where('dsn.stock_date = ?',$date);

        // Case 2 //

        if (isset($params['good']) && $params['good']) {
            if (is_array($params['good']) && count($params['good']))
                $select_warehouse_stock->where('g.id IN (?)', $params['good']);
            elseif (is_numeric($params['good'])) 
                $select_warehouse_stock->where('g.id = ?', intval($params['good']));
            else
                $select_warehouse_stock->where('1=0', 1);
        }

        if (isset($params['brand']) && $params['brand']) {
            if (is_array($params['brand']) && count($params['brand']))
                $select_warehouse_stock->where('g.brand_id IN (?)', $params['brand']);
            elseif (is_numeric($params['brand']))
                $select_warehouse_stock->where('g.brand_id = ?', intval($params['brand']));
            else
                $select_warehouse_stock->where('1=0', 1);
        }

        // End Case 2 //


        $select_total_dis_stock = $db->select()
        ->from(array('i' => WAREHOUSE_DB.'.imei'),array('COUNT(i.imei_sn)'))
        ->joinLeft(array('d' => WAREHOUSE_DB.'.distributor'),'d.id = i.distributor_id',array())
        ->joinLeft(array('g' => WAREHOUSE_DB.'.good'),'g.id = i.good_id',array())
        ->joinLeft(array('rm' => HR_DB.'.regional_market'),'rm.id = d.region',array())
        ->joinLeft(array('a' => HR_DB.'.area'),'a.id = rm.area_id',array())
        ->where('d.region != 8413')
        ->where('i.imei_sn NOT IN (SELECT imei FROM hr.timing_sale AS ts WHERE ts.time_add <="'.$date.' 23:59:59")')
        ->where('i.old_data IS NULL')
        ->where('d.del IS NULL')
        ->where('i.out_date <=?',$date.' 23:59:59');

        // Case 3 //

        if (isset($params['good']) && $params['good']) {
            if (is_array($params['good']) && count($params['good']))
                $select_total_dis_stock->where('g.id IN (?)', $params['good']);
            elseif (is_numeric($params['good'])) 
                $select_total_dis_stock->where('g.id = ?', intval($params['good']));
            else
                $select_total_dis_stock->where('1=0', 1);
        }

        if (isset($params['brand']) && $params['brand']) {
            if (is_array($params['brand']) && count($params['brand']))
                $select_total_dis_stock->where('g.brand_id IN (?)', $params['brand']);
            elseif (is_numeric($params['brand']))
                $select_total_dis_stock->where('g.brand_id = ?', intval($params['brand']));
            else
                $select_total_dis_stock->where('1=0', 1);
        }

        // End Case 3 //


        $select_total_wh_stock = $db->select()
        ->from(array('dsn' => WAREHOUSE_DB.'.daily_stock_new'),array('SUM(dsn.imei_storage)'))
        ->joinLeft(array('w' => WAREHOUSE_DB.'.warehouse'),'w.id = dsn.warehouse_id',array())
        ->joinLeft(array('g' => WAREHOUSE_DB.'.good'),'g.id = dsn.good_id',array())
        ->where('w.area_id > ?',0)
        ->where('dsn.stock_date = ?',$date);

        // Case 4 //

        if (isset($params['good']) && $params['good']) {
            if (is_array($params['good']) && count($params['good']))
                $select_total_wh_stock->where('g.id IN (?)', $params['good']);
            elseif (is_numeric($params['good'])) 
                $select_total_wh_stock->where('g.id = ?', intval($params['good']));
            else
                $select_total_wh_stock->where('1=0', 1);
        }

        if (isset($params['brand']) && $params['brand']) {
            if (is_array($params['brand']) && count($params['brand']))
                $select_total_wh_stock->where('g.brand_id IN (?)', $params['brand']);
            elseif (is_numeric($params['brand']))
                $select_total_wh_stock->where('g.brand_id = ?', intval($params['brand']));
            else
                $select_total_wh_stock->where('1=0', 1);
        }

        // End Case 4 //

        if(isset($params['rd_id']) and $params['rd_id']){
            $select_total_dis_stock->joinleft(array('asm' => HR_DB.'.asm'),'asm.area_id = a.id',array());
            $select_total_dis_stock->where('asm.staff_id =?',$params['rd_id']);

            $select_total_wh_stock->joinleft(array('asm' => HR_DB.'.asm'),'asm.area_id = w.area_id',array());
            $select_total_wh_stock->where('asm.staff_id =?',$params['rd_id']);

        }


            if(isset($params['stock_by']) && $params['stock_by'] == 2){ // Not Activate
                $select_distributor_stock->where('i.activated_date IS NULL');
                $select_total_dis_stock->where('i.activated_date IS NULL');

            }elseif(isset($params['stock_by']) && $params['stock_by'] == 3){ // Not Timing

                $select_wh_stock->where('i2.imei_sn NOT IN (SELECT imei FROM hr.timing_sale)');
                $select_total_wh_stock->where('i2.imei_sn NOT IN (SELECT imei FROM hr.timing_sale)');

            }elseif(isset($params['stock_by']) && $params['stock_by'] == 4){ // Not Activate and Not Timing

                $select_distributor_stock->where('i.activated_date IS NULL');
                $select_total_dis_stock->where('i.activated_date IS NULL');

            }



            // if(isset($params['good_id']) && $params['good_id']){
            //     $select_distributor_stock->where('i.good_id = ?',$params['good_id']);
            //     $select_warehouse_stock->where('dsn.good_id =?',$params['good_id']);
            //     $select_total_wh_stock->where('dsn.good_id =?',$params['good_id']);
            //     $select_total_dis_stock->where('i.good_id =?',$params['good_id']);
            // }


            $get = array(
                'area_id'   => 'a.id',
                'area_name' => 'a.name',
                'provience_name' => 'rm.name',
                'stock_name' => '',
                'sellout'   => new Zend_Db_Expr("(".$select_sellout_01.")+(".$select_sellout_02.")"),
                'total_sellout' => new Zend_Db_Expr("(".$select_total_sale.")"),
                'distributor_stock' => new Zend_Db_Expr("(".$select_distributor_stock.")"),
                'total_distributor_stock'   => new Zend_Db_Expr("(".$select_total_dis_stock.")"),
                'warehouse_stock'   => new Zend_Db_Expr("(".$select_warehouse_stock.")"),
                'total_warehouse_stock' => new Zend_Db_Expr("(".$select_total_wh_stock.")")
            );

            $select = $db->select()
            ->from(array('a' => HR_DB.'.area'),$get)
            ->join(array('rm' => HR_DB.'.regional_market'),'rm.area_id = a.id',array())
            ->group('rm.id')
            ->order('a.name ASC');

            if(isset($params['rd_id']) and $params['rd_id']){
                $select->joinLeft(array('asm' => HR_DB.'.asm'),'asm.area_id = a.id',array());
                $select->where('asm.staff_id =?',$params['rd_id']);
            }



            // echo $select; die;

            $result = $db->fetchAll($select);
            return $result;

        }


        public function InventoryTurnOverByModel($params)
        {

            $tmp_from = explode('/', $params['from']);
            $tmp_to = explode('/', $params['to']);
            $tmp_date = explode('/', $params['date']);

            $from = $tmp_from[2]."-".$tmp_from[1]."-".$tmp_from[0];
            $to = $tmp_to[2]."-".$tmp_to[1]."-".$tmp_to[0];
            $date = $tmp_date[2]."-".$tmp_date[1]."-".$tmp_date[0];

            $date_count = (strtotime($to) - strtotime($from))/  ( 60 * 60 * 24 );

            $inventory_data = date('Y-m-d');

            $db = Zend_Registry::get('db');

            if(isset($params['invertory_by']) && $params['invertory_by'] == 1){ 

                $sub_select_sellout_01 = $db->select()
                ->from(array('t' => HR_DB.'.timing'),array('COUNT(t.id)'))
                ->joinLeft(array('ts' => HR_DB.'.timing_sale'),'ts.timing_id = t.id',array())
                ->where('t.created_by != 18')
                ->where('ts.product_id = g.id')
                ->where('ts.time_add >= ?',$from.' 00:00:00')
                ->where('ts.time_add <= ?', $to.' 23:59:59');

                $sub_select_sellout_02 = 0;

                $select_total_sale = $db->select()
                ->from(array('t' => HR_DB.'.timing'),array('COUNT(t.id)'))
                ->joinLeft(array('ts' => HR_DB.'.timing_sale'),'ts.timing_id = t.id',array())
                ->joinLeft(array('g' => WAREHOUSE_DB.'.good'),'g.id = ts.product_id',array())
                ->where('t.created_by != 18')
                ->where('ts.time_add >= ?',$from.' 00:00:00')
                ->where('ts.time_add <= ?', $to.' 23:59:59');

                // Case 2 //

                if (isset($params['good']) && $params['good']) {
                    if (is_array($params['good']) && count($params['good']))
                        $select_total_sale->where('g.id IN (?)', $params['good']);
                    elseif (is_numeric($params['good'])) 
                        $select_total_sale->where('g.id = ?', intval($params['good']));
                    else
                        $select_total_sale->where('1=0', 1);
                }

                if (isset($params['brand']) && $params['brand']) {
                    if (is_array($params['brand']) && count($params['brand']))
                        $select_total_sale->where('g.brand_id IN (?)', $params['brand']);
                    elseif (is_numeric($params['brand']))
                        $select_total_sale->where('g.brand_id = ?', intval($params['brand']));
                    else
                        $select_total_sale->where('1=0', 1);
                }

                // End Case 2 //

                // if(isset($params['good_id']) && $params['good_id']){
                //     $select_total_sale->where('ts.product_id =?',$params['good_id']);
                // }

                // if(isset($params['good_id']) && $params['good_id']){
                //     $sub_select_sellout_01->where('ts.product_id =?',$params['good_id']);
                // }

            }

            if(isset($params['invertory_by']) && $params['invertory_by'] == 2){ // activate

                $sub_select_sellout_01 = $db->select()
                ->from(array('i' => WAREHOUSE_DB.'.imei'),array('COUNT(i.imei_sn)'))
                ->joinLeft(array('g' => WAREHOUSE_DB.'.good'),'g.id = i.good_id',array())
                ->where('i.distributor_id IS NOT NULL')
                ->where('i.good_id = g.id')
                ->where('i.activated_date >=?',$from.' 00:00:00')
                ->where('i.activated_date <=?',$to.' 23:59:59');

                // Case 3 //

                if (isset($params['good']) && $params['good']) {
                    if (is_array($params['good']) && count($params['good']))
                        $sub_select_sellout_01->where('g.id IN (?)', $params['good']);
                    elseif (is_numeric($params['good'])) 
                        $sub_select_sellout_01->where('g.id = ?', intval($params['good']));
                    else
                        $sub_select_sellout_01->where('1=0', 1);
                }

                if (isset($params['brand']) && $params['brand']) {
                    if (is_array($params['brand']) && count($params['brand']))
                        $sub_select_sellout_01->where('g.brand_id IN (?)', $params['brand']);
                    elseif (is_numeric($params['brand']))
                        $sub_select_sellout_01->where('g.brand_id = ?', intval($params['brand']));
                    else
                        $sub_select_sellout_01->where('1=0', 1);
                }

                // End Case 3 //

                $sub_select_sellout_02 = $db->select()
                ->from(array('i' => WAREHOUSE_DB.'.imei'),array('COUNT(i.imei_sn)'))
                ->joinLeft(array('w' => WAREHOUSE_DB.'.warehouse'),'w.id = i.warehouse_id',array())
                ->joinLeft(array('g' => WAREHOUSE_DB.'.good'),'g.id = i.good_id',array())
                ->where('w.area_id > 0')
                ->where('i.distributor_id IS NULL')
                ->where('i.good_id = g.id')
                ->where('i.activated_date >=?',$from.' 00:00:00')
                ->where('i.activated_date <=?',$to.' 23:59:59');

                // Case 4 //

                if (isset($params['good']) && $params['good']) {
                    if (is_array($params['good']) && count($params['good']))
                        $sub_select_sellout_02->where('g.id IN (?)', $params['good']);
                    elseif (is_numeric($params['good'])) 
                        $sub_select_sellout_02->where('g.id = ?', intval($params['good']));
                    else
                        $sub_select_sellout_02->where('1=0', 1);
                }

                if (isset($params['brand']) && $params['brand']) {
                    if (is_array($params['brand']) && count($params['brand']))
                        $sub_select_sellout_02->where('g.brand_id IN (?)', $params['brand']);
                    elseif (is_numeric($params['brand']))
                        $sub_select_sellout_02->where('g.brand_id = ?', intval($params['brand']));
                    else
                        $sub_select_sellout_02->where('1=0', 1);
                }

                // End Case 4 //

                $select_total_sale = $db->select()
                ->from(array('i' => WAREHOUSE_DB.'.imei'),array('COUNT(i.imei_sn)'))
                ->joinLeft(array('w' => WAREHOUSE_DB.'.warehouse'),'w.id = i.warehouse_id',array())
                ->joinLeft(array('g' => WAREHOUSE_DB.'.good'),'g.id = i.good_id',array())
                ->where('i.activated_date >=?',$from.' 00:00:00')
                ->where('i.activated_date <=?',$to.' 23:59:59');

                // Case 5 //

                if (isset($params['good']) && $params['good']) {
                    if (is_array($params['good']) && count($params['good']))
                        $select_total_sale->where('g.id IN (?)', $params['good']);
                    elseif (is_numeric($params['good'])) 
                        $select_total_sale->where('g.id = ?', intval($params['good']));
                    else
                        $select_total_sale->where('1=0', 1);
                }

                if (isset($params['brand']) && $params['brand']) {
                    if (is_array($params['brand']) && count($params['brand']))
                        $select_total_sale->where('g.brand_id IN (?)', $params['brand']);
                    elseif (is_numeric($params['brand']))
                        $select_total_sale->where('g.brand_id = ?', intval($params['brand']));
                    else
                        $select_total_sale->where('1=0', 1);
                }

                // End Case 5 //

                // if(isset($params['good_id']) && $params['good_id']){
                //     $sub_select_sellout->where('i.good_id =?',$params['good_id']);
                //     $select_total_sale->where('i.good_id =?',$params['good_id']);
                // }

            }


            $select_distributor_stock = $db->select()
            ->from(array('i' => WAREHOUSE_DB.'.imei'),array('COUNT(i.imei_sn)'))
            ->joinLeft(array('d' => WAREHOUSE_DB.'.distributor'),'d.id = i.distributor_id',array())

            ->where('d.region != 8413')
            ->where('i.old_data IS NULL')
            ->where('d.del IS NULL')
            ->where('i.imei_sn NOT IN (SELECT imei FROM hr.timing_sale AS ts WHERE ts.time_add <="'.$date.' 23:59:59")')
            ->where('i.out_date <=?',$date.' 23:59:59')
            ->where('i.good_id = g.id');

            $select_warehouse_stock = $db->select()
            ->from(array('dsn' => WAREHOUSE_DB.'.daily_stock_new'),array('SUM(dsn.imei_storage)'))
            ->joinLeft(array('w' => WAREHOUSE_DB.'.warehouse'),'w.id = dsn.warehouse_id',array())

            ->where('w.area_id > ?',0)
            ->where('dsn.good_id = g.id')
            ->where('dsn.stock_date = ?',$date);

            $select_total_dis_stock = $db->select()
            ->from(array('i' => WAREHOUSE_DB.'.imei'),array('COUNT(i.imei_sn)'))
            ->joinLeft(array('d' => WAREHOUSE_DB.'.distributor'),'d.id = i.distributor_id',array())
            ->joinLeft(array('g' => WAREHOUSE_DB.'.good'),'g.id = i.good_id',array())

            ->where('d.region != 8413')
            ->where('i.imei_sn NOT IN (SELECT imei FROM hr.timing_sale AS ts WHERE ts.time_add <="'.$date.' 23:59:59")')
            ->where('i.old_data IS NULL')
            ->where('d.del IS NULL')
            ->where('i.out_date <=?',$date.' 23:59:59');

            // Case 6 //

            if (isset($params['good']) && $params['good']) {
                if (is_array($params['good']) && count($params['good']))
                    $select_total_dis_stock->where('g.id IN (?)', $params['good']);
                elseif (is_numeric($params['good'])) 
                    $select_total_dis_stock->where('g.id = ?', intval($params['good']));
                else
                    $select_total_dis_stock->where('1=0', 1);
            }

            if (isset($params['brand']) && $params['brand']) {
                if (is_array($params['brand']) && count($params['brand']))
                    $select_total_dis_stock->where('g.brand_id IN (?)', $params['brand']);
                elseif (is_numeric($params['brand']))
                    $select_total_dis_stock->where('g.brand_id = ?', intval($params['brand']));
                else
                    $select_total_dis_stock->where('1=0', 1);
            }

            // End Case 6 //

            $select_total_wh_stock = $db->select()
            ->from(array('dsn' => WAREHOUSE_DB.'.daily_stock_new'),array('SUM(dsn.imei_storage)'))
            ->joinLeft(array('w' => WAREHOUSE_DB.'.warehouse'),'w.id = dsn.warehouse_id',array())
            ->joinLeft(array('g' => WAREHOUSE_DB.'.good'),'g.id = dsn.good_id',array())

            ->where('w.area_id > ?',0)
            ->where('dsn.stock_date = ?',$date);

            // Case 7 //

            if (isset($params['good']) && $params['good']) {
                if (is_array($params['good']) && count($params['good']))
                    $select_total_dis_stock->where('g.id IN (?)', $params['good']);
                elseif (is_numeric($params['good'])) 
                    $select_total_dis_stock->where('g.id = ?', intval($params['good']));
                else
                    $select_total_dis_stock->where('1=0', 1);
            }

            if (isset($params['brand']) && $params['brand']) {
                if (is_array($params['brand']) && count($params['brand']))
                    $select_total_dis_stock->where('g.brand_id IN (?)', $params['brand']);
                elseif (is_numeric($params['brand']))
                    $select_total_dis_stock->where('g.brand_id = ?', intval($params['brand']));
                else
                    $select_total_dis_stock->where('1=0', 1);
            }

            // End Case 7 //

            if(isset($params['stock_by']) && $params['stock_by'] == 2){
                $select_distributor_stock->where('i.activated_date IS NULL');
                $select_total_dis_stock->where('i.activated_date IS NULL');

            }elseif(isset($params['stock_by']) && $params['stock_by'] == 3){
                $select_wh_stock->where('i2.imei_sn NOT IN (SELECT imei FROM hr.timing_sale)');
                $select_total_wh_stock->where('i2.imei_sn NOT IN (SELECT imei FROM hr.timing_sale)');

            }elseif(isset($params['stock_by']) && $params['stock_by'] == 4){
                $select_distributor_stock->where('i.activated_date IS NULL');
                $select_total_dis_stock->where('i.activated_date IS NULL');
            }

            // if(isset($params['good_id']) && $params['good_id']){
                // $select_total_dis_stock->where('i.good_id =?',$params['good_id']);
                // $select_total_wh_stock->where('dsn.good_id =?',$params['good_id']);
            // }


            $get = array(
                'good_id'          => 'g.id',

                'model_name'        => 'g.name',

                'sellout'           => new Zend_Db_Expr("(".$sub_select_sellout_01.")+(".$sub_select_sellout_02.")"),

                'total_sellout'     => new Zend_Db_Expr("(".$select_total_sale.")"),

                'distributor_stock' => new Zend_Db_Expr("(".$select_distributor_stock.")"),

                'warehouse_stock'   => new Zend_Db_Expr("(".$select_warehouse_stock.")"),

                'total_distributor_stock'   => new Zend_Db_Expr("(".$select_total_dis_stock.")"),

                'total_warehouse_stock' => new Zend_Db_Expr("(".$select_total_wh_stock.")")   

            );

            $select = $db->select()
            ->from(array('g' => WAREHOUSE_DB.'.good'),$get)
            ->where('g.cat_id = ?',$params['cat_id'])
            ->where('g.product_status =?',1);

             if (isset($params['good']) && $params['good']) {
                if (is_array($params['good']) && count($params['good']))
                    $select->where('g.id IN (?)', $params['good']);
                elseif (is_numeric($params['good'])) 
                    $select->where('g.id = ?', intval($params['good']));
                else
                    $select->where('1=0', 1);
            }

            if (isset($params['brand']) && $params['brand']) {
                if (is_array($params['brand']) && count($params['brand']))
                    $select->where('g.brand_id IN (?)', $params['brand']);
                elseif (is_numeric($params['brand']))
                    $select->where('g.brand_id = ?', intval($params['brand']));
                else
                    $select->where('1=0', 1);
            }


            // if(isset($params['good_id']) && $params['good_id']){
            //     $select->where('g.id =?',$params['good_id']);
            // }

            // echo $select; die;

            $result = $db->fetchAll($select);
            return $result;


        }

        public function OnShelfRateDistributor($params){
            $db = Zend_Registry::get('db');

            $sub_select_stock = $db->select()
            ->from(array('d' => WAREHOUSE_DB.'.distributor'),array('d.id'))
            ->join(array('i' => WAREHOUSE_DB.'.imei'),'i.distributor_id = d.id',array())
            ->join(array('rm' => HR_DB.'.regional_market'),'rm.id = d.region',array())
            ->joinLeft(array('g' => WAREHOUSE_DB.'.good'),'i.good_id = g.id',array())
            ->joinLeft(array('a' => HR_DB.'.area'),'a.id = rm.area_id',array())

            ->where('d.del IS NULL')
            ->where('i.imei_sn NOT IN (SELECT imei FROM hr.timing_sale)')
            ->where('i.imei_sn NOT IN (SELECT imei FROM hr.timing_control)')
            ->group('d.id');

            // Fillter Model
            if (isset($params['good']) && $params['good']) {
                if (is_array($params['good']) && count($params['good']))
                    $sub_select_stock->where('g.id IN (?)', $params['good']);
                elseif (is_numeric($params['good']))
                    $sub_select_stock->where('g.id = ?', intval($params['good']));
                else
                    $sub_select_stock->where('1=0', 1);
            }

            //Fillter Brand
            if (isset($params['brand']) && $params['brand']) {
                if (is_array($params['brand']) && count($params['brand']))
                    $sub_select_stock->where('g.brand_id IN (?)', $params['brand']);
                elseif (is_numeric($params['brand']))
                    $sub_select_stock->where('g.brand_id = ?', intval($params['brand']));
                else
                    $sub_select_stock->where('1=0', 1);
            }

            // Fillter Area
            if (isset($params['area_id']) && $params['area_id']) {
                if (is_array($params['area_id']) && count($params['area_id']))
                    $sub_select_stock->where('a.id IN (?)', $params['area_id']);
                elseif (is_numeric($params['area_id']))
                    $sub_select_stock->where('a.id = ?', intval($params['area_id']));
                else
                    $sub_select_stock->where('1=0', 1);
            }

            // Imei Status No Activated and Not timing
            if(isset($params['inventory_status']) && $params['inventory_status'] == 1){
                $sub_select_stock->where('i.activated_date IS NULL');
            }

            // Imei Status Activate No Timing
            if(isset($params['inventory_status']) && $params['inventory_status'] == 3){
                $sub_select_stock->where('i.activated_date IS NOT NULL');
            }

            $stock_result = $db->fetchAll($sub_select_stock);

            // Count Distributor Stock
            $sub_select_test = $db->select()
            ->from(array('d2' => WAREHOUSE_DB.'.distributor'),array('COUNT(d2.id)'))
            ->where('d.id IN (?)',$stock_result)
            ->where('d2.id = d.id');


            $get = array(
                'distributor_id'        => 'd.id',
                'distributor_name'      => 'd.title',
                'distributor_area'      => 'a.name',
                'distributor_provience' => 'rm.name',
                'distributor_stock'         => new Zend_Db_Expr("(".$sub_select_test.")")
            );

            $select = $db->select()
            ->from(array('d' => WAREHOUSE_DB.'.distributor'),$get)
            ->join(array('rm' => HR_DB.'.regional_market'),'d.region = rm.id',array())
            ->join(array('a' => HR_DB.'.area'),'rm.area_id = a.id',array())


            ->where('a.id != 120')
            ->where('d.agent_warehouse_id IS NULL')
            ->where('d.del IS NULL')
            ->group('d.id')
            ->order('distributor_stock DESC');

            // Check Distributor Have PC
            if(isset($params['pc']) && $params['pc'] == 1){

                $sub_select = $db->select()
                ->from(array('ss' => HR_DB.'.store_staff'),array('s.d_id'))
                ->joinLeft(array('s' => 'store'),'s.id = ss.store_id',array())
                ->where('s.del is null')
                ->where('ss.is_leader = 0');

                $sub_result = $db->fetchAll($sub_select);

                $select->where('d.id IN (?)',$sub_result);

            }

            // Check Distributor No PC
            if(isset($params['pc']) && $params['pc'] == 2){

                $sub_select = $db->select()
                ->from(array('ss' => HR_DB.'.store_staff'),array('s.d_id'))
                ->joinLeft(array('s' => 'store'),'s.id = ss.store_id',array())
                ->where('s.del is null')
                ->where('ss.is_leader = 0');

                $sub_result = $db->fetchAll($sub_select);

                $select->where('d.id NOT IN (?)',$sub_result);
            }


            // Fillter By Area 1 = 1
            if (isset($params['area_id']) && $params['area_id']) {
                if (is_array($params['area_id']) && count($params['area_id']))
                    $select->where('a.id IN (?)', $params['area_id']);
                elseif (is_numeric($params['area_id']))
                    $select->where('a.id = ?', intval($params['area_id']));
                else
                    $select->where('1=0', 1);
            }


            // echo $select; die;
            $result = $db->fetchAll($select);
            return $result;
            
        }

        public function OnShelfRateRGM($params){
            $db = Zend_Registry::get('db');

            // Select Distributor Have Stock List
            $sub_select_stock = $db->select()
            ->from(array('d' => WAREHOUSE_DB.'.distributor'),array('d.id'))
            ->join(array('i' => WAREHOUSE_DB.'.imei'),'i.distributor_id = d.id',array())
            ->join(array('rm' => HR_DB.'.regional_market'),'rm.id = d.region',array())
            ->joinLeft(array('g' => WAREHOUSE_DB.'.good'),'i.good_id = g.id',array())
            ->joinLeft(array('a' => HR_DB.'.area'),'a.id = rm.area_id',array())

            ->where('d.del IS NULL')
            ->where('i.imei_sn NOT IN (SELECT imei FROM hr.timing_sale)')
            ->where('i.imei_sn NOT IN (SELECT imei FROM hr.timing_control)')
            ->group('d.id');

            // Fillter Model
            if (isset($params['good']) && $params['good']) {
                if (is_array($params['good']) && count($params['good']))
                    $sub_select_stock->where('g.id IN (?)', $params['good']);
                elseif (is_numeric($params['good']))
                    $sub_select_stock->where('g.id = ?', intval($params['good']));
                else
                    $sub_select_stock->where('1=0', 1);
            }

            //Fillter Brand
            if (isset($params['brand']) && $params['brand']) {
                if (is_array($params['brand']) && count($params['brand']))
                    $sub_select_stock->where('g.brand_id IN (?)', $params['brand']);
                elseif (is_numeric($params['brand']))
                    $sub_select_stock->where('g.brand_id = ?', intval($params['brand']));
                else
                    $sub_select_stock->where('1=0', 1);
            }

            // Fillter Area
            if (isset($params['area_id']) && $params['area_id']) {
                if (is_array($params['area_id']) && count($params['area_id']))
                    $sub_select_stock->where('a.id IN (?)', $params['area_id']);
                elseif (is_numeric($params['area_id']))
                    $sub_select_stock->where('a.id = ?', intval($params['area_id']));
                else
                    $sub_select_stock->where('1=0', 1);
            }

            // Imei Status No Activated and Not timing
            if(isset($params['inventory_status']) && $params['inventory_status'] == 1){
                $sub_select_stock->where('i.activated_date IS NULL');
            }

            // Imei Status Activate No Timing
            if(isset($params['inventory_status']) && $params['inventory_status'] == 3){
                $sub_select_stock->where('i.activated_date IS NOT NULL');
            }

            // Have PC
            if(isset($params['pc']) && $params['pc'] == 1){

                $sub_select_01 = $db->select()
                ->from(array('ss' => HR_DB.'.store_staff'),array('s.d_id'))
                ->joinLeft(array('s' => 'store'),'s.id = ss.store_id',array())
                ->where('s.del is null')
                ->where('ss.is_leader = 0');

                $sub_result = $db->fetchAll($sub_select_01);

                $sub_select_stock->where('d.id IN (?)',$sub_result);

            }

            // No Pc
            if(isset($params['pc']) && $params['pc'] == 2){
                $sub_select_01 = $db->select()
                ->from(array('ss' => HR_DB.'.store_staff'),array('s.d_id'))
                ->joinLeft(array('s' => 'store'),'s.id = ss.store_id',array())
                ->where('s.del is null')
                ->where('ss.is_leader = 0');

                $sub_result = $db->fetchAll($sub_select_01);

                $sub_select_stock->where('d.id NOT IN (?)',$sub_result);
            }


            $stock_result = $db->fetchAll($sub_select_stock);

            // Count Distributor Stock
            $sub_select_test = $db->select()
            ->from(array('d' => WAREHOUSE_DB.'.distributor'),array('COUNT(d.id)'))
            ->joinLeft(array('rm' => HR_DB.'.regional_market'),'rm.id = d.region',array())
            ->where('d.id IN (?)',$stock_result)
            ->where('rm.area_id = a.id');



            $get = array(
                'area_name'                     => 'a.name',
                'distributor_have_stock'        => new Zend_Db_Expr("(".$sub_select_test.")"),
                'distributor_count'             => 'COUNT(d.id)'
            );

            $select = $db->select()
            ->from(array('a' => HR_DB.'.area'),$get)
            ->joinLeft(array('rm' => HR_DB.'.regional_market'),'a.id = rm.area_id',array())
            ->joinLeft(array('d' => WAREHOUSE_DB.'.distributor'),'rm.id = d.region',array())


            ->where('d.del IS NULL')
            ->where('d.agent_warehouse_id IS NULL')
            ->where('a.id != 120')
            ->group('a.id')
            ->order('a.id ASC');



            if (isset($params['area_id']) && $params['area_id']) {
                if (is_array($params['area_id']) && count($params['area_id']))
                    $select->where('a.id IN (?)', $params['area_id']);
                elseif (is_numeric($params['area_id']))
                    $select->where('a.id = ?', intval($params['area_id']));
                else
                    $select->where('1=0', 1);
            }


            // echo $select; die;
            
            $result = $db->fetchAll($select);
            return $result;
        }


    }

