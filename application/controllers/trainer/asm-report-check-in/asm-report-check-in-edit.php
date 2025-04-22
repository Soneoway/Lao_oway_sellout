<?php

$staff_id=$this->getRequest()->getParam('id');
$staff_code=$this->getRequest()->getParam('staff_code');
$store_id-$this->getRequest()->getParam('store_id');
$month_year = $this->getRequest()->getParam('month_year');
$export = $this->getRequest()->getParam('export', 0);

$start_date = date("Y-m-d", strtotime($month_year . "-21" . "-1 Month"));
$end_date = date('Y-m-d', strtotime($month_year . "-20"));

$QSalesCheckInLog= new Application_Model_SalesCheckInLog();

if (isset($staff_code) && $staff_code) {
    $get_staff_id = $QSalesCheckInLog->getStaffId($staff_code);
    $staff_id = $get_staff_id;
}
$sales_check_in_detail = $QSalesCheckInLog->getDetails($staff_id);
$this->view->sales_check_in_detail = $sales_check_in_detail;
$params = array(
    'start_date'        => $start_date,
    'end_date'          => $end_date,
);

if (!empty($month_year)) {
    $list_sales_check_in = $QSalesCheckInLog->getSalesCheckIn($staff_id, $store_id, $start_date, $end_date);
    $list_sales_leave = $QSalesCheckInLog->getSalesLeave($staff_id, $start_date, $end_date);
  


    $date_range = DateDiff($start_date, $end_date);

    $arrDayOfMonth = array();

    for ($i=0; $i<=$date_range; $i++) {
        $check_leave = $QSalesCheckInLog->CheckLeave($staff_id, date('Y-m-d',strtotime($start_date . "+$i days")));
        $arrDayOfMonth[$i] = array(
            "day_month" => date('d',strtotime($start_date . "+$i days")),
            "day_week" => date('Y-m-d',strtotime($start_date . "+$i days")),
            "action_id" => $check_leave['action_id'],
            "status_ap" => isset($check_leave['status_ap']) ? $check_leave['status_ap'] : "O",
            "mode_code" => $check_leave['mode_code'],
            "leave_remark" => $check_leave['leave_remark'],
            "approve_time" => $check_leave['approve_time'],
            "remark" => $check_leave['remark'],
            "certificate_file" => $check_leave['certificate_file'],
            "certificate_at" => $check_leave['certificate_at'],
        );
    }

    $leaveMerge = array_merge($list_sales_leave, $arrDayOfMonth);
    $checkInMerge = array_merge($list_sales_check_in,$leaveMerge);
    $mergeAllData = array_group_by(array_merge($list_sales_leave, $checkInMerge), 'day_week');

    usort($mergeAllData, build_sorter("day_week", "ASC"));
    
    $this->view->params = $params;
    
    $this->view->mergeAllData = $mergeAllData;

}
// echo '<pre>';
// print_r($list_sales_leave);


$userStorage = Zend_Auth::getInstance()->getStorage()->read();
$this->view->group_id = $userStorage->group_id;
$this->view->back_url = $this->getRequest()->getServer('HTTP_REFERER');
$this->_helper->viewRenderer->setRender('/asm-report-check-in/create');

function array_group_by($array, $group_by = 'id')
{
    $ids = array_column($array, $group_by);
    $ids = array_unique($ids);
    $result = array_filter($array, function ($key, $value) use ($ids) {
        return in_array($value, array_keys($ids));
    }, ARRAY_FILTER_USE_BOTH);

    return $result;
}

function DateDiff($strDate1, $strDate2)
{
    return (strtotime($strDate2) - strtotime($strDate1)) / (60 * 60 * 24);  // 1 day = 60*60*24
}

function build_sorter($key, $dir = 'ASC')
{
    return function ($a, $b) use ($key, $dir) {
        $t1 = strtotime(is_array($a) ? $a[$key] : $a->$key);
        $t2 = strtotime(is_array($b) ? $b[$key] : $b->$key);
        if ($t1 == $t2) return 0;
        return (strtoupper($dir) == 'ASC' ? ($t1 < $t2) : ($t1 > $t2)) ? -1 : 1;
    };
}