<?php

$page            = $this->getRequest()->getParam('page', 1);
$id              = $this->getRequest()->getParam('id');
$name            = $this->getRequest()->getParam('name');
$area_id         = $this->getRequest()->getParam('area_id');
$regional_market = $this->getRequest()->getParam('regional_market');
$district        = $this->getRequest()->getParam('district');
$sort            = $this->getRequest()->getParam('sort', 'created_at');
$store_status    = $this->getRequest()->getParam('store_status');
$mkr_checked         = $this->getRequest()->getParam('mkr_checked', 0);
$mkr_not_check         = $this->getRequest()->getParam('mkr_not_check', 0);

$export          = $this->getRequest()->getParam('export', 0);
$desc            = $this->getRequest()->getParam('desc', 1);

$limit = LIMITATION;
$total = 0;

$params = array_filter(array(
	'id'              => $id,
    'name'            => $name,
    'address'         => $address,
    'area_id'         => $area_id,
    'regional_market' => $regional_market,
    'district'        => $district,
    'sub_district'    => $sub_district,
    'export'          => $export,
    'store_type'      => $store_type,
    'st_disable'      => $st_disable,
    'store_status'    => $store_status,
    'mkr_checked'     => $mkr_checked,
    'mkr_not_check'   => $mkr_not_check

));

$params['sort'] = $sort;
$params['desc'] = $desc;

$QArea = new Application_Model_Area();
$QAsm = new Application_Model_Asm();
$QSubarea = new Application_Model_SubArea();
$QAreaControl = new Application_Model_AreaControl();
$QStore = new Application_Model_Store();
$QRegionalMarket = new Application_Model_RegionalMarket();
$userStorage = Zend_Auth::getInstance()->getStorage()->read();

// Search Area Promission
if(in_array($userStorage->group_id, array(RM_ID,ASM_ID))){ // RGM , RM

	$where = $QAsm->getAdapter()->quoteInto('staff_id = ?', $userStorage->id);
	$staff_area = $QAsm->fetchAll($where);

	$area_array = array();
	foreach ($staff_area as $value) {
		$area_array[] = $value['area_id'];
	}

	// Area List
	$where2 = $QArea->getAdapter()->quoteInto('id IN (?)',$area_array);
	$area = $QArea->fetchAll($where2);

	// Store List
	$params['area_array'] = $area_array;
	$store = $QStore->fetchPagination($page, $limit, $total, $params);

}else if(in_array($userStorage->group_id, array(SALES_ID))){ // Sale

	$where = $QSubarea->getAdapter()->quoteInto('staff_id =?',$userStorage->id);
	$sub_area = $QSubarea->fetchAll($where);

	$sub_area_array = array();
	foreach($sub_area as $value) {
		$sub_area_array[] = $value['id'];
	}

	// Area List
	$area = $QAreaControl->getsaleAreaPromission($userStorage->id);

	$params['agency'] = $sub_area_array;
	$store = $QStore->fetchPagination($page, $limit, $total, $params);

}else if(in_array($userStorage->group_id,array(TRAINING_TEAM_GROUP_ID))){ // Trainner

	$where = $QAsm->getAdapter()->quoteInto('staff_id = ?', $userStorage->id);
	$staff_province = $QAsm->fetchAll($where);

	$province_array = array();
	foreach ($staff_province as $value) {
		$province_array[] = $value['area_id'];
	}

	$area = $QAsm->getTrainnerArea($userStorage->id);

	$params['province_id'] = $province_array;
	$store = $QStore->fetchPagination($page, $limit, $total, $params);

}else{ // another

	$area = $QArea->fetchAll();

	$store = $QStore->fetchPagination($page, $limit, $total, $params);

}

$this->view->areas = $QArea->fetchAll(null, 'name');

if ($area_id) {
    if (is_array($area_id) && count($area_id))
        $where = $QRegionalMarket->getAdapter()->quoteInto('area_id IN (?)', $area_id);
    else
        $where = $QRegionalMarket->getAdapter()->quoteInto('area_id = ?', $area_id);

    $this->view->regional_markets = $QRegionalMarket->fetchAll($where, 'name');
}

if ($regional_market) {
    if (is_array($regional_market) && count($regional_market))
        $where = $QRegionalMarket->getAdapter()->quoteInto('parent IN (?)', $regional_market);
    else
        $where = $QRegionalMarket->getAdapter()->quoteInto('parent = ?', $regional_market);

    $this->view->districts = $QRegionalMarket->fetchAll($where, 'name');
}


if ($export && $export == 1) {
    $loader = new Zend_Loader_PluginLoader();
    $loader->addPrefixPath('Export_', 'My/Application/Export2CSV');
    $sales = $loader->load('Sales');
    Export_Sales::store_list($stores);
}

$this->view->area = $area;
$this->view->store_type  = $info;
$this->view->params = $params;
$this->view->sort = $sort;
$this->view->desc = $desc;
$this->view->store = $store;
$this->view->area_cache = $QArea->get_cache();
$this->view->regional_cache = $QRegionalMarket->get_cache();
$this->view->offset      = $limit*($page-1);
$this->view->total       = $total;
$this->view->limit       = $limit;
$this->view->url         = HOST.'report/market-research'.( $params ? '?'.http_build_query($params).'&' : '?' );

$flashMessenger = $this->_helper->flashMessenger;
$messages = $flashMessenger->setNamespace('success')->getMessages();

$flashMessenger = $this->_helper->flashMessenger;
$messages = $flashMessenger->setNamespace('Error')->getMessages();

$this->view->messages = $messages;


$this->_helper->viewRenderer->setRender('/market-research/index');
?>