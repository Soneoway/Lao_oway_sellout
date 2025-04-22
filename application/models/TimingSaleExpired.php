<?php

class Application_Model_TimingSaleExpired extends Zend_Db_Table_Abstract
{
    protected $_name = 'timing_sale_expired';

    public function getAllImeiExpired($staff_id)
    {

        if (isset($staff_id) and $staff_id)
        {
            $db = Zend_Registry::get('db');
            $select = $db->select()
            ->from(array('t' => 'timing'), array('t.store', 't.from'))
            ->join(array('ts' => $this->_name), 't.id=ts.timing_id', array('ts.id', 'ts.imei', 'ts.product_id', 'ts.model_id'))
            ->joinLeft(array('tss' => 'timing_sale'), 'ts.imei=tss.imei', array())
            ->joinLeft(array('i' => WAREHOUSE_DB.'.imei'), 'ts.imei=i.imei_sn', 
                array(
                    'i.activated_date',
                    'can_reimport' => new Zend_Db_Expr('CASE WHEN (i.activated_date IS NOT NULL AND DATEDIFF(i.activated_date, now()) <= 15 AND DATEDIFF(i.activated_date,  t.`from`) >= -3 AND tss.imei IS NULL) THEN 1 ELSE 0 END'),
                    'limit_expire'  => new Zend_Db_Expr('CASE WHEN (DATEDIFF(i.activated_date, now()) > 15 AND i.activated_date IS NOT NULL AND tss.imei IS NULL) THEN 1 ELSE 0 END'),
                    'limit_expire_activated'  => new Zend_Db_Expr('CASE WHEN (DATEDIFF(i.activated_date,  t.`from`) < -3 AND i.activated_date IS NOT NULL AND tss.imei IS NULL) THEN 1 ELSE 0 END'),
                    'already_timing' => new Zend_Db_Expr('CASE WHEN (tss.imei IS NOT NULL) THEN 1 ELSE 0 END'),
                ))
            ->joinLeft(array('s' => 'staff'),'t.staff_id = s.id', array('store_id' => 's.id'))
            ->joinLeft(array('st' => 'store'),'t.store = st.id', array('store_name' => 'st.name'))
            ->join(array('g' => WAREHOUSE_DB . '.' . 'good'), 'ts.product_id=g.id', array('good_name' =>'g.name'))
            ->join(array('gs' => WAREHOUSE_DB . '.' . 'good_color'), 'ts.model_id = gs.id', array('good_color' =>'gs.name'));

            $select->where('staff_id = ? ', $staff_id);
            $select->where ('MONTH(t.`from`) = MONTH(CURDATE())', null);
            $select->group('ts.imei');

            $result = $db->fetchAll($select);
            return $result;
        }
        else
        {
            return - 1;
        }


    }

