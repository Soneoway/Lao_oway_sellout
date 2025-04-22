<?php
$page  = $this->getRequest()->getParam('page', 1);
$name  = $this->getRequest()->getParam('name', '');
$area_id  = $this->getRequest()->getParam('area_id', '');
$limit = LIMITATION;
$total = 0;

$params = array(
    'name' => $name,
    'area_id' => $area_id,
);

$QArea = new Application_Model_Area();
$this->view->areas = $QArea->fetchAll();

$QModel = new Application_Model_RegionalMarket();
$regional_markets = $QModel->fetchPagination($page, $limit, $total, $params);

$this->view->regional_markets = $regional_markets;

$this->view->params = $params;
$this->view->limit = $limit;
$this->view->total = $total;
$this->view->url = HOST.'manage/regional-market/'.( $params ? '?'.http_build_query($params).'&' : '?' );
$this->view->offset = $limit*($page-1);

$flashMessenger = $this->_helper->flashMessenger;
$messages = $flashMessenger->setNamespace('success')->getMessages();
$this->view->messages = $messages;


if($this->getRequest()->isXmlHttpRequest()) {
    $this->_helper->layout->disableLayout();
    $this->_helper->viewRenderer->setRender('regional-market/partials/searchname');
} else {
    $this->_helper->viewRenderer->setRender('regional-market/index');
}