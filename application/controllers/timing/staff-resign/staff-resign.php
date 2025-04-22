<?php

$page       = $this->getRequest()->getParam('page', 1);
$from       = $this->getRequest()->getParam('from', date('01/m/Y'));
$to         = $this->getRequest()->getParam('to', date('d/m/Y'));
$grand_area_id  = $this->getRequest()->getParam('grand_area_id');
$area_id    = $this->getRequest()->getParam('area_id');
$group_id   = $this->getRequest()->getParam('group_id');
$export     = $this->getRequest()->getParam('export');

$limit = 20;
$total = $total2 = 0;

$userStorage = Zend_Auth::getInstance()->getStorage()->read();

$params = array( 
    'from'          => $from,
    'to'            => $to,
    'grand_area_id' => $grand_area_id,
    'area_id'       => $area_id,
    'group_id'      => $group_id,
    'export'        => $export,
);

if ( in_array($userStorage->group_id, array(RM_ID, RMSTANDBY_ID, ASM_ID, ASMSTANDBY_ID, SALES_ADMIN_ID, TRAINING_TEAM_ID, 36)) ){
	$params['asm'] = $userStorage->id;
    $params['asm_group'] = $userStorage->group_id;
}

//print_r($params);

$QStaff     = new Application_Model_Staff();
$QArea      = new Application_Model_Area();
$QGroup     = new Application_Model_Group();
$QGrandArea = new Application_Model_GrandArea();


$where = $QGroup->getAdapter()->quoteInto('id IN (?)', array(4,5,9,31,16,28,35,37,38,17,36));
$this->view->groups = $QGroup->fetchAll($where, 'name');

if ( in_array($userStorage->group_id, array(RM_ID, RMSTANDBY_ID, ASM_ID, ASMSTANDBY_ID, SALES_ADMIN_ID, TRAINING_TEAM_ID, 36)) ) {

    $areas = $QArea->getAreaByAsmTable($userStorage->id);

    if ( in_array($userStorage->group_id, array(RM_ID, RMSTANDBY_ID)) ) {
        $grand_areas = $QGrandArea->getGrandAreaByAsmTable($userStorage->id);
    } else {
        $grand_areas = $QGrandArea->fetchAll(null, 'name');
    }
} else {
    $areas = $QArea->fetchAll(null, 'name');
    $grand_areas = $QGrandArea->fetchAll(null, 'name');
}

if (isset($grand_area_id) && $grand_area_id) {
    $areas = $QArea->getAreaByGrandArea($grand_area_id);
}

$this->view->areas = $areas;
$this->view->grand_areas = $grand_areas;

if ( isset($export) && $export ) {

    if ($export == 1) { 
        $staff_list = $QStaff->fetchPagination_StaffResign(null, null, $total2, $params);
        $this->_exportStaffResignList($staff_list); 
    }
    // if ($export == 2) { 
    //     $headcount_list = $QTiming->getHeadCountByPosition($params);
    //     $this->_exportHeadCountByPosition($headcount_list); 
    // }

    // if ($export == 3) {

    //     $rd_id = $this->getRequest()->getParam('rd_id');
    //     $type = $this->getRequest()->getParam('type');

    //     $staff_list = $QTiming->getStaffByRD($rd_id, $type);
    //     //print_r($staff_list); die;
    //     $this->_exportStaffByRD($staff_list); 
    // }

} 

$staff_list = $QStaff->fetchPagination_StaffResign($page, $limit, $total, $params);
//print_r($staff_list);

// Check First Time Not Show Data
if (!empty($_GET)) {
	//$list = $QOppoSaleTarget->getListBySaleAchieve($params);
} 

$this->view->params = $params;
$this->view->lists = $staff_list;

$this->view->offset      = $limit*($page-1);
$this->view->total       = $total;
$this->view->limit       = $limit;
$this->view->url         = HOST.'timing/staff-resign'.( $params ? '?'.http_build_query($params).'&' : '?' );

$flashMessenger = $this->_helper->flashMessenger;
$messages = $flashMessenger->setNamespace('success')->getMessages();
$this->view->messages = $messages;

if($this->getRequest()->isXmlHttpRequest()) {
    $this->_helper->layout->disableLayout();

    $this->_helper->viewRenderer->setRender('staff-resign/partials/list');
} else
    $this->_helper->viewRenderer->setRender('staff-resign/index');