    function fetchPagination($page, $limit, &$total, $params){
        $db = Zend_Registry::get('db');

        $select = $db->select()
        // ->distinct()
        ->from(array('st' => 'store'),array(new Zend_Db_Expr('SQL_CALC_FOUND_ROWS ts.id'), 't.store', 't.from', 'st.name', 'st.district'))

        // ->from(array('t' => 'timing'), array(new Zend_Db_Expr('SQL_CALC_FOUND_ROWS ts.id'), 't.store', 't.from'))

        ->join(array('t' => 'timing'),'t.store = st.id',array())
        ->join(array('ts' => 'timing_sale'), 't.id = ts.timing_id AND t.approved_at IS NOT NULL AND t.approved_at <> 0', array('ts.imei', 'ts.product_id', 'ts.model_id'))
        ->joinLeft(array('c' => 'customer'), 'c.id = ts.customer_id', array('customer_name' => 'c.name','customer_phone' => 'c.phone_number'))
        ->joinLeft(array('i' => WAREHOUSE_DB.'.imei'), 'ts.imei = i.imei_sn', array('i.activated_date'))
        ->joinLeft(array('d1' => WAREHOUSE_DB.'.distributor'), 'st.d_id = d1.id'            , array())
        ->joinLeft(array('d2' => WAREHOUSE_DB.'.distributor'), 'i.distributor_id = d2.id'   , array())

        // ->join(array('s' => 'staff'), 's.id=t.staff_id', array('s.firstname', 's.lastname', 's.email', 's.phone_number'))

        ->joinLeft(array('rm5' => 'regional_market'),'rm5.id = d2.region',array())
        ->joinLeft(array('a2' => 'area'),'a2.id = rm5.area_id',array())
        ->joinLeft(array('g' => WAREHOUSE_DB.'.good'),'g.id = i.good_id',array())
        ->joinLeft(array('b' => WAREHOUSE_DB.'.brand'),'b.id = g.brand_id',array('brand_name' => 'b.name'))

        ->where('ts.imei NOT IN (SELECT imei FROM timing_control)')
        ->group('i.imei_sn');



        if (isset($params['activated']) && $params['activated'])
            $select->where('i.activated_date IS NOT NULL', 1);

        if (isset($params['not_activated']) && $params['not_activated'])
            $select->where('i.activated_date IS NULL', 1);
        
        $select->order(array('t.from DESC', 't.staff_id', 'st.id'));

        if (isset($params['staff_id']) and $params['staff_id']) {
            $select->where('t.staff_id = ?', intval($params['staff_id']));

        } elseif (isset($params['sale']) and $params['sale']) {
            $select->where('t.sales_id =?', $params['sale']);

            // follow STORE-STAFF concept
            // $select ->join(array('ssl' => 'store_staff_log'), 't.store = ssl.store_id', array());
            // $log_where = $this->getAdapter()->quoteInto('ssl.staff_id = ?', intval($params['sale'])).
            // " AND " . $this->getAdapter()->quoteInto('ssl.is_leader = ?', 1).
            // " AND " . $this->getAdapter()->quoteInto('DATE(t.from) >= FROM_UNIXTIME(ssl.joined_at, \'%Y-%m-%d\')', 1).
            // " AND (".
            // $this->getAdapter()->quoteInto('DATE(t.from) < FROM_UNIXTIME(ssl.released_at, \'%Y-%m-%d\')', 1).
            // " OR " . $this->getAdapter()->quoteInto('ssl.released_at IS NULL', 1).
            // " OR " . $this->getAdapter()->quoteInto('ssl.released_at = 0', 1).
            // " ) ";

            // $select->where($log_where);


        } elseif (isset($params['leader']) and $params['leader']) {
            // follow STORE-STAFF concept
            // lấy store thuộc region mà nó quản lý
            $select
            ->joinLeft(array('lg' => 'store_leader_log'), 't.store = lg.store_id', array())
            ->joinLeft(array('ssl' => 'store_staff_log'), 't.store = ssl.store_id', array());

            // quyền leader
            $log_where = " ( " . $this->getAdapter()->quoteInto('lg.staff_id = ?', intval($params['leader'])).
            " AND " . $this->getAdapter()->quoteInto('DATE(t.from) >= FROM_UNIXTIME(lg.joined_at, \'%Y-%m-%d\')', 1).
            " AND (".
            $this->getAdapter()->quoteInto('DATE(t.from) < FROM_UNIXTIME(lg.released_at, \'%Y-%m-%d\')', 1).
            " OR " . $this->getAdapter()->quoteInto('lg.released_at IS NULL', 1).
            " OR " . $this->getAdapter()->quoteInto('lg.released_at = 0', 1).

            " )
        ) ";
            // quyền sales
        $log_where .= " OR " .

        " ( " . $this->getAdapter()->quoteInto('ssl.staff_id = ?', intval($params['leader'])).
        " AND " . $this->getAdapter()->quoteInto('ssl.is_leader = ?', 1).
        " AND " . $this->getAdapter()->quoteInto('DATE(t.from) >= FROM_UNIXTIME(ssl.joined_at, \'%Y-%m-%d\')', 1).
        " AND (".
        $this->getAdapter()->quoteInto('DATE(t.from) < FROM_UNIXTIME(ssl.released_at, \'%Y-%m-%d\')', 1).
        " OR " . $this->getAdapter()->quoteInto('ssl.released_at IS NULL', 1).
        " OR " . $this->getAdapter()->quoteInto('ssl.released_at = 0', 1).
        " )
    )";

    $select->where($log_where);

} elseif (isset($params['asm']) and $params['asm']) {
    // $select->where('t.asm_id =?', $params['asm']);

            $QAsm = new Application_Model_Asm();
            $list_regions = $QAsm->get_cache($params['asm']);
            $list_regions = isset($list_regions['province']) && is_array($list_regions['province']) ? $list_regions['province'] : array();

            if (count($list_regions)) {
                $select->where('st.province_id IN (?)', $list_regions); // lọc staff thuộc regional_market trên
            }

            else
                $select->where('1=0', 1);
}

if(isset($params['rd']) && $params['rd']) {
    $QArea = new Application_Model_Area();
    $areas = $QArea->getAreaByAsmTable($params['rd']);

    $data = array();
    foreach($areas as $item) {
        $data[] = $item['id'];
    }

    $rgm_area = implode(',', $data);

    // $select->where('a.id IN ('.$rgm_area.')');
    $select->where('a2.id IN ('.$rgm_area.')');

}

