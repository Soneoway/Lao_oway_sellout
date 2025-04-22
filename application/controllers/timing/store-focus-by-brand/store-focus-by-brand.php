<?php

$from       = $this->getRequest()->getParam('from', date('01/m/Y'));
$to         = $this->getRequest()->getParam('to', date('d/m/Y'));
$export     = $this->getRequest()->getParam('export', 0);

$params = array(
    'from'      => $from,
    'to'        => $to, 
    'export'    => $export,
);

//print_r($params);

$userStorage = Zend_Auth::getInstance()->getStorage()->read();
//$this->view->group_id = $userStorage->group_id;

if (in_array($userStorage->group_id, My_Staff_Group::$allow_in_area_view) && !My_Staff_Permission_Area::view_all($userStorage->id))
    $params['asm'] = $userStorage->id;

if ($userStorage->group_id == AM_ID) 
    $params['am'] = $userStorage->id;

$QCompetitorRecord = new Application_Model_CompetitorRecord();

if (!empty($_GET)) { 

	$sellout_01 = $QCompetitorRecord->getSelloutByOPPO($params);
	$sellout_02 = $QCompetitorRecord->getSelloutByCompetitor($params);

}

// echo "<pre>"; print_r($sellout_01);
// echo "<pre>"; print_r($sellout_02);

$sf_by_brand = array_merge($sellout_01,$sellout_02);

// echo "<pre>"; print_r($sf_by_brand);

$this->view->params = $params;
$this->view->sf_by_brand = $sf_by_brand;

$flashMessenger = $this->_helper->flashMessenger;
$messages = $flashMessenger->setNamespace('success')->getMessages();
$this->view->messages = $messages;

if($this->getRequest()->isXmlHttpRequest()) {
    $this->_helper->layout->disableLayout();

    $this->_helper->viewRenderer->setRender('store-focus-by-brand/partials/list');
} else
    $this->_helper->viewRenderer->setRender('store-focus-by-brand/index');