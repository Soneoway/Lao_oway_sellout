<?php
set_time_limit(0);

$page       = $this->getRequest()->getParam('page', 1);
$sort       = $this->getRequest()->getParam('sort', 'product_count');
$desc       = $this->getRequest()->getParam('desc', 1);
$from       = $this->getRequest()->getParam('from', date('01/m/Y') );
$to         = $this->getRequest()->getParam('to', date('d/m/Y'));
$id         = $this->getRequest()->getParam('id');
$name       = $this->getRequest()->getParam('name');
$area_id    = $this->getRequest()->getParam('area_id');
$regional_market   = $this->getRequest()->getParam('regional_market');
$district   = $this->getRequest()->getParam('district');
$store      = $this->getRequest()->getParam('store');
$has_pg     = $this->getRequest()->getParam('has_pg');
$sales_from = $this->getRequest()->getParam('sales_from');
$sales_to   = $this->getRequest()->getParam('sales_to');
$org        = $this->getRequest()->getParam('org');
$market_type= $this->getRequest()->getParam('market_type');
$market_name= $this->getRequest()->getParam('market_name');
$it_junction= $this->getRequest()->getParam('it_junction');
$del        = $this->getRequest()->getParam('del');
$export     = $this->getRequest()->getParam('export');

$staff_name = $this->getRequest()->getParam('staff_name');
$staff_code = $this->getRequest()->getParam('staff_code');

$shop_id    = $this->getRequest()->getParam('shop_id');
$shop_code  = $this->getRequest()->getParam('shop_code');
$brand_id   = $this->getRequest()->getParam('brand_id');
$d_tag      = $this->getRequest()->getParam('d_tag');

$selected_product = $this->getRequest()->getParam('selected_product', array(403));

$store_level     = $this->getRequest()->getParam('store_level');

$good_id = $this->getRequest()->getParam('good_id'); // Search by product name


$limit = LIMITATION;
$total = $total2 = 0;

$params = array(
    'page'       => $page,
    'sort'       => $sort,
    'desc'       => $desc,
    'from'       => $from,
    'to'         => $to,
    'id'         => $id,
    'name'       => $name,
    'area_id'    => $area_id,
    'regional_market' => $regional_market,
    'district'   => $district,
    'store'      => $store,
    'has_pg'     => $has_pg,
    'sales_from' => $sales_from,
    'sales_to'   => $sales_to,
    'org'        => $org,
    'market_type'=> $market_type,
    'market_name'=> $market_name,
    'it_junction'=> $it_junction,
    'del'        => $del,
    'export'     => $export,
    'staff_name' => $staff_name,
    'staff_code' => $staff_code,
    'selected_product' => $selected_product,
    'shop_id'    => $shop_id,
    'shop_code'  => $shop_code,
    'good_id'  => $good_id,
    'store_level'=> $store_level,
    'brand'  => $brand_id,
    'd_tag' => $d_tag,

);

$QGood = new Application_Model_Good();
$goods_list = $QGood->getProduct($params);
$this->view->goods = $goods_list;

$brands = $QGood->get_brand();
$this->view->brands = $brands;

// get User Info
$userStorage = Zend_Auth::getInstance()->getStorage()->read();
$group_id = $userStorage->group_id;
if (!$userStorage || !isset($userStorage->id)) $this->_redirect(HOST);

// Group Permission
if (in_array($group_id, My_Staff_Group::$allow_in_area_view) && !My_Staff_Permission_Area::view_all($userStorage->id)) {
    $params['asm'] = $userStorage->id;

    $QAsm = new Application_Model_Asm();
    $result_area = $QAsm->get_cache($userStorage->id);
    $params['area_permission'] = $result_area['area'];
    
} elseif ( in_array($group_id, array(SALES_ID,LEADER_ID,PGPB_ID) ) ) {

    $QStaff = new Application_Model_Staff();
    $result_area = $QStaff->getStaffArea($userStorage->id);
    $params['area_permission'] = $result_area['staff_area_id'];

} 

if ($userStorage->group_id == AM_ID)
    $params['am'] = $userStorage->id;

//check sale permission
if ($userStorage->group_id == SALES_ID)
    $params['sale_id'] = $userStorage->id;

//check pcm permission
if ($userStorage->group_id == PCM_ID)
    $params['pcm_id'] = $userStorage->id;

//check sale leader permission
if ($userStorage->group_id == LEADER_ID)
    $params['leader_id'] = $userStorage->id;

//check brandshop manager permission
if ($userStorage->group_id == BM_ID)
    $params['bm_id'] = $userStorage->id;

//check admin brandshop permission
if ($userStorage->group_id == 39)
    $params['admin_bs'] = $userStorage->id;

// Condition Here for Performance
if ( isset($export) && $export == 5 ) {
    // Export Brand Shop Manager
    $this->_exportExcelComBM($params);
}

if ( isset($export) && $export == 6 ) {
    // Export Brandshop Store [List]

    $selected_product_name = "";
    $cnt_selected = count($params['selected_product']);
    for ($i=0;$i<$cnt_selected;$i++) {

        if ($cnt_selected > 3) {
            $selected_product_name = $cnt_selected." Selected Product";
        } else {
            $selected_product_name = $selected_product_name.$goods_list[ $params['selected_product'][$i] ]['name'];
            if ($i != $cnt_selected-1) { $selected_product_name = $selected_product_name.", "; }
        }

    }

    $params['selected_product_name'] = $selected_product_name;
    $this->_exportExcelBsStoreList($params);
}

