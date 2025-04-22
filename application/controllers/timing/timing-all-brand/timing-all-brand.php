<?php
$page            = $this->getRequest()->getParam('page', 1);
$store_id        = $this->getRequest()->getParam('store_id');
$store_name      = $this->getRequest()->getParam('store_name');
$staff_code      = $this->getRequest()->getParam('staff_code');
$staff_name      = $this->getRequest()->getParam('staff_name');
$brand_id        = $this->getRequest()->getParam('brand_id');
$area_id         = $this->getRequest()->getParam('area_id');
$regional_market = $this->getRequest()->getParam('regional_market');
$district        = $this->getRequest()->getParam('district');
$from            = $this->getRequest()->getParam('from', date('01/m/Y'));
$to              = $this->getRequest()->getParam('to', date('d/m/Y'));
//$export          = $this->getRequest()->getParam('export', 0);

$limit = LIMITATION;
$total = 0;

if ($tmp_imei) { $imei = explode("\n", $tmp_imei); }

$params = array(
    'store_id'        => $store_id,
    'store_name'      => $store_name,
    'staff_code'      => $staff_code,
    'staff_name'      => $staff_name,
    'brand_id'        => $brand_id,
    'area_id'         => $area_id,
    'regional_market' => $regional_market,
    'district'        => $district,
    'from'            => $from,
    'to'              => $to, 
    //'export'          => $export,
);

//print_r($params);

$userStorage = Zend_Auth::getInstance()->getStorage()->read();
//$this->view->group_id = $userStorage->group_id;

if (in_array($userStorage->group_id, My_Staff_Group::$allow_in_area_view) && !My_Staff_Permission_Area::view_all($userStorage->id))
    $params['asm'] = $userStorage->id;

//check sale permission
if ($userStorage->group_id == SALES_ID)
    $params['sale_id'] = $userStorage->id;

//check sale leader permission
if ($userStorage->group_id == LEADER_ID)
    $params['leader_id'] = $userStorage->id;

if ($userStorage->group_id == PGPB_ID)
    $params['pc_id'] = $userStorage->id;

$QAllBrandList = new Application_Model_AllBrandList();
$this->view->brand_list = $QAllBrandList->fetchAll($where, 'name');

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

$QAbTiming = new Application_Model_AbTiming();

/*
if ($export && $export == 1) {
    $timing_issue = $QTimingIssue->fetchPagination(null, null, $total, $params);
    $this->_exportTimingIssue($timing_issue,$params);
}*/

$ab_timing = $QAbTiming->fetchPagination($page, $limit, $total, $params);

$params['get_total_count'] = 0;
//$total_count = $QTimingIssue->fetchPagination(null, null, $total, $params);

$this->view->params = $params;
$this->view->ab_timing = $ab_timing;
// $this->view->total_count = $total_count;

$this->view->limit = $limit;
$this->view->total = $total;
$this->view->url = HOST.'timing/timing-all-brand/'.( $params ? '?'.http_build_query($params).'&' : '?' );
$this->view->offset = $limit*($page-1);

$flashMessenger = $this->_helper->flashMessenger;
$messages = $flashMessenger->setNamespace('success')->getMessages();
$this->view->messages = $messages;

if($this->getRequest()->isXmlHttpRequest()) {
    $this->_helper->layout->disableLayout();

    $this->_helper->viewRenderer->setRender('timing-all-brand/partials/list');
} else
    $this->_helper->viewRenderer->setRender('timing-all-brand/index');