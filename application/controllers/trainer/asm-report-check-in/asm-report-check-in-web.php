
<?php
$this->_helper->layout->disableLayout();

$staff_id = $this->getRequest()->getParam('id');
$month_year = $this->getRequest()->getParam('start_date');

$userStorage = Zend_Auth::getInstance()->getStorage()->read();

$QSalesCheckInLog = new Application_Model_SalesCheckInLog();
$sales_check_in_detail = $QSalesCheckInLog->getDetails($staff_id);


$params = array(
    'staff_code' => $sales_check_in_detail['staff_code'],
    'staff_name' => $sales_check_in_detail['staff_name'],
    'area_name' => $sales_check_in_detail['area_name'],
    'store_id' => $sales_check_in_detail['store_id'],
    'store_name' => $sales_check_in_detail['store_name'],
    'pcm_code' => $userStorage->code,
    'pcm_name' => $userStorage->firstname . ' ' . $userStorage->lastname,
    'start_date' => $month_year,
    'joined_at' => $sales_check_in_detail['joined_at'],
    'off_date' => $sales_check_in_detail['off_date'],
    'created_at' => $sales_check_in_detail['created_at'],
    'first_check_in' => $sales_check_in_detail['first_check_in'],
    'first_leave' => $sales_check_in_detail['first_leave'],
);
function array_group_by($array, $group_by = 'id')
{
    $ids = array_column($array, $group_by);
    $ids = array_unique($ids);
    $result = array_filter($array, function ($key, $value) use ($ids) {
        return in_array($value, array_keys($ids));
    }, ARRAY_FILTER_USE_BOTH);

    return $result;
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

$start_date = date("Y-m-d", strtotime($month_year . "-21" . "-1 Month"));
$end_date = date('Y-m-d', strtotime($month_year . "-20"));

$date_range = (strtotime($end_date) - strtotime($start_date)) / (60 * 60 * 24);

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
$list_sales_check_in = $QSalesCheckInLog->getSalesCheckIn($staff_id, $pc_check_in_detail['store_id'], $start_date, $end_date);
$list_sales_leave = $QSalesCheckInLog->getSalesLeave($staff_id, $start_date, $end_date);


 $leaveMerge = array_merge($list_sales_leave, $arrDayOfMonth);
 $checkInMerge = array_merge($list_sales_check_in,$leaveMerge);
 $mergeAllData = array_group_by(array_merge($list_sales_leave, $checkInMerge), 'day_week');

usort($mergeAllData, build_sorter("day_week", "ASC"));

// Send Data to View
$this->view->params = $params;
$this->view->mergeAllData = $mergeAllData;
$this->_helper->viewRenderer->setRender('asm-report-check-in/asm-report-check-in-web');