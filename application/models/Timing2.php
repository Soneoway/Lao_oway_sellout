<?php
class Application_Model_Timing2 extends Zend_Db_Table_Abstract
{
    protected $_name = 'timing';

    function fetchPagination($page, $limit, &$total, $params){
        $db = Zend_Registry::get('db');

        if ($limit) {
            $select = $db->select()
                ->from(array('p' => $this->_name),
                    array(new Zend_Db_Expr('SQL_CALC_FOUND_ROWS p.id'), 'p.*'));
        } else {
            $select = $db->select()
                ->from(array('p' => $this->_name),
                    array('p.*'));
        }

        //sửa điều kiện inner join -> left join on staff
        $select
            ->joinLeft(array('s' => 'staff'),
                'p.staff_id = s.id',
                array('s.email', 's.firstname', 's.lastname', 's.department', 's.team', 's.regional_market', 's.phone_number'));



        if (isset($params['imei']) and $params['imei']){

            //chỉ khi search theo imei mới cần join timing_sale
            $select
                ->joinLeft(array('ts' => 'timing_sale'),
                    'p.id = ts.timing_id',
                    array());

            $select->group('p.id');

            $select->where('ts.imei LIKE ?', '%'.$params['imei'].'%');
        }

        $select
            ->join(array('ina' => 'imei_not_activated'),
                'p.id = ina.timing_id and ina.removed_at IS NULL',
                array());

        if (isset($params['email']) and $params['email']) {
            $params['email'] = preg_replace('/'.EMAIL_SUFFIX.'/', '', $params['email']);
            $select->where('s.email LIKE ?', $params['email'].EMAIL_SUFFIX);
        }

        if (isset($params['name']) and $params['name'])
            $select->where('CONCAT(s.firstname, " ",s.lastname) LIKE ?', '%'.$params['name'].'%');

        if (isset($params['staff_id']) and $params['staff_id']) {
            $select->where('s.id = ?', $params['staff_id']);

        } elseif (isset($params['sale']) and $params['sale']) {

            $select->where('p.sales_id = ?', $params['sale']);

        } elseif (isset($params['asm']) and $params['asm']) {
            $QArea = new Application_Model_Area();
            $where = $QArea->getAdapter()->quoteInto('leader_ids = ?', $params['asm']); // lấy AREA mà staff này làm ASM
            $area  = $QArea->fetchAll($where);

            if (isset($area) && count($area) > 0) {

                $area_id = array();
                foreach ($area as $key => $value) {
                    $area_id[] = $value['id']; // lấy id những AREA trên
                }

                $QRegion = new Application_Model_RegionalMarket();
                $where = $QRegion->getAdapter()->quoteInto('area_id IN (?)', $area_id); // lấy regional_market thuộc những AREA trên
                $regional_markets = $QRegion->fetchAll($where);

                $regional_market_ids = array();

                foreach ($regional_markets as $key => $value) {
                    $regional_market_ids[] = $value['id']; // lấy id những regional_market trên
                }

                $select->where('s.regional_market IN (?)', $regional_market_ids); // lọc staff thuộc regional_market trên
            }
        }

        if (isset($params['department']) and $params['department'])
            $select->where('s.department = ?', $params['department']);

        if (isset($params['team']) and $params['team'])
            $select->where('s.team = ?', $params['team']);

        if (isset($params['from_date']) and $params['from_date']){
            $date = explode('/', $params['from_date']);
            $select->where('date(p.from) >= ?', $date[2].'-'.$date[1].'-'.$date[0]);
        }

        if (isset($params['to_date']) and $params['to_date']){
            $date = explode('/', $params['to_date']);
            $select->where('date(p.from) <= ?', $date[2].'-'.$date[1].'-'.$date[0]);
        }

        if (isset($params['regional_market']) and $params['regional_market'])
            $select->where('s.regional_market = ?', $params['regional_market']);

        if (isset($params['store']) and $params['store'])
            $select->where('p.store = ?', $params['store']);

        if (isset($params['area_id']) and $params['area_id']){
            $QRegionalMarket = new Application_Model_RegionalMarket();
            $where = $QRegionalMarket->getAdapter()->quoteInto('area_id = ?', $params['area_id']);

            $regional_markets = $QRegionalMarket->fetchAll($where);
            $tem = array();
            foreach ($regional_markets as $regional_market)
                $tem[] = $regional_market->id;

            $select->where('s.regional_market IN (?)', $tem);
        }

        if (isset($params['sales_team']) and $params['sales_team']){
            $QSalesTeamStaff = new Application_Model_SalesTeamStaff();
            $where = $QSalesTeamStaff->getAdapter()->quoteInto('sales_team_id = ?', $params['sales_team']);

            $sales_team_staffs = $QSalesTeamStaff->fetchAll($where);

            $tem = array();
            foreach ($sales_team_staffs as $sales_team_staff)
                $tem[] = $sales_team_staff->staff_id;

            $select->where('p.staff_id IN (?)', $tem);

        }

        if (isset($params['sort']) and $params['sort']) {
            $order_str = '';

            switch ( $params['sort'] ) {
                case 'p.store':
                    $select->join(array('st' => 'store'),
                        'p.store = st.id',
                        array());
                    $order_str = 'st.`name`';
                    break;
                case 's.regional_market':
                    $select->join(array('rm' => 'regional_market'),
                        's.regional_market = rm.id',
                        array());
                    $order_str = 'rm.`name`';
                    break;
                case 'team':
                    $select->join(array('te' => 'team'),
                        's.team = te.id',
                        array());
                    $order_str = 'te.`name`';
                    break;
                case 'department':
                    $select->join(array('de' => 'department'),
                        's.department = de.id',
                        array());
                    $order_str = 'de.`name`';
                    break;

                default:
                    break;
            }

            $collate = ' ';
            $desc = (isset($params['desc']) and $params['desc'] == 1) ? ' DESC ' : ' ASC ';

            if ($params['sort'] == 'name'){
                $collate = ' COLLATE utf8_unicode_ci ';
                $order_str .= ' CONCAT(s.firstname, " ",s.lastname) '.$collate . $desc;
            } elseif ( $params['sort'] == 's.regional_market' || $params['sort'] == 'department' || $params['sort'] == 'team' || $params['sort'] == 'p.store' ) {
                $collate = ' COLLATE utf8_unicode_ci ';
                $order_str .= $collate . $desc;
            } else {
                $order_str = $params['sort'] . ' ' . $collate . $desc;
            }

            $select->order(new Zend_Db_Expr($order_str));
        }

        if ($limit)
            $select->limitPage($page, $limit);

        $result = $db->fetchAll($select);

        if ($limit)
            $total = $db->fetchOne("select FOUND_ROWS()");
        return $result;
    }

