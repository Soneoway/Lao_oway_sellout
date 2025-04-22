<?php
class Application_Model_Timing extends Zend_Db_Table_Abstract
{
    protected $_name = 'timing';
    function getSellOut($date, $to_date, $good_id){
        $db = Zend_Registry::get('db');
        $select = $db->select()
        ->from(array('ts' => 'timing_sale'), array('product_count' => 'COUNT(ts.id)'))
        ->joinLeft(array('t' => 'timing'), 't.id=ts.timing_id', array())
        ->join(array('g' => WAREHOUSE_DB.'.'.'good'), 'ts.product_id=g.id', array())
        ->group('ts.product_id');
        $select->where('t.from >= ?', $date.' 00:00:00');
        $select->where('t.from <= ?', $to_date.' 23:59:59');
        $select->where('ts.product_id = ?', $good_id);
        $result = $db->fetchOne($select);
        return $result;
    }
    function getSellOut2($date, $to_date, $good_id){
        $db = Zend_Registry::get('db');
        $select = $db->select()
        ->from(array('ts' => 'timing_sale'), array('product_count' => 'COUNT(ts.id)'))
        ->joinLeft(array('t' => 'timing'), 't.id=ts.timing_id', array())
        ->join(array('g' => WAREHOUSE_DB.'.'.'good'), 'ts.product_id=g.id', array())
        ->group('ts.product_id');
        $select->where('t.from >= ?', $date.' 00:00:00');
        $select->where('t.from <= ?', $to_date.' 23:59:59');
        $select->where('ts.product_id = ?', $good_id);
        $result = $db->fetchOne($select);
        return $result;
    }
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
        $select->distinct();
//sửa điều kiện inner join -> left join on staff
        $select
        ->joinLeft(array('s' => 'staff'),
            'p.staff_id = s.id',
            array('s.email', 's.firstname', 's.lastname', 's.department', 's.team', 's.regional_market', 's.phone_number'))
        ->joinLeft(array('st' => 'store'),
            'p.store = st.id',
            array());
        if (isset($params['imei']) and $params['imei']){
//chỉ khi search theo imei mới cần join timing_sale
            $select
            ->joinLeft(array('ts' => 'timing_sale'),
                'p.id = ts.timing_id',
                array());
            $select->group('p.id');
            $select->where('ts.imei LIKE ?', '%'.$params['imei'].'%');
        }
        if (isset($params['email']) and $params['email']) {
            $params['email'] = preg_replace('/'.EMAIL_SUFFIX.'/', '', $params['email']);
            $select->where('s.email LIKE ?', $params['email'].EMAIL_SUFFIX);
        }
        if (isset($params['name']) and $params['name'])
            $select->where('CONCAT(s.firstname, " ",s.lastname) LIKE ?', '%'.$params['name'].'%');
        if (isset($params['staff_id']) and $params['staff_id']) {
            $select->where('s.id = ?', $params['staff_id']);
        } elseif (isset($params['sale']) and $params['sale']) {
// follow STORE-STAFF concept
            $select ->join(array('ssl' => 'store_staff_log'), 'p.store = ssl.store_id', array());
            $log_where = $this->getAdapter()->quoteInto('ssl.staff_id = ?', $params['sale']).
            " AND " . $this->getAdapter()->quoteInto('ssl.is_leader = ?', 1).
            " AND " . $this->getAdapter()->quoteInto('DATE(p.from) >= FROM_UNIXTIME(ssl.joined_at, \'%Y-%m-%d\')', 1).
            " AND (".
            $this->getAdapter()->quoteInto('DATE(p.from) < FROM_UNIXTIME(ssl.released_at, \'%Y-%m-%d\')', 1).
            " OR " . $this->getAdapter()->quoteInto('ssl.released_at IS NULL', 1).
            " OR " . $this->getAdapter()->quoteInto('ssl.released_at = 0', 1).
            " ) ";
            $select->where($log_where);
        } elseif (isset($params['leader']) and $params['leader']) {
// follow STORE-STAFF concept
// lấy store thuộc region mà nó quản lý
            $select
            ->joinLeft(array('lg' => 'store_leader_log'), 'st.id = lg.store_id', array())
            ->joinLeft(array('ssl' => 'store_staff_log'), 'st.id = ssl.store_id', array());
// quyền leader
            $log_where = " ( " . $this->getAdapter()->quoteInto('lg.staff_id = ?', $params['leader']).
            " AND " . $this->getAdapter()->quoteInto('DATE(p.from) >= FROM_UNIXTIME(lg.joined_at, \'%Y-%m-%d\')', 1).
            " AND (".
            $this->getAdapter()->quoteInto('DATE(p.from) < FROM_UNIXTIME(lg.released_at, \'%Y-%m-%d\')', 1).
            " OR " . $this->getAdapter()->quoteInto('lg.released_at IS NULL', 1).
            " OR " . $this->getAdapter()->quoteInto('lg.released_at = 0', 1).
            " )
        ) ";
// quyền sales
        $log_where .= " OR " .
        " ( " . $this->getAdapter()->quoteInto('ssl.staff_id = ?', $params['leader']).
        " AND " . $this->getAdapter()->quoteInto('ssl.is_leader = ?', 1).
        " AND " . $this->getAdapter()->quoteInto('DATE(p.from) >= FROM_UNIXTIME(ssl.joined_at, \'%Y-%m-%d\')', 1).
        " AND (".
        $this->getAdapter()->quoteInto('DATE(p.from) < FROM_UNIXTIME(ssl.released_at, \'%Y-%m-%d\')', 1).
        " OR " . $this->getAdapter()->quoteInto('ssl.released_at IS NULL', 1).
        " OR " . $this->getAdapter()->quoteInto('ssl.released_at = 0', 1).
        " )
    )";
    $select->where($log_where);
} elseif (isset($params['asm']) and $params['asm']) {
    $QAsm = new Application_Model_Asm();
    $list_regions = $QAsm->get_cache($params['asm']);
    $list_regions = isset($list_regions['province']) && is_array($list_regions['province']) ? $list_regions['province'] : array();
    if (count($list_regions))
$select->where('st.regional_market IN (?)', $list_regions); // lọc staff thuộc regional_market trên
else
    $select->where('1=0', 1);
}
if (isset($params['from_date']) and $params['from_date']){
    $date = explode('/', $params['from_date']);
    $select->where('date(p.from) >= ?', $date[2].'-'.$date[1].'-'.$date[0]);
}
if (isset($params['to_date']) and $params['to_date']){
    $date = explode('/', $params['to_date']);
    $select->where('date(p.from) <= ?', $date[2].'-'.$date[1].'-'.$date[0]);
}
if (isset($params['regional_market']) and $params['regional_market'])
    $select->where('st.regional_market = ?', $params['regional_market']);
if (isset($params['district']) and $params['district'])
    $select->where('st.district = ?', $params['district']);
if (isset($params['store']) and $params['store'])
    $select->where('st.id = ?', $params['store']);
if (isset($params['area_id']) and $params['area_id']){
    $QRegionalMarket = new Application_Model_RegionalMarket();
    $where = $QRegionalMarket->getAdapter()->quoteInto('area_id = ?', $params['area_id']);
    $regional_markets = $QRegionalMarket->fetchAll($where);
    $tem = array();
    foreach ($regional_markets as $regional_market)
        $tem[] = $regional_market->id;
    $select->where('st.regional_market IN (?)', $tem);
}
if (isset($params['sort']) and $params['sort']) {
    $order_str = '';
    switch ( $params['sort'] ) {
        case 'st.id':
        $order_str = 'st.`name`';
        break;
        case 'st.regional_market':
        $select->join(array('rm' => 'regional_market'),
            'st.regional_market = rm.id',
            array());
        $order_str = 'rm.`name`';
        break;
        default:
        break;
    }
    $collate = ' ';
    $desc = (isset($params['desc']) and $params['desc'] == 1) ? ' DESC ' : ' ASC ';
    if ($params['sort'] == 'name'){
        $collate = ' COLLATE utf8_unicode_ci ';
        $order_str .= ' CONCAT(s.firstname, " ",s.lastname) '.$collate . $desc;
    } elseif ( in_array($params['sort'], array('st.regional_market', 'st.id'  ))   ) {
        $collate = ' COLLATE utf8_unicode_ci ';
        $order_str .= $collate . $desc;
    } else {
        $order_str = $params['sort'] . ' ' . $collate . $desc;
    }
    $select->order(new Zend_Db_Expr($order_str));
}
// count timing sale for timing list page
if (isset($params['timing_list']) and $params['timing_list'] == 1) {
    $select->joinLeft(array('ts' => 'timing_sale'), 'p.id = ts.timing_id',
        array('sell_out' => new Zend_Db_Expr('COUNT(ts.imei)') ));
    $select->group('p.id');
}
//echo $select;
if ($limit)
    $select->limitPage($page, $limit);
$result = $db->fetchAll($select);
if ($limit)
    $total = $db->fetchOne("select FOUND_ROWS()");
return $result;
}
function reporttotal($page, $limit, &$total, $params)
{
    $db = Zend_Registry::get('db');
    if ( isset($params['get_total_sales']) and $params['get_total_sales'] )
        $get = array();
    elseif (isset($params['export2']) and $params['export2'])
        $get = array(
            's.id', 's.title','s.code', 's.firstname', 's.lastname', 's.regional_market as s_regional_market','s.status','s.off_date'
        );
    else
        $get = array(
            new Zend_Db_Expr('SQL_CALC_FOUND_ROWS s.id'), 'sid' => 's.id', 's.title','s.code', 's.firstname',
            's.lastname', 's.department', 's.phone_number','s.status','s.off_date'
        );
    $from = explode('/', $params['from']);
    $to = explode('/', $params['to']);
    $select = $db->select()
    ->from(array('s' => 'staff'),
        $get
    )
    ->join(array('t' => 'timing'),
        's.id = t.staff_id AND t.`from` >= \''. $from[2].'-'.$from[1].'-'.$from[0] . ' 00:00:00\' AND t.`from` <= \''. $to[2].'-'.$to[1].'-'.$to[0] . ' 23:59:59\'',
        array('t.from', 't.approved_at', 't.store')
    )
    ->joinLeft(array('sto' => 'store'),
        't.store = sto.id',
        array('store_name' => 'sto.name', 'sto.company_address', 'sto.shipping_address')
    )
    ->joinLeft(array('r' => 'regional_market'),
        'sto.regional_market = r.id',
        array('r_regional_market' => 'r.name'))
    ->joinLeft(array('a' => 'area'),
        'a.id = r.area_id',
        array('area' => 'a.name'))
    ->joinLeft(array('ts' => 'timing_sale'),
        't.id = ts.timing_id  AND t.approved_at IS NOT NULL AND t.approved_at <> 0 AND t.approved_at <> \'\'',
        array('product_count' => 'COUNT(ts.id)'))
    ->group('s.id');

    $sub_select = $db->select()
    ->from(array('t1'=>'timing'), array('t1.staff_id'));
    if ( isset($params['store']) && $params['store'] ) {
        $sub_select->where( 't1.store = ?', $params['store']);
    }
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
    if ( isset($params['asm']) && $params['asm'] ) {
        $QAsm = new Application_Model_Asm();
        $list_regions = $QAsm->get_cache($params['asm']);
        $list_regions = isset($list_regions['district']) && is_array($list_regions['district']) ? $list_regions['district'] : array();
        if (count($list_regions) > 0)
            $select->where( 'sto.district IN (?)', $list_regions);
        else
            $select->where('1=0', 1);
    }
    if ( isset($params['regional_market']) && $params['regional_market'] ) {
        $select->where( 'sto.regional_market = ?', $params['regional_market']);
    }
    if ( isset($params['store']) && $params['store'] ) {
        $select->where( 't.store = ?', $params['store']);
    }
    if (isset( $params['sort'] ) && $params['sort']) {
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
        $order_str .= 'COUNT(ts.id) desc';
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

//echo $select;die;
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

function report($page, $limit, &$total, $params) {
    set_time_limit(0);
    ini_set('memory_limit', '-1');
    error_reporting(~E_ALL);
    ini_set("display_error", '0');
    $db = Zend_Registry::get('db');
    if ( isset($params['get_total_sales']) and $params['get_total_sales'] )
        $get = array();
    elseif (isset($params['export2']) and $params['export2'])
        $get = array(
            'staff_id' => 's.id',
            'staff_code' => 's.code',
            'reporter' => new Zend_Db_Expr("CONCAT(s.firstname, ' ' , s.lastname)"),
            'unit' => new Zend_Db_Expr($db->quote(1)) ,
            'user_status' => new Zend_Db_Expr('(CASE s.status WHEN 1 THEN "Enable" WHEN 0 THEN "Disable" END)') ,
            'staff_status' => new Zend_Db_Expr('(CASE WHEN s.off_date is null THEN "Working" WHEN s.off_date is not null THEN "Quit" END)')
        );
    else
        $get = array(
            new Zend_Db_Expr('SQL_CALC_FOUND_ROWS s.id'), 'sid' => 's.id', 's.title','s.code', 's.firstname', 's.lastname'
        );
    $from = explode('/', $params['from']);
    $to = explode('/', $params['to']);
    if (isset($params['export2']) and $params['export2']){
        $select = $db->select()
        ->from(array('s' => 'staff'), $get)
        ->joinLeft(array('t'    => 'timing'),
            's.id = t.staff_id AND t.approved_at IS NOT NULL AND t.approved_at <> 0 AND t.approved_at <> \'\' AND t.created_at >= \''. $from[2].'-'.$from[1].'-'.$from[0] . ' 00:00:00\' AND t.created_at <= \''. $to[2].'-'.$to[1].'-'.$to[0] . ' 23:59:59\'', array())
        ->joinLeft(array('sto'  => 'store')             , 't.store = sto.id'            , array('store_name' => 'sto.name', 'store_id' => 'sto.id', 'org_id' => 'sto.org_dealer', 'shop_id' => 'sto.store_id', 'sub_district' => 'sto.sub_district'))

        ->joinLeft(array('r'    => 'regional_market')   , 't.store_area = r.id'  , array('province' => 'r.name')) // update timing 08-07-2022

        ->joinLeft(array('d'    => 'regional_market')   , 'sto.district = d.id'         , array('district' => 'd.name', 'com_zone' => 'd.commission_zone'))
        ->joinLeft(array('a'    => 'area')              , 'r.area_id = a.id'            , array('area_id' => 'a.id', 'area' => 'a.name'))
        ->join(array('ts'       => 'timing_sale')       , 't.id = ts.timing_id'         , array('imei' => 'ts.imei', 'time_add' => 'ts.time_add'))
        ->joinLeft(array('gr'   => 'group')             , 's.group_id = gr.id'          , array('group_name' => 'gr.name'))
        ->joinLeft(array('grl'  => 'group')             , 'sl.group_id = grl.id'        , array('group_leader_name' => 'grl.name', 'group_leader_id' => 'grl.id'))
        ->joinLeft(array('o'    => 'org')               , 'sto.org_dealer = o.org_id'   , array('org_name' => 'o.org_name'))
        ->joinLeft(array('st'   => 'store_type')        , 'o.store_type_id = st.store_type_id', array('store_type' => 'st.store_type_name'))
        ->joinLeft(array('i'    => WAREHOUSE_DB.'.imei'), 'ts.imei = i.imei_sn'         , array('activated_date' => 'i.activated_date', 'last_scan' => 'i.stock_shop_scan', 'out_date' => 'i.out_date'))
        ->joinLeft(array('g'    => WAREHOUSE_DB.'.good'), 'ts.product_id = g.id'        , array('good_id' => 'g.id','good_name' => 'g.name','price'=>'g.price_3'))
        ->joinLeft(array('gc'   => WAREHOUSE_DB.'.good_color'), 'ts.model_id = gc.id'   , array('color_id' => 'gc.id','good_color' => 'gc.name'));
        $select->order(array('area ASC','province ASC','district ASC','reporter ASC','store_name ASC','good_name ASC','good_color ASC'));

    } else {

        $select = $db->select()
        ->from(array('s' => 'staff'), $get)
        ->join(array('t'    => 'timing'),
            's.id = t.staff_id AND t.approved_at IS NOT NULL AND t.approved_at <> 0 AND t.approved_at <> \'\' AND t.created_at >= \''. $from[2].'-'.$from[1].'-'.$from[0] . ' 00:00:00\' AND t.created_at <= \''. $to[2].'-'.$to[1].'-'.$to[0] . ' 23:59:59\'',
            array('t.from', 't.approved_at', 't.store'))

        ->join(array('ts'   => 'timing_sale')           , 't.id = ts.timing_id'         , array('product_count' => 'COUNT(ts.id)'))
        ->joinLeft(array('sto'  => 'store')             , 't.store = sto.id'            , array('store_name' => 'sto.name'))
        ->joinLeft(array('o'    => 'org')               , 'sto.org_dealer = o.org_id'   , array('org_name' => 'o.org_name'))

        ->joinLeft(array('r'    => 'regional_market')   , 't.store_area = r.id'  , array('regional_market' => 'r.name')) // update timing 08-07-2022

        ->joinLeft(array('d'    => 'regional_market')   , 'sto.district = d.id'         , array('district' => 'd.name'))
        ->joinLeft(array('a'    => 'area')              , 'a.id = r.area_id'            , array('area' => 'a.name'))
        ->joinLeft(array('g'    => WAREHOUSE_DB.'.good'), 'ts.product_id = g.id'        , array())
        ->joinLeft(array('i'    => WAREHOUSE_DB.'.imei'), 'ts.imei = i.imei_sn AND i.activated_date IS NOT NULL', array('activated_count' => 'COUNT(i.id)'))
        ->joinLeft(array('gkl'  => 'good_kpi_log'),
            "   gkl.good_id = ts.product_id
            AND gkl.color_id = ts.model_id
            AND t.created_at >= CONCAT(gkl.from_date,' 00:00:00')
            AND t.created_at <= CONCAT(gkl.to_date,' 23:59:59')
            ", array('total_price' => new Zend_Db_Expr("SUM(gkl.price)")))
        ->group('s.id');
    }
    $sub_select = $db->select()
    ->from(array('t1'=>'timing'), array('t1.staff_id'));


    if (isset($params['from']) && $params['from'] && !isset($params['to'])) {
        $sub_select->where( 't1.created_at >= ?', $from[2].'-'.$from[1].'-'.$from[0] . ' 00:00:00' );
    } elseif (isset($params['to']) && $params['to'] && !isset($params['from'])) {
        $sub_select->where( 't1.created_at <= ?', $to[2].'-'.$to[1].'-'.$to[0] . ' 23:59:59' );
    } elseif (isset($params['to']) && $params['to'] && isset($params['from']) && $params['from'] ) {
        $sub_select->where( 't1.created_at >= ?', $from[2].'-'.$from[1].'-'.$from[0] .' 00:00:00');
        $sub_select->where( 't1.created_at <= ?', $to[2].'-'.$to[1].'-'.$to[0] . ' 23:59:59');
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

    if(isset($params['company']) && $params['company'] ){
        $select->where('s.company_id =?',$params['company']);
    }

    if (isset($params['brand']) && $params['brand']) {
        if (is_array($params['brand']) && count($params['brand']))
            $select->where('g.brand_id IN (?)', $params['brand']);
        elseif (is_numeric($params['brand']))
            $select->where('g.brand_id = ?', intval($params['brand']));
        else
            $select->where('1=0', 1);
    }

    if(isset($params['position']) && $params['position'] == 1){
        $select->where('s.group_id IN (?)',array(4,64));
    }

    if(isset($params['position']) && $params['position'] == 2){
        $select->where('s.group_id = 9');
    }

    if(isset($params['position']) && $params['position'] == 3){
        $select->where('s.group_id = 5');
    }

    if ( isset($params['staff_code']) && $params['staff_code'] ) {
        $select->where('s.code = ?', $params['staff_code']);
    }

    if ( isset($params['phone_number']) && $params['phone_number'] ) {
        $select->where( 's.phone_number LIKE ?', '%'.$params['phone_number'].'%');
    }

    if ( isset($params['chk_tme']) && $params['chk_tme'] ) {
        $select->where('sto.rank <> ?', $params['chk_tme']);
    }


    if (isset($params['good']) && $params['good']) {
        if (is_array($params['good']) && count($params['good']))
            $select->where('ts.product_id IN (?)', $params['good']);
        elseif (is_numeric($params['good']))
            $select->where('ts.product_id = ?', intval($params['good']));
        else
            $select->where('1=0', 1);
    }


    if ( isset($params['staff_code']) && $params['staff_code'] ) {
        $select->where('s.code = ?', $params['staff_code']);
    }
    if ( isset($params['phone_number']) && $params['phone_number'] ) {
        $select->where( 's.phone_number LIKE ?', '%'.$params['phone_number'].'%');
    }
    if ( isset($params['chk_tme']) && $params['chk_tme'] ) {
        $select->where('sto.rank <> ?', $params['chk_tme']);
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
            $select->where('sto.regional_market IN (?)', $params['regional_market']);
        elseif (is_numeric($params['regional_market']))
            $select->where('sto.regional_market = ?', intval($params['regional_market']));
        else
            $select->where('1=0', 1);
    }
// Add Filter District
    if (isset($params['district']) && $params['district']) {
        if (is_array($params['district']) && count($params['district']))
            $select->where('sto.district IN (?)', $params['district']);
        elseif (is_numeric($params['district']))
            $select->where('sto.district = ?', intval($params['district']));
        else
            $select->where('1=0', 1);
    }

// -----(reportActivate)
    if ( isset($params['actived']) && $params['actived'] == 'Not Actived' ) {
        $select->where('i.activated_date IS NULL');
    }
    if ( isset($params['actived']) && $params['actived'] == 'Actived' ) {
        $select->where('i.activated_date IS NOT NULL');
    }

// Add Filter Store
    if (isset($params['store']) && $params['store']) {
        if (is_array($params['store']) && count($params['store']))
            $select->where('t.store IN (?)', $params['store']);
        elseif (is_numeric($params['store']))
            $select->where('t.store = ?', intval($params['store']));
        else
            $select->where('1=0', 1);
    }
// Add Filter Store Type
    if (isset($params['store_type']) && $params['store_type']) {

        if (is_array($params['store_type']) && count($params['store_type']))
            $select->where('sto.org_dealer IN (?)', $params['store_type']);
        elseif (is_numeric($params['store_type']))
            $select->where('sto.org_dealer = ?', intval($params['store_type']));
        else
            $select->where('1=0', 1);
    }
// check ermission ASM, ASM Stand by, Sale Admin, Traning

    if ( isset($params['asm']) && $params['asm'] ) {

        $QAsm = new Application_Model_Asm();

        $list_regions = $QAsm->get_cache($params['asm']);

        $list_regions = isset($list_regions['province']) && is_array($list_regions['province']) ? $list_regions['province'] : array();
        if (count($list_regions) > 0)
            $select->where( 'sto.province_id IN (?)', $list_regions);
        else
            $select->where('1=0', 1);
    }

// check ermission AM
    if ( isset($params['am']) && $params['am'] ) {
        $QAm = new Application_Model_Am();
        $list_org = $QAm->get_cache($params['am']);
        
        $list_org = isset($list_org['store_type']) && is_array($list_org['store_type']) ? $list_org['store_type'] : array();
        if (count($list_org) > 0)
            $select->where( 'sto.org_dealer IN (?)', $list_org);
        else
            $select->where('1=0', 1);
    }
// check Permission Admin Brandshop
    if ( isset($params['admin_bs']) && $params['admin_bs'] ) {
        $select->where('(sto.org_dealer = ?', 18);
        $select->orWhere('o.store_type_id = ?)', 3);
    }
    if (isset($params['sale_id']) && intval($params['sale_id']) > 0) {
        $select->where('t.sales_id =?', $params['sale_id']);
    // $QSalesArea = new Application_Model_SalesArea();
    // $list_regions = $QSalesArea->get_cache($params['sale_id']);
    // $list_regions = isset($list_regions['district']) && is_array($list_regions['district']) ? $list_regions['district'] : array();
    // if (count($list_regions) > 0) {
    //     $select->where('( sto.district IN (?)', $list_regions);
    //     $sub_select = $db->select()
    //     ->from(array('ssl' => 'store_staff'), array('st_id' => 'ssl.store_id'))
    //     ->where('staff_id = ?', $params['sale_id']);
    //     $ss_result = $db->fetchAll($sub_select);
    //     foreach ($ss_result as $key => $value) { $ss_data[] = $value['st_id']; }
    //     $select->orWhere('sto.id IN (?) )', $ss_data);
    // } else {
    //     $select->joinRight(array('ssl' => 'store_staff_log'), 'ssl.store_id=t.store', array());
    //     $log_where = $this->getAdapter()->quoteInto('ssl.staff_id = ?', $params['sale_id']).
    //     " AND " . $this->getAdapter()->quoteInto('ssl.is_leader = ?', 1).
    //     " AND " . $this->getAdapter()->quoteInto('t.created_at >= FROM_UNIXTIME(ssl.joined_at)', 1).
    //     " AND (".
    //     $this->getAdapter()->quoteInto('t.created_at < FROM_UNIXTIME(ssl.released_at)', 1).
    //     " OR " . $this->getAdapter()->quoteInto('ssl.released_at IS NULL', 1).
    //     " OR " . $this->getAdapter()->quoteInto('ssl.released_at = 0', 1).
    //     " ) ";
    //     $select->where($log_where);
    // }
    }
    if (isset($params['pcm_id']) && intval($params['pcm_id']) > 0) {
        $select
        ->joinRight(array('ssl' => 'store_staff_log'), 'ssl.store_id=t.store', array());
        $log_where = $this->getAdapter()->quoteInto('ssl.staff_id = ?', $params['pcm_id']).
        " AND " . $this->getAdapter()->quoteInto('ssl.is_leader = ?', 2).
        " AND " . $this->getAdapter()->quoteInto('DATE(t.from) >= FROM_UNIXTIME(ssl.joined_at, \'%Y-%m-%d\')', 1).
        " AND (".
        $this->getAdapter()->quoteInto('DATE(t.from) < FROM_UNIXTIME(ssl.released_at, \'%Y-%m-%d\')', 1).
        " OR " . $this->getAdapter()->quoteInto('ssl.released_at IS NULL', 1).
        " OR " . $this->getAdapter()->quoteInto('ssl.released_at = 0', 1).
        " ) ";
        $select->where($log_where);
    }
    if (isset($params['bm_id']) && intval($params['bm_id']) > 0) {
        $select
        ->joinRight(array('ssl' => 'store_staff_log'), 'ssl.store_id=t.store', array());
        $log_where = $this->getAdapter()->quoteInto('ssl.staff_id = ?', $params['bm_id']).
        " AND " . $this->getAdapter()->quoteInto('ssl.is_leader = ?', 3).
        " AND " . $this->getAdapter()->quoteInto('DATE(t.from) >= FROM_UNIXTIME(ssl.joined_at, \'%Y-%m-%d\')', 1).
        " AND (".
        $this->getAdapter()->quoteInto('DATE(t.from) < FROM_UNIXTIME(ssl.released_at, \'%Y-%m-%d\')', 1).
        " OR " . $this->getAdapter()->quoteInto('ssl.released_at IS NULL', 1).
        " OR " . $this->getAdapter()->quoteInto('ssl.released_at = 0', 1).
        " ) ";
        $select->where($log_where);
    }
    if (isset($params['leader_id']) && intval($params['leader_id']) > 0) {
        $select
        ->joinRight(array('ssl' => 'store_leader_log'), 'ssl.store_id=t.store', array());
        $log_where = $this->getAdapter()->quoteInto('ssl.staff_id = ?', $params['leader_id']).
        " AND " . $this->getAdapter()->quoteInto('DATE(t.from) >= FROM_UNIXTIME(ssl.joined_at, \'%Y-%m-%d\')', 1).
        " AND (".
        $this->getAdapter()->quoteInto('DATE(t.from) < FROM_UNIXTIME(ssl.released_at, \'%Y-%m-%d\')', 1).
        " OR " . $this->getAdapter()->quoteInto('ssl.released_at IS NULL', 1).
        " OR " . $this->getAdapter()->quoteInto('ssl.released_at = 0', 1).
        " ) ";
        $select->where($log_where);
    }
/*
// check permission Sale
if ( isset($params['sale_id']) && $params['sale_id'] ) {
$QStoreStaff = new Application_Model_StoreStaff();
$tmp_where = $QStoreStaff->getAdapter()->quoteInto('staff_id = ?', $params['sale_id']);
$result = $QStoreStaff->fetchAll($tmp_where,'id');
for($i=0;$i<count($result);$i++) {
$list_storestaff[$i] = $result[$i]['store_id'];
}
if (count($list_storestaff) > 0)
$select->where( 'sto.id IN (?)', $list_storestaff);
else
$select->where('1=0', 1);
}
// check permission Sale Leader
if ( isset($params['leader_id']) && $params['leader_id'] ) {
$QStoreLeader = new Application_Model_StoreLeader();
$leader_where = $QStoreLeader->getAdapter()->quoteInto('staff_id = ?', $params['leader_id']);
$result_leader = $QStoreLeader->fetchAll($leader_where,'id');
for($i=0;$i<count($result_leader);$i++) {
$list_storeleader[$i] = $result_leader[$i]['store_id'];
}
if (count($list_storeleader) > 0)
$select->where( 'sto.id IN (?)', $list_storeleader);
else
$select->where('1=0', 1);
}
*/
/*
if ( isset($params['regional_market']) && $params['regional_market'] ) {
$select->where( 'sto.regional_market = ?', $params['regional_market']);s
}
if ( isset($params['store']) && $params['store'] ) {
$select->where( 't.store = ?', $params['store']);
}
*/

$select->where('ts.imei NOT IN (SELECT imei from timing_control)');

if (isset($params['sort']) && $params['sort'] && !isset($params['export2'])) {
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

// echo $select; die;

$analytics = $db->fetchAll($select);
$total = $db->fetchOne("select FOUND_ROWS()");
$sales = array();
if (isset($params['export2']) and $params['export2']) {
    $sales = $analytics;
} else {
    foreach ($analytics as $key => $value) {
        $temp = array();
        $temp['activated_total'] = $value['activated_count'];
        $temp['total'] = $value['product_count'];
        $temp['title'] = $value['title'];
        $temp['code'] = $value['code'];
        $temp['firstname'] = $value['firstname'];
        $temp['lastname'] = $value['lastname'];
        $temp['phone_number'] = $value['phone_number'];
        $temp['regional_market'] = $value['regional_market'];
        $temp['store'] = $value['store'];
        $temp['area'] = $value['area'];
        $temp['total_price'] = $value['total_price'];
        $sales[$value['sid']] = $temp;
    }
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
        $select = $db->select()
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
        //      $areas = $QArea->get_cache();
    //      foreach ($areas as $area => $v) {
    // $select  = $db->select()
    //  ->from(array('st'=>'sales_team'), array('st.id'));
// $st_ids = $db->fetchAll($select);
// $select = $db->select()
    //  ->from(array('sts'=>'sales_team_staff'), array('sts.staff_id'))
    //  ->where('sts.is_leader = 1 AND sts.sales_team_id IN (?)', $st_ids);
// $result[$area] = $db->fetchAll($select);
    //      }
//         $cache->save($result, 'leader_by_area_cache', array(), null);
//     }
//     return $result;
// }
function report_by_staff($params, &$analytics, &$count) {
    $db = Zend_Registry::get('db');

    $select = $db->select()->from(array('s' => HR_DB.'.staff'),array());
    $select->join(array('t' => HR_DB.'.timing'), 's.id = t.staff_id AND t.approved_at IS NOT NULL AND t.approved_at <> 0 AND t.approved_at <> \'\'', array('time' => 't.created_at',  't.store'))
    ->join(array('ts' => HR_DB.'.timing_sale'), 'ts.timing_id = t.id', array('imei' => 'ts.imei'))
    ->join(array('p' => WAREHOUSE_DB.'.good'), 'p.id = ts.product_id', array('product_name' => 'p.desc', 'product_id' => 'p.id'))
    ->join(array('sto' => HR_DB.'.store'), 't.store = sto.id', array())
    ->join(array('r' => HR_DB.'.regional_market'), 'r.id = sto.regional_market', array())
    ->join(array('a' => HR_DB.'.area'), 'r.area_id = a.id', array())
    ->joinLeft(array('c' => HR_DB.'.customer'), 'c.id = ts.customer_id', array('customer_name' => 'c.name', 'customer_phone' => 'phone_number', 'customer_address' => 'c.address'))
    ->joinLeft(array('b' => WAREHOUSE_DB.'.brand'),'b.id = p.brand_id',array('brand_name' => 'b.name'))
    ->order('t.created_at DESC');

    if ( isset($params['staff_id']) && $params['staff_id'] == 6535) {
        if(isset($params['rgm_area']) && $params['rgm_area']){
            $select->joinLeft(array('i' => WAREHOUSE_DB.'.imei'),'i.imei_sn = ts.imei',array());
            $select->joinLeft(array('d' => WAREHOUSE_DB.'.distributor'),'d.id = i.distributor_id',array());
            $select->joinLeft(array('rm' => HR_DB.'.regional_market'),'rm.id = d.region',array());
            $select->joinLeft(array('a11' => HR_DB.'.area'),'a11.id = rm.area_id',array());
            $select->where('a11.id IN ('.$params['rgm_area'].')');
        }

        $select->where('t.staff_id = ?', $params['staff_id']);
    }else{
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
        $select->where( 't.created_at > ?', $from[2].'-'.$from[1].'-'.$from[0] . ' 00:00:00' );
    } elseif (isset($params['to']) && $params['to'] && !isset($params['from'])) {
        $select->where( 't.created_at < ?', $to[2].'-'.$to[1].'-'.$to[0] . ' 23:59:59' );
    } elseif (isset($params['to']) && $params['to'] && isset($params['from']) && $params['from'] ) {
        $select->where( 't.created_at >= ?', $from[2].'-'.$from[1].'-'.$from[0] .' 00:00:00');
        $select->where( 't.created_at <= ?', $to[2].'-'.$to[1].'-'.$to[0] . ' 23:59:59');
    }

    // check ermission ASM, ASM Stand by, Sale Admin, Traning
    if ( isset($params['asm']) && $params['asm'] ) {
        $select->where('t.asm_id = ?',$params['asm']);

        // $QAsm = new Application_Model_Asm();
        // $list_regions = $QAsm->get_cache($params['asm']);
        // $list_regions = isset($list_regions['district']) && is_array($list_regions['district']) ? $list_regions['district'] : array();
        // if (count($list_regions) > 0)
        //     $select->where( 'sto.district IN (?)', $list_regions);
        // else
        //     $select->where('1=0', 1);
    }


    // check ermission AM
    if ( isset($params['am']) && $params['am'] ) {
        $QAm = new Application_Model_Am();
        $list_org = $QAm->get_cache($params['am']);
        $list_org = isset($list_org['store_type']) && is_array($list_org['store_type']) ? $list_org['store_type'] : array();
        if (count($list_org) > 0)
            $select->where( 'sto.org_dealer IN (?)', $list_org);
        else
            $select->where('1=0', 1);
    }

    if (isset($params['sale_id']) && intval($params['sale_id']) > 0) {
        $select->where('t.sales_id =?',$params['sale_id']);

        // $QSalesArea = new Application_Model_SalesArea();
        // $list_regions = $QSalesArea->get_cache($params['sale_id']);
        // $list_regions = isset($list_regions['district']) && is_array($list_regions['district']) ? $list_regions['district'] : array();
        // if (count($list_regions) > 0) {
        //     $select->where('( sto.district IN (?)', $list_regions);
        //     $sub_select = $db->select()
        //     ->from(array('ssl' => 'store_staff'), array('st_id' => 'ssl.store_id'))
        //     ->where('staff_id = ?', $params['sale_id']);
        //     $ss_result = $db->fetchAll($sub_select);
        //     foreach ($ss_result as $key => $value) { $ss_data[] = $value['st_id']; }
        //     $select->orWhere('sto.id IN (?) )', $ss_data);
        // } else {
        //     $select->joinRight(array('ssl' => 'store_staff_log'), 'ssl.store_id=t.store', array());
        //     $log_where = $this->getAdapter()->quoteInto('ssl.staff_id = ?', $params['sale_id']).
        //     " AND " . $this->getAdapter()->quoteInto('ssl.is_leader = ?', 1).
        //     " AND " . $this->getAdapter()->quoteInto('t.created_at >= FROM_UNIXTIME(ssl.joined_at)', 1).
        //     " AND (".
        //     $this->getAdapter()->quoteInto('t.created_at < FROM_UNIXTIME(ssl.released_at)', 1).
        //     " OR " . $this->getAdapter()->quoteInto('ssl.released_at IS NULL', 1).
        //     " OR " . $this->getAdapter()->quoteInto('ssl.released_at = 0', 1).
        //     " ) ";
        //     $select->where($log_where);
        // }
    }

    if (isset($params['pcm_id']) && intval($params['pcm_id']) > 0) {
        $select
        ->joinRight(array('ssl' => 'store_staff_log'), 'ssl.store_id=t.store', array());
        $log_where = $this->getAdapter()->quoteInto('ssl.staff_id = ?', $params['pcm_id']).
        " AND " . $this->getAdapter()->quoteInto('ssl.is_leader = ?', 2).
        " AND " . $this->getAdapter()->quoteInto('DATE(t.from) >= FROM_UNIXTIME(ssl.joined_at, \'%Y-%m-%d\')', 1).
        " AND (".
        $this->getAdapter()->quoteInto('DATE(t.from) < FROM_UNIXTIME(ssl.released_at, \'%Y-%m-%d\')', 1).
        " OR " . $this->getAdapter()->quoteInto('ssl.released_at IS NULL', 1).
        " OR " . $this->getAdapter()->quoteInto('ssl.released_at = 0', 1).
        " ) ";
        $select->where($log_where);
    }

    if (isset($params['leader_id']) && intval($params['leader_id']) > 0) {
        $select
        ->joinRight(array('ssl' => 'store_leader_log'), 'ssl.store_id=t.store', array());
        $log_where = $this->getAdapter()->quoteInto('ssl.staff_id = ?', $params['leader_id']).
        " AND " . $this->getAdapter()->quoteInto('DATE(t.from) >= FROM_UNIXTIME(ssl.joined_at, \'%Y-%m-%d\')', 1).
        " AND (".
        $this->getAdapter()->quoteInto('DATE(t.from) < FROM_UNIXTIME(ssl.released_at, \'%Y-%m-%d\')', 1).
        " OR " . $this->getAdapter()->quoteInto('ssl.released_at IS NULL', 1).
        " OR " . $this->getAdapter()->quoteInto('ssl.released_at = 0', 1).
        " ) ";
        $select->where($log_where);
    }

    $stmt  = $select->query();
    $total = $stmt->fetchAll();
    $sales = array();
    $analytics = array();
    $count = 0;

    // echo $select; die;

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
            $temp['brand_name']       = $value['brand_name'];
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

function report_by_store($page, $limit, &$total, $params){
    $db = Zend_Registry::get('db');
    $sub_select = $db->select()
    ->from(array('t1'=>'timing'), array())
    ->join(array('ts1' => 'timing_sale'),
        't1.id = ts1.timing_id',
        array('product_count' => 'COUNT(DISTINCT ts1.imei)'))
    ->where('t1.approved_at IS NOT NULL', 1)
    ->where('t1.approved_at <> 0', 1)
    ->where('t1.approved_at <> \'\'', 1)
    ->where('t1.store=s.id', 1)
    ;
    $from = explode('/', $params['from']);
    $to = explode('/', $params['to']);
    if (isset($params['from']) && $params['from'] && !isset($params['to'])) {
        $sub_select->where('t1.from >= ?', $from[2].'-'.$from[1].'-'.$from[0] . ' 00:00:00');
    } elseif (isset($params['to']) && $params['to'] && !isset($params['from'])) {
        $sub_select->where('t1.from <= ?', $to[2].'-'.$to[1].'-'.$to[0] . ' 23:59:59');
    } elseif (isset($params['to']) && $params['to'] && isset($params['from']) && $params['from'] ) {
        $sub_select->where('t1.from >= ?', $from[2].'-'.$from[1].'-'.$from[0] . ' 00:00:00');
        $sub_select->where('t1.from <= ?', $to[2].'-'.$to[1].'-'.$to[0] . ' 23:59:59');
    }
    if (isset($params['asm']) && intval($params['asm']) > 0) {
        $QAsm = new Application_Model_Asm();
        $list_regions = $QAsm->get_cache($params['asm']);
        $list_regions = isset($list_regions['district']) && is_array($list_regions['district']) ? $list_regions['district'] : array();
        $sub_select->join(array('st' => 'store'), 'st.id=t1.store', array())
        ->join(array('r' => 'regional_market'), 'r.id=st.district', array());
        if (count($list_regions))
            $sub_select->where('st.district IN (?)', $list_regions);
        else
            $sub_select->where('1=0', 1);
    }
    if (isset($params['sales_store']) && intval($params['sales_store']) > 0) {
        $sub_select
        ->join(array('str' => 'store'), 't1.store = str.id', array())
        ->joinLeft(array('ss' => 'store_staff_log'), 'ss.store_id=str.id', array());
        $log_where = $this->getAdapter()->quoteInto('ss.staff_id = ?', $params['sales_store']).
        " AND " . $this->getAdapter()->quoteInto('ss.is_leader = ?', 1).
        " AND " . $this->getAdapter()->quoteInto('DATE(t1.from) >= FROM_UNIXTIME(ss.joined_at, \'%Y-%m-%d\')', 1).
        " AND (".
        $this->getAdapter()->quoteInto('DATE(t1.from) < FROM_UNIXTIME(ss.released_at, \'%Y-%m-%d\')', 1).
        " OR " . $this->getAdapter()->quoteInto('ss.released_at IS NULL', 1).
        " OR " . $this->getAdapter()->quoteInto('ss.released_at = 0', 1).
        " )";
        $sub_select->where($log_where);
    }
    if (isset($params['leader_province']) && intval($params['leader_province']) > 0) {
        $sub_select
        ->join(array('str' => 'store'), 't1.store = str.id', array())
        ->joinLeft(array('lg' => 'store_leader_log'), 'str.id = lg.store_id', array())
        ->joinLeft(array('ssl' => 'store_staff_log'), 'str.id = ssl.store_id', array());
// quyền leader
        $log_where = " ( " . $this->getAdapter()->quoteInto('lg.staff_id = ?', $params['leader_province']).
        " AND " . $this->getAdapter()->quoteInto('DATE(t1.from) >= FROM_UNIXTIME(lg.joined_at, \'%Y-%m-%d\')', 1).
        " AND (".
        $this->getAdapter()->quoteInto('DATE(t1.from) < FROM_UNIXTIME(lg.released_at, \'%Y-%m-%d\')', 1).
        " OR " . $this->getAdapter()->quoteInto('lg.released_at IS NULL', 1).
        " OR " . $this->getAdapter()->quoteInto('lg.released_at = 0', 1).
        " )
    ) ";
// quyền sales
    $log_where .= " OR " .
    " ( " . $this->getAdapter()->quoteInto('ssl.staff_id = ?', $params['leader_province']).
    " AND " . $this->getAdapter()->quoteInto('ssl.is_leader = ?', 1).
    " AND " . $this->getAdapter()->quoteInto('DATE(t1.from) >= FROM_UNIXTIME(ssl.joined_at, \'%Y-%m-%d\')', 1).
    " AND (".
    $this->getAdapter()->quoteInto('DATE(t1.from) < FROM_UNIXTIME(ssl.released_at, \'%Y-%m-%d\')', 1).
    " OR " . $this->getAdapter()->quoteInto('ssl.released_at IS NULL', 1).
    " OR " . $this->getAdapter()->quoteInto('ssl.released_at = 0', 1).
    " )
)";
$sub_select->where($log_where);
}
if ( isset($params['get_total_sales']) and $params['get_total_sales'] ){
    $get = array();
    array_push($get, new Zend_Db_Expr('('.$sub_select.') as product_count'));
} elseif (isset($params['export']) and $params['export']) {
    $get = array(
        's.id', 'store_name' => 's.name', 'company_address' => 's.company_address', 'shipping_address' => 's.shipping_address', new Zend_Db_Expr('('.$sub_select.') as product_count')
    );
} else {
    $get = array(
        new Zend_Db_Expr('SQL_CALC_FOUND_ROWS s.id'), 'store_name' => 's.name'
    );
    array_push($get, new Zend_Db_Expr('('.$sub_select.') as product_count'));
}
$select = $db->select()
->from(array('s' => 'store'), $get
);
$select
->join(array('r' => 'regional_market'),
    's.regional_market = r.id',
    array('province_name' => 'r.name'))
->join(array('d' => 'regional_market'),
    's.district = d.id',
    array('district_name' => 'd.name'))
->join(array('a' => 'area'),
    'r.area_id = a.id',
    array('area_name' => 'a.name', 'area_id' => 'a.id'));
$select->where('s.district IS NOT NULL', 1);
$select->where('s.district <> \'\'', 1);
$select->where('s.district <> 0', 1);
if ($params['export'])
    $select->joinLeft(array('sls' => 'store_staff'), 'sls.store_id=s.id AND sls.is_leader = 1', array('sales_id' => 'sls.staff_id'));
if ( isset($params['name']) && $params['name'] )
    $select->where('s.name LIKE ?', '%'.$params['name'].'%');
if ( isset($params['area']) && $params['area'] ) {
    if (is_array($params['area']))
        $select->where( 'r.area_id IN (?)', $params['area']);
    else
        $select->where( 'r.area_id = ?', $params['area']);
}
if ( isset($params['regional_market']) && $params['regional_market'] )
    $select->where( 's.regional_market = ?', $params['regional_market']);
if ( isset($params['district']) && $params['district'] )
    $select->where( 's.district = ?', $params['district']);
if ( isset($params['store']) && $params['store'] )
    $select->where( 's.id = ?', $params['store']);
if ( isset($params['sales_from']) and $params['sales_from']!='' )
    $select->having('product_count >= '.$params['sales_from']);
if ( isset($params['sales_to']) and $params['sales_to']!='' )
    $select->having('product_count <= '.$params['sales_to']);
if ((isset($params['asm']) && intval($params['asm']) > 0) || (isset($params['asm']) && intval($params['asm']) > 0)) {
    if (count($list_regions))
        $select->where('s.district IN (?)', $list_regions);
    else
        $select->where('1=0', 1);
}
if (isset($params['sales_store']) && intval($params['sales_store']) > 0) {
    $select->join(array('sl' => 'store_staff'), 'sl.store_id=s.id', array());
    $select->where('sl.staff_id = ?', $params['sales_store']);
}
if (isset($params['leader_province']) && intval($params['leader_province']) > 0) {
    $select->join(array('sl' => 'store_leader'), 'sl.store_id=s.id', array());
    $select->where('sl.staff_id = ?', $params['leader_province']);
}
if (!$params['export'])
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
// $select->where('(del IS NULL OR del = 0)', 1);
    if ($params['export'])
        return $select;
    if (!$params['export'] and $limit)
        $select->limitPage($page, $limit);
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


// Report By Product : Short Report
function short_report_by_product($params, &$analytics, &$count) {
    $db = Zend_Registry::get('db');
    $select = $db->select()->from(array('s' => 'staff'),array());
    $select
    ->join(array('t' => 'timing'), 's.id = t.staff_id AND t.approved_at IS NOT NULL AND t.approved_at <> 0 AND t.approved_at <> \'\'', array('time' => 't.created_at',  't.store'))
    ->join(array('ts' => 'timing_sale'), 'ts.timing_id = t.id', array('imei' => 'ts.imei'))
    ->join(array('p' => WAREHOUSE_DB.'.good'), 'p.id = ts.product_id', array('product_name' => 'p.desc', 'product_id' => 'p.id'))
    ->join(array('sto' => 'store'), 't.store = sto.id', array())
    ->join(array('o' => 'org'), 'sto.org_dealer = o.org_id', array())
    ->join(array('r' => 'regional_market'), 'r.id = sto.regional_market', array())
    ->join(array('a' => 'area'), 'r.area_id = a.id', array())
    ->joinLeft(array('c' => 'customer'), 'c.id = ts.customer_id', array('customer_name' => 'c.name', 'customer_phone' => 'phone_number', 'customer_address' => 'c.address'))
    ->order('t.created_at DESC');
    if ( isset($params['model']) && $params['model'] ) {
        $select->where('ts.product_id = ?', $params['model']);
    }
    if ( isset($params['area']) && $params['area'] ) {
        $select->where('a.id = ?', $params['area']);
    }
    $from = explode('/', $params['from']);
    $to = explode('/', $params['to']);
    if (isset($params['from']) && $params['from'] && !isset($params['to'])) {
        $select->where( 't.created_at > ?', $from[2].'-'.$from[1].'-'.$from[0] . ' 00:00:00' );
    } elseif (isset($params['to']) && $params['to'] && !isset($params['from'])) {
        $select->where( 't.created_at < ?', $to[2].'-'.$to[1].'-'.$to[0] . ' 23:59:59' );
    } elseif (isset($params['to']) && $params['to'] && isset($params['from']) && $params['from'] ) {
        $select->where( 't.created_at >= ?', $from[2].'-'.$from[1].'-'.$from[0] .' 00:00:00');
        $select->where( 't.created_at <= ?', $to[2].'-'.$to[1].'-'.$to[0] . ' 23:59:59');
    }
// check ermission ASM, ASM Stand by, Sale Admin, Traning
    if ( isset($params['asm']) && $params['asm'] ) {
        $QAsm = new Application_Model_Asm();
        $list_regions = $QAsm->get_cache($params['asm']);
        $list_regions = isset($list_regions['district']) && is_array($list_regions['district']) ? $list_regions['district'] : array();
        if (count($list_regions) > 0)
            $select->where( 'sto.district IN (?)', $list_regions);
        else
            $select->where('1=0', 1);
    }
// check ermission AM
    if ( isset($params['am']) && $params['am'] ) {
        $QAm = new Application_Model_Am();
        $list_org = $QAm->get_cache($params['am']);
        $list_org = isset($list_org['store_type']) && is_array($list_org['store_type']) ? $list_org['store_type'] : array();
        if (count($list_org) > 0)
            $select->where( 'sto.org_dealer IN (?)', $list_org);
        else
            $select->where('1=0', 1);
    }
// check Permission Admin Brandshop
    if ( isset($params['admin_bs']) && $params['admin_bs'] ) {
        $select->where('(sto.org_dealer = ?', 18);
        $select->orWhere('o.store_type_id = ?)', 3);
    }
    if (isset($params['sale_id']) && intval($params['sale_id']) > 0) {
        $QSalesArea = new Application_Model_SalesArea();
        $list_regions = $QSalesArea->get_cache($params['sale_id']);
        $list_regions = isset($list_regions['district']) && is_array($list_regions['district']) ? $list_regions['district'] : array();
        if (count($list_regions) > 0) {
            $select->where('( sto.district IN (?)', $list_regions);
            $sub_select = $db->select()
            ->from(array('ssl' => 'store_staff'), array('st_id' => 'ssl.store_id'))
            ->where('staff_id = ?', $params['sale_id']);
            $ss_result = $db->fetchAll($sub_select);
            foreach ($ss_result as $key => $value) { $ss_data[] = $value['st_id']; }
            $select->orWhere('sto.id IN (?) )', $ss_data);
        } else {
            $select->joinRight(array('ssl' => 'store_staff_log'), 'ssl.store_id=t.store', array());
            $log_where = $this->getAdapter()->quoteInto('ssl.staff_id = ?', $params['sale_id']).
            " AND " . $this->getAdapter()->quoteInto('ssl.is_leader = ?', 1).
            " AND " . $this->getAdapter()->quoteInto('t.created_at >= FROM_UNIXTIME(ssl.joined_at)', 1).
            " AND (".
            $this->getAdapter()->quoteInto('t.created_at < FROM_UNIXTIME(ssl.released_at)', 1).
            " OR " . $this->getAdapter()->quoteInto('ssl.released_at IS NULL', 1).
            " OR " . $this->getAdapter()->quoteInto('ssl.released_at = 0', 1).
            " ) ";
            $select->where($log_where);
        }
    }
    if (isset($params['pcm_id']) && intval($params['pcm_id']) > 0) {
        $select
        ->joinRight(array('ssl' => 'store_staff_log'), 'ssl.store_id=t.store', array());
        $log_where = $this->getAdapter()->quoteInto('ssl.staff_id = ?', $params['pcm_id']).
        " AND " . $this->getAdapter()->quoteInto('ssl.is_leader = ?', 2).
        " AND " . $this->getAdapter()->quoteInto('DATE(t.from) >= FROM_UNIXTIME(ssl.joined_at, \'%Y-%m-%d\')', 1).
        " AND (".
        $this->getAdapter()->quoteInto('DATE(t.from) < FROM_UNIXTIME(ssl.released_at, \'%Y-%m-%d\')', 1).
        " OR " . $this->getAdapter()->quoteInto('ssl.released_at IS NULL', 1).
        " OR " . $this->getAdapter()->quoteInto('ssl.released_at = 0', 1).
        " ) ";
        $select->where($log_where);
    }
    if (isset($params['leader_id']) && intval($params['leader_id']) > 0) {
        $select
        ->joinRight(array('ssl' => 'store_leader_log'), 'ssl.store_id=t.store', array());
        $log_where = $this->getAdapter()->quoteInto('ssl.staff_id = ?', $params['leader_id']).
        " AND " . $this->getAdapter()->quoteInto('DATE(t.from) >= FROM_UNIXTIME(ssl.joined_at, \'%Y-%m-%d\')', 1).
        " AND (".
        $this->getAdapter()->quoteInto('DATE(t.from) < FROM_UNIXTIME(ssl.released_at, \'%Y-%m-%d\')', 1).
        " OR " . $this->getAdapter()->quoteInto('ssl.released_at IS NULL', 1).
        " OR " . $this->getAdapter()->quoteInto('ssl.released_at = 0', 1).
        " ) ";
        $select->where($log_where);
    }
    $stmt  = $select->query();
    $total = $stmt->fetchAll();
    $sales = array();
    $analytics = array();
    $count = 0;
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
    return $sales;
}
/**
* Read /application/models/docs/report_by_dealer.sql for detail
* @param  [type] $page   [description]
* @param  [type] $limit  [description]
* @param  [type] &$total [description]
* @param  [type] $params [description]
* @return [type]         [description]
*/


function report_by_dealer($page, $limit, &$total, $params)
{
    $db = Zend_Registry::get('db');
    $from = explode('/', $params['from']);
    $to = explode('/', $params['to']);
    $select_count_store = $db->select()
    ->from(array('ss' => 'store'), array('num_store' => 'COUNT(DISTINCT ss.id)'))
    ->where('ss.d_id = d.id');
    if ( (isset($params['get_total_sales']) and $params['get_total_sales'])) {
        $select_colums = array(
            'id'                  => 'A.id',
            'distributor_name'    => 'A.distributor_name',
            'district_name'       => 'A.district_name',
            'district_id'         => 'A.district_id',
            'region_name'         => 'A.region_name',
            'region_id'           => 'A.region_id',
            'area_name'           => 'A.area_name',
            'area_id'             => 'A.area_id',
            'total_store'         => 'SUM(A.num_store)',
            'total_store_sellout' => 'SUM(A.total_store_sellout)',
            'total_quantity'      => 'SUM(A.product_count)',
        );
        $distributor_columns_parent = array('d.id', 'distributor_name' => 'IFNULL(d.title, \'__Unclassified__\')');
        $distributor_columns_children = array('id' => 'd.parent', 'distributor_name' => 'IFNULL(d.title, \'__Unclassified__\')');
    }
    elseif (isset($params['export']) && $params['export']) {
        $select_colums = array(
            'id'                  => 'A.id',
            'distributor_name'    => 'A.distributor_name',
            'district_name'       => 'A.district_name',
            'district_id'         => 'A.district_id',
            'region_name'         => 'A.region_name',
            'region_id'           => 'A.region_id',
            'area_name'           => 'A.area_name',
            'area_id'             => 'A.area_id',
            'total_store'         => 'SUM(A.num_store)',
            'total_store_sellout' => 'SUM(A.total_store_sellout)',
            'total_quantity'      => 'SUM(A.product_count)',
            'add',
        );
        $distributor_columns_parent = array('d.id', 'distributor_name' => 'IFNULL(d.title, \'__Unclassified__\')', 'd.add' );
        $distributor_columns_children = array('id' => 'd.parent', 'distributor_name' => 'IFNULL(d.title, \'__Unclassified__\')', 'd.add' );
    }
    else {
        $select_colums = array(
            new Zend_Db_Expr('SQL_CALC_FOUND_ROWS A.id'),
            'distributor_name'    => 'A.distributor_name',
            'district_name'       => 'A.district_name',
            'district_id'         => 'A.district_id',
            'region_name'         => 'A.region_name',
            'region_id'           => 'A.region_id',
            'area_name'           => 'A.area_name',
            'area_id'             => 'A.area_id',
            'total_store'         => 'SUM(A.num_store)',
            'total_store_sellout' => 'SUM(A.total_store_sellout)',
            'total_quantity'      => 'SUM(A.product_count)',
            'add',
        );
        $distributor_columns_parent = array('d.id', 'distributor_name' => 'IFNULL(d.title, \'__Unclassified__\')','add');
        $distributor_columns_children = array('id' => 'd.parent', 'distributor_name' => 'IFNULL(d.title, \'__Unclassified__\')','add');
    }
    $select_parent_dealer = $db->select()
    ->from(array('d' => WAREHOUSE_DB.'.'.'distributor'), $distributor_columns_parent)
    ->joinLeft(array('s' => 'store'), 'd.id=s.d_id', array('num_store' => new Zend_Db_Expr('('.$select_count_store.')')))
    ->joinLeft(array('t' => 'timing'), 't.store=s.id', array('total_store_sellout' => 'COUNT(DISTINCT t.store)'))
    ->joinLeft(array('ts' => 'timing_sale'), 'ts.timing_id = t.id AND ts.imei NOT IN (select imei from timing_control) AND t.approved_at IS NOT NULL AND t.approved_at <> 0 AND t.approved_at <> \'\'', array('product_count' => 'COUNT(DISTINCT ts.imei)'))
    ->joinLeft(array('di' => 'regional_market'), 'd.district=di.id', array('district_name' => 'di.name', 'district_id' => 'di.id'))
    ->joinLeft(array('r' => 'regional_market'), 'di.parent=r.id', array('region_name' => 'r.name', 'region_id' => 'r.id'))
    ->joinLeft(array('a' => 'area'), 'r.area_id=a.id', array('area_name' => 'a.name', 'area_id' => 'a.id'))
    ->where('d.parent = 0')
    ->group('d.id');
    if (isset($params['from']) && $params['from'])
        $select_parent_dealer->where('t.from >= ?', $from[2].'-'.$from[1].'-'.$from[0] . ' 00:00:00');
    if (isset($params['to']) && $params['to'])
        $select_parent_dealer->where('t.from <= ?', $to[2].'-'.$to[1].'-'.$to[0] . ' 23:59:59');
    if ( isset($params['asm']) && $params['asm'] ) {
        $QAsm = new Application_Model_Asm();
        $list_regions = $QAsm->get_cache($params['asm']);
        $list_regions = isset($list_regions['district']) && is_array($list_regions['district']) ? $list_regions['district'] : array();
        if (count($list_regions) > 0)
            $select_parent_dealer->where('s.district IN (?)', $list_regions);
        else
            $select_parent_dealer->where('1=0', 1);
    }
    if (isset($params['sales_store']) && intval($params['sales_store']) > 0) {
        $select_parent_dealer
        ->joinRight(array('ssl' => 'store_staff_log'), 'ssl.store_id=t.store', array());
        $log_where = $this->getAdapter()->quoteInto('ssl.staff_id = ?', $params['sales_store']).
        " AND " . $this->getAdapter()->quoteInto('ssl.is_leader = ?', 1).
        " AND " . $this->getAdapter()->quoteInto('DATE(t.from) >= FROM_UNIXTIME(ssl.joined_at, \'%Y-%m-%d\')', 1).
        " AND (".
        $this->getAdapter()->quoteInto('DATE(t.from) < FROM_UNIXTIME(ssl.released_at, \'%Y-%m-%d\')', 1).
        " OR " . $this->getAdapter()->quoteInto('ssl.released_at IS NULL', 1).
        " OR " . $this->getAdapter()->quoteInto('ssl.released_at = 0', 1).
        " ) ";
        $select_parent_dealer->where($log_where);
    }
    if (isset($params['leader_province']) && intval($params['leader_province']) > 0) {
        $select_parent_dealer
        ->join(array('str' => 'store'), 't.store = str.id', array())
        ->joinLeft(array('lg' => 'store_leader_log'), 'str.id = lg.store_id', array())
        ->joinLeft(array('ssl' => 'store_staff_log'), 'str.id = ssl.store_id', array());
// quyền leader
        $log_where = " ( " . $this->getAdapter()->quoteInto('lg.staff_id = ?', $params['leader_province']).
        " AND " . $this->getAdapter()->quoteInto('DATE(t.from) >= FROM_UNIXTIME(lg.joined_at, \'%Y-%m-%d\')', 1).
        " AND (".
        $this->getAdapter()->quoteInto('DATE(t1.from) < FROM_UNIXTIME(lg.released_at, \'%Y-%m-%d\')', 1).
        " OR " . $this->getAdapter()->quoteInto('lg.released_at IS NULL', 1).
        " OR " . $this->getAdapter()->quoteInto('lg.released_at = 0', 1).
        " )
    ) ";
// quyền sales
    $log_where .= " OR " .
    " ( " . $this->getAdapter()->quoteInto('ssl.staff_id = ?', $params['leader_province']).
    " AND " . $this->getAdapter()->quoteInto('ssl.is_leader = ?', 1).
    " AND " . $this->getAdapter()->quoteInto('DATE(t.from) >= FROM_UNIXTIME(ssl.joined_at, \'%Y-%m-%d\')', 1).
    " AND (".
    $this->getAdapter()->quoteInto('DATE(t.from) < FROM_UNIXTIME(ssl.released_at, \'%Y-%m-%d\')', 1).
    " OR " . $this->getAdapter()->quoteInto('ssl.released_at IS NULL', 1).
    " OR " . $this->getAdapter()->quoteInto('ssl.released_at = 0', 1).
    " )
)";
$select_parent_dealer->where($log_where);
}
////////////////////////////////////////////////////////////////////////////////////////////////////////
$select_children = $db->select()
->from(array('d' => WAREHOUSE_DB.'.'.'distributor'), $distributor_columns_children)
->joinLeft(array('s' => 'store'), 'd.id=s.d_id', array('num_store' => new Zend_Db_Expr('('.$select_count_store.')')))
->joinLeft(array('t' => 'timing'), 't.store=s.id', array('total_store_sellout' => 'COUNT(DISTINCT t.store)'))
->joinLeft(array('ts' => 'timing_sale'), 'ts.timing_id = t.id AND ts.imei NOT IN (select imei from timing_control) AND t.approved_at IS NOT NULL AND t.approved_at <> 0 AND t.approved_at <> \'\'', array('product_count' => 'COUNT(DISTINCT ts.imei)'))
->joinLeft(array('di' => 'regional_market'), 'd.district=di.id', array('district_name' => 'di.name', 'district_id' => 'di.id'))
->joinLeft(array('r' => 'regional_market'), 'di.parent=r.id', array('region_name' => 'r.name', 'region_id' => 'r.id'))
->joinLeft(array('a' => 'area'), 'r.area_id=a.id', array('area_name' => 'a.name', 'area_id' => 'a.id'))
->where('d.parent <> 0')
->group('d.parent');
if (isset($params['from']) && $params['from'])
    $select_children->where('t.from >= ?', $from[2].'-'.$from[1].'-'.$from[0] . ' 00:00:00');
if (isset($params['to']) && $params['to'])
    $select_children->where('t.from <= ?', $to[2].'-'.$to[1].'-'.$to[0] . ' 23:59:59');
if ( isset($params['asm']) && $params['asm'] ) {
    $QAsm = new Application_Model_Asm();
    $list_regions = $QAsm->get_cache($params['asm']);
    $list_regions = isset($list_regions['district']) && is_array($list_regions['district']) ? $list_regions['district'] : array();
    if (count($list_regions) > 0)
        $select_children->where('s.district IN (?)', $list_regions);
    else
        $select_children->where('1=0', 1);
}
if (isset($params['sales_store']) && intval($params['sales_store']) > 0) {
    $select_children
    ->joinRight(array('ssl' => 'store_staff_log'), 'ssl.store_id=t.store', array());
    $log_where = $this->getAdapter()->quoteInto('ssl.staff_id = ?', $params['sales_store']).
    " AND " . $this->getAdapter()->quoteInto('ssl.is_leader = ?', 1).
    " AND " . $this->getAdapter()->quoteInto('DATE(t.from) >= FROM_UNIXTIME(ssl.joined_at, \'%Y-%m-%d\')', 1).
    " AND (".
    $this->getAdapter()->quoteInto('DATE(t.from) < FROM_UNIXTIME(ssl.released_at, \'%Y-%m-%d\')', 1).
    " OR " . $this->getAdapter()->quoteInto('ssl.released_at IS NULL', 1).
    " OR " . $this->getAdapter()->quoteInto('ssl.released_at = 0', 1).
    " ) ";
    $select_children->where($log_where);
}
if (isset($params['leader_province']) && intval($params['leader_province']) > 0) {
    $select_children
    ->join(array('str' => 'store'), 't.store = str.id', array())
    ->joinLeft(array('lg' => 'store_leader_log'), 'str.id = lg.store_id', array())
    ->joinLeft(array('ssl' => 'store_staff_log'), 'str.id = ssl.store_id', array());
// quyền leader
    $log_where = " ( " . $this->getAdapter()->quoteInto('lg.staff_id = ?', $params['leader_province']).
    " AND " . $this->getAdapter()->quoteInto('DATE(t.from) >= FROM_UNIXTIME(lg.joined_at, \'%Y-%m-%d\')', 1).
    " AND (".
    $this->getAdapter()->quoteInto('DATE(t.from) < FROM_UNIXTIME(lg.released_at, \'%Y-%m-%d\')', 1).
    " OR " . $this->getAdapter()->quoteInto('lg.released_at IS NULL', 1).
    " OR " . $this->getAdapter()->quoteInto('lg.released_at = 0', 1).
    " )
) ";
// quyền sales
$log_where .= " OR " .
" ( " . $this->getAdapter()->quoteInto('ssl.staff_id = ?', $params['leader_province']).
" AND " . $this->getAdapter()->quoteInto('ssl.is_leader = ?', 1).
" AND " . $this->getAdapter()->quoteInto('DATE(t.from) >= FROM_UNIXTIME(ssl.joined_at, \'%Y-%m-%d\')', 1).
" AND (".
$this->getAdapter()->quoteInto('DATE(t.from) < FROM_UNIXTIME(ssl.released_at, \'%Y-%m-%d\')', 1).
" OR " . $this->getAdapter()->quoteInto('ssl.released_at IS NULL', 1).
" OR " . $this->getAdapter()->quoteInto('ssl.released_at = 0', 1).
" )
)";
$select_children->where($log_where);
}
////////////////////////////////////////////////////////////////////////////////////////////////////////
$sub_select = $db->select()
->union(array($select_parent_dealer, $select_children));
$select = $db->select()
->from(array('A' => $sub_select), $select_colums)
->group('A.id')
->order('total_quantity DESC');
if ( isset($params['name']) && $params['name'] )
    $select->having('distributor_name LIKE ?', '%'.$params['name'].'%');
if ( isset($params['area']) && $params['area'] ) {
    if (is_array($params['area']))
        $select->where( 'area_id IN (?)', $params['area']);
    else
        $select->where( 'area_id = ?', intval($params['area']));
}
if ( isset($params['district']) && $params['district'] ) {
    $select->where( 'district_id = ?', intval($params['district']));
}
if ( isset($params['province']) && $params['province'] ) {
    $select->where( 'region_id = ?', intval($params['province']));
}
if ( isset($params['distributor']) && $params['distributor'] ) {
    $select->where( 'id = ?', intval($params['distributor']));
}
if ( isset($params['sales_from']) and $params['sales_from'] != '' )
    $select->having('total_quantity >= '.intval($params['sales_from']));
if ( isset($params['sales_to']) and $params['sales_to'] != '' )
    $select->having('total_quantity <= '.intval($params['sales_to']));
////////////////////////////////////////////////////////////////////////////////////////////////////////
if ( !isset($params['get_total_sales']) || !$params['get_total_sales'] ) {
    $order_str = ' FIELD(`distributor_name`, "__Unclassified__") DESC,';
    if (isset( $params['sort'] ) && $params['sort']) {
        $collate = ' ';
        $desc = (isset($params['desc']) && $params['desc'] == 1) ? ' DESC ' : ' ASC ';
        if ( in_array( $params['sort'], array( 'distributor_name', 'area_name', 'region_name' ) ) )
            $collate = ' COLLATE utf8_unicode_ci ';
        $order_str .= $params['sort'] . ' ' . $collate . $desc;
    }
    $select->order(new Zend_Db_Expr($order_str));
} else {
    $select_p = $db->select()
    ->from(array('B' => $select),
        array(new Zend_Db_Expr('SUM( B.total_quantity )')));
    return $db->fetchOne($select_p);
}
if (!$params['export'] and $limit)
    $select->limitPage($page, $limit);
$analytics = $db->fetchAll($select);
if (!$params['export'] && (!isset($params['get_total_sales']) || !$params['get_total_sales']))
    $total = $db->fetchOne("select FOUND_ROWS()");
return $analytics;
}


function report_by_dealer_all($page, $limit, &$total, $params)
{
    $db = Zend_Registry::get('db');
    $from = explode('/', $params['from']);
    $to = explode('/', $params['to']);
    $select_count_store = $db->select()
    ->from(array('ss' => 'store'), array('total_store' => 'COUNT(DISTINCT ss.id)'))
    ->where('ss.d_id = d.id');
    if ( (isset($params['get_total_sales']) and $params['get_total_sales']) || (isset($params['export']) && $params['export']))
        $distributor_columns_parent = array('d.id', 'add', 'distributor_name' => 'IFNULL(d.title, \'__Unclassified__\')');
    else
        $distributor_columns_parent = array(new Zend_Db_Expr('SQL_CALC_FOUND_ROWS d.id'), 'add', 'distributor_name' => 'IFNULL(d.title, \'__Unclassified__\')');
    $select = $db->select()
    ->from(array('d' => WAREHOUSE_DB.'.'.'distributor'), $distributor_columns_parent)
    ->joinLeft(array('s' => 'store'), 'd.id=s.d_id', array('total_store' => new Zend_Db_Expr('('.$select_count_store.')')))
    ->joinLeft(array('t' => 'timing'), 't.store=s.id', array('total_store_sellout' => 'COUNT(DISTINCT t.store)'))
    ->joinLeft(array('ts' => 'timing_sale'), 'ts.timing_id = t.id AND t.approved_at IS NOT NULL AND t.approved_at <> 0 AND t.approved_at <> \'\'', array('total_quantity' => 'COUNT(DISTINCT ts.imei)'))
    ->joinLeft(array('i'=>WAREHOUSE_DB.'.imei'),'ts.imei = i.imei_sn',array(
        'sellout_activated'=>'COUNT(DISTINCT CASE WHEN( i.activated_date IS NOT NULL AND i.activated_date <> 0 AND i.activated_date <> "") THEN i.imei_sn ELSE NULL END)'
    ))
    ->joinLeft(array('di' => 'regional_market'), 'd.district=di.id', array('district_name' => 'di.name', 'district_id' => 'di.id'))
    ->joinLeft(array('r' => 'regional_market'), 'di.parent=r.id', array('region_name' => 'r.name', 'region_id' => 'r.id'))
    ->joinLeft(array('a' => 'area'), 'r.area_id=a.id', array('area_name' => 'a.name', 'area_id' => 'a.id'))
    ->group('d.id');
    if (isset($params['from']) && $params['from'])
        $select->where('t.from >= ?', $from[2].'-'.$from[1].'-'.$from[0] . ' 00:00:00');
    if (isset($params['to']) && $params['to'])
        $select->where('t.from <= ?', $to[2].'-'.$to[1].'-'.$to[0] . ' 23:59:59');
    if ( isset($params['asm']) && $params['asm'] ) {
        $QAsm = new Application_Model_Asm();
        $list_regions = $QAsm->get_cache($params['asm']);
        $list_regions = isset($list_regions['district']) && is_array($list_regions['district']) ? $list_regions['district'] : array();
        if (count($list_regions) > 0)
            $select->where('s.district IN (?)', $list_regions);
        else
            $select->where('1=0', 1);
    }
    if (isset($params['sales_store']) && intval($params['sales_store']) > 0) {
        $select
        ->joinRight(array('ssl' => 'store_staff_log'), 'ssl.store_id=t.store', array());
        $log_where = $this->getAdapter()->quoteInto('ssl.staff_id = ?', $params['sales_store']).
        " AND " . $this->getAdapter()->quoteInto('ssl.is_leader = ?', 1).
        " AND " . $this->getAdapter()->quoteInto('DATE(t.from) >= FROM_UNIXTIME(ssl.joined_at, \'%Y-%m-%d\')', 1).
        " AND (".
        $this->getAdapter()->quoteInto('DATE(t.from) < FROM_UNIXTIME(ssl.released_at, \'%Y-%m-%d\')', 1).
        " OR " . $this->getAdapter()->quoteInto('ssl.released_at IS NULL', 1).
        " OR " . $this->getAdapter()->quoteInto('ssl.released_at = 0', 1).
        " ) ";
        $select->where($log_where);
    }
    if (isset($params['leader_province']) && intval($params['leader_province']) > 0) {
        $select
        ->join(array('str' => 'store'), 't.store = str.id', array())
        ->joinLeft(array('lg' => 'store_leader_log'), 'str.id = lg.store_id', array())
        ->joinLeft(array('ssl' => 'store_staff_log'), 'str.id = ssl.store_id', array());
// quyền leader
        $log_where = " ( " . $this->getAdapter()->quoteInto('lg.staff_id = ?', $params['leader_province']).
        " AND " . $this->getAdapter()->quoteInto('DATE(t.from) >= FROM_UNIXTIME(lg.joined_at, \'%Y-%m-%d\')', 1).
        " AND (".
        $this->getAdapter()->quoteInto('DATE(t1.from) < FROM_UNIXTIME(lg.released_at, \'%Y-%m-%d\')', 1).
        " OR " . $this->getAdapter()->quoteInto('lg.released_at IS NULL', 1).
        " OR " . $this->getAdapter()->quoteInto('lg.released_at = 0', 1).
        " )
    ) ";
// quyền sales
    $log_where .= " OR " .
    " ( " . $this->getAdapter()->quoteInto('ssl.staff_id = ?', $params['leader_province']).
    " AND " . $this->getAdapter()->quoteInto('ssl.is_leader = ?', 1).
    " AND " . $this->getAdapter()->quoteInto('DATE(t.from) >= FROM_UNIXTIME(ssl.joined_at, \'%Y-%m-%d\')', 1).
    " AND (".
    $this->getAdapter()->quoteInto('DATE(t.from) < FROM_UNIXTIME(ssl.released_at, \'%Y-%m-%d\')', 1).
    " OR " . $this->getAdapter()->quoteInto('ssl.released_at IS NULL', 1).
    " OR " . $this->getAdapter()->quoteInto('ssl.released_at = 0', 1).
    " )
)";
$select->where($log_where);
}
if ( isset($params['name']) && $params['name'] )
    $select->where('IFNULL(d.title, \'__Unclassified__\') LIKE ?', '%'.$params['name'].'%');
if ( isset($params['area']) && $params['area'] ) {
    if (is_array($params['area']))
        $select->where( 'a.id IN (?)', $params['area']);
    else
        $select->where( 'a.id = ?', intval($params['area']));
}
if ( isset($params['district']) && $params['district'] ) {
    $select->where( 'di.id = ?', intval($params['district']));
}
if ( isset($params['province']) && $params['province'] ) {
    $select->where( 'r.id = ?', intval($params['province']));
}
if ( isset($params['distributor']) && $params['distributor'] ) {
    $select->where( 'id = ?', intval($params['distributor']));
}
if ( isset($params['sales_from']) and $params['sales_from'] != '' )
    $select->having('total_quantity >= '.intval($params['sales_from']));
if ( isset($params['sales_to']) and $params['sales_to'] != '' )
    $select->having('total_quantity <= '.intval($params['sales_to']));
////////////////////////////////////////////////////////////////////////////////////////////////////////
if ( !isset($params['get_total_sales']) || !$params['get_total_sales'] ) {
    $order_str = ' FIELD(`distributor_name`, "__Unclassified__") DESC,';
    if (isset( $params['sort'] ) && $params['sort']) {
        $collate = ' ';
        $desc = (isset($params['desc']) && $params['desc'] == 1) ? ' DESC ' : ' ASC ';
        if ( in_array( $params['sort'], array( 'distributor_name', 'area_name', 'region_name' ) ) )
            $collate = ' COLLATE utf8_unicode_ci ';
        $order_str .= $params['sort'] . ' ' . $collate . $desc;
    }
    $select->order(new Zend_Db_Expr($order_str));
} else {
    $select_p = $db->select()
    ->from(array('B' => $select),
        array(new Zend_Db_Expr('SUM( B.total_quantity )')));
    return $db->fetchOne($select_p);
}
if (!$params['export'] and $limit)
    $select->limitPage($page, $limit);
$analytics = $db->fetchAll($select);
if (!$params['export'] && (!isset($params['get_total_sales']) || !$params['get_total_sales']))
    $total = $db->fetchOne("select FOUND_ROWS()");
return $analytics;
}



function report_by_product($page, $limit, &$total, $params)
{
    set_time_limit(0);
    ini_set('memory_limit', '-1');
    error_reporting(~E_ALL);
    ini_set("display_error", '0');

    $db = Zend_Registry::get('db');
    if ( isset($params['get_total_sales']) and $params['get_total_sales'] )
        $count_expr = array();
    else
        $count_expr = array(new Zend_Db_Expr('SQL_CALC_FOUND_ROWS t.id'));
    $select = $db->select()
    ->from(array('t' => 'timing'), $count_expr)
    ->join(array('ts' => 'timing_sale')             , 't.id=ts.timing_id AND t.approved_at <> 0 AND t.approved_at IS NOT NULL'  , array('total' => 'COUNT(DISTINCT ts.imei)'))
    ->joinLeft(array('g' => WAREHOUSE_DB.'.'.'good')    , 'g.id=ts.product_id'      , array('product_id' => 'g.id', 'product_name' => 'g.name', 'product_desc' => 'desc'))
    ->joinLeft(array('gc' => WAREHOUSE_DB.'.'.'good_color'), 'gc.id=ts.model_id'    , array('color_name' => 'gc.name'))
    ->joinLeft(array('s' => 'store')                    , 's.id=t.store'            , array('store_name' => 's.name','store_code' => 's.store_code',  'district' => 'rg.name','st_id' => 's.id','st_oppo_id' =>'s.oppo_id'))
    ->joinLeft(array('o' => 'org')                      , 's.org_dealer=o.org_id'   , array())
    ->joinLeft(array('r' => 'regional_market')          , 'r.id=s.regional_market'  , array('province'  => 'r.name'))
    ->joinLeft(array('a' => 'area')                     , 'a.id=r.area_id'          , array('area_id' => 'a.id', 'area_name' => 'a.name'))
    ->joinLeft(array('rg' => 'regional_market'),  'rg.parent=r.id', array())
//->joinLeft(array('st' => 'staff')                   , 'st.id=t.staff_id'        , array('reporter' => "CONCAT(CONCAT(st.firstname,' '), st.lastname)" ));

    ->joinLeft(array('i' => WAREHOUSE_DB.'.imei'),'ts.imei = i.imei_sn',array())
    ->joinLeft(array('d' => WAREHOUSE_DB.'.distributor'),'i.distributor_id = d.id',array('d_name' => 'd.title','d_code' => 'd.distributor_code','d_id' => 'd.id','d_warehouse' => 'd.warehouse_id'))
    ->joinLeft(array('rm' => HR_DB.'.regional_market'),'d.region = rm.id',array())
    ->joinLeft(array('a2' => HR_DB.'.area'),'rm.area_id = a2.id',array())
    ->joinLeft(array('b' => WAREHOUSE_DB.'.brand'),'b.id = g.brand_id',array('brand_name' => 'b.name'))

    ->where('ts.imei not in (select imei from hr.timing_control)')
    ->where('a.id NOT IN (49,72)');

    $from = explode('/', $params['from']);
    $to = explode('/', $params['to']);
    if (isset($params['from']) && $params['from'])
        $select->where('t.created_at >= ?', $from[2].'-'.$from[1].'-'.$from[0] . ' 00:00:00');
    if (isset($params['to']) && $params['to'])
        $select->where('t.created_at <= ?', $to[2].'-'.$to[1].'-'.$to[0] . ' 23:59:59');
/*
if (isset($params['sales_from']) && $params['sales_from'])
$select->having('total >= ?', $params['sales_from']);
if (isset($params['sales_to']) && $params['sales_to'])
$select->having('total <= ?', $params['sales_to']);
*/

if (isset($params['name'])) {
    if (is_array($params['name']) && count($params['name']) > 0) {
        $select->where('g.id IN (?)', $params['name']);
    } else {
        $select->where('g.id = ?', $params['name']);
    }
}

if(isset($params['rgm_area']) && $params['rgm_area']) {
    $select->where('a2.id IN ('.$params['rgm_area'].')');
}

if(isset($params['d_tag']) && $params['d_tag']) {
    $select->where('d.d_tag =?',$params['d_tag']);
}

if (isset($params['brand']) && $params['brand']) {
    if (is_array($params['brand']) && count($params['brand']))
        $select->where('g.brand_id IN (?)', $params['brand']);
    elseif (is_numeric($params['brand']))
        $select->where('g.brand_id = ?', intval($params['brand']));
    else
        $select->where('1=0', 1);
}



if (isset($params['area_id'])) {
    if (is_array($params['area_id']) && count($params['area_id']) > 0) {
        $select->where('a.id IN (?)', $params['area_id']);
    }
}

if (isset($params['asm']) && $params['asm']) {
    $QAsm = new Application_Model_Asm();
    $list_regions = $QAsm->get_cache($params['asm']);
    $list_regions = isset($list_regions['province']) && is_array($list_regions['province']) ? $list_regions['province'] : array();
    if (count($list_regions) > 0)
        $select->where('s.province_id IN (?)', $list_regions);
    else
        $select->where('1=0', 1);
}

if ( isset($params['am']) && $params['am'] ) {
    $QAm = new Application_Model_Am();
    $list_org = $QAm->get_cache($params['am']);
    $list_org = isset($list_org['store_type']) && is_array($list_org['store_type']) ? $list_org['store_type'] : array();
    if (count($list_org) > 0)
        $select->where( 's.org_dealer IN (?)', $list_org);
    else
        $select->where('1=0', 1);
}

// check Permission Admin Brandshop
if ( isset($params['admin_bs']) && $params['admin_bs'] ) {
    $select->where('(s.org_dealer = ?', 18);
    $select->orWhere('o.store_type_id = ?)', 3);
}

if (isset($params['sales_store']) && intval($params['sales_store']) > 0) {
    $select
    ->joinRight(array('ssl' => 'store_staff_log'), 'ssl.store_id=t.store', array());
    $log_where = $this->getAdapter()->quoteInto('ssl.staff_id = ?', $params['sales_store']).
    " AND " . $this->getAdapter()->quoteInto('ssl.is_leader = ?', 1).
    " AND " . $this->getAdapter()->quoteInto('t.created_at >= FROM_UNIXTIME(ssl.joined_at)', 1).
    " AND (".
    $this->getAdapter()->quoteInto('t.created_at < FROM_UNIXTIME(ssl.released_at)', 1).
    " OR " . $this->getAdapter()->quoteInto('ssl.released_at IS NULL', 1).
    " OR " . $this->getAdapter()->quoteInto('ssl.released_at = 0', 1).
    " ) ";
    $select->where($log_where);
}

if (isset($params['leader_id']) && intval($params['leader_id']) > 0) {
    $select
    ->joinRight(array('ssl' => 'store_leader_log'), 'ssl.store_id=t.store', array());
    $log_where = $this->getAdapter()->quoteInto('ssl.staff_id = ?', $params['leader_id']).
    " AND " . $this->getAdapter()->quoteInto('DATE(t.from) >= FROM_UNIXTIME(ssl.joined_at, \'%Y-%m-%d\')', 1).
    " AND (".
    $this->getAdapter()->quoteInto('DATE(t.from) < FROM_UNIXTIME(ssl.released_at, \'%Y-%m-%d\')', 1).
    " OR " . $this->getAdapter()->quoteInto('ssl.released_at IS NULL', 1).
    " OR " . $this->getAdapter()->quoteInto('ssl.released_at = 0', 1).
    " ) ";
    $select->where($log_where);
}

if (isset($params['bm_id']) && intval($params['bm_id']) > 0) {
    $select
    ->joinRight(array('ssl' => 'store_staff_log'), 'ssl.store_id=t.store', array());
    $log_where = $this->getAdapter()->quoteInto('ssl.staff_id = ?', $params['bm_id']).
    " AND " . $this->getAdapter()->quoteInto('ssl.is_leader = ?', 3).
    " AND " . $this->getAdapter()->quoteInto('t.created_at >= FROM_UNIXTIME(ssl.joined_at)', 1).
    " AND (".
    $this->getAdapter()->quoteInto('t.created_at < FROM_UNIXTIME(ssl.released_at)', 1).
    " OR " . $this->getAdapter()->quoteInto('ssl.released_at IS NULL', 1).
    " OR " . $this->getAdapter()->quoteInto('ssl.released_at = 0', 1).
    " ) ";
    $select->where($log_where);
}
/*
if (isset($params['leader_province']) && intval($params['leader_province']) > 0) {
$select
->joinRight(array('lg' => 'leader_log'), 's.regional_market = lg.regional_market', array());
$log_where = $this->getAdapter()->quoteInto('lg.staff_id = ?', $params['leader_province']).
" AND " . $this->getAdapter()->quoteInto('DATE(t.from) >= FROM_UNIXTIME(lg.from_date, \'%Y-%m-%d\')', 1).
" AND (".
$this->getAdapter()->quoteInto('DATE(t.from) < FROM_UNIXTIME(lg.to_date, \'%Y-%m-%d\')', 1).
" OR " . $this->getAdapter()->quoteInto('lg.to_date IS NULL', 1).
" OR " . $this->getAdapter()->quoteInto('lg.to_date = 0', 1).
" ) ";
$select->where($log_where);
}
*/
if (isset($params['regional_market'])) {
    if (is_array($params['regional_market']) && count($params['regional_market']) > 0) {
        $select->where('r.id IN (?)', $params['regional_market']);
    }
}
if (isset($params['district'])) {
    if (is_array($params['district']) && count($params['district']) > 0) {
        $select->where('s.district IN (?)', $params['district']);
    }
}
if (isset($params['store'])) {
    if (is_array($params['store']) && count($params['store']) > 0) {
        $select->where('s.id IN (?)', $params['store']);
    }
}
/*
if (isset($params['sort']) && $params['sort']) {
$collate = ' ';
$desc = (isset($params['desc']) && $params['desc'] == 1) ? ' DESC ' : ' ASC ';
$order_str = '';
switch ($params['sort']) {
case 'area_name':
$params['sort'] = 'a.name';
break;
case 'province_name':
$params['sort'] = 'r.name';
break;
case 'product_name':
$params['sort'] = 'g.name';
break;
default:
break;
}
if ( in_array( $params['sort'], array( 'area_name', 'product_name', 'province_name' ) ) )
$collate = ' COLLATE utf8_unicode_ci ';
$order_str = $params['sort'] . ' ' . $collate . $desc;
$select->order(new Zend_Db_Expr($order_str));
}
*/
if (isset($params['export']) && $params['export'] == 1) {
    $select->joinLeft(array('st' => 'staff'), 'st.id = t.staff_id', array(
        'reporter_name'  => "CONCAT(CONCAT(st.firstname,' '), st.lastname)",
        'reporter_code'=> 'st.code'));
    $select->joinLeft(array('gr' => 'group'), 'st.group_id = gr.id', array('group_name'=> 'gr.name'));
    $select->group(array('t.staff_id', 'g.id', 'gc.id','t.store'));
    $select->order(array('a.name ASC', 'r.name ASC', 'reporter_name ASC', 'total DESC'));
    
} else if (isset($params['export']) && $params['export'] == 2) {
    $select->group(array('g.id','t.store'));

} else {
    $select->group(array('a.id', 'r.id','g.id'));
    $select->order('total DESC');
}

if ($limit && !$params['export'])
    $select->limitPage($page, $limit);
if ( isset($params['get_total_sales']) and $params['get_total_sales'] ){
    // $select_p->joinLeft(array('ds' => 'regional_market'), 'ds.id=s.district', array('district=>'ds.name'))
    $select_p = $db->select()
    ->from(array('pa' => $select),
        array(new Zend_Db_Expr('SUM( pa.total )')));
    return $db->fetchOne($select_p);
} else {
//echo $select;
    $result = $db->fetchAll($select);
}
if (!$params['export'])
    $total = $db->fetchOne("select FOUND_ROWS()");
return $result;
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
function count_product_by_dealer($dealer_id, $params){
    $db = Zend_Registry::get('db');
    $select = $db->select()
    ->from(array('t' => 'timing'),array()
);
    $select->joinLeft(array('ts' => 'timing_sale'),
        'ts.timing_id = t.id',
        array('product_count' => 'COUNT(ts.product_id)', 'ts.product_id'))
    ->join(array('s'=>'store'), 's.id=t.store', array());
    $select->where('t.approved_at IS NOT NULL AND t.approved_at <> 0 AND t.approved_at <> \'\'', 1);
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
    if ($dealer_id == 'null') {
        $select->joinLeft(array('d' => WAREHOUSE_DB.'.'.'distributor'), 'd.id=s.d_id', array());
        $select->where( 's.d_id IS NULL OR s.d_id = 0', 1);
    } else {
        $select->where( 's.d_id = ?', $dealer_id);
    }
    try {
        return $db->fetchAll($select);
    } catch(Exception $ex) {
    }
}

function short_report_by_store($params) {

    $from = date_create_from_format("d/m/Y", $params['from'])->format("Y-m-d");
    $to = date_create_from_format("d/m/Y", $params['to'])->format("Y-m-d");
    $db = Zend_Registry::get('db');

    $get = array(
        'imei'  => 'ts.imei',
        'model' => 'g.desc',
        'color' => 'gc.name',
        'timing_date' => 't.created_at',
        'brand_name' => 'b.name'
    );
    $select = $db->select()
    ->from(array('t'  => 'timing'), $get)
    ->join(array('ts' => 'timing_sale')         , 't.id = ts.timing_id'         , array())
    ->joinLeft(array('st' => 'store')               , 't.store = st.id'             , array())
    ->joinLeft(array('rm' => 'regional_market')     , 'st.regional_market = rm.id'  , array())
    ->joinLeft(array('rm2'=> 'regional_market')     , 'st.district = rm2.id'        , array())
    ->joinLeft(array('a'  => 'area')                , 'rm.area_id = a.id'           , array())
    ->joinLeft(array('g'  => WAREHOUSE_DB.'.good')  , 'ts.product_id = g.id'        , array())
    ->joinLeft(array('gc' => WAREHOUSE_DB.'.good_color'), 'ts.model_id = gc.id'     , array())
    ->joinLeft(array('b' => WAREHOUSE_DB.'.brand'),'b.id = g.brand_id',array())

    ->where('st.id = ?', $params['store_id'])
    ->order(array('t.created_at DESC', 'g.desc ASC', 'gc.name ASC'));

    if (isset($params['from']) && $params['from'])
        $select->where('t.created_at >= ?', $from.' 00:00:00');
    if (isset($params['to']) && $params['to'])
        $select->where('t.created_at <= ?', $to.' 23:59:59');

    // echo $select; die;
    $sales = $db->fetchAll($select);
    return $sales;
}

function report_by_area($params)
{
// SELECT
//     a.id,
//     a.`name`,
//     COUNT(DISTINCT ts.imei) AS total_quantity,
//     SUM(ts.price)* 0.8 AS total_value
// FROM
//     timing t
// INNER JOIN timing_sale ts ON t.id = ts.timing_id
// AND t.approved_at IS NOT NULL
// AND t.approved_at <> 0
// AND t.approved_at <> ''
// AND t.`from` >= '2015-02-01 00:00:00'
// AND t.`from` <= '2015-02-07 23:59:59'
// INNER JOIN store s ON t.store = s.id
// INNER JOIN regional_market r ON s.regional_market = r.id
// INNER JOIN area a ON r.area_id = a.id
// GROUP BY
//     a.id
    $from = explode('/', $params['from']);
    $to = explode('/', $params['to']);
    $db = Zend_Registry::get('db');
    $select = $db->select()
    ->from(array('t' => 'timing'), array())
    ->join(array('ts' => 'timing_sale'), 't.id=ts.timing_id
        AND t.approved_at IS NOT NULL
        AND t.approved_at <> 0
        AND t.approved_at <> \'\'',
        array(
            'total_quantity' => 'COUNT(DISTINCT ts.imei)',
            'total_activated' => 'COUNT( DISTINCT CASE WHEN (i.activated_date IS NOT NULL AND i.activated_date <> 0 AND i.activated_date <> "") THEN i.imei_sn ELSE NULL END )',
            'total_value' => 'SUM( ts.price )*0.8',
            'total_value_activated' => 'SUM( CASE WHEN i.activated_date IS NOT NULL AND i.activated_date <> 0 AND i.activated_date <> "" THEN ts.price ELSE 0 END )*0.8',
        ))
    ->join(array('s' => 'store'), 't.store=s.id', array())
    ->join(array('d' => 'regional_market'), 's.district=d.id', array())
    ->join(array('r' => 'regional_market'), 'd.parent=r.id', array())
    ->join(array('a' => 'area'), 'r.area_id=a.id', array('a.id', 'a.name', 'a.region_share'))
    ->join(array('i' => WAREHOUSE_DB.'.imei'),'i.imei_sn = ts.imei',array())
    ->group('a.id');
    if (isset($params['from']) && $params['from'])
        $select->where('t.from >= ?', $from[2].'-'.$from[1].'-'.$from[0] . ' 00:00:00');
    if (isset($params['to']) && $params['to'])
        $select->where('t.from <= ?', $to[2].'-'.$to[1].'-'.$to[0] . ' 23:59:59');
    if (isset($params['export']) and $params['export'])
        return $select->__toString();
    if ( isset($params['get_total_sales']) and $params['get_total_sales'] ){
        $select_total = $db->select()
        ->from(array('A' => $select), array(
            'total_quantity'        => 'SUM(A.total_quantity)',
            'total_activated'       => 'SUM(A.total_activated)',
            'total_value'           => 'SUM(A.total_value)',
            'total_value_activated' => 'SUM(A.total_value_activated)',
        )
    );
        return $db->fetchRow($select_total);
    }
    $analytics = $db->fetchAll($select);
    return $analytics;
}
function getDay($params)
{
    $db = Zend_Registry::get('db');
    $staff_id = $params['user_id'];
    $month = $params['month'];
// $condition  = $params['condition'];
    if (isset($staff_id) and isset($month)) {
        $select = $db->select()->from(array('p' => $this->_name), array('p.*'));
        $select->where('staff_id = ? ', $staff_id);
        $select->where('MONTH(created_at) = ?', $month);
        $result = $db->fetchAll($select);
        $day_approve = $day_not_approve = 0;
        foreach ($result as $k => $v)
        {
            if($v['status'] == 0)
            {
                $day_not_approve++;
            }
            else
            {
                $day_approve++;
            }
        }
        $result = array(
            'day_approve' => $day_approve,
            'day_not_approve' => $day_not_approve,
            'total' => count($result),
        );
        return $result;
    } else
    return - 1;
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
public function checkImeiDealer($imei, $store){
    $QImei = new Application_Model_WebImei();
    $QStore = new Application_Model_Store();
    $QDistributor = new Application_Model_Distributor();
    $QGood = new Application_Model_Good();
    $QBypassImei = new Application_Model_BypassImei();
    $QLockedImei = new Application_Model_LockedImei();
    $imei_lock = $QLockedImei->getLockImei($imei);
    $userStorage = Zend_Auth::getInstance()->getStorage()->read();

// Query 01 : Get Bypass IMEI
    $where_bypass = $QBypassImei->getAdapter()->quoteInto('imei = ?', $imei);
    $bypass_check = $QBypassImei->fetchRow($where_bypass);
// Check 01 : Check Bypass IMEI - If Bypass IMEI Then Pass This Function
    if ( $bypass_check['imei'] == $imei ) return 0;


// Query 02 : Get Imei Data from Imei table
    $where_imei = $QImei->getAdapter()->quoteInto('imei_sn = ?', $imei);
    $imei_check = $QImei->fetchRow($where_imei);

//Check 03-2 : Check imei Seven-eleven (ຖ້າເປັນຂອງເຊເວັນແມ່ນໃຫ້ລັອກການລາຍງານຍອດໃນລະບົບ)
    if ($imei_check ['true_seven'] == 1 ) return 8;

//Check  imei by store (ຖ້າເປັນຮ້ານທີ່ຢູ່ໃນນີ້ແມ່ນຈະລັອກການລາຍງານຍອດໃນລະບົບ)
    if ($imei_check ['distributor_id'] == 63376 ) return 17;

//Check  imei by store (ຖ້າເປັນຮ້ານທີ່ຢູ່ໃນນີ້ແມ່ນຈະລັອກການລາຍງານຍອດໃນລະບົບ)
    if ($imei_check ['distributor_id'] == 62994 ) return 17;

//Check  imei by store (ຖ້າເປັນຮ້ານທີ່ຢູ່ໃນນີ້ແມ່ນຈະລັອກການລາຍງານຍອດໃນລະບົບ)
    if ($imei_check ['distributor_id'] == 63289 ) return 17;

if($userStorage->id != 18){ // case for Normal User
    if ($imei_check ['distributor_id'] == 3032 ) return 50;
}

if ($imei_lock ['status_imei'] == 1 ) return 17;

// Check 02-1 : Check Old Imei Case - If Old Imei Then Pass This Function
if ($imei_check['old_data'] == 1 ) return 51;

// Imei Not Exit
if(!$imei_check) return ;

// Check 02-2 : Allow Dummy Warehouse to timing
if ($imei_check['warehouse_id'] == 193 ) return 51; // 193 = warehouse_id is Previous IMEI

if ($imei_check ['distributor_id'] == '' ) return 14;
if ( $store == 50200 ) return 0; // by pass pc oppo shop 

// Check 02-3 : Check Imei that exists in Imei table
//if (!$imei_check || !isset($imei_check['distributor_id'])) return 1;

// Check 02-3 : new
if (!$imei_check || !isset($imei_check['distributor_id'])) return 13;

// Check 02-4 : Check Only Normal IMEI
// if ($imei_check['type'] != 1 ) return 7;

// Query 03 : Get Store Data from CATTY Store
$where_store = $QStore->getAdapter()->quoteInto('id = ?', intval($store));
$store_check = $QStore->fetchRow($where_store);
// Check 03-1 : Check Store that exists in Store table
if (!$store_check || !isset($store_check['d_id'])) return 2;
// Query 04 : Get Good Data from Good table
$where_good = array();
$where_good[] = $QGood->getAdapter()->quoteInto('id = ?', $imei_check['good_id']);
$good_check = $QGood->fetchRow($where_good);
// Check 04-1 : Only IMEI from OPPO can Timing

// if ($good_check['brand_id'] <> 1) { return 11; }

// Query 05 : Get Distributor Data via Imei table
$where_dis1 = $QDistributor->getAdapter()->quoteInto('id = ?', $imei_check['distributor_id']);
$distributor_imei = $QDistributor->fetchRow($where_dis1);
// Check 05-1 : Check Distributor that exists in Distributor table via Imei table
if (!$distributor_imei) return 3;
// Query 06 : Get Distributor Data via Store table
$where_dis2 = $QDistributor->getAdapter()->quoteInto('id = ?', intval($store_check['d_id']));
$distributor_check = $QDistributor->fetchRow($where_dis2);
// Check 06-1 : Check Distributor that exists in Distributor table via Store table
if (!$distributor_check) return 5;

// Check 06-2 : Check parent of Distributor in Warehouse
if ( ! (
    in_array( $distributor_imei['id'], array( $distributor_check['id'], $distributor_check['parent'] ) )
    || (in_array( $distributor_imei['parent'], array( $distributor_check['id'], $distributor_check['parent'] ) )
        && $distributor_imei['parent'] <> 0)
)
)
    return 6;

    return 0;
}
/**
* @param  [type] $page   [description]
* @param  [type] $limit  [description]
* @param  [type] &$total [description]
* @param  [type] $params [description]
* @return [type]         [description]
*/
function report_inventory_by_dealer($page, $limit, &$total, $params)
{
    $db = Zend_Registry::get('db');
    $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini');
    $config = $config->toArray();
    $con=mysqli_connect($config['resources']['db_tunnel']['params']['host'],$config['resources']['db_tunnel']['params']['username'],$config['resources']['db_tunnel']['params']['password'],$config['resources']['db_tunnel']['params']['dbname'], $config['resources']['db_tunnel']['params']['port']);
// Check connection
    if (mysqli_connect_errno())
    {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }
    /* change character set to utf8 */
    if (!$con->set_charset("utf8")) {
        printf("Error loading character set utf8: %s\n", $con->error);
    }
    $centerDbName = $config['resources']['db']['params']['dbname'];
    $biDbName = $config['resources']['db_tunnel']['params']['dbname'];
    $whDbName = $config['resources']['db_tunnel']['params']['dbnamewh'];
    $countFoundRows = true;
    if (isset($params['export']) and $params['export'])
        $countFoundRows = false;
    $where = ' WHERE 1=1
    AND tmp1.parent = 0
    AND tmp1.del = 0
    ';
    $SQL_CALC_FOUND_ROWS = $sLimit = '';
    if ($countFoundRows){
        $SQL_CALC_FOUND_ROWS = ' SQL_CALC_FOUND_ROWS ';
        if ($limit){
            $offset = $limit*($page-1);
            $sLimit .= ' LIMIT '.$offset.', '.$limit.' ';
        }
    }
    if (isset($params['asm']) && intval($params['asm']) > 0) {
        $QAsm = new Application_Model_Asm();
        $list_regions = $QAsm->get_cache($params['asm']);
        $list_regions = isset($list_regions['district']) && is_array($list_regions['district']) ? $list_regions['district'] : array();
        if (count($list_regions))
            $where .= ' AND '.$db->quoteInto('tmp1.district IN (?)', $list_regions);
        else
            $where .= ' AND 1=0 ';
    }
    if (isset($params['name']) and $params['name']){
        $where .= ' AND '.$db->quoteInto('tmp1.title LIKE ?', '%'.$params['name'].'%');
    }
    if (isset($params['area']) and $params['area']){
        $QRegionalMarket = new Application_Model_RegionalMarket();
        $districtsByArea = $QRegionalMarket->get_district_by_area_cache($params['area']);
        if ($districtsByArea){
            $arrDistricts = array();
            foreach ($districtsByArea as $id=>$val)
                $arrDistricts[] = $val;
            $where .= ' AND '.$db->quoteInto('tmp1.district IN (?)', $arrDistricts);
        } else
        $where .= ' AND 1=0 ';
    }
    if (isset($params['regional_market']) and $params['regional_market']){
        $QRegionalMarket = new Application_Model_RegionalMarket();
        $districtsByProvince = $QRegionalMarket->get_district_by_province_cache($params['regional_market']);
        if ($districtsByProvince){
            $arrDistricts = array();
            foreach ($districtsByProvince as $id=>$val)
                $arrDistricts[] = $id;
            $where .= ' AND '.$db->quoteInto('tmp1.district IN (?)', $arrDistricts);
        } else
        $where .= ' AND 1=0 ';
    }
    if (isset($params['district']) and $params['district']){
        $where .= ' AND '.$db->quoteInto('tmp1.district = ?', $params['district']);
    }
    $sql = '
    select '.$SQL_CALC_FOUND_ROWS.' tmp1.id,
    tmp1.title, tmp1.parent, tmp1.product_id, tmp1.color,
    SUM((IFNULL(tmp1.sum_total_activated,0) + IFNULL(tmp2.sum_total_activated,0))) as sum_sum1 ,
    SUM((IFNULL(tmp1.sum_total_sell_in,0) + IFNULL(tmp2.sum_total_sell_in,0))) as sum_sum2
    from
    (
    SELECT
    d.id, d.district, d.title, d.parent, d.del, dsi.product_id, dsi.color, SUM(dsi.total_activated) as sum_total_activated, SUM(dsi.total_sell_in) as sum_total_sell_in
    FROM
    '.$whDbName.'.distributor d
    LEFT JOIN
    '.$biDbName.'.dealer_sell_in dsi
    ON
    dsi.dealer_id = d.id
    WHERE
    d.del = 0
    GROUP BY
    d.id
    ) as tmp1
    left join (
    SELECT
    d.id, d.title, d.parent, d.del, dsi.product_id, dsi.color, SUM(dsi.total_activated) as sum_total_activated, SUM(dsi.total_sell_in) as sum_total_sell_in
    FROM
    '.$whDbName.'.distributor d
    LEFT JOIN
    '.$biDbName.'.dealer_sell_in dsi
    ON
    dsi.dealer_id = d.id
    WHERE
    d.del = 0
    GROUP BY
    d.id
    ) tmp2
    on tmp2.parent = tmp1.id
    '.$where.'
    group by tmp1.id
    '.$sLimit.'
    ';
    if (isset($params['export']) and $params['export'])
        return $sql;
    $resultSql = mysqli_query($con,$sql);
    $result = array();
    while($row = mysqli_fetch_array($resultSql))
        $result[] = $row;
    if ($countFoundRows){
        $totalSql = mysqli_query($con,'select FOUND_ROWS()');
        $totalResult = mysqli_fetch_row($totalSql);
        $total = isset($totalResult[0]) ? $totalResult[0] : 0;
    }
    return $result;
}
function report_inventory_by_dealer_detail($page, $limit, &$total, $params)
{
    $db = Zend_Registry::get('db');
    $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini');
    $config = $config->toArray();
    $con=mysqli_connect($config['resources']['db_tunnel']['params']['host'],$config['resources']['db_tunnel']['params']['username'],$config['resources']['db_tunnel']['params']['password'],$config['resources']['db_tunnel']['params']['dbname'], $config['resources']['db_tunnel']['params']['port']);
// Check connection
    if (mysqli_connect_errno())
    {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }
    /* change character set to utf8 */
    if (!$con->set_charset("utf8")) {
        printf("Error loading character set utf8: %s\n", $con->error);
    }
    $biDbName = $config['resources']['db_tunnel']['params']['dbname'];
    $whDbName = $config['resources']['db_tunnel']['params']['dbnamewh'];
    $countFoundRows = true;
    if (isset($params['export']) and $params['export'])
        $countFoundRows = false;
    $where = ' WHERE 1=1
    AND d.del = 0
    ';
    $SQL_CALC_FOUND_ROWS = $sLimit = '';
    if ($countFoundRows){
        $SQL_CALC_FOUND_ROWS = ' SQL_CALC_FOUND_ROWS ';
        if ($limit){
            $offset = $limit*($page-1);
            $sLimit .= ' LIMIT '.$offset.', '.$limit.' ';
        }
    }
    if (isset($params['asm']) && intval($params['asm']) > 0) {
        $QAsm = new Application_Model_Asm();
        $list_regions = $QAsm->get_cache($params['asm']);
        $list_regions = isset($list_regions['district']) && is_array($list_regions['district']) ? $list_regions['district'] : array();
        if (count($list_regions))
            $where .= ' AND '.$db->quoteInto('d.district IN (?)', $list_regions);
        else
            $where .= ' AND 1=0 ';
    }
    if (isset($params['name']) and $params['name']){
        $where .= ' AND '.$db->quoteInto('d.title LIKE ?', '%'.$params['name'].'%');
    }
    if (isset($params['area']) and $params['area']){
        $QRegionalMarket = new Application_Model_RegionalMarket();
        $districtsByArea = $QRegionalMarket->get_district_by_area_cache($params['area']);
        if ($districtsByArea){
            $arrDistricts = array();
            foreach ($districtsByArea as $id=>$val)
                $arrDistricts[] = $val;
            $where .= ' AND '.$db->quoteInto('d.district IN (?)', $arrDistricts);
        } else
        $where .= ' AND 1=0 ';
    }
    if (isset($params['regional_market']) and $params['regional_market']){
        $QRegionalMarket = new Application_Model_RegionalMarket();
        $districtsByProvince = $QRegionalMarket->get_district_by_province_cache($params['regional_market']);
        if ($districtsByProvince){
            $arrDistricts = array();
            foreach ($districtsByProvince as $id=>$val)
                $arrDistricts[] = $id;
            $where .= ' AND '.$db->quoteInto('d.district IN (?)', $arrDistricts);
        } else
        $where .= ' AND 1=0 ';
    }
    if (isset($params['district']) and $params['district']){
        $where .= ' AND '.$db->quoteInto('d.district = ?', $params['district']);
    }
    if (isset($params['product_id']) and $params['product_id']){
        $where .= ' AND '.$db->quoteInto('dsi.product_id = ?', $params['product_id']);
    }
    if (isset($params['color']) and $params['color']){
        $where .= ' AND '.$db->quoteInto('dsi.color = ?', $params['color']);
    }
    if (isset($params['dealer_id']) and $params['dealer_id']){
        $where .= ' AND ( '.$db->quoteInto('d.id = ?', $params['dealer_id']) . ' OR ' . $db->quoteInto('d.parent = ?', $params['dealer_id']) . ' ) ';
    }
    $sql = '
    SELECT
    '.$SQL_CALC_FOUND_ROWS.' d.id, d.district, d.title, d.parent, d.del, dsi.product_id, dsi.color, SUM(dsi.total_activated) as sum_total_activated, SUM(dsi.total_sell_in) as sum_total_sell_in
    FROM
    '.$whDbName.'.distributor d
    LEFT JOIN
    '.$biDbName.'.dealer_sell_in dsi
    ON
    dsi.dealer_id = d.id
    '.$where.'
    GROUP BY
    d.id, product_id, color
    '.$sLimit.'
    ';
    if (isset($params['export']) and $params['export'])
        return $sql;
    $resultSql = mysqli_query($con,$sql);
    $result = array();
    while($row = mysqli_fetch_array($resultSql))
        $result[] = $row;
    if ($countFoundRows){
        $totalSql = mysqli_query($con,'select FOUND_ROWS()');
        $totalResult = mysqli_fetch_row($totalSql);
        $total = isset($totalResult[0]) ? $totalResult[0] : 0;
    }
    return $result;
}
/**
* @param  [type] $page   [description]
* @param  [type] $limit  [description]
* @param  [type] &$total [description]
* @param  [type] $params [description]
* @return [type]         [description]
*/
function report_loyalty_plan($page, $limit, &$total, $params)
{
    $db = Zend_Registry::get('db');
    $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini');
    $config = $config->toArray();
    $dbTunnel = $config['resources']['db_tunnel']['params'];
    $con=mysqli_connect($dbTunnel['host'],$dbTunnel['username'],$dbTunnel['password'],$dbTunnel['dbname'], $dbTunnel['port']);
// Check connection
    if (mysqli_connect_errno())
    {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }
    /* change character set to utf8 */
    if (!$con->set_charset("utf8")) {
        printf("Error loading character set utf8: %s\n", $con->error);
    }
    $biDbName = $dbTunnel['dbname'];
    $centerDbName = $dbTunnel['dbnamecenter'];
    $whDbName = $dbTunnel['dbnamewh'];
    $countFoundRows = true;
    if (isset($params['export']) and $params['export'])
        $countFoundRows = false;
    $where = ' WHERE 1=1
    ';
    $SQL_CALC_FOUND_ROWS = $sLimit = '';
    if ($countFoundRows){
        $SQL_CALC_FOUND_ROWS = ' SQL_CALC_FOUND_ROWS ';
        if ($limit){
            $offset = $limit*($page-1);
            $sLimit .= ' LIMIT '.$offset.', '.$limit.' ';
        }
    }
    if (isset($params['asm']) && intval($params['asm']) > 0) {
        $QAsm = new Application_Model_Asm();
        $list_regions = $QAsm->get_cache($params['asm']);
        $list_regions = isset($list_regions['district']) && is_array($list_regions['district']) ? $list_regions['district'] : array();
        if (count($list_regions))
            $where .= ' AND '.$db->quoteInto('d.district IN (?)', $list_regions);
        else
            $where .= ' AND 1=0 ';
    }
    if (isset($params['name']) and $params['name'])
        $where .= ' AND '.$db->quoteInto('d.`title` LIKE ?', '%'.$params['name'].'%');
    if (isset($params['loyalty_plan_id']) and $params['loyalty_plan_id'])
        $where .= ' AND '.$db->quoteInto('dl.`loyalty_plan_id` IN (?)', $params['loyalty_plan_id']);
    if (isset($params['from_date']) and $params['from_date']){
        list($day, $month, $year) = explode('/', $params['from_date']);
        $from_date = $year.'-'.$month.'-'.$day;
        $where .= ' AND '.$db->quoteInto('dlpr.`date` >= ?', $from_date);
    }
    if (isset($params['to_date']) and $params['to_date']){
        list($day, $month, $year) = explode('/', $params['to_date']);
        $to_date = $year.'-'.$month.'-'.$day;
        $where .= ' AND '.$db->quoteInto('dlpr.`date` <= ?', $to_date);
    }
    if (isset($params['area']) and $params['area']){
        $QRegionalMarket = new Application_Model_RegionalMarket();
        $districtsByArea = $QRegionalMarket->get_district_by_area_cache($params['area']);
        if ($districtsByArea){
            $arrDistricts = array();
            foreach ($districtsByArea as $id=>$val)
                $arrDistricts[] = $val;
            $where .= ' AND '.$db->quoteInto('d.`district` IN (?)', $arrDistricts);
        } else
        $where .= ' AND 1=0 ';
    }
    if (isset($params['regional_market']) and $params['regional_market']){
        $QRegionalMarket = new Application_Model_RegionalMarket();
        $districtsByProvince = $QRegionalMarket->get_district_by_province_cache($params['regional_market']);
        if ($districtsByProvince){
            $arrDistricts = array();
            foreach ($districtsByProvince as $id=>$val)
                $arrDistricts[] = $id;
            $where .= ' AND '.$db->quoteInto('d.`district` IN (?)', $arrDistricts);
        } else
        $where .= ' AND 1=0 ';
    }
    if (isset($params['district']) and $params['district']){
        $where .= ' AND '.$db->quoteInto('d.`district` = ?', $params['district']);
    }
    $sql = '
    SELECT
    '.$SQL_CALC_FOUND_ROWS.' d.`id`, d.`district`, d.`title`, d.`parent`, d.`del`,
    dl.`dealer_id`, dl.`loyalty_plan_id`
    , SUM(dlpr.`result_sell_in`) as total_result_sell_in
    , SUM(dlpr.`result_sell_out`) as total_result_sell_out
    FROM
    `'.$whDbName.'`.`distributor` d
    LEFT JOIN
    `'.$centerDbName.'`.`dealer_loyalty` dl
    ON
    dl.`dealer_id` = d.`id`
    LEFT JOIN
    `'.$biDbName.'`.`dealer_loyalty_plan_result` dlpr
    ON
    dl.`dealer_id` = dlpr.`dealer_id`
    '.$where.'
    AND d.`del` = 0
    GROUP BY
    d.`id`
    '.$sLimit.'
    ';
    if (isset($params['export']) and $params['export'])
        return $sql;
    $resultSql = mysqli_query($con,$sql);
    $result = array();
    while($row = mysqli_fetch_array($resultSql))
        $result[] = $row;
    if ($countFoundRows){
        $totalSql = mysqli_query($con,'select FOUND_ROWS()');
        $totalResult = mysqli_fetch_row($totalSql);
        $total = isset($totalResult[0]) ? $totalResult[0] : 0;
    }
    return $result;
}
function getListRule($params){
    $db = Zend_Registry::get('db');
    $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini');
    $config = $config->toArray();
    $dbTunnel = $config['resources']['db_tunnel']['params'];
    $con=mysqli_connect($dbTunnel['host'],$dbTunnel['username'],$dbTunnel['password'],$dbTunnel['dbname'], $dbTunnel['port']);
// Check connection
    if (mysqli_connect_errno())
    {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }
    /* change character set to utf8 */
    if (!$con->set_charset("utf8")) {
        printf("Error loading character set utf8: %s\n", $con->error);
    }
    $biDbName = $dbTunnel['dbname'];
    $centerDbName = $dbTunnel['dbnamecenter'];
    $whDbName = $dbTunnel['dbnamewh'];
    $where = ' WHERE 1=1
    ';
    if (isset($params['from_date']) and $params['from_date'])
        $where .= ' AND '.$db->quoteInto('lpr.from_date <= ?', $params['from_date']);
    if (isset($params['to_date']) and $params['to_date'])
        $where .= ' AND '.$db->quoteInto('lpr.to_date >= ? OR lpr.to_date IS NULL', $params['to_date']);
    $sql = '
    SELECT
    lp.*,
    lpr.loyalty_plan_id, lpr.from_date, lpr.to_date, lpr.type,
    lpr.product_id, lpr.color, lpr.value
    FROM
    '.$centerDbName.'.loyalty_plan lp
    LEFT JOIN
    '.$centerDbName.'.loyalty_plan_rule lpr
    ON
    lp.id = lpr.loyalty_plan_id
    '. $where .'
    ORDER BY
    lpr.type
    ';
    $resultSql = mysqli_query($con,$sql);
    $result = array();
    while($row = mysqli_fetch_array($resultSql)){
        $result[] = $row;
    }
    return $result;
}
function getResultByProductAndPlan($params, $con){
    $db = Zend_Registry::get('db');
    $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini');
    $config = $config->toArray();
    $dbTunnel = $config['resources']['db_tunnel']['params'];
    $biDbName = $dbTunnel['dbname'];
    $centerDbName = $dbTunnel['dbnamecenter'];
    $whDbName = $dbTunnel['dbnamewh'];
    $where = ' WHERE 1=1
    ';
    if (isset($params['from_date']) and $params['from_date'])
        $where .= ' AND '.$db->quoteInto('dlpr.date >= ?', $params['from_date']);
    if (isset($params['to_date']) and $params['to_date'])
        $where .= ' AND '.$db->quoteInto('dlpr.date <= ?', $params['to_date']);
    if (isset($params['dealer_id']) and $params['dealer_id'])
        $where .= ' AND '.$db->quoteInto('dlpr.dealer_id = ?', $params['dealer_id']);
    if (isset($params['product_id']) and $params['product_id'])
        $where .= ' AND '.$db->quoteInto('dlpr.product_id = ?', $params['product_id']);
    if (isset($params['color']) and $params['color'])
        $where .= ' AND '.$db->quoteInto('dlpr.color = ?', $params['color']);
    $sql = '
    SELECT
    SUM(dlpr.`sell_in`) AS total_sell_in, SUM(dlpr.`result_sell_in`) AS total_result_sell_in, SUM(dlpr.`sell_out`) AS total_sell_out, SUM(dlpr.`result_sell_out`) AS total_result_sell_out
    FROM
    `'.$biDbName.'`.`dealer_loyalty_plan_result` dlpr
    '. $where .'
    ';
    $resultSql = mysqli_query($con,$sql);
    $result = array();
    while($row = mysqli_fetch_array($resultSql)){
        $result = $row;
    }
    return $result;
}


// Set PC Commision \\
// Set PC Commision \\
// Set PC Commision \\
public function getCurrentKPI($params) {
    $db = Zend_Registry::get('db');
    $select = $db->select()
    ->from(array('gkl' => 'good_kpi_log'), array('gkl.*'))
    ->join(array('g'  => WAREHOUSE_DB.'.good')      , 'gkl.good_id = g.id'  , array('good_name'  => 'g.name'))
    ->join(array('gc' => WAREHOUSE_DB.'.good_color'), 'gkl.color_id = gc.id', array('color_name' => 'gc.name'))
    ->join(array('ts' => 'timing_sale'),
        "   gkl.good_id = ts.product_id

        AND gkl.color_id = ts.model_id
        " ,
        array())
    ->join(array('t'  => 'timing'),
        "   ts.timing_id = t.id
        AND t.created_at >= CONCAT(gkl.from_date,' 00:00:00')
        AND t.created_at <= CONCAT(gkl.to_date,' 23:59:59')
        ",
        array())
    ->join(array('i'  => WAREHOUSE_DB.'.imei'), "ts.imei = i.imei_sn", array(
//'price' => new Zend_Db_Expr( "SUM( gkl.price )" ),
        'price' => new Zend_Db_Expr(
            "   SUM(
            CASE WHEN
            DATE(i.activated_date) <= DATE_FORMAT(DATE(t.created_at) + INTERVAL 7 DAY, '%Y-%m-%d')
            AND DATE(i.activated_date) >= DATE_FORMAT(DATE(t.created_at), '%Y-%m-%d')
            THEN gkl.price END
            )
            " ),
        'total_sellout' => new Zend_Db_Expr( "COUNT( ts.imei )" ),
        'activated' => new Zend_Db_Expr( "COUNT( CASE WHEN i.activated_date IS NOT NULL THEN i.imei_sn END )" ),
        'not_activated' => new Zend_Db_Expr( "COUNT( CASE WHEN i.activated_date IS NULL THEN i.imei_sn END )" ),
        'com_activated' => new Zend_Db_Expr(
            "   COUNT(
            CASE WHEN
            DATE(i.activated_date) <= DATE_FORMAT(DATE(t.created_at) + INTERVAL 30 DAY, '%Y-%m-%d')
            AND DATE(i.activated_date) >= DATE_FORMAT(DATE(t.created_at) - INTERVAL 30 DAY, '%Y-%m-%d')
            THEN i.imei_sn END
            )
            " ),
        'com_a83_sim_lock' => new Zend_Db_Expr(
            "   COUNT(
            CASE WHEN
            (SELECT ps.imei_sn FROM warehouse.packed_sim AS ps
            WHERE ps.imei_sn = i.imei_sn AND g.id = 323 AND ps.sim_activated_at IS NOT NULL) IS NOT NULL
            AND DATE(i.activated_date) <= DATE_FORMAT(DATE(t.created_at) + INTERVAL 7 DAY, '%Y-%m-%d')
            AND DATE(i.activated_date) >= DATE_FORMAT(DATE(t.created_at), '%Y-%m-%d')
            THEN i.imei_sn END
            )
            " ),
        'com_a3s_sim_lock' => new Zend_Db_Expr(
            "   COUNT(
            CASE WHEN
            (SELECT ps.imei_sn FROM warehouse.packed_sim AS ps
            WHERE ps.imei_sn = i.imei_sn AND g.id = 338 AND ps.sim_activated_at IS NOT NULL) IS NOT NULL
            AND DATE(i.activated_date) <= DATE_FORMAT(DATE(t.created_at) + INTERVAL 7 DAY, '%Y-%m-%d')
            AND DATE(i.activated_date) >= DATE_FORMAT(DATE(t.created_at), '%Y-%m-%d')
            THEN i.imei_sn END
            )
            " ),
    ))
    ->join(array('s' => 'staff'), 't.staff_id = s.id', array('staff_code' => 's.code'))
    ->joinLeft(array('b' => WAREHOUSE_DB.'.brand'),'b.id = g.brand_id',array('brand_name' => 'b.name'));

    $select->where('t.staff_id = ?', $params['staff_id']);
    $select->where('t.created_at >= ?', $params['from'].' 00:00:00');
    $select->where('t.created_at <= ?', $params['to'].' 23:59:59');
    $select->group(array('gkl.id'));
    $select->order('g.name ASC');
    //echo $select;
    $result = $db->fetchAll($select);
    //echo "<pre>"; print_r($result); echo "</pre>"; //die;
    return $result;
}
public function report_by_dealer_oppoclub_by_store($params) {
    set_time_limit(0);
    ini_set('memory_limit', '-1');
    error_reporting(~E_ALL);
    ini_set("display_error", '0');
    $db = Zend_Registry::get('db');
// Prepare Data
    $tmp_from = explode('/', $params['from']);
    $from = $tmp_from[2].'-'.$tmp_from[1].'-'.$tmp_from[0];
    $tmp_to = explode('/', $params['to']);
    $to = $tmp_to[2].'-'.$tmp_to[1].'-'.$tmp_to[0];
// Generate Main Query
    $get = array(
        'd_id'          =>  new Zend_Db_Expr('SQL_CALC_FOUND_ROWS d.id'),
        'd_name'        =>  'd.title',
        'd_rank'        =>  'd.rank',
        'st_id'         =>  'AAA.st_id',
        'st_name'       =>  'AAA.st_name',
        'st_rank'       =>  'AAA.st_rank',
        'st_type'       =>  'AAA.st_type',
        'area_name'     =>  'a.name',
        'province'      =>  'rm2.name',
        'district'      =>  'rm1.name',
        'd_area'        =>  'a2.name',
        'd_province'    =>  'rm3.name',
        'd_district'    =>  'rm4.name',
        'total_sellout' =>  'AAA.sellout',
        'total_active'  =>  'AAA.active',
        'cnt_active'    =>  'AAA.cnt_active',
    );
// Generate SubQuery for each Quater
    $sub_from = $tmp_from[2].'-'.$tmp_from[1];
    $sub_to = $tmp_to[2].'-'.$tmp_to[1];
    $sub_from_last = date('Y-m-t', strtotime($sub_from));
    $diff_month = date('m', ( strtotime($sub_to) - strtotime($sub_from) ) );
    for ($i=0;$i<=$diff_month;$i++) {

        $sub_select_quater[$i] = $db->select()
        ->from(array('t2' => 'timing'), new Zend_Db_Expr('COUNT(ts2.imei)') )
        ->join(array('ts2' => 'timing_sale'), 't2.id = ts2.timing_id', array())
/*
->join(array('i2' => WAREHOUSE_DB.'.imei'),
"   ts2.imei = i2.imei_sn
AND i2.activated_date <= DATE_FORMAT(LAST_DAY(t2.created_at) + INTERVAL 7 DAY, '%Y-%m-%d 23:59:59')
AND i2.activated_date >= DATE_FORMAT(DATE_ADD(DATE_ADD(LAST_DAY(t2.created_at),INTERVAL 1 DAY),INTERVAL -1 MONTH) - INTERVAL 7 DAY,'%Y-%m-%d 00:00:00')
"
, array())
*/
->join(array('i2' => WAREHOUSE_DB.'.imei'),
    "   ts2.imei = i2.imei_sn
    AND i2.activated_date <= DATE_FORMAT(DATE(t2.created_at) + INTERVAL 7 DAY, '%Y-%m-%d 23:59:59')
    AND i2.activated_date >= DATE_FORMAT(DATE(t2.created_at) - INTERVAL 3 DAY, '%Y-%m-%d 00:00:00')
    "
    , array())
->join(array('st2' => 'store'), 't2.store = st2.id', array())
->join(array('o' => 'org'), 'st2.org_dealer = o.org_id AND o.store_type_id = 2', array())
->where('i2.distributor_id = d.id')
->where('t2.store = AAA.st_id');
if ($i==0) {
    $sub_select_quater[$i]->where('ts2.time_add >= ?', $from." 00:00:00");
    if (strtotime($to) < strtotime($sub_from_last) ) { $sub_select_quater[$i]->where('ts2.time_add <= ?', $to." 23:59:59"); }
    else { $sub_select_quater[$i]->where('ts2.time_add <= ?', $sub_from_last." 23:59:59"); }
    $get[ "QCnt_".$tmp_from[1]."_".$tmp_from[2] ] = '('.$sub_select_quater[$i].')';

} else {
    $tmp_start = date("Y-m-01", strtotime(date("Y-m-01", strtotime($sub_from)) . "+".$i." month"));
    $tmp_end = date("Y-m-t", strtotime(date("Y-m-01", strtotime($sub_from)) . "+".$i." month"));
    $sub_select_quater[$i]->where('ts2.time_add >= ?', $tmp_start." 00:00:00");
    if (strtotime($to) < strtotime($tmp_end) ) { $sub_select_quater[$i]->where('ts2.time_add <= ?', $to." 23:59:59"); }
    else { $sub_select_quater[$i]->where('ts2.time_add <= ?', $tmp_end." 23:59:59"); }
// split sub quater
    $d = explode('-', $tmp_start);
    $get[ "QCnt_".$d[1]."_".$d[0] ] = '('.$sub_select_quater[$i].')';

}
}
// Get Sellout Activate Each Month for Export
// For Total Sellout
for ($i=0;$i<=$diff_month;$i++) {

    $sub_select_quater[$i] = $db->select()
    ->from(array('t2' => 'timing'), new Zend_Db_Expr('COUNT(ts2.imei)') )
    ->join(array('ts2' => 'timing_sale'), 't2.id = ts2.timing_id', array())
    ->join(array('i2' => WAREHOUSE_DB.'.imei'), 'ts2.imei = i2.imei_sn', array())
    ->join(array('st2' => 'store'), 't2.store = st2.id', array())
    ->join(array('o' => 'org'), 'st2.org_dealer = o.org_id AND o.store_type_id = 2', array())
    ->where('i2.distributor_id = d.id')
    ->where('t2.store = AAA.st_id');
    if ($i==0) {
        $sub_select_quater[$i]->where('ts2.time_add >= ?', $from." 00:00:00");
        if (strtotime($to) < strtotime($sub_from_last) ) { $sub_select_quater[$i]->where('ts2.time_add <= ?', $to." 23:59:59"); }
        else { $sub_select_quater[$i]->where('ts2.time_add <= ?', $sub_from_last." 23:59:59"); }
        $get[ "Q_".$tmp_from[1]."_".$tmp_from[2] ] = '('.$sub_select_quater[$i].')';

    } else {
        $tmp_start = date("Y-m-01", strtotime(date("Y-m-01", strtotime($sub_from)) . "+".$i." month"));
        $tmp_end = date("Y-m-t", strtotime(date("Y-m-01", strtotime($sub_from)) . "+".$i." month"));
        $sub_select_quater[$i]->where('ts2.time_add >= ?', $tmp_start." 00:00:00");
        if (strtotime($to) < strtotime($tmp_end) ) { $sub_select_quater[$i]->where('ts2.time_add <= ?', $to." 23:59:59"); }
        else { $sub_select_quater[$i]->where('ts2.time_add <= ?', $tmp_end." 23:59:59"); }
// split sub quater
        $d = explode('-', $tmp_start);
        $get[ "Q_".$d[1]."_".$d[0] ] = '('.$sub_select_quater[$i].')';
    }
}
// For Total Activate
for ($i=0;$i<=$diff_month;$i++) {

    $sub_select_quater[$i] = $db->select()
    ->from(array('t2' => 'timing'), new Zend_Db_Expr('COUNT(ts2.imei)') )
    ->join(array('ts2' => 'timing_sale'), 't2.id = ts2.timing_id', array())
    ->join(array('i2' => WAREHOUSE_DB.'.imei'), 'ts2.imei = i2.imei_sn AND i2.activated_date IS NOT NULL', array())
    ->join(array('st2' => 'store'), 't2.store = st2.id', array())
    ->join(array('o' => 'org'), 'st2.org_dealer = o.org_id AND o.store_type_id = 2', array())
    ->where('i2.distributor_id = d.id')
    ->where('t2.store = AAA.st_id');
    if ($i==0) {
        $sub_select_quater[$i]->where('ts2.time_add >= ?', $from." 00:00:00");
        if (strtotime($to) < strtotime($sub_from_last) ) { $sub_select_quater[$i]->where('ts2.time_add <= ?', $to." 23:59:59"); }
        else { $sub_select_quater[$i]->where('ts2.time_add <= ?', $sub_from_last." 23:59:59"); }
        $get[ "QA_".$tmp_from[1]."_".$tmp_from[2] ] = '('.$sub_select_quater[$i].')';

    } else {
        $tmp_start = date("Y-m-01", strtotime(date("Y-m-01", strtotime($sub_from)) . "+".$i." month"));
        $tmp_end = date("Y-m-t", strtotime(date("Y-m-01", strtotime($sub_from)) . "+".$i." month"));
        $sub_select_quater[$i]->where('ts2.time_add >= ?', $tmp_start." 00:00:00");
        if (strtotime($to) < strtotime($tmp_end) ) { $sub_select_quater[$i]->where('ts2.time_add <= ?', $to." 23:59:59"); }
        else { $sub_select_quater[$i]->where('ts2.time_add <= ?', $tmp_end." 23:59:59"); }
// split sub quater
        $d = explode('-', $tmp_start);
        $get[ "QA_".$d[1]."_".$d[0] ] = '('.$sub_select_quater[$i].')';
    }
}
$sub_select = $db->select()
->from(array('ts' => 'timing_sale'), array(
    'sellout'       => new Zend_Db_Expr('COUNT(i1.imei_sn)'),
    'active'        => new Zend_Db_Expr('COUNT(i2.imei_sn)'),
    'cnt_active'    => new Zend_Db_Expr('COUNT(i3.imei_sn)'),
    'st_id'         => 'st.id',
    'st_name'       => 'st.name',
    'st_rank'       => 'st.rank',
    'st_type'       => 'o.org_name',
    'st_region'     => 'st.regional_market',
    'st_district'   => 'st.district',
    'i1.distributor_id'))
->join(array('t'  => 'timing')  , 'ts.timing_id = t.id'                     , array())
->join(array('st' => 'store')   , 't.store = st.id'                         , array())
->join(array('o'  => 'org')     , 'st.org_dealer = o.org_id AND o.store_type_id = 2', array())
->join(array('i1' => WAREHOUSE_DB.'.imei'), 'i1.imei_sn = ts.imei'          , array())
->joinLeft(array('i2' => WAREHOUSE_DB.'.imei'), 'i2.imei_sn = ts.imei AND i2.activated_date IS NOT NULL', array())
/*
->joinLeft(array('i3' => WAREHOUSE_DB.'.imei'),
"   ts.imei = i3.imei_sn
AND i3.activated_date <= DATE_FORMAT(LAST_DAY(t.created_at) + INTERVAL 7 DAY, '%Y-%m-%d 23:59:59')
AND i3.activated_date >= DATE_FORMAT(DATE_ADD(DATE_ADD(LAST_DAY(t.created_at),INTERVAL 1 DAY),INTERVAL -1 MONTH) - INTERVAL 7 DAY,'%Y-%m-%d 00:00:00')
"
, array())
*/
->joinLeft(array('i3' => WAREHOUSE_DB.'.imei'),
    "   ts.imei = i3.imei_sn
    AND i3.activated_date <= DATE_FORMAT(DATE(t.created_at) + INTERVAL 7 DAY, '%Y-%m-%d 23:59:59')
    AND i3.activated_date >= DATE_FORMAT(DATE(t.created_at) - INTERVAL 3 DAY, '%Y-%m-%d 00:00:00')
    "
    , array())
->where('ts.time_add >= ?', $from." 00:00:00")
->where('ts.time_add <= ?', $to." 23:59:59")
->group(array('i1.distributor_id','t.store'));
$select = $db->select()
->from(array('d' => WAREHOUSE_DB.'.distributor'), $get)
->joinLeft(array('or1'   => 'oppoclub_reward')   ,
    "d.id = or1.d_id AND or1.quater_no = 'Quater_01' AND or1.quater_year = '2016' " , array())
->joinLeft(array('ol1'   => 'oppoclub_level')   , 'or1.level_id = ol1.id' , array('d1_level' => 'ol1.name'))
->joinLeft(array('or2'   => 'oppoclub_reward')  ,
    "d.id = or2.d_id AND or2.quater_no = 'Quater_02' AND or2.quater_year = '2016' " , array())
->joinLeft(array('ol2'   => 'oppoclub_level')   , 'or2.level_id = ol1.id' , array('d2_level' => 'ol2.name'))
->joinLeft(array('or3'   => 'oppoclub_reward')  ,
    "d.id = or3.d_id AND or3.quater_no = 'Quater_03' AND or3.quater_year = '2016' " , array())
->joinLeft(array('ol3'   => 'oppoclub_level')   , 'or3.level_id = ol3.id' , array('d3_level' => 'ol3.name'))
->joinLeft(array('or4'   => 'oppoclub_reward')  ,
    "d.id = or4.d_id AND or4.quater_no = 'Quater_04' AND or4.quater_year = '2017' " , array())
->joinLeft(array('ol4'   => 'oppoclub_level')   , 'or4.level_id = ol4.id' , array('d4_level' => 'ol4.name'))
->joinLeft(array('AAA' => new Zend_Db_Expr('('.$sub_select.')')), 'd.id = AAA.distributor_id', array())
->joinLeft(array('rm1'  => 'regional_market')   , 'AAA.st_region = rm1.id'      , array())
->joinLeft(array('rm2'  => 'regional_market')   , 'AAA.st_district = rm2.id'    , array())
->joinLeft(array('a'    => 'area')              , 'rm1.area_id = a.id'          , array())
->joinLeft(array('rm3'  => 'regional_market')   , 'd.region = rm3.id'           , array())
->joinLeft(array('rm4'  => 'regional_market')   , 'd.district = rm4.id'         , array())
->joinLeft(array('a2'    => 'area')             , 'rm3.area_id = a2.id'         , array())
->where('d.rank IN (7,8)')
->group(array('d.id','AAA.st_id'))
->order('AAA.sellout DESC');
// Filter Condition
if (isset($params['id']) && $params['id']) {
    $select->where('d.id = ?', $params['id']);
}
if (isset($params['name']) && $params['name']) {
    $select->where('d.title LIKE ?', "%".$params['name']."%");
}
if (isset($params['area_id'])) {
    if (is_array($params['area_id']) && count($params['area_id']) > 0) {
        $select->where('a.id IN (?)', $params['area_id']);
    }
}
if (isset($params['regional_market'])) {
    if (is_array($params['regional_market']) && count($params['regional_market']) > 0) {
        $select->where('d.region IN (?)', $params['regional_market']);
    }
}
if (isset($params['district'])) {
    if (is_array($params['district']) && count($params['district']) > 0) {
        $select->where('d.district IN (?)', $params['district']);
    }
}
if (isset($params['level_id']) && $params['level_id']) {
    $select->where('( or1.level_id = ?', $params['level_id']);
    $select->orWhere('or2.level_id = ?', $params['level_id']);
    $select->orWhere('or3.level_id = ?', $params['level_id']);
    $select->orWhere('or4.level_id = ? )', $params['level_id']);
}
// check ermission ASM, ASM Stand by, Sale Admin, Traning
if ( isset($params['asm']) && $params['asm'] ) {
    $QAsm = new Application_Model_Asm();
    $list_regions = $QAsm->get_cache($params['asm']);
    $list_regions = isset($list_regions['district']) && is_array($list_regions['district']) ? $list_regions['district'] : array();
    if (count($list_regions) > 0)
        $select->where( 'd.district IN (?)', $list_regions);
    else
        $select->where('1=0', 1);
}

if ($limit) {
    $select->limitPage($page, $limit);
}
//echo $select; die;
$result = $db->fetchAll($select);
$total = $db->fetchOne("select FOUND_ROWS()");
//print_r($result);die;
return $result;
}



public function report_by_dealer_oppoclub($page, $limit, &$total, $params) {
    set_time_limit(0);
    ini_set('memory_limit', '-1');
    error_reporting(~E_ALL);
    ini_set("display_error", '0');
    $db = Zend_Registry::get('db');
// Prepare Data
    $tmp_from = explode('/', $params['from']);
    $from = $tmp_from[2].'-'.$tmp_from[1].'-'.$tmp_from[0];
    $tmp_to = explode('/', $params['to']);
    $to = $tmp_to[2].'-'.$tmp_to[1].'-'.$tmp_to[0];
// Generate Main Query
    $sub_select_store_all = $db->select()
    ->from(array('st1' => 'store'), new Zend_Db_Expr('COUNT(st1.id)') )
    ->join(array('o' => 'org'), 'st1.org_dealer = o.org_id AND o.store_type_id = 2', array())
    ->where('st1.d_id = d.id');
    $get = array(
        'd_id'          =>  new Zend_Db_Expr('SQL_CALC_FOUND_ROWS d.id'),
        'd_name'        =>  'd.title',
        'd_rank'        =>  'd.rank',
        'area_name'     =>  'a.name',
        'province'      =>  'rm2.name',
        'district'      =>  'rm1.name',
        'total_sellout' =>  'AAA.sellout',
        'total_active'  =>  'AAA.active',
        'cnt_active'    =>  'AAA.cnt_active',
        'store_active'  =>  'AAA.cnt_store_active',
        'store_all'     =>  new Zend_Db_Expr('('.$sub_select_store_all.')')
    );
// Generate SubQuery for each Quater
    $sub_from = $tmp_from[2].'-'.$tmp_from[1];
    $sub_to = $tmp_to[2].'-'.$tmp_to[1];
    $sub_from_last = date('Y-m-t', strtotime($sub_from));
    $diff_month = date('m', ( strtotime($sub_to) - strtotime($sub_from) ) );
    for ($i=0;$i<=$diff_month;$i++) {

        $sub_select_quater[$i] = $db->select()
        ->from(array('t2' => 'timing'), new Zend_Db_Expr('COUNT(ts2.imei)') )
        ->join(array('ts2' => 'timing_sale'), 't2.id = ts2.timing_id', array())
/*
->join(array('i2' => WAREHOUSE_DB.'.imei'),
"   ts2.imei = i2.imei_sn
AND i2.activated_date <= DATE_FORMAT(LAST_DAY(t2.created_at) + INTERVAL 7 DAY, '%Y-%m-%d 23:59:59')
AND i2.activated_date >= DATE_FORMAT(DATE_ADD(DATE_ADD(LAST_DAY(t2.created_at),INTERVAL 1 DAY),INTERVAL -1 MONTH) - INTERVAL 7 DAY,'%Y-%m-%d 00:00:00')
"
, array())
*/
->join(array('i2' => WAREHOUSE_DB.'.imei'),
    "   ts2.imei = i2.imei_sn
    AND i2.activated_date <= DATE_FORMAT(DATE(t2.created_at) + INTERVAL 7 DAY, '%Y-%m-%d 23:59:59')
    AND i2.activated_date >= DATE_FORMAT(DATE(t2.created_at) - INTERVAL 3 DAY, '%Y-%m-%d 00:00:00')
    "
    , array())
->join(array('st2' => 'store'), 't2.store = st2.id', array())
->join(array('o' => 'org'), 'st2.org_dealer = o.org_id AND o.store_type_id = 2', array())
->where('i2.distributor_id = d.id');
if ($i==0) {
    $sub_select_quater[$i]->where('ts2.time_add >= ?', $from." 00:00:00");
    if (strtotime($to) < strtotime($sub_from_last) ) { $sub_select_quater[$i]->where('ts2.time_add <= ?', $to." 23:59:59"); }
    else { $sub_select_quater[$i]->where('ts2.time_add <= ?', $sub_from_last." 23:59:59"); }
    $get[ "QCnt_".$tmp_from[1]."_".$tmp_from[2] ] = '('.$sub_select_quater[$i].')';

} else {
    $tmp_start = date("Y-m-01", strtotime(date("Y-m-01", strtotime($sub_from)) . "+".$i." month"));
    $tmp_end = date("Y-m-t", strtotime(date("Y-m-01", strtotime($sub_from)) . "+".$i." month"));
    $sub_select_quater[$i]->where('ts2.time_add >= ?', $tmp_start." 00:00:00");
    if (strtotime($to) < strtotime($tmp_end) ) { $sub_select_quater[$i]->where('ts2.time_add <= ?', $to." 23:59:59"); }
    else { $sub_select_quater[$i]->where('ts2.time_add <= ?', $tmp_end." 23:59:59"); }
// split sub quater
    $d = explode('-', $tmp_start);
    $get[ "QCnt_".$d[1]."_".$d[0] ] = '('.$sub_select_quater[$i].')';

}
}
// Get Sellout Activate Each Month for Export
if ($params['export']) {
// For Total Sellout
    for ($i=0;$i<=$diff_month;$i++) {

        $sub_select_quater[$i] = $db->select()
        ->from(array('t2' => 'timing'), new Zend_Db_Expr('COUNT(ts2.imei)') )
        ->join(array('ts2' => 'timing_sale'), 't2.id = ts2.timing_id', array())
        ->join(array('i2' => WAREHOUSE_DB.'.imei'), 'ts2.imei = i2.imei_sn', array())
        ->join(array('st2' => 'store'), 't2.store = st2.id', array())
        ->join(array('o' => 'org'), 'st2.org_dealer = o.org_id AND o.store_type_id = 2', array())
        ->where('i2.distributor_id = d.id');
        if ($i==0) {
            $sub_select_quater[$i]->where('ts2.time_add >= ?', $from." 00:00:00");
            if (strtotime($to) < strtotime($sub_from_last) ) { $sub_select_quater[$i]->where('ts2.time_add <= ?', $to." 23:59:59"); }
            else { $sub_select_quater[$i]->where('ts2.time_add <= ?', $sub_from_last." 23:59:59"); }
            $get[ "Q_".$tmp_from[1]."_".$tmp_from[2] ] = '('.$sub_select_quater[$i].')';

        } else {
            $tmp_start = date("Y-m-01", strtotime(date("Y-m-01", strtotime($sub_from)) . "+".$i." month"));
            $tmp_end = date("Y-m-t", strtotime(date("Y-m-01", strtotime($sub_from)) . "+".$i." month"));
            $sub_select_quater[$i]->where('ts2.time_add >= ?', $tmp_start." 00:00:00");
            if (strtotime($to) < strtotime($tmp_end) ) { $sub_select_quater[$i]->where('ts2.time_add <= ?', $to." 23:59:59"); }
            else { $sub_select_quater[$i]->where('ts2.time_add <= ?', $tmp_end." 23:59:59"); }
// split sub quater
            $d = explode('-', $tmp_start);
            $get[ "Q_".$d[1]."_".$d[0] ] = '('.$sub_select_quater[$i].')';
        }
    }
// For Total Activate
    for ($i=0;$i<=$diff_month;$i++) {

        $sub_select_quater[$i] = $db->select()
        ->from(array('t2' => 'timing'), new Zend_Db_Expr('COUNT(ts2.imei)') )
        ->join(array('ts2' => 'timing_sale'), 't2.id = ts2.timing_id', array())
        ->join(array('i2' => WAREHOUSE_DB.'.imei'), 'ts2.imei = i2.imei_sn AND i2.activated_date IS NOT NULL', array())
        ->join(array('st2' => 'store'), 't2.store = st2.id', array())
        ->join(array('o' => 'org'), 'st2.org_dealer = o.org_id AND o.store_type_id = 2', array())
        ->where('i2.distributor_id = d.id');
        if ($i==0) {
            $sub_select_quater[$i]->where('ts2.time_add >= ?', $from." 00:00:00");
            if (strtotime($to) < strtotime($sub_from_last) ) { $sub_select_quater[$i]->where('ts2.time_add <= ?', $to." 23:59:59"); }
            else { $sub_select_quater[$i]->where('ts2.time_add <= ?', $sub_from_last." 23:59:59"); }
            $get[ "QA_".$tmp_from[1]."_".$tmp_from[2] ] = '('.$sub_select_quater[$i].')';

        } else {
            $tmp_start = date("Y-m-01", strtotime(date("Y-m-01", strtotime($sub_from)) . "+".$i." month"));
            $tmp_end = date("Y-m-t", strtotime(date("Y-m-01", strtotime($sub_from)) . "+".$i." month"));
            $sub_select_quater[$i]->where('ts2.time_add >= ?', $tmp_start." 00:00:00");
            if (strtotime($to) < strtotime($tmp_end) ) { $sub_select_quater[$i]->where('ts2.time_add <= ?', $to." 23:59:59"); }
            else { $sub_select_quater[$i]->where('ts2.time_add <= ?', $tmp_end." 23:59:59"); }
// split sub quater
            $d = explode('-', $tmp_start);
            $get[ "QA_".$d[1]."_".$d[0] ] = '('.$sub_select_quater[$i].')';
        }
    }
}
$sub_select = $db->select()
->from(array('ts' => 'timing_sale'), array(
    'sellout'   => new Zend_Db_Expr('COUNT(i1.imei_sn)'),
    'active'    => new Zend_Db_Expr('COUNT(i2.imei_sn)'),
    'cnt_active'=> new Zend_Db_Expr('COUNT(i3.imei_sn)'),
    'cnt_store_active' => new Zend_Db_Expr('COUNT(DISTINCT st.id)'),
    'i1.distributor_id'))
->join(array('t' => 'timing'), 'ts.timing_id = t.id', array())
->join(array('st' => 'store'), 't.store = st.id', array())
->join(array('o' => 'org'), 'st.org_dealer = o.org_id AND o.store_type_id = 2', array())
->join(array('i1' => WAREHOUSE_DB.'.imei'), 'i1.imei_sn = ts.imei', array())
->joinLeft(array('i2' => WAREHOUSE_DB.'.imei'), 'i2.imei_sn = ts.imei AND i2.activated_date IS NOT NULL', array())
/*
->joinLeft(array('i3' => WAREHOUSE_DB.'.imei'),
"   ts.imei = i3.imei_sn
AND i3.activated_date <= DATE_FORMAT(LAST_DAY(t.created_at) + INTERVAL 7 DAY, '%Y-%m-%d 23:59:59')
AND i3.activated_date >= DATE_FORMAT(DATE_ADD(DATE_ADD(LAST_DAY(t.created_at),INTERVAL 1 DAY),INTERVAL -1 MONTH) - INTERVAL 7 DAY,'%Y-%m-%d 00:00:00')
"
, array())
*/
->join(array('i3' => WAREHOUSE_DB.'.imei'),
    "   ts.imei = i3.imei_sn
    AND i3.activated_date <= DATE_FORMAT(DATE(t.created_at) + INTERVAL 7 DAY, '%Y-%m-%d 23:59:59')
    AND i3.activated_date >= DATE_FORMAT(DATE(t.created_at) - INTERVAL 3 DAY, '%Y-%m-%d 00:00:00')
    "
    , array())
->where('ts.time_add >= ?', $from." 00:00:00")
->where('ts.time_add <= ?', $to." 23:59:59")
->group('i1.distributor_id');
if (isset($params['season']) && $params['season'] == 2) {
// Season 02
    $select = $db->select()
    ->from(array('d' => WAREHOUSE_DB.'.distributor'), $get)
    ->joinLeft(array('rm1'  => 'regional_market')   , 'd.district = rm1.id' , array())
    ->joinLeft(array('rm2'  => 'regional_market')   , 'd.region = rm2.id'   , array())
    ->joinLeft(array('a'    => 'area')              , 'rm2.area_id = a.id'  , array())
    ->joinLeft(array('or1'   => 'oppoclub_reward')   ,
        "d.id = or1.d_id AND or1.quater_no = 'Quater_01' AND or1.quater_year = '2018' " , array())
    ->joinLeft(array('ol1'   => 'oppoclub_level')   , 'or1.level_id = ol1.id' , array('d1_level' => 'ol1.name'))
    ->joinLeft(array('or2'   => 'oppoclub_reward')  ,
        "d.id = or2.d_id AND or2.quater_no = 'Quater_02' AND or2.quater_year = '2018' " , array())
    ->joinLeft(array('ol2'   => 'oppoclub_level')   , 'or2.level_id = ol2.id' , array('d2_level' => 'ol2.name'))
    ->joinLeft(array('or3'   => 'oppoclub_reward')  ,
        "d.id = or3.d_id AND or3.quater_no = 'Quater_03' AND or3.quater_year = '2018' " , array())
    ->joinLeft(array('ol3'   => 'oppoclub_level')   , 'or3.level_id = ol3.id' , array('d3_level' => 'ol3.name'))
    ->joinLeft(array('or4'   => 'oppoclub_reward')  ,
        "d.id = or4.d_id AND or4.quater_no = 'Quater_04' AND or4.quater_year = '2018' " , array())
    ->joinLeft(array('ol4'   => 'oppoclub_level')   , 'or4.level_id = ol4.id' , array('d4_level' => 'ol4.name'))
    ->joinLeft(array('AAA' => new Zend_Db_Expr('('.$sub_select.')')), 'd.id = AAA.distributor_id', array())
    ->where('d.rank IN (7,8)')
    ->group('d.id')
    ->order('AAA.cnt_active DESC');
} else {
// Season 01
    $select = $db->select()
    ->from(array('d' => WAREHOUSE_DB.'.distributor'), $get)
    ->joinLeft(array('rm1'  => 'regional_market')   , 'd.district = rm1.id' , array())
    ->joinLeft(array('rm2'  => 'regional_market')   , 'd.region = rm2.id'   , array())
    ->joinLeft(array('a'    => 'area')              , 'rm2.area_id = a.id'  , array())
    ->joinLeft(array('or1'   => 'oppoclub_reward')   ,
        "d.id = or1.d_id AND or1.quater_no = 'Quater_01' AND or1.quater_year = '2016' " , array())
    ->joinLeft(array('ol1'   => 'oppoclub_level')   , 'or1.level_id = ol1.id' , array('d1_level' => 'ol1.name'))
    ->joinLeft(array('or2'   => 'oppoclub_reward')  ,
        "d.id = or2.d_id AND or2.quater_no = 'Quater_02' AND or2.quater_year = '2016' " , array())
    ->joinLeft(array('ol2'   => 'oppoclub_level')   , 'or2.level_id = ol2.id' , array('d2_level' => 'ol2.name'))
    ->joinLeft(array('or3'   => 'oppoclub_reward')  ,
        "d.id = or3.d_id AND or3.quater_no = 'Quater_03' AND or3.quater_year = '2016' " , array())
    ->joinLeft(array('ol3'   => 'oppoclub_level')   , 'or3.level_id = ol3.id' , array('d3_level' => 'ol3.name'))
    ->joinLeft(array('or4'   => 'oppoclub_reward')  ,
        "d.id = or4.d_id AND or4.quater_no = 'Quater_04' AND or4.quater_year = '2017' " , array())
    ->joinLeft(array('ol4'   => 'oppoclub_level')   , 'or4.level_id = ol4.id' , array('d4_level' => 'ol4.name'))
    ->joinLeft(array('AAA' => new Zend_Db_Expr('('.$sub_select.')')), 'd.id = AAA.distributor_id', array())
    ->where('d.rank IN (7,8)')
    ->group('d.id')
    ->order('AAA.cnt_active DESC');
}
// Filter Condition
if (isset($params['id']) && $params['id']) {
    $select->where('d.id = ?', $params['id']);
}
if (isset($params['name']) && $params['name']) {
    $select->where('d.title LIKE ?', "%".$params['name']."%");
}
if (isset($params['area_id'])) {
    if (is_array($params['area_id']) && count($params['area_id']) > 0) {
        $select->where('a.id IN (?)', $params['area_id']);
    }
}
if (isset($params['regional_market'])) {
    if (is_array($params['regional_market']) && count($params['regional_market']) > 0) {
        $select->where('d.region IN (?)', $params['regional_market']);
    }
}
if (isset($params['district'])) {
    if (is_array($params['district']) && count($params['district']) > 0) {
        $select->where('d.district IN (?)', $params['district']);
    }
}
if (isset($params['level_id']) && $params['level_id']) {
// Season 01
    if ( $from >= '2016-04-01' && $to <= '2016-06-30' ) {
        $select->where('or1.level_id = ?', $params['level_id']);
    } else if ( $from >= '2016-07-01' && $to <= '2016-09-30' ) {
        $select->where('or2.level_id = ?', $params['level_id']);
    } else if ( $from >= '2016-10-01' && $to <= '2016-12-31' ) {
        $select->where('or3.level_id = ?', $params['level_id']);
    } else if ( $from >= '2017-01-01' && $to <= '2017-03-31' ) {
        $select->where('or4.level_id = ?', $params['level_id']);
    }
// Season 02
    else if ( $from >= '2018-03-01' && $to <= '2018-05-31' ) {
        $select->where('or1.level_id = ?', $params['level_id']);
    } else if ( $from >= '2018-07-01' && $to <= '2018-09-30' ) {
        $select->where('or2.level_id = ?', $params['level_id']);
    } else if ( $from >= '2018-10-01' && $to <= '2018-12-31' ) {
        $select->where('or3.level_id = ?', $params['level_id']);
    } else if ( $from >= '2019-01-01' && $to <= '2019-03-31' ) {
        $select->where('or4.level_id = ?', $params['level_id']);
    }
    else {
        $select->where('( or1.level_id = ?', $params['level_id']);
        $select->orWhere('or2.level_id = ?', $params['level_id']);
        $select->orWhere('or3.level_id = ?', $params['level_id']);
        $select->orWhere('or4.level_id = ? )', $params['level_id']);
    }
}
// check ermission ASM, ASM Stand by, Sale Admin, Traning
if ( isset($params['asm']) && $params['asm'] ) {
    $QAsm = new Application_Model_Asm();
    $list_regions = $QAsm->get_cache($params['asm']);
    $list_regions = isset($list_regions['district']) && is_array($list_regions['district']) ? $list_regions['district'] : array();
    if (count($list_regions) > 0)
        $select->where( 'd.district IN (?)', $list_regions);
    else
        $select->where('1=0', 1);
}
if ($limit) {
    $select->limitPage($page, $limit);
}
//echo $select; die;
$result = $db->fetchAll($select);
$total = $db->fetchOne("select FOUND_ROWS()");
//print_r($result);die;
return $result;
}
function get_total_oppoclub($params) {
    $db = Zend_Registry::get('db');
// Prepare Data
    $tmp_from = explode('/', $params['from']);
    $from = $tmp_from[2].'-'.$tmp_from[1].'-'.$tmp_from[0];
    $tmp_to = explode('/', $params['to']);
    $to = $tmp_to[2].'-'.$tmp_to[1].'-'.$tmp_to[0];
    $select = $db->select()
    ->from(array('ts' => 'timing_sale'), array(
        'sellout'   => new Zend_Db_Expr('COUNT(i1.imei_sn)'),
        'count'     => new Zend_Db_Expr('COUNT(i2.imei_sn)'),
        'active'    => new Zend_Db_Expr('COUNT(i3.imei_sn)')
    ))
    ->join(array('t' => 'timing'), 'ts.timing_id = t.id', array())
    ->join(array('st' => 'store'), 't.store = st.id', array())
    ->join(array('o' => 'org'), 'st.org_dealer = o.org_id AND o.store_type_id = 2', array())
    ->join(array('i1' => WAREHOUSE_DB.'.imei'), 'i1.imei_sn = ts.imei', array())
/*
->joinLeft(array('i2' => WAREHOUSE_DB.'.imei'),
"   ts.imei = i2.imei_sn
AND i2.activated_date <= DATE_FORMAT(LAST_DAY(t.created_at) + INTERVAL 7 DAY, '%Y-%m-%d 23:59:59')
AND i2.activated_date >= DATE_FORMAT(DATE_ADD(DATE_ADD(LAST_DAY(t.created_at),INTERVAL 1 DAY),INTERVAL -1 MONTH) - INTERVAL 7 DAY,'%Y-%m-%d 00:00:00')
"
, array())
*/
->joinLeft(array('i2' => WAREHOUSE_DB.'.imei'),
    "   ts.imei = i2.imei_sn
    AND i2.activated_date <= DATE_FORMAT(DATE(t.created_at) + INTERVAL 7 DAY, '%Y-%m-%d 23:59:59')
    AND i2.activated_date >= DATE_FORMAT(DATE(t.created_at) - INTERVAL 3 DAY, '%Y-%m-%d 00:00:00')
    "
    , array())
->joinLeft(array('i3' => WAREHOUSE_DB.'.imei'), 'i3.imei_sn = ts.imei AND i3.activated_date IS NOT NULL', array())
->join(array('d' => WAREHOUSE_DB.'.distributor'), 'd.id = i1.distributor_id AND d.rank IN (7,8)', array())
->where('ts.time_add >= ?', $from." 00:00:00")
->where('ts.time_add <= ?', $to." 23:59:59");
// Filter Condition
if (isset($params['id']) && $params['id']) {
    $select->where('d.id = ?', $params['id']);
}
if (isset($params['name']) && $params['name']) {
    $select->where('d.title LIKE ?', "%".$params['name']."%");
}
if (isset($params['area_id'])) {
    $select->join(array('rm' => 'regional_market'), 'd.region = rm.id', array());
    if (is_array($params['area_id']) && count($params['area_id']) > 0) {
        $select->where('rm.area_id IN (?)', $params['area_id']);
    }
}
if (isset($params['regional_market'])) {
    if (is_array($params['regional_market']) && count($params['regional_market']) > 0) {
        $select->where('d.region IN (?)', $params['regional_market']);
    }
}
if (isset($params['district'])) {
    if (is_array($params['district']) && count($params['district']) > 0) {
        $select->where('d.district IN (?)', $params['district']);
    }
}
if (isset($params['level_id']) && $params['level_id']) {
    if (isset($params['season']) && $params['season'] == 2) {
// Season 02
        $select->joinLeft(array('or1'   => 'oppoclub_reward')   ,
            "d.id = or1.d_id AND or1.quater_no = 'Quater_01' AND or1.quater_year = '2018' " , array())
        ->joinLeft(array('ol1'   => 'oppoclub_level')   , 'or1.level_id = ol1.id' , array('d1_level' => 'ol1.name'))
        ->joinLeft(array('or2'   => 'oppoclub_reward')  ,
            "d.id = or2.d_id AND or2.quater_no = 'Quater_02' AND or2.quater_year = '2018' " , array())
        ->joinLeft(array('ol2'   => 'oppoclub_level')   , 'or2.level_id = ol2.id' , array('d2_level' => 'ol2.name'))
        ->joinLeft(array('or3'   => 'oppoclub_reward')  ,
            "d.id = or3.d_id AND or3.quater_no = 'Quater_03' AND or3.quater_year = '2018' " , array())
        ->joinLeft(array('ol3'   => 'oppoclub_level')   , 'or3.level_id = ol3.id' , array('d3_level' => 'ol3.name'))
        ->joinLeft(array('or4'   => 'oppoclub_reward')  ,
            "d.id = or4.d_id AND or4.quater_no = 'Quater_04' AND or4.quater_year = '2018' " , array())
        ->joinLeft(array('ol4'   => 'oppoclub_level')   , 'or4.level_id = ol4.id' , array('d4_level' => 'ol4.name'));
    } else {
// Season 01
        $select->joinLeft(array('or1'   => 'oppoclub_reward')   ,
            "d.id = or1.d_id AND or1.quater_no = 'Quater_01' AND or1.quater_year = '2016' " , array())
        ->joinLeft(array('ol1'   => 'oppoclub_level')   , 'or1.level_id = ol1.id' , array('d1_level' => 'ol1.name'))
        ->joinLeft(array('or2'   => 'oppoclub_reward')  ,
            "d.id = or2.d_id AND or2.quater_no = 'Quater_02' AND or2.quater_year = '2016' " , array())
        ->joinLeft(array('ol2'   => 'oppoclub_level')   , 'or2.level_id = ol2.id' , array('d2_level' => 'ol2.name'))
        ->joinLeft(array('or3'   => 'oppoclub_reward')  ,
            "d.id = or3.d_id AND or3.quater_no = 'Quater_03' AND or3.quater_year = '2016' " , array())
        ->joinLeft(array('ol3'   => 'oppoclub_level')   , 'or3.level_id = ol3.id' , array('d3_level' => 'ol3.name'))
        ->joinLeft(array('or4'   => 'oppoclub_reward')  ,
            "d.id = or4.d_id AND or4.quater_no = 'Quater_04' AND or4.quater_year = '2017' " , array())
        ->joinLeft(array('ol4'   => 'oppoclub_level')   , 'or4.level_id = ol4.id' , array('d4_level' => 'ol4.name'));
    }
// Season 01
    if ( $from >= '2016-04-01' && $to <= '2016-06-30') {
        $select->where('or1.level_id = ?', $params['level_id']);
    } else if ( $from >= '2016-07-01' && $to <= '2016-09-30' ) {
        $select->where('or2.level_id = ?', $params['level_id']);
    } else if ( $from >= '2016-10-01' && $to <= '2016-12-31' ) {
        $select->where('or3.level_id = ?', $params['level_id']);
    } else if ( $from >= '2017-01-01' && $to <= '2017-03-31' ) {
        $select->where('or4.level_id = ?', $params['level_id']);
    }
// Season 02
    else if ( $from >= '2018-03-01' && $to <= '2018-05-31' ) {
        $select->where('or1.level_id = ?', $params['level_id']);
    } else if ( $from >= '2018-07-01' && $to <= '2018-09-30' ) {
        $select->where('or2.level_id = ?', $params['level_id']);
    } else if ( $from >= '2018-10-01' && $to <= '2018-12-31' ) {
        $select->where('or3.level_id = ?', $params['level_id']);
    } else if ( $from >= '2019-01-01' && $to <= '2019-03-31' ) {
        $select->where('or4.level_id = ?', $params['level_id']);
    }
    else {
        $select->where('( or1.level_id = ?', $params['level_id']);
        $select->orWhere('or2.level_id = ?', $params['level_id']);
        $select->orWhere('or3.level_id = ?', $params['level_id']);
        $select->orWhere('or4.level_id = ? )', $params['level_id']);
    }
/*
$select->where('( or1.level_id = ?', $params['level_id']);
$select->orWhere('or2.level_id = ?', $params['level_id']);
$select->orWhere('or3.level_id = ?', $params['level_id']);
$select->orWhere('or4.level_id = ? )', $params['level_id']);
*/
}
// check ermission ASM, ASM Stand by, Sale Admin, Traning
if ( isset($params['asm']) && $params['asm'] ) {
    $QAsm = new Application_Model_Asm();
    $list_regions = $QAsm->get_cache($params['asm']);
    $list_regions = isset($list_regions['district']) && is_array($list_regions['district']) ? $list_regions['district'] : array();
    if (count($list_regions) > 0)
        $select->where( 'd.district IN (?)', $list_regions);
    else
        $select->where('1=0', 1);
}
//echo $select;
$result = $db->fetchRow($select);
return $result;
}
function get_store_all_oppoclub($params) {
    $db = Zend_Registry::get('db');
    $select = $db->select()
    ->from(array('st'   => 'store'), array('st_id' => 'st.id', 'store_name' => 'st.name', 'store_rank' => 'st.rank'))
    ->join(array('d'    => WAREHOUSE_DB.'.distributor') ,
        'st.d_id = d.id AND d.rank IN (7,8)',
        array('d_id' => 'd.id', 'd_name' => 'd.title', 'd_rank' => 'd.rank'))
    ->joinLeft(array('rm1'  => 'regional_market')   , 'st.regional_market = rm1.id' , array('province' => 'rm1.name'))
    ->joinLeft(array('rm2'  => 'regional_market')   , 'st.district = rm2.id'        , array('district' => 'rm2.name'))
    ->joinLeft(array('a'    => 'area')              , 'rm1.area_id = a.id'          , array('area_name' => 'a.name'))
    ->joinLeft(array('o'    => 'org')               , 'st.org_dealer = o.org_id'    , array('store_type' => 'o.org_name'))
    ->where('o.store_type_id = 2')
    ->where('d.id = ?', $params['dealer_id'])
    ->group('st.id')
    ->order('st.name ASC');
//echo $select;
    $result = $db->fetchAll($select);
    return $result;
}
function get_store_active_oppoclub($params) {
    $db = Zend_Registry::get('db');
// Prepare Data
    $tmp_from = explode('/', $params['from']);
    $from = $tmp_from[2].'-'.$tmp_from[1].'-'.$tmp_from[0];
    $tmp_to = explode('/', $params['to']);
    $to = $tmp_to[2].'-'.$tmp_to[1].'-'.$tmp_to[0];
    $select = $db->select()
    ->from(array('st'   => 'store'), array('st_id' => 'st.id', 'store_name' => 'st.name', 'store_rank' => 'st.rank'))
    ->join(array('t'    => 'timing')        , 'st.id = t.store'     , array())
    ->join(array('ts'   => 'timing_sale')   , 't.id = ts.timing_id' , array())
    ->join(array('i1'    => WAREHOUSE_DB.'.imei'), 'ts.imei = i1.imei_sn',
        array('sellout' => new Zend_Db_Expr('COUNT(i1.imei_sn)') ))
    ->joinLeft(array('i2'    => WAREHOUSE_DB.'.imei'),
        'ts.imei = i2.imei_sn AND i2.activated_date IS NOT NULL',
        array('active' => new Zend_Db_Expr('COUNT(i2.imei_sn)') ))
/*
->joinLeft(array('i3'    => WAREHOUSE_DB.'.imei'),
"   ts.imei = i3.imei_sn
AND i3.activated_date <= DATE_FORMAT(LAST_DAY(t.created_at) + INTERVAL 7 DAY, '%Y-%m-%d 23:59:59')
AND i3.activated_date >= DATE_FORMAT(DATE_ADD(DATE_ADD(LAST_DAY(t.created_at),INTERVAL 1 DAY),INTERVAL -1 MONTH) - INTERVAL 7 DAY,'%Y-%m-%d 00:00:00')
"
, array('count' => new Zend_Db_Expr('COUNT(i3.imei_sn)') ))
*/
->joinLeft(array('i3'    => WAREHOUSE_DB.'.imei'),
    "   ts.imei = i3.imei_sn
    AND i3.activated_date <= DATE_FORMAT(DATE(t.created_at) + INTERVAL 7 DAY, '%Y-%m-%d 23:59:59')
    AND i3.activated_date >= DATE_FORMAT(DATE(t.created_at) - INTERVAL 3 DAY, '%Y-%m-%d 00:00:00')
    "
    , array('count' => new Zend_Db_Expr('COUNT(i3.imei_sn)') ))
->join(array('d'    => WAREHOUSE_DB.'.distributor') ,
    'i1.distributor_id = d.id AND d.rank IN (7,8)',
    array('d_id' => 'd.id', 'd_name' => 'd.title', 'd_rank' => 'd.rank'))
->joinLeft(array('rm1'  => 'regional_market')   , 'st.regional_market = rm1.id' , array('province' => 'rm1.name'))
->joinLeft(array('rm2'  => 'regional_market')   , 'st.district = rm2.id'        , array('district' => 'rm2.name'))
->joinLeft(array('a'    => 'area')              , 'rm1.area_id = a.id'          , array('area_name' => 'a.name'))
->joinLeft(array('o'    => 'org')               , 'st.org_dealer = o.org_id'    , array('store_type' => 'o.org_name'))
->where('o.store_type_id = 2')
->where('d.id = ?', $params['dealer_id'])
->where('ts.time_add >= ?', $from." 00:00:00")
->where('ts.time_add <= ?', $to." 23:59:59")
->group('st.id')
->order('d.title ASC');
//echo $select;
$result = $db->fetchAll($select);
return $result;
}
//
public function get_storeHUB_active_oppoclub($params) {
    set_time_limit(0);
    ini_set('memory_limit', '-1');
    error_reporting(~E_ALL);
    ini_set("display_error", '0');
    $db = Zend_Registry::get('db');
// Prepare Data
    $tmp_from = explode('/', $params['from']);
    $from = $tmp_from[2].'-'.$tmp_from[1].'-'.$tmp_from[0];
    $tmp_to = explode('/', $params['to']);
    $to = $tmp_to[2].'-'.$tmp_to[1].'-'.$tmp_to[0];
    $get = array(
        'd_id'          =>  new Zend_Db_Expr('SQL_CALC_FOUND_ROWS d.id'),
        'd_name'        =>  'd.title',
        'd_rank'        =>  'd.rank',
        'st_id'         =>  'AAA.store_id',
        'st_name'       =>  'AAA.store_name',
        'st_rank'       =>  'AAA.store_rank',
        'area_name'     =>  'a.name',
        'province'      =>  'rm2.name',
        'district'      =>  'rm1.name',
        'total_sellout' =>  'AAA.sellout',
        'total_active'  =>  'AAA.active',
        'cnt_active'    =>  'AAA.cnt_active'
    );
// Generate SubQuery for each Quater
    $sub_from = $tmp_from[2].'-'.$tmp_from[1];
    $sub_to = $tmp_to[2].'-'.$tmp_to[1];
    $sub_from_last = date('Y-m-t', strtotime($sub_from));
    $diff_month = date('m', ( strtotime($sub_to) - strtotime($sub_from) ) );
    for ($i=0;$i<=$diff_month;$i++) {

        $sub_select_quater[$i] = $db->select()
        ->from(array('t2' => 'timing'), new Zend_Db_Expr('COUNT(ts2.imei)') )
        ->join(array('ts2' => 'timing_sale'), 't2.id = ts2.timing_id', array())
/*
->join(array('i2' => WAREHOUSE_DB.'.imei'),
"   ts2.imei = i2.imei_sn
AND i2.activated_date <= DATE_FORMAT(LAST_DAY(t2.created_at) + INTERVAL 7 DAY, '%Y-%m-%d 23:59:59')
AND i2.activated_date >= DATE_FORMAT(DATE_ADD(DATE_ADD(LAST_DAY(t2.created_at),INTERVAL 1 DAY),INTERVAL -1 MONTH) - INTERVAL 7 DAY,'%Y-%m-%d 00:00:00')
"
, array())
*/
->join(array('i2' => WAREHOUSE_DB.'.imei'),
    "   ts2.imei = i2.imei_sn
    AND i2.activated_date <= DATE_FORMAT(DATE(t2.created_at) + INTERVAL 7 DAY, '%Y-%m-%d 23:59:59')
    AND i2.activated_date >= DATE_FORMAT(DATE(t2.created_at) - INTERVAL 3 DAY, '%Y-%m-%d 00:00:00')
    "
    , array())
->join(array('st2' => 'store'), 't2.store = st2.id', array())
->join(array('o' => 'org'), 'st2.org_dealer = o.org_id AND o.store_type_id = 2', array())
->where('i2.distributor_id = d.id')
->where('t2.store = AAA.store_id');
if ($i==0) {
    $sub_select_quater[$i]->where('ts2.time_add >= ?', $from." 00:00:00");
    if (strtotime($to) < strtotime($sub_from_last) ) { $sub_select_quater[$i]->where('ts2.time_add <= ?', $to." 23:59:59"); }
    else { $sub_select_quater[$i]->where('ts2.time_add <= ?', $sub_from_last." 23:59:59"); }
    $get[ "QCnt_".$tmp_from[1]."_".$tmp_from[2] ] = '('.$sub_select_quater[$i].')';

} else {
    $tmp_start = date("Y-m-01", strtotime(date("Y-m-01", strtotime($sub_from)) . "+".$i." month"));
    $tmp_end = date("Y-m-t", strtotime(date("Y-m-01", strtotime($sub_from)) . "+".$i." month"));
    $sub_select_quater[$i]->where('ts2.time_add >= ?', $tmp_start." 00:00:00");
    if (strtotime($to) < strtotime($tmp_end) ) { $sub_select_quater[$i]->where('ts2.time_add <= ?', $to." 23:59:59"); }
    else { $sub_select_quater[$i]->where('ts2.time_add <= ?', $tmp_end." 23:59:59"); }
// split sub quater
    $d = explode('-', $tmp_start);
    $get[ "QCnt_".$d[1]."_".$d[0] ] = '('.$sub_select_quater[$i].')';

}
}
// Get Sellout Activate Each Month for Export
// For Total Sellout
for ($i=0;$i<=$diff_month;$i++) {

    $sub_select_quater[$i] = $db->select()
    ->from(array('t2' => 'timing'), new Zend_Db_Expr('COUNT(ts2.imei)') )
    ->join(array('ts2' => 'timing_sale'), 't2.id = ts2.timing_id', array())
    ->join(array('i2' => WAREHOUSE_DB.'.imei'), 'ts2.imei = i2.imei_sn', array())
    ->join(array('st2' => 'store'), 't2.store = st2.id', array())
    ->join(array('o' => 'org'), 'st2.org_dealer = o.org_id AND o.store_type_id = 2', array())
    ->where('i2.distributor_id = d.id')
    ->where('t2.store = AAA.store_id');
    if ($i==0) {
        $sub_select_quater[$i]->where('ts2.time_add >= ?', $from." 00:00:00");
        if (strtotime($to) < strtotime($sub_from_last) ) { $sub_select_quater[$i]->where('ts2.time_add <= ?', $to." 23:59:59"); }
        else { $sub_select_quater[$i]->where('ts2.time_add <= ?', $sub_from_last." 23:59:59"); }
        $get[ "Q_".$tmp_from[1]."_".$tmp_from[2] ] = '('.$sub_select_quater[$i].')';

    } else {
        $tmp_start = date("Y-m-01", strtotime(date("Y-m-01", strtotime($sub_from)) . "+".$i." month"));
        $tmp_end = date("Y-m-t", strtotime(date("Y-m-01", strtotime($sub_from)) . "+".$i." month"));
        $sub_select_quater[$i]->where('ts2.time_add >= ?', $tmp_start." 00:00:00");
        if (strtotime($to) < strtotime($tmp_end) ) { $sub_select_quater[$i]->where('ts2.time_add <= ?', $to." 23:59:59"); }
        else { $sub_select_quater[$i]->where('ts2.time_add <= ?', $tmp_end." 23:59:59"); }
// split sub quater
        $d = explode('-', $tmp_start);
        $get[ "Q_".$d[1]."_".$d[0] ] = '('.$sub_select_quater[$i].')';
    }
}
// For Total Activate
for ($i=0;$i<=$diff_month;$i++) {

    $sub_select_quater[$i] = $db->select()
    ->from(array('t2' => 'timing'), new Zend_Db_Expr('COUNT(ts2.imei)') )
    ->join(array('ts2' => 'timing_sale'), 't2.id = ts2.timing_id', array())
    ->join(array('i2' => WAREHOUSE_DB.'.imei'), 'ts2.imei = i2.imei_sn AND i2.activated_date IS NOT NULL', array())
    ->join(array('st2' => 'store'), 't2.store = st2.id', array())
    ->join(array('o' => 'org'), 'st2.org_dealer = o.org_id AND o.store_type_id = 2', array())
    ->where('i2.distributor_id = d.id')
    ->where('t2.store = AAA.store_id');
    if ($i==0) {
        $sub_select_quater[$i]->where('ts2.time_add >= ?', $from." 00:00:00");
        if (strtotime($to) < strtotime($sub_from_last) ) { $sub_select_quater[$i]->where('ts2.time_add <= ?', $to." 23:59:59"); }
        else { $sub_select_quater[$i]->where('ts2.time_add <= ?', $sub_from_last." 23:59:59"); }
        $get[ "QA_".$tmp_from[1]."_".$tmp_from[2] ] = '('.$sub_select_quater[$i].')';

    } else {
        $tmp_start = date("Y-m-01", strtotime(date("Y-m-01", strtotime($sub_from)) . "+".$i." month"));
        $tmp_end = date("Y-m-t", strtotime(date("Y-m-01", strtotime($sub_from)) . "+".$i." month"));
        $sub_select_quater[$i]->where('ts2.time_add >= ?', $tmp_start." 00:00:00");
        if (strtotime($to) < strtotime($tmp_end) ) { $sub_select_quater[$i]->where('ts2.time_add <= ?', $to." 23:59:59"); }
        else { $sub_select_quater[$i]->where('ts2.time_add <= ?', $tmp_end." 23:59:59"); }
// split sub quater
        $d = explode('-', $tmp_start);
        $get[ "QA_".$d[1]."_".$d[0] ] = '('.$sub_select_quater[$i].')';
    }
}
$sub_select = $db->select()
->from(array('ts' => 'timing_sale'), array(
    'sellout'   => new Zend_Db_Expr('COUNT(i1.imei_sn)'),
    'active'    => new Zend_Db_Expr('COUNT(i2.imei_sn)'),
    'cnt_active'=> new Zend_Db_Expr('COUNT(i3.imei_sn)'),
    'store_id'  => 'st.id',
    'store_name'=> 'st.name',
    'store_rank'=> 'st.rank',
    'store_regional_market' => 'st.regional_market',
    'store_district'        => 'st.district',
    'i1.distributor_id'))
->join(array('t' => 'timing'), 'ts.timing_id = t.id', array())
->join(array('st' => 'store'), 't.store = st.id', array())
->join(array('o' => 'org'), 'st.org_dealer = o.org_id AND o.store_type_id = 2', array())
->join(array('i1' => WAREHOUSE_DB.'.imei'), 'i1.imei_sn = ts.imei', array())
->joinLeft(array('i2' => WAREHOUSE_DB.'.imei'), 'i2.imei_sn = ts.imei AND i2.activated_date IS NOT NULL', array())
/*
->joinLeft(array('i3' => WAREHOUSE_DB.'.imei'),
"   ts.imei = i3.imei_sn
AND i3.activated_date <= DATE_FORMAT(LAST_DAY(t.created_at) + INTERVAL 7 DAY, '%Y-%m-%d 23:59:59')
AND i3.activated_date >= DATE_FORMAT(DATE_ADD(DATE_ADD(LAST_DAY(t.created_at),INTERVAL 1 DAY),INTERVAL -1 MONTH) - INTERVAL 7 DAY,'%Y-%m-%d 00:00:00')
"
, array())
*/
->joinLeft(array('i3' => WAREHOUSE_DB.'.imei'),
    "   ts.imei = i3.imei_sn
    AND i3.activated_date <= DATE_FORMAT(DATE(t.created_at) + INTERVAL 7 DAY, '%Y-%m-%d 23:59:59')
    AND i3.activated_date >= DATE_FORMAT(DATE(t.created_at) - INTERVAL 3 DAY, '%Y-%m-%d 00:00:00')
    "
    , array())
->where('ts.time_add >= ?', $from." 00:00:00")
->where('ts.time_add <= ?', $to." 23:59:59")
->group(array('i1.distributor_id','t.store'));
$select = $db->select()
->from(array('d' => WAREHOUSE_DB.'.distributor'), $get)
->joinLeft(array('AAA' => new Zend_Db_Expr('('.$sub_select.')')), 'd.id = AAA.distributor_id'   , array())
->joinLeft(array('rm1'  => 'regional_market')   , 'AAA.store_district = rm1.id'         , array())
->joinLeft(array('rm2'  => 'regional_market')   , 'AAA.store_regional_market = rm2.id'  , array())
->joinLeft(array('a'    => 'area')              , 'rm2.area_id = a.id'  , array())
->where('d.rank IN (7,8)')
->where('d.id = ?', $params['dealer_id'])
->order('AAA.sellout DESC');
//echo $select; die;
$result = $db->fetchAll($select);
//print_r($result);die;
return $result;
}
// function for web service for BI Database [Sellout]
function getSellOut_BI($params){
    set_time_limit(0);
    ini_set('memory_limit', '-1');
    error_reporting(~E_ALL);
    ini_set("display_error", '0');
    $db = Zend_Registry::get('db');
    $select = $db->select()
    ->from(array('ts' => 'timing_sale'), array('total_sellout' => new Zend_Db_Expr('COUNT(ts.imei)') ))
    ->joinLeft(array('t' => 'timing'), 't.id = ts.timing_id',
        array(
            'timing_date' => new Zend_Db_Expr('DATE(t.created_at)'),
            'product_id'  => 'ts.product_id',
            'color_id'    => 'ts.model_id'
        ))
    ->join(array('st' => 'store'), 't.store = st.id', array('st_id' => 'st.id'))
    ->join(array('i' => WAREHOUSE_DB.'.imei'), 'ts.imei = i.imei_sn AND i.old_data IS NULL', array())
    ->join(array('d' => WAREHOUSE_DB.'.distributor'), 'i.distributor_id = d.id',
        array(
            'd_id'      => 'd.id',
            'd_province'  => 'd.region',
            'd_district'  => 'd.district'
        ))
    ->join(array('rm' => 'regional_market'), 'd.region = rm.id', array('d_area' => 'rm.area_id'))
    ->joinLeft(array('i2' => WAREHOUSE_DB.'.imei'),
        'ts.imei = i2.imei_sn AND i2.old_data IS NULL AND i2.activated_date IS NOT NULL',
        array('total_active' => new Zend_Db_Expr('COUNT(i2.imei_sn)') ))
    ->group(array('ts.product_id','ts.model_id','st.id','DATE(t.created_at)'));
    $select->where('t.created_at >= ?', $params['from_date'].' 00:00:00');
    $select->where('t.created_at <= ?', $params['to_date'].' 23:59:59');
//echo $select; die;
    $result = $db->fetchAll($select);
    return $result;
}
// function for web service for BI Database [Sellin]
function getSellIn_BI($params){
    set_time_limit(0);
    ini_set('memory_limit', '-1');
    error_reporting(~E_ALL);
    ini_set("display_error", '0');
    $db = Zend_Registry::get('db');
    $select = $db->select()
    ->from(array('m' => WAREHOUSE_DB.'.market'), array('sellin_date' => new Zend_Db_Expr('DATE(m.outmysql_time)') ))
    ->join(array('i' => WAREHOUSE_DB.'.imei'), 'm.sn = i.sales_sn AND i.old_data IS NULL',
        array(
            'total_sellin'  => new Zend_Db_Expr('COUNT(i.imei_sn)'),
            'good_id'       => 'i.good_id',
            'color_id'      => 'i.good_color'
        ))
    ->join(array('d' => WAREHOUSE_DB.'.distributor'), 'i.distributor_id = d.id',
        array(
            'd_id'      => 'd.id',
            'province'  => 'd.region',
            'district'  => 'd.district'
        ))
    ->join(array('rm' => 'regional_market'), 'd.region = rm.id', array('area_id' => 'rm.area_id'))
    ->joinLeft(array('i2' => WAREHOUSE_DB.'.imei'),
        'i.imei_sn = i2.imei_sn AND i2.old_data IS NULL AND i2.activated_date IS NOT NULL',
        array( 'total_active'  => new Zend_Db_Expr('COUNT(i2.imei_sn)') ))
    ->group(array('i.good_id','i.good_color','d.id','DATE(m.outmysql_time)') );
    $select->where('m.outmysql_time >= ?', $params['from_date'].' 00:00:00');
    $select->where('m.outmysql_time <= ?', $params['to_date'].' 23:59:59');
//echo $select; die;
    $result = $db->fetchAll($select);
    return $result;
}
// function for web service for BI Database [Remain Stock]
function getRemainStock_BI($params){
    set_time_limit(0);
    ini_set('memory_limit', '-1');
    error_reporting(~E_ALL);
    ini_set("display_error", '0');
    $db = Zend_Registry::get('db');
    $sub_select = $db->select()
    ->from(array('ts' => 'timing_sale'), array('imei' => 'ts.imei'))
    ->where('ts.imei = i.imei_sn');
    $select = $db->select()
    ->from(array('i' => WAREHOUSE_DB.'.imei'), array('remain_stock' => new Zend_Db_Expr('COUNT(i.imei_sn)') ))
    ->join(array('g'  => WAREHOUSE_DB.'.good'), 'i.good_id = g.id', array('good_name' => 'g.name', 'good_desc' => 'g.desc'))
    ->join(array('gc' => WAREHOUSE_DB.'.good_color')  ,'i.good_color = gc.id'   , array('color_name' => 'gc.name'))
    ->group(array('g.id','gc.id') )
    ->order(array('remain_stock DESC', 'good_name ASC', 'color_name ASC'));
    $select->where('g.cat_id = ?', PHONE_CAT_ID);
    $select->where('i.old_data IS NULL');
    $select->where('i.distributor_id = ?', $params['dealer_id']);
    $select->where('i.imei_sn NOT IN (?)', $sub_select);
//echo $select; die;
    $result = $db->fetchAll($select);
    return $result;
}
public function GetSellOutForWebService($params){
    $db = Zend_Registry::get('db');
    $select = $db->select()
    ->from(array('t'=> $this->_name),array(
        'one'   => 'COUNT(DISTINCT CASE WHEN t.created_at >= "'.$params['one'].'" AND t.created_at <= "'.$params['one_end'].'" THEN ts.imei ELSE NULL END)',
        'two'   => 'COUNT(DISTINCT CASE WHEN t.created_at >= "'.$params['two'].'" AND t.created_at <= "'.$params['two_end'].'" THEN ts.imei ELSE NULL END)',
        'three' => 'COUNT(DISTINCT CASE WHEN t.created_at >= "'.$params['three'].'" AND t.created_at <= "'.$params['three_end'].'" THEN ts.imei ELSE NULL END)'
    ))
    ->join(array('ts'=>'timing_sale'),
        't.id = ts.timing_id
        AND t.created_at IS NOT NULL
        AND t.created_at <> 0
        AND t.created_at <> ""
        AND t.created_at >= "'. $params['three'] .'"
        AND t.created_at <= "'. $params['currentMonth'].'"
        ',
        array())
    ->where('t.store = ?',$params['store_id']);

    $sellout = $db->fetchRow($select);
    return $sellout;
}
public function getAreaCoverage($params){
// change date format
    $d = explode('/', $params['from']);
    $from = $d[2].'-'.$d[1].'-'.$d[0];
    $d = explode('/', $params['to']);
    $to = $d[2].'-'.$d[1].'-'.$d[0];
    $db = Zend_Registry::get('db');

    $sub_select_join = $db->select()
    ->from(array('st_all' => 'store'), array('cnt_store' => new Zend_Db_Expr('COUNT(st_all.id)')))
    ->join(array('ss2' => 'store_staff')     , 'st_all.id = ss2.store_id'        , array())
    ->join(array('s3'  => 'staff')           , 'ss2.staff_id = s3.id'            , array())
    ->join(array('rm2' => 'regional_market') , 'st_all.regional_market = rm2.id' , array())
    ->join(array('a2'  => 'area')            , 'rm2.area_id = a2.id'             , array())
    ->join(array('o' => 'org')               , 'st_all.org_dealer = o.org_id'    , array())
    ->where('o.store_type_id = 2')
    ->where('st_all.rank <> 4')
    ->where('a2.id = a.id')
    ->where('s3.id = s2.id');
    $sub_get = array(
        'store_name' => 'st.name',
        'sellout' => new Zend_Db_Expr('COUNT(ts.imei)'),
        'sale_code' => 's2.code',
        'sale_name' => new Zend_Db_Expr("CONCAT(s2.firstname,' ',s2.lastname)"),
        'area_name' => 'a.name',
        'areas_id'  => 'a.id',
        't.sales_id',
        'store_all' => new Zend_Db_Expr("(".$sub_select_join.")")
    );
    $sub_select = $db->select()
    ->from(array('st'=> 'store'), $sub_get)
    ->join(array('t' => 'timing'),
        "   st.id = t.store
        AND t.created_at >= '".$from." 00:00:00'
        AND t.created_at <= '".$to." 23:59:59'
        "
        ,array())
    ->join(array('ts' => 'timing_sale'), 't.id = ts.timing_id', array())
    ->join(array('rm' => 'regional_market'), 'st.regional_market = rm.id', array())
    ->join(array('a' => 'area'), 'rm.area_id = a.id', array())
    ->join(array('s2' => 'staff'), 't.sales_id = s2.id', array())
    ->join(array('o' => 'org'), 'st.org_dealer = o.org_id', array())
    ->where('o.store_type_id = 2')
    ->where('st.rank <> 4')
    ->group(array('t.store', 't.sales_id'))
    ->having('sellout >= 2');
    $get = array(
        'asm_code'  => 's.code',
        'asm_name'  => new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)"),
        'st_area'   => 'AAA.area_name',
        'sale_code' => 'AAA.sale_code',
        'sale_name' => 'AAA.sale_name',
        'st_active' => new Zend_Db_Expr("COUNT(AAA.store_name)"),
        'st_all'    => 'AAA.store_all'
    );
    $select = $db->select()
    ->from(array('s'=> 'staff'), $get)
    ->join(array('asm' => 'asm'), 's.id = asm.staff_id', array())
    ->joinLeft(array('AAA' => $sub_select), 'asm.area_id = AAA.areas_id',array())
    ->where('s.group_id = 5')
    ->group(array('s.id','asm.area_id','AAA.sales_id'));
//echo $select; die;
    $result = $db->fetchAll($select);
    return $result;
}
// Inventory Report : MTD Sellout, Last Sellout
public function getInventorySellout($params){
// change date format
    $d = explode('/', $params['from']);
    $from = $d[2].'-'.$d[1].'-'.$d[0];
    $d = explode('/', $params['to']);
    $to = $d[2].'-'.$d[1].'-'.$d[0];
    $db = Zend_Registry::get('db');
    $sub_get = array(
        'sale_id'   => 's.id',
        'sale_code' => 's.code',
        'sale_name' => new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)"),
        'store_id'  => 'st.id',
        'store_name'=> 'st.name',
        'areas_name'=> 'a2.name',
        'sellout'   => new Zend_Db_Expr("COUNT(i.imei_sn)"),
        'i.distributor_id'
    );
    $sub_select = $db->select()
    ->from(array('i'=> WAREHOUSE_DB.'.imei'), $sub_get)
    ->join(array('ts' => 'timing_sale'), 'i.imei_sn = ts.imei', array())
    ->join(array('t' => 'timing'),
        "   ts.timing_id = t.id
        AND t.created_at >= '".$from." 00:00:00'
        AND t.created_at <= '".$to." 23:59:59'
        "
        ,array())
    ->join(array('st'  => 'store')           , 't.store = st.id'                , array())
    ->join(array('o'   => 'org')             , 'st.org_dealer = o.org_id AND o.store_type_id = 2', array())
    ->join(array('rm2' => 'regional_market') , 'st.regional_market = rm2.id'    , array())
    ->join(array('a2'  => 'area')            , 'rm2.area_id = a2.id'            , array())
    ->join(array('s'   => 'staff')           , 't.sales_id = s.id'              , array())
    ->group(array('i.distributor_id', 't.store', 't.sales_id'));
    $get = array(
        'd_id'      => 'd.id',
        'd_name'    => 'd.title',
        'd_area'    => 'a.name',
        'sale_id'   => 'AAA.sale_id',
        'sale_code' => 'AAA.sale_code',
        'sale_name' => 'AAA.sale_name',
        'st_id'     => 'AAA.store_id',
        'st_name'   => 'AAA.store_name',
        'st_area'   => 'AAA.areas_name',
        'sellout' => new Zend_Db_Expr("COALESCE(AAA.sellout, 0)")
    );
    $select = $db->select()
    ->from(array('d'=> WAREHOUSE_DB.'.distributor'), $get)
    ->join(array('rm' => 'regional_market'), 'd.region = rm.id', array())
    ->join(array('a'  => 'area'), 'rm.area_id = a.id', array())
    ->joinLeft(array('AAA' => $sub_select), 'd.id = AAA.distributor_id', array())
    ->where('d.rank IN (7,8)')
    ->order(array('d.id ASC','a.name ASC','AAA.sale_id ASC'));
//echo $select; die;
    $result = $db->fetchAll($select);
    return $result;
}
// Inventory Report : Remaining Stock of Distributor
public function getInventoryRemain(){
    $db = Zend_Registry::get('db');
    $sub_select = $db->select()
    ->from(array('ts'=> 'timing_sale'), array('ts.imei'))
    ->where('ts.imei = i.imei_sn');
    $get = array(
        'd_id'      => 'd.id',
        'd_name'    => 'd.title',
        'd_area'    => 'a.name',
        'remain' => new Zend_Db_Expr("COUNT(i.imei_sn)")
    );
    $select = $db->select()
    ->from(array('d'=> WAREHOUSE_DB.'.distributor'), $get)
    ->joinLeft(array('i' => WAREHOUSE_DB.'.imei'), "d.id = i.distributor_id AND i.imei_sn NOT IN (".$sub_select.")", array())
    ->joinLeft(array('rm' => 'regional_market') , 'd.region = rm.id'    , array())
    ->joinLeft(array('a'  => 'area')            , 'rm.area_id = a.id'   , array())
    ->where('d.rank IN (7,8)')
    ->group('d.id')
    ->order(array('d.id ASC','a.name ASC'));
//echo $select; die;
    $result = $db->fetchAll($select);
    return $result;
}
// Inventory Report : Distributor_Chain
public function getDistributorChain(){
    $db = Zend_Registry::get('db');
    $sub_select = $db->select()
    ->from(array('ts'=> 'timing_sale'), array('ts.imei'))
    ->where('ts.imei = i.imei_sn');
    $get = array(
        'd_id'      => 'd.id',
        'd_name'    => 'd.title',
        'd_area'    => 'a1.name',
        'st_id'     => 'st.id',
        'st_name'   => 'st.name',
        'st_area'   => 'a2.name',
        'sale_code' => 's.code',
        'sale_name' => new Zend_Db_Expr("CONCAT(s.firstname, ' ',s.lastname)"),
        'group_name'=> 'g.name'
    );
    $select = $db->select()
    ->from(array('d'=> WAREHOUSE_DB.'.distributor'), $get)
    ->joinLeft(array('st' => 'store')       , 'd.id = st.d_id'                          , array())
    ->joinLeft(array('o' => 'org')          , 'st.org_dealer = o.org_id AND o.store_type_id = 2', array())
    ->joinLeft(array('ss' => 'store_staff') , 'st.id = ss.store_id AND ss.is_leader = 1', array())
    ->joinLeft(array('s'  => 'staff')       , 'ss.staff_id = s.id'                      , array())
    ->joinLeft(array('g'  => 'group')       , 's.group_id = g.id'                       , array())
    ->joinLeft(array('rm1' => 'regional_market') , 'd.region = rm1.id'                  , array())
    ->joinLeft(array('a1'  => 'area')            , 'rm1.area_id = a1.id'                , array())
    ->joinLeft(array('rm2' => 'regional_market') , 'st.regional_market = rm2.id'        , array())
    ->joinLeft(array('a2'  => 'area')            , 'rm2.area_id = a2.id'                , array())
    ->where('d.rank IN (7,8)')
    ->order('d.id ASC');
//echo $select; die;
    $result = $db->fetchAll($select);
    return $result;
}
// Management Dept Report : Sale Performance
public function getSalePerformance($params){
// change date format
    $d = explode('/', $params['from']);
    $from = $d[2].'-'.$d[1].'-'.$d[0];
    $d = explode('/', $params['to']);
    $to = $d[2].'-'.$d[1].'-'.$d[0];
    $db = Zend_Registry::get('db');
    $get = array(
        'st_area'       => 'a.name',
        'staff_id'      => 's.id',
        'staff_name'    => new Zend_Db_Expr("CONCAT(s.firstname, ' ',s.lastname)"),
        'group_name'    => 'g.name',
        'staff_join'    => 's.joined_at',
        'staff_created' => 's.created_at',
        'sellout_all'   => new Zend_Db_Expr("COUNT(ts1.imei)"),
        'sellout_hero'  => new Zend_Db_Expr("COUNT(ts2.imei)")
    );
    $select = $db->select()
    ->from(array('t'=> 'timing'), $get)
    ->join(array('st' => 'store')           , 't.store = st.id'                             , array())
    ->join(array('rm' => 'regional_market') , 'st.regional_market = rm.id'                  , array())
    ->join(array('a'  => 'area')            , 'rm.area_id = a.id'                           , array())
    ->join(array('s'  => 'staff')           , 't.sales_id = s.id'                           , array())
    ->join(array('g'  => 'group')           , 's.group_id = g.id'                           , array())
    ->joinLeft(array('ts1' => 'timing_sale'), 't.id = ts1.timing_id'                        , array())
    ->joinLeft(array('ts2' => 'timing_sale'), 'ts1.imei = ts2.imei AND ts2.product_id = 17' , array())
    ->where('t.created_at >= ?', $from." 00:00:00")
    ->where('t.created_at <= ?', $to." 23:59:59")
    ->group(array('s.id','a.id'))
    ->order(array('a.name ASC','sellout_hero DESC'));
//echo $select; die;
    $result = $db->fetchAll($select);
    return $result;
}
// Management Dept Report : Monday Report
public function getMondayReport($params){
// change date format
    $d = explode('/', $params['from']);
    $from = $d[2].'-'.$d[1].'-'.$d[0];
    $d = explode('/', $params['to']);
    $to = $d[2].'-'.$d[1].'-'.$d[0];
    $db = Zend_Registry::get('db');
// Qry01 : Count All PC By Store Staff
    $sub_select_01 = $db->select()
    ->from(array('s1'=> 'staff'), array('cnt_pc' => new Zend_Db_Expr("COUNT(DISTINCT s1.id)")))
    ->join(array('sst1' => 'store_staff')   , 's1.id = sst1.staff_id'       , array())
    ->join(array('st1' => 'store')          , 'sst1.store_id = st1.id'      , array())
    ->join(array('rm1' => 'regional_market'), 'st1.regional_market = rm1.id', array())
    ->where('rm1.area_id = a.id')
    ->where('s1.group_id = 4')
    ->where('st1.del IS NULL')
    ->where('st1.rank <> 3');

// Qry02 : Count Active Staff
    $sub_select_02 = $db->select()
    ->from(array('st2'=> 'store'), array('cnt_staff' => new Zend_Db_Expr("COUNT(DISTINCT s2.id)")))
    ->join(array('t2' => 'timing'),
        "   st2.id = t2.store
        AND t2.staff_id <> t2.sales_id
        AND t2.created_at >= '".$from." 00:00:00'
        AND t2.created_at <= '".$to." 23:59:59'
        "
        , array())
    ->join(array('ts2' => 'timing_sale')    , 't2.id = ts2.timing_id'       , array())
    ->join(array('s2'  => 'staff')          , 't2.staff_id = s2.id'         , array())
    ->join(array('rm2' => 'regional_market'), 'st2.regional_market = rm2.id', array())
    ->where('rm2.area_id = a.id')
    ->where('st2.del IS NULL')
    ->where('st2.rank <> 3');
// Qry03 : Count Sellout By PC
    $sub_select_03 = $db->select()
    ->from(array('st3'=> 'store'), array('sellout' => new Zend_Db_Expr("COUNT(ts3.imei)")))
    ->join(array('t3' => 'timing'),
        "   st3.id = t3.store
        AND t3.created_at >= '".$from." 00:00:00'
        AND t3.created_at <= '".$to." 23:59:59'
        "
        , array())
    ->join(array('ts3' => 'timing_sale')    , 't3.id = ts3.timing_id'                   , array())
    ->join(array('s3'  => 'staff')          , 't3.staff_id = s3.id AND s3.group_id = 4 ', array())
    ->join(array('rm3' => 'regional_market'), 'st3.regional_market = rm3.id'            , array())
    ->where('rm3.area_id = a.id')
    ->where('st3.del IS NULL')
    ->where('st3.rank <> 3');
// Qry04 : Count Sellout ALL
    $sub_select_04 = $db->select()
    ->from(array('st4'=> 'store'), array('sellout' => new Zend_Db_Expr("COUNT(ts4.imei)")))
    ->join(array('t4' => 'timing'),
        "   st4.id = t4.store
        AND t4.created_at >= '".$from." 00:00:00'
        AND t4.created_at <= '".$to." 23:59:59'
        "
        , array())
    ->join(array('ts4' => 'timing_sale')    , 't4.id = ts4.timing_id'       , array())
    ->join(array('rm4' => 'regional_market'), 'st4.regional_market = rm4.id', array())
    ->where('rm4.area_id = a.id')
    ->where('st4.del IS NULL')
    ->where('st4.rank <> 3');
// Qry05 : Count Store With Retailer
    $sub_select_05 = $db->select()
    ->from(array('st5'=> 'store'), array('cnt_store' => new Zend_Db_Expr("COUNT(st5.id)")))
    ->join(array('d5' => WAREHOUSE_DB.'.distributor')   , 'st5.d_id = d5.id'            , array())
    ->join(array('rm5' => 'regional_market')            , 'st5.regional_market = rm5.id', array())
    ->where('rm5.area_id = a.id')
    ->where('st5.del IS NULL')
    ->where('st5.rank <> 3');
// Qry06 : Count Store Active
    $sub_select_06 = $db->select()
    ->from(array('st6'=> 'store'), array('cnt_store' => new Zend_Db_Expr("COUNT(DISTINCT st6.id)")))
    ->join(array('t6' => 'timing'),
        "   st6.id = t6.store
        AND t6.created_at >= '".$from." 00:00:00'
        AND t6.created_at <= '".$to." 23:59:59'
        "
        , array())
    ->join(array('ts6' => 'timing_sale')    , 't6.id = ts6.timing_id'       , array())
    ->join(array('rm6' => 'regional_market'), 'st6.regional_market = rm6.id', array())
    ->where('rm6.area_id = a.id')
    ->where('st6.del IS NULL')
    ->where('st6.rank <> 3');
// Qry07 [sub] : Count PC Active AND Habe KPI >= 50
    $semi_sub_select_07 = $db->select()
    ->from(array('st7'=> 'store'), array(
        's7_id'     => 's7.id',
        'gkl7_kpi'  => new Zend_Db_Expr("SUM(gkl7.kpi)"),
        'rm7.area_id'
    ))
    ->join(array('t7'   => 'timing'),
        "   st7.id = t7.store
        AND t7.staff_id <> t7.sales_id
        AND t7.created_at >= '".$from." 00:00:00'
        AND t7.created_at <= '".$to." 23:59:59'
        "
        , array())
    ->join(array('ts7'  => 'timing_sale')       , 't7.id = ts7.timing_id'       , array())
    ->join(array('gkl7' => 'good_kpi_log'),
        "   ts7.product_id = gkl7.good_id
        AND ts7.model_id = gkl7.color_id
        AND t7.created_at >= gkl7.from_date
        AND t7.created_at <= gkl7.to_date
        "
        , array())
    ->join(array('rm7'  => 'regional_market')   , 'st7.regional_market = rm7.id', array())
    ->join(array('s7'   => 'staff')             , 't7.staff_id = s7.id'         , array())
    ->where('st7.del IS NULL')
    ->where('st7.rank <> 3')
    ->group(array('s7.id', 'rm7.area_id'))
    ->having('gkl7_kpi >= 50');
    $sub_select_07 = $db->select()
    ->from(array('AAA'=> new Zend_Db_Expr("(".$semi_sub_select_07.")")), array('cnt_pc' => new Zend_Db_Expr("COUNT(AAA.s7_id)")))
    ->where('AAA.area_id = a.id');

// Qry08 [sub] : Count PC Active AND Habe KPI < 50
    $semi_sub_select_08 = $db->select()
    ->from(array('st8'=> 'store'), array(
        's8_id'     => 's8.id',
        'gkl8_kpi'  => new Zend_Db_Expr("SUM(gkl8.kpi)"),
        'rm8.area_id'
    ))
    ->join(array('t8'   => 'timing'),
        "   st8.id = t8.store
        AND t8.staff_id <> t8.sales_id
        AND t8.created_at >= '".$from." 00:00:00'
        AND t8.created_at <= '".$to." 23:59:59'
        "
        , array())
    ->join(array('ts8'  => 'timing_sale')       , 't8.id = ts8.timing_id'       , array())
    ->join(array('gkl8' => 'good_kpi_log'),
        "   ts8.product_id = gkl8.good_id
        AND ts8.model_id = gkl8.color_id
        AND t8.created_at >= gkl8.from_date
        AND t8.created_at <= gkl8.to_date
        "
        , array())
    ->join(array('rm8'  => 'regional_market')   , 'st8.regional_market = rm8.id', array())
    ->join(array('s8'   => 'staff')             , 't8.staff_id = s8.id'         , array())
    ->where('st8.del IS NULL')
    ->where('st8.rank <> 3')
    ->group(array('s8.id', 'rm8.area_id'))
    ->having('gkl8_kpi < 50');
    $sub_select_08 = $db->select()
    ->from(array('AAA'=> new Zend_Db_Expr("(".$semi_sub_select_08.")")), array('cnt_pc' => new Zend_Db_Expr("COUNT(AAA.s8_id)")))
    ->where('AAA.area_id = a.id');
    $get = array(
        'area_name'         => 'a.name',
        'cnt_pc_all'        => new Zend_Db_Expr("(".$sub_select_01.")"),
        'cnt_staff_active'  => new Zend_Db_Expr("(".$sub_select_02.")"),
        'cnt_sellout_pc'    => new Zend_Db_Expr("(".$sub_select_03.")"),
        'cnt_sellout_all'   => new Zend_Db_Expr("(".$sub_select_04.")"),
        'cnt_store_retailer'=> new Zend_Db_Expr("(".$sub_select_05.")"),
        'cnt_store_active'  => new Zend_Db_Expr("(".$sub_select_06.")"),
        'pckpi_more_50'     => new Zend_Db_Expr("(".$sub_select_07.")"),
        'pckpi_less_50'     => new Zend_Db_Expr("(".$sub_select_08.")"),
    );
    $select = $db->select()
    ->from(array('a'=> 'area'), $get)
    ->where('a.id NOT IN (48,49,72)')
    ->order('a.name ASC');
//echo $select; die;
    $result = $db->fetchAll($select);
    return $result;
}
// Management Dept Report : Friday Report
public function getFridayReport($params){
// change date format
    $d = explode('/', $params['from']);
    $from = $d[2].'-'.$d[1].'-'.$d[0];
    $d = explode('/', $params['to']);
    $to = $d[2].'-'.$d[1].'-'.$d[0];
    $db = Zend_Registry::get('db');
// Qry01 : Count Store Have Sellout >= 2
    $semi_sub_select_01 = $db->select()
    ->from(array('st1'=> 'store'), array(
        'store_name'    => 'st1.name',
        'sellout'       => new Zend_Db_Expr("COUNT(ts1.imei)"),
        'rm1.area_id'
    ))
    ->join(array('t1' => 'timing')  ,
        "   st1.id = t1.store
        AND t1.created_at >= '".$from." 00:00:00'
        AND t1.created_at <= '".$to." 23:59:59'
        "
        , array())
    ->join(array('ts1' => 'timing_sale')    , 't1.id = ts1.timing_id'       , array())
    ->join(array('rm1' => 'regional_market'), 'st1.regional_market = rm1.id', array())
//->where('st1.del IS NULL')
//->where('st1.rank <> 3')
    ->group('st1.id')
    ->having('sellout >= 2');
    $sub_select_01 = $db->select()
    ->from(array('AAA'=> new Zend_Db_Expr("(".$semi_sub_select_01.")")), array('cnt_store' => new Zend_Db_Expr("COUNT(AAA.store_name)")))
    ->where('AAA.area_id = a.id');

// Qry02 : Count Store With Retailer [All]
    $sub_select_02 = $db->select()
    ->from(array('st2'=> 'store'), array('cnt_store' => new Zend_Db_Expr("COUNT(st2.id)")))
    ->join(array('d2' => WAREHOUSE_DB.'.distributor')   , 'st2.d_id = d2.id'            , array())
    ->join(array('rm2' => 'regional_market')            , 'st2.regional_market = rm2.id', array())
    ->where('rm2.area_id = a.id');
//->where('st2.del IS NULL')
//->where('st2.rank <> 3');
// Qry03 : Count PC [ALL]
    $sub_select_03 = $db->select()
    ->from(array('st3'=> 'store'), array('cnt_pc' => new Zend_Db_Expr("COUNT(DISTINCT s3.id)")))
    ->join(array('ss3' => 'store_staff')    , 'st3.id = ss3.store_id AND ss3.is_leader = 0' , array())
    ->join(array('s3'  => 'staff')          , 'ss3.staff_id = s3.id AND s3.off_date IS NULL AND s3.pc_stand_by = 0', array())
    ->join(array('rm3' => 'regional_market'), 'st3.regional_market = rm3.id'                , array())
    ->where('rm3.area_id = a.id');
//->where('st3.del IS NULL')
//->where('st3.rank <> 3');

// Qry04 : Count Sale
    $sub_select_04 = $db->select()
//->from(array('st4'=> 'store'), array('cnt_sale' => new Zend_Db_Expr("( COUNT(DISTINCT s4.id) + COUNT(DISTINCT sl4.staff_id) )")))
    ->from(array('st4'=> 'store'), array('cnt_sale' => new Zend_Db_Expr("COUNT(DISTINCT s4.id)")))
    ->join(array('ss4' => 'store_staff')    , 'st4.id = ss4.store_id AND ss4.is_leader = 1' , array())
    ->join(array('s4'  => 'staff')          , 'ss4.staff_id = s4.id AND s4.off_date IS NULL AND s4.group_id = 9', array())
    ->join(array('rm4' => 'regional_market'), 'st4.regional_market = rm4.id'                , array())
//->joinLeft(array('sl4' => 'store_leader'), 'st4.id = sl4.store_id', array())
    ->where('rm4.area_id = a.id');
//->where('st4.del IS NULL')
//->where('st4.rank <> 3');

// Qry05 : Count Sellout [ALL]
    $sub_select_05 = $db->select()
    ->from(array('st5'=> 'store'), array('sellout' => new Zend_Db_Expr("COUNT(ts5.imei)")))
    ->join(array('t5' => 'timing')  ,
        "   st5.id = t5.store
        AND t5.created_at >= '".$from." 00:00:00'
        AND t5.created_at <= '".$to." 23:59:59'
        "
        , array())
    ->join(array('ts5' => 'timing_sale')    , 't5.id = ts5.timing_id'       , array())
    ->join(array('rm5' => 'regional_market'), 'st5.regional_market = rm5.id', array())
    ->join(array('s5'  => 'staff')          , 't5.staff_id = s5.id'         , array())
    ->where('rm5.area_id = a.id');
//->where('st5.del IS NULL')
//->where('st5.rank <> 3');
// Qry06 : Count Sellout [No AM]
    $sub_select_06 = $db->select()
    ->from(array('st6'=> 'store'), array('sellout_sale' => new Zend_Db_Expr("COUNT(ts6.imei)")))
    ->join(array('t6' => 'timing')  ,
        "   st6.id = t6.store
        AND t6.created_at >= '".$from." 00:00:00'
        AND t6.created_at <= '".$to." 23:59:59'
        "
        , array())
    ->join(array('ts6' => 'timing_sale')    , 't6.id = ts6.timing_id'                       , array())
    ->join(array('rm6' => 'regional_market'), 'st6.regional_market = rm6.id'                , array())
    ->join(array('s6'  => 'staff')          , 't6.sales_id = s6.id AND s6.group_id <> 27'   , array())
    ->where('rm6.area_id = a.id');
//->where('st6.del IS NULL')
//->where('st6.rank <> 3');
// Qry07 : Count AM
    $sub_select_07 = $db->select()
    ->from(array('st7'=> 'store'), array('cnt_sale_no_am' => new Zend_Db_Expr("( COUNT(DISTINCT s7.id) + COUNT(DISTINCT sl7.staff_id) )")))
    ->join(array('ss7' => 'store_staff'), 'st7.id = ss7.store_id AND ss7.is_leader = 1' , array())
    ->join(array('s7'  => 'staff'), 'ss7.staff_id = s7.id AND s7.off_date IS NULL AND s7.group_id = 27', array())
    ->join(array('rm7' => 'regional_market'), 'st7.regional_market = rm7.id', array())
    ->joinLeft(array('sl7' => 'store_leader'), 'st7.id = sl7.store_id', array())
    ->where('rm7.area_id = a.id');
//->where('st7.del IS NULL')
//->where('st7.rank <> 3');
// Qry08 : Count Store With Retailer [No AM]

    $semi_sub_select_08 = $db->select()
    ->from(array('ss8' => 'store_staff'), array('store_id' => 'ss8.store_id'))
    ->join(array('s8'  => 'staff'), 'ss8.staff_id = s8.id AND s8.group_id = 27', array())
    ->where('ss8.is_leader = 1')
    ->where('ss8.store_id = st8.id');
    $sub_select_08 = $db->select()
    ->from(array('st8' => 'store'), array('cnt_store_no_am' => new Zend_Db_Expr("COUNT(st8.id)")))
    ->join(array('d8'  => WAREHOUSE_DB.'.distributor')  , 'st8.d_id = d8.id'                            , array())
    ->join(array('rm8' => 'regional_market')            , 'st8.regional_market = rm8.id'                , array())
    ->where('rm8.area_id = a.id')
    ->where('st8.id NOT IN (?)', $semi_sub_select_08);
//->where('st8.del IS NULL')
//->where('st8.rank <> 3');
// Qry09 : Count Store Have Sellout >= 2 [No AM]
    $semi_sub_select_09 = $db->select()
    ->from(array('st9'=> 'store'), array(
        'store_name'    => 'st9.name',
        'sellout_no_am' => new Zend_Db_Expr("COUNT(ts9.imei)"),
        'rm9.area_id'
    ))
    ->join(array('t9' => 'timing')  ,
        "   st9.id = t9.store
        AND t9.created_at >= '".$from." 00:00:00'
        AND t9.created_at <= '".$to." 23:59:59'
        "
        , array())
    ->join(array('ts9' => 'timing_sale')    , 't9.id = ts9.timing_id'       , array())
    ->join(array('rm9' => 'regional_market'), 'st9.regional_market = rm9.id', array())
    ->join(array('s9' => 'staff'), 't9.sales_id = s9.id AND s9.group_id <> 27', array())
//->where('st9.del IS NULL')
//->where('st9.rank <> 3')
    ->group('st9.id')
    ->having('sellout_no_am >= 2');
    $sub_select_09 = $db->select()
    ->from(array('AAA'=> new Zend_Db_Expr("(".$semi_sub_select_09.")")), array('cnt_store_no_am' => new Zend_Db_Expr("COUNT(AAA.store_name)")))
    ->where('AAA.area_id = a.id');
// Qry10 : Count PC Stand By
    $sub_select_10 = $db->select()
    ->from(array('st10'=> 'store'), array('cnt_pc' => new Zend_Db_Expr("COUNT(DISTINCT s10.id)")))
    ->join(array('ss10'=> 'store_staff')        , 'st10.id = ss10.store_id AND ss10.is_leader = 0'  , array())
    ->join(array('s10'  => 'staff')             , 'ss10.staff_id = s10.id AND s10.off_date IS NULL AND s10.pc_stand_by = 1', array())
    ->join(array('rm10' => 'regional_market')   , 'st10.regional_market = rm10.id'                  , array())
    ->where('rm10.area_id = a.id');
//->where('st10.del IS NULL')
//->where('st10.rank <> 3');
// Qry11 : Count Sale Event
    $sub_select_11 = $db->select()
    ->from(array('s11'  => 'staff'), array('cnt_pc' => new Zend_Db_Expr("COUNT(s11.id)")))
    ->join(array('rm11' => 'regional_market'), 's11.regional_market = rm11.id', array())
    ->where('s11.off_date IS NULL')
    ->where('s11.group_id = 31')
    ->where('rm11.area_id = a.id');
// Qry12 : Count ASM
    $sub_select_12 = $db->select()
    ->from(array('st12'=> 'store'), array('cnt_sale_asm' => new Zend_Db_Expr("COUNT(DISTINCT s12.id)")))
    ->join(array('ss12' => 'store_staff')       , 'st12.id = ss12.store_id AND ss12.is_leader = 1'  , array())
    ->join(array('s12'  => 'staff')             , 'ss12.staff_id = s12.id AND s12.off_date IS NULL AND s12.group_id = 5', array())
    ->join(array('rm12' => 'regional_market')   , 'st12.regional_market = rm12.id'                  , array())
    ->where('rm12.area_id = a.id');
//->where('st12.del IS NULL')
//->where('st12.rank <> 3');
// Qry13 : Count ASM Stand By
    $sub_select_13 = $db->select()
    ->from(array('st13'=> 'store'), array('cnt_sale_asm_standby' => new Zend_Db_Expr("COUNT(DISTINCT s13.id)")))
    ->join(array('ss13' => 'store_staff')       , 'st13.id = ss13.store_id AND ss13.is_leader = 1'  , array())
    ->join(array('s13'  => 'staff')             , 'ss13.staff_id = s13.id AND s13.off_date IS NULL AND s13.group_id = 16', array())
    ->join(array('rm13' => 'regional_market')   , 'st13.regional_market = rm13.id'                  , array())
    ->where('rm13.area_id = a.id');
//->where('st13.del IS NULL')
//->where('st13.rank <> 3');
// Qry14: Count RM
    $sub_select_14 = $db->select()
    ->from(array('st14' => 'store'), array('cnt_sale_rm' => new Zend_Db_Expr("COUNT(DISTINCT s14.id)")))
    ->join(array('ss14' => 'store_staff')       , 'st14.id = ss14.store_id AND ss14.is_leader = 1'  , array())
    ->join(array('s14'  => 'staff')             , 'ss14.staff_id = s14.id AND s14.off_date IS NULL AND s14.group_id = 28', array())
    ->join(array('rm14' => 'regional_market')   , 'st14.regional_market = rm14.id'                  , array())
    ->where('rm14.area_id = a.id');
//->where('st14.del IS NULL')
//->where('st14.rank <> 3');
// Qry15 : Count Sale Leader
    $sub_select_15 = $db->select()
    ->from(array('st15' => 'store'), array('cnt_sale_leader' => new Zend_Db_Expr("COUNT(DISTINCT s15.id)")))
    ->join(array('sl15' => 'store_leader')      , 'st15.id = sl15.store_id'                         , array())
    ->join(array('s15'  => 'staff')             , 'sl15.staff_id = s15.id AND s15.off_date IS NULL' , array())
    ->join(array('rm15' => 'regional_market')   , 'st15.regional_market = rm15.id'                  , array())
    ->where('rm15.area_id = a.id');
//->where('st15.del IS NULL')
//->where('st15.rank <> 3');
    $get = array(
        'area_id'                   => 'a.id',
        'area_name'                 => 'a.name',
        'cnt_store_more_2'          => new Zend_Db_Expr("(".$sub_select_01.")"),
        'cnt_store_retailer'        => new Zend_Db_Expr("(".$sub_select_02.")"),
        'cnt_pc_all'                => new Zend_Db_Expr("(".$sub_select_03.")"),
        'cnt_sale_all'              => new Zend_Db_Expr("(".$sub_select_04.")"),
        'cnt_sellout_all'           => new Zend_Db_Expr("(".$sub_select_05.")"),
        'cnt_sellout_no_am'         => new Zend_Db_Expr("(".$sub_select_06.")"),
        'cnt_sale_am'               => new Zend_Db_Expr("(".$sub_select_07.")"),
        'cnt_store_retailer_no_am'  => new Zend_Db_Expr("(".$sub_select_08.")"),
        'cnt_store_more_2_no_am'    => new Zend_Db_Expr("(".$sub_select_09.")"),
        'cnt_pc_stand_by'           => new Zend_Db_Expr("(".$sub_select_10.")"),
        'cnt_sale_event'            => new Zend_Db_Expr("(".$sub_select_11.")"),
        'cnt_sale_asm'              => new Zend_Db_Expr("(".$sub_select_12.")"),
        'cnt_sale_asm_standby'      => new Zend_Db_Expr("(".$sub_select_13.")"),
        'cnt_sale_rm'               => new Zend_Db_Expr("(".$sub_select_14.")"),
        'cnt_sale_leader'           => new Zend_Db_Expr("(".$sub_select_15.")"),
    );
    $select = $db->select()
    ->from(array('a'=> 'area'), $get)
    ->where('a.id NOT IN (48,49,72)')
    ->order('a.name ASC');
//echo $select; die;
    $result = $db->fetchAll($select);
    return $result;
}
public function getT3Report($params) {
// change date format
    $d = explode('/', $params['from']);
    $from = $d[2].'-'.$d[1].'-'.$d[0];
    $d = explode('/', $params['to']);
    $to = $d[2].'-'.$d[1].'-'.$d[0];
    $db = Zend_Registry::get('db');
// Qry01 : Count Store Have Retailer
    $sub_select_01 = $db->select()
    ->from(array('st1'=> 'store'), array('cnt_store' => new Zend_Db_Expr("COUNT(st1.id)")))
    ->join(array('d1' => WAREHOUSE_DB.'.distributor'), 'st1.d_id = d1.id', array())
    ->where('st1.district = rm2.id')
    ->where('st1.del IS NULL')
    ->where('st1.rank <> 3');
// Qry02 : Count Store Have Sellout >= 4
    $semi_sub_select_02 = $db->select()
    ->from(array('st2'=> 'store'), array(
        'store_name'    => 'st2.name',
        'sellout'       => new Zend_Db_Expr("COUNT(ts2.imei)"),
        'st2.district'
    ))
    ->join(array('t2' => 'timing')  ,
        "   st2.id = t2.store
        AND t2.created_at >= '".$from." 00:00:00'
        AND t2.created_at <= '".$to." 23:59:59'
        "
        , array())
    ->join(array('ts2' => 'timing_sale'), 't2.id = ts2.timing_id', array())
    ->where('st2.del IS NULL')
    ->where('st2.rank <> 3')
    ->group('st2.id')
    ->having('sellout >= 4');
    $sub_select_02 = $db->select()
    ->from(array('AAA'=> new Zend_Db_Expr("(".$semi_sub_select_02.")")), array('cnt_store' => new Zend_Db_Expr("COUNT(AAA.store_name)")))
    ->where('AAA.district = rm2.id');
// Qry03 : Count Sellout [ALL]
    $sub_select_03 = $db->select()
    ->from(array('st3'=> 'store'), array('sellout' => new Zend_Db_Expr("COUNT(ts3.imei)")))
    ->join(array('t3' => 'timing')  ,
        "   st3.id = t3.store
        AND t3.created_at >= '".$from." 00:00:00'
        AND t3.created_at <= '".$to." 23:59:59'
        "
        , array())
    ->join(array('ts3' => 'timing_sale'), 't3.id = ts3.timing_id', array())
    ->where('st3.district = rm2.id')
    ->where('st3.del IS NULL')
    ->where('st3.rank <> 3');
    $get = array(
        'area_name'         => 'a.name',
        'province_name'     => 'rm.name',
        'district_name'     => 'rm2.name',
        'cnt_store'         => new Zend_Db_Expr("(".$sub_select_01.")"),
        'cnt_store_more_4'  => new Zend_Db_Expr("(".$sub_select_02.")"),
        'sellout_all'       => new Zend_Db_Expr("(".$sub_select_03.")"),
    );
    $select = $db->select()
    ->from(array('a'    => 'area'), $get)
    ->join(array('rm'   => 'regional_market'), 'a.id = rm.area_id'  , array())
    ->join(array('rm2'  => 'regional_market'), 'rm.id = rm2.parent' , array())
    ->order(array('a.name ASC', 'rm.name ASC', 'rm2.name ASC'));
//echo $select; die;
    $result = $db->fetchAll($select);
    return $result;
}
// Weekly Report : PC By Model Daily
public function getPcByModel($params) {
// change date format
    $d = explode('/', $params['from']);
    $from = $d[2].'-'.$d[1].'-'.$d[0];
    $d = explode('/', $params['to']);
    $to = $d[2].'-'.$d[1].'-'.$d[0];
    $period_loop = ( (strtotime($to) - strtotime($from)) / (24*60*60) ) + 1;
    $db = Zend_Registry::get('db');
    $get_01 = array(
        'area_id'       => 'a.id',
        'area_name'     => 'a.name',
        'staff_id'      => 's.id',
        'staff_code'    => 's.code',
        'staff_name'    => new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)"),
        'staff_group'   => 'g.name',
        'st_id'         => 'st.id',
        'st_name'       => 'st.name',
        'st_type'       => 'o.org_name',
        'd_id'          => 'd.id',
        'd_name'        => 'd.title',
        'total_sellout' => new Zend_Db_Expr("COUNT(ts.imei)"),
    );
    if (is_array($params['product_id'])) { $product_id = implode(",", $params['product_id']); }
    else { $product_id = $params['product_id']; }
// Generate Column of Day
    $get_02 = array();
    for ($i=0;$i<$period_loop;$i++) {
        $day =  date('Y-m-d', strtotime("+".$i." Day", strtotime($from)));
        $get_02['sellout_'.$i] = "COUNT(
        CASE WHEN
        t.created_at >= '".$day." 00:00:00'
        AND t.created_at <= '".$day." 23:59:59'
        AND ts.product_id IN (".$product_id.")
        THEN ts.imei END)";
    }
    $get = $get_01 + $get_02;
    $select = $db->select()
    ->from(array('s' => 'staff'), $get)
    ->join(array('g' => 'group')            , 's.group_id = g.id'           , array())
    ->join(array('rm'=> 'regional_market')  , 's.regional_market = rm.id'   , array())
    ->join(array('a' => 'area')             , 'rm.area_id = a.id'           , array())
    ->join(array('t' => 'timing')           , 's.id = t.staff_id'           , array())
    ->join(array('ts'=> 'timing_sale')      , 't.id = ts.timing_id'         , array())
    ->join(array('st'=> 'store')            , 't.store = st.id'             , array())
    ->join(array('o' => 'org')              , 'st.org_dealer = o.org_id'    , array())
    ->joinLeft(array('d' => WAREHOUSE_DB.'.distributor'), 'st.d_id = d.id'  , array())
    ->where('s.group_id = ?', PGPB_ID)
//->where('ts.product_id IN (?)', $params['product_id'])
    ->where('t.created_at >= ?', $from." 00:00:00")
    ->where('t.created_at <= ?', $to." 23:59:59")
    ->group(array('s.id','st.id'))
    ->order(array('a.name ASC', 's.code ASC'));
// Filter Only More Than 10 Unit/Day
    for ($j=0;$j<$period_loop;$j++) {
        $day =  date('Y-m-d', strtotime("+".$j." Day", strtotime($from)));
        $select->orHaving("sellout_".$j." >= 10");
    }
// echo $select; die;

    $result = $db->fetchAll($select);
    return $result;
}
// Weekly Report : Sale Hero Product Sellout
public function getSaleHero($params) {
// change date format
    $d = explode('/', $params['from']);
    $from = $d[2].'-'.$d[1].'-'.$d[0];
    $d = explode('/', $params['to']);
    $to = $d[2].'-'.$d[1].'-'.$d[0];
    $db = Zend_Registry::get('db');
    $get = array(
        'area_id'       => 'a.id',
        'area_name'     => 'a.name',
        'staff_id'      => 's.id',
        'staff_code'    => 's.code',
        'staff_name'    => new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)"),
        'group_name'    => 'g.name',
        'hero_sellout'  => new Zend_Db_Expr("COUNT(ts.imei)"),
    );
    $hero_product = array(299);
    $select = $db->select()
    ->from(array('s' => 'staff'), $get)
    ->join(array('g' => 'group')            , 's.group_id = g.id'           , array())
    ->join(array('t' => 'timing')           , 's.id = t.sales_id'           , array())
    ->join(array('st'=> 'store')            , 't.store = st.id'             , array())
    ->join(array('rm'=> 'regional_market')  , 'st.regional_market = rm.id'  , array())
    ->join(array('a' => 'area')             , 'rm.area_id = a.id'           , array())
    ->join(array('ts'=> 'timing_sale')      , 't.id = ts.timing_id'         , array())
    ->where('ts.product_id IN (?)', $hero_product)
    ->where('t.created_at >= ?', $from." 00:00:00")
    ->where('t.created_at <= ?', $to." 23:59:59")
    ->group(array('s.id','a.id'))
    ->order(array('a.name ASC', 's.code ASC'));
// echo $select; die;
    $result = $db->fetchAll($select);
    return $result;
}
// Weekly Report : Asm Hero Product Sellout
public function getAsmHero($params) {
// change date format
    $d = explode('/', $params['from']);
    $from = $d[2].'-'.$d[1].'-'.$d[0];
    $d = explode('/', $params['to']);
    $to = $d[2].'-'.$d[1].'-'.$d[0];
    $db = Zend_Registry::get('db');
    $get = array(
        'area_id'       => 'a.id',
        'zone'          => new Zend_Db_Expr("(CASE WHEN asm.type = 2 THEN CONCAT('Area : ',a.name) ELSE CONCAT('Province : ',rm.name) END)"),
        'staff_id'      => 's.id',
        'staff_code'    => 's.code',
        'staff_name'    => new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)"),
        'group_name'    => 'g.name',
        'hero_sellout'  => new Zend_Db_Expr("COUNT(ts.imei)"),
    );
    $hero_product = array(299);
    $select = $db->select()
    ->from(array('a'   => 'area'), $get)
    ->join(array('rm'  => 'regional_market'), 'a.id = rm.area_id'   , array())
    ->join(array('asm' => 'asm')    , 'asm.area_id = (CASE WHEN asm.type = 2 THEN rm.area_id ELSE rm.id END)', array())
    ->join(array('s'   => 'staff')  , 'asm.staff_id = s.id'         , array())
    ->join(array('g'   => 'group')  , 's.group_id = g.id'           , array())
    ->join(array('st'  => 'store')  , 'rm.id = st.regional_market'  , array())
    ->join(array('t'   => 'timing') , 'st.id = t.store'             , array())
    ->join(array('ts'  => 'timing_sale'), 't.id = ts.timing_id'     , array())
    ->where('ts.product_id IN (?)', $hero_product)
    ->where('s.off_date IS NULL', 1)
    ->where('s.group_id IN (?)', array(ASM_ID, ASMSTANDBY_ID))
    ->where('t.created_at >= ?', $from." 00:00:00")
    ->where('t.created_at <= ?', $to." 23:59:59")
    ->group(array('asm.area_id','asm.staff_id'))
    ->order(array('zone ASC', 's.code ASC'));
// echo $select; die;
    $result = $db->fetchAll($select);
    return $result;
}
// Weekly Report : Hero Product Perforomance Report
public function getHeroPerformance($params) {
// change date format
    $d = explode('/', $params['from']);
    $from = $d[2].'-'.$d[1].'-'.$d[0];
    $d = explode('/', $params['to']);
    $to = $d[2].'-'.$d[1].'-'.$d[0];
// MTD Range
    $period_start = date('Y-m-01', strtotime($from));
//$period_end = date('Y-m-d', strtotime($from));
    $period_end = $to;
    $db = Zend_Registry::get('db');
// Qry01 : Count Store Have Sellout >= 2
    $semi_sub_select_01 = $db->select()
    ->from(array('st1'=> 'store'), array(
        'store_name'    => 'st1.name',
        'sellout'       => new Zend_Db_Expr("COUNT(ts1.imei)"),
        'rm1.area_id'
    ))
    ->join(array('t1' => 'timing')  ,
        "   st1.id = t1.store
        AND t1.created_at >= '".$from." 00:00:00'
        AND t1.created_at <= '".$to." 23:59:59'
        "
        , array())
    ->join(array('ts1' => 'timing_sale')    , 't1.id = ts1.timing_id'       , array())
    ->join(array('rm1' => 'regional_market'), 'st1.regional_market = rm1.id', array())
//->where('st1.del IS NULL')
//->where('st1.rank <> 3')
    ->group('st1.id')
    ->having('sellout >= 2');
    $sub_select_01 = $db->select()
    ->from(array('AAA'=> new Zend_Db_Expr("(".$semi_sub_select_01.")")), array('cnt_store' => new Zend_Db_Expr("COUNT(AAA.store_name)")))
    ->where('AAA.area_id = a.id');
/*
// Qry03 : Count PC [Check In]
$sub_select_03 = $db->select()
->from(array('s3' => 'staff'), array('cnt_pc' => new Zend_Db_Expr("COUNT(DISTINCT s3.id)")))
->join(array('rm3' => 'regional_market'), 's3.regional_market = rm3.id', array())
->join(array('pcl' => 'pc_check_in_log'),
"   s3.id = pcl.staff_id
AND pcl.action_id = 1
AND pcl.status <> 'N'
AND pcl.check_in >= '".$from." 00:00:00'
AND pcl.check_in <= '".$to." 23:59:59'
", array())
->where('rm3.area_id = a.id')
->where('s3.off_date IS NULL');
// Qry04 : Count Sale [Check In]
$sub_select_04 = $db->select()
->from(array('s4' => 'staff'), array('cnt_sale' => new Zend_Db_Expr("COUNT(DISTINCT s4.id)")))
->join(array('rm4' => 'regional_market'), 's4.regional_market = rm4.id', array())
->join(array('scl' => 'sales_check_in_log'),
"   s4.id = scl.staff_id
AND scl.action_id = 1
AND scl.status <> 'N'
AND scl.check_in >= '".$from." 00:00:00'
AND scl.check_in <= '".$to." 23:59:59'
", array())
->where('rm4.area_id = a.id')
->where('s4.group_id = ?', SALES_ID)
->where('s4.off_date IS NULL');
*/
// Qry03 : Count PC [ALL]
$sub_select_03 = $db->select()
->from(array('st3'=> 'store'), array('cnt_pc' => new Zend_Db_Expr("COUNT(DISTINCT s3.id)")))
->join(array('ss3' => 'store_staff')    , 'st3.id = ss3.store_id AND ss3.is_leader = 0' , array())
->join(array('s3'  => 'staff')          , 'ss3.staff_id = s3.id AND s3.off_date IS NULL', array())
->join(array('rm3' => 'regional_market'), 'st3.regional_market = rm3.id'                , array())
->where('rm3.area_id = a.id');
//->where('st3.del IS NULL')
//->where('st3.rank <> 3');

// Qry04 : Count Sale
$sub_select_04 = $db->select()
//->from(array('st4'=> 'store'), array('cnt_sale' => new Zend_Db_Expr("( COUNT(DISTINCT s4.id) + COUNT(DISTINCT sl4.staff_id) )")))
->from(array('st4'=> 'store'), array('cnt_sale' => new Zend_Db_Expr("COUNT(DISTINCT s4.id)")))
->join(array('ss4' => 'store_staff')    , 'st4.id = ss4.store_id AND ss4.is_leader = 1' , array())
->join(array('s4'  => 'staff')          , 'ss4.staff_id = s4.id AND s4.off_date IS NULL AND s4.group_id = 9', array())
->join(array('rm4' => 'regional_market'), 'st4.regional_market = rm4.id'                , array())
//->joinLeft(array('sl4' => 'store_leader'), 'st4.id = sl4.store_id', array())
->where('rm4.area_id = a.id');
//->where('st4.del IS NULL')
//->where('st4.rank <> 3');
$get = array(
    'grand_area' => new Zend_Db_Expr(
        "
        (
        CASE
        WHEN a.name LIKE 'BKK-E1%' THEN 'BKK East-1'
        WHEN a.name LIKE 'BKK-E2%' THEN 'BKK East-2'
        WHEN a.name LIKE 'BKK-E3%' THEN 'BKK East-3'
        WHEN a.name LIKE 'BKK-E4%' THEN 'BKK East-4'
        WHEN a.name LIKE 'BKK-E5%' THEN 'BKK East-5'
        WHEN a.name LIKE 'BKK-W1%' THEN 'BKK West-1'
        WHEN a.name LIKE 'BKK-W2%' THEN 'BKK West-2'
        WHEN a.name LIKE 'BKK-W3%' THEN 'BKK West-3'
        ELSE a.name
        END
        )
        "
    ),
    'area_name' => 'a.name',
    'sellout_hero_01'  => new Zend_Db_Expr(
        "COUNT(CASE WHEN t.created_at >= '".$from." 00:00:00' AND t.created_at <= '".$to." 23:59:59'
AND ts.product_id = 299 THEN ts.imei END)"),    // F5
    'sellout_hero_02'  => new Zend_Db_Expr(
        "COUNT(CASE WHEN t.created_at >= '".$from." 00:00:00' AND t.created_at <= '".$to." 23:59:59'
AND ts.product_id = 300 THEN ts.imei END)"),    // F5 Youth
    'sellout_hero_03'  => new Zend_Db_Expr(
        "COUNT(CASE WHEN t.created_at >= '".$from." 00:00:00' AND t.created_at <= '".$to." 23:59:59'
AND ts.product_id = 301 THEN ts.imei END)"),    // F5 6GB
    'sellout_all'      => new Zend_Db_Expr(
        "COUNT(CASE WHEN t.created_at >= '".$from." 00:00:00' AND t.created_at <= '".$to." 23:59:59' THEN ts.imei END)"),
    'sellout_mtd'      => new Zend_Db_Expr("COUNT(ts.imei)"),
    'cnt_store_more_2' => new Zend_Db_Expr("(".$sub_select_01.")"),
    'cnt_pc'           => new Zend_Db_Expr("(".$sub_select_03.")"),
    'cnt_sale'         => new Zend_Db_Expr("(".$sub_select_04.")"),
);
$select = $db->select()
->from(array('st' => 'store'), $get)
->join(array('rm' => 'regional_market') , 'st.regional_market = rm.id'  , array())
->join(array('a'  => 'area')            , 'rm.area_id = a.id'           , array())
->joinLeft(array('t'  => 'timing')      ,
    "   st.id = t.store
    AND t.created_at >= '".$period_start." 00:00:00'
    AND t.created_at <= '".$period_end." 23:59:59'
    ", array())
->joinLeft(array('ts' => 'timing_sale') , 't.id = ts.timing_id'         , array())
->where('a.id NOT IN (48,49,72)', 1)
->group('a.id')
->order(array('grand_area ASC', 'a.name ASC'));
//echo $select; die;
$result = $db->fetchAll($select);
return $result;
}
// Sale Tool : Stock Shop Report
public function getStockShop($page, $limit, &$total, $params) {
// change date format
    $d = explode('/', $params['from']);
    $from = $d[2].'-'.$d[1].'-'.$d[0];
    $d = explode('/', $params['to']);
    $to = $d[2].'-'.$d[1].'-'.$d[0];
    $db = Zend_Registry::get('db');
    $get = array();
    if ( isset($params['get_total_sales']) and $params['get_total_sales'] == 1 ) {
        $get_01['st_id'] = 'st.id';
    } else {
        $get_01['st_id'] = new Zend_Db_Expr('SQL_CALC_FOUND_ROWS st.id');
    }
    $get_02 = array(
        'st_id'         => 'DISTINCT st.id',
        'st_name'       => 'st.name',
        'st_area'       => 'a.name',
        'st_province'   => 'rm.name',
        'st_district'   => 'rm2.name',
        'sale_name'     => new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)"),
        'sale_group'    => 'g.name',
        'st_stock'      => new Zend_Db_Expr("COUNT(CASE WHEN ts.imei IS NULL THEN i.imei_sn END)"),
        'st_sellout'    => new Zend_Db_Expr("COUNT(CASE WHEN ts.imei IS NOT NULL THEN ts.imei END)")
    );
    $get = $get_01 + $get_02;
// get imei that already timing sale
/*
$sub_select = $db->select()
->from(array('ts' => 'timing_sale'), array('imei' => 'ts.imei'))
->where('ts.imei = i.imei_sn');*/

// main select
$select = $db->select()
->from(array('st' => 'store'), $get)
->join(array('ss' => 'store_staff')         , 'st.id = ss.store_id AND ss.is_leader = 1'    , array())
->join(array('rm' => 'regional_market')     , 'st.regional_market = rm.id'                  , array())
->join(array('rm2'=> 'regional_market')     , 'st.district = rm2.id'                        , array())
->join(array('a'  => 'area')                , 'rm.area_id = a.id'                           , array())
->join(array('s'  => 'staff')               , 'ss.staff_id = s.id'                          , array())
->join(array('g'  => 'group')               , 's.group_id = g.id'                           , array())
//->join(array('ssh'=> 'stock_shop_log')      , 'st.id = ssh.store_id'                        , array())
->join(array('i'  => WAREHOUSE_DB.'.imei')  , 'st.id = i.stock_shop_id'                     , array())
->joinLeft(array('ts' => 'timing_sale')     , 'i.imei_sn = ts.imei'                         , array())
//->where('i.imei_sn NOT IN (?)', $sub_select)
->where('st.del IS NULL')
->where('st.rank <> 3')
//->where('s.off_date IS NULL')
->group(array('st.id'))
->order(array('a.name ASC','st_stock DESC'));
if (isset($params['from']) && $params['from']) {
    $select->where('i.stock_shop_date >= ?', $from.' 00:00:00');
//$select->where('ssh.created_at >= ?', $from.' 00:00:00');
}
if (isset($params['to']) && $params['to']) {
    $select->where('i.stock_shop_date <= ?', $to.' 23:59:59');
//$select->where('ssh.created_at <= ?', $to.' 23:59:59');
}
if (isset($params['store_id']) && $params['store_id']) {
    $select->where('st.id LIKE ?', "%".$params['store_id']."%");
}
if (isset($params['store_name']) && $params['store_name']) {
    $select->where('st.name LIKE ?', "%".$params['store_name']."%");
}
if (isset($params['name'])) {
    if (is_array($params['name']) && count($params['name']) > 0) {
        $select->where('i.good_id IN (?)', $params['name']);
    } else {
        $select->where('i.good_id = ?', $params['name']);
    }
}
// ASM Permission
if (isset($params['asm']) && $params['asm']) {
    $QAsm = new Application_Model_Asm();
    $list_regions = $QAsm->get_cache($params['asm']);
    $list_regions = isset($list_regions['district']) && is_array($list_regions['district']) ? $list_regions['district'] : array();
    if (count($list_regions) > 0)
        $select->where('st.district IN (?)', $list_regions);
    else
        $select->where('1=0', 1);
}
// Sale Permission
if (isset($params['sale_id']) && intval($params['sale_id']) > 0) {
    $select
    ->joinRight(array('ssl' => 'store_staff_log'), 'ssl.store_id=i.stock_shop_id', array());
    $log_where = $this->getAdapter()->quoteInto('ssl.staff_id = ?', $params['sale_id']).
    " AND " . $this->getAdapter()->quoteInto('ssl.is_leader = ?', 1).
    " AND " . $this->getAdapter()->quoteInto('DATE(i.stock_shop_scan) >= FROM_UNIXTIME(ssl.joined_at, \'%Y-%m-%d\')', 1).
    " AND (".
    $this->getAdapter()->quoteInto('DATE(i.stock_shop_scan) < FROM_UNIXTIME(ssl.released_at, \'%Y-%m-%d\')', 1).
    " OR " . $this->getAdapter()->quoteInto('ssl.released_at IS NULL', 1).
    " OR " . $this->getAdapter()->quoteInto('ssl.released_at = 0', 1).
    " ) ";
    $select->where($log_where);
}
// Leader Permission
if (isset($params['leader_id']) && intval($params['leader_id']) > 0) {
    $select
    ->joinRight(array('ssl' => 'store_leader_log'), 'ssl.store_id=i.stock_shop_id', array());
    $log_where = $this->getAdapter()->quoteInto('ssl.staff_id = ?', $params['leader_id']).
    " AND " . $this->getAdapter()->quoteInto('DATE(i.stock_shop_scan) >= FROM_UNIXTIME(ssl.joined_at, \'%Y-%m-%d\')', 1).
    " AND (".
    $this->getAdapter()->quoteInto('DATE(i.stock_shop_scan) < FROM_UNIXTIME(ssl.released_at, \'%Y-%m-%d\')', 1).
    " OR " . $this->getAdapter()->quoteInto('ssl.released_at IS NULL', 1).
    " OR " . $this->getAdapter()->quoteInto('ssl.released_at = 0', 1).
    " ) ";
    $select->where($log_where);
}
if (isset($params['area_id'])) {
    if (is_array($params['area_id']) && count($params['area_id']) > 0) {
        $select->where('a.id IN (?)', $params['area_id']);
    }
}
if (isset($params['regional_market'])) {
    if (is_array($params['regional_market']) && count($params['regional_market']) > 0) {
        $select->where('rm.id IN (?)', $params['regional_market']);
    }
}
if (isset($params['district'])) {
    if (is_array($params['district']) && count($params['district']) > 0) {
        $select->where('st.district IN (?)', $params['district']);
    }
}
if (isset($params['store'])) {
    if (is_array($params['store']) && count($params['store']) > 0) {
        $select->where('st.id IN (?)', $params['store']);
    }
}
if ($limit && !$params['export']) {
    $select->limitPage($page, $limit);
}
if ( isset($params['get_total_sales']) and $params['get_total_sales'] == 1 ){
    $select_p = $db->select()
    ->from(array('pa' => $select),
        array(new Zend_Db_Expr('SUM( pa.st_stock )')));
//echo $select_p; die;
    return $db->fetchOne($select_p);
}
//echo $select; die;
$result = $db->fetchAll($select);
$total = $db->fetchOne("select FOUND_ROWS()");

return $result;
}
function short_report_by_stock_shop($params) {
    $d = explode('/', $params['from']);
    $from = $d[2].'-'.$d[1].'-'.$d[0];
    $d = explode('/', $params['to']);
    $to = $d[2].'-'.$d[1].'-'.$d[0];
    $db = Zend_Registry::get('db');
    $sub_select = $db->select()
    ->from(array('ts' => 'timing_sale'), array('imei' => 'ts.imei'))
    ->where('ts.imei = i.imei_sn');
    $select = $db->select()
    ->from(array('i' => WAREHOUSE_DB.'.imei'), array('imei_sn' => 'i.imei_sn','last_scan' => 'i.stock_shop_scan'))
    ->joinLeft(array('g'  => WAREHOUSE_DB.'.good')      , 'i.good_id = g.id'    , array('model' => 'g.name'))
    ->joinLeft(array('gc' => WAREHOUSE_DB.'.good_color'), 'i.good_color = gc.id', array('color' => 'gc.name'))
    ->where('i.stock_shop_id = ?', $params['store_id'])
    ->where('i.stock_shop_date >= ?', $from.' 00:00:00')
    ->where('i.stock_shop_date <= ?', $to.' 23:59:59')
    ->where('i.imei_sn NOT IN (?)', $sub_select)
    ->order(array('i.stock_shop_scan DESC', 'g.name ASC', 'gc.name ASC'));

//echo $select;
    $result = $db->fetchAll($select);
    return $result;
}
function short_report_by_stock_shop_sellout($params) {
    $d = explode('/', $params['from']);
    $from = $d[2].'-'.$d[1].'-'.$d[0];
    $d = explode('/', $params['to']);
    $to = $d[2].'-'.$d[1].'-'.$d[0];
    $db = Zend_Registry::get('db');
    $select = $db->select()
    ->from(array('i' => WAREHOUSE_DB.'.imei'), array('imei_sn' => 'i.imei_sn'))
    ->join(array('ts'=> 'timing_sale'), 'i.imei_sn = ts.imei', array('timing_date' => 'ts.time_add'))
    ->joinLeft(array('g'  => WAREHOUSE_DB.'.good')      , 'i.good_id = g.id'    , array('model' => 'g.name'))
    ->joinLeft(array('gc' => WAREHOUSE_DB.'.good_color'), 'i.good_color = gc.id', array('color' => 'gc.name'))
    ->where('i.stock_shop_id = ?', $params['store_id'])
    ->where('i.stock_shop_date >= ?', $from.' 00:00:00')
    ->where('i.stock_shop_date <= ?', $to.' 23:59:59')
    ->order(array('ts.time_add DESC', 'g.name ASC', 'gc.name ASC'));

//echo $select;
    $result = $db->fetchAll($select);
    return $result;
}
function short_report_by_stock_shop_all_scan($params) {
    $d = explode('/', $params['from']);
    $from = $d[2].'-'.$d[1].'-'.$d[0];
    $d = explode('/', $params['to']);
    $to = $d[2].'-'.$d[1].'-'.$d[0];
    $db = Zend_Registry::get('db');
    $select = $db->select()
    ->from(array('i' => WAREHOUSE_DB.'.imei'), array('imei_sn' => 'i.imei_sn','last_scan' => 'i.stock_shop_scan'))
    ->joinLeft(array('ts'=> 'timing_sale'), 'i.imei_sn = ts.imei', array('timing_date' => 'ts.time_add'))
    ->joinLeft(array('g'  => WAREHOUSE_DB.'.good')      , 'i.good_id = g.id'    , array('model' => 'g.name'))
    ->joinLeft(array('gc' => WAREHOUSE_DB.'.good_color'), 'i.good_color = gc.id', array('color' => 'gc.name'))
    ->where('i.stock_shop_id = ?', $params['store_id'])
    ->where('i.stock_shop_date >= ?', $from.' 00:00:00')
    ->where('i.stock_shop_date <= ?', $to.' 23:59:59')
    ->order(array('ts.time_add DESC', 'g.name ASC', 'gc.name ASC'));

//echo $select;
    $result = $db->fetchAll($select);
    return $result;
}
function getReortByMarketType($params) {
    $d = explode('/', $params['from']);
    $from = $d[2].'-'.$d[1].'-'.$d[0];
    $d = explode('/', $params['to']);
    $to = $d[2].'-'.$d[1].'-'.$d[0];
    $db = Zend_Registry::get('db');
    $get = array(
        'area_name' => 'a.name',
// Standard Shoping Mall
        'store_active_01_org' => new Zend_Db_Expr(
            "COUNT( DISTINCT CASE WHEN mt.id = 1 AND o.store_type_id = 1 AND ts.imei IS NOT NULL THEN st.id END )"),
        'store_active_01_dealer' => new Zend_Db_Expr(
            "COUNT( DISTINCT CASE WHEN mt.id = 1 AND (o.store_type_id = 2 AND o.org_id <> 18) AND ts.imei IS NOT NULL THEN st.id END )"),
        'store_active_01_bs' => new Zend_Db_Expr(
            "COUNT( DISTINCT CASE WHEN mt.id = 1 AND (o.store_type_id = 3 OR o.org_id = 18) AND ts.imei IS NOT NULL THEN st.id END )"),

        'store_all_01_org' => new Zend_Db_Expr(
            "COUNT( DISTINCT CASE WHEN mt.id = 1 AND o.store_type_id = 1 THEN st.id END )"),
        'store_all_01_dealer' => new Zend_Db_Expr(
            "COUNT( DISTINCT CASE WHEN mt.id = 1 AND (o.store_type_id = 2 AND o.org_id <> 18) THEN st.id END )"),
        'store_all_01_bs' => new Zend_Db_Expr(
            "COUNT( DISTINCT CASE WHEN mt.id = 1 AND (o.store_type_id = 3 OR o.org_id = 18) THEN st.id END )"),
        'sellout_01_org' => new Zend_Db_Expr(
            "COUNT( CASE WHEN mt.id = 1 AND o.store_type_id = 1 THEN ts.imei END )"),
        'sellout_01_dealer' => new Zend_Db_Expr(
            "COUNT( CASE WHEN mt.id = 1 AND (o.store_type_id = 2 AND o.org_id <> 18) THEN ts.imei END )"),
        'sellout_01_bs' => new Zend_Db_Expr(
            "COUNT( CASE WHEN mt.id = 1 AND (o.store_type_id = 3 OR o.org_id = 18) THEN ts.imei END )"),
// Supermarket
        'store_active_02_org' => new Zend_Db_Expr(
            "COUNT( DISTINCT CASE WHEN mt.id = 2 AND o.store_type_id = 1 AND ts.imei IS NOT NULL THEN st.id END )"),
        'store_active_02_dealer' => new Zend_Db_Expr(
            "COUNT( DISTINCT CASE WHEN mt.id = 2 AND (o.store_type_id = 2 AND o.org_id <> 18) AND ts.imei IS NOT NULL THEN st.id END )"),
        'store_active_02_bs' => new Zend_Db_Expr(
            "COUNT( DISTINCT CASE WHEN mt.id = 2 AND (o.store_type_id = 3 OR o.org_id = 18) AND ts.imei IS NOT NULL THEN st.id END )"),

        'store_all_02_org' => new Zend_Db_Expr(
            "COUNT( DISTINCT CASE WHEN mt.id = 2 AND o.store_type_id = 1 THEN st.id END )"),
        'store_all_02_dealer' => new Zend_Db_Expr(
            "COUNT( DISTINCT CASE WHEN mt.id = 2 AND (o.store_type_id = 2 AND o.org_id <> 18) THEN st.id END )"),
        'store_all_02_bs' => new Zend_Db_Expr(
            "COUNT( DISTINCT CASE WHEN mt.id = 2 AND (o.store_type_id = 3 OR o.org_id = 18) THEN st.id END )"),
        'sellout_02_org' => new Zend_Db_Expr(
            "COUNT( CASE WHEN mt.id = 2 AND o.store_type_id = 1 THEN ts.imei END )"),
        'sellout_02_dealer' => new Zend_Db_Expr(
            "COUNT( CASE WHEN mt.id = 2 AND (o.store_type_id = 2 AND o.org_id <> 18) THEN ts.imei END )"),
        'sellout_02_bs' => new Zend_Db_Expr(
            "COUNT( CASE WHEN mt.id = 2 AND (o.store_type_id = 3 OR o.org_id = 18) THEN ts.imei END )"),
// Stand Alone
        'store_active_03_org' => new Zend_Db_Expr(
            "COUNT( DISTINCT CASE WHEN mt.id = 3 AND o.store_type_id = 1 AND ts.imei IS NOT NULL THEN st.id END )"),
        'store_active_03_dealer' => new Zend_Db_Expr(
            "COUNT( DISTINCT CASE WHEN mt.id = 3 AND (o.store_type_id = 2 AND o.org_id <> 18) AND ts.imei IS NOT NULL THEN st.id END )"),
        'store_active_03_bs' => new Zend_Db_Expr(
            "COUNT( DISTINCT CASE WHEN mt.id = 3 AND (o.store_type_id = 3 OR o.org_id = 18) AND ts.imei IS NOT NULL THEN st.id END )"),

        'store_all_03_org' => new Zend_Db_Expr(
            "COUNT( DISTINCT CASE WHEN mt.id = 3 AND o.store_type_id = 1 THEN st.id END )"),
        'store_all_03_dealer' => new Zend_Db_Expr(
            "COUNT( DISTINCT CASE WHEN mt.id = 3 AND (o.store_type_id = 2 AND o.org_id <> 18) THEN st.id END )"),
        'store_all_03_bs' => new Zend_Db_Expr(
            "COUNT( DISTINCT CASE WHEN mt.id = 3 AND (o.store_type_id = 3 OR o.org_id = 18) THEN st.id END )"),
        'sellout_03_org' => new Zend_Db_Expr(
            "COUNT( CASE WHEN mt.id = 3 AND o.store_type_id = 1 THEN ts.imei END )"),
        'sellout_03_dealer' => new Zend_Db_Expr(
            "COUNT( CASE WHEN mt.id = 3 AND (o.store_type_id = 2 AND o.org_id <> 18) THEN ts.imei END )"),
        'sellout_03_bs' => new Zend_Db_Expr(
            "COUNT( CASE WHEN mt.id = 3 AND (o.store_type_id = 3 OR o.org_id = 18) THEN ts.imei END )"),
// Local Shopping Mall
        'store_active_04_org' => new Zend_Db_Expr(
            "COUNT( DISTINCT CASE WHEN mt.id = 4 AND o.store_type_id = 1 AND ts.imei IS NOT NULL THEN st.id END )"),
        'store_active_04_dealer' => new Zend_Db_Expr(
            "COUNT( DISTINCT CASE WHEN mt.id = 4 AND (o.store_type_id = 2 AND o.org_id <> 18) AND ts.imei IS NOT NULL THEN st.id END )"),
        'store_active_04_bs' => new Zend_Db_Expr(
            "COUNT( DISTINCT CASE WHEN mt.id = 4 AND (o.store_type_id = 3 OR o.org_id = 18) AND ts.imei IS NOT NULL THEN st.id END )"),

        'store_all_04_org' => new Zend_Db_Expr(
            "COUNT( DISTINCT CASE WHEN mt.id = 4 AND o.store_type_id = 1 THEN st.id END )"),
        'store_all_04_dealer' => new Zend_Db_Expr(
            "COUNT( DISTINCT CASE WHEN mt.id = 4 AND (o.store_type_id = 2 AND o.org_id <> 18) THEN st.id END )"),
        'store_all_04_bs' => new Zend_Db_Expr(
            "COUNT( DISTINCT CASE WHEN mt.id = 4 AND (o.store_type_id = 3 OR o.org_id = 18) THEN st.id END )"),
        'sellout_04_org' => new Zend_Db_Expr(
            "COUNT( CASE WHEN mt.id = 4 AND o.store_type_id = 1 THEN ts.imei END )"),
        'sellout_04_dealer' => new Zend_Db_Expr(
            "COUNT( CASE WHEN mt.id = 4 AND (o.store_type_id = 2 AND o.org_id <> 18) THEN ts.imei END )"),
        'sellout_04_bs' => new Zend_Db_Expr(
            "COUNT( CASE WHEN mt.id = 4 AND (o.store_type_id = 3 OR o.org_id = 18) THEN ts.imei END )"),
// Mini market
        'store_active_05_org' => new Zend_Db_Expr(
            "COUNT( DISTINCT CASE WHEN mt.id = 5 AND o.store_type_id = 1 AND ts.imei IS NOT NULL THEN st.id END )"),
        'store_active_05_dealer' => new Zend_Db_Expr(
            "COUNT( DISTINCT CASE WHEN mt.id = 5 AND (o.store_type_id = 2 AND o.org_id <> 18) AND ts.imei IS NOT NULL THEN st.id END )"),
        'store_active_05_bs' => new Zend_Db_Expr(
            "COUNT( DISTINCT CASE WHEN mt.id = 5 AND (o.store_type_id = 3 OR o.org_id = 18) AND ts.imei IS NOT NULL THEN st.id END )"),

        'store_all_05_org' => new Zend_Db_Expr(
            "COUNT( DISTINCT CASE WHEN mt.id = 5 AND o.store_type_id = 1 THEN st.id END )"),
        'store_all_05_dealer' => new Zend_Db_Expr(
            "COUNT( DISTINCT CASE WHEN mt.id = 5 AND (o.store_type_id = 2 AND o.org_id <> 18) THEN st.id END )"),
        'store_all_05_bs' => new Zend_Db_Expr(
            "COUNT( DISTINCT CASE WHEN mt.id = 5 AND (o.store_type_id = 3 OR o.org_id = 18) THEN st.id END )"),
        'sellout_05_org' => new Zend_Db_Expr(
            "COUNT( CASE WHEN mt.id = 5 AND o.store_type_id = 1 THEN ts.imei END )"),
        'sellout_05_dealer' => new Zend_Db_Expr(
            "COUNT( CASE WHEN mt.id = 5 AND (o.store_type_id = 2 AND o.org_id <> 18) THEN ts.imei END )"),
        'sellout_05_bs' => new Zend_Db_Expr(
            "COUNT( CASE WHEN mt.id = 5 AND (o.store_type_id = 3 OR o.org_id = 18) THEN ts.imei END )"),
// Event
        'store_active_06_org' => new Zend_Db_Expr(
            "COUNT( DISTINCT CASE WHEN mt.id = 6 AND o.store_type_id = 1 AND ts.imei IS NOT NULL THEN st.id END )"),
        'store_active_06_dealer' => new Zend_Db_Expr(
            "COUNT( DISTINCT CASE WHEN mt.id = 6 AND (o.store_type_id = 2 AND o.org_id <> 18) AND ts.imei IS NOT NULL THEN st.id END )"),
        'store_active_06_bs' => new Zend_Db_Expr(
            "COUNT( DISTINCT CASE WHEN mt.id = 6 AND (o.store_type_id = 3 OR o.org_id = 18) AND ts.imei IS NOT NULL THEN st.id END )"),

        'store_all_06_org' => new Zend_Db_Expr(
            "COUNT( DISTINCT CASE WHEN mt.id = 6 AND o.store_type_id = 1 THEN st.id END )"),
        'store_all_06_dealer' => new Zend_Db_Expr(
            "COUNT( DISTINCT CASE WHEN mt.id = 6 AND (o.store_type_id = 2 AND o.org_id <> 18) THEN st.id END )"),
        'store_all_06_bs' => new Zend_Db_Expr(
            "COUNT( DISTINCT CASE WHEN mt.id = 6 AND (o.store_type_id = 3 OR o.org_id = 18) THEN st.id END )"),
        'sellout_06_org' => new Zend_Db_Expr(
            "COUNT( CASE WHEN mt.id = 6 AND o.store_type_id = 1 THEN ts.imei END )"),
        'sellout_06_dealer' => new Zend_Db_Expr(
            "COUNT( CASE WHEN mt.id = 6 AND (o.store_type_id = 2 AND o.org_id <> 18) THEN ts.imei END )"),
        'sellout_06_bs' => new Zend_Db_Expr(
            "COUNT( CASE WHEN mt.id = 6 AND (o.store_type_id = 3 OR o.org_id = 18) THEN ts.imei END )"),
    );
$select = $db->select()->from(array('st' => 'store'), $get)
->join(array('rm'     => 'regional_market') , 'st.regional_market = rm.id'  , array())
->join(array('a'      => 'area')            , 'rm.area_id = a.id'           , array())
->join(array('rm2'    => 'regional_market') , 'st.district = rm2.id'        , array())
->join(array('o'      => 'org')             , 'st.org_dealer = o.org_id'    , array())
->joinLeft(array('sm' => 'store_market')    , 'st.id = sm.store_id'         , array())
->joinLeft(array('mn' => 'market_name')     , 'sm.market_name_id = mn.id'   , array())
->joinLeft(array('mt' => 'market_type')     , 'mn.market_type_id = mt.id'   , array())
->joinLeft(array('t'  => 'timing'),
    "   st.id = t.store
    AND t.created_at >= '".$from." 00:00:00'
    AND t.created_at <= '".$to." 23:59:59'
    ",
    array())
->joinLeft(array('ts' => 'timing_sale')     , 't.id = ts.timing_id'         , array())
->group('a.id')
->order('a.name ASC');
// Filter
if (isset($params['id']) && $params['id']) {
    $select-where('st.id = ?', $params['id']);
}
if (isset($params['name']) && $params['name']) {
    $select-where('st.name = ?', $params['name']);
}
if (isset($params['name']) && $params['name']) {
    $select-where('st.name = ?', $params['name']);
}
if (isset($params['area_id']) && $params['area_id']) {
    if (is_array($params['area_id']) && count($params['area_id']))
        $select->where('a.id IN (?)', $params['area_id']);
    elseif (is_numeric($params['area_id']))
        $select->where('a.id = ?', intval($params['area_id']));
    else
        $select->where('1=0', 1);
}
if (isset($params['regional_market']) && $params['regional_market']) {
    if (is_array($params['regional_market']) && count($params['regional_market']))
        $select->where('st.regional_market IN (?)', $params['regional_market']);
    elseif (is_numeric($params['regional_market']))
        $select->where('st.regional_market = ?', intval($params['regional_market']));
    else
        $select->where('1=0', 1);
}
if (isset($params['district']) && $params['district']) {
    if (is_array($params['district']) && count($params['district']))
        $select->where('st.district IN (?)', $params['district']);
    elseif (is_numeric($params['district']))
        $select->where('st.district = ?', intval($params['district']));
    else
        $select->where('1=0', 1);
}
if (isset($params['store']) && $params['store']) {
    if (is_array($params['store']) && count($params['store']))
        $select->where('st.id IN (?)', $params['store']);
    elseif (is_numeric($params['store']))
        $select->where('st.id = ?', intval($params['store']));
    else
        $select->where('1=0', 1);
}
if (isset($params['org']) && $params['org']) {
    if (is_array($params['org']) && count($params['org']))
        $select->where('st.org_dealer IN (?)', $params['org']);
    elseif (is_numeric($params['org']))
        $select->where('st.org_dealer = ?', intval($params['org']));
    else
        $select->where('1=0', 1);
}
if (isset($params['market_type']) && $params['market_type']) {
    if (is_array($params['market_type']) && count($params['market_type']))
        $select->where('mn.market_type_id IN (?)', $params['market_type']);
    elseif (is_numeric($params['market_type']))
        $select->where('mn.market_type_id = ?', intval($params['market_type']));
    else
        $select->where('1=0', 1);
}
if (isset($params['market_name']) && $params['market_name']) {
    $select->where('mn.name LIKE ?', '%'.$params['market_name'].'%');
}
//echo $select; die;
$result = $db->fetchAll($select);
return $result;
}


function analytic_store($page, $limit, &$total, $params) {

    set_time_limit(0);
    ini_set('memory_limit', '-1');
    error_reporting(~E_ALL);
    ini_set("display_error", '0');
    $d1 = explode('/', $params['from']);
    $from = $d1[2].'-'.$d1[1].'-'.$d1[0];
    $d2 = explode('/', $params['to']);
    $to = $d2[2].'-'.$d2[1].'-'.$d2[0];
    $db = Zend_Registry::get('db');

    if ( (isset($params['export']) and $params['export']) || (isset($params['total_sellout']) && $params['total_sellout'] == 0) ) {
        $get_01 = array( 'st_id' => 'st.id' );
    } else {
        $get_01 = array( 'st_id' => new Zend_Db_Expr('SQL_CALC_FOUND_ROWS st.id') );
    }

    $get_02 = array(
        'st_name'       => 'st.name',
        'st_code'       => 'st.store_code',
        'oppo_id'       => 'st.oppo_id',
        'st_level'      => 'st.store_grade',
        'st_shopid'     => 'st.store_id',
        'st_shopcode'   => 'st.store_code',
        'st_del'        => 'st.del',
        'st_rank'       => 'st.rank',
        'st_grade'      => 'st.store_grade',
        'd_id'          => 'd.id',
        'd_code'          => 'd.distributor_code',
        'd_name'        => 'd.title',
        'area_id'       => 'a.id',
        'area'          => 'a.name',
        'province'      => 'rm.name',
        'district'      => 'rm2.name',
        'st_type'       => 'o.org_name',
        'st_operation'  => 'so.name',
        'it_junction'   => 'st.it_junction',
        'is_pre_order'  => 'st.is_pre_order',
        'brand_name'    => 'b.name'
    );
    if ($d1[1] == $d2[1]) {

    // Range of Last 1 Month
        $tmp_start_01 = new DateTime( $from );
        $tmp_start_01->modify( 'first day of previous month' );
        $last_start_01 = $tmp_start_01->format( 'Y-m-d' );
        $tmp_end_01 = new DateTime( $from );
        $tmp_end_01->modify( 'last day of previous month' );
        $last_end_01 = $tmp_end_01->format( 'Y-m-d' );
        // Range of Last 2 Month
        $tmp_start_02 = new DateTime( $last_start_01 );
        $tmp_start_02->modify( 'first day of previous month' );
        $last_start_02 = $tmp_start_02->format( 'Y-m-d' );

        $tmp_end_02 = new DateTime( $last_end_01 );
        $tmp_end_02->modify( 'last day of previous month' );
        $last_end_02 = $tmp_end_02->format( 'Y-m-d' );

        $get_03 = array(
            'last_02'   => new Zend_Db_Expr("COUNT( CASE WHEN t.created_at >= '".$last_start_02." 00:00:00' AND t.created_at <= '".$last_end_02." 23:59:59' THEN ts.imei END )"),
            'last_01'   => new Zend_Db_Expr("COUNT( CASE WHEN t.created_at >= '".$last_start_01." 00:00:00' AND t.created_at <= '".$last_end_01." 23:59:59' THEN ts.imei END )"),
            'sellout'   => new Zend_Db_Expr("COUNT( CASE WHEN t.created_at >= '".$from." 00:00:00' AND ts.product_id = i.good_id AND t.created_at <= '".$to." 23:59:59' THEN ts.imei END )"),
            'sellout_hero'  => new Zend_Db_Expr("COUNT( CASE WHEN t.created_at >= '".$from." 00:00:00' AND t.created_at <= '".$to." 23:59:59' AND ts.product_id IN (".implode(",", $params['selected_product']).") THEN ts.imei END )"),
            'total_price' => new Zend_Db_Expr("SUM( CASE WHEN t.created_at >= '".$from." 00:00:00' AND t.created_at <= '".$to." 23:59:59' THEN gkl.price END )"),
          'activated' => new Zend_Db_Expr("COUNT( CASE WHEN t.created_at >= '".$from." 00:00:00' AND t.created_at <= '".$to." 23:59:59' AND i.activated_date IS NOT NULL THEN ts.imei END )"),
          //'not_activated' => new Zend_Db_Expr("COUNT( CASE WHEN t.created_at >= '".$from." 00:00:00' AND t.created_at <= '".$to." 23:59:59' AND i.activated_date IS NULL THEN ts.imei END )"),
        );
        $start = $last_start_02;
    } else {

        $get_03 = array(
            'sellout'       => new Zend_Db_Expr("COUNT(ts.imei)"),
            'sellout_hero'  => new Zend_Db_Expr("COUNT(CASE WHEN ts.product_id IN (".implode(",", $params['selected_product']).") THEN ts.imei END)"),
            'total_price'   => new Zend_Db_Expr("SUM(gkl.price)"),
            'activated' => new Zend_Db_Expr("COUNT( CASE WHEN i.activated_date IS NOT NULL THEN ts.imei END )"),
        );
        $start = $from;

        /*
        $get_03 = array(
        'sellout'   => new Zend_Db_Expr("(".$sub_select_sellout.")"),
        'activated' => new Zend_Db_Expr("(".$sub_select_activated.")"),
        'total_price' => new Zend_Db_Expr("(".$sub_select_price.")"),
        'sellout_hero'   => new Zend_Db_Expr("(".$sub_select_hero_sellout.")"),
        'activated_hero' => new Zend_Db_Expr("(".$sub_select_hero_activated.")"),
    );*/
}

$get = $get_01 + $get_02 + $get_03;
//$get = $get_01 + $get_02;
$select = $db->select()
->from(array('st' => 'store'), $get)
->joinLeft(array('d'    => WAREHOUSE_DB.'.distributor'), 'st.d_id = d.id'       , array())
->joinLeft(array('rm'   => 'regional_market')   , 'st.regional_market = rm.id'  , array())
->joinLeft(array('a'    => 'area')              , 'rm.area_id = a.id'           , array())
->joinLeft(array('rm2'  => 'regional_market')   , 'st.district = rm2.id'        , array())
->joinLeft(array('o'    => 'org')               , 'st.org_dealer = o.org_id'    , array())
->joinLeft(array('so'   => 'store_operation')   , 'st.operation_id = so.id'     , array())
->joinLeft(array('t'    => 'timing'),
    "   st.id = t.store
    AND t.created_at >= '".$start." 00:00:00'
    AND t.created_at <= '".$to." 23:59:59'
    ", array())

->joinLeft(array('ts'   => 'timing_sale')   , 't.id = ts.timing_id '         , array())
->joinLeft(array('g'    => WAREHOUSE_DB.'.good'), 'ts.product_id = g.id'        , array('model_name' => 'g.name'))
->joinLeft(array('gkl'  => 'good_kpi_log'),
    "   ts.product_id = gkl.good_id
    AND ts.model_id = gkl.color_id
    AND CONCAT(gkl.from_date, ' 00:00:00') <= t.created_at
    AND CONCAT(gkl.to_date, ' 23:59:59') >= t.created_at
    ", array())

->joinLeft(array('i' => WAREHOUSE_DB.'.imei'),'ts.imei = i.imei_sn',array())
->joinLeft(array('d2' => WAREHOUSE_DB.'.distributor'),'i.distributor_id = d2.id',array())
->joinLeft(array('rm3' => HR_DB.'.regional_market'),'d2.region = rm3.id',array())
->joinLeft(array('a3' => HR_DB.'.area'),'rm3.area_id = a3.id',array())
->joinLeft(array('b' => WAREHOUSE_DB.'.brand'),'b.id = g.brand_id',array())
// ->joinLeft(array('g' => WAREHOUSE_DB.'.good'),'g.id = i.good_id',array())


// ->where('t.created_by != 18')
// ->where('st.del IS NULL')
->where('ts.imei NOT IN (select imei from hr.timing_control)');

if( isset($params['get_product']) && $params['get_product']) {
    $select->group('i.good_id');
    $select->group('i.good_color');
}

$select->group('st.id');
$select->order('sellout DESC');

// Filter
if ( isset($params['id']) && $params['id'] ) {
    $select->where('st.store_code LIKE ?', '%'.$params['id'].'%');
}

if (isset($params['brand']) && $params['brand']) {
    if (is_array($params['brand']) && count($params['brand']))
        $select->where('g.brand_id IN (?)', $params['brand']);
    elseif (is_numeric($params['brand']))
        $select->where('g.brand_id = ?', intval($params['brand']));
    else
        $select->where('1=0', 1);
}

if(isset($params['rgm_area']) && $params['rgm_area']){
    $select->where('a3.id IN ('.$params['rgm_area'].')');
}

if (isset($params['good_id']) && $params['good_id']) {
    if (is_array($params['good_id']) && count($params['good_id']))
        $select->where('ts.product_id IN (?)', $params['good_id']);
    elseif (is_numeric($params['good_id']))
        $select->where('ts.product_id = ?', intval($params['good_id']));
    else
        $select->where('1=0', 1);
}


if ( isset($params['name']) && $params['name'] ) {
    $select->where('st.name LIKE ?', '%'.$params['name'].'%');
}

if( isset($params['d_tag']) && $params['d_tag'] ) {
    $select->where('d.d_tag =?',1);
}

if ( isset($params['del']) && $params['del'] == 1) {
    $select->where('st.del IS NULL');
}

if ( isset($params['shop_id']) && $params['shop_id'] ) {
    $select->where('st.store_id LIKE ?', '%'.$params['shop_id'].'%');
}
if ( isset($params['shop_code']) && $params['shop_code'] ) {
    $select->where('st.store_code LIKE ?', '%'.$params['shop_code'].'%');
}
if ( isset($params['it_junction']) && $params['it_junction'] ) {
    $select->where('st.it_junction = ?', $params['it_junction']);
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

// Add Filter Store
if (isset($params['store']) && $params['store']) {
    if (is_array($params['store']) && count($params['store']))
        $select->where('st.id IN (?)', $params['store']);
    elseif (is_numeric($params['store']))
        $select->where('st.id = ?', intval($params['store']));
    else
        $select->where('1=0', 1);
}

// Add Filter Store Type
if (isset($params['org']) && $params['org']) {
    if (is_array($params['org']) && count($params['org']))
        $select->where('st.org_dealer IN (?)', $params['org']);
    elseif (is_numeric($params['org']))
        $select->where('st.org_dealer = ?', intval($params['org']));
    else
        $select->where('1=0', 1);
}

// Add Filter Market Type, Market Name
if ( (isset($params['market_type']) and $params['market_type']) || (isset($params['market_name']) && $params['market_name']) ) {
    $select->join( array('sm' => 'store_market'), 'st.id = sm.store_id'         , array());
    $select->join( array('mn' => 'market_name') , 'sm.market_name_id = mn.id'   , array('market_name' => 'mn.name'));
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


// Add Filter Sale Code, Sale Name
if ( (isset($params['staff_name']) and $params['staff_name']) || (isset($params['staff_code']) && $params['staff_code']) ) {
    $select->joinLeft( array('ss' => 'store_staff'), 'st.id = ss.store_id AND ss.is_leader = 1', array());
    $select->joinLeft( array('s' => 'staff'), 'ss.staff_id = s.id', array());

// Filter Sale Name
    if (isset($params['staff_name']) && $params['staff_name'])
        $select->where("CONCAT(s.firstname, ' ', s.lastname) LIKE ?", '%'.$params['staff_name'].'%');

// Filter Sale Code
    if (isset($params['staff_code']) && $params['staff_code'])
        $select->where('s.code LIKE ?', '%'.$params['staff_code'].'%');
}

// Add Filter Sale More Than
if (isset($params['sales_from']) && $params['sales_from'])
    $select->having('sellout >= ?', $params['sales_from']);

// Add Filter Sale Less Than
if (isset($params['sales_from']) && $params['sales_from'])
    $select->having('sellout <= ?', $params['sales_from']);

// Add Filter Store Level
if (isset($params['store_level']) && $params['store_level']) {
    if (is_array($params['store_level']) && count($params['store_level']))
        $select->where('st.store_grade IN (?)', $params['store_level']);
    else
        $select->where('1=0', 1);
}

// check ermission ASM, ASM Stand by, Sale Admin, Traning
if ( isset($params['asm']) && $params['asm'] ) {
    $QAsm = new Application_Model_Asm();
    $list_regions = $QAsm->get_cache($params['asm']);
    $list_regions = isset($list_regions['province']) && is_array($list_regions['province']) ? $list_regions['province'] : array();
    if (count($list_regions) > 0)
        $select->where( 'st.province_id IN (?)', $list_regions);
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
} else {
    $select->where('st.rank <> 3');
}

// check Permission Admin Brandshop
if ( isset($params['admin_bs']) && $params['admin_bs'] ) {
    $select->where('(st.org_dealer = ?', 18);
    $select->orWhere('o.store_type_id = ?)', 3);
}

if (isset($params['sale_id']) && intval($params['sale_id']) > 0) {

    $QSalesArea = new Application_Model_SalesArea();
    $list_regions = $QSalesArea->get_cache($params['sale_id']);
    $list_regions = isset($list_regions['district']) && is_array($list_regions['district']) ? $list_regions['district'] : array();
    if (count($list_regions) > 0) {
        $select->where('( st.district IN (?)', $list_regions);
        $sub_select = $db->select()
        ->from(array('ssl' => 'store_staff'), array('st_id' => 'ssl.store_id'))
        ->where('staff_id = ?', $params['sale_id']);
        $ss_result = $db->fetchAll($sub_select);
        foreach ($ss_result as $key => $value) { $ss_data[] = $value['st_id']; }
        $select->orWhere('st.id IN (?) )', $ss_data);
    } else {
        $select->joinRight(array('ssl' => 'store_staff'), 'ssl.store_id = st.id', array());
        $log_where = $this->getAdapter()->quoteInto('ssl.staff_id = ?', $params['sale_id']).
        " AND " . $this->getAdapter()->quoteInto('ssl.is_leader = ?', 1);
        $select->where($log_where);
    }
}

if (isset($params['pcm_id']) && intval($params['pcm_id']) > 0) {
//$select->joinRight(array('t' => 'timing'), 'st.id = t.store', array());
//$select->joinRight(array('ssl' => 'store_staff_log'), 'ssl.store_id=t.store', array());
    $select->joinRight(array('ssl' => 'store_staff'), 'ssl.store_id=st.id', array());
    $log_where = $this->getAdapter()->quoteInto('ssl.staff_id = ?', $params['pcm_id']).
    " AND " . $this->getAdapter()->quoteInto('ssl.is_leader = ?', 2);

    $select->where($log_where);
}

if (isset($params['bm_id']) && intval($params['bm_id']) > 0) {
//$select->joinRight(array('t' => 'timing'), 'st.id = t.store', array());
//$select->joinRight(array('ssl' => 'store_staff_log'), 'ssl.store_id=t.store', array());
    $select->joinRight(array('ssl' => 'store_staff'), 'ssl.store_id=st.id', array());
    $log_where = $this->getAdapter()->quoteInto('ssl.staff_id = ?', $params['bm_id']).
    " AND " . $this->getAdapter()->quoteInto('ssl.is_leader = ?', 3);

    $select->where($log_where);
}

if (isset($params['leader_id']) && intval($params['leader_id']) > 0) {
//$select->joinRight(array('t' => 'timing'), 'st.id = t.store', array());
//$select->joinRight(array('ssl' => 'store_leader_log'), 'ssl.store_id=t.store', array());
    $select->joinRight(array('ssl' => 'store_leader'), 'ssl.store_id=st.id', array());
    $log_where = $this->getAdapter()->quoteInto('ssl.staff_id = ?', $params['leader_id']);

    $select->where($log_where);
}

// Special Permission [5 Sale]
$now = date('Y-m-d H:i:s');
if ( $now >= '2017-02-07 00:00:00' && $now <= '2017-02-24 23:59:59' ) {
    if (isset($params['sale_id']) && in_array( $params['sale_id'], array(154,189,194,225,179) ) ) {
        $mn_list = array();
        if ($params['sale_id'] == 154) { $mn_list = array(212); }
        if ($params['sale_id'] == 189) { $mn_list = array(15, 30); }
        if ($params['sale_id'] == 194) { $mn_list = array(1083); }
        if ($params['sale_id'] == 225) { $mn_list = array(16); }
        if ($params['sale_id'] == 179) { $mn_list = array(192); }
        $sub_select_sp = $db->select()
        ->from(array('sm'=> 'store_market'), array('st_id' => 'sm.store_id'))
        ->where('sm.market_name_id IN (?)', $mn_list);
        $select->orWhere('st.id IN (?)', $sub_select_sp);
    }
}



if ( isset($params['export']) && $params['export'] ) {
//echo $select; die;
    return $db->fetchAll($select);
}

if ($limit) {
    $select->limitPage($page, $limit);
}

if ( isset($params['total_sellout']) && $params['total_sellout'] == 0 ) {
    $select_p = $db->select()
    ->from(array('pa' => $select), array(
        'total_sellout' => new Zend_Db_Expr('SUM( pa.sellout )'),
        'total_sellout_hero' => new Zend_Db_Expr('SUM( pa.sellout_hero )'),
        'total_price' => new Zend_Db_Expr('SUM( pa.total_price )'),
        'total_activated' => new Zend_Db_Expr('SUM( pa.activated )')
    ));

//echo $select_p;
    return $db->fetchRow($select_p);
}

// echo $select; die;
$result = $db->fetchAll($select);
$total = $db->fetchOne("select FOUND_ROWS()");

return $result;
}

// Report By Store : Export BS Store List
function analytic_bs_store($params) {
    set_time_limit(0);
    ini_set('memory_limit', '-1');
    error_reporting(~E_ALL);
    ini_set("display_error", '0');
    $d1 = explode('/', $params['from']);
    $from = $d1[2].'-'.$d1[1].'-'.$d1[0];
    $d2 = explode('/', $params['to']);
    $to = $d2[2].'-'.$d2[1].'-'.$d2[0];
    $db = Zend_Registry::get('db');
    $get_01 = array(
        'st_id'     => 'st.id',
        'st_name'   => 'st.name',
        'st_type'   => 'o.org_name',
        'st_del'    => 'st.del',
        'st_level'  => 'st.store_grade',
        'd_id'      => 'd.id',
        'd_name'    => 'd.title',
        'd_code'    => 'd.store_code',
        'area_id'   => 'a.id',
        'area'      => 'a.name',
        'province'  => 'rm.name',
        'district'  => 'rm2.name',
        'operator_package'  => 'st.operator_package',
        'rental_fee'        => 'st.rental_fee',
        'service_fee'       => 'st.service_fee',
        'ct_oppo_start'     => 'st.oppo_contract_start',
        'ct_oppo_end'       => 'st.oppo_contract_end',
        'ct_market_start'   => 'st.oppo_contract_start',
        'ct_market_end'     => 'st.oppo_contract_end',
        'shop_size'         => 'st.shop_size',
        'deposit'           => 'st.deposit',
        'room_type'         => 'st.room_type',
        'shop_acreage'      => 'st.shop_acreage',
        'shop_version'      => 'st.shop_version',
        'exp_shop'          => new Zend_Db_Expr("(CASE WHEN st.exp_shop = 1 THEN 'Yes' ELSE 'No' END)"),
    );
    if ($d1[1] == $d2[1]) {
// Range of Last 1 Month
        $tmp_start_01 = new DateTime( $from );
        $tmp_start_01->modify( 'first day of previous month' );
        $last_start_01 = $tmp_start_01->format( 'Y-m-d' );
        $tmp_end_01 = new DateTime( $from );
        $tmp_end_01->modify( 'last day of previous month' );
        $last_end_01 = $tmp_end_01->format( 'Y-m-d' );
// Range of Last 2 Month
        $tmp_start_02 = new DateTime( $last_start_01 );
        $tmp_start_02->modify( 'first day of previous month' );
        $last_start_02 = $tmp_start_02->format( 'Y-m-d' );

        $tmp_end_02 = new DateTime( $last_end_01 );
        $tmp_end_02->modify( 'last day of previous month' );
        $last_end_02 = $tmp_end_02->format( 'Y-m-d' );

        $get_02 = array(
            'last_02'   => new Zend_Db_Expr("COUNT( CASE WHEN t.created_at >= '".$last_start_02." 00:00:00' AND t.created_at <= '".$last_end_02." 23:59:59' THEN ts.imei END )"),
            'last_01'   => new Zend_Db_Expr("COUNT( CASE WHEN t.created_at >= '".$last_start_01." 00:00:00' AND t.created_at <= '".$last_end_01." 23:59:59' THEN ts.imei END )"),
            'sellout'   => new Zend_Db_Expr("COUNT( CASE WHEN t.created_at >= '".$from." 00:00:00' AND t.created_at <= '".$to." 23:59:59' THEN ts.imei END )"),
            'sellout_hero'  => new Zend_Db_Expr("COUNT( CASE WHEN t.created_at >= '".$from." 00:00:00' AND t.created_at <= '".$to." 23:59:59' AND ts.product_id IN (".implode(",", $params['selected_product']).") THEN ts.imei END )"),
            'total_price' => new Zend_Db_Expr("SUM( CASE WHEN t.created_at >= '".$from." 00:00:00' AND t.created_at <= '".$to." 23:59:59' THEN gkl.price END )"),
            'cost_price' => new Zend_Db_Expr(
                "   SUM(
                CASE WHEN t.created_at >= '".$from." 00:00:00' AND t.created_at <= '".$to." 23:59:59' THEN
                ( CASE
                WHEN d2.rank = 1 THEN g.price_1
                WHEN d2.rank = 2 THEN g.price_2
                WHEN d2.rank = 3 THEN g.price_3
                WHEN d2.rank = 4 THEN g.price_4
                WHEN d2.rank = 5 THEN g.price_5
                WHEN d2.rank = 6 THEN g.price_6
                WHEN d2.rank = 7 THEN g.price_7
                WHEN d2.rank = 8 THEN g.price_8
                WHEN d2.rank = 9 THEN g.price_9
                WHEN d2.rank = 10 THEN g.price_10
                WHEN d2.rank = 11 THEN g.price_11
                WHEN d2.rank = 12 THEN g.price_12
                WHEN d2.rank = 13 THEN g.price_13
                WHEN d2.rank = 14 THEN g.price_14
                WHEN d2.rank = 15 THEN g.price_15
                WHEN d2.rank = 16 THEN g.price_16
                ELSE 0
                END )
                END
            )"),
        );
        $start = $last_start_02;
    } else {

        $get_02 = array(
            'sellout'       => new Zend_Db_Expr("COUNT(ts.imei)"),
            'sellout_hero'  => new Zend_Db_Expr("COUNT(CASE WHEN ts.product_id IN (".implode(",", $params['selected_product']).") THEN ts.imei END)"),
            'total_price' => new Zend_Db_Expr("SUM(gkl.price)"),
            'cost_price' => new Zend_Db_Expr(
                "   SUM(
                CASE
                WHEN d2.rank = 1 THEN g.price_1
                WHEN d2.rank = 2 THEN g.price_2
                WHEN d2.rank = 3 THEN g.price_3
                WHEN d2.rank = 4 THEN g.price_4
                WHEN d2.rank = 5 THEN g.price_5
                WHEN d2.rank = 6 THEN g.price_6
                WHEN d2.rank = 7 THEN g.price_7
                WHEN d2.rank = 8 THEN g.price_8
                WHEN d2.rank = 9 THEN g.price_9
                WHEN d2.rank = 10 THEN g.price_10
                WHEN d2.rank = 11 THEN g.price_11
                WHEN d2.rank = 12 THEN g.price_12
                WHEN d2.rank = 13 THEN g.price_13
                WHEN d2.rank = 14 THEN g.price_14
                WHEN d2.rank = 15 THEN g.price_15
                WHEN d2.rank = 16 THEN g.price_16
                ELSE 0
                END
            )"),
        );
        $start = $from;
    }
    $get = $get_01 + $get_02;
    $select = $db->select()
    ->from(array('st' => 'store'), $get)
    ->join(array('o'   => 'org')                , 'st.org_dealer = o.org_id'    , array())
    ->join(array('rm2' => 'regional_market')    , 'st.district = rm2.id'        , array())
    ->join(array('rm'  => 'regional_market')    , 'st.regional_market = rm.id'  , array())
    ->join(array('a'   => 'area')               , 'rm.area_id = a.id'           , array())
    ->joinLeft(array('d' => WAREHOUSE_DB.'.distributor'), 'st.d_id = d.id'      , array())
    ->joinLeft(array('t' => 'timing'),
        "   st.id = t.store
        AND t.created_at >= '".$start." 00:00:00'
        AND t.created_at <= '".$to." 23:59:59'
        ", array())
    ->joinLeft(array('ts'  => 'timing_sale')                , 't.id = ts.timing_id'     , array())
    ->joinLeft(array('i'   => WAREHOUSE_DB.'.imei')         , 'ts.imei = i.imei_sn'     , array())
    ->joinLeft(array('d2'  => WAREHOUSE_DB.'.distributor')  , 'i.distributor_id = d2.id', array())
    ->joinLeft(array('g'   => WAREHOUSE_DB.'.good')         , 'i.good_id = g.id'        , array())
    ->joinLeft(array('gkl' => 'good_kpi_log'),
        "   gkl.good_id = ts.product_id
        AND gkl.color_id = ts.model_id
        AND t.created_at >= CONCAT(gkl.from_date,' 00:00:00')
        AND t.created_at <= CONCAT(gkl.to_date,' 23:59:59')
        ", array())
    ->where('(st.org_dealer = ?', 18)
    ->orWhere('o.store_type_id = ?)', 3)
    ->group('st.id')
    ->order('sellout DESC');
// Filter
    if ( isset($params['id']) && $params['id'] ) {
        $select->where('st.id LIKE ?', '%'.$params['id'].'%');
    }
    if ( isset($params['name']) && $params['name'] ) {
        $select->where('st.name LIKE ?', '%'.$params['name'].'%');
    }
    if ( isset($params['shop_id']) && $params['shop_id'] ) {
        $select->where('st.store_id LIKE ?', '%'.$params['shop_id'].'%');
    }
    if ( isset($params['shop_code']) && $params['shop_code'] ) {
        $select->where('st.store_code LIKE ?', '%'.$params['shop_code'].'%');
    }
    if ( isset($params['it_junction']) && $params['it_junction'] ) {
        $select->where('st.it_junction = ?', $params['it_junction']);
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
// Add Filter Store
    if (isset($params['store']) && $params['store']) {
        if (is_array($params['store']) && count($params['store']))
            $select->where('st.id IN (?)', $params['store']);
        elseif (is_numeric($params['store']))
            $select->where('st.id = ?', intval($params['store']));
        else
            $select->where('1=0', 1);
    }
// Add Filter Store Type
    if (isset($params['org']) && $params['org']) {
        if (is_array($params['org']) && count($params['org']))
            $select->where('st.org_dealer IN (?)', $params['org']);
        elseif (is_numeric($params['org']))
            $select->where('st.org_dealer = ?', intval($params['org']));
        else
            $select->where('1=0', 1);
    }
// Add Filter Market Type, Market Name
    if ( (isset($params['market_type']) and $params['market_type']) || (isset($params['market_name']) && $params['market_name']) ) {
        $select->join( array('sm' => 'store_market'), 'st.id = sm.store_id'         , array());
        $select->join( array('mn' => 'market_name') , 'sm.market_name_id = mn.id'   , array('market_name' => 'mn.name'));
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
// Add Filter Sale More Than
    if (isset($params['sales_from']) && $params['sales_from'])
        $select->having('sellout >= ?', $params['sales_from']);
// Add Filter Sale Less Than
    if (isset($params['sales_from']) && $params['sales_from'])
        $select->having('sellout <= ?', $params['sales_from']);
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
    if (isset($params['bm_id']) && intval($params['bm_id']) > 0) {
        $select->joinRight(array('ssl' => 'store_staff'), 'ssl.store_id=st.id', array());
        $log_where = $this->getAdapter()->quoteInto('ssl.staff_id = ?', $params['bm_id']).
        " AND " . $this->getAdapter()->quoteInto('ssl.is_leader = ?', 3);
        $select->where($log_where);
    }
// echo $select; die;
    $result = $db->fetchAll($select);
    return $result;
}
// Report By Store : Sellout By Model
public function getAllModel($store_id,$params) {

    set_time_limit(0);
    ini_set('memory_limit', '-1');
    error_reporting(~E_ALL);
    ini_set("display_error", '0');
    $db = Zend_Registry::get('db');
    $select = $db->select()
    ->from(array('g' => WAREHOUSE_DB.'.good'), array('good_id' => 'g.id', 'good_name' => 'g.name'));
    $select->join(array('gcc' => WAREHOUSE_DB.'.good_color_combined'), 'g.id = gcc.good_id', array());
    $select->join(array('gc'  => WAREHOUSE_DB.'.good_color'), 'gcc.good_color_id = gc.id', array('color_id' => 'gc.id', 'color_name' => 'gc.name'));
/*
if (!is_null($store_id)) {
$from = date_create_from_format("d/m/Y", $params['from'])->format("Y-m-d");
$to = date_create_from_format("d/m/Y", $params['to'])->format("Y-m-d");
$select->joinLeft(array('ts' => 'timing_sale'), "g.id = ts.product_id AND gc.id = ts.model_id", array());
$select->joinLeft(array('t'  => 'timing'),
"   ts.timing_id = t.id
AND t.store = ".$store_id."
AND DATE(t.created_at) >= '".$from."'
AND DATE(t.created_at) <= '".$to."'
",
array('cnt' => 'COUNT(ts.imei)'));
}
*/
$select->where('g.cat_id = ?', PHONE_CAT_ID);
$select->group(array('g.id','gc.id'));
$select->order('g.name ASC');
//echo $select; die;
$result = $db->fetchAll($select);
return $result;
}
function getSelloutByModel($from, $to) {
    set_time_limit(0);
    ini_set('memory_limit', '-1');
    error_reporting(~E_ALL);
    ini_set("display_error", '0');
    $db = Zend_Registry::get('db');
    $get = array(
        'st_id'     => 't.store',
        'model_id'  => 'ts.product_id',
        'color_id'  => 'ts.model_id',
        'sellout'   => new Zend_Db_Expr("COUNT(ts.imei)"),
    );
    $select = $db->select()
    ->from(array('t' => 'timing'), $get)
    ->join(array('ts' => 'timing_sale'), 't.id = ts.timing_id', array())
    ->where('t.created_at >= ?', $from." 00:00:00")
    ->where('t.created_at <= ?', $to." 23:59:59")
    ->group(array('ts.product_id','ts.model_id','t.store'));
    $data = $db->fetchAll($select);
    $result = array();
    for($i=0;$i<count($data);$i++) {
        $result[ $data[$i]['st_id'] ][ $data[$i]['model_id'] ][ $data[$i]['color_id'] ] = $data[$i]['sellout'];
    }
    return $result;
}
function short_report_by_kpi_pc($params) {
//$params['from'] = '2016-06-01';
//$params['to'] = '2016-06-10';
    $db = Zend_Registry::get('db');
    $select = $db->select()
    ->from(array('gkl' => 'good_kpi_log'), array())
//->join(array('g'  => WAREHOUSE_DB.'.good')      , 'gkl.good_id = g.id'  , array('good_name'  => 'g.name'))
//->join(array('gc' => WAREHOUSE_DB.'.good_color'), 'gkl.color_id = gc.id', array('color_name' => 'gc.name'))
    ->join(array('ts' => 'timing_sale'),
        "   gkl.good_id = ts.product_id
        AND gkl.color_id = ts.model_id
        " ,
        array('imei'  => 'ts.imei'))
    ->join(array('t'  => 'timing'),
        "   ts.timing_id = t.id
        AND t.created_at >= CONCAT(gkl.from_date,' 00:00:00')
        AND t.created_at <= CONCAT(gkl.to_date,' 23:59:59')
        ",
        array('timing_date'  => 't.created_at'))
    ->joinLeft(array('i'  => WAREHOUSE_DB.'.imei'), "ts.imei = i.imei_sn", array('activated_date'  => 'i.activated_date'));
    $select->where('t.staff_id = ?', $params['staff_id']);
    $select->where('t.created_at >= ?', $params['from'].' 00:00:00');
    $select->where('t.created_at <= ?', $params['to'].' 23:59:59');
    if ($params['gkl_id'] != 0) {
        $select->where('gkl.id = ?', $params['gkl_id']);
    }
//echo $select;
    $result = $db->fetchAll($select);
    return $result;
}


function analytic_area($params) {
    set_time_limit(0);
    ini_set('memory_limit', '-1');
    error_reporting(~E_ALL);
    ini_set("display_error", '0');
    $d = explode('/', $params['from']);
    $from = $d[2].'-'.$d[1].'-'.$d[0];
    $d = explode('/', $params['to']);
    $to = $d[2].'-'.$d[1].'-'.$d[0];
    $db = Zend_Registry::get('db');


    // Part //

    $total_sellout = $db->select()
    ->from(array('ts' => HR_DB.'.timing_sale'),array('COUNT(ts.id)'))
    ->joinLeft(array('t' => HR_DB.'.timing'),'ts.timing_id = t.id',array())
    ->joinLeft(array('rm' => HR_DB.'.regional_market'),'t.store_area = rm.id',array())
    ->joinLeft(array('g' => WAREHOUSE_DB.'.good'),'g.id = ts.product_id',array())
    ->where('ts.imei NOT IN (select imei from timing_control)')
    ->where('t.created_at >= ?',$from." 00:00:00")
    ->where('t.created_at <= ?',$to." 23:59:59");

    if (isset($params['area']) && $params['area']) {
        if (is_array($params['area']) && count($params['area']))
            $total_sellout->where('rm.area_id IN (?)', $params['area']);
        elseif (is_numeric($params['area']))
            $total_sellout->where('rm.area_id = ?', intval($params['area']));
        else
            $total_sellout->where('1=0', 1);
    }

    if (isset($params['brand']) && $params['brand']) {
        if (is_array($params['brand']) && count($params['brand']))
            $total_sellout->where('g.brand_id IN (?)', $params['brand']);
        elseif (is_numeric($params['brand']))
            $total_sellout->where('g.brand_id = ?', intval($params['brand']));
        else
            $total_sellout->where('1=0', 1);
    }

    // End Part //

    // Part //

    $total_activated = $db->select()
    ->from(array('ts' => HR_DB.'.timing_sale'),array('COUNT(ts.id)'))
    ->joinLeft(array('t' => HR_DB.'.timing'),'ts.timing_id = t.id',array())
    ->joinLeft(array('i' => WAREHOUSE_DB.'.imei'),'ts.imei = i.imei_sn',array())
    ->joinLeft(array('g' => WAREHOUSE_DB.'.good'),'g.id = ts.product_id',array())
    ->joinLeft(array('rm' => HR_DB.'.regional_market'),'rm.id = t.store_area',array())
    ->where('ts.imei NOT IN (select imei from timing_control)')
    ->where('t.created_at >= ?',$from." 00:00:00")
    ->where('t.created_at <= ?',$to." 23:59:59")
    ->where('i.activated_date IS NOT NULL');


    if (isset($params['area']) && $params['area']) {
        if (is_array($params['area']) && count($params['area']))
            $total_activated->where('rm.area_id IN (?)', $params['area']);
        elseif (is_numeric($params['area']))
            $total_activated->where('rm.area_id = ?', intval($params['area']));
        else
            $total_activated->where('1=0', 1);
    }

    if (isset($params['brand']) && $params['brand']) {
        if (is_array($params['brand']) && count($params['brand']))
            $total_activated->where('g.brand_id IN (?)', $params['brand']);
        elseif (is_numeric($params['brand']))
            $total_activated->where('g.brand_id = ?', intval($params['brand']));
        else
            $total_activated->where('1=0', 1);
    }

    // End Part //

    $sellout = $db->select()
    ->from(array('ts' => HR_DB.'.timing_sale'),array('COUNT(ts.id)'))
    ->joinLeft(array('t' => HR_DB.'.timing'),'ts.timing_id = t.id',array())
    ->joinLeft(array('rm' => HR_DB.'.regional_market'),'t.store_area = rm.id',array())
    ->joinLeft(array('g' => WAREHOUSE_DB.'.good'),'g.id = ts.product_id',array())
    ->where('ts.imei NOT IN (select imei from timing_control)')
    ->where('t.created_at >= ?',$from." 00:00:00")
    ->where('t.created_at <= ?',$to." 23:59:59")
    ->where('t.created_by != 6535')
    ->where('rm.area_id = a.id');

    // Part //

    if (isset($params['area']) && $params['area']) {
        if (is_array($params['area']) && count($params['area']))
            $sellout->where('rm.area_id IN (?)', $params['area']);
        elseif (is_numeric($params['area']))
            $sellout->where('rm.area_id = ?', intval($params['area']));
        else
            $sellout->where('1=0', 1);
    }

    if (isset($params['brand']) && $params['brand']) {
        if (is_array($params['brand']) && count($params['brand']))
            $sellout->where('g.brand_id IN (?)', $params['brand']);
        elseif (is_numeric($params['brand']))
            $sellout->where('g.brand_id = ?', intval($params['brand']));
        else
            $sellout->where('1=0', 1);
    }

    // End Part //

    $oppo_sellout = $db->select()
    ->from(array('ts' => HR_DB.'.timing_sale'),array('COUNT(ts.id)'))
    ->joinLeft(array('t' => HR_DB.'.timing'),'ts.timing_id = t.id',array())
    ->joinLeft(array('i' => WAREHOUSE_DB.'.imei'),'ts.imei = i.imei_sn',array())
    ->joinLeft(array('d' => WAREHOUSE_DB.'.distributor'),'i.distributor_id = d.id',array())
    ->joinLeft(array('rm' => HR_DB.'.regional_market'),'d.region = rm.id',array())
    ->joinLeft(array('g' => WAREHOUSE_DB.'.good'),'g.id = ts.product_id',array())

    ->where('ts.imei NOT IN (select imei from timing_control)')
    ->where('t.created_at >= ?',$from." 00:00:00")
    ->where('t.created_at <= ?',$to." 23:59:59")
    ->where('t.created_by = 6535')
    ->where('rm.area_id = a.id');

    // Part //

    if (isset($params['area']) && $params['area']) {
        if (is_array($params['area']) && count($params['area']))
            $oppo_sellout->where('rm.area_id IN (?)', $params['area']);
        elseif (is_numeric($params['area']))
            $oppo_sellout->where('rm.area_id = ?', intval($params['area']));
        else
            $oppo_sellout->where('1=0', 1);
    }

    if (isset($params['brand']) && $params['brand']) {
        if (is_array($params['brand']) && count($params['brand']))
            $oppo_sellout->where('g.brand_id IN (?)', $params['brand']);
        elseif (is_numeric($params['brand']))
            $oppo_sellout->where('g.brand_id = ?', intval($params['brand']));
        else
            $oppo_sellout->where('1=0', 1);
    }


    // End Part //

    $oppo_sellout_activated = $db->select()
    ->from(array('ts' => HR_DB.'.timing_sale'),array('COUNT(ts.id)'))
    ->joinLeft(array('t' => HR_DB.'.timing'),'ts.timing_id = t.id',array())
    ->joinLeft(array('i' => WAREHOUSE_DB.'.imei'),'ts.imei = i.imei_sn',array())
    ->joinLeft(array('rm' => HR_DB.'.regional_market'),'t.store_area = rm.id',array())
    ->joinLeft(array('g' => WAREHOUSE_DB.'.good'),'g.id = ts.product_id',array())

    ->where('ts.imei NOT IN (select imei from timing_control)')
    ->where('t.created_at >= ?',$from." 00:00:00")
    ->where('t.created_at <= ?',$to." 23:59:59")
    ->where('i.activated_date IS NOT NULL')
    ->where('t.created_by = 6535')
    ->where('rm.area_id = a.id');

    // Part //

    if (isset($params['area']) && $params['area']) {
        if (is_array($params['area']) && count($params['area']))
            $oppo_sellout_activated->where('rm.area_id IN (?)', $params['area']);
        elseif (is_numeric($params['area']))
            $oppo_sellout_activated->where('rm.area_id = ?', intval($params['area']));
        else
            $oppo_sellout_activated->where('1=0', 1);
    }

    if (isset($params['brand']) && $params['brand']) {
        if (is_array($params['brand']) && count($params['brand']))
            $oppo_sellout_activated->where('g.brand_id IN (?)', $params['brand']);
        elseif (is_numeric($params['brand']))
            $oppo_sellout_activated->where('g.brand_id = ?', intval($params['brand']));
        else
            $oppo_sellout_activated->where('1=0', 1);
    }


    // End Part //

    $realme_sellout_activated = $db->select()
    ->from(array('ts' => HR_DB.'.timing_sale'),array('COUNT(ts.id)'))
    ->joinLeft(array('t' => HR_DB.'.timing'),'ts.timing_id = t.id',array())
    ->joinLeft(array('i' => WAREHOUSE_DB.'.imei'),'ts.imei = i.imei_sn',array())
    ->joinLeft(array('rm' => HR_DB.'.regional_market'),'t.store_area = rm.id',array())
    ->joinLeft(array('g' => WAREHOUSE_DB.'.good'),'g.id = ts.product_id',array())

    ->where('ts.imei NOT IN (select imei from timing_control)')
    ->where('t.created_at >= ?',$from." 00:00:00")
    ->where('t.created_at <= ?',$to." 23:59:59")
    ->where('i.activated_date IS NOT NULL')
    ->where('t.created_by != 6535')
    ->where('rm.area_id = a.id');

    // Part //

    if (isset($params['area']) && $params['area']) {
        if (is_array($params['area']) && count($params['area']))
            $realme_sellout_activated->where('rm.area_id IN (?)', $params['area']);
        elseif (is_numeric($params['area']))
            $realme_sellout_activated->where('rm.area_id = ?', intval($params['area']));
        else
            $realme_sellout_activated->where('1=0', 1);
    }

    if (isset($params['brand']) && $params['brand']) {
        if (is_array($params['brand']) && count($params['brand']))
            $realme_sellout_activated->where('g.brand_id IN (?)', $params['brand']);
        elseif (is_numeric($params['brand']))
            $realme_sellout_activated->where('g.brand_id = ?', intval($params['brand']));
        else
            $realme_sellout_activated->where('1=0', 1);
    }

    // End Part //

    $get = array(
        'area_id'   => 'a.id',
        
        'area_name' => 'a.name',

        'total_activated' => new Zend_Db_Expr("(".$total_activated.")"),

        'total_sellout' => new Zend_Db_Expr("(".$total_sellout.")"),

        'sellout'   => new Zend_Db_Expr("(".$sellout.")"),

        'oppo_sellout' => new Zend_Db_Expr("(".$oppo_sellout.")"),

        'oppo_activated' => new Zend_Db_Expr("(".$oppo_sellout_activated.")"),

        'realme_activated' => new Zend_Db_Expr("(".$realme_sellout_activated.")"),

        // 'activated_pc' => new Zend_Db_Expr(
        //     "   COUNT(
        //     CASE WHEN
        //     i.activated_date IS NOT NULL
        //     AND DATE(i.activated_date) <= DATE_FORMAT(DATE(t.created_at) + INTERVAL 7 DAY, '%Y-%m-%d')
        //     AND DATE(i.activated_date) >= DATE_FORMAT(DATE(t.created_at), '%Y-%m-%d')
        //     THEN i.imei_sn END )
        //     "),
        // 'activated_sale' => new Zend_Db_Expr(
        //     "   COUNT(
        //     CASE WHEN
        //     i.activated_date IS NOT NULL
        //     AND DATE(i.activated_date) <= DATE_FORMAT(DATE(t.created_at) + INTERVAL 7 DAY, '%Y-%m-%d')
        //     AND DATE(i.activated_date) >= DATE_FORMAT(DATE(t.created_at) - INTERVAL 3 DAY, '%Y-%m-%d')
        //     THEN i.imei_sn END )
        //     "),
    );
    $select = $db->select()
    ->from(array('a' => HR_DB.'.area'),$get)
    ->joinLeft(array('rm' => HR_DB.'.regional_market'),'a.id = rm.area_id',array())
    ->order('a.name ASC');

    if ( isset($params['export']) && $params['export'] == 1 ) {
        $select->order('a.name ASC');
    } else {
        $select->order('a.name ASC');
    }


    if (isset($params['area']) && $params['area']) {
        if (is_array($params['area']) && count($params['area']))
            $select->where('a.id IN (?)', $params['area']);
        elseif (is_numeric($params['area']))
            $select->where('a.id = ?', intval($params['area']));
        else
            $select->where('1=0', 1);
    }

    if (isset($params['list_regions']) && $params['list_regions']) {
        if (is_array($params['list_regions']) && count($params['list_regions']))
            $select->where('a.id IN (?)', $params['list_regions']);
        elseif (is_numeric($params['list_regions']))
            $select->where('a.id = ?', intval($params['list_regions']));
        else
            $select->where('1=0', 1);
    }

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

    if (!isset($params['total_sales'])) {

        $select->group('a.id');
    }

    // echo $select; die;
    $result = $db->fetchAll($select);
    return $result;
}

// chart timing area------------------------------------------
function chart_timing_month() {

    $params = array(
        'sort'   => '',
        'desc'   => 1,
        'from'   => date('1/m/Y'),
        'to'     => date('d/m/Y'),
        'area'   => '',
        'export' => 0,
    );

    set_time_limit(0);
    ini_set('memory_limit', '-1');
    error_reporting(~E_ALL);
    ini_set("display_error", '0');
    $d = explode('/', $params['from']);
    $from = $d[2].'-'.$d[1].'-'.$d[0];
    $d = explode('/', $params['to']);
    $to = $d[2].'-'.$d[1].'-'.$d[0];
    $db = Zend_Registry::get('db');


    $get = array(
        'area_id'   => 'a.id',
        'area_name' => 'a.name',

        'realme'   => new Zend_Db_Expr("COUNT(CASE WHEN t.created_by != 6535 THEN ts.imei END)"),

        'oppo' => new Zend_Db_Expr("COUNT(CASE WHEN t.created_by = 6535 THEN i.imei_sn END)"),

        'realme_activated' => new Zend_Db_Expr("COUNT(CASE WHEN t.created_by != 6535 and i.activated_date IS NOT NULL THEN ts.imei END)"),
        'oppo_activated' => new Zend_Db_Expr("COUNT(CASE WHEN t.created_by = 6535 and i.activated_date IS NOT NULL THEN ts.imei END)"),

        'realme_not_activated' => new Zend_Db_Expr("COUNT(CASE WHEN t.created_by != 6535 and i.activated_date IS NULL THEN ts.imei END)"),
        'oppo_not_activated' => new Zend_Db_Expr("COUNT(CASE WHEN t.created_by = 6535 and i.activated_date IS NULL THEN ts.imei END)"),
    );

    $select = $db->select()
    ->from(array('t' => HR_DB.'.timing'),$get)
    ->join(array('ts' => HR_DB.'.timing_sale'),'ts.timing_id = t.id',array())
    ->joinLeft(array('i' => WAREHOUSE_DB.'.imei'),'i.imei_sn = ts.imei',array())
    ->joinLeft(array('d' => WAREHOUSE_DB.'.distributor'),'d.id = i.distributor_id',array())
    ->joinLeft(array('rm' => HR_DB.'.regional_market'),'rm.id = d.region',array())
    ->joinLeft(array('a' => HR_DB.'.area'),'a.id = rm.area_id',array())
    ->where('ts.imei NOT IN (select imei from hr.timing_control)')
    ->where('t.created_at >= ?',$from.' 00:00:00')
    ->where('t.created_at <= ?',$to.' 23:59:59');

    $select->group('a.id');


    // echo $select; die;
    $result = $db->fetchAll($select);
    // print_r($result); 
    return $result;
}

function chart_timing_week() {
    $todate = date('d-m-Y');
    $toyear = date('Y');
    $numweek = date("W", strtotime($todate));
    $datefrom = date( "d/m/Y", strtotime($toyear."W".$numweek."1") );
    $dateto = date( "d/m/Y", strtotime($toyear."W".$numweek."7") );
    $params = array(
        'sort'   => '',
        'desc'   => 1,
        'from'   => $datefrom,
        'to'     => $dateto,
        'area'   => '',
        'export' => 0,
    );
    set_time_limit(0);
    ini_set('memory_limit', '-1');
    error_reporting(~E_ALL);
    ini_set("display_error", '0');
    $d = explode('/', $params['from']);
    $from = $d[2].'-'.$d[1].'-'.$d[0];
    $d = explode('/', $params['to']);
    $to = $d[2].'-'.$d[1].'-'.$d[0];

    $db = Zend_Registry::get('db');
    $get = array(
        'area_id'   => 'a.id',
        'area_name' => 'a.name',
        'sellout'   => new Zend_Db_Expr("COUNT(ts.imei)"),
        'activated' => new Zend_Db_Expr("COUNT( CASE WHEN i.activated_date IS NOT NULL THEN i.imei_sn END )"),
        'not_activated' => new Zend_Db_Expr("COUNT( CASE WHEN i.activated_date IS NULL THEN i.imei_sn END )"),
        'activated_pc' => new Zend_Db_Expr(
            "   COUNT(
            CASE WHEN
            i.activated_date IS NOT NULL
            AND DATE(i.activated_date) <= DATE_FORMAT(DATE(t.created_at) + INTERVAL 7 DAY, '%Y-%m-%d')
            AND DATE(i.activated_date) >= DATE_FORMAT(DATE(t.created_at), '%Y-%m-%d')
            THEN i.imei_sn END )
            "),
        'activated_sale' => new Zend_Db_Expr(
            "   COUNT(
            CASE WHEN
            i.activated_date IS NOT NULL
            AND DATE(i.activated_date) <= DATE_FORMAT(DATE(t.created_at) + INTERVAL 7 DAY, '%Y-%m-%d')
            AND DATE(i.activated_date) >= DATE_FORMAT(DATE(t.created_at) - INTERVAL 3 DAY, '%Y-%m-%d')
            THEN i.imei_sn END )
            "),
    );
    $select = $db->select()
    ->from(array('st' => 'store'), $get)
    ->join(array('o'  => 'org'), 'st.org_dealer = o.org_id', array())
    ->join(array('rm' => 'regional_market'), 'st.regional_market = rm.id', array())
    ->join(array('a' => 'area'), 'rm.area_id = a.id', array())
    ->joinLeft(array('t' => 'timing'),
        "   st.id = t.store
        AND t.created_at >= '".$from." 00:00:00'
        AND t.created_at <= '".$to." 23:59:59'
        "
        , array())
    ->joinLeft(array('ts' => 'timing_sale'), 't.id = ts.timing_id', array())
    ->joinLeft(array('i' => WAREHOUSE_DB.'.imei'), 'ts.imei = i.imei_sn', array())
    ->where('ts.imei NOT IN (select imei from timing_control)');

    $select->order('a.name ASC');
    $select->group('a.id');

//echo $select;
    $result = $db->fetchAll($select);
    return $result;
}

function chart_timing_day() {
    $params = array(
        'sort'   => '',
        'desc'   => 1,
        'from'   => date('d/m/Y'),
        'to'     => date('d/m/Y'),
        'area'   => '',
        'export' => 0,
    );

    set_time_limit(0);
    ini_set('memory_limit', '-1');
    error_reporting(~E_ALL);
    ini_set("display_error", '0');
    $d = explode('/', $params['from']);
    $from = $d[2].'-'.$d[1].'-'.$d[0];
    $d = explode('/', $params['to']);
    $to = $d[2].'-'.$d[1].'-'.$d[0];
    $db = Zend_Registry::get('db');


    $get = array(
        'area_id'   => 'a.id',
        'area_name' => 'a.name',

        'realme'   => new Zend_Db_Expr("COUNT(CASE WHEN t.created_by != 6535 THEN ts.imei END)"),

        'oppo' => new Zend_Db_Expr("COUNT(CASE WHEN t.created_by = 6535 THEN i.imei_sn END)"),

        'realme_activated' => new Zend_Db_Expr("COUNT(CASE WHEN t.created_by != 6535 and i.activated_date IS NOT NULL THEN ts.imei END)"),
        'oppo_activated' => new Zend_Db_Expr("COUNT(CASE WHEN t.created_by = 6535 and i.activated_date IS NOT NULL THEN ts.imei END)"),

        'realme_not_activated' => new Zend_Db_Expr("COUNT(CASE WHEN t.created_by != 6535 and i.activated_date IS NULL THEN ts.imei END)"),
        'oppo_not_activated' => new Zend_Db_Expr("COUNT(CASE WHEN t.created_by = 6535 and i.activated_date IS NULL THEN ts.imei END)"),
    );

    $select = $db->select()
    ->from(array('t' => HR_DB.'.timing'),$get)
    ->join(array('ts' => HR_DB.'.timing_sale'),'ts.timing_id = t.id',array())
    ->joinLeft(array('i' => WAREHOUSE_DB.'.imei'),'i.imei_sn = ts.imei',array())
    ->joinLeft(array('d' => WAREHOUSE_DB.'.distributor'),'d.id = i.distributor_id',array())
    ->joinLeft(array('rm' => HR_DB.'.regional_market'),'rm.id = d.region',array())
    ->joinLeft(array('a' => HR_DB.'.area'),'a.id = rm.area_id',array())
    ->where('ts.imei NOT IN (select imei from hr.timing_control)')
    ->where('t.created_by != 18')
    ->where('t.created_at >= ?',$from.' 00:00:00')
    ->where('t.created_at <= ?',$to.' 23:59:59');

    $select->group('a.id');


    // echo $select; die;
    $result = $db->fetchAll($select);
    // print_r($result); 
    return $result;
}



function analytic_imei($params) {
    set_time_limit(0);
    ini_set('memory_limit', '-1');
    error_reporting(~E_ALL);
    ini_set("display_error", '0');

    // Change Date Format
    $d = explode('/', $params['from']);
    $from = $d[2].'-'.$d[1].'-'.$d[0];
    $d = explode('/', $params['to']);
    $to = $d[2].'-'.$d[1].'-'.$d[0];
    $db = Zend_Registry::get('db');

    // Check ASM of this imei for ABM
    $sub_select_01 = $db->select()
    ->from(array('asm' => 'asm'), array('bal.id'))
    ->join(array('s3'  => 'staff')      , 'asm.staff_id = s3.id AND s3.group_id IN (5,16)', array())
    ->join(array('bal' => 'bs_area_log'), 's3.id = bal.staff_id', array())
    ->where('a.id = asm.area_id', 1)
    ->where('bal.from_date <= t.created_at', 1)
    ->where('(bal.to_date >= t.created_at', 1)
    ->orWhere('bal.to_date IS NULL)', 1)
    ->limit(1);

    /*
    
    $sub_select_02 = $db->select()
    ->from(array('ps' => WAREHOUSE_DB.'.packed_sim'), array('ps.imei_sn'))
    ->where('ps.imei_sn = i.imei_sn', 1);
    */

    $get = array(
        'area_id'           => 'a.id',
        'area_name'         => 'a.name',
        'province_name'     => 'rm.name',
        // 'district_name'     => 'rm2.name',
        'sub_area_name'     => 'sa.name',
        'sub_district_name' => 'sd.name',
        'oppo_id'           => 'st.oppo_id',
        'company'           => 's.company_id',
        // 'geo_name'          => 'geo.name_en',
        // 'th_province_name'  => 'tp.name_en',
        'store_code'        => 'st.store_code',
        'st_id'             => 'st.id',
        'st_name'           => 'st.name',
        'st_del'            => new Zend_Db_Expr("(CASE WHEN st.del = 1 THEN 'Closed' ELSE 'Active' END)"),
        'st_rank'           => 'st.rank',
        'st_org_id'         => 'o.org_id',
        'st_type'           => 'o.org_name',
        'st_type_id'        => 'o.store_type_id',
        // 'st_operation'      => 'so.name',
        'st_level'          => 'st.store_grade',
        'st_shop_id'        => 'st.store_id',
        'st_shop_code'      => 'st.store_code',
        'brand_name'        => 'b.name',
        'st_d_id'           => 'd1.id',
        'st_d_name'         => 'd1.title',
        'leader_code'       => 's2.code',
        'leader_name'       => new Zend_Db_Expr("CONCAT(s2.firstname, ' ' , s2.lastname)"),
        'pcm_code'       => 's3.code',
        'pcm_name'       => new Zend_Db_Expr("CONCAT(s3.firstname, ' ' , s3.lastname)"),
        'stock_code'       => 's4.code',
        'stock_name'       => new Zend_Db_Expr("CONCAT(s4.firstname, ' ' , s4.lastname)"),
        'asm_code'       => 's5.code',
        'asm_name'       => new Zend_Db_Expr("CONCAT(s5.firstname, ' ' , s5.lastname)"),
        'leader_group'      => 'gr2.name',
        'reporter_code'     => 's.code',
        'sale_id'           => 't.sales_id',
        'cus_name'          => 'cus.name',
        'cus_phone'          => 'cus.phone_number',
        'province'          => 'rm3.name',
        'reporter_id'       => 's.id',
        'reporter_name'     => new Zend_Db_Expr("CONCAT(s.firstname, ' ' , s.lastname)"),
        'reporter_group_id' => 'gr.id',
        'reporter_group'    => 'gr.name',
        'reporter_off_date' => 's.off_date',
        'reporter_joined_at'=> 's.joined_at',
        'reporter_created_date' => 's.created_at',
        'reporter_pc_stand_by'  => 's.pc_stand_by',
        'model_id'          => 'g.id',
        'cat_name'          => 'cat.name',
        'model'             => 'g.name',
        'model_desc'        => 'g.desc',
        'color'             => 'gc.name',
        'imei'              => 'i.imei_sn',
        'model_type'        => 'i.type',
        'out_price'         => 'ts.out_price',
        'sales_price'         => 'ts.sales_price',
        'imei_cpo'          => 'cpo.imei',
        'sale_off_percent'              => 'mk.sale_off_percent',
        'timing_id'              => 't.id',
        'timing_date'       => 't.created_at',
        'remark_reporter'       => 't.note',
        'oppo_shop'          => 't.oppo_shop',
        'oppo_shop_id'      => 't.oppo_shop_id',
        'activated_date'    => 'i.activated_date',

    // For OPPO PC Timing
        'oppo_pc_provience' => 'rm5.name',
        'oppo_pc_area'      => 'a2.name',

        'imei_d_id'         => 'd2.id',
	'imei_d_code'	    => 'd2.distributor_code',
        'imei_d_name'       => 'd2.title',
	'superior_d_name'       => 'd3.title',
        'imei_d_ka_type'    => 'o2.org_name',
        'warehouse_name'    => 'wh.name',
        'pre_order'         => new Zend_Db_Expr("(CASE WHEN ts.pre_order_status = 1 THEN 'Yes' ELSE 'No' END)"),
        'warrant_no'        => 'ts.warrant_no',
    //'sim_locked'        => new Zend_Db_Expr("( CASE WHEN (".$sub_select_02.") IS NOT NULL THEN 'Yes' ELSE 'No' END)"),
        'asm_flag'          => new Zend_Db_Expr("(".$sub_select_01.")"),
        'score'             => 'gkl.kpi',
    //'com_rate_pc'       => 'gkl.com_rate',
    //'com_rate_pc_aec'   => 'gkl.com_rate_aec',
        'com_rate_pc'       => new Zend_Db_Expr(
            "(
            CASE
            WHEN
            i.distributor_id = 11293
            AND i.good_id = 140
            AND t.created_at >= '2017-03-22 00:00:00'
            AND t.created_at <= '2017-03-31 23:59:59'
            THEN
            '20'
            WHEN
            t.created_at >= '2017-12-01 00:00:00'
            AND t.created_at <= '2017-12-07 23:59:59'
            AND t.staff_id = 10458
            THEN
            0
            WHEN
            t.created_at >= '2018-01-01 00:00:00'
            AND t.created_at <= '2018-01-27 23:59:59'
            AND t.staff_id IN (9712, 15997, 30952, 30962)
            THEN
            0
            WHEN
            t.created_at >= '2018-04-25 00:00:00'
            AND t.created_at <= '2018-04-26 23:59:59'
            AND ts.product_id = 310
            AND ts.pre_order_status = 1
            AND ts.warrant_no IS NOT NULL
            THEN
            400
            WHEN
            t.created_at >= '2018-04-27 00:00:00'
            AND t.created_at <= '2018-04-30 23:59:59'
            AND ts.product_id = 310
            AND ts.pre_order_status = 1
            AND ts.warrant_no IS NOT NULL
            AND t.store IN (
            17693,12138,10382,4232,12049,9078,16568,13548,20801,8560,
            4917,13710,11632,13314,1925,9696,9189,9176,11998,1534,
            6,1012,459,21079,7827,456,17365,768,16411,16,
            13458,1363,6037,9448,7077,39,5441,1718,6242,4581,
            17400,1720,5832,8680,1529,20609,9177,6505,1920,15243,
            956,13228,1719,17458)
            THEN
            400
            ELSE
            gkl.com_rate
            END
        )"),
        'com_rate_pc_aec'   => new Zend_Db_Expr(
            "(
            CASE
            WHEN
            i.distributor_id = 11293
            AND i.good_id = 140
            AND t.created_at >= '2017-03-22 00:00:00'
            AND t.created_at <= '2017-03-31 23:59:59'
            THEN
            '30'
            WHEN
            t.created_at >= '2017-12-01 00:00:00'
            AND t.created_at <= '2017-12-07 23:59:59'
            AND t.staff_id = 10458
            THEN
            0
            WHEN
            t.created_at >= '2018-01-01 00:00:00'
            AND t.created_at <= '2018-01-27 23:59:59'
            AND t.staff_id IN (9712, 15997, 30952, 30962)
            THEN
            0
            ELSE
            gkl.com_rate_aec
            END
        )"),
        'price'             => 'gkl.price',
        'com_rate_sale'     => new Zend_Db_Expr(
            "(
            CASE
            WHEN t.created_at <= '2017-02-28 23:59:59' THEN
            CASE WHEN sa.com_rate = 2 THEN
            CASE WHEN FIND_IN_SET(i.good_id,'128,142') THEN '120' ELSE '20' END
            ELSE
            CASE WHEN FIND_IN_SET(i.good_id,'128,142') THEN '80' ELSE '10' END
            END
            WHEN t.created_at >= '2017-03-01 00:00:00' AND t.created_at <= '2017-03-17 23:59:59' THEN
            CASE WHEN sa.com_rate = 2 THEN
            CASE WHEN FIND_IN_SET(i.good_id,'142,208') THEN '120' ELSE '20' END
            ELSE
            CASE WHEN FIND_IN_SET(i.good_id,'142,208') THEN '80' ELSE '10' END
            END
            WHEN t.created_at >= '2017-03-18 00:00:00' AND t.created_at <= '2017-10-31 23:59:59' THEN
            CASE WHEN sa.com_rate = 2 THEN
            CASE WHEN FIND_IN_SET(i.good_id,'208,210') THEN '120' ELSE '20' END
            ELSE
            CASE WHEN FIND_IN_SET(i.good_id,'208,210') THEN '80' ELSE '10' END
            END
            WHEN t.created_at >= '2017-11-01 00:00:00' AND t.created_at <= '2017-11-30 23:59:59' THEN
            CASE WHEN sa.com_rate = 2 THEN
            CASE WHEN FIND_IN_SET(i.good_id,'208,299') THEN '60' ELSE '20' END
            ELSE
            CASE WHEN FIND_IN_SET(i.good_id,'208,299') THEN '40' ELSE '10' END
            END
            WHEN t.created_at >= '2017-12-01 00:00:00' AND t.created_at <= '2018-03-31 23:59:59' THEN
            CASE WHEN sa.com_rate = 2 THEN
            CASE WHEN FIND_IN_SET(i.good_id,'299,301') THEN '60' ELSE '20' END
            ELSE
            CASE WHEN FIND_IN_SET(i.good_id,'299,301') THEN '40' ELSE '10' END
            END
            WHEN t.created_at >= '2018-04-01 00:00:00' AND t.created_at <= '2018-04-30 23:59:59' THEN
            CASE WHEN sa.com_rate = 2 THEN
            CASE WHEN FIND_IN_SET(i.good_id,'299,301,310,311') THEN '60' ELSE '20' END
            ELSE
            CASE WHEN FIND_IN_SET(i.good_id,'299,301,310,311') THEN '40' ELSE '10' END
            END
            WHEN t.created_at >= '2018-05-01 00:00:00' AND t.created_at <= '2018-06-30 23:59:59' THEN
            CASE WHEN sa.com_rate = 2 THEN
            CASE WHEN FIND_IN_SET(i.good_id,'310,311,312') THEN '60' ELSE '20' END
            ELSE
            CASE WHEN FIND_IN_SET(i.good_id,'310,311,312') THEN '40' ELSE '10' END
            END
            WHEN t.created_at >= '2018-07-01 00:00:00' AND t.created_at <= '2018-07-31 23:59:59' THEN
            CASE WHEN sa.com_rate = 2 THEN
            CASE WHEN FIND_IN_SET(i.good_id,'310,311') THEN '60' ELSE '20' END
            ELSE
            CASE WHEN FIND_IN_SET(i.good_id,'310,311') THEN '40' ELSE '10' END
            END
            WHEN t.created_at >= '2018-08-01 00:00:00' AND t.created_at <= '2018-08-31 23:59:59' THEN
            CASE WHEN sa.com_rate = 2 THEN
            CASE WHEN FIND_IN_SET(i.good_id,'310,345') THEN '60' ELSE '20' END
            ELSE
            CASE WHEN FIND_IN_SET(i.good_id,'310,345') THEN '40' ELSE '10' END
            END
            WHEN t.created_at >= '2018-09-01 00:00:00' AND t.created_at <= '2018-11-30 23:59:59' THEN
            CASE WHEN sa.com_rate = 2 THEN
            CASE WHEN FIND_IN_SET(i.good_id,'345') THEN '60' ELSE '20' END
            ELSE
            CASE WHEN FIND_IN_SET(i.good_id,'345') THEN '40' ELSE '10' END
            END
            WHEN t.created_at >= '2018-12-01 00:00:00' AND t.created_at <= '2019-02-10 23:59:59' THEN
            CASE WHEN sa.com_rate = 2 THEN
            CASE WHEN FIND_IN_SET(i.good_id,'345,353') THEN '60' ELSE '20' END
            ELSE
            CASE WHEN FIND_IN_SET(i.good_id,'345,353') THEN '40' ELSE '10' END
            END
            WHEN t.created_at >= '2019-02-11 00:00:00' AND t.created_at <= '2019-02-28 23:59:59' THEN
            CASE
            WHEN i.good_id = 353 THEN
            '100'
            WHEN sa.com_rate = 2 THEN
            CASE WHEN FIND_IN_SET(i.good_id,'345') THEN '60' ELSE '20' END
            ELSE
            CASE WHEN FIND_IN_SET(i.good_id,'345') THEN '40' ELSE '10' END
            END
            WHEN t.created_at >= '2019-03-01 00:00:00' AND t.created_at <= '2019-04-30 23:59:59' THEN
            CASE
            WHEN i.good_id = 353 THEN
            '100'
            WHEN sa.com_rate = 2 THEN
            CASE WHEN FIND_IN_SET(i.good_id,'345,371') THEN '60' ELSE '20' END
            ELSE
            CASE WHEN FIND_IN_SET(i.good_id,'345,371') THEN '40' ELSE '10' END
            END
            ELSE
            CASE
            WHEN i.good_id IN (353,399,403) THEN
            '100'
            WHEN sa.com_rate = 2 THEN
            CASE WHEN FIND_IN_SET(i.good_id,'371,390,392,395') THEN '60' ELSE '20' END
            ELSE
            CASE WHEN FIND_IN_SET(i.good_id,'371,390,392,395') THEN '40' ELSE '10' END
            END
            END
        )"),
'com_rate_asm'     => new Zend_Db_Expr(
    "(
    CASE
    WHEN t.created_at <= '2017-02-28 23:59:59' THEN
    CASE
    WHEN a.id >= 73 AND a.id <= 117 THEN
    CASE WHEN FIND_IN_SET(i.good_id,'128,142') THEN '30' ELSE '10' END
    ELSE
    CASE WHEN FIND_IN_SET(i.good_id,'128,142') THEN '60' ELSE '15' END
    END
    WHEN t.created_at >= '2017-03-01 00:00:00' AND t.created_at <= '2017-03-17 23:59:59' THEN
    CASE
    WHEN a.id >= 73 AND a.id <= 117 THEN
    CASE WHEN FIND_IN_SET(i.good_id,'142,208') THEN '30' ELSE '10' END
    ELSE
    CASE WHEN FIND_IN_SET(i.good_id,'142,208') THEN '60' ELSE '15' END
    END
    WHEN t.created_at >= '2017-03-18 00:00:00' AND t.created_at <= '2017-10-31 23:59:59' THEN
    CASE
    WHEN a.id >= 73 AND a.id <= 117 THEN
    CASE WHEN FIND_IN_SET(i.good_id,'208,210') THEN '30' ELSE '10' END
    ELSE
    CASE WHEN FIND_IN_SET(i.good_id,'208,210') THEN '60' ELSE '15' END
    END
    WHEN t.created_at >= '2017-11-01 00:00:00' AND t.created_at <= '2017-11-30 23:59:59' THEN
    CASE
    WHEN a.id >= 73 AND a.id <= 117 THEN
    CASE WHEN FIND_IN_SET(i.good_id,'208,299') THEN '20' ELSE '10' END
    ELSE
    CASE WHEN FIND_IN_SET(i.good_id,'208,299') THEN '30' ELSE '15' END
    END
    WHEN t.created_at >= '2017-12-01 00:00:00' AND t.created_at <= '2018-03-31 23:59:59' THEN
    CASE
    WHEN a.id >= 73 AND a.id <= 117 THEN
    CASE WHEN FIND_IN_SET(i.good_id,'299,301') THEN '20' ELSE '10' END
    ELSE
    CASE WHEN FIND_IN_SET(i.good_id,'299,301') THEN '30' ELSE '15' END
    END
    WHEN t.created_at >= '2018-04-01 00:00:00' AND t.created_at <= '2018-04-30 23:59:59' THEN
    CASE
    WHEN a.id >= 73 AND a.id <= 117 THEN
    CASE WHEN FIND_IN_SET(i.good_id,'299,301,310,311') THEN '20' ELSE '10' END
    ELSE
    CASE WHEN FIND_IN_SET(i.good_id,'299,301,310,311') THEN '30' ELSE '15' END
    END
    WHEN t.created_at >= '2018-05-01 00:00:00' AND t.created_at <= '2018-06-30 23:59:59' THEN
    CASE
    WHEN a.id >= 73 AND a.id <= 117 THEN
    CASE WHEN FIND_IN_SET(i.good_id,'310,311,312') THEN '20' ELSE '10' END
    ELSE
    CASE WHEN FIND_IN_SET(i.good_id,'310,311,312') THEN '30' ELSE '15' END
    END
    WHEN t.created_at >= '2018-07-01 00:00:00' AND t.created_at <= '2018-07-31 23:59:59' THEN
    CASE
    WHEN a.id >= 73 AND a.id <= 117 THEN
    CASE WHEN FIND_IN_SET(i.good_id,'310,311') THEN '20' ELSE '10' END
    ELSE
    CASE WHEN FIND_IN_SET(i.good_id,'310,311') THEN '30' ELSE '15' END
    END
    WHEN t.created_at >= '2018-08-01 00:00:00' AND t.created_at <= '2018-08-31 23:59:59' THEN
    CASE
    WHEN a.id >= 73 AND a.id <= 117 THEN
    CASE WHEN FIND_IN_SET(i.good_id,'310,345') THEN '20' ELSE '10' END
    ELSE
    CASE WHEN FIND_IN_SET(i.good_id,'310,345') THEN '30' ELSE '15' END
    END
    WHEN t.created_at >= '2018-09-01 00:00:00' AND t.created_at <= '2018-11-30 23:59:59' THEN
    CASE
    WHEN a.id >= 73 AND a.id <= 117 THEN
    CASE WHEN FIND_IN_SET(i.good_id,'345') THEN '20' ELSE '10' END
    ELSE
    CASE WHEN FIND_IN_SET(i.good_id,'345') THEN '30' ELSE '15' END
    END
    WHEN t.created_at >= '2018-12-01 00:00:00' AND t.created_at <= '2019-02-28 23:59:59' THEN
    CASE
    WHEN a.id >= 73 AND a.id <= 117 THEN
    CASE WHEN FIND_IN_SET(i.good_id,'345,353') THEN '20' ELSE '10' END
    ELSE
    CASE WHEN FIND_IN_SET(i.good_id,'345,353') THEN '30' ELSE '15' END
    END
    WHEN t.created_at >= '2019-03-01 00:00:00' AND t.created_at <= '2019-04-30 23:59:59' THEN
    CASE
    WHEN a.id >= 73 AND a.id <= 117 THEN
    CASE WHEN FIND_IN_SET(i.good_id,'345,353,371') THEN '20' ELSE '10' END
    ELSE
    CASE WHEN FIND_IN_SET(i.good_id,'345,353,371') THEN '30' ELSE '15' END
    END
    ELSE
    CASE
    WHEN a.id >= 73 AND a.id <= 117 THEN
    CASE WHEN FIND_IN_SET(i.good_id,'353,399,403,371,390,392,395') THEN '20' ELSE '10' END
    ELSE
    CASE WHEN FIND_IN_SET(i.good_id,'353,399,403,371,390,392,395') THEN '30' ELSE '15' END
    END

    END
)"),
'com_rate_rm'     => new Zend_Db_Expr(
    "(
    CASE
    WHEN t.created_at <= '2017-02-28 23:59:59' THEN
    CASE
    WHEN a.id >= 73 AND a.id <= 117 THEN
    CASE WHEN FIND_IN_SET(i.good_id,'128,142') THEN '20' ELSE '6' END
    ELSE
    CASE WHEN FIND_IN_SET(i.good_id,'128,142') THEN '40' ELSE '10' END
    END
    WHEN t.created_at >= '2017-03-01 00:00:00' AND t.created_at <= '2017-03-17 23:59:59' THEN
    CASE
    WHEN a.id >= 73 AND a.id <= 117 THEN
    CASE WHEN FIND_IN_SET(i.good_id,'142,208') THEN '20' ELSE '6' END
    ELSE
    CASE WHEN FIND_IN_SET(i.good_id,'142,208') THEN '40' ELSE '10' END
    END
    WHEN t.created_at >= '2017-03-18 00:00:00' AND t.created_at <= '2017-10-31 23:59:59' THEN
    CASE
    WHEN a.id >= 73 AND a.id <= 117 THEN
    CASE WHEN FIND_IN_SET(i.good_id,'208,210') THEN '30' ELSE '10' END
    ELSE
    CASE WHEN FIND_IN_SET(i.good_id,'208,210') THEN '30' ELSE '10' END
    END
    WHEN t.created_at >= '2017-11-01 00:00:00' AND t.created_at <= '2017-11-30 23:59:59' THEN
    CASE
    WHEN a.id >= 73 AND a.id <= 117 THEN
    CASE WHEN FIND_IN_SET(i.good_id,'208,299') THEN '15' ELSE '10' END
    ELSE
    CASE WHEN FIND_IN_SET(i.good_id,'208,299') THEN '20' ELSE '10' END
    END
    WHEN t.created_at >= '2017-12-01 00:00:00' AND t.created_at <= '2018-03-31 23:59:59' THEN
    CASE
    WHEN a.id >= 73 AND a.id <= 117 THEN
    CASE WHEN FIND_IN_SET(i.good_id,'299,301') THEN '15' ELSE '10' END
    ELSE
    CASE WHEN FIND_IN_SET(i.good_id,'299,301') THEN '20' ELSE '10' END
    END
    WHEN t.created_at >= '2018-04-01 00:00:00' AND t.created_at <= '2018-04-30 23:59:59' THEN
    CASE
    WHEN a.id >= 73 AND a.id <= 117 THEN
    CASE WHEN FIND_IN_SET(i.good_id,'299,301,310,311') THEN '15' ELSE '10' END
    ELSE
    CASE WHEN FIND_IN_SET(i.good_id,'299,301,310,311') THEN '20' ELSE '10' END
    END
    WHEN t.created_at >= '2018-05-01 00:00:00' AND t.created_at <= '2018-06-30 23:59:59' THEN
    CASE
    WHEN a.id >= 73 AND a.id <= 117 THEN
    CASE WHEN FIND_IN_SET(i.good_id,'310,311,312') THEN '15' ELSE '10' END
    ELSE
    CASE WHEN FIND_IN_SET(i.good_id,'310,311,312') THEN '20' ELSE '10' END
    END
    WHEN t.created_at >= '2018-07-01 00:00:00' AND t.created_at <= '2018-07-31 23:59:59' THEN
    CASE
    WHEN a.id >= 73 AND a.id <= 117 THEN
    CASE WHEN FIND_IN_SET(i.good_id,'310,311') THEN '15' ELSE '10' END
    ELSE
    CASE WHEN FIND_IN_SET(i.good_id,'310,311') THEN '20' ELSE '10' END
    END
    WHEN t.created_at >= '2018-08-01 00:00:00' AND t.created_at <= '2018-08-31 23:59:59' THEN
    CASE
    WHEN a.id >= 73 AND a.id <= 117 THEN
    CASE WHEN FIND_IN_SET(i.good_id,'310,345') THEN '15' ELSE '10' END
    ELSE
    CASE WHEN FIND_IN_SET(i.good_id,'310,345') THEN '20' ELSE '10' END
    END
    WHEN t.created_at >= '2018-09-01 00:00:00' AND t.created_at <= '2018-11-31 23:59:59' THEN
    CASE
    WHEN a.id >= 73 AND a.id <= 117 THEN
    CASE WHEN FIND_IN_SET(i.good_id,'345') THEN '15' ELSE '10' END
    ELSE
    CASE WHEN FIND_IN_SET(i.good_id,'345') THEN '20' ELSE '10' END
    END
    WHEN t.created_at >= '2018-12-01 00:00:00' AND t.created_at <= '2019-02-28 23:59:59' THEN
    CASE
    WHEN a.id >= 73 AND a.id <= 117 THEN
    CASE WHEN FIND_IN_SET(i.good_id,'345') THEN '15' ELSE '10' END
    ELSE
    CASE WHEN FIND_IN_SET(i.good_id,'345') THEN '20' ELSE '10' END
    END
    WHEN t.created_at >= '2019-03-01 00:00:00' AND t.created_at <= '2019-04-30 23:59:59' THEN
    CASE
    WHEN a.id >= 73 AND a.id <= 117 THEN
    CASE WHEN FIND_IN_SET(i.good_id,'345,353,371') THEN '15' ELSE '10' END
    ELSE
    CASE WHEN FIND_IN_SET(i.good_id,'345,353,371') THEN '20' ELSE '10' END
    END
    ELSE
    CASE
    WHEN a.id >= 73 AND a.id <= 117 THEN
    CASE WHEN FIND_IN_SET(i.good_id,'353,399,403,371,390,392,395') THEN '15' ELSE '10' END
    ELSE
    CASE WHEN FIND_IN_SET(i.good_id,'353,399,403,371,390,392,395') THEN '20' ELSE '10' END
    END
    END
)"),
);

$select = $db->select()
->from(array('st' => 'store'), $get)
->join(array('o'  => 'org')             , 'st.org_dealer = o.org_id'    , array())

// ->join(array('rm' => 'regional_market') , 'st.regional_market = rm.id'  , array())
// ->join(array('a'  => 'area')            , 'rm.area_id = a.id'           , array())


// ->joinLeft(array('so' => 'store_operation'), 'st.operation_id = so.id'  , array())
// ->joinLeft(array('am' => 'area_map')    , 'rm.id = am.regional_market'  , array())
// ->joinLeft(array('tp' => 'th_province') , 'am.th_province_id = tp.id'   , array())
// ->joinLeft(array('geo'=> 'geography')   , 'tp.geo_id = geo.id'          , array())

// ->join(array('rm2'=> 'regional_market') , 'st.district = rm2.id'        , array())


->join(array('t'  => 'timing')          , 'st.id = t.store'             , array())
->join(array('ts' => 'timing_sale')     , 't.id = ts.timing_id'         , array())

->joinLeft(array('rm' => 'regional_market'),'t.store_area = rm.id',array())
->joinLeft(array('a' => 'area'),'rm.area_id = a.id',array())


->joinLeft(array('cpo' => 'custromer_pre_order')     , 'ts.imei = cpo.imei' , array())

->joinLeft(array('rm3' => 'regional_market') , 't.regional_id = rm3.id'  , array())


->join(array('cus' => 'customer')     , 'ts.customer_id = cus.id'         , array())
->joinLeft(array('i'  => WAREHOUSE_DB.'.imei')      , 'ts.imei = i.imei_sn' , array())
->joinLeft(array('mk'  => WAREHOUSE_DB.'.market')      , 'mk.sn = i.sales_sn' , array())
->join(array('g'  => WAREHOUSE_DB.'.good')      , 'i.good_id = g.id'    , array())
->join(array('cat'  => WAREHOUSE_DB.'.good_category')      , 'g.cat_id = cat.id'    , array())
->join(array('gc' => WAREHOUSE_DB.'.good_color'), 'i.good_color = gc.id', array())
->joinLeft(array('d1' => WAREHOUSE_DB.'.distributor'), 'st.d_id = d1.id'            , array())
->joinLeft(array('d2' => WAREHOUSE_DB.'.distributor'), 'i.distributor_id = d2.id'   , array())
->joinLeft(array('b' => WAREHOUSE_DB.'.brand'),'b.id = g.brand_id',array())

// For OPPO PC Report
->joinLeft(array('rm5' => 'regional_market'),'rm5.id = d2.region',array())
->joinLeft(array('a2' => 'area'),'a2.id = rm5.area_id',array())

->joinLeft(array('o2' => 'org')                      , 'd2.ka_type = o2.org_id'     , array())
->joinLeft(array('wh' => WAREHOUSE_DB.'.warehouse'),'d2.warehouse_id = wh.id',array())
->joinLeft(array('d3' => WAREHOUSE_DB.'.distributor'), 'wh.id = d3.agent_warehouse_id'   , array())
->joinLeft(array('s'   => 'staff')  , 't.staff_id = s.id'      , array())
->joinLeft(array('gr'  => 'group')  , 's.group_id = gr.id'     , array())
->joinLeft(array('s2'  => 'staff')  , 't.sales_id = s2.id'     , array())
->joinLeft(array('s3'  => 'staff')  , 't.pcm_id = s3.id'     , array())
->joinLeft(array('s4'  => 'staff')  , 't.stock_id = s4.id'     , array())
->joinLeft(array('s5'  => 'staff')  , 't.asm_id = s5.id'     , array())
->joinLeft(array('gr2' => 'group')  , 's2.group_id = gr2.id'   , array())
->joinLeft(array('sd' => 'sub_district')    , 'st.sub_district = sd.id' , array())
->joinLeft(array('ac' => 'area_control')    , 'sd.id = ac.sub_district' , array())
->joinLeft(array('sa' => 'sub_area')        , 'st.agency = sa.id'  , array())
->joinLeft(array('gkl' => 'good_kpi_log'),
    "   gkl.good_id = i.good_id
    AND gkl.good_id = gkl.type
    AND gkl.color_id = i.good_color
    AND t.created_at >= CONCAT(gkl.from_date,' 00:00:00')
    AND t.created_at <= CONCAT(gkl.to_date,' 23:59:59')
    ", array())

->where('t.created_at >= ?', $from." 00:00:00")
->where('t.created_at <= ?', $to." 23:59:59")

->where('ts.imei NOT IN (SELECT imei FROM timing_control)')
->group('i.imei_sn')

->order(array('a.name ASC', 'rm.name ASC', 'sa.name ASC', 'sd.name ASC', 't.created_at ASC'));

if ( isset($params['name']) && $params['name'] ) {
    $select->where('CONCAT(s.firstname, " ",s.lastname) LIKE ?', '%'.$params['name'].'%');
//$select->orWhere('CONCAT(s2.firstname, " ",s2.lastname) LIKE ?', '%'.$params['name'].'%');
}

if ( isset($params['staff_code']) && $params['staff_code'] ) {
    $select->where('s.code = ?', $params['staff_code']);
//$select->orWhere('s2.code = ?', $params['staff_code']);
}

if(isset($params['company']) && $params['company'] ){
    $select->where('s.company_id =?',$params['company']);
}

if (isset($params['brand']) && $params['brand']) {
    if (is_array($params['brand']) && count($params['brand']))
        $select->where('g.brand_id IN (?)', $params['brand']);
    elseif (is_numeric($params['brand']))
        $select->where('g.brand_id = ?', intval($params['brand']));
    else
        $select->where('1=0', 1);
}

if(isset($params['position']) && $params['position'] == 1){
    $select->where('s.group_id IN (?)',array(4,64));
}

if(isset($params['position']) && $params['position'] == 2){
    $select->where('s.group_id = 9');
}

if(isset($params['position']) && $params['position'] == 3){
    $select->where('s.group_id = 5');
}

if ( isset($params['phone_number']) && $params['phone_number'] ) {
    $select->where('s.phone_number LIKE ?', '%'.$params['phone_number'].'%');
//$select->orWhere('s2.phone_number LIKE ?', '%'.$params['phone_number'].'%');
}

if(isset($params['rgm_area']) && $params['rgm_area']) {
    $select->where('a2.id IN ('.$params['rgm_area'].')');
}

if ( isset($params['chk_tme']) && $params['chk_tme'] ) {
    $select->where('st.rank <> ?', $params['chk_tme']);
}

// Add Filter Good
if (isset($params['good']) && $params['good']) {
    if (is_array($params['good']) && count($params['good']))
        $select->where('ts.product_id IN (?)', $params['good']);
    elseif (is_numeric($params['good']))
        $select->where('ts.product_id = ?', intval($params['good']));
    else
        $select->where('1=0', 1);
}

// if(isset($params['rgm_area']) && $params['rgm_area']){
//     $select->joinLeft(array('dis' => WAREHOUSE_DB.'.distributor'),'dis.id = i.distributor_id',array());
//     $select->joinLeft(array('rm51' => HR_DB.'.regional_market'),'rm51.id = dis.region',array());
//     $select->joinLeft(array('a3' => HR_DB.'.area'),'a3.id = rm51.area_id',array());

//     $select->where('a3.id IN (?)',$params['rgm_area']);

//     // $select->where('t.created_by = 6535');
// }



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

// ------(reportActived)
if ( isset($params['actived']) && $params['actived'] == 'Not Actived' ) {
    $select->where('i.activated_date IS NULL');
}
if ( isset($params['actived']) && $params['actived'] == 'Actived' ) {
    $select->where('i.activated_date IS NOT NULL');
}

// Add Filter Store
if (isset($params['store']) && $params['store']) {
    if (is_array($params['store']) && count($params['store']))
        $select->where('t.store IN (?)', $params['store']);
    elseif (is_numeric($params['store']))
        $select->where('t.store = ?', intval($params['store']));
    else
        $select->where('1=0', 1);
}

if (isset($params['store_type']) && $params['store_type']) {
    if (is_array($params['store_type']) && count($params['store_type']))
        $select->where('st.org_dealer IN (?)', $params['store_type']);
    elseif (is_numeric($params['store']))
        $select->where('st.org_dealer = ?', intval($params['store_type']));
    else
        $select->where('1=0', 1);
}
// check ermission ASM, ASM Stand by, Sale Admin, Traning
if ( isset($params['asm']) && $params['asm'] ) {
    $QAsm = new Application_Model_Asm();
    $list_regions = $QAsm->get_cache($params['asm']);
    $list_regions = isset($list_regions['province']) && is_array($list_regions['province']) ? $list_regions['province'] : array();
    if (count($list_regions) > 0)
        $select->where( 'st.province_id IN (?)', $list_regions);
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

// check Permission SALE
if (isset($params['sale_id']) && intval($params['sale_id']) > 0) {
    $QSalesArea = new Application_Model_SalesArea();
    $list_regions = $QSalesArea->get_cache($params['sale_id']);
    $list_regions = isset($list_regions['district']) && is_array($list_regions['district']) ? $list_regions['district'] : array();
    if (count($list_regions) > 0) {
        $select->where('( st.district IN (?)', $list_regions);
        $sub_select = $db->select()
        ->from(array('ssl' => 'store_staff'), array('st_id' => 'ssl.store_id'))
        ->where('staff_id = ?', $params['sale_id']);
        $ss_result = $db->fetchAll($sub_select);
        foreach ($ss_result as $key => $value) { $ss_data[] = $value['st_id']; }
        $select->orWhere('st.id IN (?) )', $ss_data);
    } else {
        $select->joinRight(array('ssl' => 'store_staff_log'), 'ssl.store_id=t.store', array());
        $log_where = $this->getAdapter()->quoteInto('ssl.staff_id = ?', $params['sale_id']).
        " AND " . $this->getAdapter()->quoteInto('ssl.is_leader = ?', 1).
        " AND " . $this->getAdapter()->quoteInto('DATE(t.from) >= FROM_UNIXTIME(ssl.joined_at, \'%Y-%m-%d\')', 1).
        " AND (".
        $this->getAdapter()->quoteInto('DATE(t.from) < FROM_UNIXTIME(ssl.released_at, \'%Y-%m-%d\')', 1).
        " OR " . $this->getAdapter()->quoteInto('ssl.released_at IS NULL', 1).
        " OR " . $this->getAdapter()->quoteInto('ssl.released_at = 0', 1).
        " ) ";
        $select->where($log_where);
    }
}

if (isset($params['pcm_id']) && intval($params['pcm_id']) > 0) {
    $select
    ->joinRight(array('ssl' => 'store_staff_log'), 'ssl.store_id=t.store', array());
    $log_where = $this->getAdapter()->quoteInto('ssl.staff_id = ?', $params['pcm_id']).
    " AND " . $this->getAdapter()->quoteInto('ssl.is_leader = ?', 2).
    " AND " . $this->getAdapter()->quoteInto('DATE(t.from) >= FROM_UNIXTIME(ssl.joined_at, \'%Y-%m-%d\')', 1).
    " AND (".
    $this->getAdapter()->quoteInto('DATE(t.from) < FROM_UNIXTIME(ssl.released_at, \'%Y-%m-%d\')', 1).
    " OR " . $this->getAdapter()->quoteInto('ssl.released_at IS NULL', 1).
    " OR " . $this->getAdapter()->quoteInto('ssl.released_at = 0', 1).
    " ) ";
    $select->where($log_where);
}

if (isset($params['bm_id']) && intval($params['bm_id']) > 0) {
    $select
    ->joinRight(array('ssl' => 'store_staff_log'), 'ssl.store_id=t.store', array());
    $log_where = $this->getAdapter()->quoteInto('ssl.staff_id = ?', $params['bm_id']).
    " AND " . $this->getAdapter()->quoteInto('ssl.is_leader = ?', 3).
    " AND " . $this->getAdapter()->quoteInto('DATE(t.from) >= FROM_UNIXTIME(ssl.joined_at, \'%Y-%m-%d\')', 1).
    " AND (".
    $this->getAdapter()->quoteInto('DATE(t.from) < FROM_UNIXTIME(ssl.released_at, \'%Y-%m-%d\')', 1).
    " OR " . $this->getAdapter()->quoteInto('ssl.released_at IS NULL', 1).
    " OR " . $this->getAdapter()->quoteInto('ssl.released_at = 0', 1).
    " ) ";
    $select->where($log_where);
}

if (isset($params['leader_id']) && intval($params['leader_id']) > 0) {
    $select
    ->joinRight(array('ssl' => 'store_leader_log'), 'ssl.store_id=t.store', array());
    $log_where = $this->getAdapter()->quoteInto('ssl.staff_id = ?', $params['leader_id']).
    " AND " . $this->getAdapter()->quoteInto('DATE(t.from) >= FROM_UNIXTIME(ssl.joined_at, \'%Y-%m-%d\')', 1).
    " AND (".
    $this->getAdapter()->quoteInto('DATE(t.from) < FROM_UNIXTIME(ssl.released_at, \'%Y-%m-%d\')', 1).
    " OR " . $this->getAdapter()->quoteInto('ssl.released_at IS NULL', 1).
    " OR " . $this->getAdapter()->quoteInto('ssl.released_at = 0', 1).
    " ) ";
    $select->where($log_where);
}

// echo $select; die;
$result = $db->fetchAll($select);
return $result;
}
///////
///////
///////
///    pit karn pherm kha commision hai sale \\\\\
///////
///////
///////
function getAmSellout($page, $limit, &$total, $params){
    $db = Zend_Registry::get('db');
    if (isset($params['export']) && $params['export']) {
        $get_01 = array(
            'store_id'      => 's.id',
            'shop_id'       => 's.store_id',
            'shop_code'     => 's.store_code',
            'distributor_id'=> 'd3.id',
            'title'         => 'd3.title',
            'org_type'      => 'o3.org_name',
            'imei_sn'       => 'wi.imei_sn',
            'good_name'     => 'wg.name',
            'desc'          => 'wg.desc',
            'color_name'    => 'wgc.name',
            'created_at'    => 't.created_at',
        );
    } else {
        $get_01 = array(
            'store_id'      => new Zend_Db_Expr("SQL_CALC_FOUND_ROWS s.id"),
            'sellout'       => new Zend_Db_Expr("COUNT(ts.imei)"),
            'sellout_hero'  => new Zend_Db_Expr("COUNT(CASE WHEN ts.product_id IN (345) THEN ts.imei END)"),
            'distributor_id'=> 'wd.id',
            'title'         => 'wd.title',
            'org_type'      => 'o2.org_name',
        );
        if ( isset($params['get_total_sales']) and $params['get_total_sales'] ) { unset($get_01['store_id']); }
    }
    $get_02 = array(
        'store_name'    => 's.name',
        'org_name'      => 'o.org_name',
        'area_name'     => 'ar.name',

    );
    $get = $get_01 + $get_02;
    $select = $db->select()
    ->from(array('t' => 'timing'), $get)
    ->join(array('s' => 'store')                    , 't.store = s.id'              , array())
    ->join(array('o' => 'org')                      , 's.org_dealer = o.org_id'     , array())
    ->join(array('ts'=> 'timing_sale')              , 't.id = ts.timing_id'         , array())
    ->join(array('rm'=> 'regional_market')          , 's.regional_market = rm.id'   , array())
    ->join(array('ar'=> 'area')                     , 'rm.area_id = ar.id'          , array())
    ->joinLeft(array('wd'=> WAREHOUSE_DB.'.distributor'), 'wd.id = s.d_id'          , array())
    ->joinLeft(array('o2'=> 'org')                      , 'wd.ka_type = o2.org_id'  , array());
    if (isset($params['export']) && $params['export']) {
        $select->join(array('wi' => WAREHOUSE_DB.'.imei'), 'ts.imei = wi.imei_sn', array());
        $select->join(array('wg' => WAREHOUSE_DB.'.good'), 'wg.id = wi.good_id', array());
        $select->join(array('wgc'=> WAREHOUSE_DB.'.good_color'), 'wgc.id = wi.good_color', array());
        $select->joinLeft(array('d3'=> WAREHOUSE_DB.'.distributor'), 'wi.distributor_id = d3.id', array());
        $select->joinLeft(array('o3'=> 'org'), 'd3.ka_type = o3.org_id', array());
        $select->order('s.id ASC');
    } else {
        $select->group('s.id');
        $select->order('sellout DESC');
        if ($limit)
            $select->limitPage($page, $limit);
    }
// Add Filter
    if ( isset($params['from']) && $params['from'] ) {
        $select->where('t.created_at >= ?', $params['from']." 00:00:00");
    }
    if ( isset($params['to']) && $params['to'] ) {
        $select->where('t.created_at <= ?', $params['to']." 23:59:59");
    }
    if ( isset($params['type']) && $params['type'] ) {
        $select->where('s.org_dealer = ?', $params['type']);
    }

    if ( isset($params['store_id']) && $params['store_id'] ) {
        $select->where('s.id = ?',  $params['store_id']);
    }
    if ( isset($params['store_name']) && $params['store_name'] ) {
        $select->where('s.name LIKE ?', '%'.$params['store_name'].'%');
    }
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
            $select->where('s.regional_market IN (?)', $params['regional_market']);
        elseif (is_numeric($params['regional_market']))
            $select->where('s.regional_market = ?', intval($params['regional_market']));
        else
            $select->where('1=0', 1);
    }
// Add Filter District
    if (isset($params['district']) && $params['district']) {
        if (is_array($params['district']) && count($params['district']))
            $select->where('s.district IN (?)', $params['district']);
        elseif (is_numeric($params['district']))
            $select->where('s.district = ?', intval($params['district']));
        else
            $select->where('1=0', 1);
    }
// Get Total Sales
    if ( isset($params['get_total_sales']) and $params['get_total_sales'] ){
        $get_p = array(
            'total_unit' => new Zend_Db_Expr('SUM( pa.sellout )'),
            'total_unit_hero' => new Zend_Db_Expr('SUM( pa.sellout_hero )'),
        );
        $select_p = $db->select()
        ->from(array('pa' => $select), $get_p);
        return $db->fetchRow($select_p);
    }
// echo $select; exit();
    $result = $db->fetchAll($select);
    $total = $db->fetchOne("select FOUND_ROWS()");
    return $result;
}
function getAmSellin($page, $limit, &$total, $params){
    $db = Zend_Registry::get('db');

    if (isset($params['export']) && $params['export']) {
        $get_01 = array(
            'distributor_id'    => 'wd.id',
            'shop_id'           => 'cs.store_id',
            'shop_code'         => 'cs.store_code',
            'imei_sn'           => 'wi.imei_sn',
            'good_name'         => 'wg.name',
            'desc'              => 'wg.desc',
            'color_name'        => 'wgc.name',
            'created_at'        => 'ct.created_at',
        );
    } else {
        $get_01 = array(
            'distributor_id'    => new Zend_Db_Expr("SQL_CALC_FOUND_ROWS wd.id"),
            'sellout'           => new Zend_Db_Expr("COUNT(cts.id)"),
            'sellout_hero'      => new Zend_Db_Expr("COUNT(CASE WHEN cts.product_id IN (345) THEN cts.imei END)"),
        );
        if ( isset($params['get_total_sales']) and $params['get_total_sales'] ) { unset($get_01['distributor_id']); }
    }
    $get_02 = array(
        'title'     => 'wd.title',
        'org_type'  => 'wo.org_name',
        'store_id'  => 'cs.id',
        'store_name'=> 'cs.name',
        'org_name'  => 'co.org_name',
        'area_name' => 'ca.name',
    );
    $get = $get_01 + $get_02;
    $select = $db->select()
    ->from(array('wi' => WAREHOUSE_DB.".imei"), $get)
    ->join(array('wd' => WAREHOUSE_DB.'.distributor')   , 'wi.distributor_id = wd.id'   , array())
    ->join(array('wo' => 'org')                         , 'wd.ka_type = wo.org_id'      , array())
    ->join(array('cts'=> 'timing_sale')                 , 'wi.imei_sn = cts.imei'       , array())
    ->join(array('ct' => 'timing')                      , 'cts.timing_id = ct.id'       , array())
    ->join(array('cs' => 'store')                       , 'ct.store = cs.id'            , array())
    ->join(array('co' => 'org')                         , 'cs.org_dealer = co.org_id'   , array())
    ->join(array('crm'=> 'regional_market')             , 'cs.regional_market = crm.id' , array())
    ->join(array('ca' => 'area')                        , 'crm.area_id = ca.id'         , array());

    if (isset($params['export']) && $params['export']) {
        $select->join(array('wg' => WAREHOUSE_DB.'.good'), 'wg.id = wi.good_id', array());
        $select->join(array('wgc'=> WAREHOUSE_DB.'.good_color'), 'wgc.id = wi.good_color', array());
        $select->order('cs.id ASC');
    } else {
        $select->group(array('wd.id','cs.id'));
        $select->order('sellout DESC');
        if ($limit)
            $select->limitPage($page, $limit);
    }

    if ( isset($params['from']) && $params['from'] ) {
        $select->where('ct.created_at >= ?', $params['from']." 00:00:00");
    }
    if ( isset($params['to']) && $params['to'] ) {
        $select->where('ct.created_at <= ?', $params['to']." 23:59:59");
    }
//if($params['by'] == '1'){
    if ( isset($params['distributor_type']) && $params['distributor_type'] ) {
        $select->where('wd.ka_type = ?', $params['distributor_type']);
    }

    if ( isset($params['distributor_name']) && $params['distributor_name'] ) {
        $select->where('wd.title LIKE ?',  '%'.$params['distributor_name'].'%');
    }
    if ( isset($params['distributor_id']) && $params['distributor_id'] ) {
        $select->where('wd.id LIKE ?',  '%'.$params['distributor_id'].'%');
    }
// } else {
//     if ( isset($params['type']) && $params['type'] ) {
//         $select->where('cs.org_dealer = ?', $params['type']);
//     }
//     if ( isset($params['store_name']) && $params['store_name'] ) {
//         $select->where('cs.name LIKE ?',  '%'.$params['store_name'].'%');
//     }
//     if ( isset($params['store_id']) && $params['store_id'] ) {
//         $select->where('cs.id LIKE ?',  '%'.$params['store_id'].'%');
//     }
// }
// Add Filter Area
    if (isset($params['area_id']) && $params['area_id']) {
        if (is_array($params['area_id']) && count($params['area_id']))
            $select->where('crm.area_id IN (?)', $params['area_id']);
        elseif (is_numeric($params['area_id']))
            $select->where('crm.area_id = ?', intval($params['area_id']));
        else
            $select->where('1=0', 1);
    }
// Add Filter Province
    if (isset($params['regional_market']) && $params['regional_market']) {
        if (is_array($params['regional_market']) && count($params['regional_market']))
            $select->where('cs.regional_market IN (?)', $params['regional_market']);
        elseif (is_numeric($params['regional_market']))
            $select->where('cs.regional_market = ?', intval($params['regional_market']));
        else
            $select->where('1=0', 1);
    }
// Add Filter District
    if (isset($params['district']) && $params['district']) {
        if (is_array($params['district']) && count($params['district']))
            $select->where('cs.district IN (?)', $params['district']);
        elseif (is_numeric($params['district']))
            $select->where('cs.district = ?', intval($params['district']));
        else
            $select->where('1=0', 1);
    }
// Get Total Sales
    if ( isset($params['get_total_sales']) and $params['get_total_sales'] ){
        $get_p = array(
            'total_unit' => new Zend_Db_Expr('SUM( pa.sellout )'),
            'total_unit_hero' => new Zend_Db_Expr('SUM( pa.sellout_hero )'),
        );
        $select_p = $db->select()
        ->from(array('pa' => $select), $get_p);
        return $db->fetchRow($select_p);
    }
//echo $select;exit();
    $result = $db->fetchAll($select);
    $total = $db->fetchOne("select FOUND_ROWS()");
    return $result;
}
// PC Level
function getTimingPcLevelCurrent($params,$startdate,$enddate){
    $selloutall = "(SELECT  COUNT(tsub.id) FROM  timing AS tsub
    INNER JOIN timing_sale AS tssub ON tsub.id = tssub.timing_id
    WHERE tsub.staff_id = t.staff_id
    AND ( tsub.created_at >= '".$startdate. " 00:00:00')
    AND (tsub.created_at <= '".$enddate. " 23:59:59') GROUP BY tsub.staff_id)";
// echo $selloutall;exit();
    $db = Zend_Registry::get('db');
    $select = $db->select()
    ->from(array('t' => $this->_name),
        array('t.created_at','t.staff_id','t.store','target'=>"(SELECT SUM(opt.target) FROM oppo_pc_target opt WHERE opt.store_id = t.store AND opt.from_date = '".$startdate."')",'selloutall'=>$selloutall))
    ->join(array('ts' => 'timing_sale'), 't.id = ts.timing_id' ,array('sellout'=>'COUNT(t.id)',
        'r9sseries' => " COUNT(case when `ts`.`product_id` in ('345') then 1 else null end)"));
    if ( isset($startdate) && $startdate) {
        $select->where('t.created_at >= ?', $startdate." 00:00:00");
    }
    if ( isset($enddate) && $enddate ) {
        $select->where('t.created_at <= ?', $enddate." 23:59:59");
    }

    $select->group('t.staff_id');
    $select->group('t.store');
// echo $select;exit();
    $result = $db->fetchAll($select);
    $newData = array();
    for($i = 0 ;$i < count($result);$i++){
        $newData[$result[$i]["staff_id"]][$result[$i]["store"]] = $result[$i];
        $newData[$result[$i]["staff_id"]]["selloutall"] = $result[$i]["selloutall"];
    }
    return $newData;
}
function getFocusPC($params) {
    set_time_limit(0);
    ini_set('memory_limit', '-1');
    error_reporting(~E_ALL);
    ini_set("display_error", '0');
// Change Date Format
    $d = explode('/', $params['from']);
    $from = $d[2].'-'.$d[1].'-'.$d[0];
    $d = explode('/', $params['to']);
    $to = $d[2].'-'.$d[1].'-'.$d[0];
    $db = Zend_Registry::get('db');
    $get = array(
        'area_id'       => 'a.id',
        'area_name'     => 'a.name',
        'staff_id'      => 's.id',
        'staff_code'    => 's.code',
        'staff_name'    => new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)"),
        'staff_created' => 's.created_at',
        'staff_joined'  => 's.joined_at',
        'st_id'         => 'st.id',
        'st_name'       => 'st.name',
        'st_type'       => 'o.org_name',
        'sellout'       => new Zend_Db_Expr("COUNT(ts.imei)"),
    );
    $select = $db->select()
    ->from(array('s'  => 'staff'), $get)
    ->join(array('rm' => 'regional_market') , 's.regional_market = rm.id'   , array())
    ->join(array('a'  => 'area')            , 'rm.area_id = a.id'           , array())
    ->join(array('ss' => 'store_staff')     , 's.id = ss.staff_id AND ss.is_leader = 0', array())
    ->join(array('st' => 'store')           , 'ss.store_id = st.id'         , array())
    ->join(array('o'  => 'org')             , 'st.org_dealer = o.org_id'    , array())
    ->join(array('rm2'=> 'regional_market') , 'st.regional_market = rm2.id' , array())
    ->join(array('a2' => 'area')            , 'rm2.area_id = a2.id'         , array())
    ->joinLeft(array('t'  => 'timing')      ,
        "   s.id = t.staff_id
        AND t.created_at >= '".$from." 00:00:00'
        AND t.created_at <= '".$to." 23:59:59'
        ", array())
    ->joinLeft(array('ts' => 'timing_sale') , 't.id = ts.timing_id'         , array())
    ->where('s.off_date IS NULL')
    ->where('s.pc_stand_by = 0')
    ->where('s.created_at <= ?', $from." 00:00:00")
    ->where('t.id IS NULL')
    ->group(array('s.id','st.id'))
    ->order(array('a.name ASC', 's.code ASC', 's.created_at ASC'));
    if ( isset($params['name']) && $params['name'] ) {
        $select->where('CONCAT(s.firstname, " ",s.lastname) LIKE ?', '%'.$params['name'].'%');
    }
    if ( isset($params['staff_code']) && $params['staff_code'] ) {
        $select->where('s.code = ?', $params['staff_code']);
    }
    if ( isset($params['phone_number']) && $params['phone_number'] ) {
        $select->where('s.phone_number LIKE ?', '%'.$params['phone_number'].'%');
    }
// Add Filter Area
    if (isset($params['area_id']) && $params['area_id']) {
        if (is_array($params['area_id']) && count($params['area_id']))
            $select->where('rm2.area_id IN (?)', $params['area_id']);
        elseif (is_numeric($params['area_id']))
            $select->where('rm2.area_id = ?', intval($params['area_id']));
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
// Add Filter Store
    if (isset($params['store']) && $params['store']) {
        if (is_array($params['store']) && count($params['store']))
            $select->where('st.id IN (?)', $params['store']);
        elseif (is_numeric($params['store']))
            $select->where('st.id = ?', intval($params['store']));
        else
            $select->where('1=0', 1);
    }
    if (isset($params['store_type']) && $params['store_type']) {
        if (is_array($params['store_type']) && count($params['store_type']))
            $select->where('st.org_dealer IN (?)', $params['store_type']);
        elseif (is_numeric($params['store']))
            $select->where('st.org_dealer = ?', intval($params['store_type']));
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
// check Permission SALE
    if (isset($params['sale_id']) && intval($params['sale_id']) > 0) {
        $QSalesArea = new Application_Model_SalesArea();
        $list_regions = $QSalesArea->get_cache($params['sale_id']);
        $list_regions = isset($list_regions['district']) && is_array($list_regions['district']) ? $list_regions['district'] : array();
        if (count($list_regions) > 0) {
            $select->where('( st.district IN (?)', $list_regions);
            $sub_select = $db->select()
            ->from(array('ssl' => 'store_staff'), array('st_id' => 'ssl.store_id'))
            ->where('staff_id = ?', $params['sale_id']);
            $ss_result = $db->fetchAll($sub_select);
            foreach ($ss_result as $key => $value) { $ss_data[] = $value['st_id']; }
            $select->orWhere('st.id IN (?) )', $ss_data);
        } else {
            $select->joinRight(array('ssl' => 'store_staff_log'), 'ssl.store_id=t.store', array());
            $log_where = $this->getAdapter()->quoteInto('ssl.staff_id = ?', $params['sale_id']).
            " AND " . $this->getAdapter()->quoteInto('ssl.is_leader = ?', 1).
            " AND " . $this->getAdapter()->quoteInto('DATE(t.from) >= FROM_UNIXTIME(ssl.joined_at, \'%Y-%m-%d\')', 1).
            " AND (".
            $this->getAdapter()->quoteInto('DATE(t.from) < FROM_UNIXTIME(ssl.released_at, \'%Y-%m-%d\')', 1).
            " OR " . $this->getAdapter()->quoteInto('ssl.released_at IS NULL', 1).
            " OR " . $this->getAdapter()->quoteInto('ssl.released_at = 0', 1).
            " ) ";
            $select->where($log_where);
        }
    }
    if (isset($params['pcm_id']) && intval($params['pcm_id']) > 0) {
        $select
        ->joinRight(array('ssl' => 'store_staff_log'), 'ssl.store_id=t.store', array());
        $log_where = $this->getAdapter()->quoteInto('ssl.staff_id = ?', $params['pcm_id']).
        " AND " . $this->getAdapter()->quoteInto('ssl.is_leader = ?', 2).
        " AND " . $this->getAdapter()->quoteInto('DATE(t.from) >= FROM_UNIXTIME(ssl.joined_at, \'%Y-%m-%d\')', 1).
        " AND (".
        $this->getAdapter()->quoteInto('DATE(t.from) < FROM_UNIXTIME(ssl.released_at, \'%Y-%m-%d\')', 1).
        " OR " . $this->getAdapter()->quoteInto('ssl.released_at IS NULL', 1).
        " OR " . $this->getAdapter()->quoteInto('ssl.released_at = 0', 1).
        " ) ";
        $select->where($log_where);
    }
    if (isset($params['bm_id']) && intval($params['bm_id']) > 0) {
        $select
        ->joinRight(array('ssl' => 'store_staff_log'), 'ssl.store_id=t.store', array());
        $log_where = $this->getAdapter()->quoteInto('ssl.staff_id = ?', $params['pcm_id']).
        " AND " . $this->getAdapter()->quoteInto('ssl.is_leader = ?', 3).
        " AND " . $this->getAdapter()->quoteInto('DATE(t.from) >= FROM_UNIXTIME(ssl.joined_at, \'%Y-%m-%d\')', 1).
        " AND (".
        $this->getAdapter()->quoteInto('DATE(t.from) < FROM_UNIXTIME(ssl.released_at, \'%Y-%m-%d\')', 1).
        " OR " . $this->getAdapter()->quoteInto('ssl.released_at IS NULL', 1).
        " OR " . $this->getAdapter()->quoteInto('ssl.released_at = 0', 1).
        " ) ";
        $select->where($log_where);
    }
    if (isset($params['leader_id']) && intval($params['leader_id']) > 0) {
        $select
        ->joinRight(array('ssl' => 'store_leader_log'), 'ssl.store_id=t.store', array());
        $log_where = $this->getAdapter()->quoteInto('ssl.staff_id = ?', $params['leader_id']).
        " AND " . $this->getAdapter()->quoteInto('DATE(t.from) >= FROM_UNIXTIME(ssl.joined_at, \'%Y-%m-%d\')', 1).
        " AND (".
        $this->getAdapter()->quoteInto('DATE(t.from) < FROM_UNIXTIME(ssl.released_at, \'%Y-%m-%d\')', 1).
        " OR " . $this->getAdapter()->quoteInto('ssl.released_at IS NULL', 1).
        " OR " . $this->getAdapter()->quoteInto('ssl.released_at = 0', 1).
        " ) ";
        $select->where($log_where);
    }
//echo $select; die;
    $result = $db->fetchAll($select);
    return $result;
}
function getSelloutByPC($staff_id, $from_date, $to_date) {
    $db = Zend_Registry::get('db');
    $select = $db->select()
    ->from(array('t' => 'timing'), array('sellout' => new Zend_Db_Expr('COUNT(ts.imei)') ))
    ->join(array('ts' => 'timing_sale'), 't.id = ts.timing_id', array())
    ->where('t.staff_id = ?', $staff_id)
    ->where('t.created_at >= ?', $from_date.' 00:00:00')
    ->where('t.created_at <= ?', $to_date.' 23:59:59');
//echo $select;
    $result = $db->fetchRow($select);
    return $result;
}
// Report Head Count By RD
function getHeadCountByRD($params) {
    $RD_Exceptoin = array('5600478','5800892');
    $db = Zend_Registry::get('db');
    $get = array(
        'zone'          => new Zend_Db_Expr("(CASE WHEN a.name LIKE 'BKK%' THEN 'BKK' ELSE 'UPC' END)"),
        'rd_id'         => 's.id',
        'rd_code'       => 's.code',
        'rd_name'       => new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)"),
        'rd_group_id'   => 's.group_id',
        'cnt_pc'        => new Zend_Db_Expr("COUNT(CASE WHEN s2.group_id = 4 THEN s2.id END)"),
        'cnt_sale'      => new Zend_Db_Expr("COUNT(CASE WHEN s2.group_id = 9 THEN s2.id END)"),
        'cnt_sale_event'=> new Zend_Db_Expr("COUNT(CASE WHEN s2.group_id = 31 THEN s2.id END)"),
        'cnt_asm'       => new Zend_Db_Expr("COUNT(CASE WHEN s2.group_id IN (5,16) THEN s2.id END)"),
        'cnt_rd'        => new Zend_Db_Expr("COUNT(CASE WHEN s2.group_id IN (28,35) THEN s2.id END)"),
        'cnt_tms_leader'=> new Zend_Db_Expr("COUNT(CASE WHEN s2.group_id = 38 THEN s2.id END)"),
        'cnt_tms'       => new Zend_Db_Expr("COUNT(CASE WHEN s2.group_id = 37 THEN s2.id END)"),
        'cnt_pcm_leader'=> new Zend_Db_Expr("COUNT(CASE WHEN s2.group_id = 36 THEN s2.id END)"),
        'cnt_pcm'       => new Zend_Db_Expr("COUNT(CASE WHEN s2.group_id = 17 THEN s2.id END)"),
    );
    $select = $db->select()
    ->from(array('s'   => 'staff'), $get)
    ->join(array('asm' => 'asm')                , 's.id = asm.staff_id' , array())
    ->join(array('a'   => 'area')               , 'asm.area_id = a.id'  , array())
    ->join(array('rm'  => 'regional_market')    , 'a.id = rm.area_id'   , array())
    ->join(array('s2'  => 'staff'),
        "   rm.id = s2.regional_market
        AND s2.off_date IS NULL
        AND s2.status = 1
        AND s2.group_id IN (4,5,9,31,16,28,35,37,38,17,36)
        AND s2.code NOT IN (".implode(",",$RD_Exceptoin).")
        ", array())
    ->where('s.group_id IN (?)', array(RM_ID, RMSTANDBY_ID))
    ->where('s.off_date IS NULL', 1)
    ->where('s.code NOT IN (?)', $RD_Exceptoin)
    ->group('s.id')
    ->order(array('zone ASC','s.code ASC'));
// ASM Permission
    if (isset($params['asm']) && $params['asm']) {
        $QAsm = new Application_Model_Asm();
        $list_regions = $QAsm->get_cache($params['asm']);
        $list_regions = isset($list_regions['province']) && is_array($list_regions['province']) ? $list_regions['province'] : array();
        if (count($list_regions) > 0)
            $select->where('rm.id IN (?)', $list_regions);
        else
            $select->where('1=0', 1);
    }
//echo $select; die;
    $result = $db->fetchAll($select);
    return $result;
}
// Report Head Count Unique
function getHeadCountUnique($params) {
    $RD_Exceptoin = array('5600478');
    $db = Zend_Registry::get('db');
    $get = array(
        'cnt_pc'        => new Zend_Db_Expr("COUNT(CASE WHEN s.group_id = 4 THEN s.id END)"),
        'cnt_sale'      => new Zend_Db_Expr("COUNT(CASE WHEN s.group_id = 9 THEN s.id END)"),
        'cnt_sale_event'=> new Zend_Db_Expr("COUNT(CASE WHEN s.group_id = 31 THEN s.id END)"),
        'cnt_asm'       => new Zend_Db_Expr("COUNT(CASE WHEN s.group_id IN (5,16) THEN s.id END)"),
        'cnt_rd'        => new Zend_Db_Expr("COUNT(CASE WHEN s.group_id IN (28,35) THEN s.id END)"),
        'cnt_tms_leader'=> new Zend_Db_Expr("COUNT(CASE WHEN s.group_id = 38 THEN s.id END)"),
        'cnt_tms'       => new Zend_Db_Expr("COUNT(CASE WHEN s.group_id = 37 THEN s.id END)"),
        'cnt_pcm_leader'=> new Zend_Db_Expr("COUNT(CASE WHEN s.group_id = 36 THEN s.id END)"),
        'cnt_pcm'       => new Zend_Db_Expr("COUNT(CASE WHEN s.group_id = 17 THEN s.id END)"),
        'cnt_total'     => new Zend_Db_Expr("COUNT(s.id)"),
    );
    $select = $db->select()
    ->from(array('s'   => 'staff'), $get)
    ->join(array('rm'  => 'regional_market')    , 's.regional_market = rm.id'   , array())
    ->join(array('a'   => 'area')               , 'rm.area_id = a.id'           , array())
    ->where('s.group_id IN (?)', array(4,5,9,31,16,28,35,37,38,17,36))
    ->where('s.off_date IS NULL', 1)
    ->where('s.code NOT IN (?)', $RD_Exceptoin)
    ->where('a.id NOT IN (?)', array(48,49,72));
// ASM Permission
    if (isset($params['asm']) && $params['asm']) {
        $QAsm = new Application_Model_Asm();
        $list_regions = $QAsm->get_cache($params['asm']);
        $list_regions = isset($list_regions['province']) && is_array($list_regions['province']) ? $list_regions['province'] : array();
        if (count($list_regions) > 0)
            $select->where('rm.id IN (?)', $list_regions);
        else
            $select->where('1=0', 1);
    }
//echo $select; die;
    $result = $db->fetchRow($select);
    return $result;
}
// Report Head Count By Position
function getHeadCountByPosition($params) {
    $RD_Exceptoin = array('5600478');
    $db = Zend_Registry::get('db');
    $get = array(
        'group_name'=> 'g.name',
        'cnt_bkk'   => new Zend_Db_Expr("COUNT(CASE WHEN a.name LIKE 'BKK%' THEN s.id END)"),
        'cnt_upc'   => new Zend_Db_Expr("COUNT(CASE WHEN a.name NOT LIKE 'BKK%' THEN s.id END)")
    );
    $select = $db->select()
    ->from(array('g' => 'group'), $get)
    ->join(array('s' => 'staff')            , 'g.id = s.group_id'           , array())
    ->join(array('rm'=> 'regional_market')  , 's.regional_market = rm.id'   , array())
    ->join(array('a' => 'area')             , 'rm.area_id = a.id'           , array())
    ->where('s.group_id IN (?)', array(4,5,9,31,16,28,35,37,38,17,36))
    ->where('s.off_date IS NULL', 1)
    ->where('s.code NOT IN (?)', $RD_Exceptoin)
->where('a.id NOT IN (?)', array(48,49,72)) // Area : Sellin, OPPO-THAI, Foreign
->group('g.id')
->order(array('g.name ASC'));
// ASM Permission
if (isset($params['asm']) && $params['asm']) {
    $QAsm = new Application_Model_Asm();
    $list_regions = $QAsm->get_cache($params['asm']);
    $list_regions = isset($list_regions['province']) && is_array($list_regions['province']) ? $list_regions['province'] : array();
    if (count($list_regions) > 0)
        $select->where('rm.id IN (?)', $list_regions);
    else
        $select->where('1=0', 1);
}
//echo $select; die;
$result = $db->fetchAll($select);
return $result;
}
// Report Head Count By Position
function getStaffByRD($rd_id,$type) {
    $RD_Exceptoin = array('5600478');
    $db = Zend_Registry::get('db');
    $get = array(
        'area_name'     => 'a.name',
        'rd_id'         => 's.id',
        'rd_code'       => 's.code',
        'rd_name'       => new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)"),
        'rd_group'      => 'g.name',
        's_id'          => 's2.id',
        's_code'        => 's2.code',
        's_name'        => new Zend_Db_Expr("CONCAT(s2.firstname, ' ', s2.lastname)"),
        's_group'       => 'g2.name',
    );
    $select = $db->select()
    ->from(array('s'   => 'staff'), $get)
    ->join(array('g'   => 'group')              , 's.group_id = g.id'   , array())
    ->join(array('asm' => 'asm')                , 's.id = asm.staff_id' , array())
    ->join(array('a'   => 'area')               , 'asm.area_id = a.id'  , array())
    ->join(array('rm'  => 'regional_market')    , 'a.id = rm.area_id'   , array())
    ->join(array('s2'  => 'staff'),
        "   rm.id = s2.regional_market
        AND s2.off_date IS NULL
        AND s2.group_id IN (4,5,9,31,16,28,35,37,38,17,36)
        AND s2.code NOT IN (".implode(",",$RD_Exceptoin).")
        ", array())
    ->join(array('g2'  => 'group')              , 's2.group_id = g2.id' , array())
    ->where('s.group_id IN (?)', array(RM_ID, RMSTANDBY_ID))
    ->where('s.off_date IS NULL', 1)
    ->where('s.code NOT IN (?)', $RD_Exceptoin)
    ->order(array('a.name ASC','s.code ASC'));
    if ($rd_id != '0') { $select->where('s.id = ?', $rd_id); }
    if ($type != '0') { $select->where('s2.group_id IN (?)', $type); }
//echo $select; die;
    $result = $db->fetchAll($select);
    return $result;
}
function fetchPagination_DOI_EX($params) {
    set_time_limit(0);
    ini_set('memory_limit', '-1');
    error_reporting(~E_ALL);
    ini_set("display_error", '0');
    $d1 = explode('/', $params['from']);
    $from = $d1[2].'-'.$d1[1].'-'.$d1[0];
    $d2 = explode('/', $params['to']);
    $to = $d2[2].'-'.$d2[1].'-'.$d2[0];
    $mtd = ((strtotime($to) - strtotime($from)) / (60*60*24)) + 1;
    $db = Zend_Registry::get('db');
    $get = array(
        'channel'       => new Zend_Db_Expr(
            "(CASE
            WHEN dg.group_type_id = 9 THEN 'Service'
            WHEN dg.group_type_id = 10 THEN 'Brand Shop'
            ELSE dg.group_name
            END)"),
        'sellout'       => new Zend_Db_Expr("COUNT(CASE WHEN i.activated_date >= '".$from." 00:00:00' AND i.activated_date <= '".$to." 23:59:59' THEN i.imei_sn END)"),
        'total_sellin'  => new Zend_Db_Expr("COUNT(i.imei_sn)"),
        'total_activated' => new Zend_Db_Expr("COUNT(CASE WHEN i.activated_date IS NOT NULL THEN i.imei_sn END)"),
        'stock'         => new Zend_Db_Expr("(COUNT(i.imei_sn) - COUNT(CASE WHEN i.activated_date IS NOT NULL THEN i.imei_sn END))"),
        'selling_rate'  => new Zend_Db_Expr("( ( (COUNT(CASE WHEN i.activated_date >= '".$from." 00:00:00' AND i.activated_date <= '".$to." 23:59:59' THEN i.imei_sn END)) / ".$mtd." ) )"),
        'safety_stock'  => new Zend_Db_Expr("(( ( (COUNT(CASE WHEN i.activated_date >= '".$from." 00:00:00' AND i.activated_date <= '".$to." 23:59:59' THEN i.imei_sn END)) / ".$mtd." ) ) * ".$params['doi_rate'].")"),
        'indicator'     => new Zend_Db_Expr("( (COUNT(i.imei_sn) - COUNT(CASE WHEN i.activated_date IS NOT NULL THEN i.imei_sn END)) - (( ( (COUNT(CASE WHEN i.activated_date >= '".$from." 00:00:00' AND i.activated_date <= '".$to." 23:59:59' THEN i.imei_sn END)) / ".$mtd." ) ) * ".$params['doi_rate'].") )"),
    );
    $select = $db->select()
    ->from(array('d' => WAREHOUSE_DB.'.distributor'), $get)
    ->join(array('i' => WAREHOUSE_DB.'.imei')               , 'd.id = i.distributor_id' , array())
    ->join(array('g' => WAREHOUSE_DB.'.good')               , 'i.good_id = g.id'        , array())
    ->join(array('dg' => WAREHOUSE_DB.'.distributor_group') , 'dg.group_id = d.group_id', array())
    ->where('d.del <> ?', 1)
    ->group("(CASE WHEN dg.group_type_id = 9 THEN 'Service' WHEN dg.group_type_id = 10 THEN 'Brand Shop' ELSE dg.group_name END)")
    ->order('indicator ASC');
// Add Filter Model
    if (isset($params['good_id']) && $params['good_id']) {
        if (is_array($params['good_id']) && count($params['good_id']))
            $select->where('i.good_id IN (?)', $params['good_id']);
        elseif (is_numeric($params['good_id']))
            $select->where('i.good_id = ?', intval($params['good_id']));
        else
            $select->where('1=0', 1);
    }
// Add Filter Color
    if (isset($params['color_id']) && $params['color_id']) {
        if (is_array($params['color_id']) && count($params['color_id']))
            $select->where('i.good_color IN (?)', $params['color_id']);
        elseif (is_numeric($params['color_id']))
            $select->where('i.good_color = ?', intval($params['color_id']));
        else
            $select->where('1=0', 1);
    }
// Permission for ASM / Sale Admin / Trainer
    if ( isset($params['asm']) && $params['asm'] ) {
        $QAsm = new Application_Model_Asm();
        $list_regions = $QAsm->get_cache($params['asm']);
        $list_regions = isset($list_regions['district']) && is_array($list_regions['district']) ? $list_regions['district'] : array();
        if (count($list_regions) > 0)
            $select->where( 'd.district IN (?)', $list_regions);
        else
            $select->where('1=0', 1);
    }
// Permission for AM
    if ( isset($params['am']) && $params['am'] ) {
        $QAm = new Application_Model_Am();
        $list_org = $QAm->get_cache($params['am']);
        $list_org = isset($list_org['store_type']) && is_array($list_org['store_type']) ? $list_org['store_type'] : array();
        if (count($list_org) > 0)
            $select->where( 'd.ka_type IN (?)', $list_org);
        else
            $select->where('1=0', 1);
    }
// echo $select; die;
    $result = $db->fetchAll($select);
    return $result;
}



// Start DOI List 000
function fetchPagination_DOI($page, $limit, &$total, $params) {

    set_time_limit(0);
    ini_set('memory_limit', '-1');
    error_reporting(~E_ALL);
    ini_set("display_error", '0');
    $d1 = explode('/', $params['from']);
    $from = $d1[2].'-'.$d1[1].'-'.$d1[0];
    $d2 = explode('/', $params['to']);
    $to = $d2[2].'-'.$d2[1].'-'.$d2[0];
    $mtd = ((strtotime($to) - strtotime($from)) / (60*60*24)) + 1;
    $db = Zend_Registry::get('db');

    $sub_select = $db->select()
    ->from(array('s2'   => 'store'), array('COUNT(i2.imei_sn)'))
    ->joinleft(array('i2'   => WAREHOUSE_DB.'.imei'), 's2.id = i2.store_id', array())
    ->where('i2.activated_date >= ?', $from." 00:00:00")
    ->where('i2.activated_date <= ?', $to." 23:59:59");

    if ( (isset($params['get_total_count']) and $params['get_total_count'] == 0) ) {
        $get_01['s_id'] = 's.id';
    } else {
        $get_01['s_id'] = new Zend_Db_Expr('SQL_CALC_FOUND_ROWS s.id');
    }
    
    $get_02 = array(
        'area_name'         => 'a.name',
        'province_name'     => 'rm.name',
        'sales_area'        => 'sb.name',
        'store_code'        => 's.store_code',
        'warehouse_id'      => 'w.id',
        'store_name'        => 's.name',
        'distributor_code'  => 'd.distributor_code',
        'distributor_name'  => 'd.title',
        'provience_name'    => 'rm.name',
        'activated_date'    => 'i.activated_date',
        'sellout'           => new Zend_Db_Expr("(".$sub_select.")"),
        'stock'             => new Zend_Db_Expr("COUNT(i.imei_sn)"),
        'imeisn'            => 'i.imei_sn',
        'instock'           => 'i.out_date',
        'category'          => 'g.cat_id',
        'good'              => 'i.good_id',
        'warehouse_name'    => 'w.name',
        'color'             => 'i.good_color',
        'brand_name'        => 'b.name',
        'selling_rate'      => new Zend_Db_Expr("( ( (".$sub_select.") / ".$mtd." ) )"),
        'safety_stock'      => new Zend_Db_Expr("(( ( (".$sub_select.") / ".$mtd." ) ) * ".$params['doi_rate'].")"),
        'indicator'         => new Zend_Db_Expr("( (COUNT(i.imei_sn) - COUNT(CASE WHEN i.activated_date IS NOT NULL THEN i.imei_sn END)) - (( ( (".$sub_select.") / ".$mtd." ) ) * ".$params['doi_rate'].") )"),
    );
    $get = $get_01 + $get_02;
    $select = $db->select()
        ->from(array('s' => 'store'),$get)
        ->join(array('d' => WAREHOUSE_DB.'.distributor'),'d.id = s.d_id',array())
        ->join(array('rm' => 'regional_market'),'s.province_id = rm.id',array())
        ->join(array('a' => 'area'),'rm.area_id = a.id',array())
        ->joinLeft(array('i' => WAREHOUSE_DB.'.imei'),'s.id = i.store_id',array())
        ->joinleft(array('g'   => WAREHOUSE_DB.'.good'), 'i.good_id = g.id', array())
        ->joinleft(array('w'   => WAREHOUSE_DB.'.warehouse'), 'w.id = d.warehouse_id', array())
        ->joinLeft(array('b' => WAREHOUSE_DB.'.brand'),'b.id = g.brand_id',array())
        ->joinLeft(array('sb' => 'sub_area'),'sb.id = s.agency',array());
    
    $select->where('i.imei_sn NOT IN (select imei from timing_sale)');
    $select->group('s.id');
    $select->order('a.name ASC');

    if(isset($params['status']) && $params['status'] == 1){                 // Check IMEI Status not Activated and not Timing
        $select->where('i.activated_date IS null');
        $select->where('i.imei_sn NOT IN (select imei from timing_sale)');
    }
    
    if(isset($params['status']) and $params['status'] == 2){                // Check IMEI Status not Timing
        $select->where('i.imei_sn NOT IN (select imei from timing_sale)');
    }
    
    if(isset($params['status']) and $params['status'] == 3){                // Check IMEI Status Activated but not Timing
        $select->where('i.activated_date IS NOT NULL');
        $select->where('i.imei_sn NOT IN (select imei from timing_sale)');
    }


//Case Warehouse
if(isset($params['warehouse_id']) and $params['warehouse_id']){
   $select->where('i.warehouse_id =?',$params['warehouse_id']);
}

if ( isset($params['s_code']) && $params['s_code'] ) {
    $select->where('s.store_code LIKE ?', '%'.$params['s_code'].'%');
}

if ( isset($params['s_name']) && $params['s_name'] ) {
    $select->where('s.name LIKE ?', '%'.$params['s_name'].'%');
}

// Add Filter Model
if (isset($params['good_id']) && $params['good_id']) {
    if (is_array($params['good_id']) && count($params['good_id']))
        $select->where('i.good_id IN (?)', $params['good_id']);
    elseif (is_numeric($params['good_id']))
        $select->where('i.good_id = ?', intval($params['good_id']));
    else
        $select->where('1=0', 1);
}

// Add Filter Color
if (isset($params['color_id']) && $params['color_id']) {
    if (is_array($params['color_id']) && count($params['color_id']))
        $select->where('i.good_color IN (?)', $params['color_id']);
    elseif (is_numeric($params['color_id']))
        $select->where('i.good_color = ?', intval($params['color_id']));
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

// Permission for ASM / Sale Admin / Trainer
if ( isset($params['asm']) && $params['asm'] ) {
    $QAsm = new Application_Model_Asm();
    $list_regions = $QAsm->get_cache($params['asm']);
    $list_regions = isset($list_regions['province']) && is_array($list_regions['province']) ? $list_regions['province'] : array();
    if (count($list_regions) > 0)
        $select->where( 's.province_id IN (?)', $list_regions);
    else
        $select->where('1=0', 1);
}

// Permission for Sale
if (isset($params['sales_id']) && intval($params['sales_id']) > 0) {
    $select->where('sb.staff_id =?',$params['sales_id']);
}

// Permission for PC & PCDB
if ( isset($params['pc_id']) && $params['pc_id'] ) {
    $select->joinRight(array('ssl' => 'store_staff'), 'ssl.store_id = s.id',array());
    $log_where = $this->getAdapter()->quoteInto('ssl.staff_id = ?', $params['pc_id']). " AND " . $this->getAdapter()->quoteInto('ssl.is_leader IN (?)',array(0,7));
    $select->where($log_where);
}

if (isset($params['get_total_count']) && $params['get_total_count'] == 0) {
    $select_p = $db->select()
    ->from(array('pa' => $select), array(
        'sum_stock' => new Zend_Db_Expr("SUM( pa.stock )"),
    ));

    return $db->fetchRow($select_p);
}

if ($limit)
    $select->limitPage($page, $limit);

$result = $db->fetchAll($select);
if ($limit)
    $total = $db->fetchOne("select FOUND_ROWS()");
return $result;
}
// End DOI List 000


// Start DOI Export_List 111
function fetchPagination_DOI_ExportList($page, $limit, &$total, $params) {

    set_time_limit(0);
    ini_set('memory_limit', '-1');
    error_reporting(~E_ALL);
    ini_set("display_error", '0');
    $d1 = explode('/', $params['from']);
    $from = $d1[2].'-'.$d1[1].'-'.$d1[0];
    $d2 = explode('/', $params['to']);
    $to = $d2[2].'-'.$d2[1].'-'.$d2[0];
    $mtd = ((strtotime($to) - strtotime($from)) / (60*60*24)) + 1;
    $db = Zend_Registry::get('db');

    $sub_select = $db->select()
    ->from(array('s2'   => 'store'), array('COUNT(i2.imei_sn)'))
    ->joinleft(array('i2'   => WAREHOUSE_DB.'.imei'), 's2.id = i2.store_id', array())
    ->where('i2.activated_date >= ?', $from." 00:00:00")
    ->where('i2.activated_date <= ?', $to." 23:59:59");

    if ( (isset($params['get_total_count']) and $params['get_total_count'] == 0) ) {
        $get_01['s_id'] = 's.id';
    } else {
        $get_01['s_id'] = new Zend_Db_Expr('SQL_CALC_FOUND_ROWS s.id');
    }
    
    $get_02 = array(
        'area_name'         => 'a.name',
        'province_name'     => 'rm.name',
        'sales_area'        => 'sb.name',
        'store_code'        => 's.store_code',
        'warehouse_id'      => 'w.id',
        'store_name'        => 's.name',
        'distributor_code'  => 'd.distributor_code',
        'distributor_name'  => 'd.title',
        'provience_name'    => 'rm.name',
        'activated_date'    => 'i.activated_date',
        'sellout'           => new Zend_Db_Expr("(".$sub_select.")"),
        'stock'             => new Zend_Db_Expr("COUNT(i.imei_sn)"),
        'imeisn'            => 'i.imei_sn',
        'instock'           => 'i.out_date',
        'category'          => 'g.cat_id',
        'good'              => 'i.good_id',
        'warehouse_name'    => 'w.name',
        'color'             => 'i.good_color',
        'brand_name'        => 'b.name',
    );
    $get = $get_01 + $get_02;
    $select = $db->select()
        ->from(array('s' => 'store'),$get)
        ->join(array('d' => WAREHOUSE_DB.'.distributor'),'d.id = s.d_id',array())
        ->join(array('rm' => 'regional_market'),'s.province_id = rm.id',array())
        ->join(array('a' => 'area'),'rm.area_id = a.id',array())
        ->joinLeft(array('i' => WAREHOUSE_DB.'.imei'),'s.id = i.store_id',array())
        ->joinleft(array('g'   => WAREHOUSE_DB.'.good'), 'i.good_id = g.id', array())
        ->joinleft(array('w'   => WAREHOUSE_DB.'.warehouse'), 'w.id = d.warehouse_id', array())
        ->joinLeft(array('b' => WAREHOUSE_DB.'.brand'),'b.id = g.brand_id',array())
        ->joinLeft(array('sb' => 'sub_area'),'sb.id = s.agency',array());
    
    $select->where('i.imei_sn NOT IN (select imei from timing_sale)');
    $select->group('s.id');
    $select->order('a.name ASC');

    if(isset($params['status']) && $params['status'] == 1){                 // Check IMEI Status not Activated and not Timing
        $select->where('i.activated_date IS null');
        $select->where('i.imei_sn NOT IN (select imei from timing_sale)');
    }
    
    if(isset($params['status']) and $params['status'] == 2){                // Check IMEI Status not Timing
        $select->where('i.imei_sn NOT IN (select imei from timing_sale)');
    }
    
    if(isset($params['status']) and $params['status'] == 3){                // Check IMEI Status Activated but not Timing
        $select->where('i.activated_date IS NOT NULL');
        $select->where('i.imei_sn NOT IN (select imei from timing_sale)');
    }

//Case Warehouse
if(isset($params['warehouse_id']) and $params['warehouse_id']){
   $select->where('i.warehouse_id =?',$params['warehouse_id']);
}

if ( isset($params['s_code']) && $params['s_code'] ) {
    $select->where('s.store_code LIKE ?', '%'.$params['s_code'].'%');
}

if ( isset($params['s_name']) && $params['s_name'] ) {
    $select->where('s.name LIKE ?', '%'.$params['s_name'].'%');
}

// Add Filter Model
if (isset($params['good_id']) && $params['good_id']) {
    if (is_array($params['good_id']) && count($params['good_id']))
        $select->where('i.good_id IN (?)', $params['good_id']);
    elseif (is_numeric($params['good_id']))
        $select->where('i.good_id = ?', intval($params['good_id']));
    else
        $select->where('1=0', 1);
}

// Add Filter Color
if (isset($params['color_id']) && $params['color_id']) {
    if (is_array($params['color_id']) && count($params['color_id']))
        $select->where('i.good_color IN (?)', $params['color_id']);
    elseif (is_numeric($params['color_id']))
        $select->where('i.good_color = ?', intval($params['color_id']));
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

// Permission for ASM / Sale Admin / Trainer
if ( isset($params['asm']) && $params['asm'] ) {
    $QAsm = new Application_Model_Asm();
    $list_regions = $QAsm->get_cache($params['asm']);
    $list_regions = isset($list_regions['province']) && is_array($list_regions['province']) ? $list_regions['province'] : array();
    if (count($list_regions) > 0)
        $select->where( 's.province_id IN (?)', $list_regions);
    else
        $select->where('1=0', 1);
}

// Permission for Sale
if (isset($params['sales_id']) && intval($params['sales_id']) > 0) {
    $select->where('sb.staff_id =?',$params['sales_id']);
}

// Permission for PC & PCDB
if ( isset($params['pc_id']) && $params['pc_id'] ) {
    $select->joinRight(array('ssl' => 'store_staff'), 'ssl.store_id = s.id',array());
    $log_where = $this->getAdapter()->quoteInto('ssl.staff_id = ?', $params['pc_id']). " AND " . $this->getAdapter()->quoteInto('ssl.is_leader IN (?)',array(0,7));
    $select->where($log_where);
}

if (isset($params['get_total_count']) && $params['get_total_count'] == 0) {
    $select_p = $db->select()
    ->from(array('pa' => $select), array(
        'sum_stock' => new Zend_Db_Expr("SUM( pa.stock )"),
    ));

    return $db->fetchRow($select_p);
}

$result = $db->fetchAll($select);
return $result;
}
// End DOI Export_List 111


// Start DOI Export_Model_List 222
function fetchPagination_DOI_ExportModelList($page, $limit, &$total, $params) {

    set_time_limit(0);
    ini_set('memory_limit', '-1');
    error_reporting(~E_ALL);
    ini_set("display_error", '0');
    $d1 = explode('/', $params['from']);
    $from = $d1[2].'-'.$d1[1].'-'.$d1[0];
    $d2 = explode('/', $params['to']);
    $to = $d2[2].'-'.$d2[1].'-'.$d2[0];
    $mtd = ((strtotime($to) - strtotime($from)) / (60*60*24)) + 1;
    $db = Zend_Registry::get('db');

    $sub_select = $db->select()
    ->from(array('s2'   => 'store'), array('COUNT(i2.imei_sn)'))
    ->joinleft(array('i2'   => WAREHOUSE_DB.'.imei'), 's2.id = i2.store_id', array())
    ->where('i2.activated_date >= ?', $from." 00:00:00")
    ->where('i2.activated_date <= ?', $to." 23:59:59");

    if ( (isset($params['get_total_count']) and $params['get_total_count'] == 0) ) {
        $get_01['s_id'] = 's.id';
    } else {
        $get_01['s_id'] = new Zend_Db_Expr('SQL_CALC_FOUND_ROWS s.id');
    }
    
    $get_02 = array(
        'area_name'         => 'a.name',
        'province_name'     => 'rm.name',
        'sales_area'        => 'sb.name',
        'store_code'        => 's.store_code',
        'warehouse_id'      => 'w.id',
        'store_name'        => 's.name',
        'distributor_code'  => 'd.distributor_code',
        'distributor_name'  => 'd.title',
        'provience_name'    => 'rm.name',
        'activated_date'    => 'i.activated_date',
        'sellout'           => new Zend_Db_Expr("(".$sub_select.")"),
        'stock'             => new Zend_Db_Expr("COUNT(i.imei_sn)"),
        'imeisn'            => 'i.imei_sn',
        'instock'           => 'i.out_date',
        'category'          => 'g.cat_id',
        'good'              => 'i.good_id',
        'warehouse_name'    => 'w.name',
        'color'             => 'i.good_color',
        'brand_name'        => 'b.name',
    );
    $get = $get_01 + $get_02;
    $select = $db->select()
        ->from(array('s' => 'store'),$get)
        ->join(array('d' => WAREHOUSE_DB.'.distributor'),'d.id = s.d_id',array())
        ->join(array('rm' => 'regional_market'),'s.province_id = rm.id',array())
        ->join(array('a' => 'area'),'rm.area_id = a.id',array())
        ->joinLeft(array('i' => WAREHOUSE_DB.'.imei'),'s.id = i.store_id',array())
        ->joinleft(array('g'   => WAREHOUSE_DB.'.good'), 'i.good_id = g.id', array())
        ->joinleft(array('w'   => WAREHOUSE_DB.'.warehouse'), 'w.id = d.warehouse_id', array())
        ->joinLeft(array('b' => WAREHOUSE_DB.'.brand'),'b.id = g.brand_id',array())
        ->joinLeft(array('sb' => 'sub_area'),'sb.id = s.agency',array());

    //export By model
    if(isset($params['good_model']) && $params['good_model']){
        $select->group('i.good_id');
        $select->group('i.good_color');
    }
    
    $select->where('i.imei_sn NOT IN (select imei from timing_sale)');
    $select->group('s.id');
    $select->order('a.name ASC');

    if(isset($params['status']) && $params['status'] == 1){                 // Check IMEI Status not Activated and not Timing
        $select->where('i.activated_date IS null');
        $select->where('i.imei_sn NOT IN (select imei from timing_sale)');
    }
    
    if(isset($params['status']) and $params['status'] == 2){                // Check IMEI Status not Timing
        $select->where('i.imei_sn NOT IN (select imei from timing_sale)');
    }
    
    if(isset($params['status']) and $params['status'] == 3){                // Check IMEI Status Activated but not Timing
        $select->where('i.activated_date IS NOT NULL');
        $select->where('i.imei_sn NOT IN (select imei from timing_sale)');
    }

//Case Warehouse
if(isset($params['warehouse_id']) and $params['warehouse_id']){
   $select->where('i.warehouse_id =?',$params['warehouse_id']);
}

if ( isset($params['s_code']) && $params['s_code'] ) {
    $select->where('s.store_code LIKE ?', '%'.$params['s_code'].'%');
}

if ( isset($params['s_name']) && $params['s_name'] ) {
    $select->where('s.name LIKE ?', '%'.$params['s_name'].'%');
}

// Add Filter Model
if (isset($params['good_id']) && $params['good_id']) {
    if (is_array($params['good_id']) && count($params['good_id']))
        $select->where('i.good_id IN (?)', $params['good_id']);
    elseif (is_numeric($params['good_id']))
        $select->where('i.good_id = ?', intval($params['good_id']));
    else
        $select->where('1=0', 1);
}

// Add Filter Color
if (isset($params['color_id']) && $params['color_id']) {
    if (is_array($params['color_id']) && count($params['color_id']))
        $select->where('i.good_color IN (?)', $params['color_id']);
    elseif (is_numeric($params['color_id']))
        $select->where('i.good_color = ?', intval($params['color_id']));
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

// Permission for ASM / Sale Admin / Trainer
if ( isset($params['asm']) && $params['asm'] ) {
    $QAsm = new Application_Model_Asm();
    $list_regions = $QAsm->get_cache($params['asm']);
    $list_regions = isset($list_regions['province']) && is_array($list_regions['province']) ? $list_regions['province'] : array();
    if (count($list_regions) > 0)
        $select->where( 's.province_id IN (?)', $list_regions);
    else
        $select->where('1=0', 1);
}

// Permission for Sale
if (isset($params['sales_id']) && intval($params['sales_id']) > 0) {
    $select->where('sb.staff_id =?',$params['sales_id']);
}

// Permission for PC & PCDB
if ( isset($params['pc_id']) && $params['pc_id'] ) {
    $select->joinRight(array('ssl' => 'store_staff'), 'ssl.store_id = s.id',array());
    $log_where = $this->getAdapter()->quoteInto('ssl.staff_id = ?', $params['pc_id']). " AND " . $this->getAdapter()->quoteInto('ssl.is_leader IN (?)',array(0,7));
    $select->where($log_where);
}

if (isset($params['get_total_count']) && $params['get_total_count'] == 0) {
    $select_p = $db->select()
    ->from(array('pa' => $select), array(
        'sum_stock' => new Zend_Db_Expr("SUM( pa.stock )"),
    ));

    return $db->fetchRow($select_p);
}

$result = $db->fetchAll($select);
return $result;
}
// End DOI Export_Model_List 222

// Start DOI Export_IME_List 333
function fetchPagination_DOI_ExportImeiList($page, $limit, &$total, $params) {

    set_time_limit(0);
    ini_set('memory_limit', '-1');
    error_reporting(~E_ALL);
    ini_set("display_error", '0');
    $d1 = explode('/', $params['from']);
    $from = $d1[2].'-'.$d1[1].'-'.$d1[0];
    $d2 = explode('/', $params['to']);
    $to = $d2[2].'-'.$d2[1].'-'.$d2[0];
    $mtd = ((strtotime($to) - strtotime($from)) / (60*60*24)) + 1;
    $db = Zend_Registry::get('db');

    $sub_select = $db->select()
    ->from(array('s2'   => 'store'), array())
    ->joinleft(array('i2'   => WAREHOUSE_DB.'.imei'), 's2.id = i2.store_id', array())
    ->where('i2.activated_date >= ?', $from." 00:00:00")
    ->where('i2.activated_date <= ?', $to." 23:59:59");
  
    $get_02 = array(
        'area_name'         => 'a.name',
        'province_name'     => 'rm.name',
        'sales_area'        => 'sb.name',
        'store_code'        => 's.store_code',
        'warehouse_id'      => 'w.id',
        'store_name'        => 's.name',
        'distributor_code'  => 'd.distributor_code',
        'distributor_name'  => 'd.title',
        'provience_name'    => 'rm.name',
        'activated_date'    => 'i.activated_date',
        'imeisn'            => 'i.imei_sn',
        'model_type'        => 'i.type',
        'instock'           => 'i.out_date',
        'category'          => 'g.cat_id',
        'good'              => 'i.good_id',
        'warehouse_name'    => 'w.name',
        'color'             => 'i.good_color',
        'brand_name'        => 'b.name',
    );
    $get = $get_02;
    $select = $db->select()
        ->from(array('s' => 'store'),$get)
        ->join(array('d' => WAREHOUSE_DB.'.distributor'),'d.id = s.d_id',array())
        ->join(array('rm' => 'regional_market'),'s.province_id = rm.id',array())
        ->join(array('a' => 'area'),'rm.area_id = a.id',array())
        ->joinLeft(array('i' => WAREHOUSE_DB.'.imei'),'s.id = i.store_id',array())
        ->joinleft(array('g'   => WAREHOUSE_DB.'.good'), 'i.good_id = g.id', array())
        ->joinleft(array('w'   => WAREHOUSE_DB.'.warehouse'), 'w.id = d.warehouse_id', array())
        ->joinLeft(array('b' => WAREHOUSE_DB.'.brand'),'b.id = g.brand_id',array())
        ->joinLeft(array('sb' => 'sub_area'),'sb.id = s.agency',array());
    
    $select->where('i.imei_sn NOT IN (select imei from timing_sale)');
    $select->order('a.name ASC');

//export DOI imei
if(isset($params['list_imei']) && $params['list_imei']) {
    $select->group('i.imei_sn');
}

if(isset($params['status']) && $params['status'] == 1){                 // Check IMEI Status not Activated and not Timing
    $select->where('i.activated_date IS null');
    $select->where('i.imei_sn NOT IN (select imei from timing_sale)');
}

if(isset($params['status']) and $params['status'] == 2){                // Check IMEI Status not Timing
    $select->where('i.imei_sn NOT IN (select imei from timing_sale)');
}

if(isset($params['status']) and $params['status'] == 3){                // Check IMEI Status Activated but not Timing
    $select->where('i.activated_date IS NOT NULL');
    $select->where('i.imei_sn NOT IN (select imei from timing_sale)');
}

//Case Warehouse
if(isset($params['warehouse_id']) and $params['warehouse_id']){
   $select->where('i.warehouse_id =?',$params['warehouse_id']);
}

if ( isset($params['s_code']) && $params['s_code'] ) {
    $select->where('s.store_code LIKE ?', '%'.$params['s_code'].'%');
}

if ( isset($params['s_name']) && $params['s_name'] ) {
    $select->where('s.name LIKE ?', '%'.$params['s_name'].'%');
}

if(isset($params['stock_date']) && $params['stock_date']){
    $select->where('i.out_date <=?',$params['stock_date'].' 23:59:59');
}


// Add Filter Model
if (isset($params['good_id']) && $params['good_id']) {
    if (is_array($params['good_id']) && count($params['good_id']))
        $select->where('i.good_id IN (?)', $params['good_id']);
    elseif (is_numeric($params['good_id']))
        $select->where('i.good_id = ?', intval($params['good_id']));
    else
        $select->where('1=0', 1);
}

// Add Filter Color
if (isset($params['color_id']) && $params['color_id']) {
    if (is_array($params['color_id']) && count($params['color_id']))
        $select->where('i.good_color IN (?)', $params['color_id']);
    elseif (is_numeric($params['color_id']))
        $select->where('i.good_color = ?', intval($params['color_id']));
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

// Permission for ASM / Sale Admin / Trainer
if ( isset($params['asm']) && $params['asm'] ) {
    $QAsm = new Application_Model_Asm();
    $list_regions = $QAsm->get_cache($params['asm']);
    $list_regions = isset($list_regions['province']) && is_array($list_regions['province']) ? $list_regions['province'] : array();
    if (count($list_regions) > 0)
        $select->where( 's.province_id IN (?)', $list_regions);
    else
        $select->where('1=0', 1);
}

// Permission for Sale
if (isset($params['sales_id']) && intval($params['sales_id']) > 0) {
    $select->where('sb.staff_id =?',$params['sales_id']);
}

// Permission for PC & PCDB
if ( isset($params['pc_id']) && $params['pc_id'] ) {
    $select->joinRight(array('ssl' => 'store_staff'), 'ssl.store_id = s.id',array());
    $log_where = $this->getAdapter()->quoteInto('ssl.staff_id = ?', $params['pc_id']). " AND " . $this->getAdapter()->quoteInto('ssl.is_leader IN (?)',array(0,7));
    $select->where($log_where);
}

$result = $db->fetchAll($select);
return $result;
}
// End DOI Export_IME_List 333



function fetchPagination_DOI_BySellout($page, $limit, &$total, $params) {
    set_time_limit(0);
    ini_set('memory_limit', '-1');
    error_reporting(~E_ALL);
    ini_set("display_error", '0');
    $d1 = explode('/', $params['from']);
    $from = $d1[2].'-'.$d1[1].'-'.$d1[0];
    $d2 = explode('/', $params['to']);
    $to = $d2[2].'-'.$d2[1].'-'.$d2[0];
    $mtd = ((strtotime($to) - strtotime($from)) / (60*60*24)) + 1;
    $db = Zend_Registry::get('db');
    $sub_select = $db->select()
    ->from(array('d2'   => WAREHOUSE_DB.'.distributor'), array('COUNT(ts2.imei)'))
    ->join(array('i2'   => WAREHOUSE_DB.'.imei'), 'd2.id = i2.distributor_id', array())
    ->join(array('ts2'  => 'timing_sale'), 'i2.imei_sn = ts2.imei', array())
    ->where('ts2.time_add >= ?', $from." 00:00:00")
    ->where('ts2.time_add <= ?', $to." 23:59:59")
    ->where('d.id = d2.id')
    ->group('d2.id');
// Add Filter Model for sub_select
    if (isset($params['good_id']) && $params['good_id']) {
        if (is_array($params['good_id']) && count($params['good_id']))
            $sub_select->where('i2.good_id IN (?)', $params['good_id']);
        elseif (is_numeric($params['good_id']))
            $sub_select->where('i2.good_id = ?', intval($params['good_id']));
        else
            $sub_select->where('1=0', 1);
    }
// Add Filter Color for sub_select
    if (isset($params['color_id']) && $params['color_id']) {
        if (is_array($params['color_id']) && count($params['color_id']))
            $sub_select->where('i2.good_color IN (?)', $params['color_id']);
        elseif (is_numeric($params['color_id']))
            $sub_select->where('i2.good_color = ?', intval($params['color_id']));
        else
            $sub_select->where('1=0', 1);
    }
    $get = array(
        'd_id'      => new Zend_Db_Expr("SQL_CALC_FOUND_ROWS d.id"),
        'd_name'    => 'd.title',
        'd_type'    => new Zend_Db_Expr(
            "(CASE
            WHEN d.rank = 1 THEN 'ORG-WDS'
            WHEN d.rank = 2 THEN 'ORG'
            WHEN d.rank = 5 THEN 'ORG-Dtac/Advice'
            WHEN d.rank = 6 THEN 'ORG-Lotus/Power Buy'
            WHEN d.rank = 7 THEN 'Dealer'
            WHEN d.rank = 8 THEN 'HUB'
            WHEN d.rank = 9 THEN 'Laos'
            WHEN d.rank = 3 THEN 'Online and Staff'
            WHEN d.rank = 10 THEN 'Brand Shop/Service'
            WHEN d.rank = 11 THEN 'King Power'
            WHEN d.rank = 12 THEN 'Jaymart'
            WHEN d.rank = 13 THEN 'Brand Shop By Dealer'
            ELSE d.rank
            END)"),
        'grand_area'    => 'ga.name',
        'd_area'        => 'a.name',
        'sellout'       => new Zend_Db_Expr("(".$sub_select.")"),
        'stock'         => new Zend_Db_Expr("(COUNT(i.imei_sn) - COUNT(ts.imei))"),
        'selling_rate'  => new Zend_Db_Expr("( ( (".$sub_select.") / ".$mtd." ) )"),
        'safety_stock'  => new Zend_Db_Expr("(( ( (".$sub_select.") / ".$mtd." ) ) * ".$params['doi_rate'].")"),
        'indicator'     => new Zend_Db_Expr("( (COUNT(i.imei_sn) - COUNT(ts.imei)) - (( ( (".$sub_select.") / ".$mtd." ) ) * ".$params['doi_rate'].") )"),
    );
    $select = $db->select()
    ->from(array('d'    => WAREHOUSE_DB.'.distributor'), $get)
    ->join(array('rm'   => 'regional_market')   , 'd.region = rm.id'            , array())
    ->join(array('a'    => 'area')              , 'rm.area_id = a.id'           , array())
    ->joinLeft(array('gal' => 'grand_area_list'), 'a.id = gal.area'             , array())
    ->joinLeft(array('ga'  => 'grand_area')     , 'gal.grand_area_id = ga.id'   , array())
    ->join(array('i'    => WAREHOUSE_DB.'.imei'), 'd.id = i.distributor_id'     , array())
    ->joinLeft(array('ts' => 'timing_sale')     , 'i.imei_sn = ts.imei'         , array())
    ->where('d.del <> ?', 1)
    ->group('d.id')
    ->having('sellout > ?', 0)
    ->order('indicator ASC');
    if ( isset($params['d_id']) && $params['d_id'] ) {
        $select->where('d.id = ?', $params['d_id']);
    }
    if ( isset($params['d_name']) && $params['d_name'] ) {
        $select->where('d.title LIKE ?', '%'.$params['d_name'].'%');
    }
// Add Filter Model
    if (isset($params['good_id']) && $params['good_id']) {
        if (is_array($params['good_id']) && count($params['good_id']))
            $select->where('i.good_id IN (?)', $params['good_id']);
        elseif (is_numeric($params['good_id']))
            $select->where('i.good_id = ?', intval($params['good_id']));
        else
            $select->where('1=0', 1);
    }
// Add Filter Color
    if (isset($params['color_id']) && $params['color_id']) {
        if (is_array($params['color_id']) && count($params['color_id']))
            $select->where('i.good_color IN (?)', $params['color_id']);
        elseif (is_numeric($params['color_id']))
            $select->where('i.good_color = ?', intval($params['color_id']));
        else
            $select->where('1=0', 1);
    }
// Add Filter Grand Area [Table]
    if (isset($params['grand_area_id']) && $params['grand_area_id']) {
        if (is_array($params['grand_area_id']) && count($params['grand_area_id']))
            $select->where('ga.id IN (?)', $params['grand_area_id']);
        elseif (is_numeric($params['grand_area_id']))
            $select->where('ga.id = ?', intval($params['grand_area_id']));
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
// Permission for ASM / Sale Admin / Trainer
    if ( isset($params['asm']) && $params['asm'] ) {
        $QAsm = new Application_Model_Asm();
        $list_regions = $QAsm->get_cache($params['asm']);
        $list_regions = isset($list_regions['district']) && is_array($list_regions['district']) ? $list_regions['district'] : array();
        if (count($list_regions) > 0)
            $select->where( 'd.district IN (?)', $list_regions);
        else
            $select->where('1=0', 1);
    }
// Permission for AM
    if ( isset($params['am']) && $params['am'] ) {
        $QAm = new Application_Model_Am();
        $list_org = $QAm->get_cache($params['am']);
        $list_org = isset($list_org['store_type']) && is_array($list_org['store_type']) ? $list_org['store_type'] : array();
        if (count($list_org) > 0)
            $select->where( 'd.ka_type IN (?)', $list_org);
        else
            $select->where('1=0', 1);
    }
/*
// Permission for Sale
if (isset($params['sale_id']) && intval($params['sale_id']) > 0) {
$select->joinRight(array('ssl' => 'store_staff'), 'ssl.store_id = st.id', array());
$log_where = $this->getAdapter()->quoteInto('ssl.staff_id = ?', $params['sale_id']).
" AND " . $this->getAdapter()->quoteInto('ssl.is_leader = ?', 1);
$select->where($log_where);
}*/
if ($limit)
    $select->limitPage($page, $limit);
//print_r($params);
//echo $select; die;
$result = $db->fetchAll($select);
if ($limit)
    $total = $db->fetchOne("select FOUND_ROWS()");
return $result;
}
// get Sellout By Store for AM
function getSelloutByShopAM($staff_id) {
    $from = date('Y-m-01');
    $to = date('Y-m-t');
    $db = Zend_Registry::get('db');
    $sub_select = $db->select()
    ->from(array('am' => 'am'), array('org_id'=>'am.org_id'))
    ->where('am.staff_id = ?', $staff_id);
    $get = array(
        'area_name'     => 'a.name',
        'st_id'         => 'st.id',
        'st_name'       => 'st.name',
        'st_del'        => 'st.del',
        'st_type'       => 'o.org_name',
        'st_target'     => 'opt.target',
        'st_sellout'    => new Zend_Db_Expr("COUNT(ts.imei)"),
        'st_price'      => new Zend_Db_Expr("SUM(gkl.price)"),
    );
    $select = $db->select()
    ->from(array('st' => 'store'), $get)
    ->join(array('o'  => 'org')             , 'st.org_dealer = o.org_id'    , array())
    ->join(array('rm' => 'regional_market') , 'st.regional_market = rm.id'  , array())
    ->join(array('a'  => 'area')            , 'rm.area_id = a.id'           , array())
    ->joinLeft(array('opt' => 'oppo_pc_target'),
        "   st.id = opt.store_id
        AND opt.from_date >= '".$from."'
        AND opt.to_date <= '".$to."'
        ", array())
    ->joinLeft(array('t'  => 'timing')      , 'st.id = t.store'             , array())
    ->joinLeft(array('ts' => 'timing_sale') , 't.id = ts.timing_id'         , array())
    ->joinLeft(array('gkl'=> 'good_kpi_log'),
        "   gkl.good_id = ts.product_id
        AND gkl.color_id = ts.model_id
        AND t.created_at >= CONCAT(gkl.from_date,' 00:00:00')
        AND t.created_at <= CONCAT(gkl.to_date,' 23:59:59')
        ", array())
    ->where('st.org_dealer IN (?)', $sub_select)
//->where('st.id IN (?)', array(3370, 9961, 10496))
    ->group('st.id')
    ->order(array('a.name ASC','st.id ASC'));
//echo $select; die;
    $result = $db->fetchAll($select);
    return $result;
}
// get KA Sellout
function getKeyAccountSellout($params) {
    $d = explode('/', $params['from']);
    $from = $d[2].'-'.$d[1].'-'.$d[0];
    $d = explode('/', $params['to']);
    $to = $d[2].'-'.$d[1].'-'.$d[0];
    $db = Zend_Registry::get('db');
    $get_01 = array(
        'channel_id'    => 'o.org_id',
        'channel_name'  => 'o.org_name',
        'sellout'       => new Zend_Db_Expr("COUNT(ts.imei)"),
    );
    $period_loop = ( (strtotime($to) - strtotime($from)) / (24*60*60) ) + 1;
    $get_02 = array();
    for ($i=0;$i<$period_loop;$i++) {
        $day =  date('Y-m-d', strtotime("+".$i." Day", strtotime($from)));
        $day_text =  date('d-M', strtotime("+".$i." Day", strtotime($from)));
        $moth_text =  date('M-Y', strtotime("+".$i." Day", strtotime($from)));
        $get_02[$day_text] = new Zend_Db_Expr("COUNT(CASE WHEN t.created_at >= '".$day." 00:00:00' AND t.created_at <= '".$day." 23:59:59' THEN ts.imei END)");
    }
    if ($params['chk_compare'] == 1) { $get_01['channel_name'] = new Zend_Db_Expr("CONCAT(o.org_name,' (".$moth_text.")')"); }
    $get = $get_01 + $get_02;
    $select = $db->select()
    ->from(array('st' => 'store'), $get)
    ->join(array('o'  => 'org')         , 'st.org_dealer = o.org_id'    , array())
    ->join(array('t'  => 'timing')      , 'st.id = t.store'             , array())
    ->join(array('ts' => 'timing_sale') , 't.id = ts.timing_id'         , array())
    ->join(array('i'  => WAREHOUSE_DB.'.imei'), 'ts.imei = i.imei_sn'   , array())
    ->join(array('dma'=> 'distributor_map_am'),
        "   (
        CASE
        WHEN dma.org_id = 2 THEN o.org_id IN (2,30)
        ELSE o.org_id = dma.org_id
        END
        )
        AND i.distributor_id = dma.d_id
        ", array())
    ->where('st.rank <> ?', 5)
    ->where('o.store_type_id = ?', 1)
    ->where('o.org_id NOT IN (?)', array(3,5,10,12))
    ->where('t.created_at >= ?', $from." 00:00:00")
    ->where('t.created_at <= ?', $to." 23:59:59")
    ->group('o.org_id')
    ->order('o.org_name ASC');
// Filter Channel
    if (isset($params['channel_id']) && $params['channel_id']) {
        if (is_array($params['channel_id']) && count($params['channel_id']))
            $select->where('o.org_id IN (?)', $params['channel_id']);
        elseif (is_numeric($params['channel_id']))
            $select->where('o.org_id = ?', intval($params['channel_id']));
        else
            $select->where('1=0', 1);
    }
// Filter Model
    if (isset($params['good_id']) && $params['good_id']) {
// Check Others
        if ( in_array(0, $params['good_id']) ) {
            $good_filter = explode(",", $params['good_filter']);
            $where_good = array_diff($good_filter, $params['good_id']);
            if ( !empty($where_good) ) { $select->where('i.good_id NOT IN (?)', $where_good); }
        } else {
            $select->where('i.good_id IN (?)', $params['good_id']);
        }
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
    if ( isset($params['am']) && $params['am'] ) {
        $QAm = new Application_Model_Am();
        $list_org = $QAm->get_cache($params['am']);
        $list_org = isset($list_org['store_type']) && is_array($list_org['store_type']) ? $list_org['store_type'] : array();
        if (count($list_org) > 0)
            $select->where( 'st.org_dealer IN (?)', $list_org);
        else
            $select->where('1=0', 1);
    }
// echo $select; die;
    $result = $db->fetchAll($select);
    return $result;
}
// get KA Sellout : %Share
function getKeyAccountSelloutShare($params) {
    $d = explode('/', $params['from']);
    $from = $d[2].'-'.$d[1].'-'.$d[0];
    $d = explode('/', $params['to']);
    $to = $d[2].'-'.$d[1].'-'.$d[0];
    $db = Zend_Registry::get('db');
    $get = array(
        'product_id'    => 'g.id',
        'product_code'  => 'g.name',
        'product_name'  => 'g.desc',
        'sellout'       => new Zend_Db_Expr("COUNT(ts.imei)"),
    );
    $select = $db->select()
    ->from(array('st' => 'store'), $get)
    ->join(array('o'  => 'org')         , 'st.org_dealer = o.org_id'    , array())
    ->join(array('t'  => 'timing')      , 'st.id = t.store'             , array())
    ->join(array('ts' => 'timing_sale') , 't.id = ts.timing_id'         , array())
    ->join(array('i'  => WAREHOUSE_DB.'.imei'), 'ts.imei = i.imei_sn'   , array())
    ->join(array('g'  => WAREHOUSE_DB.'.good'), 'i.good_id = g.id'      , array())
    ->join(array('dma'=> 'distributor_map_am'),
        "   (
        CASE
        WHEN dma.org_id = 2 THEN o.org_id IN (2,30)
        ELSE o.org_id = dma.org_id
        END
        )
        AND i.distributor_id = dma.d_id
        ", array())
    ->where('st.rank <> ?', 5)
    ->where('o.store_type_id = ?', 1)
    ->where('o.org_id NOT IN (?)', array(3,5,10,12))
    ->where('t.created_at >= ?', $from." 00:00:00")
    ->where('t.created_at <= ?', $to." 23:59:59")
    ->group('g.id')
    ->order('g.name ASC');
// Filter Channel
    if (isset($params['channel_id']) && $params['channel_id']) {
        if (is_array($params['channel_id']) && count($params['channel_id']))
            $select->where('o.org_id IN (?)', $params['channel_id']);
        elseif (is_numeric($params['channel_id']))
            $select->where('o.org_id = ?', intval($params['channel_id']));
        else
            $select->where('1=0', 1);
    }
/*
// Filter Model
if (isset($params['good_id']) && $params['good_id']) {
if (is_array($params['good_id']) && count($params['good_id']))
$select->where('i.good_id IN (?)', $params['good_id']);
elseif (is_numeric($params['good_id']))
$select->where('i.good_id = ?', intval($params['good_id']));
else
$select->where('1=0', 1);
}
*/
// Filter Model
if (isset($params['good_id']) && $params['good_id']) {
// Check Others
    if ( in_array(0, $params['good_id']) ) {
        $good_filter = explode(",", $params['good_filter']);
        $where_good = array_diff($good_filter, $params['good_id']);
        if ( !empty($where_good) ) { $select->where('i.good_id NOT IN (?)', $where_good); }
    } else {
        $select->where('i.good_id IN (?)', $params['good_id']);
    }
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
if ( isset($params['am']) && $params['am'] ) {
    $QAm = new Application_Model_Am();
    $list_org = $QAm->get_cache($params['am']);
    $list_org = isset($list_org['store_type']) && is_array($list_org['store_type']) ? $list_org['store_type'] : array();
    if (count($list_org) > 0)
        $select->where( 'st.org_dealer IN (?)', $list_org);
    else
        $select->where('1=0', 1);
}
// echo $select; die;
$result = $db->fetchAll($select);
return $result;
}
// Sellout Analysis By Area [Days]
function analysisByAreaDays($params) {
        //echo "<pre>"; print_r($params); echo "<br/>";
    $d1 = explode('/', $params['from']);
    $from = $d1[2].'-'.$d1[1].'-'.$d1[0];
    $d2 = explode('/', $params['to']);
    $to = $d2[2].'-'.$d2[1].'-'.$d2[0];
    $db = Zend_Registry::get('db');
    $get_01 = array(
        'area_id'   => 'a.id',
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
        'sellout'   => new Zend_Db_Expr("COUNT(ts.imei)"),
    );
    $period_loop = ( (strtotime($to) - strtotime($from)) / (24*60*60) ) + 1;
    $get_02 = array();
    for ($i=0;$i<$period_loop;$i++) {
        $day =  date('Y-m-d', strtotime("+".$i." Day", strtotime($from)));
        $day_text =  date('d-M', strtotime("+".$i." Day", strtotime($from)));
        $get_02[$day_text] = new Zend_Db_Expr("COUNT(CASE WHEN t.created_at >= '".$day." 00:00:00' AND t.created_at <= '".$day." 23:59:59' THEN ts.imei END)");
    }
    $get = $get_01 + $get_02;
    $select = $db->select()
    ->from(array('a'  => 'area'), $get)
    ->join(array('rm' => 'regional_market'), 'a.id = rm.area_id', array())
    ->join(array('st' => 'store'), 'rm.id = st.regional_market', array())
    ->join(array('t'  => 'timing'), 
        "   st.id = t.store
        AND t.created_at >= '".$from." 00:00:00' 
        AND t.created_at <= '".$to." 23:59:59' 
        ", array())
    ->join(array('ts' => 'timing_sale')         , 't.id = ts.timing_id', array())
    ->where('ts.imei NOT IN (select imei from timing_control)')
    ->where('a.id NOT IN (?)', array(48,49,72));

    if ( isset($params['product_id']) && $params['product_id'] ) {
        $select->where('ts.product_id IN (?)', $params['product_id']);
    } else {
        if ( isset($params['report_model']) && $params['report_model'] = 1 ) { 
            $select->where('1=0', 1);
        }

    }
        // For Sellout Analysis By Model #1
    if ( isset($params['report_model']) && $params['report_model'] = 1 ) {
        $select->join(array('g' => WAREHOUSE_DB.'.good'), 'ts.product_id = g.id', 
            array('model_name'=>new Zend_Db_Expr("CONCAT('[',g.name,'] ',g.desc)")));
        $select->group(new Zend_Db_Expr('area_name, model_name WITH ROLLUP'));
    } else {
        $select->group(new Zend_Db_Expr('area_name WITH ROLLUP'));
    }
        // Main Select 
    $main_get = array(
        'zone_name' => new Zend_Db_Expr("(CASE WHEN AAA.area_name IS NULL THEN 'Laos' ELSE AAA.area_name END)"),
        'AAA.*'
    );
    $main_select = $db->select()
    ->from(array('AAA' => $select), $main_get)
    ->order(new Zend_Db_Expr("(CASE WHEN zone_name = 'Laos' THEN 1 ELSE 2 END) ASC, zone_name ASC"));
    if ( isset($params['area_id']) && $params['area_id'] ) {
        $area_temp = implode("|", $params['area_id']);
        $area_temp = $area_temp."|Laos";
        $main_select->having('(AAA.area_id IN (?)', $params['area_id']);
        $main_select->orHaving('zone_name REGEXP ?)', $area_temp);
    } else {
        if (isset($params['asm']) && $params['asm']) {

        } else {
            $main_select->having('zone_name = ?', 'Laos');
        }
    }
        // For Sellout Analysis By Model #2
    if ( isset($params['report_model']) && $params['report_model'] = 1 ) {
        $main_select->having('(AAA.model_name IS NOT NULL OR zone_name = ?)', 'Laos');
    }
        // ASM Permission
    if (isset($params['asm']) && $params['asm']) {
        $QAsm = new Application_Model_Asm();
        $list_regions = $QAsm->get_cache($params['asm']);
        $list_regions = isset($list_regions['area']) && is_array($list_regions['area']) ? $list_regions['area'] : array();
        if (count($list_regions) > 0)
            $main_select->having('AAA.area_id IN (?)', $list_regions);
        else
            $main_select->where('1=0', 1);
    }
        // echo $main_select; die;
    $result = $db->fetchAll($main_select);
    return $result;
}
    // Sellout Analysis By Area [Months]
function analysisByAreaMonths($params) {
        //echo "<pre>"; print_r($params); echo "<br/>";
    $d1 = explode('/', $params['from']);
    $from = $d1[2].'-'.$d1[1].'-'.$d1[0];
    $d2 = explode('/', $params['to']);
    $to = $d2[2].'-'.$d2[1].'-'.$d2[0];
    $start = new DateTime($from);
    $end   = new DateTime( date("Y-m-30", strtotime($to)) );
    $diff  = $start->diff($end);
    $period_loop = $diff->format('%y') * 12 + $diff->format('%m') + 1;
    $db = Zend_Registry::get('db');
    $get_01 = array(
        'area_id'   => 'a.id',
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
        'sellout'   => new Zend_Db_Expr("COUNT(ts.imei)"),
    );
    $get_02 = array();
    for ($i=0;$i<$period_loop;$i++) {
        $day_start =  date('Y-m-01', strtotime("+".$i." Month", strtotime($from)));
        $day_end =  date('Y-m-t', strtotime("+".$i." Month", strtotime($from)));
        $day_text =  date('M-Y', strtotime("+".$i." Month", strtotime($from)));
        $get_02[$day_text] = new Zend_Db_Expr("COUNT(CASE WHEN t.created_at >= '".$day_start." 00:00:00' AND t.created_at <= '".$day_end." 23:59:59' THEN ts.imei END)");
    }
    $get = $get_01 + $get_02;
    $select = $db->select()
    ->from(array('a'  => 'area'), $get)
    ->join(array('rm' => 'regional_market'), 'a.id = rm.area_id', array())
    ->join(array('st' => 'store'), 'rm.id = st.regional_market', array())
    ->joinLeft(array('t'  => 'timing'), 
        "   st.id = t.store
        AND t.created_at >= '".$from." 00:00:00' 
        AND t.created_at <= '".$to." 23:59:59' 
        ", array())
    ->join(array('ts' => 'timing_sale'), 't.id = ts.timing_id', array())
    ->where('ts.imei NOT IN (select imei from timing_control)')
    ->where('a.id NOT IN (?)', array(48,49,72));
    if ( isset($params['product_id']) && $params['product_id'] ) {
        $select->where('ts.product_id IN (?)', $params['product_id']);
    } else {
        if ( isset($params['report_model']) && $params['report_model'] = 1 ) { 
            $select->where('1=0', 1);
        }

    }
            // For Sellout Analysis By Model #1
    if ( isset($params['report_model']) && $params['report_model'] = 1 ) {
        $select->join(array('g' => WAREHOUSE_DB.'.good'), 'ts.product_id = g.id', 
            array('model_name'=>new Zend_Db_Expr("CONCAT('[',g.name,'] ',g.desc)")));
        $select->group(new Zend_Db_Expr('area_name, model_name WITH ROLLUP'));
    } else {
        $select->group(new Zend_Db_Expr('area_name WITH ROLLUP'));
    }
            // Main Select 
    $main_get = array(
        'zone_name' => new Zend_Db_Expr("(CASE WHEN AAA.area_name IS NULL THEN 'Laos' ELSE AAA.area_name END)"),
        'AAA.*'
    );
    $main_select = $db->select()
    ->from(array('AAA' => $select), $main_get)
    ->order(new Zend_Db_Expr("(CASE WHEN zone_name = 'Laos' THEN 1 ELSE 2 END) ASC, zone_name ASC"));
    if ( isset($params['area_id']) && $params['area_id'] ) {
        $area_temp = implode("|", $params['area_id']);
        $area_temp = $area_temp."|Laos";
        $main_select->having('(AAA.area_id IN (?)', $params['area_id']);
        $main_select->orHaving('zone_name REGEXP ?)', $area_temp);
    } else {
        if (isset($params['asm']) && $params['asm']) {

        } else {
            $main_select->having('zone_name = ?', 'Laos');
        }
    }
            // For Sellout Analysis By Model #2
    if ( isset($params['report_model']) && $params['report_model'] = 1 ) {
        $main_select->having('(AAA.model_name IS NOT NULL OR zone_name = ?)', 'Laos');
    }
            // ASM Permission
    if (isset($params['asm']) && $params['asm']) {
        $QAsm = new Application_Model_Asm();
        $list_regions = $QAsm->get_cache($params['asm']);
        $list_regions = isset($list_regions['area']) && is_array($list_regions['area']) ? $list_regions['area'] : array();
        if (count($list_regions) > 0)
            $main_select->having('AAA.area_id IN (?)', $list_regions);
        else
            $main_select->where('1=0', 1);
    }
            // echo $main_select; die;
    $result = $db->fetchAll($main_select);
    return $result;
}
        // Sellout Analysis By Channel [Days]
function analysisByChannelDays($params) {
            //echo "<pre>"; print_r($params); echo "<br/>";
    $d1 = explode('/', $params['from']);
    $from = $d1[2].'-'.$d1[1].'-'.$d1[0];
    $d2 = explode('/', $params['to']);
    $to = $d2[2].'-'.$d2[1].'-'.$d2[0];
    $db = Zend_Registry::get('db');
    $get_01 = array(
        'channel_name' => new Zend_Db_Expr(
            "   (CASE
            WHEN a.id = 48 THEN 'OPPO Thai'
            WHEN st.org_dealer IN (1,19) THEN 'Dealer' 
            WHEN st.org_dealer IN (12,5,20,3,26,10,41,42,43) THEN 'Operator' 
            WHEN st.org_dealer IN (27,32,38) THEN 'KR' 
            WHEN st.org_dealer IN (16,18,33,34,37,23,22,21,31,40,39,47) THEN 'Brandshop' ELSE 'KA' 
            END)
            "),
        'sellout'   => new Zend_Db_Expr("COUNT(ts.imei)"),
    );
    $period_loop = ( (strtotime($to) - strtotime($from)) / (24*60*60) ) + 1;
    $get_02 = array();
    for ($i=0;$i<$period_loop;$i++) {
        $day =  date('Y-m-d', strtotime("+".$i." Day", strtotime($from)));
        $day_text =  date('d-M', strtotime("+".$i." Day", strtotime($from)));
        $get_02[$day_text] = new Zend_Db_Expr("COUNT(CASE WHEN t.created_at >= '".$day." 00:00:00' AND t.created_at <= '".$day." 23:59:59' THEN ts.imei END)");
    }
    $get = $get_01 + $get_02;
    $select = $db->select()
    ->from(array('a'  => 'area'), $get)
    ->join(array('rm' => 'regional_market'), 'a.id = rm.area_id'    , array())
    ->join(array('st' => 'store')   , 'rm.id = st.regional_market'  , array())
    ->join(array('t'  => 'timing'), 
        "   st.id = t.store
        AND t.created_at >= '".$from." 00:00:00' 
        AND t.created_at <= '".$to." 23:59:59' 
        ", array())
    ->join(array('ts' => 'timing_sale')         , 't.id = ts.timing_id', array())
    ->where('a.id NOT IN (?)', array(49,72))
    ->where('ts.imei NOT IN (select imei from timing_control)')
    ->group('channel_name')
    ->order(new Zend_Db_Expr(
        "(CASE 
        WHEN channel_name = 'OPPO Thai' THEN 1 
        WHEN channel_name = 'Dealer'    THEN 2 
        WHEN channel_name = 'Brandshop' THEN 3 
        WHEN channel_name = 'KA'        THEN 4 
        WHEN channel_name = 'KR'        THEN 5 
        WHEN channel_name = 'Operator'  THEN 6 
        ELSE 7 
        END) ASC"));

    if ( isset($params['product_id']) && $params['product_id'] ) {
        $select->where('ts.product_id IN (?)', $params['product_id']);
    }
                // ASM Permission
    if (isset($params['asm']) && $params['asm']) {
        $QAsm = new Application_Model_Asm();
        $list_regions = $QAsm->get_cache($params['asm']);
        $list_regions = isset($list_regions['area']) && is_array($list_regions['area']) ? $list_regions['area'] : array();
        if (count($list_regions) > 0)
            $select->where('a.id IN (?)', $list_regions);
        else
            $select->where('1=0', 1);
    }
                // echo $select; die;
    $result = $db->fetchAll($select);
    return $result;
}
            // Sellout Analysis By Channel [Months]
function analysisByChannelMonths($params) {
                //echo "<pre>"; print_r($params); echo "<br/>";
    $d1 = explode('/', $params['from']);
    $from = $d1[2].'-'.$d1[1].'-'.$d1[0];
    $d2 = explode('/', $params['to']);
    $to = $d2[2].'-'.$d2[1].'-'.$d2[0];
    $start = new DateTime($from);
    $end   = new DateTime( date("Y-m-30", strtotime($to)) );
    $diff  = $start->diff($end);
    $period_loop = $diff->format('%y') * 12 + $diff->format('%m') + 1;
    $db = Zend_Registry::get('db');
    $get_01 = array(
        'channel_name' => new Zend_Db_Expr(
            "   (CASE
            WHEN a.id = 48 THEN 'OPPO Thai'
            WHEN st.org_dealer IN (1,19) THEN 'Dealer' 
            WHEN st.org_dealer IN (12,5,20,3,26,10,41,42,43) THEN 'Operator' 
            WHEN st.org_dealer IN (27,32,38) THEN 'KR' 
            WHEN st.org_dealer IN (16,18,33,34,37,23,22,21,31,40,39,47) THEN 'Brandshop' ELSE 'KA' 
            END)
            "),
        'sellout'   => new Zend_Db_Expr("COUNT(ts.imei)"),
    );
    $get_02 = array();
    for ($i=0;$i<$period_loop;$i++) {
        $day_start =  date('Y-m-01', strtotime("+".$i." Month", strtotime($from)));
        $day_end =  date('Y-m-t', strtotime("+".$i." Month", strtotime($from)));
        $day_text =  date('M-Y', strtotime("+".$i." Month", strtotime($from)));
        $get_02[$day_text] = new Zend_Db_Expr("COUNT(CASE WHEN t.created_at >= '".$day_start." 00:00:00' AND t.created_at <= '".$day_end." 23:59:59' THEN ts.imei END)");
    }
    $get = $get_01 + $get_02;
    $select = $db->select()
    ->from(array('a'  => 'area'), $get)
    ->join(array('rm' => 'regional_market'), 'a.id = rm.area_id'    , array())
    ->join(array('st' => 'store')   , 'rm.id = st.regional_market'  , array())
    ->join(array('t'  => 'timing'), 
        "   st.id = t.store
        AND t.created_at >= '".$from." 00:00:00' 
        AND t.created_at <= '".$to." 23:59:59' 
        ", array())
    ->join(array('ts' => 'timing_sale')         , 't.id = ts.timing_id', array())
    ->where('ts.imei NOT IN (select imei from timing_control)')
    ->where('a.id NOT IN (?)', array(49,72))
    ->group('channel_name')
    ->order(new Zend_Db_Expr(
        "(CASE 
        WHEN channel_name = 'OPPO Thai' THEN 1 
        WHEN channel_name = 'Dealer'    THEN 2 
        WHEN channel_name = 'Brandshop' THEN 3 
        WHEN channel_name = 'KA'        THEN 4 
        WHEN channel_name = 'KR'        THEN 5 
        WHEN channel_name = 'Operator'  THEN 6 
        ELSE 7 
        END) ASC"));

    if ( isset($params['product_id']) && $params['product_id'] ) {
        $select->where('ts.product_id IN (?)', $params['product_id']);
    }
                    // ASM Permission
    if (isset($params['asm']) && $params['asm']) {
        $QAsm = new Application_Model_Asm();
        $list_regions = $QAsm->get_cache($params['asm']);
        $list_regions = isset($list_regions['area']) && is_array($list_regions['area']) ? $list_regions['area'] : array();
        if (count($list_regions) > 0)
            $select->where('a.id IN (?)', $list_regions);
        else
            $select->where('1=0', 1);
    }
                    // echo $select; die;
    $result = $db->fetchAll($select);
    return $result;
}
                // Get PC & Shop Level List
function getPcShopLevel($params){
    set_time_limit(0);
    ini_set('memory_limit', '-1');
    error_reporting(~E_ALL);
    ini_set("display_error", '0');
    $db = Zend_Registry::get('db');
    $get = array(
        'staff_id'      => 's.id',
        'staff_code'    => 's.code',
        'staff_name'    => new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)"),
        'pc_stand_by'   => 's.pc_stand_by',
        'staff_joined'  => 's.joined_at',
        'staff_offdate' => 's.off_date',
        'work_month'    => new Zend_Db_Expr(
            "
            CASE WHEN s.off_date IS NULL THEN TIMESTAMPDIFF(MONTH, DATE(s.joined_at), '".$params['to']."') 
            ELSE TIMESTAMPDIFF(MONTH, DATE(s.joined_at), s.off_date) END
            "),
        'staff_level'   => 'pl.level_name',
        'grand_area'    => new Zend_Db_Expr(
            "   (CASE 
            WHEN a.name LIKE 'BKK-E1%' THEN 'BKK East-1'
            WHEN a.name LIKE 'BKK-E2%' THEN 'BKK East-2'
            WHEN a.name LIKE 'BKK-E3%' THEN 'BKK East-3'
            WHEN a.name LIKE 'BKK-E4%' THEN 'BKK East-4'
            WHEN a.name LIKE 'BKK-E5%' THEN 'BKK East-5'
            WHEN a.name LIKE 'BKK-W1%' THEN 'BKK West-1'
            WHEN a.name LIKE 'BKK-W2%' THEN 'BKK West-2'
            WHEN a.name LIKE 'BKK-W3%' THEN 'BKK West-3'
            ELSE
            a.name  
            END)
            "),
        'area_name'     => 'a.name',
        'st_id'         => 'st.id',
        'st_name'       => 'st.name',
        'st_type'       => 'o.org_name',
        'st_status'     => new Zend_Db_Expr("(CASE WHEN st.del = 1 THEN 'Disabled' ELSE 'Active' END)"),
        'st_level'      => new Zend_Db_Expr("(CASE WHEN st.store_grade = 'S' THEN '*S' ELSE st.store_grade END)"),
                        // 'shop_check'    => new Zend_Db_Expr("COUNT( DISTINCT cl.id)"),
        'shop_check'    => 'pa.grade',
        'etest'         => 'pa.etest_result',
    );
    $sql = '';
    if ( isset($params['flag']) && $params['flag'] == 1 ) {
        $sql = ' AND s.pc_stand_by = 0';
    }
    $select = $db->select()
    ->from(array('st' => 'store'), $get)
    ->join(array('o'  => 'org')             , 'st.org_dealer = o.org_id'                , array())
    ->join(array('rm' => 'regional_market') , 'st.regional_market = rm.id'              , array())
    ->join(array('a'  => 'area')            , 'rm.area_id = a.id'                       , array())
    ->joinLeft(array('ss' => 'store_staff') , 
        "   st.id = ss.store_id 
        AND ss.is_leader = 0
        ", array())
    ->joinLeft(array('s'  => 'staff'), 
        "   ss.staff_id = s.id 
        AND s.group_id = ".PGPB_ID." 
        AND s.off_date IS NULL ".$sql." 
        ", array())
    ->joinLeft(array('pl' => 'pc_level'), 's.staff_level = pl.id', array())

                        // ->joinLeft(array('cl' => 'check_list_data_log'), 
                        //     "   s.id = cl.pc_id 
                        //         AND cl.status = 'Y' 
                        //     ", array())
    ->joinLeft(array('pa' => 'pc_active'), 
        "   s.id = pa.staff_id 
        AND pa.created_at >= '".$params['from']."' 
        AND pa.created_at <= '".$params['to']."' 
        ", array())
                        // ->where('s.group_id = ?', PGPB_ID)
                        // ->where('s.off_date IS NULL', 1)
    ->where('st.store_grade IS NOT NULL', 1)
    ->group(array('s.id','st.id'))
    ->order(new Zend_Db_Expr(
        "(
        CASE 
        WHEN st.store_grade = 'S' THEN 1 
        WHEN st.store_grade = 'A' THEN 2
        WHEN st.store_grade = 'B' THEN 3
        WHEN st.store_grade = 'C' THEN 4
        ELSE 5 
        END                    
    ) ASC, st.id ASC, s.code ASC"));
    if ( isset($params['area_id']) && $params['area_id'] ) {
        $select->where('a.id IN (?)', $params['area_id']);
    }
    if ( isset($params['store_type']) && $params['store_type'] ) {
        $select->where('o.org_id IN (?)', $params['store_type']);
    }
    if ( isset($params['flag']) && $params['flag'] == 1 ) {
                        // $select->where('s.pc_stand_by = ?', 0);
        $select->where('st.store_grade IN (?)', array('S','A'));
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
                    // echo $select; die;
    $result = $db->fetchAll($select);
    return $result;
}
                // Get Sellout for PC & Shop Level By PC
function getSelloutForPcShopLevel($params) {
    $db = Zend_Registry::get('db');
    $get = array(
        'staff_id'      => 't.staff_id',
        'total_sellout' => new Zend_Db_Expr("COUNT(ts.imei)"),
        'sellout_a'     => new Zend_Db_Expr("COUNT(CASE WHEN g.series = 'A' THEN ts.imei END)"), 
        'sellout_r'     => new Zend_Db_Expr("COUNT(CASE WHEN g.series = 'R' THEN ts.imei END)"), 
        'sellout_f'     => new Zend_Db_Expr("COUNT(CASE WHEN g.series = 'F' THEN ts.imei END)"),
        'total_price'   => new Zend_Db_Expr("SUM(gkl.price)"),
        'cnt_store'     => new Zend_Db_Expr("COUNT(DISTINCT t.store)"),
    );
    $select = $db->select()
    ->from(array('t'  => 'timing'), $get)
    ->join(array('ts' => 'timing_sale')         , 't.id = ts.timing_id' , array())
    ->join(array('gkl' => 'good_kpi_log')       , 
        "   gkl.good_id = ts.product_id 
        AND gkl.color_id = ts.model_id 
        AND t.created_at >= CONCAT(gkl.from_date,' 00:00:00') 
        AND t.created_at <= CONCAT(gkl.to_date,' 23:59:59')
        ", array())
    ->join(array('i'  => WAREHOUSE_DB.'.imei')  , 
        "   ts.imei = i.imei_sn
        AND DATE(i.activated_date) <= DATE_FORMAT(DATE(t.created_at) + INTERVAL 7 DAY, '%Y-%m-%d') 
        AND DATE(i.activated_date) >= DATE_FORMAT(DATE(t.created_at), '%Y-%m-%d')
        " , array())
    ->join(array('g'  => WAREHOUSE_DB.'.good')  , 'i.good_id = g.id'    , array())
    ->where('t.staff_id = ?', $params['staff_id'])
    ->where('t.created_at >= ?', $params['from']." 00:00:00")
    ->where('t.created_at <= ?', $params['to']." 23:59:59")
    ->group(array('t.staff_id'));
                    // echo $select; die;
    $result = $db->fetchRow($select);
    return $result;
}
                // Get Shop Level By Area List
function getShopLevelByArea($params) {
    $db = Zend_Registry::get('db');
    $get = array(
        'area_name' => 'a.name',
        'cnt_store' => new Zend_Db_Expr("COUNT(st.id)"),
        'cnt_s'     => new Zend_Db_Expr("COUNT(CASE WHEN st.store_grade = 'S' THEN st.id END)"),
        'cnt_a'     => new Zend_Db_Expr("COUNT(CASE WHEN st.store_grade = 'A' THEN st.id END)"),
        'cnt_b'     => new Zend_Db_Expr("COUNT(CASE WHEN st.store_grade = 'B' THEN st.id END)"),
        'cnt_c'     => new Zend_Db_Expr("COUNT(CASE WHEN st.store_grade = 'C' THEN st.id END)"),
    );
    $select = $db->select()
    ->from(array('st' => 'store'), $get)
    ->join(array('o'  => 'org')             , 'st.org_dealer = o.org_id'    , array())
    ->join(array('rm' => 'regional_market') , 'st.regional_market = rm.id'  , array())
    ->join(array('a'  => 'area')            , 'rm.area_id = a.id'           , array())
    ->where('st.del IS NULL', 1)
    ->where('st.store_grade IS NOT NULL', 1)
    ->group('a.id')
    ->order('a.name ASC');
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
                    // echo $select; die;
    $result = $db->fetchAll($select);
    return $result;
}
                // Get Sellout By Store, Model, Color, Day 
function getKaSelloutDetails($params) { 
    set_time_limit(0);
    ini_set('memory_limit', '-1');
    error_reporting(~E_ALL);
    ini_set("display_error", '0');
    if (is_array($params['selected_date'])) {
        $selected_date_01 = $params['selected_date'][0];
        $selected_date_02 = $params['selected_date'][1];
        $d1 = explode('-', $selected_date_01);
        $d2 = explode('-', $selected_date_02);
        $selected_date_last_01 = date('Y-m-'.$d1[2], strtotime("-15 Days", strtotime($selected_date_01)));
        $selected_date_last_02 = date('Y-m-'.$d2[2], strtotime("-15 Days", strtotime($selected_date_02)));
    } else {
        $d = explode('-', $params['selected_date']);
        $selected_date = $d[0].'-'.$d[1].'-'.$d[2];
        $selected_date_last = date('Y-m-'.$d[2], strtotime("-15 Days", strtotime($selected_date)));
        $selected_date_01 = $selected_date_02 = $selected_date;
        $selected_date_last_01 = $selected_date_last_02 = $selected_date_last;
    }
    $db = Zend_Registry::get('db');
    $get = array(
        'area_name'     => 'a.name',
        'st_id'         => 'st.id',
        'st_name'       => 'st.name',
        'st_status'     => new Zend_Db_Expr("(CASE WHEN st.del = 1 THEN 'Closed' ELSE 'Active' END)"),
        'st_rank'       => 'st.rank',
        'st_type'       => 'o.org_name',
        'st_shopid'     => 'st.store_id',
        'st_shopcode'   => 'st.store_code',
        'good_name'     => 'g.name',
        'good_desc'     => 'g.desc',
        'good_color'    => 'gc.name',
        'timing_day'    => new Zend_Db_Expr("DAY(t.created_at)"),
        'sum_price_now' => new Zend_Db_Expr("SUM(CASE WHEN t.created_at >= '".$selected_date_01." 00:00:00' AND t.created_at <= '".$selected_date_02." 23:59:59' THEN gkl.price END)"),
        'sum_price_last'=> new Zend_Db_Expr("SUM(CASE WHEN t.created_at >= '".$selected_date_last_01." 00:00:00' AND t.created_at <= '".$selected_date_last_02." 23:59:59' THEN gkl.price END)"),
        'sellout_now'   => new Zend_Db_Expr("COUNT(CASE WHEN t.created_at >= '".$selected_date_01." 00:00:00' AND t.created_at <= '".$selected_date_02." 23:59:59' THEN ts.imei END)"),
        'sellout_last'  => new Zend_Db_Expr("COUNT(CASE WHEN t.created_at >= '".$selected_date_last_01." 00:00:00' AND t.created_at <= '".$selected_date_last_02." 23:59:59' THEN ts.imei END)"),
    );
    $select = $db->select()
    ->from(array('st' => 'store'), $get)
    ->join(array('o'  => 'org')             , 'st.org_dealer = o.org_id'    , array())
    ->join(array('rm' => 'regional_market') , 'st.regional_market = rm.id'  , array())
    ->join(array('a'  => 'area')            , 'rm.area_id = a.id'           , array())
    ->join(array('t'  => 'timing')          , 'st.id = t.store'             , array())
    ->join(array('ts' => 'timing_sale')     , 't.id = ts.timing_id'         , array())

    ->join(array('i'  => WAREHOUSE_DB.'.imei')      , 'ts.imei = i.imei_sn' , array())
    ->join(array('g'  => WAREHOUSE_DB.'.good')      , 'i.good_id = g.id'    , array())
    ->join(array('gc' => WAREHOUSE_DB.'.good_color'), 'i.good_color = gc.id', array())
    ->join(array('dma'=> 'distributor_map_am')      , 
        "   ( 
        CASE 
        WHEN dma.org_id = 2 THEN o.org_id IN (2,30) 
        ELSE o.org_id = dma.org_id 
        END 
        )
        AND i.distributor_id = dma.d_id
        ", array())
    ->joinLeft(array('gkl' => 'good_kpi_log'), 
        "   ts.product_id = gkl.good_id 
        AND ts.model_id = gkl.color_id 
        AND CONCAT(gkl.from_date, ' 00:00:00') <= t.created_at 
        AND CONCAT(gkl.to_date, ' 23:59:59') >= t.created_at 
        ",array())
    ->where('(( t.created_at >= ?', $selected_date_01." 00:00:00")
    ->where('t.created_at <= ?)', $selected_date_02." 23:59:59")
    ->orWhere('(t.created_at >= ?', $selected_date_last_01." 00:00:00")
    ->where('t.created_at <= ?))', $selected_date_last_02." 23:59:59")
    ->where('o.org_id IN (?)', $params['channel_id'])
    ->where('st.rank <> ?', 5)
    ->group(array('st.id','g.id','gc.id','timing_day'))
    ->order(array('a.name ASC','st.id ASC','g.name ASC','gc.name ASC'));
                    // echo $select; die;
    $result = $db->fetchAll($select);
    return $result;
}
function getOperatorStock($params){
    $operator_list = array(0);
    $db = Zend_Registry::get('db');
    $get = array(
        'd_id'      => 'd.id',
        'd_name'    => 'd.title',
        'model_name'=> 'g.name',
        'model_desc'=> 'g.desc',
        'sellin'    => new Zend_Db_Expr("COUNT(i.imei_sn)"),
        'sellout'   => new Zend_Db_Expr("COUNT(ts.imei)"),
        'stock'     => new Zend_Db_Expr("COUNT(CASE WHEN ts.id IS NULL THEN i.imei_sn END)"),
    );
    $select = $db->select()
    ->from(array('d'  => WAREHOUSE_DB.'.distributor'), $get)
    ->join(array('i'  => WAREHOUSE_DB.'.imei')      , 'd.id = i.distributor_id' , array())
    ->join(array('g'  => WAREHOUSE_DB.'.good')      , 'i.good_id = g.id'        , array())
    ->join(array('gc' => WAREHOUSE_DB.'.good_color'), 'i.good_color = gc.id'    , array())
    ->joinLeft(array('ts' => 'timing_sale')         , 
        "   i.imei_sn = ts.imei 
        AND DATE(i.activated_date) <= DATE_FORMAT(DATE(ts.time_add) + INTERVAL 7 DAY, '%Y-%m-%d') 
        AND DATE(i.activated_date) >= DATE_FORMAT(DATE(ts.time_add) - INTERVAL 3 Day, '%Y-%m-%d')      
        "     , array())
    ->where('d.del <> ?', 1)
                        // ->where('d.id IN (?)', $operator_list)
    ->group(array('d.id','g.id'));
                    // check Permission AM
    if ( isset($params['am']) && $params['am'] ) {
        $QAm = new Application_Model_Am();
        $list_org = $QAm->get_cache($params['am']);
        $list_org = isset($list_org['store_type']) && is_array($list_org['store_type']) ? $list_org['store_type'] : array();
        $chk_awn = array_intersect($list_org, array(12,19,41));
        $chk_true = array_intersect($list_org, array(3,26,42));
        $chk_dtac = array_intersect($list_org, array(5,20,43));
                        // AWN 
        if ( count($chk_awn) > 0 ) { 
            array_push($operator_list, 3007);
            array_push($operator_list, 11293);
        }
                        // True 
        if ( count($chk_true) > 0 ) { 
            array_push($operator_list, 3028);
        }
                        // Dtac
        if ( count($chk_dtac) > 0 ) { 
            array_push($operator_list, 3005);
            array_push($operator_list, 44927);
        }
    } else {
        $operator_list = array(3005, 3028, 3007, 11293, 44927);
    }
    $select->where('d.id IN (?)', $operator_list);
                    // echo $select; die;
    $result = $db->fetchAll($select);
    return $result;
}
                // get KR Sellout
function getKrSellout($params) {
    $d1 = explode('/', $params['from']);
    $d2 = explode('/', $params['to']);
    $from = $d1[2].'-'.$d1[1].'-'.$d1[0];
    $to = $d2[2].'-'.$d2[1].'-'.$d2[0];
    $from_last = date('Y-m-'.$d1[0], strtotime("-1 Month", strtotime($from)));
    $to_last = date('Y-m-'.$d2[0], strtotime("-1 Month", strtotime($to)));
    $db = Zend_Registry::get('db');
    $sub_select_01 = $db->select()
    ->from(array('i'  => WAREHOUSE_DB.'.imei'), array('sellout_01' => new Zend_Db_Expr("COUNT(ts.imei)") ) )
    ->join(array('ts' => 'timing_sale') , 'i.imei_sn = ts.imei' , array())
    ->join(array('t'  => 'timing')      , 'ts.timing_id = t.id' , array())
    ->join(array('st' => 'store')       , 't.store = st.id'     , array())
    ->where('t.created_at >= ?', $from." 00:00:00")
    ->where('t.created_at <= ?', $to." 23:59:59")
    ->where('st.org_dealer IN (?)', array(1,27,32,33,34,37,38))
    ->where('i.distributor_id = d.id')
    ->group('i.distributor_id');
    $sub_select_02 = $db->select()
    ->from(array('i2'  => WAREHOUSE_DB.'.imei'), array('sellout_02' => new Zend_Db_Expr("COUNT(ts2.imei)") ) )
    ->join(array('ts2' => 'timing_sale') , 'i2.imei_sn = ts2.imei' , array())
    ->join(array('t2'  => 'timing')      , 'ts2.timing_id = t2.id' , array())
    ->join(array('st2' => 'store')       , 't2.store = st2.id'     , array())
    ->where('t2.created_at >= ?', $from_last." 00:00:00")
    ->where('t2.created_at <= ?', $to_last." 23:59:59")
    ->where('st2.org_dealer IN (?)', array(1,27,32,33,34,37,38))
    ->where('i2.distributor_id = d.id')
    ->group('i2.distributor_id');
                    // Filter Model 
    if (isset($params['good_id']) && $params['good_id']) {
        if (is_array($params['good_id']) && count($params['good_id'])) {
            $sub_select_01->where('i.good_id IN (?)', $params['good_id']);
            $sub_select_02->where('i2.good_id IN (?)', $params['good_id']);
        } else if (is_numeric($params['good_id'])) {
            $sub_select_01->where('i.good_id = ?', intval($params['good_id']));
            $sub_select_02->where('i2.good_id = ?', intval($params['good_id']));
        } else {
            $sub_select_01->where('1=0', 1);
            $sub_select_02->where('1=0', 1);
        }
    }
                    // Filter Color 
    if (isset($params['color_id']) && $params['color_id']) {
        if (is_array($params['color_id']) && count($params['color_id'])) {
            $sub_select_01->where('i.good_color IN (?)', $params['color_id']);
            $sub_select_02->where('i2.good_color IN (?)', $params['color_id']);
        } else if (is_numeric($params['color_id'])) {
            $sub_select_01->where('i.good_color = ?', intval($params['color_id']));
            $sub_select_02->where('i2.good_color = ?', intval($params['color_id']));
        } else {
            $sub_select_01->where('1=0', 1);
            $sub_select_02->where('1=0', 1);
        }      
    }
    $get = array(
        'd_id'          => 'd.id',
        'd_name'        => 'd.title',
        'sellout_last'  => new Zend_Db_Expr("COALESCE((".$sub_select_02."),0)"),
        'sellout'       => new Zend_Db_Expr("COALESCE((".$sub_select_01."),0)"),
    );
    $select = $db->select()
    ->from(array('d' => WAREHOUSE_DB.'.distributor'), $get)
    ->where('d.is_kr = ?', 1)
    ->group('d.id')
    ->order('d.id ASC');
    if ( isset($params['d_id']) && $params['d_id']) {
        $select->where('d.id = ?', $params['d_id']);
    }
    if ( isset($params['d_name']) && $params['d_name']) {
        $select->where('d.title LIKE ?', "%".$params['d_name']."%");
    }
                    // check Permission AM
    if ( isset($params['am']) && $params['am'] ) {
        $QAm = new Application_Model_Am();
        $list_org = $QAm->get_cache($params['am']);
        $list_org = isset($list_org['store_type']) && is_array($list_org['store_type']) ? $list_org['store_type'] : array();
        $chk_arr = array_intersect($list_org, array(27,32,33,34,37,38));
        if ( count($chk_arr) > 0 )
            $select->where('1=1', 1);
        else
            $select->where('1=0', 1);
    }
                    // echo $select; die;
    $result = $db->fetchAll($select);
    return $result;
}
                // get KR Sellout : Export Product Report 
function getKrProductReport($params) {
    $d1 = explode('/', $params['from']);
    $d2 = explode('/', $params['to']);
    $from = $d1[2].'-'.$d1[1].'-'.$d1[0];
    $to = $d2[2].'-'.$d2[1].'-'.$d2[0];
    $db = Zend_Registry::get('db');
    $get = array(
        'good_id'       => 'g.id',
        'good_name'     => 'g.name',
        'good_desc'     => 'g.desc',
        'sellout'       => new Zend_Db_Expr("COUNT(ts.imei)"),
        'sellout_today' => new Zend_Db_Expr(
            "COUNT(CASE WHEN ts.time_add >= '".$to." 00:00:00' AND ts.time_add <= '".$to." 23:59:59' THEN ts.imei END)"),
    );
    $select = $db->select()
    ->from(array('d' => WAREHOUSE_DB.'.distributor'), $get)
    ->join(array('i' => WAREHOUSE_DB.'.imei'), 'd.id = i.distributor_id', array())
    ->join(array('g' => WAREHOUSE_DB.'.good'), 'i.good_id = g.id'       , array())
    ->join(array('ts'=> 'timing_sale'), 
        "   i.imei_sn = ts.imei
        AND ts.time_add >= '".$from." 00:00:00' 
        AND ts.time_add <= '".$to." 23:59:59' 
        ", array())
    ->where('d.is_kr = ?', 1)
    ->group('g.id')
    ->order('g.name ASC');
    if ( isset($params['d_id']) && $params['d_id']) {
        $select->where('d.id = ?', $params['d_id']);
    }
    if ( isset($params['d_name']) && $params['d_name']) {
        $select->where('d.title LIKE ?', "%".$params['d_name']."%");
    }
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
    if ( isset($params['am']) && $params['am'] ) {
        $QAm = new Application_Model_Am();
        $list_org = $QAm->get_cache($params['am']);
        $list_org = isset($list_org['store_type']) && is_array($list_org['store_type']) ? $list_org['store_type'] : array();
        $chk_arr = array_intersect($list_org, array(27,32,33,34,37,38));
        if ( count($chk_arr) > 0 )
            $select->where('1=1', 1);
        else
            $select->where('1=0', 1);
    }
                    // echo $select; die;
    $result = $db->fetchAll($select);
    return $result;
}
                // Weekly Report : Get Sellout By Area for Achieve Report
function getSelloutForAchieveReport($params) {
    $d1 = explode('/', $params['from']);
    $d2 = explode('/', $params['to']);
    $from = $d1[2].'-'.$d1[1].'-'.$d1[0];
    $to = $d2[2].'-'.$d2[1].'-'.$d2[0];
    $db = Zend_Registry::get('db');
    $get = array(
        'area_id'   => 'a.id',
        'area_name' => 'a.name',
        'area_gfk'  => 'ag.gfk',
        'sellout'   => new Zend_Db_Expr("COUNT(ts.imei)"),
    );
    $select = $db->select()
    ->from(array('st' => 'store'), $get)
    ->join(array('rm' => 'regional_market') , 'st.regional_market = rm.id'  , array())
    ->join(array('a'  => 'area')            , 'rm.area_id = a.id'           , array())
    ->joinLeft(array('ag'=> 'area_gfk')     , 'a.id = ag.area_id'           , array())
    ->joinLeft(array('t' => 'timing')       , 'st.id = t.store'             , array())
    ->joinLeft(array('ts'=> 'timing_sale')  , 't.id = ts.timing_id'         , array())
    ->where('t.created_at >= ?', $from." 00:00:00")
    ->where('t.created_at <= ?', $to." 23:59:59")
    ->where('a.id NOT IN (?)', array(48,49,72))
    ->group('a.id')
    ->order('a.name ASC');
                    // echo $select; die;
    $result = $db->fetchAll($select);
    return $result;
}
function getCurrentStockByDID($d_id) {
    $db = Zend_Registry::get('db');
    $get = array(
        'd_id'   => 'd.id',
        'd_name' => 'd.title',
                        // 'stock'  => new Zend_Db_Expr("(COUNT(i.imei_sn) - COUNT(ts.imei))"),
        'stock'      => new Zend_Db_Expr("(COUNT(i.imei_sn) - COUNT(CASE WHEN i.activated_date IS NOT NULL THEN i.imei_sn END))"),
        'stock_scan' => new Zend_Db_Expr("(COUNT(CASE WHEN i.stock_shop_id IS NOT NULL THEN i.imei_sn END) - COUNT(CASE WHEN i.activated_date IS NOT NULL THEN i.imei_sn END))"),
    );
    $select = $db->select()
    ->from(array('d' => WAREHOUSE_DB.'.distributor'), $get)
    ->join(array('i' => WAREHOUSE_DB.'.imei')   , 'd.id = i.distributor_id' , array())
                        // ->joinLeft(array('ts' => 'timing_sale')     , 'i.imei_sn = ts.imei'     , array())
    ->where('d.id = ?', $d_id)
    ->group('d.id');
                    // echo $select; die;
    $result = $db->fetchRow($select);
    return $result;
}
                // Factory Report By Week 
function getSelloutByChannel($params) {
                    //echo "<pre>"; print_r($params); echo "<br/>";
    $d1 = explode('/', $params['from']);
    $from = $d1[2].'-'.$d1[1].'-'.$d1[0];
    $db = Zend_Registry::get('db');
    $get_01 = array(
                            // 'product_id'   => 'g.id',
                            // 'product_code' => 'g.name',
        'product_name' => new Zend_Db_Expr("('".$params['product_text']."')"),
        'channel_name' => new Zend_Db_Expr(
            "   (CASE
            WHEN a.id = 48 THEN 'OPPO Thai'
            WHEN st.org_dealer = 1 THEN 'Dealer' 
            WHEN st.org_dealer IN (12,19,5,20,3,26,10,41,42,43) THEN 'Operator' 
            WHEN st.org_dealer IN (27,32,38) THEN 'KR' 
            WHEN st.org_dealer IN (16,18,33,34,37,23,22,21,31,40,39,47) THEN 'Brandshop' ELSE 'KA' 
            END)
            "),
        'sellout'   => new Zend_Db_Expr("COUNT(ts.imei)"),
    );
    $period_loop = $params['week_no'];
    $get_02 = array();
    for ($i=0;$i<$period_loop;$i++) {
        $week_txt = $i + 1;
        $days_start = $i * 7;
        $days_end = $days_start + 6;
        $start = date('Y-m-d', strtotime("+".$days_start." Days" ,strtotime($from)));
        $end = date('Y-m-d', strtotime("+".$days_end." Days" ,strtotime($from)));
        $get_02["W".$week_txt] = new Zend_Db_Expr("COUNT(CASE WHEN t.created_at >= '".$start." 00:00:00' AND t.created_at <= '".$end." 23:59:59' THEN ts.imei END)");
    }
                        // echo "<pre>"; print_r($get_02); die;
    $get = $get_01 + $get_02;
    $select = $db->select()
    ->from(array('a'  => 'area'), $get)
    ->join(array('rm' => 'regional_market'), 'a.id = rm.area_id'    , array())
    ->join(array('st' => 'store')   , 'rm.id = st.regional_market'  , array())
    ->join(array('t'  => 'timing'), 
        "   st.id = t.store
        AND t.created_at >= '".$from." 00:00:00' 
        AND t.created_at <= '".$end." 23:59:59' 
        ", array())
    ->join(array('ts' => 'timing_sale')         , 't.id = ts.timing_id' , array())
    ->join(array('g'  => WAREHOUSE_DB.'.good')  , 'ts.product_id = g.id', array())
    ->where('a.id NOT IN (?)', array(49,72))
    ->group(array('channel_name'))
    ->order(new Zend_Db_Expr(
        "(CASE 
        WHEN channel_name = 'OPPO Thai' THEN 1 
        WHEN channel_name = 'Dealer'    THEN 2 
        WHEN channel_name = 'Brandshop' THEN 3 
        WHEN channel_name = 'KA'        THEN 4 
        WHEN channel_name = 'KR'        THEN 5 
        WHEN channel_name = 'Operator'  THEN 6 
        ELSE 7 
        END) ASC"));

    if ( isset($params['product_id']) && $params['product_id'] ) {
        $select->where('ts.product_id IN (?)', $params['product_id']);
    }
                            // ASM Permission
    if (isset($params['asm']) && $params['asm']) {
        $QAsm = new Application_Model_Asm();
        $list_regions = $QAsm->get_cache($params['asm']);
        $list_regions = isset($list_regions['area']) && is_array($list_regions['area']) ? $list_regions['area'] : array();
        if (count($list_regions) > 0)
            $select->where('a.id IN (?)', $list_regions);
        else
            $select->where('1=0', 1);
    }
                            // echo $select; die;
    $result = $db->fetchAll($select);
    return $result;
}
                        // Factory Report By Week 
function getSelloutByStoreType($params) {
                            //echo "<pre>"; print_r($params); echo "<br/>";
    $d1 = explode('/', $params['from']);
    $from = $d1[2].'-'.$d1[1].'-'.$d1[0];
    $db = Zend_Registry::get('db');
    $get_01 = array(
        'product_id'   => 'g.id',
        'product_code' => 'g.name',
        'product_name' => 'g.desc',
        'channel_name' => new Zend_Db_Expr(
            "   (CASE
            WHEN a.id = 48 THEN 'OPPO Thai'
            WHEN st.org_dealer = 1 THEN 'Dealer' 
            WHEN st.org_dealer IN (12,19,5,20,3,26,10,41,42,43) THEN 'Operator' 
            WHEN st.org_dealer IN (27,32,38) THEN 'KR' 
            WHEN st.org_dealer IN (16,18,33,34,37,23,22,21,31,40,39,47) THEN 'Brandshop' ELSE 'KA' 
            END)
            "),
        'store_type' => 'o.org_name',
        'sellout'    => new Zend_Db_Expr("COUNT(ts.imei)"),
    );
    $period_loop = $params['week_no'];
    $get_02 = array();
    for ($i=0;$i<$period_loop;$i++) {
        $week_txt = $i + 1;
        $days_start = $i * 7;
        $days_end = $days_start + 6;
        $start = date('Y-m-d', strtotime("+".$days_start." Days" ,strtotime($from)));
        $end = date('Y-m-d', strtotime("+".$days_end." Days" ,strtotime($from)));
        $get_02["W".$week_txt] = new Zend_Db_Expr("COUNT(CASE WHEN t.created_at >= '".$start." 00:00:00' AND t.created_at <= '".$end." 23:59:59' THEN ts.imei END)");
    }
                                // echo "<pre>"; print_r($get_02); die;
    $get = $get_01 + $get_02;
    $select = $db->select()
    ->from(array('a'  => 'area'), $get)
    ->join(array('rm' => 'regional_market'), 'a.id = rm.area_id'    , array())
    ->join(array('st' => 'store')   , 'rm.id = st.regional_market'  , array())
    ->join(array('o'  => 'org')     , 'st.org_dealer = o.org_id'    , array())
    ->join(array('t'  => 'timing'), 
        "   st.id = t.store
        AND t.created_at >= '".$from." 00:00:00' 
        AND t.created_at <= '".$end." 23:59:59' 
        ", array())
    ->join(array('ts' => 'timing_sale')         , 't.id = ts.timing_id' , array())
    ->join(array('g'  => WAREHOUSE_DB.'.good')  , 'ts.product_id = g.id', array())
    ->where('a.id NOT IN (?)', array(49,72))
    ->group(array('o.org_id', 'g.id'))
    ->order(new Zend_Db_Expr(
        "(CASE 
        WHEN channel_name = 'OPPO Thai' THEN 1 
        WHEN channel_name = 'Dealer'    THEN 2 
        WHEN channel_name = 'Brandshop' THEN 3 
        WHEN channel_name = 'KA'        THEN 4 
        WHEN channel_name = 'KR'        THEN 5 
        WHEN channel_name = 'Operator'  THEN 6 
        ELSE 7 
        END) ASC, o.org_name ASC"));

    if ( isset($params['product_id']) && $params['product_id'] ) {
        $select->where('ts.product_id IN (?)', $params['product_id']);
    }
                                    // ASM Permission
    if (isset($params['asm']) && $params['asm']) {
        $QAsm = new Application_Model_Asm();
        $list_regions = $QAsm->get_cache($params['asm']);
        $list_regions = isset($list_regions['area']) && is_array($list_regions['area']) ? $list_regions['area'] : array();
        if (count($list_regions) > 0)
            $select->where('a.id IN (?)', $list_regions);
        else
            $select->where('1=0', 1);
    }
                                    // echo $select; die;
    $result = $db->fetchAll($select);
    return $result;
}
                                // Report By Store : Export BS Store List [Stock Scan]
function getBsStoreStockScan($params) {
    set_time_limit(0);
    ini_set('memory_limit', '-1');
    error_reporting(~E_ALL);
    ini_set("display_error", '0');
    $d1 = explode('/', $params['from']);
    $from = $d1[2].'-'.$d1[1].'-'.$d1[0];
    $d2 = explode('/', $params['to']);
    $to = $d2[2].'-'.$d2[1].'-'.$d2[0];
    $db = Zend_Registry::get('db');
    $get = array(
        'area_id'   => 'a.id',
        'area'      => 'a.name',
        'st_id'     => 'st.id',
        'st_name'   => 'st.name',
        'st_status' => new Zend_Db_Expr("(CASE WHEN st.del IS NULL THEN 'Active' ELSE 'Closed' END)"),
        'st_type'   => 'o.org_name',
        'd_id'      => 'd.id',
        'd_name'    => 'd.title',
        'room_type'     => 'st.room_type',
        'shop_acreage'  => 'st.shop_acreage',
        'shop_version'  => 'st.shop_version',
    );
    $select = $db->select()
    ->from(array('st' => 'store'), $get)
    ->join(array('o'  => 'org')             , 'st.org_dealer = o.org_id'    , array())
    ->join(array('rm' => 'regional_market') , 'st.regional_market = rm.id'  , array())
    ->join(array('a'  => 'area')            , 'rm.area_id = a.id'           , array())
    ->joinLeft(array('d' => WAREHOUSE_DB.'.distributor'), 'st.d_id = d.id'  , array())
    ->where('(st.org_dealer = ?', 18)
    ->orWhere('o.store_type_id = ?)', 3)
    ->group('st.id')
    ->order(array('a.name ASC','st.id ASC'));
                                    // Filter 
    if ( isset($params['id']) && $params['id'] ) {
        $select->where('st.id LIKE ?', '%'.$params['id'].'%');
    }
    if ( isset($params['name']) && $params['name'] ) {
        $select->where('st.name LIKE ?', '%'.$params['name'].'%');
    }
    if ( isset($params['shop_id']) && $params['shop_id'] ) {
        $select->where('st.store_id LIKE ?', '%'.$params['shop_id'].'%');
    }
    if ( isset($params['shop_code']) && $params['shop_code'] ) {
        $select->where('st.store_code LIKE ?', '%'.$params['shop_code'].'%');
    }
    if ( isset($params['it_junction']) && $params['it_junction'] ) {
        $select->where('st.it_junction = ?', $params['it_junction']);
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
                                    // Add Filter Store
    if (isset($params['store']) && $params['store']) {
        if (is_array($params['store']) && count($params['store']))
            $select->where('st.id IN (?)', $params['store']);
        elseif (is_numeric($params['store']))
            $select->where('st.id = ?', intval($params['store']));
        else
            $select->where('1=0', 1);
    }
                                    // Add Filter Store Type
    if (isset($params['org']) && $params['org']) {
        if (is_array($params['org']) && count($params['org']))
            $select->where('st.org_dealer IN (?)', $params['org']);
        elseif (is_numeric($params['org']))
            $select->where('st.org_dealer = ?', intval($params['org']));
        else
            $select->where('1=0', 1);
    }
                                    // Add Filter Market Type, Market Name
    if ( (isset($params['market_type']) and $params['market_type']) || (isset($params['market_name']) && $params['market_name']) ) {
        $select->join( array('sm' => 'store_market'), 'st.id = sm.store_id'         , array());
        $select->join( array('mn' => 'market_name') , 'sm.market_name_id = mn.id'   , array('market_name' => 'mn.name'));
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
    if (isset($params['bm_id']) && intval($params['bm_id']) > 0) {
        $select->joinRight(array('ssl' => 'store_staff'), 'ssl.store_id=st.id', array());
        $log_where = $this->getAdapter()->quoteInto('ssl.staff_id = ?', $params['bm_id']).
        " AND " . $this->getAdapter()->quoteInto('ssl.is_leader = ?', 3);
        $select->where($log_where);
    }
                                    // echo $select; die;
    $result = $db->fetchAll($select);
    return $result;
}
                                // Report By Store : Get Stock Scan
function getBsStockScanByStoreID($params){
    $db = Zend_Registry::get('db');
                                    // Get Sellout 
    $get_01 = array('sellout' => new Zend_Db_Expr("COUNT(ts.imei)"));
    $select_01 = $db->select()
    ->from(array('t' => 'timing'), $get_01)
    ->join(array('ts'=> 'timing_sale'), 't.id = ts.timing_id', array())
    ->where('t.store = ?', $params['store_id'])
    ->group(array('t.store'));
                                    // Get Sellin Scan
    $get_02 = array('sellin_scan' => new Zend_Db_Expr("COUNT(i.imei_sn)"));
    $select_02 = $db->select()
    ->from(array('i' => WAREHOUSE_DB.'.imei'), $get_02)
    ->where('i.stock_shop_id = ?', $params['store_id'])
    ->where('i.stock_shop_id IS NOT NULL', 1)
    ->group(array('i.stock_shop_id'));
    $select_01->where('ts.product_id = ?', $params['good_id']);
    $select_01->where('ts.model_id = ?', $params['color_id']);
    $select_02->where('i.good_id = ?', $params['good_id']);
    $select_02->where('i.good_color = ?', $params['color_id']);
                                    // Execute Query
    $result_01 = $db->fetchRow($select_01);
    $result_02 = $db->fetchRow($select_02);
                                    // Check Data
    $sellout = $sellin_scan = 0;
    if ( !empty($result_01) ) { $sellout = $result_01['sellout']; } 
    if ( !empty($result_02) ) { $sellin_scan = $result_02['sellin_scan']; } 
    $result = array(
        'sellout'       => $sellout,
        'sellin_scan'   => $sellin_scan,
        'stock_scan'    => $sellin_scan - $sellout,
    );
    return $result;
}
                                // Report By Store : Get Sellout for BS DOI By Product, Store
function getSelloutBsDOI($params){
    $now = date('Y-m-d');
    $from = date('Y-m-d', strtotime("-".$params['doi_rate']." Day", strtotime($now)));
    $to = date('Y-m-d', strtotime("-1 Day", strtotime($now)));
    $db = Zend_Registry::get('db');
                                    // Get Sellout 
    $get = array('sellout' => new Zend_Db_Expr("COUNT(ts.imei)"));
    $select = $db->select()
    ->from(array('t' => 'timing'), $get)
    ->join(array('ts'=> 'timing_sale'), 't.id = ts.timing_id', array())
    ->where('t.created_at >= ?', $from." 00:00:00")
    ->where('t.created_at <= ?', $to." 23:59:59")
    ->where('t.store = ?', $params['store_id'])
    ->where('ts.product_id = ?', $params['good_id'])
    ->group(array('t.store'));
                                    // echo $select; die;
    $result = $db->fetchRow($select);
    return $result;
}
                                // get KR BI Report
function getKrBiReport($params) {
                                    // Prepare Date 
    $d1 = explode('/', $params['from']);
    $d2 = explode('/', $params['to']);
    $from = $d1[2].'-'.$d1[1].'-'.$d1[0];
    $to = $d2[2].'-'.$d2[1].'-'.$d2[0];
    $start = new DateTime($from);
    $end   = new DateTime( date("Y-m-30", strtotime($to)) );
    $diff  = $start->diff($end);
    $period_loop = $diff->format('%y') * 12 + $diff->format('%m') + 1;
    $db = Zend_Registry::get('db');
    $get_01 = array(
        'sellout'       => new Zend_Db_Expr("COUNT(ts.imei)"),
        'total_price'   => new Zend_Db_Expr("SUM(gkl.price)"),
    );
    $get_02 = array();
    for ($i=0;$i<$period_loop;$i++) {
        $day_start =  date('Y-m-01', strtotime("+".$i." Month", strtotime($from)));
        $day_end =  date('Y-m-t', strtotime("+".$i." Month", strtotime($from)));
        $moth_text =  date('M-Y', strtotime("+".$i." Month", strtotime($from)));
        $get_02["sellout_".$moth_text] = new Zend_Db_Expr("COUNT(CASE WHEN t.created_at >= '".$day_start." 00:00:00' AND t.created_at <= '".$day_end." 23:59:59' THEN ts.imei END)");
        $get_02["price_".$moth_text] = new Zend_Db_Expr("SUM(CASE WHEN t.created_at >= '".$day_start." 00:00:00' AND t.created_at <= '".$day_end." 23:59:59' THEN gkl.price END)");
    }
    $get = $get_01 + $get_02;
    $select = $db->select()
    ->from(array('d'  => WAREHOUSE_DB.'.distributor'), $get)
    ->join(array('i'  => WAREHOUSE_DB.'.imei')  , 'd.id = i.distributor_id' , array())
    ->join(array('ts' => 'timing_sale')         , 'i.imei_sn = ts.imei'     , array())
    ->join(array('t'  => 'timing')              , 'ts.timing_id = t.id'     , array())
    ->join(array('gkl'=> 'good_kpi_log'), 
        "   gkl.good_id = ts.product_id 
        AND gkl.color_id = ts.model_id 
        AND t.created_at >= CONCAT(gkl.from_date,' 00:00:00') 
        AND t.created_at <= CONCAT(gkl.to_date,' 23:59:59')
        ", array())
    ->where('d.is_kr = ?', 1)
    ->where('t.created_at >= ?', $from." 00:00:00")
    ->where('t.created_at <= ?', $to." 23:59:59");
                                    // Filter Distributor 
    if (isset($params['d_id']) && $params['d_id']) {
        if (is_array($params['d_id']) && count($params['d_id']))
            $select->where('d.id IN (?)', $params['d_id']);
        elseif (is_numeric($params['d_id']))
            $select->where('d.id = ?', intval($params['d_id']));
        else
            $select->where('1=0', 1);
    }
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
    if ( isset($params['am']) && $params['am'] ) {
        $QAm = new Application_Model_Am();
        $list_org = $QAm->get_cache($params['am']);
        $list_org = isset($list_org['store_type']) && is_array($list_org['store_type']) ? $list_org['store_type'] : array();
        $chk_kr = array_intersect($list_org, array(27,32,33,34,37,38));
                                        // AM Online 
        if ( count($chk_kr) > 0 ) { $select->where('1=1', 1); }
        else { $select->where('1=0', 1); } 
    }
                                    // echo $select; die;
    $result = $db->fetchAll($select);
    return $result;
}



//---------------------------new export--------------------------


function exprotimeistore($params){                                  
    set_time_limit(0);
    ini_set('memory_limit', '-1');
    error_reporting(~E_ALL);
    ini_set("display_error", '0');
    $d1 = explode('/', $params['from']);
    $from = $d1[2].'-'.$d1[1].'-'.$d1[0];
    $d2 = explode('/', $params['to']);
    $to = $d2[2].'-'.$d2[1].'-'.$d2[0];
    $db = Zend_Registry::get('db');

    $get = array(
        'st_id'         => 'st.id',
        'st_code'       => 'st.store_code',
        'st_name'       => 'st.name',
        'st_del'        => 'st.del',
        'd_id'          => 'd.id',
        'd_name'        => 'd.title',
        'd_code'        => 'd.distributor_code',
        'area'          => 'a.name',
        'province_name' => 'rm.name',
        'province'      => 'rm3.name',
        'sale_code'     => 'stf.code',
        'reporter_code' => 'st2.code',
        'reporter_name' => 't.staff_id',
        'remark'        => 't.note',
        'opposhop'      => 't.oppo_shop',
        'sale_name'     => new Zend_Db_Expr("CONCAT(stf.firstname_en, ' ' , stf.lastname_en)"),
        'imei_sn'       => 'ts.imei',
        'product_id'    => 'i.good_id',
        'color_id'      => 'i.good_color',
        'timing_date'   => 't.created_at',
        'activated_date'    => 'i.activated_date',
        'brand_name'    => 'b.name'
    );

    $start = $from;

    $select = $db->select()
    ->from(array('st' => 'store'), $get)
    ->joinLeft(array('d'    => WAREHOUSE_DB.'.distributor'), 'st.d_id = d.id'       , array())
    ->joinLeft(array('rm'   => 'regional_market')   , 'st.regional_market = rm.id'  , array())
    ->joinLeft(array('a'    => 'area')              , 'rm.area_id = a.id'           , array())
    ->joinLeft(array('rm2'  => 'regional_market')   , 'st.district = rm2.id'        , array())
    ->joinLeft(array('o'    => 'org')               , 'st.org_dealer = o.org_id'    , array())
    ->joinLeft(array('so'   => 'store_operation')   , 'st.operation_id = so.id'     , array())

    ->joinLeft(array('ssfl' => 'store_staff_log'),'ssfl.store_id = st.id AND ssfl.is_leader = 1 AND ssfl.released_at IS NULL',array())
    ->joinLeft(array('stf' => 'staff'),'stf.id = ssfl.staff_id',array())

    ->joinLeft(array('t'    => 'timing'),
        "   st.id = t.store
        AND t.created_at >= '".$start." 00:00:00'
        AND t.created_at <= '".$to." 23:59:59'
        " 
        , array())

    ->joinLeft(array('st2' => 'staff'),'st2.id = t.staff_id'                    , array())
    ->joinLeft(array('ts'   => 'timing_sale')   , 't.id = ts.timing_id'         , array())
    ->joinLeft(array('rm3'  => 'regional_market')   , 't.regional_id = rm3.id'        , array())
    ->joinLeft(array('i'  => WAREHOUSE_DB.'.imei')      , 'ts.imei = i.imei_sn' , array())

    ->joinLeft(array('g' => WAREHOUSE_DB.'.good'),'g.id = i.good_id',array())

    ->joinLeft(array('d2' => WAREHOUSE_DB.'.distributor'),'i.distributor_id = d2.id',array())
    ->joinLeft(array('rm4' => HR_DB.'.regional_market'),'d2.region = rm4.id',array())
    ->joinLeft(array('a2' => HR_DB.'.area'),'rm4.area_id = a2.id',array())
    ->joinLeft(array('b' => WAREHOUSE_DB.'.brand'),'b.id = g.brand_id',array())

    ->where('i.imei_sn NOT IN (select imei from timing_control)')
    ->group('ts.imei');


    if ( isset($params['id']) && $params['id'] ) {
        $select->where('st.id LIKE ?', '%'.$params['id'].'%');
    }

    if (isset($params['brand']) && $params['brand']) {
        if (is_array($params['brand']) && count($params['brand']))
            $select->where('g.brand_id IN (?)', $params['brand']);
        elseif (is_numeric($params['brand']))
            $select->where('g.brand_id = ?', intval($params['brand']));
        else
            $select->where('1=0', 1);
    }

    if (isset($params['good_id']) && $params['good_id']) {
        if (is_array($params['good_id']) && count($params['good_id']))
            $select->where('ts.product_id IN (?)', $params['good_id']);
        elseif (is_numeric($params['good_id']))
            $select->where('ts.product_id = ?', intval($params['good_id']));
        else
            $select->where('1=0', 1);
    }

    if(isset($params['rgm_area']) && $params['rgm_area']) {
        $select->where('a2.id IN ('.$params['rgm_area'].')');
    }


    //cattagory
    if(isset($params['phone']) and $params['phone']){
        $select->where('g.cat_id =?',$params['phone']);
    }

    if(isset($params['iot']) and $params['iot']){
        $select->where('g.cat_id =?',$params['iot']);
    }

    if ( isset($params['name']) && $params['name'] ) {
        $select->where('st.name LIKE ?', '%'.$params['name'].'%');
    }
    if ( isset($params['shop_id']) && $params['shop_id'] ) {
        $select->where('st.store_id LIKE ?', '%'.$params['shop_id'].'%');
    }
    if ( isset($params['shop_code']) && $params['shop_code'] ) {
        $select->where('st.store_code LIKE ?', '%'.$params['shop_code'].'%');
    }
    if ( isset($params['it_junction']) && $params['it_junction'] ) {
        $select->where('st.it_junction = ?', $params['it_junction']);
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
                                        // Add Filter Store
    if (isset($params['store']) && $params['store']) {
        if (is_array($params['store']) && count($params['store']))
            $select->where('st.id IN (?)', $params['store']);
        elseif (is_numeric($params['store']))
            $select->where('st.id = ?', intval($params['store']));
        else
            $select->where('1=0', 1);
    }
                                        // Add Filter Store Type
    if (isset($params['org']) && $params['org']) {
        if (is_array($params['org']) && count($params['org']))
            $select->where('st.org_dealer IN (?)', $params['org']);
        elseif (is_numeric($params['org']))
            $select->where('st.org_dealer = ?', intval($params['org']));
        else
            $select->where('1=0', 1);
    }


    if (isset($params['asm']) && $params['asm']) {
        $QAsm = new Application_Model_Asm();
        $list_regions = $QAsm->get_cache($params['asm']);
        $list_regions = isset($list_regions['province']) && is_array($list_regions['province']) ? $list_regions['province'] : array();
        if (count($list_regions) > 0)
            $select->where('st.province_id IN (?)', $list_regions);
        else
            $select->where('1=0', 1);
    }
                                        // Add Filter Market Type, Market Name
    // if ( (isset($params['market_type']) and $params['market_type']) || (isset($params['market_name']) && $params['market_name']) ) {
    //     $select->join( array('sm' => 'store_market'), 'st.id = sm.store_id'         , array());
    //     $select->join( array('mn' => 'market_name') , 'sm.market_name_id = mn.id'   , array('market_name' => 'mn.name'));
    //                                     //$select->joinLeft( array('mt' => 'market_type') , 'mn.market_type_id = mt.id'   , array('market_type' => 'mt.nsame'));
    //                                     // filter Market Type
    //     if (isset($params['market_type']) and $params['market_type']) {
    //         if (is_array($params['market_type']) && count($params['market_type'])) {
    //             $select->where('mn.market_type_id IN (?)', $params['market_type']);
    //         } elseif (is_numeric($params['market_type'])) {

    //             $select->where('mn.market_type_id = ?', intval($params['market_type']));
    //         } else {
    //             $select->where('1=0', 1);
    //         }
    //     }
                                        // Filter Market Name
    //     if (isset($params['market_name']) and $params['market_name']) {
    //         if (is_array($params['market_name']) && count($params['market_name'])) {
    //             $select->where('mn.id IN (?)', $params['market_name']);
    //         } elseif (is_numeric($params['market_name'])) {

    //             $select->where('mn.id = ?', intval($params['market_name']));
    //         } else {
    //             $select->where('1=0', 1);
    //         }
    //     }
    // }


    $result = $db->fetchAll($select);
    $total = $db->fetchOne("select FOUND_ROWS()");

    return $result;
}


function report_by_product_imei($params)
{
    set_time_limit(0);
    ini_set('memory_limit', '-1');
    error_reporting(~E_ALL);
    ini_set("display_error", '0');

    $db = Zend_Registry::get('db');
    if ( isset($params['get_total_sales']) and $params['get_total_sales'] )
        $count_expr = array();
    else
        $count_expr = array(new Zend_Db_Expr('SQL_CALC_FOUND_ROWS t.id'));
    $select = $db->select()
    ->from(array('t' => 'timing'), $count_expr)
    ->joinLeft(array('ts' => 'timing_sale')             , 't.id=ts.timing_id AND t.approved_at <> 0 AND t.approved_at IS NOT NULL'  , array('imei_sn' => 'ts.imei'))
    ->joinLeft(array('g' => WAREHOUSE_DB.'.'.'good')    , 'g.id=ts.product_id'      , array('product_id' => 'g.id', 'product_name' => 'g.name', 'product_desc' => 'desc'))
    ->joinLeft(array('gc' => WAREHOUSE_DB.'.'.'good_color'), 'gc.id=ts.model_id'    , array('color_name' => 'gc.name'))
    ->joinLeft(array('s' => 'store')                    , 's.id=t.store'            , array())
    ->joinLeft(array('o' => 'org')                      , 's.org_dealer=o.org_id'   , array())
    ->joinLeft(array('r' => 'regional_market')          , 'r.id=s.regional_market'  , array('province'  => 'r.name'))
    ->joinLeft(array('a' => 'area')                     , 'a.id=r.area_id'          , array('area_id' => 'a.id', 'area_name' => 'a.name'))
    ->joinLeft(array('st' => 'staff'), 'st.id = t.staff_id', array(
        'reporter'  => "CONCAT(CONCAT(st.firstname,' '), st.lastname)",
        'staff_code'=> 'st.code'))
    ->joinLeft(array('gr' => 'group'), 'st.group_id = gr.id', array('group_name'=> 'gr.name'))

    ->where('a.id NOT IN (49,72)');
    $from = explode('/', $params['from']);
    $to = explode('/', $params['to']);
    if (isset($params['from']) && $params['from'])
        $select->where('t.created_at >= ?', $from[2].'-'.$from[1].'-'.$from[0] . ' 00:00:00');
    if (isset($params['to']) && $params['to'])
        $select->where('t.created_at <= ?', $to[2].'-'.$to[1].'-'.$to[0] . ' 23:59:59');

    if (isset($params['name'])) {
        if (is_array($params['name']) && count($params['name']) > 0) {
            $select->where('g.id IN (?)', $params['name']);
        } else {
            $select->where('g.id = ?', $params['name']);
        }
    }
    if (isset($params['area_id'])) {
        if (is_array($params['area_id']) && count($params['area_id']) > 0) {
            $select->where('a.id IN (?)', $params['area_id']);
        }
    }

                                //cattagory
    if(isset($params['phone']) and $params['phone']){
        $select->where('g.cat_id =?',$params['phone']);
    }

    if(isset($params['iot']) and $params['iot']){
        $select->where('g.cat_id =?',$params['iot']);
    }


    if (isset($params['asm']) && $params['asm']) {
        $QAsm = new Application_Model_Asm();
        $list_regions = $QAsm->get_cache($params['asm']);
        $list_regions = isset($list_regions['district']) && is_array($list_regions['district']) ? $list_regions['district'] : array();
        if (count($list_regions) > 0)
            $select->where('s.district IN (?)', $list_regions);
        else
            $select->where('1=0', 1);
    }

    if ( isset($params['am']) && $params['am'] ) {
        $QAm = new Application_Model_Am();
        $list_org = $QAm->get_cache($params['am']);
        $list_org = isset($list_org['store_type']) && is_array($list_org['store_type']) ? $list_org['store_type'] : array();
        if (count($list_org) > 0)
            $select->where( 's.org_dealer IN (?)', $list_org);
        else
            $select->where('1=0', 1);
    }
                                // check Permission Admin Brandshop
    if ( isset($params['admin_bs']) && $params['admin_bs'] ) {
        $select->where('(s.org_dealer = ?', 18);
        $select->orWhere('o.store_type_id = ?)', 3);
    }
    if (isset($params['sales_store']) && intval($params['sales_store']) > 0) {
        $select
        ->joinRight(array('ssl' => 'store_staff_log'), 'ssl.store_id=t.store', array());
        $log_where = $this->getAdapter()->quoteInto('ssl.staff_id = ?', $params['sales_store']).
        " AND " . $this->getAdapter()->quoteInto('ssl.is_leader = ?', 1).
        " AND " . $this->getAdapter()->quoteInto('t.created_at >= FROM_UNIXTIME(ssl.joined_at)', 1).
        " AND (".
        $this->getAdapter()->quoteInto('t.created_at < FROM_UNIXTIME(ssl.released_at)', 1).
        " OR " . $this->getAdapter()->quoteInto('ssl.released_at IS NULL', 1).
        " OR " . $this->getAdapter()->quoteInto('ssl.released_at = 0', 1).
        " ) ";
        $select->where($log_where);
    }
    if (isset($params['leader_id']) && intval($params['leader_id']) > 0) {
        $select
        ->joinRight(array('ssl' => 'store_leader_log'), 'ssl.store_id=t.store', array());
        $log_where = $this->getAdapter()->quoteInto('ssl.staff_id = ?', $params['leader_id']).
        " AND " . $this->getAdapter()->quoteInto('DATE(t.from) >= FROM_UNIXTIME(ssl.joined_at, \'%Y-%m-%d\')', 1).
        " AND (".
        $this->getAdapter()->quoteInto('DATE(t.from) < FROM_UNIXTIME(ssl.released_at, \'%Y-%m-%d\')', 1).
        " OR " . $this->getAdapter()->quoteInto('ssl.released_at IS NULL', 1).
        " OR " . $this->getAdapter()->quoteInto('ssl.released_at = 0', 1).
        " ) ";
        $select->where($log_where);
    }
    if (isset($params['bm_id']) && intval($params['bm_id']) > 0) {
        $select
        ->joinRight(array('ssl' => 'store_staff_log'), 'ssl.store_id=t.store', array());
        $log_where = $this->getAdapter()->quoteInto('ssl.staff_id = ?', $params['bm_id']).
        " AND " . $this->getAdapter()->quoteInto('ssl.is_leader = ?', 3).
        " AND " . $this->getAdapter()->quoteInto('t.created_at >= FROM_UNIXTIME(ssl.joined_at)', 1).
        " AND (".
        $this->getAdapter()->quoteInto('t.created_at < FROM_UNIXTIME(ssl.released_at)', 1).
        " OR " . $this->getAdapter()->quoteInto('ssl.released_at IS NULL', 1).
        " OR " . $this->getAdapter()->quoteInto('ssl.released_at = 0', 1).
        " ) ";
        $select->where($log_where);
    }

    if (isset($params['regional_market'])) {
        if (is_array($params['regional_market']) && count($params['regional_market']) > 0) {
            $select->where('r.id IN (?)', $params['regional_market']);
        }
    }
    if (isset($params['district'])) {
        if (is_array($params['district']) && count($params['district']) > 0) {
            $select->where('s.district IN (?)', $params['district']);
        }
    }
    if (isset($params['store'])) {
        if (is_array($params['store']) && count($params['store']) > 0) {
            $select->where('s.id IN (?)', $params['store']);
        }
    }

    $result = $db->fetchAll($select);

    $total = $db->fetchOne("select FOUND_ROWS()");
    return $result;
}

function report_by_imei($params) {
    set_time_limit(0); 
    ini_set('memory_limit', '-1');
    error_reporting(~E_ALL);
    ini_set("display_error", '0');

    $db = Zend_Registry::get('db');


    $get = array(
        'warehouse_id'          => 'w.id',
        'warehouse_name'        => 'w.name',
        'total'                 => new Zend_Db_Expr("COUNT(i.imei_sn)"),
        'activate_report'       => new Zend_Db_Expr("COUNT(CASE WHEN i.activated_date IS NOT NULL AND i.imei_sn = ts.imei THEN i.imei_sn END)"),
        'activated_not_report'  => new Zend_Db_Expr('COUNT(CASE WHEN i.imei_sn NOT IN (SELECT imei FROM timing_sale) AND i.activated_date IS NOT NULL THEN i.imei_sn END)'),
    );

    $select = $db->select()
    ->from(array('i' => WAREHOUSE_DB.'.imei'), $get)
    ->joinLeft(array('w' => WAREHOUSE_DB.'.warehouse'),'w.id = i.warehouse_id',array())
    ->joinLeft(array('ts' => 'timing_sale'),'ts.imei = i.imei_sn',array())
    ->joinLeft(array('g' => WAREHOUSE_DB.'.good'),'g.id = i.good_id',array())
    ->joinLeft(array('b' => WAREHOUSE_DB.'.brand'),'b.id = g.brand_id',array('name'))

    ->group('w.id');

    if (isset($params['activate_from']) and $params['activate_from']){
        list( $day, $month, $year ) = explode('/', $params['activate_from']);
        list( $year,$time ) = explode(' ', $year);

        if (isset($day) and isset($month) and isset($year) ){
            $select->where('i.activated_date >= ?', $year.'-'.$month.'-'.$day.' '.'00:00:00');
        }
    }

    if (isset($params['activate_to']) and $params['activate_to']){
        list( $day, $month, $year ) = explode('/', $params['activate_to']);
        list( $year,$time ) = explode(' ', $year);

        if (isset($day) and isset($month) and isset($year) ){
            $select->where('i.activated_date <= ?', $year.'-'.$month.'-'.$day.' '.'23:59:59');
        }
    }

    if (isset($params['timing_from']) and $params['timing_from']){
        list( $day, $month, $year ) = explode('/', $params['timing_from']);
        list( $year,$time ) = explode(' ', $year);

        if (isset($day) and isset($month) and isset($year) ){
            $select->where('ts.time_add >= ?', $year.'-'.$month.'-'.$day.' '.'00:00:00');
        }
    }

    if (isset($params['timing_to']) and $params['timing_to']){
        list( $day, $month, $year ) = explode('/', $params['timing_to']);
        list( $year,$time ) = explode(' ', $year);

        if (isset($day) and isset($month) and isset($year) ){
            $select->where('ts.time_add <= ?', $year.'-'.$month.'-'.$day.' '.'23:59:59');
        }
    }

    if (isset($params['good_id'])) {
        if (is_array($params['good_id']) && count($params['good_id']) > 0) {
            $select->where('i.good_id IN (?)', $params['good_id']);
        } else {
            $select->where('i.good_id IN (?)', $params['good_id']);
        }
    }

    if (isset($params['warehouse_id']) && $params['warehouse_id']) {
        if (is_array($params['warehouse_id']) && count($params['warehouse_id']))
            $select->where('i.warehouse_id IN (?)', $params['warehouse_id']);
        elseif (is_numeric($params['warehouse_id']))
            $select->where('i.warehouse_id = ?', intval($params['warehouse_id']));
        else
            $select->where('1=0', 1);
    }

    if ( isset($params['total_sales']) and $params['total_sales'] ){
        $get_p = array(
            'total_unit_activated' => new Zend_Db_Expr('SUM( pa.total )'),
            'total_unit_activated_timing' => new Zend_Db_Expr('SUM( pa.activate_report )'),
            'total_unit_activated_not_timing' => new Zend_Db_Expr('SUM( pa.activated_not_report )'),
        );
        $select_p = $db->select()
        ->from(array('pa' => $select), $get_p);
        return $db->fetchAll($select_p);
    }


    $result = $db->fetchAll($select);
    return $result;
}

function report_by_imei_product($params){
    set_time_limit(0);
    ini_set('memory_limit', '-1');
    error_reporting(~E_ALL);
    ini_set("display_error", '0');

    $db = Zend_Registry::get('db');
    $get = array(
        'product_name' => 'g.name',
        'total'                 => new Zend_Db_Expr("COUNT(i.imei_sn)"),
        'activate_report'       => new Zend_Db_Expr("COUNT(CASE WHEN i.activated_date IS NOT NULL AND i.imei_sn = ts.imei THEN i.imei_sn END)"),
        'activated_not_report'  => new Zend_Db_Expr('COUNT(CASE WHEN i.imei_sn NOT IN (SELECT imei FROM timing_sale) AND i.activated_date IS NOT NULL THEN i.imei_sn END)'),
    );

    $select = $db->select()
    ->from(array('i' => WAREHOUSE_DB.'.imei'),$get)
    ->joinLeft(array('g' => WAREHOUSE_DB.'.good'),'g.id = i.good_id',array())
    ->joinLeft(array('w' => WAREHOUSE_DB.'.warehouse'),'w.id = i.warehouse_id',array())
    ->joinLeft(array('ts' => 'timing_sale'),'ts.imei = i.imei_sn',array())

    ->where('g.cat_id =?',11)
    ->where('w.status IS NULL')
    ->where('i.imei_sn NOT IN (SELECT imei FROM timing_sale)')
    ->group('g.id')
    ->order('g.name');

    if (isset($params['activate_from']) and $params['activate_from']){
        list( $day, $month, $year ) = explode('/', $params['activate_from']);
        list( $year,$time ) = explode(' ', $year);

        if (isset($day) and isset($month) and isset($year) ){
            $select->where('i.activated_date >= ?', $year.'-'.$month.'-'.$day.' '.'00:00:00');
        }
    }

    if (isset($params['activate_to']) and $params['activate_to']){
        list( $day, $month, $year ) = explode('/', $params['activate_to']);
        list( $year,$time ) = explode(' ', $year);

        if (isset($day) and isset($month) and isset($year) ){
            $select->where('i.activated_date <= ?', $year.'-'.$month.'-'.$day.' '.'23:59:59');
        }
    }

    if (isset($params['timing_from']) and $params['timing_from']){
        list( $day, $month, $year ) = explode('/', $params['timing_from']);
        list( $year,$time ) = explode(' ', $year);

        if (isset($day) and isset($month) and isset($year) ){
            $select->where('ts.time_add >= ?', $year.'-'.$month.'-'.$day.' '.'00:00:00');
        }
    }

    if (isset($params['timing_to']) and $params['timing_to']){
        list( $day, $month, $year ) = explode('/', $params['timing_to']);
        list( $year,$time ) = explode(' ', $year);

        if (isset($day) and isset($month) and isset($year) ){
            $select->where('ts.time_add <= ?', $year.'-'.$month.'-'.$day.' '.'23:59:59');
        }
    }

    if (isset($params['good_id'])) {
        if (is_array($params['good_id']) && count($params['good_id']) > 0) {
            $select->where('i.good_id IN (?)', $params['good_id']);
        } else {
            $select->where('i.good_id IN (?)', $params['good_id']);
        }
    }

    if (isset($params['warehouse_id']) && $params['warehouse_id']) {
        if (is_array($params['warehouse_id']) && count($params['warehouse_id']))
            $select->where('i.warehouse_id IN (?)', $params['warehouse_id']);
        elseif (is_numeric($params['warehouse_id']))
            $select->where('i.warehouse_id = ?', intval($params['warehouse_id']));
        else
            $select->where('1=0', 1);
    }


    $result = $db->fetchAll($select);
    return $result;
}

function report_by_imei_distributor($params){
    set_time_limit(0);
    ini_set('memory_limit', '-1');
    error_reporting(~E_ALL);
    ini_set("display_error", '0');

    $db = Zend_Registry::get('db');
    $get = array(
        'store_name' => 'st.name',
        'store_code' => 'st.store_code',
        'distributor_name' => 'd.title',
        'product_name'      => 'g.name',
        'color'             => 'gc.name',
        'imei'              => 'i.imei_sn',
        'activated_date'    => 'i.activated_date',
        'warehouse_name'    =>'w.name',
    );

    $select = $db->select()
    ->from(array('i' => WAREHOUSE_DB.'.imei'),$get)
    ->joinLeft(array('g' => WAREHOUSE_DB.'.good'),'g.id = i.good_id',array())
    ->joinLeft(array('st' => 'store'), 'st.id = i.store_id', array())
    ->joinLeft(array('w' => WAREHOUSE_DB.'.warehouse'),'w.id = i.warehouse_id',array())
    ->joinLeft(array('ts' => 'timing_sale'),'ts.imei = i.imei_sn',array())
    ->joinLeft(array('d' => WAREHOUSE_DB.'.distributor'),'d.id = i.distributor_id',array())
    ->joinLeft(array('gc' => WAREHOUSE_DB.'.good_color'),'gc.id = i.good_color',array())

    ->where('g.cat_id =?',11)
    ->where('i.distributor_id IS NOT NULL')
    ->where('i.imei_sn NOT IN (SELECT imei FROM timing_sale)');


    if (isset($params['activate_from']) and $params['activate_from']){
        list( $day, $month, $year ) = explode('/', $params['activate_from']);
        list( $year,$time ) = explode(' ', $year);

        if (isset($day) and isset($month) and isset($year) ){
            $select->where('i.activated_date >= ?', $year.'-'.$month.'-'.$day.' '.'00:00:00');
        }
    }

    if (isset($params['activate_to']) and $params['activate_to']){
        list( $day, $month, $year ) = explode('/', $params['activate_to']);
        list( $year,$time ) = explode(' ', $year);

        if (isset($day) and isset($month) and isset($year) ){
            $select->where('i.activated_date <= ?', $year.'-'.$month.'-'.$day.' '.'23:59:59');
        }
    }

    if (isset($params['timing_from']) and $params['timing_from']){
        list( $day, $month, $year ) = explode('/', $params['timing_from']);
        list( $year,$time ) = explode(' ', $year);

        if (isset($day) and isset($month) and isset($year) ){
            $select->where('ts.time_add >= ?', $year.'-'.$month.'-'.$day.' '.'00:00:00');
        }
    }

    if (isset($params['timing_to']) and $params['timing_to']){
        list( $day, $month, $year ) = explode('/', $params['timing_to']);
        list( $year,$time ) = explode(' ', $year);

        if (isset($day) and isset($month) and isset($year) ){
            $select->where('ts.time_add <= ?', $year.'-'.$month.'-'.$day.' '.'23:59:59');
        }
    }

    if (isset($params['good_id'])) {
        if (is_array($params['good_id']) && count($params['good_id']) > 0) {
            $select->where('i.good_id IN (?)', $params['good_id']);
        } else {
            $select->where('i.good_id IN (?)', $params['good_id']);
        }
    }

    $result = $db->fetchAll($select);
    return $result;
}

function report_by_imei_warehouse($params){
    set_time_limit(0);
    ini_set('memory_limit', '-1');
    error_reporting(~E_ALL);
    ini_set("display_error", '0');

    $db = Zend_Registry::get('db');
    $get = array(
        'warehouse_name'    => 'w.name',
        'product_name'      => 'g.name',
        'color'             => 'gc.name',
        'imei'              => 'i.imei_sn',
        'activated_date'    => 'i.activated_date',
    );

    $select = $db->select()
    ->from(array('i' => WAREHOUSE_DB.'.imei'),$get)
    ->joinLeft(array('g' => WAREHOUSE_DB.'.good'),'g.id = i.good_id',array())
    ->joinLeft(array('w' => WAREHOUSE_DB.'.warehouse'),'w.id = i.warehouse_id',array())
    ->joinLeft(array('ts' => 'timing_sale'),'ts.imei = i.imei_sn',array())
    ->joinLeft(array('gc' => WAREHOUSE_DB.'.good_color'),'gc.id = i.good_color',array())

    ->where('g.cat_id =?',11)
    ->where('i.distributor_id IS NULL')
    ->where('i.imei_sn NOT IN (SELECT imei FROM timing_sale)');

    if (isset($params['activate_from']) and $params['activate_from']){
        list( $day, $month, $year ) = explode('/', $params['activate_from']);
        list( $year,$time ) = explode(' ', $year);

        if (isset($day) and isset($month) and isset($year) ){
            $select->where('i.activated_date >= ?', $year.'-'.$month.'-'.$day.' '.'00:00:00');
        }
    }

    if (isset($params['activate_to']) and $params['activate_to']){
        list( $day, $month, $year ) = explode('/', $params['activate_to']);
        list( $year,$time ) = explode(' ', $year);

        if (isset($day) and isset($month) and isset($year) ){
            $select->where('i.activated_date <= ?', $year.'-'.$month.'-'.$day.' '.'23:59:59');
        }
    }

    if (isset($params['timing_from']) and $params['timing_from']){
        list( $day, $month, $year ) = explode('/', $params['timing_from']);
        list( $year,$time ) = explode(' ', $year);

        if (isset($day) and isset($month) and isset($year) ){
            $select->where('ts.time_add >= ?', $year.'-'.$month.'-'.$day.' '.'00:00:00');
        }
    }

    if (isset($params['timing_to']) and $params['timing_to']){
        list( $day, $month, $year ) = explode('/', $params['timing_to']);
        list( $year,$time ) = explode(' ', $year);

        if (isset($day) and isset($month) and isset($year) ){
            $select->where('ts.time_add <= ?', $year.'-'.$month.'-'.$day.' '.'23:59:59');
        }
    }

    if (isset($params['good_id'])) {
        if (is_array($params['good_id']) && count($params['good_id']) > 0) {
            $select->where('i.good_id IN (?)', $params['good_id']);
        } else {
            $select->where('i.good_id IN (?)', $params['good_id']);
        }
    }

    if (isset($params['warehouse_id']) && $params['warehouse_id']) {
        if (is_array($params['warehouse_id']) && count($params['warehouse_id']))
            $select->where('i.warehouse_id IN (?)', $params['warehouse_id']);
        elseif (is_numeric($params['warehouse_id']))
            $select->where('i.warehouse_id = ?', intval($params['warehouse_id']));
        else
            $select->where('1=0', 1);
    }

    $result = $db->fetchAll($select);
    return $result;
}

function reportTimng($params)
{
    set_time_limit(0);
    ini_set('memory_limit', '-1');
    error_reporting(~E_ALL);
    ini_set("display_error", '0');

    $from = explode('/', $params['from']);
    $to = explode('/', $params['to']);

    $form_date  = $from[2].'-'.$from[1].'-'.$from[0];
    $to_date = $to[2].'-'.$to[1].'-'.$to[0];
    $to_day = date('Y-m-d'); // ວັນທີ່ປະຈຸບັນ
    $data_data =  date("Y-m-t", strtotime($form_date));
    $first_day_of_month = $from[2].'-'.$from[1].'-'.'01'; // ມື້ທຳອິດຂອງເດືອນ
    $last_date_of_month = date("Y-m-t", strtotime($form_date)); // ມື້ສຸດທ້າຍຂອງເດືອນ

    $db = Zend_Registry::get('db');

    $sub_select_01 = $db->select()
    ->from(array('oat' => 'oppo_area_target'),array('target' =>'SUM(oat.target)'));

    if (isset($params['from']) && $params['from']){
        $sub_select_01->where('oat.from_date =? ',$first_day_of_month);
    }

    if(isset($params['target_date']) && $params['target_date']){
        $sub_select_01->where('oat.to_date =? ',$data_data);
    }

    $sub_select_02 = $db->select()
    ->from(array('oat' => 'oppo_area_target'),array('target' =>'SUM(oat.target_hero)'));

    if (isset($params['from']) && $params['from']){
        $sub_select_02->where('oat.from_date =? ',$first_day_of_month);
    }

    if(isset($params['target_date']) && $params['target_date']){
        $sub_select_02->where('oat.to_date =? ',$data_data);
    }



    $get = array(
        /* ຕາມຊວງເວລາ ຂອງການຄົ້ນຫາ */
        'allmodel_sellout' => new Zend_Db_Expr("COUNT(CASE WHEN t.created_at >= '".$first_day_of_month." 00:00:00' AND t.created_at <= '".$to_date." 23:59:59' THEN ts.imei END)"),
        'hero_sellout'  => new Zend_Db_Expr("COUNT( CASE WHEN ts.is_hero = 1 AND t.created_at >= '".$first_day_of_month." 00:00:00' AND t.created_at <= '".$to_date." 23:59:59' THEN ts.imei END )"),
        /* end */

        /* ສະເພາະວັນທີ່ປະຈຸບັນ ບໍ່ມີຜົນກັບຄົ້ນຫາ */
        'to_day_allmodel' => new Zend_Db_Expr("COUNT(CASE WHEN t.created_at >= '".$form_date." 00:00:00' AND t.created_at <= '".$to_date." 23:59:59' THEN ts.imei END)"),
        'to_day_hero' => new Zend_Db_Expr("COUNT(CASE WHEN t.created_at >= '".$form_date." 00:00:00' AND t.created_at <= '".$to_date." 23:59:59' AND ts.is_hero = 1 THEN ts.imei END)"),
        /* end */

        /* ເຄືອງທີ່ Activate ຕາມຊວງເວລາຄົ້ນຫາ */
        'activated_all_model' => new Zend_Db_Expr("COUNT(CASE WHEN i.activated_date IS NOT NULL AND t.created_at >= '".$form_date." 00:00:00' AND t.created_at <= '".$to_date." 23:59:59' THEN ts.imei END)"),
        'activated_hero_product' => new Zend_Db_Expr("COUNT(CASE WHEN i.activated_date IS NOT NULL AND ts.is_hero = 1 AND t.created_at >= '".$form_date." 00:00:00' AND t.created_at <= '".$to_date." 23:59:59' THEN ts.imei END)"),
        /* end */

        /* ເຄືອງທີ່ Activate ວັນທີ່ປະຈຸບັນ */
        'activate_all_model_today' => new Zend_Db_Expr("COUNT(CASE WHEN t.created_at >= '".$to_day." 00:00:00' AND t.created_at <= '".$to_day." 23:59:59' AND i.activated_date IS NOT NULL THEN ts.imei END)"),
        'activate_hero_product_today' => new Zend_Db_Expr("COUNT(CASE WHEN t.created_at >= '".$to_day." 00:00:00' AND t.created_at <= '".$to_day." 23:59:59' AND i.activated_date IS NOT NULL AND ts.is_hero = 1 THEN ts.imei END)"),
        /* end */

        /* ເປົ່າໝາຍຍອດຂາຍ ຕາມຊວງເວລາຄົ້ນຫາ ຄ່າຈະບວກຕາມເດືອນ*/
        'target' => new Zend_Db_Expr("(".$sub_select_01.")"),
        'target_hero' => new Zend_Db_Expr("(".$sub_select_02.")"),
        /* end */
    );

    $select = $db->select()
    ->from(array('i' => WAREHOUSE_DB.'.imei'),$get)
    ->joinLeft(array('ts' => 'timing_sale'),'ts.imei = i.imei_sn',array())
    ->joinLeft(array('t' => 'timing'),'t.id = ts.timing_id',array());

    $result = $db->fetchAll($select);
        // echo $select;
        // die;

    // print_r($result);
    // exit;
    return $result;
}



function AreaTimingReport($params)
{
    set_time_limit(0);
    ini_set('memory_limit', '-1');
    error_reporting(~E_ALL);
    ini_set("display_error", '0');

    $from = explode('/', $params['from']);
    $to = explode('/', $params['to']);

    $form_date  = $from[2].'-'.$from[1].'-'.$from[0];
    $to_date = $to[2].'-'.$to[1].'-'.$to[0];
    $to_day = date('Y-m-d'); // ວັນທີ່ປະຈຸບັນ
    $data_data =  date("Y-m-t", strtotime($form_date));
    $first_day_of_month = $from[2].'-'.$from[1].'-'.'01'; // ມື້ທຳອິດຂອງເດືອນ
    $last_date_of_month = date("Y-m-t", strtotime($form_date)); // ມື້ສຸດທ້າຍຂອງເດືອນ

    $db = Zend_Registry::get('db');

    $sub_select_01 = $db->select()
    ->from(array('oat' => 'oppo_area_target'),array('target' =>'oat.target'))
    ->where('oat.area_id = a.id');

    $sub_select_03 = $db->select()->from(array('t' => 'timing'),array('SUM(ts.sales_price)'))
    ->join(array('ts' => 'timing_sale'),'t.id = ts.timing_id',array())
    ->joinLeft(array('rm' => 'regional_market'),'rm.id = t.store_area',array())

    ->where('t.created_at >= ?',$form_date.' 00:00:00')
    ->where('t.created_at <= ?',$to_date.' 23:59:59');

    $sub_select_04 = $db->select()->from(array('t' => 'timing'),array('SUM(ts.sales_price)'))
    ->join(array('ts' => 'timing_sale'),'t.id = ts.timing_id',array())
    ->joinLeft(array('rm' => 'regional_market'),'rm.id = t.store_area',array())

    ->where('t.created_at >= ?',$first_day_of_month.' 00:00:00')
    ->where('t.created_at <= ?',$to_date.' 23:59:59');

    if (isset($params['from']) && $params['from']){
        $sub_select_01->where('oat.from_date =? ',$first_day_of_month);
    }

    if(isset($params['target_date']) && $params['target_date']){
        $sub_select_01->where('oat.to_date =? ',$data_data);
    }

    $sub_select_02 = $db->select()
    ->from(array('oat' => 'oppo_area_target'),array('target' =>'SUM(oat.target_hero)'))
    ->where('oat.area_id = a.id');

    if (isset($params['from']) && $params['from']){
        $sub_select_02->where('oat.from_date =? ',$first_day_of_month);
    }

    if(isset($params['target_date']) && $params['target_date']){
        $sub_select_02->where('oat.to_date =? ',$data_data);
    } 

    $get = array(
        'area_name' => 'a.name',
        'score_point' => 'scr.gfk',

        /* ຕາມຊວງເວລາ ຂອງການຄົ້ນຫາ */
        'allmodel_sellout' => new Zend_Db_Expr("COUNT(CASE WHEN t.created_at >= '".$first_day_of_month." 00:00:00' AND t.created_at <= '".$to_date." 23:59:59' THEN ts.imei END)"),
        'hero_sellout'  => new Zend_Db_Expr("COUNT( CASE WHEN ts.is_hero = 1 AND t.created_at >= '".$first_day_of_month." 00:00:00' AND t.created_at <= '".$to_date." 23:59:59' THEN ts.imei END )"),

        /* AAA ຈຳນວນເງິນ ຂອງການຄົ້ນຫາ */ 
        'monthly_price'  => new Zend_Db_Expr("SUM(CASE WHEN ts.sales_price AND ts.time_add >= '".$first_day_of_month." 00:00:00' AND ts.time_add <= '".$to_date." 23:59:59' THEN ts.sales_price END)"),
        'today_price'  => new Zend_Db_Expr("SUM(CASE WHEN ts.sales_price AND ts.time_add >= '".$form_date." 00:00:00' AND ts.time_add <= '".$to_date." 23:59:59' THEN ts.sales_price END)"),
        'total_today_price'  => new Zend_Db_Expr("(".$sub_select_03.")"),
        'total_monthly_price'  => new Zend_Db_Expr("(".$sub_select_04.")"),


        /* ສະເພາະວັນທີ່ປະຈຸບັນ ບໍ່ມີຜົນກັບຄົ້ນຫາ */
        'to_day_allmodel' => new Zend_Db_Expr("COUNT(CASE WHEN t.created_at >= '".$form_date." 00:00:00' AND t.created_at <= '".$to_date." 23:59:59' THEN ts.imei END)"),
        'to_day_hero' => new Zend_Db_Expr("COUNT(CASE WHEN t.created_at >= '".$form_date." 00:00:00' AND t.created_at <= '".$to_date." 23:59:59' AND ts.is_hero = 1 THEN ts.imei END)"),


        /* ເຄືອງທີ່ Activate ຕາມຊວງເວລາຄົ້ນຫາ */
        'activated_all_model' => new Zend_Db_Expr("COUNT(CASE WHEN i.activated_date IS NOT NULL AND t.created_at >= '".$form_date." 00:00:00' AND t.created_at <= '".$to_date." 23:59:59' THEN ts.imei END)"),
        'activated_hero_product' => new Zend_Db_Expr("COUNT(CASE WHEN i.activated_date IS NOT NULL AND ts.is_hero = 1 AND t.created_at >= '".$form_date." 00:00:00' AND t.created_at <= '".$to_date." 23:59:59' THEN ts.imei END)"),


        /* ເຄືອງທີ່ Activate ວັນທີ່ປະຈຸບັນ */
        'activate_all_model_today' => new Zend_Db_Expr("COUNT(CASE WHEN t.created_at >= '".$to_day." 00:00:00' AND t.created_at <= '".$to_day." 23:59:59' AND i.activated_date IS NOT NULL THEN ts.imei END)"),
        'activate_hero_product_today' => new Zend_Db_Expr("COUNT(CASE WHEN t.created_at >= '".$to_day." 00:00:00' AND t.created_at <= '".$to_day." 23:59:59' AND i.activated_date IS NOT NULL AND ts.is_hero = 1 THEN ts.imei END)"),


        /* ເປົ່າໝາຍຍອດຂາຍ ຕາມຊວງເວລາຄົ້ນຫາ ຄ່າຈະບວກຕາມເດືອນ*/
        'target' => new Zend_Db_Expr("(".$sub_select_01.")"),
        'target_hero' => new Zend_Db_Expr("(".$sub_select_02.")"),

    );

    $select = $db->select()
    ->from(array('i' => WAREHOUSE_DB.'.imei'),$get)
    ->joinLeft(array('ts' => 'timing_sale'),'ts.imei = i.imei_sn',array())
    ->joinLeft(array('t' => 'timing'),'t.id = ts.timing_id',array())
    ->joinLeft(array('s' => 'store'),'s.id = t.store',array())
    ->joinLeft(array('rm' => 'regional_market'),'rm.id = t.store_area',array())
    ->joinLeft(array('a' => 'area'),'rm.area_id = a.id',array())
    ->joinLeft(array('scr' => 'area_gfk'),'scr.area_id = a.id', array())
    ->where('a.id NOT IN (?)' , array(120))
    ->group('a.id')
    ->order('a.name ASC');

    $result = $db->fetchAll($select);
    return $result;
}

function fetchPagination_DOI_Detail($page, $limit, &$total, $params) {
    set_time_limit(0);
    ini_set('memory_limit', '-1');
    error_reporting(~E_ALL);
    ini_set("display_error", '0');
    $d1 = explode('/', $params['from']);
    $from = $d1[2].'-'.$d1[1].'-'.$d1[0];
    $d2 = explode('/', $params['to']);
    $to = $d2[2].'-'.$d2[1].'-'.$d2[0];
    $mtd = ((strtotime($to) - strtotime($from)) / (60*60*24)) + 1;
    $db = Zend_Registry::get('db');

    $sub_select = $db->select()
    ->from(array('d2'   => WAREHOUSE_DB.'.distributor'), array('COUNT(i2.imei_sn)'))
    ->joinleft(array('i2'   => WAREHOUSE_DB.'.imei'), 'd2.id = i2.distributor_id', array())
    ->where('i2.activated_date >= ?', $from." 00:00:00")
    ->where('i2.activated_date <= ?', $to." 23:59:59")
    ->where('d.id = d2.id')
    ->group('d2.id');

// Add Filter Model for sub_select
    if (isset($params['good_id']) && $params['good_id']) {
        if (is_array($params['good_id']) && count($params['good_id']))
            $sub_select->where('i2.good_id IN (?)', $params['good_id']);
        elseif (is_numeric($params['good_id']))
            $sub_select->where('i2.good_id = ?', intval($params['good_id']));
        else
            $sub_select->where('1=0', 1);
    }


// Add Filter Color for sub_select
    if (isset($params['color_id']) && $params['color_id']) {
        if (is_array($params['color_id']) && count($params['color_id']))
            $sub_select->where('i2.good_color IN (?)', $params['color_id']);
        elseif (is_numeric($params['color_id']))
            $sub_select->where('i2.good_color = ?', intval($params['color_id']));
        else
            $sub_select->where('1=0', 1);
    }

    if ( (isset($params['get_total_count']) and $params['get_total_count'] == 0) ) {
        $get_01['d_id'] = 'd.id';
    } else {
        $get_01['d_id'] = new Zend_Db_Expr('SQL_CALC_FOUND_ROWS d.id');
    }
    
    $get_02 = array(
// 'd_id'      => new Zend_Db_Expr("SQL_CALC_FOUND_ROWS d.id"),
        'd_name'    => 'd.title',
        'd_type'    => new Zend_Db_Expr(
            "(CASE
            WHEN d.rank = 1 THEN 'ORG-WDS'
            WHEN d.rank = 2 THEN 'ORG'
            WHEN d.rank = 5 THEN 'ORG-Dtac/Advice'
            WHEN d.rank = 6 THEN 'ORG-Lotus/Power Buy'
            WHEN d.rank = 7 THEN 'Dealer'
            WHEN d.rank = 8 THEN 'HUB'
            WHEN d.rank = 9 THEN 'Laos'
            WHEN d.rank = 3 THEN 'Online and Staff'
            WHEN d.rank = 10 THEN 'Brand Shop/Service'
            WHEN d.rank = 11 THEN 'King Power'
            WHEN d.rank = 12 THEN 'Jaymart'
            WHEN d.rank = 13 THEN 'Brand Shop By Dealer'
            ELSE d.rank
            END)"),
        'grand_area'    => 'ga.name',
        'd_area'        => 'a.name',
        'provience_name' => 'rm.name',
        'sellout'       => new Zend_Db_Expr("(".$sub_select.")"),
// 'stock'         => new Zend_Db_Expr("(COUNT(i.imei_sn) -( COUNT(CASE WHEN i.activated_date IS NOT NULL THEN i.imei_sn END) - COUNT(CASE WHEN i.activated_date_stock IS NOT NULL THEN i.imei_sn END)))"), ປິດກ່ອນ
        'stock'         => new Zend_Db_Expr("COUNT(i.imei_sn)"),
        'imeisn'        => 'i.imei_sn',
        'instock'       => 'i.out_date',
        'category' => 'g.cat_id',
        'good'   => 'i.good_id',
        'warehouse_name' => 'w.name',
        'color'   => 'i.good_color',
        'selling_rate'  => new Zend_Db_Expr("( ( (".$sub_select.") / ".$mtd." ) )"),
        'safety_stock'  => new Zend_Db_Expr("(( ( (".$sub_select.") / ".$mtd." ) ) * ".$params['doi_rate'].")"),
        'indicator'     => new Zend_Db_Expr("( (COUNT(i.imei_sn) - COUNT(CASE WHEN i.activated_date IS NOT NULL THEN i.imei_sn END)) - (( ( (".$sub_select.") / ".$mtd." ) ) * ".$params['doi_rate'].") )"),
    );
    $get = $get_01 + $get_02;
    $select = $db->select()
    ->from(array('d'    => WAREHOUSE_DB.'.distributor'), $get)
    ->join(array('rm'   => 'regional_market')   , 'd.region = rm.id'            , array())
    ->join(array('a'    => 'area')              , 'rm.area_id = a.id'           , array())
    ->joinLeft(array('gal' => 'grand_area_list'), 'a.id = gal.area'             , array())
    ->joinLeft(array('ga'  => 'grand_area')     , 'gal.grand_area_id = ga.id'   , array())
    ->joinleft(array('i'    => WAREHOUSE_DB.'.imei'), 'd.id = i.distributor_id'     , array())
    ->joinleft(array('g'   => WAREHOUSE_DB.'.good'), 'i.good_id = g.id', array())
    ->joinleft(array('w'   => WAREHOUSE_DB.'.warehouse'), 'w.id = d.warehouse_id', array())
// ->joinLeft(array('plg' => 'print_log'),'plg.good_id = i.good_id',array())
//->where('i.imei_sn IN (Select imei from print_log)')

    ->where('d.region != 8599')
    ->where('d.del IS NULL')
    ->group('i.imei_sn');

// ->having('sellout > ?', 0)
    // ->order('indicator ASC');
//export Doi imei
    // if(isset($params['list_imei']) && $params['list_imei']) {
    //         // $select->join(array('st' => 'store'),'st.d_id = d.id',array());
    //         // $select->where('d.id =?',$params['d_id']);
    //     $select->group('i.imei_sn');

    // }

    if(isset($params['status']) && $params['status'] == 1){
        $select->where('i.activated_date IS null');
    $select->where('i.imei_sn NOT IN (select imei from timing_sale)'); // Check timing_sale
}
if(isset($params['status']) and $params['status'] == 2){
    $select->where('i.imei_sn NOT IN (select imei from timing_sale)'); // Check timing_sale
}


//Case Warehouse
if(isset($params['warehouse_id']) and $params['warehouse_id']){
   $select->where('i.warehouse_id =?',$params['warehouse_id']);
}

if ( isset($params['d_id']) && $params['d_id'] ) {
    $select->where('d.id = ?', $params['d_id']);
}

if ( isset($params['d_name']) && $params['d_name'] ) {
    $select->where('d.title LIKE ?', '%'.$params['d_name'].'%');
}

if(isset($params['phone']) and $params['phone']){
    $select->where('g.cat_id =?',$params['phone']);
}

if(isset($params['iot']) and $params['iot']){
    $select->where('g.cat_id =?',$params['iot']);
}

// Add Filter Model
if (isset($params['good_id']) && $params['good_id']) {
    if (is_array($params['good_id']) && count($params['good_id']))
        $select->where('i.good_id IN (?)', $params['good_id']);
    elseif (is_numeric($params['good_id']))
        $select->where('i.good_id = ?', intval($params['good_id']));
    else
        $select->where('1=0', 1);
}
// Add Filter Color
if (isset($params['color_id']) && $params['color_id']) {
    if (is_array($params['color_id']) && count($params['color_id']))
        $select->where('i.good_color IN (?)', $params['color_id']);
    elseif (is_numeric($params['color_id']))
        $select->where('i.good_color = ?', intval($params['color_id']));
    else
        $select->where('1=0', 1);
}
// Add Filter Grand Area [Table]
if (isset($params['grand_area_id']) && $params['grand_area_id']) {
    if (is_array($params['grand_area_id']) && count($params['grand_area_id']))
        $select->where('ga.id IN (?)', $params['grand_area_id']);
    elseif (is_numeric($params['grand_area_id']))
        $select->where('ga.id = ?', intval($params['grand_area_id']));
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

// Permission for ASM / Sale Admin / Trainer
if ( isset($params['asm']) && $params['asm'] ) {
    $QAsm = new Application_Model_Asm();
    $list_regions = $QAsm->get_cache($params['asm']);
    $list_regions = isset($list_regions['district']) && is_array($list_regions['district']) ? $list_regions['district'] : array();
    if (count($list_regions) > 0)
        $select->where( 'd.district IN (?)', $list_regions);
    else
        $select->where('1=0', 1);
}
// Permission for AM
if ( isset($params['am']) && $params['am'] ) {
    $QAm = new Application_Model_Am();
    $list_org = $QAm->get_cache($params['am']);
    $list_org = isset($list_org['store_type']) && is_array($list_org['store_type']) ? $list_org['store_type'] : array();
    if (count($list_org) > 0)
        $select->where( 'd.ka_type IN (?)', $list_org);
    else
        $select->where('1=0', 1);
}
// Permission for Admin Brandshop
if ( isset($params['admin_bs']) && $params['admin_bs'] ) {
    $select->where( 'd.rank IN (?)', array(10,13));
}
/*
// Permission for Sale
if (isset($params['sale_id']) && intval($params['sale_id']) > 0) {
$select->joinRight(array('ssl' => 'store_staff'), 'ssl.store_id = st.id', array());
$log_where = $this->getAdapter()->quoteInto('ssl.staff_id = ?', $params['sale_id']).
" AND " . $this->getAdapter()->quoteInto('ssl.is_leader = ?', 1);
$select->where($log_where);
}*/
if (isset($params['get_total_count']) && $params['get_total_count'] == 0) {
    $select_p = $db->select()
    ->from(array('pa' => $select), array(
        'sum_stock' => new Zend_Db_Expr("SUM( pa.stock )"),
    ));
//echo $select_p; die;
    return $db->fetchRow($select_p);
}
// $select->orWhere('i.activated_date_stock =?',1)   ປິດກ່ອນ
if ($limit)
    $select->limitPage($page, $limit);
// print_r($params);
// echo $select; die;
$result = $db->fetchAll($select);
if ($limit)
    $total = $db->fetchOne("select FOUND_ROWS()");
return $result;
}

function DistributorStockDetail($params){
    $db = Zend_Registry::get('db');

    $get= array(
        'store_code'        => 's.store_code',
        'model_name'        => 'g.name',
        'store_name'        => 's.name',
        'color_name'        => 'gc.name',
        'brand_id'          => 'g.brand_id',
        'qulity'            => new Zend_Db_Expr("COUNT(CASE WHEN i.good_id = g.id and i.good_color = gc.id THEN i.imei_sn END)"),
    );

    $select = $db->select()
    ->from(array('s'    => 'store'), $get)
    ->joinleft(array('i'    => WAREHOUSE_DB.'.imei'), 's.id = i.store_id', array())
    ->joinleft(array('g'   => WAREHOUSE_DB.'.good'), 'i.good_id = g.id', array())

    //->joinleft(array('w'   => WAREHOUSE_DB.'.warehouse'), 'w.id = d.warehouse_id', array())
    //->joinLeft(array('gcc' => WAREHOUSE_DB.'.good_color_combined'),'g.id = gcc.good_id',array())

    ->join(array('gc' => WAREHOUSE_DB.'.good_color'),'i.good_color = gc.id',array())

    ->where('i.old_data IS NULL')
    ->where('i.imei_sn NOT IN (select imei from hr.timing_sale)')
    ->where('s.id =?',$params['s_id'])
    ->group(array('i.good_id','i.good_color'))
    ->order('g.name ASC');

    if(isset($params['status']) && $params['status'] == 1){
        $select->where('i.activated_date IS null');
        $select->where('i.imei_sn NOT IN (select imei from timing_sale)'); // Check timing_sale
    }

    if(isset($params['status']) and $params['status'] == 2){
        $select->where('i.imei_sn NOT IN (select imei from timing_sale)'); // Check timing_sale
    }

    // echo $select; die;

    $result = $db->fetchAll($select);
    return $result;
}

// OPPO General Target Report
function OppoGeneralReport($params){
    set_time_limit(0);
    ini_set('memory_limit', '-1');
    error_reporting(~E_ALL);
    ini_set("display_error", '0');

    
    $now_date = date('Y-m-d');
    $last_date_of_month = date("Y-m-t", strtotime($now_date));

    if(date('d') == '1'){
        $yesterday = date("Y-m-t", strtotime("last day of previous month"));
        $first_day_of_month = date("Y-m-01", strtotime("first day of previous month"));
        $last_date_of_month = date("Y-m-t", strtotime("last day of previous month"));

    }else{
        $yesterday = date('Y-m-d',strtotime("-1 days"));
        $first_day_of_month = date('Y-m-01');
        $last_date_of_month = date("Y-m-t", strtotime($now_date));
    }

    $db = Zend_Registry::get('db');

    $get =array(
        'area_name'         => 'a.name',
        'general_target'    => 'oat.target',
        'total_sale'        => new Zend_Db_Expr("COUNT(CASE WHEN t.created_at >='".$first_day_of_month." 00:00:00' and t.created_at <='".$yesterday." 23:59:59' THEN t.id END)"),
        'yesterday_sale'    => new Zend_Db_Expr("COUNT(CASE WHEN t.created_at >='".$yesterday." 00:00:00' and t.created_at <='".$yesterday." 23:59:59' THEN t.id END)")
    );

    $select = $db->select()
    ->from(array('a' => 'area'),$get);

    $select->join(array('oat' => 'oppo_area_target'),'a.id = oat.area_id and oat.from_date = "'.$first_day_of_month.'" and oat.to_date = "'.$last_date_of_month.'"',array());
    $select->joinLeft(array('rm' => 'regional_market'),'rm.area_id = a.id',array());
    $select->joinLeft(array('t' => 'timing'),'t.store_area = rm.id',array());
    $select->joinLeft(array('ts' => 'timing_sale'), 't.id = ts.timing_id');

    $select->where('a.id != 120');
    $select->where('t.id = ts.timing_id');
    $select->group('a.id');

    $result = $db->fetchAll($select);
    return $result;
}
// OPPO Model Report
function OppoModelReport($params){
    set_time_limit(0);
    ini_set('memory_limit', '-1');
    error_reporting(~E_ALL);
    ini_set("display_error", '0');

    $now_date = date('Y-m-d');
    $last_date_of_month = date("Y-m-t", strtotime($now_date));

    if(date('d') == '1'){
        $yesterday = date("Y-m-t", strtotime("last day of previous month"));
        $first_day_of_month = date("Y-m-01", strtotime("first day of previous month"));
        $last_date_of_month = date("Y-m-t", strtotime("last day of previous month"));

    }else{
        $yesterday = date('Y-m-d',strtotime("-1 days"));
        $first_day_of_month = date('Y-m-01');
        $last_date_of_month = date("Y-m-t", strtotime($now_date));
    }

    $db = Zend_Registry::get('db');

    $get = array(
        'model_name' => 'g.name',
        'total_sale' => new Zend_Db_Expr('COUNT(ts.id)'),
	    'brand_name' => 'b.name'
    );

    $select = $db->select()
        ->from(array('t' => 'timing'),$get);
    $select->join(array('ts' => 'timing_sale'),'ts.timing_id = t.id',array());
    $select->joinLeft(array('rm' => 'regional_market'),'rm.id = t.store_area',array());
    $select->joinLeft(array('g' => WAREHOUSE_DB.'.good'),'ts.product_id = g.id',array());
    $select->joinLeft(array('b' => WAREHOUSE_DB.'.brand'),'g.brand_id = b.id',array());

    $select->where('t.created_at >= ?',$yesterday.' 00:00:00');
    $select->where('t.created_at <= ?',$yesterday.' 23:59:59');
    $select->where('rm.area_id != 120');
    $select->where('t.id = ts.timing_id');
    $select->where('b.id =?',4);
    $select->group('g.name');

    $result = $db->fetchAll($select);

    return $result;

}

// OPPO Hero Target Report
function OppoHeroReport($params){
    set_time_limit(0);
    ini_set('memory_limit', '-1');
    error_reporting(~E_ALL);
    ini_set("display_error", '0');

    $now_date = date('Y-m-d');
    $last_date_of_month = date("Y-m-t", strtotime($now_date));

    if(date('d') == '1'){
        $yesterday = date("Y-m-t", strtotime("last day of previous month"));
        $first_day_of_month = date("Y-m-01", strtotime("first day of previous month"));
        $last_date_of_month = date("Y-m-t", strtotime("last day of previous month"));

    }else{
        $yesterday = date('Y-m-d',strtotime("-1 days"));
        $first_day_of_month = date('Y-m-01');
        $last_date_of_month = date("Y-m-t", strtotime($now_date));
    }

    $db = Zend_Registry::get('db');

    $get =array(
        'area_name'         => 'a.name',
        'hero_target'    => 'oat.target_hero',
        'total_hero_sale'        => new Zend_Db_Expr("COUNT(CASE WHEN t.created_at >='".$first_day_of_month." 00:00:00' and t.created_at <='".$yesterday." 23:59:59' THEN t.id END)"),
        'yesterday_hero_sale'    => new Zend_Db_Expr("COUNT(CASE WHEN t.created_at >='".$yesterday." 00:00:00' and t.created_at <='".$yesterday." 23:59:59' THEN t.id END)")
    );

    $select = $db->select()
    ->from(array('a' => 'area'),$get);

    $select->join(array('oat' => 'oppo_area_target'),'a.id = oat.area_id and oat.from_date = "'.$first_day_of_month.'" and oat.to_date = "'.$last_date_of_month.'"',array());
    $select->joinLeft(array('rm' => 'regional_market'),'rm.area_id = a.id',array());
    $select->joinLeft(array('t' => 'timing'),'t.store_area = rm.id',array());
    $select->joinLeft(array('ts' => 'timing_sale'),'ts.timing_id = t.id',array());

    $select->where('ts.is_hero = 1');
    $select->where('ts.timing_id = t.id');
    $select->where('a.id != 120');
    $select->group('a.id');
    // echo $select; die;
    $result = $db->fetchAll($select);
    return $result;
}


// OPPO Area Model Report
function OppoAreaModelReport($params){
    set_time_limit(0);
    ini_set('memory_limit', '-1');
    error_reporting(~E_ALL);
    ini_set("display_error", '0');

    $now_date = date('Y-m-d');
    $last_date_of_month = date("Y-m-t", strtotime($now_date));

    if(date('d') == '1'){
        $yesterday = date("Y-m-t", strtotime("last day of previous month"));
        $first_day_of_month = date("Y-m-01", strtotime("first day of previous month"));
        $last_date_of_month = date("Y-m-t", strtotime("last day of previous month"));

    }else{
        $yesterday = date('Y-m-d',strtotime("-1 days"));
        $first_day_of_month = date('Y-m-01');
        $last_date_of_month = date("Y-m-t", strtotime($now_date));
    }

    $db = Zend_Registry::get('db');

    $get = array(
        'area_name' => 'a.name',
        'model_name' => 'g.name',
        'area_total_sale' => new Zend_Db_Expr('COUNT(ts.id)'),
	    'brand_name' => 'b.name'
    );

    $select = $db->select()
        ->from(array('t' => 'timing'),$get);
    $select->join(array('ts' => 'timing_sale'),'ts.timing_id = t.id',array());
    $select->joinLeft(array('rm' => 'regional_market'),'rm.id = t.store_area',array());
    $select->joinLeft(array('a' => 'area'),'rm.area_id = a.id',array());
    $select->joinLeft(array('g' => WAREHOUSE_DB.'.good'),'ts.product_id = g.id',array());
    $select->joinLeft(array('b' => WAREHOUSE_DB.'.brand'),'g.brand_id = b.id',array());

    $select->where('t.created_at >= ?',$yesterday.' 00:00:00');
    $select->where('t.created_at <= ?',$yesterday.' 23:59:59');
    $select->where('rm.area_id != 120');
    $select->where('t.id = ts.timing_id');
    $select->where('b.id =?',4);
    $select->group(array('a.name', 'g.id'));
    $result = $db->fetchAll($select);

    return $result;

}



// OPPO Score Report
function OppoScoreReport($params){
    set_time_limit(0);
    ini_set('memory_limit', '-1');
    error_reporting(~E_ALL);
    ini_set("display_error", '0');

    $now_date = date('Y-m-d');
    $last_date_of_month = date("Y-m-t", strtotime($now_date));

    if(date('d') == '1'){
        $yesterday = date("Y-m-t", strtotime("last day of previous month"));
        $first_day_of_month = date("Y-m-01", strtotime("first day of previous month"));
        $last_date_of_month = date("Y-m-t", strtotime("last day of previous month"));

    }else{
        $yesterday = date('Y-m-d',strtotime("-1 days"));
        $first_day_of_month = date('Y-m-01');
        $last_date_of_month = date("Y-m-t", strtotime($now_date));
    }

    $db = Zend_Registry::get('db');

    $sub_select = $db->select()->from(array('t' => 'timing'),array('SUM(ts.sales_price)'))
    ->join(array('ts' => 'timing_sale'),'t.id = ts.timing_id',array())
    ->joinLeft(array('rm' => 'regional_market'),'rm.id = t.store_area',array())

    ->where('t.created_at >= ?',$yesterday.' 00:00:00')
    ->where('t.created_at <= ?',$yesterday.' 23:59:59')
    ->where('rm.staff_id = ap.staff_id');

    $sub_select2 = $db->select()->from(array('t' => 'timing'),array('SUM(ts.sales_price)'))
    ->join(array('ts' => 'timing_sale'),'t.id = ts.timing_id',array())
    ->joinLeft(array('rm' => 'regional_market'),'rm.id = t.store_area',array())

    ->where('t.created_at >= ?',$first_day_of_month.' 00:00:00')
    ->where('t.created_at <= ?',$yesterday.' 23:59:59')
    ->where('rm.staff_id = ap.staff_id');

    $sub_select3 = $db->select()->from(array('t' => 'timing'),array('SUM(ts.sales_price)'))
    ->join(array('ts' => 'timing_sale'),'t.id = ts.timing_id',array())
    ->joinLeft(array('rm' => 'regional_market'),'rm.id = t.store_area',array())

    ->where('t.created_at >= ?',$yesterday.' 00:00:00')
    ->where('t.created_at <= ?',$yesterday.' 23:59:59');

    $sub_select4 = $db->select()->from(array('t' => 'timing'),array('SUM(ts.sales_price)'))
    ->join(array('ts' => 'timing_sale'),'t.id = ts.timing_id',array())
    ->joinLeft(array('rm' => 'regional_market'),'rm.id = t.store_area',array())

    ->where('t.created_at >= ?',$first_day_of_month.' 00:00:00')
    ->where('t.created_at <= ?',$yesterday.' 23:59:59');

    $get = array(
        'area_name' => 'ap.area_name',
        'score_point' => 'ap.point',
        'area_price' => new Zend_Db_Expr("(".$sub_select.")"),
        'national_price' => new Zend_Db_Expr("(".$sub_select2.")"),
        'total_daily_price' => new Zend_Db_Expr("(".$sub_select3.")"),
        'total_monthly_price' => new Zend_Db_Expr("(".$sub_select4.")")
    );

    $select = $db->select()->from(array('ap' => 'area_point'),$get);
    // $select ->where('a.id != 120');
    $select->group('ap.area_name');

    // echo $select; die;

    $result = $db->fetchAll($select);
    return $result;

}

}