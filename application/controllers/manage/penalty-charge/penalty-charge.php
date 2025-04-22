<?php

$penalty_type_id = $this->getRequest()->getParam('penalty_type_id',2);
$from_date       = $this->getRequest()->getParam('from', date('01/m/Y'));
$to_date         = $this->getRequest()->getParam('to', date('d/m/Y'));
$export          = $this->getRequest()->getParam('export');

$params = array(
    'penalty_type_id'   => $penalty_type_id,
    'from'              => $from_date,
    'to'                => $to_date,
    'export'            => $export,
);

// print_r($params);

$userStorage = Zend_Auth::getInstance()->getStorage()->read();
//$this->view->group_id = $userStorage->group_id;

$QPunishMemo = new Application_Model_PunishMemo();
$QPunishMemoStore = new Application_Model_PunishMemoStore();

if ($export && $export == 1) {
    $penalty_list = $QPunishMemo->getList($params);
    $this->_exportPenaltyList($penalty_list,$params);
}


// Check First Time Not Show Data
if (!empty($_GET)) { 

    if ($penalty_type_id == 1) {
        // By Store
        $penalty_list = $QPunishMemoStore->getList($params);
    } else {
        // By Staff 
        $penalty_list = $QPunishMemo->getList($params);
    }

    //print_r($penalty_list);
}

$penalty_type = array(
    '0'  => array('id' => '1', 'name' => 'By Store'),
    '1'  => array('id' => '2', 'name' => 'By Staff'),
);

$this->view->params = $params;
$this->view->penalty_type = $penalty_type;
$this->view->penalty_list = $penalty_list;

$flashMessenger = $this->_helper->flashMessenger;
$messages = $flashMessenger->setNamespace('success')->getMessages();
$this->view->messages = $messages;

if($this->getRequest()->isXmlHttpRequest()) {
    $this->_helper->layout->disableLayout();

    //$this->_helper->viewRenderer->setRender('penalty-charge/partials/list2');
} else
    $this->_helper->viewRenderer->setRender('penalty-charge/index');