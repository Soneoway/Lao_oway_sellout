<?php
// $h="hello";
// $this->view->h= $h;
$page            = $this->getRequest()->getParam('page', 1);
$store_id        = $this->getRequest()->getParam('store_id');
$store_name      = $this->getRequest()->getParam('store_name');
$staff_code      = $this->getRequest()->getParam('staff_code');
$staff_name      = $this->getRequest()->getParam('staff_name');
$market_name     = $this->getRequest()->getParam('market_name');
$area_id         = $this->getRequest()->getParam('area_id');
$export          = $this->getRequest()->getParam('export', 0);
$export_wait     = $this->getRequest()->getParam('export_wait', 0);
$update          = $this->getRequest()->getParam('update');
$off             = $this->getRequest()->getParam('off', 1);
$month_year_ex   = $this->getRequest()->getParam('month_year');

$limit = LIMITATION;
$total = 0;

$month_year = date("Y-m");
$start_date = date("Y-m-d", strtotime($month_year . "-21" . "-1 Month"));
$end_date = date('Y-m-d', strtotime($month_year . "-20"));

$params = array(
    'store_id'        => $store_id,
    'store_name'      => $store_name,
    'staff_code'      => $staff_code,
    'staff_name'      => $staff_name,
    'market_name'     => $market_name,
    'area_id'         => $area_id,
    'start_date'      => $start_date,
    'export'          => $export,
    'off'             => $off,
);

$db = Zend_Registry::get('db');
$QSalesCheckInLog = new Application_Model_AsmCheckInLog();

//echo '<pre>';
//print_r($params);

$userStorage = Zend_Auth::getInstance()->getStorage()->read();
//$this->view->group_id = $userStorage->group_id;

$QArea = $db->select()
    ->from(array('ar' => 'area'));

    if ($userStorage->group_id == TRAINING_TEAM_ID) {
        $QArea->join(array('a' => 'asm'), 'a.area_id = ar.id', array());
        $QArea->where("a.staff_id = ?", $userStorage->id);
    }

$QArea->order('ar.name ASC');

$area_list = $db->fetchAll($QArea);

// echo '<pre>';
// print_r($area_list);
$this->view->areas = $area_list;

if (in_array($userStorage->group_id, My_Staff_Group::$allow_in_area_view) && !My_Staff_Permission_Area::view_all($userStorage->id))
    $params['asm'] = $userStorage->id;

//check sale permission
if ($userStorage->group_id == SALES_ID)
    $params['sale_id'] = $userStorage->id;

//check pcm permission
if ($userStorage->group_id == PCM_ID)
    $params['pcm_id'] = $userStorage->id;

if ($userStorage->group_id == TRAINING_TEAM_ID)
    $params['training_id'] = $userStorage->id;

//check sale leader permission
if ($userStorage->group_id == LEADER_ID)
    $params['leader_id'] = $userStorage->id;
if ($userStorage->group_id == PGPB_ID)
    $params['pc_id'] = $userStorage->id;

$start_date_export = date("Y-m-d", strtotime($month_year_ex . "-21" . "-1 Month"));
$end_date_export = date('Y-m-d', strtotime($month_year_ex . "-20"));

$date_title = array(
    $start_date_export,
    $end_date_export,
);

if ($export_wait && $export_wait == 1) {

    $approve_wait = $QSalesCheckInLog->loadApproveWaitingExcel($area_id, $start_date_export, $end_date_export);

//    echo '<pre>';
//    print_r($approve_wait);

    $get = array(
        'area_name' => 'ar.name',
    );
    $select = $db->select()
        ->from(array('ar' => 'area'), $get)
        ->where("ar.id IN (?)", $area_id)
        ->order('area_name ASC');

    $area_name = $db->fetchAll($select);

    $this->sales_approve_wait_export($date_title, $approve_wait, $area_name);
}

if ($export && $export == 1) {

    $report_check_in = $QSalesCheckInLog->loadReportExcelAllCheckIn($area_id, $start_date_export, $end_date_export);

//    echo '<pre>';
//    print_r($report_check_in);
//    die;

    $get = array(
        'area_name' => 'ar.name',
    );
    $select = $db->select()
        ->from(array('ar' => 'area'), $get)
        ->where("ar.id IN (?)", $area_id)
        ->order('area_name ASC');

    $area_name = $db->fetchAll($select);

    $this->sales_check_in_by_area_export($date_title, $report_check_in, $area_name);
}

$sales_check_in = $QSalesCheckInLog->fetchPagination($page, $limit, $total, $params);

$params['get_total_count'] = 0;
$total_count = $QSalesCheckInLog->fetchPagination(null, null, $total, $params);


$this->view->params = $params;
$this->view->sales_check_in = $sales_check_in;
$this->view->total_count = $total_count;

$this->view->limit = $limit;
$this->view->total = $total;
$this->view->url = HOST.'trainer/sales-report-check-in/'.( $params ? '?'.http_build_query($params).'&' : '?' );
$this->view->offset = $limit*($page-1);


$flashMessenger = $this->_helper->flashMessenger;
$messages = $flashMessenger->setNamespace('success')->getMessages();
$this->view->messages = $messages;


if($this->getRequest()->isXmlHttpRequest()) {
    $this->_helper->layout->disableLayout();

    $this->_helper->viewRenderer->setRender('sales-report-check-in/partials/list');
} else
    $this->_helper->viewRenderer->setRender('sales-report-check-in/index');
