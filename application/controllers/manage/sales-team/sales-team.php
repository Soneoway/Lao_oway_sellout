<?php
$regional_market = $this->getRequest()->getParam('regional_market');
$area_id         = $this->getRequest()->getParam('area_id');
$name            = $this->getRequest()->getParam('name');
$staff_email     = $this->getRequest()->getParam('staff_email');
$staff_name      = $this->getRequest()->getParam('staff_name');

$page  = $this->getRequest()->getParam('page', 1);
$limit = LIMITATION;
$total = 0;

$params = array(
    'regional_market' => $regional_market,
    'name'            => $name,
    'staff_email'     => $staff_email,
    'staff_name'      => $staff_name,
    'area_id'         => $area_id,
    'page'            => $page,
    'limit'           => $limit,
);

$QSalesTeam = new Application_Model_SalesTeam();
$sales_teams = $QSalesTeam->fetchPagination($page, $limit, $total, $params);

$this->view->sales_teams = $sales_teams;

$QArea = new Application_Model_Area();
$this->view->cached_areas = $QArea->get_cache();

$QRegionalMarket = new Application_Model_RegionalMarket();
$this->view->cached_regional_markets = $QRegionalMarket->get_cache();

if ($area_id) {
    $where = $QRegionalMarket->getAdapter()->quoteInto('area_id = ?', $area_id);
    $this->view->regional_markets = $QRegionalMarket->fetchAll($where);
}

$this->view->limit  = $limit;
$this->view->total  = $total;
$this->view->params = $params;
$this->view->url = HOST.'manage/sales-team/'.( $params ? '?'.http_build_query($params).'&' : '?' );
$this->view->offset = $limit*($page-1);

$flashMessenger = $this->_helper->flashMessenger;
$messages = $flashMessenger->setNamespace('success')->getMessages();
$this->view->messages = $messages;

$this->_helper->viewRenderer->setRender('sales-team/index');