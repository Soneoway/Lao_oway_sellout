<?php
$flashMessenger             = $this->_helper->flashMessenger;
$messages                   = $flashMessenger->setNamespace('success')->getMessages();
$this->view->messages       = $messages;
$messages_error             = $flashMessenger->setNamespace('error')->getMessages();
$this->view->messages_error = $messages_error;

$QTrainerTypeAsset          = new Application_Model_TrainerTypeAsset();
$page                       = $this->getRequest()->getParam('page', 1);
$name                       = $this->getRequest()->getParam('name');

$params = array_filter(array(
    'name' => $name
));

$limit              = LIMITATION;
$total              = 0;

$assets = $QTrainerTypeAsset->fetchPagination($page, $limit, $total, $params);

$this->view->assets     = $assets;
$this->view->params     = $params;
$this->view->limit      = $limit;
$this->view->total      = $total;
$this->view->url        = HOST . 'trainer/type-asset' . ($params ? '?' . http_build_query($params) .'&' : '?');
$this->view->offset     = $limit * ($page - 1);






