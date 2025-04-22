<?php
$page = $this->getRequest()->getParam('page', 1);
$store_id = $this->getRequest()->getParam('store_id');
$store_name = $this->getRequest()->getParam('store_name');
$staff_code = $this->getRequest()->getParam('staff_code');
$staff_name = $this->getRequest()->getParam('staff_name');
$market_name = $this->getRequest()->getParam('market_name');
$area_id = $this->getRequest()->getParam('area_id');
$group_id = $this->getRequest()->getParam('group_id');
$type_report = $this->getRequest()->getParam('type_report');
$export = $this->getRequest()->getParam('export', 0);
$export_wait = $this->getRequest()->getParam('export_wait', 0);
$update = $this->getRequest()->getParam('update');
$off = $this->getRequest()->getParam('off', 1);
$month_year_ex = $this->getRequest()->getParam('month_year');

$limit = LIMITATION;
$total = 0;

$month_year = date("Y-m");
$start_date = date("Y-m-d", strtotime($month_year . "-21" . "-1 Month"));
$end_date = date('Y-m-d', strtotime($month_year . "-20"));

$params = array(
    'group_id' => $group_id,
    'store_id' => $store_id,
    'store_name' => $store_name,
    'staff_code' => $staff_code,
    'staff_name' => $staff_name,
    'market_name' => $market_name,
    'area_id' => $area_id,
    'start_date' => $start_date,
    'export' => $export,
    'off' => $off,
);

$db = Zend_Registry::get('db');
$QPcCheckInLog = new Application_Model_PcCheckInLog();
$QSalesCheckInLog = new Application_Model_SalesCheckInLog();
$QBmCheckInLog = new Application_Model_BmCheckInLog();
$QTmsCheckInLog = new Application_Model_TmsCheckInLog();
$QPcmCheckInLog = new Application_Model_PcmCheckInLog();

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

$sale_list1 = array(ASM_ID, SALES_ID, 31, 33, 34);


if ($export_wait && $export_wait == 1) {

    for ($i = 0; $i < count($_GET['group_id']); $i++) {
        if ($_GET['group_id'][$i] == 4) {
            $approve_wait[$i] = $QPcCheckInLog->loadApproveWaitingExcel($area_id, $start_date_export, $end_date_export);

            // echo '<pre>';
            // print_r($approve_wait);
            // die;

        } elseif (in_array($_GET['group_id'][$i], $sale_list1)) {
            $approve_wait[$i] = $QSalesCheckInLog->loadApproveWaitingExcel($area_id, $start_date_export, $end_date_export);

            // echo '<pre>';
            // print_r($approve_wait);
            // die;

        } elseif ($_GET['group_id'][$i] == 34) {

            $approve_wait[$i] = $QSalesCheckInLog->loadAsmAssApproveWaitingExcel($area_id, $start_date_export, $end_date_export);

            // echo '<pre>';
            // print_r($approve_wait);

        } elseif ($_GET['group_id'][$i] == TMS) {

            $approve_wait[$i] = $QTmsCheckInLog->loadTmsApproveWaitingExcel($area_id, $start_date_export, $end_date_export);

            // echo '<pre>';
            // print_r($approve_wait);


        } elseif ($_GET['group_id'][$i] == PCM) {

            $approve_wait[$i] = $QPcmCheckInLog->loadApproveWaitingExcel($area_id, $start_date_export, $end_date_export);

            // echo '<pre>';
            // print_r($approve_wait);
        }

        $get = array(
            'area_name' => 'ar.name',
        );
        $select = $db->select()
            ->from(array('ar' => 'area'), $get)
            ->where("ar.id IN (?)", $area_id)
            ->order('area_name ASC');

        $area_name = $db->fetchAll($select);

        for ($i = 0; $i < count($approve_wait); $i++) {
            for ($j = 0; $j < count($approve_wait[$i]); $j++) {
                $approve[] = $approve_wait[$i][$j];
            }
        }
        //      echo '<pre>';
        // print_r($approve);
        // die;

        $this->check_approve_wait_export($date_title, $approve, $area_name);
    }
}


