<?php
$page            = $this->getRequest()->getParam('page', 1);
$sort            = $this->getRequest()->getParam('sort', 'product_count');
$desc            = $this->getRequest()->getParam('desc', 1);
$from            = $this->getRequest()->getParam('from', date('01/m/Y'));
$to              = $this->getRequest()->getParam('to', date('d/m/Y'));
$phone_number    = $this->getRequest()->getParam('phone_number');
$name            = $this->getRequest()->getParam('name');
$staff_code      = $this->getRequest()->getParam('staff_code');
$area_id         = $this->getRequest()->getParam('area_id');
$regional_market = $this->getRequest()->getParam('regional_market');
$district        = $this->getRequest()->getParam('district');
$store           = $this->getRequest()->getParam('store');
$store_type      = $this->getRequest()->getParam('store_type');
$chk_tme         = $this->getRequest()->getParam('chk_tme');
$actived         = $this->getRequest()->getParam('actived');
$good_id         = $this->getRequest()->getParam('good'); // Search by product name
$company         = $this->getRequest()->getParam('company');
$position        = $this->getRequest()->getParam('position');
$brand_id        = $this->getRequest()->getParam('brand_id');


$limit = LIMITATION;

$params = array(
    'page'            => $page,
    'sort'            => $sort,
    'desc'            => $desc,
    'from'            => $from,
    'to'              => $to,
    'phone_number'    => $phone_number,
    'name'            => $name,
    'staff_code'      => trim($staff_code),
    'area_id'         => $area_id,
    'regional_market' => $regional_market,
    'district'        => $district,
    'store'           => $store,
    'store_type'      => $store_type,
    'chk_tme'         => $chk_tme,
    'actived'         => $actived,
    'group_id'        => 4,
    'good'            => $good_id,
    'company'         => $company,
    'position'        => $position,
    'brand'        => $brand_id

);


$QGood = new Application_Model_Good();

$goods_list = $QGood->getProduct($params);
$this->view->goods = $goods_list;

$brands = $QGood->get_brand();
$this->view->brands = $brands;


$userStorage = Zend_Auth::getInstance()->getStorage()->read();
$group_id = $userStorage->group_id;

// My_Staff_Permission_Area::view_all // kiểm tra xem người này có toàn quyền xem tất cả các khu vực hay không
// viết xong rồi giờ nhìn vô éo nhớ nó là cái gì, phải comment lại cho chắc
if (in_array($userStorage->group_id, My_Staff_Group::$allow_in_area_view) && !My_Staff_Permission_Area::view_all($userStorage->id))
    $params['asm'] = $userStorage->id;

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

$QArea = new Application_Model_Area();
//$areas = $QArea->get_cache();

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

// echo $areas.'<br />';

$QRegionalMarket = new Application_Model_RegionalMarket();

$QOrg = new Application_Model_Org();
$org_result = $QOrg->fetchAll(null, 'org_name');


$QStoreType = new Application_Model_StoreType();
$st_result = $QStoreType->fetchAll(null, 'store_type_name');

$result = array();
if ($st_result){
    foreach ($st_result as $item) {
        $result[$item['store_type_id']] = array('store_type_name' => $item['store_type_name']);
    }
}

$i=0;
foreach ($org_result as $item) {
    $info[$i]['org_id'] = $item['org_id'];
    $info[$i]['org_name'] = "[".$result[ $item['store_type_id'] ]['store_type_name']."] ".$item['org_name'];
    $i++;
}

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

$total = $total2 = 0;

$QTiming = new Application_Model_Timing();

// Check First Time Not Show Data
if (!empty($_GET)) { 
    $sales = $QTiming->report($page, $limit, $total, $params);

    //$params['get_total_sales'] = true;
    $total_sales = $QTiming->report(null, null, $total2, $params);
    unset($params['get_total_sales']);
    unset($params['asm']);
    unset($params['am']);
    unset($params['sale_id']);
    unset($params['leader_id']);
}


// [Sake Note] : Start 
// do not use area model to get total activated becuase other part of report by pc/sale dont use table imei_kpi
// modified : get actived imei in Model:Timing:report 
/*
    $QImeiKpi = new Application_Model_ImeiKpi();
    $params['get_total_sales'] = true;
    $total_sales1 = $QImeiKpi->fetchArea($params); 
*/
// [Sake Note] : End 

$QTeam = new Application_Model_Team;
$this->view->teams = $QTeam->get_cache();

$this->view->total_sales = $total_sales;
//$this->view->total_sales_activated = $total_sales1['total_activated'];
$this->view->sales       = $sales;
$this->view->offset      = $limit*($page-1);
$this->view->total       = $total;
$this->view->limit       = $limit;
$this->view->url         = HOST.'timing/analytics'.( $params ? '?'.http_build_query($params).'&' : '?' );
$this->view->desc        = $desc;
$this->view->current_col = $sort;
$this->view->to          = $to;
$this->view->from        = $from;
$this->view->areas       = $areas;
$this->view->params      = $params;
$this->view->store_type  = $info;