    function report($page, $limit, &$total, $params)
    {
        $db = Zend_Registry::get('db');

        if ( isset($params['get_total_sales']) and $params['get_total_sales'] )
            $get = array();
        elseif (isset($params['export2']) and $params['export2'])
            $get = array(
                's.id', 's.title', 's.firstname', 's.lastname', 's.regional_market'
            );
        else
            $get = array(
                new Zend_Db_Expr('SQL_CALC_FOUND_ROWS s.id'), 'sid' => 's.id', 's.title', 's.firstname', 's.lastname', 's.title', 's.department', 's.phone_number'
            );

        if (isset($params['export2']) and $params['export2']){
            $select = $db->select()
                ->from(array('s' => 'staff'),
                    $get
                )
                ->joinLeft(array('t' => 'timing'),
                    's.id = t.staff_id AND t.approved_at IS NOT NULL AND t.approved_at <> 0 AND t.approved_at <> \'\'',
                    array('t.staff_id', 't.from', 't.store')
                )
                ->joinLeft(array('sto' => 'store'),
                    't.store = sto.id',
                    array('store_name' => 'sto.name', 'sto.company_address', 'sto.shipping_address')
                )
                ->joinLeft(array('r' => 'regional_market'),
                    'sto.regional_market = r.id',
                    array('regional_market' => 'r.name'))
                ->joinLeft(array('a' => 'area'),
                    'r.area_id = a.id',
                    array('area' => 'a.name'))
                ->joinLeft(array('ts' => 'timing_sale'),
                    't.id = ts.timing_id',
                    array('ts.product_id', 'ts.model_id', 'ts.customer_id', 'ts.imei'))

                ->joinLeft(array('cus' => 'customer'),
                    'ts.customer_id = cus.id',
                    array('customer_name' => 'cus.name', 'cus.email', 'cus.phone_number', 'cus.address'));

        } else {

            $select = $db->select()
                ->from(array('s' => 'staff'),
                    $get
                )
                ->joinLeft(array('t' => 'timing'),
                    's.id = t.staff_id',
                    array('t.staff_id', 't.from', 't.approved_at', 't.store')
                )
                ->joinLeft(array('sto' => 'store'),
                    't.store = sto.id',
                    array('store_name' => 'sto.name', 'sto.company_address', 'sto.shipping_address')
                )
                ->joinLeft(array('r' => 'regional_market'),
                    'sto.regional_market = r.id',
                    array('regional_market' => 'r.name'))
                ->joinLeft(array('a' => 'area'),
                    'a.id = r.area_id',
                    array('area' => 'a.name'))
                ->joinLeft(array('ts' => 'timing_sale'),
                    't.id = ts.timing_id  AND t.approved_at IS NOT NULL AND t.approved_at <> 0 AND t.approved_at <> \'\'',
                    array('product_count' => 'COUNT(ts.id)'))
                ->group('s.id');
        }

        $sub_select = $db->select()
            ->from(array('t1'=>'timing'), array('t1.staff_id'));

        if ( isset($params['store']) && $params['store'] ) {


            $sub_select->where( 't1.store = ?', $params['store']);

        }

        $from = explode('/', $params['from']);
        $to = explode('/', $params['to']);

        if (isset($params['from']) && $params['from'] && !isset($params['to'])) {

            $sub_select->where( 't1.`from` > ?', $from[2].'-'.$from[1].'-'.$from[0] . ' 00:00:00' );

        } elseif (isset($params['to']) && $params['to'] && !isset($params['from'])) {

            $sub_select->where( 't1.`from` < ?', $to[2].'-'.$to[1].'-'.$to[0] . ' 23:59:59' );

        } elseif (isset($params['to']) && $params['to'] && isset($params['from']) && $params['from'] ) {

            $sub_select->where( 't1.`from` >= ?', $from[2].'-'.$from[1].'-'.$from[0] .' 00:00:00');

            $sub_select->where( 't1.`from` <= ?', $to[2].'-'.$to[1].'-'.$to[0] . ' 23:59:59');
        }

        $cond = $select
            ->orWhere('s.group_id IN (?)', array(PGPB_ID, SALES_ID))
            ->orWhere('s.id IN (?)', $sub_select)
            ->getPart(Zend_Db_Select::WHERE);

        $select->reset(Zend_Db_Select::WHERE);

        $select->where ( implode(' ', $cond ) );

        if ( isset($params['name']) && $params['name'] ) {
            $select->where('CONCAT(s.firstname, " ",s.lastname) LIKE ?', '%'.$params['name'].'%');
        }

        if ( isset($params['phone_number']) && $params['phone_number'] ) {
            $select->where( 's.phone_number LIKE ?', '%'.$params['phone_number'].'%');
        }

        if ( isset($params['area']) && $params['area'] ) {
            $select->where( 'r.area_id = ?', $params['area']);
        }

        if ( isset($params['area_asm']) && $params['area_asm'] ) {

            if (is_array($params['area_asm']))

                $select->where( 'r.area_id IN (?)', $params['area_asm']);

            else

                $select->where( 'r.area_id = ?', $params['area_asm']);
        }

        if ( isset($params['regional_market']) && $params['regional_market'] ) {
            $select->where( 'sto.regional_market = ?', $params['regional_market']);
        }

        if ( isset($params['store']) && $params['store'] ) {

            $select->where( 't.store = ?', $params['store']);
        }


        if (isset($params['from']) && $params['from'] && !isset($params['to'])) {

            $select->where( 't.`from` > ?', $from[2].'-'.$from[1].'-'.$from[0] . ' 00:00:00' );

        } elseif (isset($params['to']) && $params['to'] && !isset($params['from'])) {

            $select->where( 't.`from` < ?', $to[2].'-'.$to[1].'-'.$to[0] . ' 23:59:59' );

        } elseif (isset($params['to']) && $params['to'] && isset($params['from']) && $params['from'] ) {
            $select->where( 't.`from` >= ?', $from[2].'-'.$from[1].'-'.$from[0] .' 00:00:00');

            $select->where( 't.`from` <= ?', $to[2].'-'.$to[1].'-'.$to[0] . ' 23:59:59');

        }


        if (isset( $params['sort'] ) && $params['sort'] && !$params['export2']) {
            $collate = ' ';
            $desc = (isset($params['desc']) && $params['desc'] == 1) ? ' DESC ' : ' ASC ';
            $order_str = '';

            if ($params['sort'] == 'name'){
                $collate = ' COLLATE utf8_unicode_ci ';
                $order_str .= ' CONCAT(s.firstname, " ",s.lastname) '.$collate . $desc;
            } elseif ( $params['sort'] == 'area' || $params['sort'] == 'province' ) {
                $collate = ' COLLATE utf8_unicode_ci ';
                $order_str .= $params['sort'] . ' ' . $collate . $desc;
            } else {
                $order_str = $params['sort'] . ' ' . $collate . $desc;
            }

            $select->order(new Zend_Db_Expr($order_str));
        }

        if ($limit) {
            $select->limitPage($page, $limit);
        }

        if ( isset($params['get_total_sales']) and $params['get_total_sales'] ){
            $select_p = $db->select()
                ->from(array('pa' => $select),
                    array(new Zend_Db_Expr('SUM( pa.product_count )')));
            return $db->fetchOne($select_p);
        }



        if (isset($params['export2']) and $params['export2']){
            return $select->__toString();
        }

        $analytics = $db->fetchAll($select);

        $total = $db->fetchOne("select FOUND_ROWS()");

        $sales = array();
        foreach ($analytics as $key => $value) {
            $temp = array();
            $temp['total'] = $value['product_count'];
            $temp['title'] = $value['title'];
            $temp['firstname'] = $value['firstname'];
            $temp['lastname'] = $value['lastname'];
            $temp['phone_number'] = $value['phone_number'];
            $temp['regional_market'] = $value['regional_market'];
            $temp['store'] = $value['store'];
            $temp['area'] = $value['area'];

            $sales[$value['sid']] = $temp;
        }

        return $sales;
    }

