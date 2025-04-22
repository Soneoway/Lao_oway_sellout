<?php

$from       = $this->getRequest()->getParam('from', date('01/m/Y'));
$to         = $this->getRequest()->getParam('to', date('d/m/Y'));
$good_id    = $this->getRequest()->getParam('good_id');
$color_id   = $this->getRequest()->getParam('color_id');
$export     = $this->getRequest()->getParam('export');

$doi_rate = 14;

$userStorage = Zend_Auth::getInstance()->getStorage()->read();

$params = array( 
    'doi_rate'      => $doi_rate,
    'from'          => $from,
    'to'            => $to,
    'good_id'       => $good_id,
    'color_id'      => $color_id,
    'export'        => $export,
);

if ( in_array($userStorage->group_id, array(RM_ID, RMSTANDBY_ID, ASM_ID, ASMSTANDBY_ID)) ){
	$params['asm'] = $userStorage->id;
    $params['asm_group'] = $userStorage->group_id;
}

if ( in_array($userStorage->group_id, array(AM_ID)) ){
    $params['am'] = $userStorage->id;
}

//print_r($params);

$QTiming    = new Application_Model_Timing();
$QGood      = new Application_Model_Good();
$GoodColorCombined = new Application_Model_GoodColorCombined();

$good_list = $QGood->get_cache2();

if (isset($good_id) && $good_id) {
    $color_list = $GoodColorCombined->getColorByModel($good_id);
}

$this->view->goods = $good_list;
$this->view->colors = $color_list;

/*
if ( isset($export) && $export ) {

    if ($export == 1) { 
        $distributor_list = $QTiming->fetchPagination_DOI_EX($params);
        $this->_exportDOI_EX_List($distributor_list); 
    }

} 
*/

// Check First Time Not Show Data
if (!empty($_GET)) {
	$distributor_list = $QTiming->fetchPagination_DOI_EX($params);
} 

$this->view->params = $params;
$this->view->lists = $distributor_list;

$flashMessenger = $this->_helper->flashMessenger;
$messages = $flashMessenger->setNamespace('success')->getMessages();
$this->view->messages = $messages;

if($this->getRequest()->isXmlHttpRequest()) {
    $this->_helper->layout->disableLayout();

    $this->_helper->viewRenderer->setRender('distributor-doi-ex/partials/list');
} else
    $this->_helper->viewRenderer->setRender('distributor-doi-ex/index');