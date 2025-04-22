<?php
$page            = $this->getRequest()->getParam('page', 1);

$store_id        = $this->getRequest()->getParam('store_id');
$store_name      = $this->getRequest()->getParam('store_name');
$org             = $this->getRequest()->getParam('org');
$area_id         = $this->getRequest()->getParam('area_id');
$regional_market = $this->getRequest()->getParam('regional_market');

$from            = $this->getRequest()->getParam('from', date('01/m/Y'));
$to              = $this->getRequest()->getParam('to', date('d/m/Y'));
$brand_id        = $this->getRequest()->getParam('brand_id');
$model_id        = $this->getRequest()->getParam('model_id');

$export          = $this->getRequest()->getParam('export', 0);

$limit = LIMITATION;
$total = 0;

$params = array(
    'store_id'        => $store_id,
    'store_name'      => $store_name,
    'org'             => $org,
    'area_id'         => $area_id,
    'regional_market' => $regional_market,
    'from'            => $from,
    'to'              => $to, 
    'brand_id'        => $brand_id,
    'model_id'        => $model_id,
    'export'          => $export,
);

//print_r($params);

$userStorage = Zend_Auth::getInstance()->getStorage()->read();
//$this->view->group_id = $userStorage->group_id;

if (in_array($userStorage->group_id, My_Staff_Group::$allow_in_area_view) && !My_Staff_Permission_Area::view_all($userStorage->id))
    $params['asm'] = $userStorage->id;

//check brandshop manager permission
if ($userStorage->group_id == BM_ID)
    $params['bm_id'] = $userStorage->id;

//check admin brandshop permission
if ($userStorage->group_id == 39)
    $params['admin_bs'] = $userStorage->id;

// Get Filter Area 
$QArea = new Application_Model_Area();

if ( in_array($userStorage->group_id, array(RM_ID, RMSTANDBY_ID)) ) {
    $areas = $QArea->getAreaByAsmTable($userStorage->id);
} else {
    $areas = $QArea->fetchAll(null, 'name');
}

$this->view->areas = $areas;

// Get Filter Province  
$QRegionalMarket = new Application_Model_RegionalMarket();

if ($area_id) {
    if (is_array($area_id) && count($area_id))
        $where = $QRegionalMarket->getAdapter()->quoteInto('area_id IN (?)', $area_id);
    else
        $where = $QRegionalMarket->getAdapter()->quoteInto('area_id = ?', $area_id);

    $this->view->regional_markets = $QRegionalMarket->fetchAll($where, 'name');
}

// Get Filter Store Type 
$QOrg = new Application_Model_Org();
$this->view->org = $QOrg->fetchAll(null, 'org_name');

// Get Filter Mobile Brand
$QMobileBrand = new Application_Model_MobileBrand();

$where = $QMobileBrand->getAdapter()->quoteInto('status = ?', 1);
$this->view->brand_list = $QMobileBrand->fetchAll($where, 'name');

// Get Filter Mobile Model  
$QMobileModel = new Application_Model_MobileModel();

if ($brand_id) {
    if (is_array($brand_id) && count($brand_id))
        $where = $QMobileModel->getAdapter()->quoteInto('brand_id IN (?)', $brand_id);
    else
        $where = $QMobileModel->getAdapter()->quoteInto('brand_id = ?', $brand_id);

    $this->view->model_list = $QMobileModel->fetchAll($where, 'name');
}

$QMobileComplete = new Application_Model_MobileComplete();

if ($export) {

    switch ($export) {
        case 1:
            $data_list = $QMobileComplete->fetchPagination(null, null, $total, $params);
            $this->_exportTradeInReport($data_list,$params);
            break;
        case 2:
            $data_list = $QMobileComplete->getImeiCondition($params);
            // echo "<pre>"; print_r($data_list); die;
            $this->_exportImeiConditionReport($data_list,$params);
            break;
        default: 
            break;
    }

    
}

// Check First Time Not Show Data
if (!empty($_GET)) {
    $data_list = $QMobileComplete->fetchPagination($page, $limit, $total, $params);
}

// $params['get_total_count'] = 0;
// $total_count = $QStaffCheckInLog->fetchPagination(null, null, $total, $params);

$this->view->params = $params;
$this->view->data_list = $data_list;
// $this->view->total_count = $total_count;

$this->view->limit = $limit;
$this->view->total = $total;
$this->view->url = HOST.'timing/trade-in/'.( $params ? '?'.http_build_query($params).'&' : '?' );
$this->view->offset = $limit*($page-1);

$flashMessenger = $this->_helper->flashMessenger;
$messages = $flashMessenger->setNamespace('success')->getMessages();
$this->view->messages = $messages;

if($this->getRequest()->isXmlHttpRequest()) {
    $this->_helper->layout->disableLayout();

    $this->_helper->viewRenderer->setRender('trade-in/partials/list');
} else
    $this->_helper->viewRenderer->setRender('trade-in/index');