    function report2(&$areas, &$leader_by_area, &$store_by_leader, &$product_by_store, $from, $to)
    {
        $db = Zend_Registry::get('db');

        $QArea = new Application_Model_Area();
        $areas = $QArea->get_cache();

        $month = date('m');
        $year = date('Y');

        if (isset($from) && $from && isset($to) && $to) {
            $fromt = explode('/', $from);
            $from = $fromt[2].'-'.$fromt[1].'-'.$fromt[0];
            $tot = explode('/', $to);
            $to = $tot[2].'-'.$tot[1].'-'.$tot[0];
        }


        // Lấy leader theo area
        foreach ($areas as $area => $v) {
            $select	= $db->select()
                ->distinct()
                ->from(array('st'=>'sales_team'), array('st.id'))
                ->where('st.area_id = ?', $area);

            $st_ids = $db->fetchAll($select);

            $select = $db->select()
                ->distinct()
                ->from(array('sts'=>'sales_team_staff'), array('sts.staff_id'))
                ->join(array('s'=>'staff'), 's.id=sts.staff_id AND sts.is_leader = 1', array('s.firstname', 's.lastname'))
                ->where('sts.sales_team_id IN (?)', $st_ids);

            $leader_by_area[$area] = $db->fetchAll($select);
        }

        // lấy store theo từng leader

        // duyệt vùng
        foreach ($leader_by_area as $area => $staffs) {

            // duyệt leader theo vùng
            foreach ($staffs as $key => $staff) {

                // Lấy sales team của leader này
                $select = $db->select()
                    ->from(array('sts'=>'sales_team_staff'), array('sts.sales_team_id'))
                    ->where('sts.staff_id = ?', $staff['staff_id']);

                $sales_team_ids = $db->fetchAll($select);

                // Lấy staff thuộc các sales team trên
                $select = $db->select()
                    // ->distinct()
                    ->from(array('sts'=>'sales_team_staff'), array('sts.staff_id'))
                    ->where('sts.sales_team_id IN (?)', $sales_team_ids);

                $staff_ids = $db->fetchAll($select);

                $store_temp = array();

                // // Lấy danh sách store mà leader chấm công
                $select = $db->select()
                    ->distinct()
                    ->from(array('ti'=>'timing'), array('store_id' => 'ti.store'))
                    ->join(array('str'=>'store'), 'str.id=ti.store AND ti.approved_at IS NOT NULL AND ti.approved_at <> 0 AND ti.approved_at <> \'\'', array('str.name'))
                    ->where('ti.staff_id = ?', $staff['staff_id']);

                if (isset($from) && $from && isset($to) && $to) {
                    $select->where('DATE(ti.`from`) >= ?', $from);
                    $select->where('DATE(ti.`from`) <= ?', $to);
                }
                else {

                    $select->where('MONTH(ti.`from`) = ?', $month);
                    $select->where('YEAR(ti.`from`) = ?', $year);
                }


                $store_leader_ids = $db->fetchAll($select);

                foreach ($store_leader_ids as $sli_k => $sli_v) {
                    $store_temp[] = $sli_v;
                }

                // Lấy cửa hàng có các staff trên
                $select = $db->select()
                    ->distinct()
                    ->from(array('ss'=>'store_staff'), array('ss.store_id'))
                    ->join(array('str'=>'store'), 'str.id=ss.store_id', array('str.name'))
                    ->where('ss.staff_id IN (?)', $staff_ids);

                $store_ids = $db->fetchAll($select);

                foreach ($store_ids as $sli_k => $sli_v) {
                    $store_temp[] = $sli_v;
                }

                $store_ids = $store_temp;


                // Lấy chấm công của TỪNG store trên
                foreach ($store_ids as $store_k => $store) {
                    $select = $db->select()
                        ->from(array('ti'=>'timing'), array('ti.id'))
                        ->where('ti.store = ?', $store['store_id'])
                        ->where('ti.approved_at IS NOT NULL AND ti.approved_at <> 0 AND ti.approved_at <> \'\'');

                    if (isset($from) && $from && isset($to) && $to) {

                        $select->where('DATE(ti.`from`) >= ?', $from);
                        $select->where('DATE(ti.`from`) <= ?', $to);
                    }
                    else {

                        $select->where('MONTH(ti.`from`) = ?', $month);
                        $select->where('YEAR(ti.`from`) = ?', $year);
                    }

                    // PC::db($select->__toString());

                    $list_timing = $db->fetchAll($select);

                    $sum_arr = array();
                    $res = array();
                    // Lấy danh sách sản phẩm đc chấm công
                    foreach ($list_timing as $list_k => $timing) {
                        $sub_select = $db->select()
                            ->from(array('tis'=>'timing_sale'), array('tis.product_id', 'tis.quantity'))
                            ->where('tis.timing_id IN (?)', $timing['id']);

                        $select = $db->select()
                            ->from(array('tis'=>$sub_select), array('tis.product_id', 'qty' => 'COUNT(tis.quantity)'))
                            ->group('tis.product_id');

                        $temp = $db->fetchAll($select);
                        // đưa mảng sản phẩm/số lượng ra để tính tổng
                        $t = array();
                        foreach ($temp as $tmk => $tem) {
                            $t[$tem['product_id']] = $tem['qty'];
                        }
                        $res[] = $t;
                    }

                    // tính tổng số lượng từng sản phẩm/store
                    foreach ($res as $rk => $rv) {
                        foreach ($rv as $product_id_key => $qty) {
                            if (!isset($sum_arr[$product_id_key])) {
                                $sum_arr[$product_id_key] = 0;
                            }

                            $sum_arr[$product_id_key] += $qty;
                        }
                    }
                    $product_by_store[$store['store_id']] = $sum_arr;
                }

                $store_by_leader[$staff['staff_id']] = $store_ids;
            }
        }
    }

