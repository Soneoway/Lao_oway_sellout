<?php
$page            = $this->getRequest()->getParam('page', 1);
$store_id        = $this->getRequest()->getParam('store_id');
$store_name      = $this->getRequest()->getParam('store_name');
$org             = $this->getRequest()->getParam('org');
$market_type     = $this->getRequest()->getParam('market_type');
$market_name     = $this->getRequest()->getParam('market_name');
$area_id         = $this->getRequest()->getParam('area_id');
$regional_market = $this->getRequest()->getParam('regional_market');
$from            = $this->getRequest()->getParam('from', date('01/m/Y'));
$to              = $this->getRequest()->getParam('to', date('d/m/Y'));
$export          = $this->getRequest()->getParam('export', 0);

$limit = LIMITATION;
$total = 0;

$params = array(
    'store_id'        => $store_id,
    'store_name'      => $store_name,
    'org'             => $org,
    'market_type'     => $market_type,      
    'market_name'     => $market_name,
    'area_id'         => $area_id,
    'regional_market' => $regional_market,
    'from'            => $from,
    'to'              => $to, 
    'export'          => $export,
);

//print_r($params);

$userStorage = Zend_Auth::getInstance()->getStorage()->read();
//$this->view->group_id = $userStorage->group_id;

// if (in_array($userStorage->group_id, My_Staff_Group::$allow_in_area_view) && !My_Staff_Permission_Area::view_all($userStorage->id))
//     $params['asm'] = $userStorage->id;

if ($userStorage->group_id == AM_ID) 
    $params['am'] = $userStorage->id;

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

$QMarketType = new Application_Model_MarketType();
$this->view->market_type = $QMarketType->fetchAll(null, 'name');

// Store Type List
$QOrg = new Application_Model_Org();

$additional_list = array(19,41,20,43,26,42,27,32,38); // Operator + KR

$where = array();
$where[] = $QOrg->getAdapter()->quoteInto('(store_type_id = 1 OR org_id IN (?))', $additional_list);
$this->view->org = $QOrg->fetchAll($where, 'org_name');

if ($market_type) {
    $QMarketName = new Application_Model_MarketName();
         
    $where = array();
    $where[] = $QMarketName->getAdapter()->quoteInto('market_type_id IN (?)', $market_type);

    // if ( isset($params['area_permission']) && !empty($params['area_permission']) ) {
    //     $where[] = $QMarketName->getAdapter()->quoteInto('area_id IN (?)', $params['area_permission']);
    // }

    $this->view->market_name = $QMarketName->fetchAll($where, 'name');
}

$QStaffCheckInLog = new Application_Model_StaffCheckInLog();

if ($export && $export == 1) {
    $channel_pc_status = $QStaffCheckInLog->CPS_fetchPagination(null, null, $total, $params);
    $this->_exportChannelPcStatus($channel_pc_status,$params);
}

// Check First Time Not Show Data
if (!empty($_GET)) {
    $channel_pc_status = $QStaffCheckInLog->CPS_fetchPagination($page, $limit, $total, $params);
}

$params['get_total_count'] = 0;
// $total_count = $QStaffCheckInLog->fetchPagination(null, null, $total, $params);

$this->view->params = $params;
$this->view->channel_pc_status = $channel_pc_status;
$this->view->total_count = $total_count;

$this->view->limit = $limit;
$this->view->total = $total;
$this->view->url = HOST.'timing/channel-pc-status/'.( $params ? '?'.http_build_query($params).'&' : '?' );
$this->view->offset = $limit*($page-1);

$flashMessenger = $this->_helper->flashMessenger;
$messages = $flashMessenger->setNamespace('success')->getMessages();
$this->view->messages = $messages;

if($this->getRequest()->isXmlHttpRequest()) {
    $this->_helper->layout->disableLayout();

    $this->_helper->viewRenderer->setRender('channel-pc-status/partials/list');
} else
    $this->_helper->viewRenderer->setRender('channel-pc-status/index');