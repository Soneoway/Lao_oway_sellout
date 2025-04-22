<?php
$page            = $this->getRequest()->getParam('page', 1);
$area_id         = $this->getRequest()->getParam('area_id');
$head_id         = $this->getRequest()->getParam('head_id');
$staff_group     = $this->getRequest()->getParam('staff_group');
$regional_market = $this->getRequest()->getParam('regional_market');
$staff_code      = $this->getRequest()->getParam('staff_code');
$staff_name      = $this->getRequest()->getParam('staff_name');
$from            = $this->getRequest()->getParam('from', date('d/m/Y'));
$to              = $this->getRequest()->getParam('to', date('d/m/Y'));
$export          = $this->getRequest()->getParam('export', 0);
$chk_bkk         = $this->getRequest()->getParam('chk_bkk');

$limit = LIMITATION;
$total = 0;

$params = array(
    'staff_code'      => $staff_code,
    'staff_name'      => $staff_name,
    'area_id'         => $area_id,
    'head_id'         => $head_id,
    'staff_group'     => $staff_group,
    'regional_market' => $regional_market,
    'from'            => $from,
    'to'              => $to,
    'export'          => $export,
    'chk_bkk'         => $chk_bkk,
);

$QuestionsAnswerLog = new Application_Model_QuestionsAnswerLog();

//print_r($params);

$userStorage = Zend_Auth::getInstance()->getStorage()->read();
//$this->view->group_id = $userStorage->group_id;

if (in_array($userStorage->group_id, My_Staff_Group::$allow_in_area_view) && !My_Staff_Permission_Area::view_all($userStorage->id))
    $params['asm'] = $userStorage->id;

$QArea = new Application_Model_Area();
$this->view->areas = $QArea->fetchAll(null, 'name');

$this->view->topic = $QuestionsAnswerLog->getQuestionsHeader();

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
        $staff_list = $QuestionsAnswerLog->fetchPagination(null, null, $total, $params);
        $this->_exportEtestAnswerExcel($staff_list, $params);
    }
}

$staff_list = $QuestionsAnswerLog->fetchPagination($page, $limit, $total, $params);

$group_checkin = array(
    PGPB_ID,
    SALES_ID,
    31,
    TRAINING_TEAM_ID,
    36,
    ASM_ID,
);

$QGroup = new Application_Model_Group();
$where = $QGroup->getAdapter()->quoteInto('id IN (?)', $group_checkin);
$this->view->staff_groups = $QGroup->fetchAll($where, 'name');

$this->view->params = $params;
$this->view->staff_list = $staff_list;
$this->view->user_storage = $userStorage->id;

$this->view->limit = $limit;
$this->view->total = $total;
$this->view->url = HOST.'trainer/report-etest-answer/'.( $params ? '?'.http_build_query($params).'&' : '?' );
$this->view->offset = $limit*($page-1);

$flashMessenger = $this->_helper->flashMessenger;
$messages = $flashMessenger->setNamespace('success')->getMessages();
$this->view->messages = $messages;

if($this->getRequest()->isXmlHttpRequest()) {
    $this->_helper->layout->disableLayout();

    $this->_helper->viewRenderer->setRender('report-etest-answer/partials/list');
} else
    $this->_helper->viewRenderer->setRender('report-etest-answer/index');