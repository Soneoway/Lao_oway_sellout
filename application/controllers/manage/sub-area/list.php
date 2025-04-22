<?php
$page               = $this->getRequest()->getParam('page', 1);
$name               = $this->getRequest()->getParam('name');
$staff_id           = $this->getRequest()->getParam('staff_id');
$status             = $this->getRequest()->getParam('status');
$limit              = LIMITATION;
$total              = 0;

$params = array(
    'name'     => $name,
    'staff_id' => $staff_id,
    'status'   => $status
);

$QModel = new Application_Model_SubArea();
$this->view->staffs = $QModel->get_subarea();

$this->view->sub_areas = $QModel->fetchPaginationSubArea($page, $limit, $total, $params);

$this->view->params      = $params;
$this->view->offset      = $limit*($page-1);
$this->view->total       = $total;
$this->view->limit       = $limit;
$this->view->url         = HOST.'manage/sub-area'.( $params ? '?'.http_build_query($params).'&' : '?' );
$this->view->test        = ( $params ? '?'.http_build_query($params).'&' : '?' );


$flashMessenger = $this->_helper->flashMessenger;
$messages = $flashMessenger->setNamespace('success')->getMessages();
$this->view->messages = $messages;

if($this->getRequest()->isXmlHttpRequest()) {
    $this->_helper->layout->disableLayout();

    $this->_helper->viewRenderer->setRender('sub-area/index');
} else
$this->_helper->viewRenderer->setRender('sub-area/index');
