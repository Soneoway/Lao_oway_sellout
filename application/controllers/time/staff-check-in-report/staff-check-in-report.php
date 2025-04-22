<?php 

$current_month = date('F Y');
if ( date('Y-m-d') > date('Y-m-20') ) { $current_month = date('F Y', strtotime("+1 Month", strtotime(date('Y-m-d')))); }

$page       = $this->getRequest()->getParam('page', 1);
$staff_group= $this->getRequest()->getParam('staff_group');
$staff_code = $this->getRequest()->getParam('staff_code');
$staff_name = $this->getRequest()->getParam('staff_name');
$work_status= $this->getRequest()->getParam('work_status');
$area_id    = $this->getRequest()->getParam('area_id');
$export     = $this->getRequest()->getParam('export', 0);
$leave_type = $this->getRequest()->getParam('leave_type');
$late_type  = $this->getRequest()->getParam('late_type');
$date       = $this->getRequest()->getParam('month_year', $current_month);

$period_from = date('Y-m-21', strtotime("-1 Month", strtotime($date)));
$period_to = date('Y-m-20', strtotime($date));

$limit = 20;
$total = 0;

$params = array(
    'staff_group'   => $staff_group,
    'staff_code'    => $staff_code,
    'staff_name'    => $staff_name,
    'work_status'   => $work_status,
    'area_id'       => $area_id,
    'export'        => $export,
    'leave_type'    => $leave_type,
    'late_type'     => $late_type,
    'period_from'   => $period_from,
    'period_to'     => $period_to,
);

$group_checkin = array(
        PGPB_ID, SALES_ID, ASM_ID, ASMSTANDBY_ID, ABM_ID, BM_ID, TRAINING_TEAM_ID, 
        33, 34, 36, 37, 38, SALES_ADMIN_ID,31, AM_ID, 31);

//print_r($params);

$QGroup = new Application_Model_Group();
$where = $QGroup->getAdapter()->quoteInto('id IN (?)', $group_checkin);
$this->view->staff_groups = $QGroup->fetchAll($where, 'name');

$this->view->leave_type = array(
        1 => 'Sick Leave with Cert.',
        5 => 'Sick Leave without Cert.',
        2 => 'Bussiness Leave',
        3 => 'Annual Leave',
        4 => 'Off Date',
        6 => 'Ordination Leave',
        7 => 'Maternity Leave',
        8 => 'Other Leave',
        9 => 'Sterile Leave',
        10 => 'Marrirage Leave',
        11 => 'Burial Leave',
        12 => 'Military Training Leave',
        13 => 'Foreigner Go Home',
        14 => 'Wait Assign Shop',
    );

$this->view->late_type = array(
        1 => 'Have Late Time',
        2 => 'No Late Time',
    );

$QArea = new Application_Model_Area();
$this->view->areas = $QArea->fetchAll(null, 'name');

$userStorage = Zend_Auth::getInstance()->getStorage()->read();
//$this->view->group_id = $userStorage->group_id;

if (in_array($userStorage->group_id, My_Staff_Group::$allow_in_area_view) && !My_Staff_Permission_Area::view_all($userStorage->id))
    $params['asm'] = $userStorage->id;

// Trade Marketing Admin
if ($userStorage->group_id == 20)
    $params['tms_id'] = $userStorage->id;

// AM + AM Admin
if ($userStorage->group_id == AM_ID)
    $params['am_id'] = $userStorage->id;

// AM + AM Admin
if ($userStorage->group_id == 39)
    $params['admin_bs'] = $userStorage->id;

$QStaffCheckInLog = new Application_Model_StaffCheckInLog();

if ($export && $export == 1) {
    if ( is_null($params['staff_group']) ) { $params['staff_group'] = $group_checkin; }
    $checkin_export = $QStaffCheckInLog->exportCheckIn($params);

    $data = array();
    foreach ($checkin_export as $key => $value) {
        foreach ($value as $key2 => $value2) {
            $data[] = $value2;
        }
    }

    usort($data, function($a, $b) { return $a['staff_code'] - $b['staff_code']; });

    // echo "<pre>"; print_r($data); die;

    $this->_exportCheckInReport($data, $params);
}

// Check First Time Not Show Data
if (!empty($_GET)) { 
    if ( is_null($params['staff_group']) ) { $params['staff_group'] = $group_checkin; }
    $checkin_list = $QStaffCheckInLog->fetchPagination($page, $limit, $total, $params);
} else {
    $params['work_status'] = array(0,1);
}
// $params['get_total_count'] = 0;
// $total_count = $QTimingIssue->fetchPagination(null, null, $total, $params);

$this->view->params = $params;
$this->view->checkin_list = $checkin_list;
$this->view->total_count = $total_count;

$this->view->limit = $limit;
$this->view->total = $total;
$this->view->url = HOST.'time/staff-check-in-report/'.( $params ? '?'.http_build_query($params).'&' : '?' );
$this->view->offset = $limit*($page-1);

$flashMessenger = $this->_helper->flashMessenger;
$messages = $flashMessenger->setNamespace('success')->getMessages();
$this->view->messages = $messages;

if($this->getRequest()->isXmlHttpRequest()) {
    $this->_helper->layout->disableLayout();

    $this->_helper->viewRenderer->setRender('staff-check-in-report/partials/list');
} else
    $this->_helper->viewRenderer->setRender('staff-check-in-report/index');