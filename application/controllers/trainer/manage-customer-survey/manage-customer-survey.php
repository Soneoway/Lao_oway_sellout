<?php
$sort = $this->getRequest()->getParam('sort', '');
$desc = $this->getRequest()->getParam('desc', 1);
$id = $this->getRequest()->getParam('id');
$topic = $this->getRequest()->getParam('topic');
$limit = LIMITATION;
$total = 0;

$params = array(
    'id' => $id,
    'topic' => $topic
);

$params['sort'] = $sort;
$params['desc'] = $desc;

$itemModel = new Application_Model_CustomerSurvey();

$page = $this->getRequest()->getParam('page', 1);
$result = $itemModel->fetchPagination($page, $limit, $total, $params);

$this->view->db = $itemModel;

//echo '<pre>';
//print_r($result);
//echo '</pre>';

$this->view->list = $result;
$this->view->limit = $limit;
$this->view->total = $total;
$this->view->params = $params;
$this->view->desc = $desc;
$this->view->sort = $sort;
$this->view->url = HOST . 'trainer/manage-customer-survey/' . ($params ? '?' . http_build_query($params) . '&' : '?');
$this->view->offset = $limit * ($page - 1);


$flashMessenger = $this->_helper->flashMessenger;
$messages = $flashMessenger->setNamespace('success')->getMessages();
$this->view->messages = $messages;
$messages_error = $flashMessenger->setNamespace('error')->getMessages();
$this->view->messages_error = $messages_error;

$this->_helper->viewRenderer->setRender('manage-customer-survey/index');