if ( isset($export) && $export == 7 ) {
    // Export Brandshop Store [Stock Scan]
    $this->_exportExcelBsStockScan($params);
}

// Data for Filter 
$QArea = new Application_Model_Area();
//$areas = $QArea->fetchAll(null, 'name');

if ( in_array($userStorage->group_id, array(RM_ID, RMSTANDBY_ID)) ) {
    $areas = $QArea->getAreaByAsmTable($userStorage->id);

    $data = array();
    foreach($areas as $item) {
        $data[] = $item['id'];
    }

    $params['rgm_area'] = implode(',', $data);

} else {
    $areas = $QArea->fetchAll(null, 'name');
}

$QMarketType = new Application_Model_MarketType();
$market_type_list = $QMarketType->fetchAll(null, 'name');

$QOrg = new Application_Model_Org();
$org = $QOrg->fetchAll(null, 'org_name');

$QRegionalMarket = new Application_Model_RegionalMarket();

if ($area_id) {
    if (is_array($area_id) && count($area_id))
        $where = $QRegionalMarket->getAdapter()->quoteInto('area_id IN (?)', $area_id);
    else
        $where = $QRegionalMarket->getAdapter()->quoteInto('area_id = ?', $area_id);

    $this->view->regional_markets = $QRegionalMarket->fetchAll($where, 'name');
}

if ($regional_market) {
    if (is_array($regional_market) && count($regional_market))
        $where = $QRegionalMarket->getAdapter()->quoteInto('parent IN (?)', $regional_market);
    else
        $where = $QRegionalMarket->getAdapter()->quoteInto('parent = ?', $regional_market);

    $this->view->districts = $QRegionalMarket->fetchAll($where, 'name');
}

if ($district) {
    $QStore = new Application_Model_Store();

    if (is_array($regional_market) && count($regional_market))
        $where = $QStore->getAdapter()->quoteInto('district IN (?)', $district);
    else
        $where = $QStore->getAdapter()->quoteInto('district = ?', $district);

    $this->view->stores = $QStore->fetchAll($where, 'name');
}

if ($market_type) {
    $QMarketName = new Application_Model_MarketName();
         
    $where = array();   
    $where[] = $QMarketName->getAdapter()->quoteInto('market_type_id IN (?)', $market_type);

    if ( isset($params['area_permission']) && !empty($params['area_permission']) ) {
        $where[] = $QMarketName->getAdapter()->quoteInto('area_id IN (?)', $params['area_permission']);
    }

    $this->view->market_name = $QMarketName->fetchAll($where, 'name');
}

$store_level = array(
    '0'  => array('id' => 'S', 'name' => 'S'),
    '1'  => array('id' => 'A', 'name' => 'A'),
    '2'  => array('id' => 'B', 'name' => 'B'),
    '3'  => array('id' => 'C', 'name' => 'C'),
);

$this->view->store_level_list = $store_level;

$QTiming = new Application_Model_Timing();

// Check First Time Not Show Data
if (!empty($_GET)) {
    unset($params['total_sellout']);
    $sales = $QTiming->analytic_store($page,$limit,$total,$params);
    $imei_report = $QTiming->exprotimeistore($params);
    //print_r($sales);

    $params['total_sellout'] = 0;
    $total_quantity = $QTiming->analytic_store(null,null,$total2,$params);
    //print_r($total_quantity);
    $this->view->total_quantity = $total_quantity;
    
} 

// Export Excel
if ( isset($export) && $export ) {
    if ($export == 2) {
        // Export Store
        $this->_exportExcelOrgDealer($sales,$params);
    }
    if ($export == 3) {
        // Export Store by Market Type
        $this->_exportExcelByMarketType($params);
    }
    
    if(isset($export) && $export == 8){
        $this->_exprotbystoreImei($imei_report,$params);
    }
    if ($export == 4) {
        // Export Store [List]

        $selected_product_name = "";
        $cnt_selected = count($params['selected_product']);
        for ($i=0;$i<$cnt_selected;$i++) {

            if ($cnt_selected > 3) {
                $selected_product_name = $cnt_selected." Selected Product";
            } else {
                $selected_product_name = $selected_product_name.$goods_list[ $params['selected_product'][$i] ]['name'];
                if ($i != $cnt_selected-1) { $selected_product_name = $selected_product_name.", "; }
            }

        }

        $params['selected_product_name'] = $selected_product_name;
        //$params['selected_product_name'] = $goods_list[ $params['selected_product'] ]['name'];

        $this->_exportExcelStoreList($sales,$params);
    }
} 


$this->view->sales           = $sales;
$this->view->offset          = $limit*($page-1);
$this->view->total           = $total;
$this->view->limit           = $limit;
$this->view->url             = HOST.'timing/analytics-store'.( $params ? '?'.http_build_query($params).'&' : '?' );
$this->view->desc            = $desc;
$this->view->current_col     = $sort;
$this->view->to              = $to;
$this->view->from            = $from;
$this->view->areas           = $areas;
$this->view->org             = $org;
$this->view->market_type     = $market_type_list;
$this->view->params          = $params;