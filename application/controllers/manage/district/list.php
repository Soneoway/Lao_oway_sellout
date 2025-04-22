<?php
$page     = $this->getRequest()->getParam('page', 1);
$name     = $this->getRequest()->getParam('name', '');
$province = $this->getRequest()->getParam('province', '');
$limit    = LIMITATION;
$total    = 0;

$params = array(
    'name'     => $name,
    'province' => $province,
);

$QModel = new Application_Model_RegionalMarket();
$this->view->provinces = $QModel->get_cache();
$this->view->regional_markets = $QModel->fetchPaginationDistrict($page, $limit, $total, $params);

$this->view->params = $params;
$this->view->limit = $limit;
$this->view->total = $total;
$this->view->url = HOST.'manage/district/'.( $params ? '?'.http_build_query($params).'&' : '?' );
$this->view->offset = $limit*($page-1);

$flashMessenger = $this->_helper->flashMessenger;
$messages = $flashMessenger->setNamespace('error')->getMessages();
$messages_success = $flashMessenger->setNamespace('success')->getMessages();
$this->view->messages_success = $messages_success;
$this->view->messages = $messages;

if($this->getRequest()->isXmlHttpRequest()) {
    $this->_helper->layout->disableLayout();
    $this->_helper->viewRenderer->setRender('district/partials/searchname');
} else {
    $this->_helper->viewRenderer->setRender('district/index');
}