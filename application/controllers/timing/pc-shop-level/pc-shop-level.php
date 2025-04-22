<?php

$from       = $this->getRequest()->getParam('from', date('01/m/Y'));
$to         = $this->getRequest()->getParam('to', date('d/m/Y'));
$area_id    = $this->getRequest()->getParam('area_id');
$store_type = $this->getRequest()->getParam('store_type');
$export     = $this->getRequest()->getParam('export');

$params = array(
    'from'          => $from,
    'to'            => $to,
    'area_id'       => $area_id,
    'store_type'    => $store_type,
    'export'        => $export,
);

$userStorage = Zend_Auth::getInstance()->getStorage()->read();

if (in_array($userStorage->group_id, My_Staff_Group::$allow_in_area_view) && !My_Staff_Permission_Area::view_all($userStorage->id))
    $params['asm'] = $userStorage->id;

$QArea = new Application_Model_Area();

if ( in_array($userStorage->group_id, array(RM_ID, RMSTANDBY_ID,TRAINING_TEAM_ID,36)) ) {
    $areas = $QArea->getAreaByAsmTable($userStorage->id);
} else {
    $areas = $QArea->fetchAll(null, 'name');
}

$QOrg = new Application_Model_Org();
$org_result = $QOrg->fetchAll(null, 'org_name');

$QStoreType = new Application_Model_StoreType();
$st_result = $QStoreType->fetchAll(null, 'store_type_name');

$result = array();
if ($st_result){
    foreach ($st_result as $item) {
        $result[$item['store_type_id']] = array('store_type_name' => $item['store_type_name']);
    }
}

$i=0;
foreach ($org_result as $item) {
    $info[$i]['org_id'] = $item['org_id'];
    $info[$i]['org_name'] = "[".$result[ $item['store_type_id'] ]['store_type_name']."] ".$item['org_name'];
    $i++;
}

//print_r($params);

if ( isset($export) && $export ) {

    switch ($export) {
        case 1: $this->_exportPcShopLevel($params);     break;
        case 2: $this->_exportShopLevelByArea($params); break;
        case 3: $this->_exportPCShopIndex($params, 1);  break;
        case 4: $this->_exportPCShopNum($params);       break;
        case 5: $this->_exportSummaryIndex($params, 1); break;
        default: break;
    }
} 

$this->view->params = $params;
$this->view->areas = $areas;
$this->view->store_type = $info;

if($this->getRequest()->isXmlHttpRequest()) {
    $this->_helper->layout->disableLayout();

    $this->_helper->viewRenderer->setRender('pc-shop-level/partials/list');
} else
    $this->_helper->viewRenderer->setRender('pc-shop-level/index');

?>