    function report2a()
    {
        $sql = "SELECT
			COUNT(ts.product_id)
		FROM
			timing_sale ts
		WHERE
			ts.timing_id IN(
				SELECT
					t.id
				FROM
					timing t
				WHERE
					t.store IN(?)
				AND DATE(t.`from`)>= ?
				AND DATE(t.`from`)<= ?
			)
		AND ts.product_id = ?";


    }

    // function get_leader_by_area_cache(){
    //     $cache      = Zend_Registry::get('cache');
    //     $result     = $cache->load('leader_by_area_cache');

    //     if ($result === false) {

    //         $result = array();

    //         $db = Zend_Registry::get('db');

    //         $QArea = new Application_Model_Area();
    // 		$areas = $QArea->get_cache();

    //  	foreach ($areas as $area => $v) {
    // $select	= $db->select()
    // 	->from(array('st'=>'sales_team'), array('st.id'));
    // $st_ids = $db->fetchAll($select);

    // $select = $db->select()
    // 	->from(array('sts'=>'sales_team_staff'), array('sts.staff_id'))
    // 	->where('sts.is_leader = 1 AND sts.sales_team_id IN (?)', $st_ids);

    // $result[$area] = $db->fetchAll($select);
    //  	}

    //         $cache->save($result, 'leader_by_area_cache', array(), null);
    //     }
    //     return $result;
    // }

