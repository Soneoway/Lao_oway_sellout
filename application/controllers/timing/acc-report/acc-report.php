<?php
$page       = $this->getRequest()->getParam('page', 1);
$store_id   = $this->getRequest()->getParam('store_id');
$store_name = $this->getRequest()->getParam('store_name');

$good_id    = $this->getRequest()->getParam('good_id');
$color_id   = $this->getRequest()->getParam('color_id');

$area_id         = $this->getRequest()->getParam('area_id');
$regional_market = $this->getRequest()->getParam('regional_market');
$district        = $this->getRequest()->getParam('district');

$export     = $this->getRequest()->getParam('export', 0);

$limit = 30;
$total = $total2 = 0;

$params = array(
    'store_id'        => $store_id,
    'store_name'      => $store_name,
    'good_id'         => $good_id,
    'color_id'        => $color_id,
    'area_id'         => $area_id,
    'regional_market' => $regional_market,
    'district'        => $district,
    'export'          => $export,
);

//print_r($params);

$userStorage = Zend_Auth::getInstance()->getStorage()->read();
//$this->view->group_id = $userStorage->group_id;

// if (in_array($userStorage->group_id, My_Staff_Group::$allow_in_area_view) && !My_Staff_Permission_Area::view_all($userStorage->id))
//     $params['asm'] = $userStorage->id;

if ( $userStorage->group_id == BM_ID ) { $params['bm_id'] = $userStorage->id; }

$QArea = new Application_Model_Area();

if ( in_array($userStorage->group_id, array(RM_ID, RMSTANDBY_ID)) ) {
    $areas = $QArea->getAreaByAsmTable($userStorage->id);
} else {
    $areas = $QArea->fetchAll(null, 'name');
}

$this->view->areas = $areas;

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

$QAccStock = new Application_Model_AccStock();
$QGood = new Application_Model_Good();
$GoodColorCombined = new Application_Model_GoodColorCombined();

$good_list = $QGood->get_acc_cache();

if (isset($good_id) && $good_id) {
    $color_list = $GoodColorCombined->getColorByModel($good_id);
}

if ($export) {

    if ($export == 1) {
        $store_acc_list = $QAccStock->fetchPagination($params);
        $this->_exportAccStock($store_acc_list,$params);
    }

}

// Check First Time Not Show Data
if (!empty($_GET)) { 
    $store_acc_list = $QAccStock->fetchPagination($page, $limit, $total, $params);
    // echo "<pre>"; print_r($store_acc_list);
}

$this->view->params = $params;
$this->view->goods = $good_list;
$this->view->colors = $color_list;
$this->view->store_acc_list = $store_acc_list;

$this->view->offset = $limit*($page-1);
$this->view->total  = $total;
$this->view->limit  = $limit;
$this->view->url    = HOST.'timing/acc-report'.( $params ? '?'.http_build_query($params).'&' : '?' );

$flashMessenger = $this->_helper->flashMessenger;
$messages = $flashMessenger->setNamespace('success')->getMessages();
$this->view->messages = $messages;

if($this->getRequest()->isXmlHttpRequest()) {
    $this->_helper->layout->disableLayout();

    $this->_helper->viewRenderer->setRender('acc-report/partials/list');
} else
    $this->_helper->viewRenderer->setRender('acc-report/index');