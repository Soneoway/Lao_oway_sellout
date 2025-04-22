<?php

$tmp_imei = $this->getRequest()->getParam('imei');
$export = $this->getRequest()->getParam('export');

if ($tmp_imei) { $imei = explode("\n", $tmp_imei); }

$params = array(
    'imei'      => $imei,
    'export'    => $export,
);

// print_r($params);

$userStorage = Zend_Auth::getInstance()->getStorage()->read();
//$this->view->group_id = $userStorage->group_id;

$QBypassImei = new Application_Model_BypassImei();

if ($export && $export == 1) {
    // $penalty_list = $QBypassImei->getList($params);
    // $this->_exportPenaltyList($penalty_list,$params);
}

// Check First Time Not Show Data
if (!empty($_GET)) { 
    $bypass_imei_list = $QBypassImei->getList($params);

    //print_r($bypass_imei_list);
}

$this->view->params = $params;
$this->view->bypass_imei_list = $bypass_imei_list;

$flashMessenger = $this->_helper->flashMessenger;
$messages = $flashMessenger->setNamespace('success')->getMessages();
$this->view->messages = $messages;

if($this->getRequest()->isXmlHttpRequest()) {
    $this->_helper->layout->disableLayout();

    $this->_helper->viewRenderer->setRender('bypass-imei/partials/list');
} else
    $this->_helper->viewRenderer->setRender('bypass-imei/index');