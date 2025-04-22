<?php
$sort                   = $this->getRequest()->getParam('sort', '');
$desc                   = $this->getRequest()->getParam('desc', 1);
$id                     = $this->getRequest()->getParam('id');
$userStorage            = Zend_Auth::getInstance()->getStorage()->read();
$name                   = $this->getRequest()->getParam('name');
$type                   = $this->getRequest()->getParam('type');
$enable                   = $this->getRequest()->getParam('enable');
$limit                      = LIMITATION;
$total                      = 0;

$params = array(
    'name'          => $name,
    'type'          => $type,
    'enable'        => $enable,
    'id'            => $id
);

$params['sort'] = $sort;
$params['desc'] = $desc;

//print_r($params);

$itemModel                  = new Application_Model_NewsInfo();
$page                       = $this->getRequest()->getParam('page', 1);
$result                     = $itemModel->fetchPagination($page, $limit, $total, $params);

// print_r($result);

$this->view->list    = $result;
$this->view->limit   = $limit;
$this->view->total   = $total;
$this->view->params  = $params;
$this->view->desc    = $desc;
$this->view->sort    = $sort;
$this->view->url    = HOST.'trainer/list-news-info/'.( $params ? '?'.http_build_query($params).'&' : '?' );
$this->view->offset  = $limit*($page-1);


$flashMessenger       = $this->_helper->flashMessenger;
$messages             = $flashMessenger->setNamespace('success')->getMessages();
$this->view->messages = $messages;
$messages_error       = $flashMessenger->setNamespace('error')->getMessages();
$this->view->messages_error = $messages_error;