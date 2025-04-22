<?php
$QCategory = new Application_Model_NotificationCategory();

$name   = $this->getRequest()->getParam('name');
$total  = 0;
$page   = $this->getRequest()->getParam('page', 1);
$limit  = LIMITATION;
$params = array(
    'name' => $name,
    );

$this->view->category = $QCategory->fetchPagination($page, $limit, $total, $params);
$this->view->params   = $params;
$this->view->limit    = $limit;
$this->view->total    = $total;
$this->view->url      = HOST.'manage/notification-category'.( $params ? '?'.http_build_query($params).'&' : '?' );
$this->view->offset   = $limit*($page-1);

$this->view->category_cache = $QCategory->get_string_cache();

$flashMessenger = $this->_helper->flashMessenger;
$this->view->messages_success = $flashMessenger->setNamespace('success')->getMessages();
$this->view->messages = $flashMessenger->setNamespace('error')->getMessages();

$this->_helper->viewRenderer->setRender('notification/category');