    function report_by_staff($params, &$analytics, &$count)
    {
        $db = Zend_Registry::get('db');

        $select = $db->select()
            ->from(array('s' => 'staff'),
                array());

        $select
            ->join(array('r' => 'regional_market'),
                'r.id = s.regional_market',
                array())
            ->join(array('a' => 'area'),
                'r.area_id = a.id',
                array())
            ->join(array('t' => 'timing'),
                's.id = t.staff_id AND t.approved_at IS NOT NULL AND t.approved_at <> 0 AND t.approved_at <> \'\'',
                array('time' => 't.from',  't.store'))
            ->join(array('ts' => 'timing_sale'),
                'ts.timing_id = t.id',
                array('imei' => 'ts.imei'))
            ->joinLeft(array('c' => 'customer'),
                'c.id = ts.customer_id',
                array('customer_name' => 'c.name', 'customer_phone' => 'phone_number', 'customer_address' => 'c.address'))
            ->join(array('p' => 'good'),
                'p.id = ts.product_id',
                array('product_name' => 'p.desc', 'product_id' => 'p.id'));


        if ( isset($params['staff_id']) && $params['staff_id'] ) {
            $select->where('t.staff_id = ?', $params['staff_id']);
        }

        if ( isset($params['name']) && $params['name'] ) {
            $select->where('CONCAT(s.firstname, " ",s.lastname) LIKE ?', '%'.$params['name'].'%');
        }

        if ( isset($params['phone_number']) && $params['phone_number'] ) {
            $select->where( 's.phone_number LIKE ?', '%'.$params['phone_number'].'%');
        }

        if ( isset($params['area']) && $params['area'] ) {
            $select->where( 'r.area_id = ?', $params['area']);
        }

        if ( isset($params['regional_market']) && $params['regional_market'] ) {
            $select->where( 's.regional_market = ?', $params['regional_market']);
        }

        $from = explode('/', $params['from']);
        $to = explode('/', $params['to']);

        if (isset($params['from']) && $params['from'] && !isset($params['to'])) {
            $select->where( 't.from > ?', $from[2].'-'.$from[1].'-'.$from[0] . ' 00:00:00' );

        } elseif (isset($params['to']) && $params['to'] && !isset($params['from'])) {
            $select->where( 't.from < ?', $to[2].'-'.$to[1].'-'.$to[0] . ' 23:59:59' );

        } elseif (isset($params['to']) && $params['to'] && isset($params['from']) && $params['from'] ) {
            $select->where( 't.from >= ?', $from[2].'-'.$from[1].'-'.$from[0] .' 00:00:00');
            $select->where( 't.from <= ?', $to[2].'-'.$to[1].'-'.$to[0] . ' 23:59:59');
        }

        $stmt  = $select->query();
        $total = $stmt->fetchAll();

        $sales = array();
        $analytics = array();
        $count = 0;

        if ( isset($params['staff_id']) && $params['staff_id'] ) {
            foreach ($total as $key => $value) {
            	// thống kê thêm cho PG
            	if ( ! isset( $analytics[ $value['product_id'] ] )  ) {
            		$analytics[ $value['product_id'] ] = 0;
            	}

            	$analytics[ $value['product_id'] ] += 1;
            	$count += 1;

                $temp                     = array();
                $temp['product']          = $value['product_name'];
                $temp['time']             = $value['time'];
                $temp['imei']             = $value['imei'];
                $temp['store']            = $value['store'];
                $temp['customer_name']    = $value['customer_name'];
                $temp['customer_phone']   = $value['customer_phone'];
                $temp['customer_address'] = $value['customer_address'];

                $sales[] = $temp;
            }
        }

        return $sales;
    }

