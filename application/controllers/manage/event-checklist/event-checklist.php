<?php
$page = $this->getRequest()->getParam('page', 1);
$area_id = $this->getRequest()->getParam('area_id');
$event_id = $this->getRequest()->getParam('event_id');
$regional_market = $this->getRequest()->getParam('regional_market');
$staff_code = $this->getRequest()->getParam('staff_code');
$staff_name = $this->getRequest()->getParam('staff_name');
$from = $this->getRequest()->getParam('from', date('01/m/Y'));
$to = $this->getRequest()->getParam('to', date('d/m/Y'));
$export = $this->getRequest()->getParam('export', 0);
$get_money = $this->getRequest()->getParam('get_money');
$get_food = $this->getRequest()->getParam('get_food');
$chk_bkk = $this->getRequest()->getParam('chk_bkk');

$limit = LIMITATION;
$total = 0;

$params = array(
    'event_id' => $event_id,
    'staff_code' => $staff_code,
    'staff_name' => $staff_name,
    'area_id' => $area_id,
    'regional_market' => $regional_market,
    'from' => $from,
    'to' => $to,
    'export' => $export,
    'get_money' => $get_money,
    'get_food' => $get_food,
    'chk_bkk' => $chk_bkk,
);

$QPcCheckInLog = new Application_Model_PcCheckInLog();

$userStorage = Zend_Auth::getInstance()->getStorage()->read();
//$this->view->group_id = $userStorage->group_id;

if (in_array($userStorage->group_id, My_Staff_Group::$allow_in_area_view) && !My_Staff_Permission_Area::view_all($userStorage->id))
    $params['asm'] = $userStorage->id;

$db = Zend_Registry::get('db');

$QEvent = $db->select()
    ->from(array('e' => 'events'));

$event = $db->fetchAll($QEvent);

$QArea = new Application_Model_Area();
$this->view->areas = $QArea->fetchAll(null, 'name');

$QRegionalMarket = new Application_Model_RegionalMarket();

if ($area_id) {
    if (is_array($area_id) && count($area_id))
        $where = $QRegionalMarket->getAdapter()->quoteInto('area_id IN (?)', $area_id);
    else
        $where = $QRegionalMarket->getAdapter()->quoteInto('area_id = ?', $area_id);

    $this->view->regional_markets = $QRegionalMarket->fetchAll($where, 'name');
}

if ($export) {
    if ($export == 1) {
    }
}

$staff_list = $QPcCheckInLog->eventCheckListPagination($page, $limit, $total, $params);

$this->view->params = $params;
$this->view->event = $event;
$this->view->staff_list = $staff_list;

$this->view->limit = $limit;
$this->view->total = $total;
$this->view->url = HOST . 'manage/event-checklist/' . ($params ? '?' . http_build_query($params) . '&' : '?');
$this->view->offset = $limit * ($page - 1);

$flashMessenger = $this->_helper->flashMessenger;
$messages = $flashMessenger->setNamespace('success')->getMessages();
$this->view->messages = $messages;

if ($this->getRequest()->isXmlHttpRequest()) {
    $this->_helper->layout->disableLayout();

    $this->_helper->viewRenderer->setRender('event-checklist/partials/list');
} else
    $this->_helper->viewRenderer->setRender('event-checklist/index');