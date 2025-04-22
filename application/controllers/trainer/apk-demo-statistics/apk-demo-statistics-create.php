<?php
$id = $this->getRequest()->getParam('id');
$from = $this->getRequest()->getParam('from');
$to = $this->getRequest()->getParam('to');

$params = array(
    'id' => $id,
    'from' => $from,
    'to' => $to
);

$db = Zend_Registry::get('db');
$select = $db->select()
    ->from(array('ast' => 'apk_statistics'));
$select->where('ast.id = ?', $params['id']);

$apk_detail = $db->fetchRow($select);

$ApkDemoStatistic = new Application_Model_ApkDemoStatistics();
$apk_demo = $ApkDemoStatistic->ApkDemoStatisticDetail($params);

$this->view->params = $params;
$this->view->data = $apk_demo;
$this->view->detail = $apk_detail;

$userStorage = Zend_Auth::getInstance()->getStorage()->read();
$this->view->group_id = $userStorage->group_id;

//back url
$this->view->back_url = $this->getRequest()->getServer('HTTP_REFERER');

$this->_helper->viewRenderer->setRender('/apk-demo-statistics/create');