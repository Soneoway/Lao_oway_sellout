<?php
$sort                   = $this->getRequest()->getParam('sort', '');
$desc                   = $this->getRequest()->getParam('desc', 1);
$id                     = $this->getRequest()->getParam('id');
$name                   = $this->getRequest()->getParam('name');
$enable                 = $this->getRequest()->getParam('enable');
$userStorage            = Zend_Auth::getInstance()->getStorage()->read();
$limit                  = LIMITATION;
$total                  = 0;

$params = array(
    'name'          => $name,
    'enable'         => $enable,
    'id'             => $id
);

$params['sort'] = $sort;
$params['desc'] = $desc;

//print_r($params);

$db = Zend_Registry::get('db');

$QKnowledge_type = $db->select()
    ->from(array('kt' => 'sales_knowledge_base_type'));
$knowledge_type = $db->fetchAll($QKnowledge_type);

$this->view->knowledge_type    = $knowledge_type;

$itemModel                  = new Application_Model_SalesKnowledgeBaseType();
$page                       = $this->getRequest()->getParam('page', 1);
$result                     = $itemModel->fetchPagination($page, $limit, $total, $params);

// print_r($result);

$this->view->list    = $result;
$this->view->limit   = $limit;
$this->view->total   = $total;
$this->view->params  = $params;
$this->view->desc    = $desc;
$this->view->sort    = $sort;
$this->view->url    = HOST.'trainer/knowledge-base-type/'.( $params ? '?'.http_build_query($params).'&' : '?' );
$this->view->offset  = $limit*($page-1);


$flashMessenger       = $this->_helper->flashMessenger;
$messages             = $flashMessenger->setNamespace('success')->getMessages();
$this->view->messages = $messages;
$messages_error       = $flashMessenger->setNamespace('error')->getMessages();
$this->view->messages_error = $messages_error;

$this->_helper->viewRenderer->setRender('knowledge-base-type/index');