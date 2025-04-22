<?php

$from       = $this->getRequest()->getParam('from', date('01/m/Y'));
$to         = $this->getRequest()->getParam('to', date('d/m/Y'));
$export     = $this->getRequest()->getParam('export', 0);

$oppo_product_id    = $this->getRequest()->getParam('oppo_product_id');
$huawei_product_id  = $this->getRequest()->getParam('huawei_product_id');
$vivo_product_id    = $this->getRequest()->getParam('vivo_product_id');
$samsung_product_id = $this->getRequest()->getParam('samsung_product_id');

$params = array(
    'from'               => $from,
    'to'                 => $to, 
    'export'             => $export,
    'oppo_product_id'    => $oppo_product_id,
    'huawei_product_id'  => $huawei_product_id,
    'vivo_product_id'    => $vivo_product_id,
    'samsung_product_id' => $samsung_product_id,
);

// print_r($params);

$userStorage = Zend_Auth::getInstance()->getStorage()->read();
//$this->view->group_id = $userStorage->group_id;

if (in_array($userStorage->group_id, My_Staff_Group::$allow_in_area_view) && !My_Staff_Permission_Area::view_all($userStorage->id))
    $params['asm'] = $userStorage->id;

if ($userStorage->group_id == AM_ID) 
    $params['am'] = $userStorage->id;

$QCompetitorRecord = new Application_Model_CompetitorRecord();
$QCompetitorProduct = new Application_Model_CompetitorProduct(); 
$QGood = new Application_Model_Good(); 

// OPPO 
$this->view->oppo_product_list = $QGood->get_cache2();

// Huawei
$where = array();
$where[] = $QCompetitorProduct->getAdapter()->quoteInto('status = ?', 0);
$where[] = $QCompetitorProduct->getAdapter()->quoteInto('brand_id = ?', 1);
$this->view->huawei_product_list = $QCompetitorProduct->fetchAll($where, 'name');

// VIVO
$where = array();
$where[] = $QCompetitorProduct->getAdapter()->quoteInto('status = ?', 0);
$where[] = $QCompetitorProduct->getAdapter()->quoteInto('brand_id = ?', 2);
$this->view->vivo_product_list = $QCompetitorProduct->fetchAll($where, 'name');

// Samsung
$where = array();
$where[] = $QCompetitorProduct->getAdapter()->quoteInto('status = ?', 0);
$where[] = $QCompetitorProduct->getAdapter()->quoteInto('brand_id = ?', 3);
$this->view->samsung_product_list = $QCompetitorProduct->fetchAll($where, 'name');

$this->view->brand_list = $brand_list;

$sellout_01 = $sellout_02 = array();

if (!empty($_GET)) { 

    if ( isset($oppo_product_id) && $oppo_product_id ) { 
        $sellout_01 = $QCompetitorRecord->getSelloutByModelOPPO($params); 
    }
	
    if ( (isset($huawei_product_id) && $huawei_product_id) || 
        (isset($vivo_product_id) && $vivo_product_id) || 
        (isset($samsung_product_id) && $samsung_product_id) ) 
    {
        $sellout_02 = $QCompetitorRecord->getSelloutByModelCompetitor($params);
    }
	

}

// echo "<pre>"; print_r($sellout_01);
// echo "<pre>"; print_r($sellout_02);

$sf_by_model = array_merge($sellout_01,$sellout_02);

// echo "<pre>"; print_r($sf_by_model);

$this->view->params = $params;
$this->view->sf_by_model = $sf_by_model;

$flashMessenger = $this->_helper->flashMessenger;
$messages = $flashMessenger->setNamespace('success')->getMessages();
$this->view->messages = $messages;

if($this->getRequest()->isXmlHttpRequest()) {
    $this->_helper->layout->disableLayout();

    $this->_helper->viewRenderer->setRender('store-focus-by-model/partials/list');
} else
    $this->_helper->viewRenderer->setRender('store-focus-by-model/index');