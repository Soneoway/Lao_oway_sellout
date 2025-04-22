<?php

$this->_helper->layout->disableLayout();

$current_month = date('F Y');
//if ( date('Y-m-d') > date('Y-m-20') ) { $current_month = date('F Y', strtotime("+1 Month", strtotime(date('Y-m-d')))); }

$staff_code = $this->getRequest()->getParam('code');
$group_id 	= $this->getRequest()->getParam('group_id');
$date       = $this->getRequest()->getParam('month_year', $current_month);

$period_from = date('Y-m-21', strtotime("-1 Month", strtotime($date)));
$period_to = date('Y-m-20', strtotime($date));

$params = array(
    'period_from'   => $period_from,
    'period_to'     => $period_to,
    'staff_code'    => $staff_code,
    'group_id'		=> $group_id,
);

$userStorage = Zend_Auth::getInstance()->getStorage()->read();

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

$staff_info = $QStaffCheckInLog->getStaffInfo($params);

if ( isset($staff_info['staff_id']) && $staff_info['staff_id'] ) {
	$params['staff_id'] = $staff_info['staff_id'];

	$leave_list = $QStaffCheckInLog->getLeaveList($params);
	$checkin_list = $QStaffCheckInLog->getCheckInList($params);
}

$this->view->params = $params;

$this->view->staff_info = $staff_info;
$this->view->leave_list = $leave_list;
$this->view->checkin_list = $checkin_list;

//back url
$this->view->back_url = $this->getRequest()->getServer('HTTP_REFERER');
$this->_helper->viewRenderer->setRender('/staff-check-in-report/print');