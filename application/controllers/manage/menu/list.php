<?php
$page  = $this->getRequest()->getParam('page', 1);
$title  = $this->getRequest()->getParam('title', '');
$parent_id  = $this->getRequest()->getParam('parent_id', '');
$limit = LIMITATION;
$total = 0;

$params = array(
    'title'        => $title,
    'parent_id'     => $parent_id,
);

$QModel = new Application_Model_Menu();
$this->view->parents = $QModel->get_cache();
$menus = $QModel->fetchPagination($page, $limit, $total, $params);

$this->view->menus = $menus;

$this->view->params = $params;
$this->view->limit  = $limit;
$this->view->total  = $total;
$this->view->url    = HOST.'manage/menu/'.( $params ? '?'.http_build_query($params).'&' : '?' );
$this->view->offset = $limit*($page-1);

$flashMessenger = $this->_helper->flashMessenger;
$messages = $flashMessenger->setNamespace('success')->getMessages();
$this->view->messages = $messages;


if($this->getRequest()->isXmlHttpRequest()) {
    $this->_helper->layout->disableLayout();
    $this->_helper->viewRenderer->setRender('menu/partials/searchname');
} else {
    $this->_helper->viewRenderer->setRender('menu/index');
}