<?php
$sort = $this->getRequest()->getParam('sort', '');
$desc = $this->getRequest()->getParam('desc', 1);
$id = $this->getRequest()->getParam('id');
$area_id = $this->getRequest()->getParam('area_id');
$regional_market = $this->getRequest()->getParam('regional_market');
$chk_bkk = $this->getRequest()->getParam('chk_bkk');
$export = $this->getRequest()->getParam('export', 0);
$userStorage = Zend_Auth::getInstance()->getStorage()->read();
$staff_code = $this->getRequest()->getParam('staff_code');
$staff_name = $this->getRequest()->getParam('staff_name');
$from = $this->getRequest()->getParam('from', date('d/m/Y'));
$to = $this->getRequest()->getParam('to', date('d/m/Y'));
$limit = LIMITATION;
$total = 0;

$params = array(
    'staff_code' => $staff_code,
    'staff_name' => $staff_name,
    'area_id' => $area_id,
    'regional_market' => $regional_market,
    'chk_bkk' => $chk_bkk,
    'from' => $from,
    'to' => $to,
    'id' => $id
);

$params['sort'] = $sort;
$params['desc'] = $desc;

$QArea = new Application_Model_Area();
$this->view->areas = $QArea->fetchAll(null, 'name');

$itemModel = new Application_Model_ApkDemoStatistics();

if (in_array($userStorage->group_id, My_Staff_Group::$allow_in_area_view) && !My_Staff_Permission_Area::view_all($userStorage->id))
    $params['asm'] = $userStorage->id;

$page = $this->getRequest()->getParam('page', 1);
$result = $itemModel->fetchPagination($page, $limit, $total, $params);

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
        $data = $itemModel->ExportByStore($params);
        $this->_exportApkDemoStatisticsByStore($data);
    } else if ($export == 2) {
        $data = $itemModel->ExportByStaff($params);
        $this->_exportApkDemoStatisticsByStaff($data);
    }
}
// print_r($result);

$this->view->db = $itemModel;

$this->view->list = $result;
$this->view->limit = $limit;
$this->view->total = $total;
$this->view->params = $params;
$this->view->desc = $desc;
$this->view->sort = $sort;
$this->view->url = HOST . 'trainer/apk-demo-statistics/' . ($params ? '?' . http_build_query($params) . '&' : '?');
$this->view->offset = $limit * ($page - 1);


$flashMessenger = $this->_helper->flashMessenger;
$messages = $flashMessenger->setNamespace('success')->getMessages();
$this->view->messages = $messages;
$messages_error = $flashMessenger->setNamespace('error')->getMessages();
$this->view->messages_error = $messages_error;

$this->_helper->viewRenderer->setRender('apk-demo-statistics/index');