    function report_by_store($page, $limit, &$total, $params)
    {
        $db = Zend_Registry::get('db');

        if ( isset($params['get_total_sales']) and $params['get_total_sales'] )
            $get = array();
        elseif (isset($params['export']) and $params['export'])
            $get = array(
                's.id', 'store_name' => 's.name', 'company_address' => 's.company_address', 'shipping_address' => 's.shipping_address'
            );
        else
            $get = array(
                new Zend_Db_Expr('SQL_CALC_FOUND_ROWS s.id'), 'store_name' => 's.name'
            );

        $select = $db->select()
            ->from(array('s' => 'store'),$get
            );

        $select
            ->joinLeft(array('r' => 'regional_market'),
                's.regional_market = r.id',
                array('province_name' => 'r.name'))
            ->joinLeft(array('a' => 'area'),
                'r.area_id = a.id',
                array('area_name' => 'a.name', 'area_id' => 'a.id'));

        $select    ->joinLeft(array('t' => 'timing'),
            's.id = t.store',
            array('t.store', 'timing_count' => 'COUNT(t.id)'));

        $from = explode('/', $params['from']);
        $to = explode('/', $params['to']);

        if (isset($params['from']) && $params['from'] && !isset($params['to'])) {


            $select->joinLeft(array('ts' => 'timing_sale'),
                '
                ts.timing_id = t.id AND t.approved_at IS NOT NULL AND t.approved_at <> 0 AND t.approved_at <> \'\'
                AND t.from >= \''.$from[2].'-'.$from[1].'-'.$from[0] . ' 00:00:00\'
                            ',
                array('product_count' => 'COUNT(ts.product_id)'));

        } elseif (isset($params['to']) && $params['to'] && !isset($params['from'])) {

            $select->joinLeft(array('ts' => 'timing_sale'),
                '
                ts.timing_id = t.id AND t.approved_at IS NOT NULL AND t.approved_at <> 0 AND t.approved_at <> \'\'
                AND t.from <= \''.$to[2].'-'.$to[1].'-'.$to[0] . ' 23:59:59\'
                            ',
                array('product_count' => 'COUNT(ts.product_id)'));

        } elseif (isset($params['to']) && $params['to'] && isset($params['from']) && $params['from'] ) {

            $select->joinLeft(array('ts' => 'timing_sale'),
                '
                ts.timing_id = t.id AND t.approved_at IS NOT NULL AND t.approved_at <> 0 AND t.approved_at <> \'\'
                AND t.from >= \''.$from[2].'-'.$from[1].'-'.$from[0] . ' 00:00:00\'
                AND t.from <= \''.$to[2].'-'.$to[1].'-'.$to[0] . ' 23:59:59\'
                            ',
                array('product_count' => 'COUNT(ts.product_id)'));

        } else {

            $select->joinLeft(array('ts' => 'timing_sale'),
                '
                ts.timing_id = t.id AND t.approved_at IS NOT NULL AND t.approved_at <> 0 AND t.approved_at <> \'\'
                            ',
                array('product_count' => 'COUNT(ts.product_id)'));

        }



        $select->group('s.id');



        if ( isset($params['name']) && $params['name'] ) {
            $select->where('s.name LIKE ?', '%'.$params['name'].'%');
        }

        if ( isset($params['area']) && $params['area'] ) {

            if (is_array($params['area']))

                $select->where( 'r.area_id IN (?)', $params['area']);

            else

                $select->where( 'r.area_id = ?', $params['area']);
        }

        if ( isset($params['area_asm']) && $params['area_asm'] ) {

            if (is_array($params['area_asm']))

                $select->where( 'r.area_id IN (?)', $params['area_asm']);

            else

                $select->where( 'r.area_id = ?', $params['area_asm']);
        }

        if ( isset($params['regional_market']) && $params['regional_market'] ) {
            $select->where( 's.regional_market = ?', $params['regional_market']);
        }

        if ( isset($params['store']) && $params['store'] ) {
            $select->where( 's.id = ?', $params['store']);
        }

        if ( isset($params['has_pg']) ) {
            if ($params['has_pg']==1)
                $select->having('timing_count > 0');
            elseif ($params['has_pg']==2)
                $select->having('timing_count = 0');
        }

        if ( isset($params['sales_from']) and $params['sales_from']!='' ){

            $select->having('product_count >= '.$params['sales_from']);

        }

        if ( isset($params['sales_to']) and $params['sales_to']!='' ){

            $select->having('product_count <= '.$params['sales_to']);

        }

        if (isset( $params['sort'] ) && $params['sort']) {
            $collate = ' ';
            $desc = (isset($params['desc']) && $params['desc'] == 1) ? ' DESC ' : ' ASC ';
            $order_str = '';

            if ( $params['sort'] == 's.name' || $params['sort'] == 'area' || $params['sort'] == 'province' ) {
                $collate = ' COLLATE utf8_unicode_ci ';
                $order_str .= $params['sort'] . ' ' . $collate . $desc;
            } else {
                $order_str = $params['sort'] . ' ' . $collate . $desc;
            }

            $select->order(new Zend_Db_Expr($order_str));
        }

        if (!$params['export'] and $limit) {
            $select->limitPage($page, $limit);
        }

        if ( isset($params['get_total_sales']) and $params['get_total_sales'] ){
            $select_p = $db->select()
                ->from(array('pa' => $select),
                    array(new Zend_Db_Expr('SUM( pa.product_count )')));
            return $db->fetchOne($select_p);
        }


        $analytics = $db->fetchAll($select);

        if (!$params['export'])
            $total = $db->fetchOne("select FOUND_ROWS()");

        return $analytics;
    }