if ($export && $export == 1) {
//  print_r ($_GET['group_id']);
// exit;
    for ($i = 0; $i < count($_GET['group_id']); $i++) {

        if ($_GET['group_id'][$i] == BM_ID) {
            $report_check_in[$i] = $QBmCheckInLog->loadReportExcelAllCheckIn($area_id, $start_date_export, $end_date_export);
        } elseif ($_GET['group_id'][$i] == 32) { //ABM
            $report_check_in[$i] = $QBmCheckInLog->loadAbmReportExcelAllCheckIn($area_id, $start_date_export, $end_date_export);
        } elseif ($_GET['group_id'][$i] == 4) {
            $report_check_in[$i] = $QPcCheckInLog->loadReportExcelAllCheckIn($area_id, $start_date_export, $end_date_export);
        } elseif ($_GET['group_id'][$i] == SALES_ID) {

            $report_check_in[$i] = $QSalesCheckInLog->loadReportExcelAllCheckIn($area_id, $start_date_export, $end_date_export);

        } elseif ($_GET['group_id'][$i] == 31) {

            $report_check_in[$i] = $QSalesCheckInLog->loadSalesEventReportExcelAllCheckIn($area_id, $start_date_export, $end_date_export);
        } elseif ($_GET['group_id'][$i] == 5) {

            $report_check_in[$i] = $QSalesCheckInLog->loadAsmReportExcelAllCheckIn($area_id, $start_date_export, $end_date_export);
        } elseif ($_GET['group_id'][$i] == 34) {

            $report_check_in[$i] = $QSalesCheckInLog->loadAsmAssReportExcelAllCheckIn($area_id, $start_date_export, $end_date_export);
        } elseif ($_GET['group_id'][$i] == 33) {

            $report_check_in[$i] = $QSalesCheckInLog->loadRmAssReportExcelAllCheckIn($area_id, $start_date_export, $end_date_export);
        } elseif ($_GET['group_id'][$i] == TMS) {

            $report_check_in[$i] = $QTmsCheckInLog->loadTmsReportExcelAllCheckIn($area_id, $start_date_export, $end_date_export);
        } elseif ($_GET['group_id'][$i] == PCM) {

            $report_check_in[$i] = $QPcmCheckInLog->loadPcmReportExcelAllCheckIn($area_id, $start_date_export, $end_date_export);
        }

    }

    $get = array(
        'area_name' => 'ar.name',
    );
    $select = $db->select()
        ->from(array('ar' => 'area'), $get)
        ->where("ar.id IN (?)", $area_id)
        ->order('area_name ASC');
    $area_name = $db->fetchAll($select);

    for ($i = 0; $i < count($report_check_in); $i++) {
        for ($j = 0; $j < count($report_check_in[$i]); $j++) {
            $report[] = $report_check_in[$i][$j];
        }
    }
    //  echo '<pre>';
    // print_r($report);
    // die;
    $this->check_in_by_area_export($date_title, $report, $area_name);
//        echo '<pre>';
//        print_r($report);
//        die;

}


$sum_total = 0;

if (!empty($_GET['group_id'])) {

    $sale_list = array(SALES_ID, 31, 33, 34, ASM_ID);

    for ($i = 0; $i < count($_GET['group_id']); $i++) {
        if (in_array($_GET['group_id'][$i], $sale_list)) {
            $result[$i] = $QSalesCheckInLog->fetchPagination($page, null, $totals, $params);
            for ($i = 0; $i < count($_GET['group_id']); $i++) {

            }
        }
    }

    $bm_list = array(BM_ID, 32);

    for ($i = 0; $i < count($_GET['group_id']); $i++) {
        if (in_array($_GET['group_id'][$i], $bm_list)) {
            $result[$i] = $QBmCheckInLog->fetchPagination($page, null, $totalb, $params);
            for ($i = 0; $i < count($_GET['group_id']); $i++) {

            }
        }
    }
    // echo  $totals;

// echo count($_GET['group_id']);
    for ($i = 0; $i < count($_GET['group_id']); $i++) {

        //BM
//        if ($_GET['group_id'][$i] == BM_ID) {
//            $result[$i] = $QBmCheckInLog->fetchPagination($page,null, $totalb, $params);
//            // echo $total;
//        }
        if ($_GET['group_id'][$i] == PGPB_ID) {
            $result[$i] = $QPcCheckInLog->fetchPagination($page, null, $totalp, $params);

        } elseif ($_GET['group_id'][$i] == TMS) {
            $result[$i] = $QTmsCheckInLog->fetchPagination($page, null, $totaltms, $params);
        } elseif ($_GET['group_id'][$i] == PCM) {
            $result[$i] = $QPcmCheckInLog->fetchPagination($page, null, $totalpcm, $params);
        }
        // $sum_total =$sum_total + $total;
        // // echo $i;
        // echo  $sum_total ;
    }
    // $sum_total= $totalb +$totals+$totalp+ $totaltms+$totalpcm;
    // echo $sum_total;
}


// echo "<pre>";
// print_r($result);
$pc_check = array();
$pc_check_in = array();
for ($i = 0; $i < count($result); $i++) {
    for ($j = 0; $j < count($result[$i]); $j++) {
        $pc_check[] = $result[$i][$j];
    }
}
// echo "<pre>";
//  print_r($pc_check_in); 

for ($k = 0; $k < 100; $k++) {
    if (!empty($pc_check[$k])) {
        $pc_check_in[] = $pc_check[$k];
    }
}


$this->view->params = $params;
$this->view->pc_check_in = $pc_check_in;
$this->view->total_count = $total_count;

$this->view->limit = $limit;
$this->view->total = $sum_total;
$this->view->url = HOST . 'trainer/report-check-in/' . ($params ? '?' . http_build_query($params) . '&' : '?');
$this->view->offset = $limit * ($page - 1);

$flashMessenger = $this->_helper->flashMessenger;
$messages = $flashMessenger->setNamespace('success')->getMessages();
$this->view->messages = $messages;

if ($this->getRequest()->isXmlHttpRequest()) {
    $this->_helper->layout->disableLayout();

    $this->_helper->viewRenderer->setRender('report-check-in/partials/list');
} else
    $this->_helper->viewRenderer->setRender('report-check-in/index');

