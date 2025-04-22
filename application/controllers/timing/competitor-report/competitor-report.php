<?php
$page            = $this->getRequest()->getParam('page', 1);
$store_id        = $this->getRequest()->getParam('store_id');
$store_name      = $this->getRequest()->getParam('store_name');
$staff_code      = $this->getRequest()->getParam('staff_code');
$staff_name      = $this->getRequest()->getParam('staff_name');
$area_id         = $this->getRequest()->getParam('area_id');
$regional_market = $this->getRequest()->getParam('regional_market');
$district        = $this->getRequest()->getParam('district');
$from            = $this->getRequest()->getParam('from', date('01/m/Y'));
$to              = $this->getRequest()->getParam('to', date('d/m/Y'));
$brand_id        = $this->getRequest()->getParam('brand_id');
$product_id      = $this->getRequest()->getParam('product_id');
$status_id       = $this->getRequest()->getParam('status_id', array(0));
$export          = $this->getRequest()->getParam('export', 0);
$update          = $this->getRequest()->getParam('update');

$limit = LIMITATION;
$total = 0;

$params = array(
    'store_id'        => $store_id,
    'store_name'      => $store_name,
    'staff_code'      => $staff_code,
    'staff_name'      => $staff_name,

    'area_id'         => $area_id,
    'regional_market' => $regional_market,
    'district'        => $district,
    'from'            => $from,
    'to'              => $to, 

    'brand_id'        => $brand_id,
    'product_id'      => $product_id,
    'status_id'       => $status_id,
    'export'          => $export,
);

//print_r($params);

$userStorage = Zend_Auth::getInstance()->getStorage()->read();
//$this->view->group_id = $userStorage->group_id;

if (in_array($userStorage->group_id, My_Staff_Group::$allow_in_area_view) && !My_Staff_Permission_Area::view_all($userStorage->id))
    $params['asm'] = $userStorage->id;

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

$QCompetitorBrand = new Application_Model_CompetitorBrand();
$brand_list = $QCompetitorBrand->fetchAll(null, 'name');

$this->view->brand_list = $brand_list;

$status_list = array(
    '0'  => array('id' => '0', 'name' => 'Normal'),
    '1'  => array('id' => '1', 'name' => 'Hero Product'),
    '2'  => array('id' => '2', 'name' => 'Flagship'),
    '3'  => array('id' => '3', 'name' => 'EOL'),
);

$this->view->status_list = $status_list;

$QCompetitorRecord = new Application_Model_CompetitorRecord();

if ($export && $export == 1) {
    $competitor_sellout = $QCompetitorRecord->fetchPagination(null, null, $total, $params);
    // echo "<pre>"; print_r($competitor_sellout);

    // $a = array_keys($competitor_sellout[0]);
    // echo "<pre>"; print_r($a);

    // array_splice($a, count($a) - 13, 13);
    // echo "<pre>"; print_r($a);

    $this->_exportCompetitorReport($competitor_sellout,$params);
}

$competitor_sellout = $QCompetitorRecord->fetchPagination($page, $limit, $total, $params);

$params['get_total_count'] = 0;
$total_count = $QCompetitorRecord->fetchPagination(null, null, $total, $params);

$this->view->params = $params;
$this->view->competitor_sellout = $competitor_sellout;
$this->view->total_count = $total_count;

$this->view->limit = $limit;
$this->view->total = $total;
$this->view->url = HOST.'timing/competitor-report/'.( $params ? '?'.http_build_query($params).'&' : '?' );
$this->view->offset = $limit*($page-1);

$flashMessenger = $this->_helper->flashMessenger;
$messages = $flashMessenger->setNamespace('success')->getMessages();
$this->view->messages = $messages;

if($this->getRequest()->isXmlHttpRequest()) {
    $this->_helper->layout->disableLayout();

    $this->_helper->viewRenderer->setRender('competitor-report/partials/list');
} else
    $this->_helper->viewRenderer->setRender('competitor-report/index');