    function count_product_by_store($store_id, $params){

        $db = Zend_Registry::get('db');

        $select = $db->select()
            ->from(array('t' => 'timing'),array()
            );
        $select->joinLeft(array('ts' => 'timing_sale'),
            'ts.timing_id = t.id',
            array('product_count' => 'COUNT(ts.product_id)', 'ts.product_id'));

        $select->where('t.approved_at IS NOT NULL AND t.approved_at <> 0 AND t.approved_at <> \'\'');

        $select->group('ts.product_id');

        $from = explode('/', $params['from']);
        $to = explode('/', $params['to']);

        if (isset($params['from']) && $params['from'] && !isset($params['to'])) {
            $select->where( 't.from > ?', $from[2].'-'.$from[1].'-'.$from[0] . ' 00:00:00' );

        } elseif (isset($params['to']) && $params['to'] && !isset($params['from'])) {
            $select->where( 't.from < ?', $to[2].'-'.$to[1].'-'.$to[0] . ' 23:59:59' );

        } elseif (isset($params['to']) && $params['to'] && isset($params['from']) && $params['from'] ) {
            $select->where( 't.from >= ?', $from[2].'-'.$from[1].'-'.$from[0] .' 00:00:00');
            $select->where( 't.from <= ?', $to[2].'-'.$to[1].'-'.$to[0] . ' 23:59:59');
        }

        $select->where( 't.store = ?', $store_id);

        return $db->fetchAll($select);
    }

    function short_report_by_store($params)
    {
        $db = Zend_Registry::get('db');

        $select = $db->select()
            ->from(array('s' => 'store'),
                array());

        $select
            ->join(array('r' => 'regional_market'),
                'r.id = s.regional_market',
                array())
            ->join(array('a' => 'area'),
                'r.area_id = a.id',
                array())
            ->join(array('t' => 'timing'),
                's.id = t.store AND t.approved_at IS NOT NULL AND t.approved_at <> 0 AND t.approved_at <> \'\'',
                array('time' => 't.from'))
            ->join(array('sta' => 'staff'),
                'sta.id = t.staff_id',
                array('sta.firstname', 'sta.lastname'))
            ->join(array('ts' => 'timing_sale'),
                'ts.timing_id = t.id',
                array('imei' => 'ts.imei'))
            ->joinLeft(array('c' => 'customer'),
                'c.id = ts.customer_id',
                array('customer_name' => 'c.name', 'customer_phone' => 'phone_number', 'customer_address' => 'c.address'))
            ->join(array('p' => 'good'),
                'p.id = ts.product_id',
                array('product_name' => 'p.desc'));


        if ( isset($params['store_id']) && $params['store_id'] ) {
            $select->where('s.id = ?', $params['store_id']);
        }

        if ( isset($params['name']) && $params['name'] ) {
            $select->where('s.name LIKE ?', '%'.$params['name'].'%');
        }

        if ( isset($params['area']) && $params['area'] ) {
            $select->where( 'r.area_id = ?', $params['area']);
        }

        if ( isset($params['regional_market']) && $params['regional_market'] ) {
            $select->where( 's.regional_market = ?', $params['regional_market']);
        }

        $from = explode('/', $params['from']);
        $to = explode('/', $params['to']);

        if (isset($params['from']) && $params['from'] && !isset($params['to'])) {
            $select->where( 't.from > ?', $from[2].'-'.$from[1].'-'.$from[0] . ' 00:00:00' );

        } elseif (isset($params['to']) && $params['to'] && !isset($params['from'])) {
            $select->where( 't.from < ?', $to[2].'-'.$to[1].'-'.$to[0] . ' 23:59:59' );

        } elseif (isset($params['to']) && $params['to'] && isset($params['from']) && $params['from'] ) {
            $select->where( 't.from >= ?', $from[2].'-'.$from[1].'-'.$from[0] .' 00:00:00');
            $select->where( 't.from <= ?', $to[2].'-'.$to[1].'-'.$to[0] . ' 23:59:59');
        }

        $stmt  = $select->query();
        $total = $stmt->fetchAll();

        $sales = array();
        if ( isset($params['store_id']) && $params['store_id'] ) {
            foreach ($total as $key => $value) {
                $temp                     = array();
                $temp['product']          = $value['product_name'];
                $temp['time']             = $value['time'];
                $temp['imei']             = $value['imei'];
                $temp['customer_name']    = $value['customer_name'];
                $temp['customer_phone']   = $value['customer_phone'];
                $temp['customer_address'] = $value['customer_address'];
                $temp['pg']               = $value['firstname'] . ' ' . $value['lastname'];

                $sales[] = $temp;
            }
        }

        return $sales;
    }

