<?php

$tmp_date = $this->getRequest()->getParam('selected_date', date('d/m/Y'));
$export = $this->getRequest()->getParam('export');


$userStorage = Zend_Auth::getInstance()->getStorage()->read();

$params = array( 
    'selected_date' => $tmp_date,
    'flag'          => 0,
);

$tmp = explode('/', $tmp_date);
$selected_date = $tmp[2].'-'.$tmp[1].'-'.$tmp[0];

if ( in_array($userStorage->group_id, array(RM_ID, RMSTANDBY_ID, ASM_ID, ASMSTANDBY_ID, SALES_ADMIN_ID)) ){
	$params['asm'] = $userStorage->id;
}

//print_r($params);

$QTiming = new Application_Model_Timing();
$QHeadcountTarget = new Application_Model_HeadcountTarget();

if ( isset($export) && $export ) {
    switch ($export) {
        // case 1:
        //     $headcount_list = $QTiming->getHeadCountByRD($params);
        //     $this->_exportHeadCountByRD($headcount_list); 
        //     break;
        case 2:
            $headcount_list = $QTiming->getHeadCountByPosition($params);
            $this->_exportHeadCountByPosition($headcount_list); 
            break;
        case 3:
            $rd_id = $this->getRequest()->getParam('rd_id');
            $type = $this->getRequest()->getParam('type');

            $staff_list = $QTiming->getStaffByRD($rd_id, $type);
            $this->_exportStaffByRD($staff_list); 
            break;
        default:
            break;
    }
} 

$now = date('Y-m-d');

$hc_target = $QHeadcountTarget->fetchAll();

$headcount_target = array();
for ($i=0;$i<count($hc_target);$i++) {
    $headcount_target[ $hc_target[$i]['rd_id'] ][ $hc_target[$i]['group_id'] ] = $hc_target[$i]['target'];
}

// Check Current Day
if (strtotime($selected_date) == strtotime($now)) {

    $headcount_list = $QTiming->getHeadCountByRD($params);
    $headcount_unique_lists = $QTiming->getHeadCountUnique($params);

    if ( isset($export) && $export == 1 ) { $this->_exportHeadCountByRD($headcount_list); }

} else {
    $params['flag'] = 1;

    $QStaff = new Application_Model_Staff();
    $QHeadcountLog = new Application_Model_HeadcountLog();

    $where = array();
    $where[] = $QHeadcountLog->getAdapter()->quoteInto('DATE(created_at) = ?', $selected_date);
    $headcount_list = $QHeadcountLog->fetchAll($where)->ToArray();

    $headcount_unique_lists = end($headcount_list);
    $headcount_unique_lists['cnt_total'] = $headcount_unique_lists['cnt_pc'] + 
        $headcount_unique_lists['cnt_sale'] + $headcount_unique_lists['cnt_sale_event'] + 
        $headcount_unique_lists['cnt_asm'] + $headcount_unique_lists['cnt_rd'] + $headcount_unique_lists['cnt_tms_leader'] +
        $headcount_unique_lists['cnt_tms'] + $headcount_unique_lists['cnt_pcm_leader'] + $headcount_unique_lists['cnt_pcm'];

    array_pop($headcount_list);

    for ($i=0;$i<count($headcount_list);$i++) {
        $staff = $QStaff->find($headcount_list[$i]['rd_id']);
        $staff = $staff->current();

        $headcount_list[$i]['rd_code'] = $staff['code'];
        $headcount_list[$i]['rd_name'] = $staff['firstname']." ".$staff['lastname'];
    }

    if ( isset($export) && $export == 1 ) { $this->_exportHeadCountByRD($headcount_list); }
}

// Check First Time Not Show Data
// if (!empty($_GET)) {	} 

$this->view->params = $params;
$this->view->lists = $headcount_list;
$this->view->unique_lists = $headcount_unique_lists;
$this->view->target_lists = $headcount_target;

$flashMessenger = $this->_helper->flashMessenger;
$messages = $flashMessenger->setNamespace('success')->getMessages();
$this->view->messages = $messages;

if($this->getRequest()->isXmlHttpRequest()) {
    $this->_helper->layout->disableLayout();

    $this->_helper->viewRenderer->setRender('headcount/partials/list');
} else
    $this->_helper->viewRenderer->setRender('headcount/index');