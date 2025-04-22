<?php
$page       = $this->getRequest()->getParam('page', 1);
$d_id       = $this->getRequest()->getParam('d_id');
$d_name     = $this->getRequest()->getParam('d_name');
$sn         = $this->getRequest()->getParam('sn');
$area_id    = $this->getRequest()->getParam('area_id');
$regional_market = $this->getRequest()->getParam('regional_market');
$status_id  = $this->getRequest()->getParam('status_id');

$export     = $this->getRequest()->getParam('export', 0);

$limit = LIMITATION;
$total = $total2 = 0;

$params = array(
    'd_id'      => $d_id,
    'd_name'    => $d_name,
    'sn'        => $sn,
    'area_id'   => $area_id,
    'regional_market' => $regional_market,
    'status_id' => $status_id,
    'export'    => $export,
);

//print_r($params);

$userStorage = Zend_Auth::getInstance()->getStorage()->read();
//$this->view->group_id = $userStorage->group_id;

if (in_array($userStorage->group_id, My_Staff_Group::$allow_in_area_view) && !My_Staff_Permission_Area::view_all($userStorage->id))
    $params['asm'] = $userStorage->id;

$QArea = new Application_Model_Area();

if ( in_array($userStorage->group_id, array(RM_ID, RMSTANDBY_ID, ASM_ID, ASMSTANDBY_ID, SALES_ADMIN_ID)) ) {

    $QAsm = new Application_Model_Asm();
    $result_area = $QAsm->get_cache($userStorage->id);

    if ( !empty($result_area['area']) ) {
        $where_area = $QArea->getAdapter()->quoteInto('id IN (?)', $result_area['area']);
        $this->view->areas = $QArea->fetchAll($where_area, 'name');
    } else {
        // ASM Reponsible Level Province 

        if ( !empty($result_area['province']) ) {

            $params['province_id'] = $result_area['province'];

            $QRegionalMarket = new Application_Model_RegionalMarket();
            $this->view->areas = $QRegionalMarket->getAreaByProvince($result_area['province']);

        } else {
            $this->view->areas = array();
        }
        
    }

} else {
    $this->view->areas = $QArea->fetchAll(null, 'name');
}

$QRegionalMarket = new Application_Model_RegionalMarket();

if ($area_id) {
    if (is_array($area_id) && count($area_id))
        $where = $QRegionalMarket->getAdapter()->quoteInto('area_id IN (?)', $area_id);
    else
        $where = $QRegionalMarket->getAdapter()->quoteInto('area_id = ?', $area_id);

    $this->view->regional_markets = $QRegionalMarket->fetchAll($where, 'name');
}

$status_list = array(
    '0'  => array('id' => '0', 'name' => 'Choose'),
    '1'  => array('id' => '1', 'name' => 'Pending'),
    '2'  => array('id' => '2', 'name' => 'Delivered'),
);

$this->view->status_list = $status_list;

$QMarket = new Application_Model_Market();

if ($export && $export == 1) {
    $order_list = $QMarket->ADAO_fetchPagination(null, null, $total2, $params);
    $this->_exportAreaDailyArrivalOrder($order_list,$params);
}

// Check First Time Not Show Data
if (!empty($_GET)) {
    $order_list = $QMarket->ADAO_fetchPagination($page, $limit, $total, $params);
    $total_order = $QMarket->ADAO_fetchPagination(null, null, $total2, $params);
}

$this->view->params = $params;
$this->view->order_list = $order_list;
$this->view->total_order = $total_order;

$this->view->limit = $limit;
$this->view->total = $total;
$this->view->url = HOST.'timing/area-daily-arrival-order/'.( $params ? '?'.http_build_query($params).'&' : '?' );
$this->view->offset = $limit*($page-1);

$flashMessenger = $this->_helper->flashMessenger;
$messages = $flashMessenger->setNamespace('success')->getMessages();
$this->view->messages = $messages;

if($this->getRequest()->isXmlHttpRequest()) {
    $this->_helper->layout->disableLayout();

    $this->_helper->viewRenderer->setRender('area-daily-arrival-order/partials/list');
} else
    $this->_helper->viewRenderer->setRender('area-daily-arrival-order/index');