if (isset($params['email']) && $params['email'])
    $select->where('s.email LIKE ?', str_replace(EMAIL_SUFFIX, '', $params['email']).EMAIL_SUFFIX);

if (isset($params['imei']) && $params['imei'])
    $select->where('ts.imei = ?', $params['imei']);

if(isset($params['pcm']) && $params['pcm'] > 0){
    $select->where('t.pcm_id =?',$params['pcm']);
}

if(isset($params['pcdb']) && $params['pcdb'] > 0){
    $select->where('t.created_by =?',$params['pcdb']);
}

if (isset($params['area_id']) && $params['area_id']) {
    $QRegionalMarket = new Application_Model_RegionalMarket();
    $list_regions = $QRegionalMarket->get_district_by_area_cache($params['area_id']);
    $list_regions = is_array($list_regions) ? $list_regions : array();

    if (count($list_regions))
        $select->where('st.district IN (?)', $list_regions);
    else
        $select->where('1=0', 1);
}

if (isset($params['regional_market']) && $params['regional_market']) {
    $QRegionalMarket = new Application_Model_RegionalMarket();
    $list_regions = $QRegionalMarket->get_district_by_province_cache($params['regional_market']);
    $list_regions = is_array($list_regions) ? $list_regions : array();

    $list_regions_arr = array();

    foreach ($list_regions as $key => $value)
        $list_regions_arr[] = $key;

    if (count($list_regions_arr))
        $select->where('st.district IN (?)', $list_regions_arr);
    else
        $select->where('1=0', 1);
}

if (isset($params['district']) && $params['district']) {
    $select->where('st.district = ?', $params['district']);
}

if (isset($params['from']) && $params['from'] && DateTime::createFromFormat('d/m/Y', $params['from']))
    $select->where('t.from >= ?', DateTime::createFromFormat('d/m/Y', $params['from'])->format('Y-m-d 00:00:00'));

if (isset($params['to']) && $params['to'] && DateTime::createFromFormat('d/m/Y', $params['to']))
    $select->where('t.from <= ?', DateTime::createFromFormat('d/m/Y', $params['to'])->format('Y-m-d 23:59:59'));


if ($limit)
    $select->limitPage($page, $limit);

$result = $db->fetchAll($select);

if ($limit)
    $total = $db->fetchOne("select FOUND_ROWS()");
return $result;
}

function fetchKpiPagination($page, $limit, &$total, $params){
    $db = Zend_Registry::get('db');

    $select = $db->select()
    ->distinct()
    ->from(array('t' => 'timing'), array(new Zend_Db_Expr('SQL_CALC_FOUND_ROWS ts.id'), 't.store', 't.from'))
    ->join(array('ts' => 'timing_sale'), 't.id=ts.timing_id', array('ts.imei', 'ts.product_id', 'ts.model_id'))
    ->joinLeft(array('c' => 'customer'), 'c.id=ts.customer_id', array('customer_name' => 'c.name','customer_phone' => 'c.phone_number'))
    ->joinLeft(array('i' => WAREHOUSE_DB.'.imei'), 'ts.imei=i.imei_sn', array('i.activated_date'))
            // ->join(array('s' => 'staff'), 's.id=t.staff_id', array('s.firstname', 's.lastname', 's.email', 's.phone_number'))
    ->join(array('st' => 'store'), 'st.id=t.store', array('st.name', 'st.district'));

    if (isset($params['activated']) && $params['activated'])
        $select->where('ts.turn = ?', 1);

    if (isset($params['not_activated']) && $params['not_activated'])
        $select->where('ts.turn = 0', 1);

    $select->order(array('t.from DESC', 't.staff_id', 'st.id'));

    if (isset($params['staff_id']) and $params['staff_id']) {
        $select->where('t.staff_id = ?', intval($params['staff_id']));

    } elseif (isset($params['sale']) and $params['sale']) {
            // follow STORE-STAFF concept
        $select ->join(array('ssl' => 'store_staff_log'), 't.store = ssl.store_id', array());
        $log_where = $this->getAdapter()->quoteInto('ssl.staff_id = ?', intval($params['sale'])).
        " AND " . $this->getAdapter()->quoteInto('ssl.is_leader = ?', 1).
        " AND " . $this->getAdapter()->quoteInto('DATE(t.from) >= FROM_UNIXTIME(ssl.joined_at, \'%Y-%m-%d\')', 1).
        " AND (".
        $this->getAdapter()->quoteInto('DATE(t.from) < FROM_UNIXTIME(ssl.released_at, \'%Y-%m-%d\')', 1).
        " OR " . $this->getAdapter()->quoteInto('ssl.released_at IS NULL', 1).
        " OR " . $this->getAdapter()->quoteInto('ssl.released_at = 0', 1).
        " ) ";

        $select->where($log_where);

    } elseif (isset($params['leader']) and $params['leader']) {
            // follow STORE-STAFF concept
            // lấy store thuộc region mà nó quản lý
        $select
        ->joinLeft(array('lg' => 'store_leader_log'), 't.store = lg.store_id', array())
        ->joinLeft(array('ssl' => 'store_staff_log'), 't.store = ssl.store_id', array());

            // quyền leader
        $log_where = " ( " . $this->getAdapter()->quoteInto('lg.staff_id = ?', intval($params['leader'])).
        " AND " . $this->getAdapter()->quoteInto('DATE(t.from) >= FROM_UNIXTIME(lg.joined_at, \'%Y-%m-%d\')', 1).
        " AND (".
        $this->getAdapter()->quoteInto('DATE(t.from) < FROM_UNIXTIME(lg.released_at, \'%Y-%m-%d\')', 1).
        " OR " . $this->getAdapter()->quoteInto('lg.released_at IS NULL', 1).
        " OR " . $this->getAdapter()->quoteInto('lg.released_at = 0', 1).

        " )
    ) ";
            // quyền sales
    $log_where .= " OR " .

    " ( " . $this->getAdapter()->quoteInto('ssl.staff_id = ?', intval($params['leader'])).
    " AND " . $this->getAdapter()->quoteInto('ssl.is_leader = ?', 1).
    " AND " . $this->getAdapter()->quoteInto('DATE(t.from) >= FROM_UNIXTIME(ssl.joined_at, \'%Y-%m-%d\')', 1).
    " AND (".
    $this->getAdapter()->quoteInto('DATE(t.from) < FROM_UNIXTIME(ssl.released_at, \'%Y-%m-%d\')', 1).
    " OR " . $this->getAdapter()->quoteInto('ssl.released_at IS NULL', 1).
    " OR " . $this->getAdapter()->quoteInto('ssl.released_at = 0', 1).
    " )
)";

