<?php
$store_id        = $this->getRequest()->getParam('store_id');
$store_name      = $this->getRequest()->getParam('store_name');
$name            = $this->getRequest()->getParam('name');
$from            = $this->getRequest()->getParam('from', date('01/m/Y'));
$to              = $this->getRequest()->getParam('to', date('d/m/Y'));
$area_id         = $this->getRequest()->getParam('area_id');
$regional_market = $this->getRequest()->getParam('regional_market');
$district        = $this->getRequest()->getParam('district');
$store           = $this->getRequest()->getParam('store');
$export          = $this->getRequest()->getParam('export');

$page = $this->getRequest()->getParam('page', 1);
$sort = $this->getRequest()->getParam('sort', 'total');
$desc = $this->getRequest()->getParam('desc', 1);

$limit = LIMITATION;
$total = 0;

$params = array(
    'store_id'        => $store_id,
    'store_name'      => $store_name,  
    'name'            => $name,
    'from'            => $from,
    'to'              => $to,
    'area_id'         => $area_id,
    'regional_market' => $regional_market,
    'district'        => $district,
    'store'           => $store,
    'page'            => $page,
    'sort'            => $sort,
    'desc'            => $desc,
    'export'          => $export,
    );


$QGood = new Application_Model_Good();
$this->view->goods = $QGood->get_cache2();


$QArea = new Application_Model_Area();
$this->view->areas = $QArea->fetchAll(null, 'name');

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

$userStorage = Zend_Auth::getInstance()->getStorage()->read();

if (in_array($userStorage->group_id, My_Staff_Group::$allow_in_area_view) && !My_Staff_Permission_Area::view_all($userStorage->id)) {
    $params['asm'] = $userStorage->id;
} elseif ($userStorage->group_id == SALES_ID) {
    $params['sale_id'] = $userStorage->id;
} elseif ($userStorage->group_id == LEADER_ID) {
    $params['leader_id'] = $userStorage->id;
}

$QTiming = new Application_Model_Timing();
$sales = $QTiming->getStockShop($page, $limit, $total, $params);

$this->view->sales = $sales;
$this->view->total = $total;

$params['get_total_sales'] = 1;
$this->view->total_sales = $QTiming->getStockShop(null, null, $total, $params);

unset($params['asm']);
unset($params['sales_store']);
unset($params['leader_province']);
unset($params['get_total_sales']);

if ( isset($export) && $export ) {
    if ($export == 1) { 
        // export Product by reporter
        //echo "<pre>".print_r($sales)."</pre>";die;
        $this->_exportExcelShopInventory($sales);
    }
}

$this->view->params      = $params;

$this->view->limit       = $limit;
$this->view->desc        = $desc;
$this->view->offset      = $limit*($page-1);
$this->view->url         = HOST.'timing/shop-inventory'.( $params ? '?'.http_build_query($params).'&' : '?' );
$this->view->current_col = $sort;