    function report_by_area($page, $limit, &$total, $params)
    {
        $db = Zend_Registry::get('db');

        $where = 'where 1=1 ';

        $subProductCount = $subProductKACount = '
                        select count(*)
                        from
                            `regional_market` AS `r`
                            JOIN `store` AS `s` ON s.regional_market = r.id
                            join timing t1 on s.id = t1.store
                            join timing_sale ts1 on t1.id = ts1.timing_id
                        where a.id = r.area_id
                            and t1.approved_at IS NOT NULL AND t1.approved_at <> 0 AND t1.approved_at <> \'\'

        ';

        $subProductKACount .= '
                            and (
                                s.`name` like "TGDĐ%"
                                or s.`name` like "VTA%"
                                or s.`name` like "Viettel%"
                            )
        ';

        $sLimit = '';
        if ($limit){
            $offset = ($page-1)*$limit;
            $sLimit = 'limit '.$offset.','.$limit;
        }

        if ( isset($params['area']) && $params['area'] ) {
            $subProductCount .= $db->quoteInto(' and a.id = ?', $params['area']);
            $subProductKACount .= $db->quoteInto(' and a.id = ?', $params['area']);
            $where .= $db->quoteInto(' and a.id = ?', $params['area']);
        }


        $from = explode('/', $params['from']);
        $to = explode('/', $params['to']);

        if (isset($params['from']) && $params['from'] && !isset($params['to'])) {
            $subProductCount .= $db->quoteInto(' and t1.from >= ?', $from[2].'-'.$from[1].'-'.$from[0] . ' 00:00:00');
            $subProductKACount .= $db->quoteInto(' and t1.from >= ?', $from[2].'-'.$from[1].'-'.$from[0] . ' 00:00:00');

        } elseif (isset($params['to']) && $params['to'] && !isset($params['from'])) {
            $subProductCount .= $db->quoteInto(' and t1.from <= ?', $to[2].'-'.$to[1].'-'.$to[0] . ' 23:59:59');
            $subProductKACount .= $db->quoteInto(' and t1.from <= ?', $to[2].'-'.$to[1].'-'.$to[0] . ' 23:59:59');

        } elseif (isset($params['to']) && $params['to'] && isset($params['from']) && $params['from'] ) {
            $subProductCount .= $db->quoteInto(' and t1.from >= ?', $from[2].'-'.$from[1].'-'.$from[0] . ' 00:00:00');
            $subProductCount .= $db->quoteInto(' and t1.from <= ?', $to[2].'-'.$to[1].'-'.$to[0] . ' 23:59:59');

            $subProductKACount .= $db->quoteInto(' and t1.from >= ?', $from[2].'-'.$from[1].'-'.$from[0] . ' 00:00:00');
            $subProductKACount .= $db->quoteInto(' and t1.from <= ?', $to[2].'-'.$to[1].'-'.$to[0] . ' 23:59:59');
        }

        $sql =
            '
                SELECT
                    `a`.`id`,
                    `a`.`name` AS `area_name`,
                    (
                      '.$subProductCount.'
                    ) as product_count,
                    (
                      '.$subProductKACount.'
                    ) as product_ka_count
                FROM
                    `area` AS `a`

                '.$where.'

                GROUP BY
                    `a`.`id`
            '
        ;

        if (isset($params['export']) and $params['export'])
            return $sql;


        if ( isset($params['get_total_sales']) and $params['get_total_sales'] ){

            $subSql = '
                select SUM( pa.product_count )
                from
                  (
                    '.$sql.'
                    '  . $sLimit . '
                   ) as pa
            ';


            return $db->fetchOne($subSql);
        }

        $analytics = $db->fetchAll($sql);

        if (! $params['export']){

            $subSql = '
                select count(*)
                from
                  ( '.$sql.' ) as pa
            ';


            $total = $db->fetchOne($subSql);

        }


        return $analytics;
    }

    function count_product_by_area($area_id, $params){

        $db = Zend_Registry::get('db');

        $select = $db->select()
            ->from(array('t' => 'timing'),array()
            );

        $select->join(array('s' => 'store'),
            't.store = s.id',
            array());

        $select->join(array('r' => 'regional_market'),
            's.regional_market = r.id',
            array());

        $select->joinLeft(array('ts' => 'timing_sale'),
            'ts.timing_id = t.id',
            array('product_count' => 'COUNT(ts.product_id)', 'ts.product_id'));

        $select->where('t.approved_at IS NOT NULL AND t.approved_at <> 0 AND t.approved_at <> \'\'');

        $select->group('ts.product_id');

        $from = explode('/', $params['from']);
        $to = explode('/', $params['to']);

        if (isset($params['from']) && $params['from'] && !isset($params['to'])) {
            $select->where( 't.from > ?', $from[2].'-'.$from[1].'-'.$from[0] . ' 00:00:00' );

        } elseif (isset($params['to']) && $params['to'] && !isset($params['from'])) {
            $select->where( 't.from < ?', $to[2].'-'.$to[1].'-'.$to[0] . ' 23:59:59' );

        } elseif (isset($params['to']) && $params['to'] && isset($params['from']) && $params['from'] ) {
            $select->where( 't.from >= ?', $from[2].'-'.$from[1].'-'.$from[0] .' 00:00:00');
            $select->where( 't.from <= ?', $to[2].'-'.$to[1].'-'.$to[0] . ' 23:59:59');
        }

        if ( isset($params['key_account']) and $params['key_account']==1 )
            $select->where( '   s.`name` like "TGDĐ%"
                                or s.`name` like "VTA%"
                                or s.`name` like "Viettel%"
            ', null );
        elseif ( isset($params['key_account']) and $params['key_account']==-1 )
            $select->where( '   not (s.`name` like "TGDĐ%"
                                or s.`name` like "VTA%"
                                or s.`name` like "Viettel%")
            ', null );

        $select->where( 'r.area_id = ?', $area_id);

        return $db->fetchAll($select);
    }
}                                                      