$select->where($log_where);

} elseif (isset($params['asm']) and $params['asm']) {
    $QAsm = new Application_Model_Asm();
    $list_regions = $QAsm->get_cache($params['asm']);
    $list_regions = isset($list_regions['district']) && is_array($list_regions['district']) ? $list_regions['district'] : array();

    if (count($list_regions)) {
                $select->where('st.district IN (?)', $list_regions); // lọc staff thuộc regional_market trên
            }
            else
                $select->where('1=0', 1);
        }

        if (isset($params['email']) && $params['email'])
            $select->where('s.email LIKE ?', str_replace(EMAIL_SUFFIX, '', $params['email']).EMAIL_SUFFIX);

        if (isset($params['imei']) && $params['imei'])
            $select->where('ts.imei = ?', $params['imei']);

        if (isset($params['area_id']) && $params['area_id']) {
            $QRegionalMarket = new Application_Model_RegionalMarket();
            $list_regions = $QRegionalMarket->get_district_by_area_cache($params['area_id']);
            $list_regions = is_array($list_regions) ? $list_regions : array();

            if (count($list_regions))
                $select->where('st.district IN (?)', $list_regions);
            else
                $select->where('1=0', 1);
        }

        if (isset($params['regional_market']) && $params['regional_market']) {
            $QRegionalMarket = new Application_Model_RegionalMarket();
            $list_regions = $QRegionalMarket->get_district_by_province_cache($params['regional_market']);
            $list_regions = is_array($list_regions) ? $list_regions : array();

            $list_regions_arr = array();

            foreach ($list_regions as $key => $value)
                $list_regions_arr[] = $key;

            if (count($list_regions_arr))
                $select->where('st.district IN (?)', $list_regions_arr);
            else
                $select->where('1=0', 1);
        }

        if (isset($params['district']) && $params['district']) {
            $select->where('st.district = ?', $params['district']);
        }

        if (isset($params['from']) && $params['from'] && DateTime::createFromFormat('d/m/Y', $params['from']))
            $select->where('t.from >= ?', DateTime::createFromFormat('d/m/Y', $params['from'])->format('Y-m-d 00:00:00'));

        if (isset($params['to']) && $params['to'] && DateTime::createFromFormat('d/m/Y', $params['to']))
            $select->where('t.from <= ?', DateTime::createFromFormat('d/m/Y', $params['to'])->format('Y-m-d 23:59:59'));

        if ($limit)
            $select->limitPage($page, $limit);

        $result = $db->fetchAll($select);

        if ($limit)
            $total = $db->fetchOne("select FOUND_ROWS()");
        return $result;
    }
}
