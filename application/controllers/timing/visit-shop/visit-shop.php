<?php
$page       = $this->getRequest()->getParam('page', 1);

$store_id   = $this->getRequest()->getParam('store_id');
$store_name = $this->getRequest()->getParam('store_name');
$staff_code = $this->getRequest()->getParam('staff_code');
$staff_name = $this->getRequest()->getParam('staff_name');
$org        = $this->getRequest()->getParam('org');
$area_id    = $this->getRequest()->getParam('area_id');
$regional_market = $this->getRequest()->getParam('regional_market');
$market_name     = $this->getRequest()->getParam('market_name');

$from       = $this->getRequest()->getParam('from', date('01/m/Y'));
$to         = $this->getRequest()->getParam('to', date('d/m/Y'));
$export     = $this->getRequest()->getParam('export', 0);

$limit = LIMITATION;
$total = 0;

$params = array(
    'store_id'      => $store_id,
    'store_name'    => $store_name,
    'staff_code'    => $staff_code,
    'staff_name'    => $staff_name,
    'org'           => $org,
    'area_id'       => $area_id,
    'regional_market' => $regional_market,
    'market_name'   => $market_name,
    'from'          => $from,
    'to'            => $to, 
    'export'        => $export,
);

//print_r($params);

$userStorage = Zend_Auth::getInstance()->getStorage()->read();
//$this->view->group_id = $userStorage->group_id;

// Get Filter Area 
$QArea = new Application_Model_Area();
$QRegionalMarket = new Application_Model_RegionalMarket();

if (in_array($userStorage->group_id, My_Staff_Group::$allow_in_area_view) && !My_Staff_Permission_Area::view_all($userStorage->id)) {

    $params['asm'] = $userStorage->id;

    $QAsm = new Application_Model_Asm();
    $result_area = $QAsm->get_cache($userStorage->id);

    if ( !empty($result_area['area']) ) {
        $where_area = $QArea->getAdapter()->quoteInto('id IN (?)', $result_area['area']);
        $this->view->areas = $QArea->fetchAll($where_area, 'name');
    } else {
        // ASM Reponsible Level Province 

        if ( !empty($result_area['province']) ) {

            $params['province_id'] = $result_area['province'];

            $this->view->areas = $QRegionalMarket->getAreaByProvince($result_area['province']);

        } else {
            $this->view->areas = array();
        }
        
    }

} else {

    $this->view->areas = $QArea->fetchAll(null, 'name');

}

// Get Filter Province  
if ($area_id) {
    if (is_array($area_id) && count($area_id))
        $where = $QRegionalMarket->getAdapter()->quoteInto('area_id IN (?)', $area_id);
    else
        $where = $QRegionalMarket->getAdapter()->quoteInto('area_id = ?', $area_id);

    $this->view->regional_markets = $QRegionalMarket->fetchAll($where, 'name');
}

// Get Filter Market Name 
if ($area_id) {
    $QMarketName = new Application_Model_MarketName();
         
    $where = array();
    $where[] = $QMarketName->getAdapter()->quoteInto('area_id IN (?)', $area_id);

    $this->view->market_name = $QMarketName->fetchAll($where, 'name');
}

// Get Filter Store Type 
$QOrg = new Application_Model_Org();
$this->view->org = $QOrg->fetchAll(null, 'org_name');

$QShopVisit = new Application_Model_ShopVisit();

if ($export) {
    switch ($export) {
        case 1: $this->_exportShopVisitReport($params); break;
        default: break;
    }
}

// Check First Time Not Show Data
if (!empty($_GET)) {
    $data_list = $QShopVisit->fetchPagination($page, $limit, $total, $params);
}

// $params['get_total_count'] = 0;
// $total_count = $QStaffCheckInLog->fetchPagination(null, null, $total, $params);

$this->view->params = $params;
$this->view->data_list = $data_list;
// $this->view->total_count = $total_count;

$this->view->limit = $limit;
$this->view->total = $total;
$this->view->url = HOST.'timing/visit-shop/'.( $params ? '?'.http_build_query($params).'&' : '?' );
$this->view->offset = $limit*($page-1);

$flashMessenger = $this->_helper->flashMessenger;
$messages = $flashMessenger->setNamespace('success')->getMessages();
$this->view->messages = $messages;

if($this->getRequest()->isXmlHttpRequest()) {
    $this->_helper->layout->disableLayout();

    $this->_helper->viewRenderer->setRender('visit-shop/partials/list');
} else
    $this->_helper->viewRenderer->setRender('visit-shop/index');