<?php

// Range Date of Sellout
$from   = $this->getRequest()->getParam('from', date('01/m/Y'));
$to     = $this->getRequest()->getParam('to', date('d/m/Y'));

$date   = $this->getRequest()->getParam('month_year', date('Y-m-d'));
$area  = $this->getRequest()->getParam('area');
$export = $this->getRequest()->getParam('export');

$last_03 = date('M-Y', strtotime("-3 Month", strtotime($date)));
$last_02 = date('M-Y', strtotime("-2 Month", strtotime($date)));
$last_01 = date('M-Y', strtotime("-1 Month", strtotime($date)));

$params = array( 
    'area'          => $area,
    'date'          => $date,
    'target_from'   => date('Y-m-01', strtotime($date)),
    'target_to'     => date('Y-m-t', strtotime($date)),
    'from'          => $from,
    'to'            => $to,
    'last_03'       => $last_03,
	'last_02'       => $last_02,
	'last_01'       => $last_01,
    'export'        => $export
);

$userStorage = Zend_Auth::getInstance()->getStorage()->read();

// if (in_array($userStorage->group_id, My_Staff_Group::$allow_in_area_view) && !My_Staff_Permission_Area::view_all($userStorage->id))
//     $params['asm'] = $userStorage->id;

$QArea = new Application_Model_Area();
$QOppoAreaTarget = new Application_Model_OppoAreaTarget();

$hero = $QOppoAreaTarget->getheroproduct();

$QOppoPcmTarget = new Application_Model_OppoPcmTarget();



$where = array();

// Add Filter Area
if (isset($params['area']) && $params['area']) {
    $where[] = $QArea->getAdapter()->quoteInto('id IN (?)', $params['area']);
}

if (in_array($userStorage->group_id, array(RM_ID,RMSTANDBY_ID))) {

    $QAsm = new Application_Model_Asm();
    $where2 = $QAsm->getAdapter()->quoteInto('staff_id = ?', $userStorage->id);
    $asm_list = $QAsm->fetchAll($where2);

    $area_rd = array();
    foreach ($asm_list as $key => $value) { $area_rd[] = $value['area_id']; }

    $params['area_rd'] = $area_rd;
    
    $where[] = $QArea->getAdapter()->quoteInto('id IN (?)', $area_rd);
    $where[] = $QArea->getAdapter()->quoteInto('id NOT IN (?)', array('48','49','72') );
    $areas = $QArea->fetchAll($where, 'name');
} else {

    $where[] = $QArea->getAdapter()->quoteInto('id NOT IN (?)', array('48','49','72') );
    $areas = $QArea->fetchAll($where, 'name');
}

$areas_list = $QArea->fetchAll(null, 'name');

// Export Excecl 
if ($export) {

    switch ($export) {
        case 1:
            $target_list = $QOppoAreaTarget->getOverViewTarget($params);
            $this->_exportOverViewTarget($target_list,$params);
            break;
            
        // Sale Targett
        case 2:
            $QOppoSaleTarget = new Application_Model_OppoSaleTarget();

            $params['from'] = $params['target_from'];
            $params['to'] = $params['target_to'];
            $params['sale_target'] = 1;

            $sale_list = $QOppoSaleTarget->GetSelloutData($params);
            $this->_exportSaleSetTarget($sale_list,$params);
            break;

        // PC Targett
        case 3:
            $pc_list = $QOppoAreaTarget->getPCTracking($params);
            $this->_exportPCTarget($pc_list,$params);
            break;

        case 4:
            $sellout = $QOppoAreaTarget->getDailySelloutByPC($params);
            $this->_exportDailySelloutByPC($sellout,$params);
            break;

        case 5:
            $params['by_shop'] = 1;
            $sellout = $QOppoAreaTarget->getDailySelloutByPC($params);
            $this->_exportDailySelloutByPC($sellout,$params);
            break;

        // Store Targett
        case 6:
            $sellout = $QOppoAreaTarget->getStoreTargetBySale($params);
            $this->_exportStoreTargetBySale($sellout,$params);
            break;

        //export area target
        case 8:
            $params['from'] = $params['target_from'];
            $params['to'] = $params['target_to'];

            $sellout = $QOppoAreaTarget->getareatarget($params);
            $this->_exportareatarget($sellout,$params);
            break;

            // Export PCM
            case 11:
            $params['from'] = $params['target_from'];
            $params['to'] = $params['target_to'];

            $sellout = $QOppoPcmTarget->getExportdata($params);
            $this->_ExportPCMTargetSellOut($sellout,$params);
            break;

            //Export Sale MKT
            case 12:
            $params['from'] = $params['target_from'];
            $params['to'] = $params['target_to'];
            
            $sellout = $QOppoAreaTarget->getMtkSelloutData($params);
            $this->_ExportMKTTargetSellOut($sellout,$params);
            break;

            //Export ASM Target
            case 13:
            $params['from'] = $params['target_from'];
            $params['to'] = $params['target_to'];
            
            $sellout = $QOppoAreaTarget->getAsmSelloutData($params);
            $this->_ExportASMTargetSellOut($sellout,$params);
            break;

        default:
            break;
    }

}

// Get Sellout Last 3 Month
$sale = $QOppoAreaTarget->getLast3MonthByArea($params);

// Get Current Area Target
$where_target[] = $QOppoAreaTarget->getAdapter()->quoteInto('from_date >= ?', $params['target_from']." 00:00:00");
$where_target[] = $QOppoAreaTarget->getAdapter()->quoteInto('to_date <= ?', $params['target_to']." 23:59:59");
$area_target = $QOppoAreaTarget->fetchAll($where_target);

$result_target = array();
for ($i=0;$i<count($area_target);$i++) {

    $result_target[ $area_target[$i]['area_id'] ]['target_hero'] = $area_target[$i]['target_hero'];
    $result_target[ $area_target[$i]['area_id'] ]['target'] = $area_target[$i]['target'];
    $result_target[ $area_target[$i]['area_id'] ]['id'] = $area_target[$i]['id'];

}

$this->view->params = $params;
$this->view->areas = $areas;
$this->view->areas_list = $areas_list;
$this->view->sellout = $sale;
$this->view->areas_target = $result_target;
$this->view->getheroproduct = $hero;

$flashMessenger = $this->_helper->flashMessenger;
$messages = $flashMessenger->setNamespace('success')->getMessages();
$this->view->messages = $messages;

if($this->getRequest()->isXmlHttpRequest()) {
    $this->_helper->layout->disableLayout();

    $this->_helper->viewRenderer->setRender('oppo-area-target/partials/list');
} else
    $this->_helper->viewRenderer->setRender('oppo-area-target/index');