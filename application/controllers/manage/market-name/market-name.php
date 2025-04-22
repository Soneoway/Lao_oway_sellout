<?php

$page           = $this->getRequest()->getParam('page', 1);
$market_name_id = $this->getRequest()->getParam('market_name_id');
$market_name    = $this->getRequest()->getParam('market_name');
$market_type    = $this->getRequest()->getParam('market_type');
$area_id        = $this->getRequest()->getParam('area_id');
$export         = $this->getRequest()->getParam('export');

$limit = 20;
$total = 0;

$params = array(
    'market_name_id'    => $market_name_id,
    'market_name'       => $market_name,
    'market_type'       => $market_type,
    'area_id'           => $area_id,
    'export'            => $export 
);
//print_r($params);

$userStorage = Zend_Auth::getInstance()->getStorage()->read();

if (in_array($userStorage->group_id, My_Staff_Group::$allow_in_area_view) && !My_Staff_Permission_Area::view_all($userStorage->id))
    $params['asm'] = $userStorage->id;

$QMarketType = new Application_Model_MarketType();
$market_type_list = $QMarketType->fetchAll(null, 'name');

$QArea = new Application_Model_Area();
$this->view->areas = $QArea->fetchAll(null, 'name');

$QMarketName = new Application_Model_MarketName();

if ($export && $export == 1) {
    $market_name = $QMarketName->fetchPagination(null, null, $total, $params);
    $this->_exportMarketName($market_name,$params);
}

$market_name = $QMarketName->fetchPagination($page, $limit, $total, $params);

$this->view->params = $params;
$this->view->market_name = $market_name;
$this->view->market_type = $market_type_list;

$this->view->limit = $limit;
$this->view->total = $total;
$this->view->url = HOST.'manage/market-name/'.( $params ? '?'.http_build_query($params).'&' : '?' );
$this->view->offset = $limit*($page-1);

$flashMessenger = $this->_helper->flashMessenger;
$messages = $flashMessenger->setNamespace('success')->getMessages();
$this->view->messages = $messages;

if($this->getRequest()->isXmlHttpRequest()) {
    $this->_helper->layout->disableLayout();

    $this->_helper->viewRenderer->setRender('market-name/partials/list');
} else
    $this->_helper->viewRenderer->setRender('market-name/index');