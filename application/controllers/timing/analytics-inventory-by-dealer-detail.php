<?php
$page           = $this->getRequest()->getParam('page', 1);
$dealer_id      = $this->getRequest()->getParam('dealer_id');
$product_id     = $this->getRequest()->getParam('product_id');
$color          = $this->getRequest()->getParam('color');

$QGood = new Application_Model_Good();
$this->view->goodsCached = $QGood->get_cat_model_color_cache();

$QArea            = new Application_Model_Area();
$areas            = $QArea->get_cache();

$QRegionalMarket  = new Application_Model_RegionalMarket();

$userStorage = Zend_Auth::getInstance()->getStorage()->read();
$group_id = $userStorage->group_id;

$params = array(
    'product_id'    => $product_id,
    'color'         => $color,
    'dealer_id'     => $dealer_id,
);

if (in_array($group_id, My_Staff_Group::$allow_in_area_view) && !My_Staff_Permission_Area::view_all($userStorage->id)) {
    $params['asm'] = $userStorage->id;
} elseif ($group_id == SALES_ID) {
    $params['sales_store'] = $userStorage->id;
} elseif ($group_id == LEADER_ID) {
    $params['leader_province'] = $userStorage->id;
}

$limit = LIMITATION;

$QTiming = new Application_Model_Timing();
$QDistributor = new Application_Model_Distributor();
$total = 0;

$result = $QTiming->report_inventory_by_dealer_detail($page, $limit, $total, $params);

$this->view->params            = $params;
$this->view->areas            = $areas;
$distributorCached = $QDistributor->get_cache();
$this->view->distributorCached = $distributorCached;
$this->view->result = $result;
$this->view->offset = $limit*($page-1);
$this->view->total  = $total;
$this->view->limit  = $limit;
$this->view->url    = HOST.'timing/analytics-inventory-by-dealer-detail'.( $params ? '?'.http_build_query($params).'&' : '?' );
