<?php
$page            = $this->getRequest()->getParam('page', 1);
$id              = $this->getRequest()->getParam('id');
$name            = $this->getRequest()->getParam('name');
$address         = $this->getRequest()->getParam('address');
$area_id         = $this->getRequest()->getParam('area_id');
$staff_email     = $this->getRequest()->getParam('staff_email');
$regional_market = $this->getRequest()->getParam('regional_market');
$district        = $this->getRequest()->getParam('district');
$sort            = $this->getRequest()->getParam('sort', 'created_at');
$d_id            = $this->getRequest()->getParam('d_id');
$d_name          = $this->getRequest()->getParam('d_name');
$market_type     = $this->getRequest()->getParam('market_type');
$market_name     = $this->getRequest()->getParam('market_name');
$store_type      = $this->getRequest()->getParam('store_type');
$store_level     = $this->getRequest()->getParam('store_level');
$have_pg         = $this->getRequest()->getParam('have_pg', 0);
$have_sales      = $this->getRequest()->getParam('have_sales', 0);
$have_asm        = $this->getRequest()->getParam('have_asm', 0);
$no_pc           = $this->getRequest()->getParam('no_pc', 0);
$have_pcm        = $this->getRequest()->getParam('have_pcm', 0);
$have_pcdb           = $this->getRequest()->getParam('have_pcdb',0);
$have_rd          = $this->getRequest()->getParam('have_rd',0);
$have_stock      = $this->getRequest()->getParam('have_stock', 0);
$no_staff        = $this->getRequest()->getParam('no_staff', 0);

$oppo_id         = $this->getRequest()->getParam('oppo_id');
$store_status    = $this->getRequest()->getParam('store_status');

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
    'staff_email'     => $staff_email,
    'have_pg'         => $have_pg,
    'no_staff'        => $no_staff,
    'no_pc'           => $no_pc,
    'have_asm'        => $have_asm,
    'have_sales'      => $have_sales,
    'have_pcm'        => $have_pcm,
    'have_rd'        => $have_rd,
    'have_pcdb'        => $have_pcdb,
    'have_stock'      => $have_stock,
    'export'          => $export,
    'd_id'            => $d_id,
    'd_name'          => $d_name,
    'market_type'     => $market_type,
    'market_name'     => $market_name,
    'store_type'      => $store_type,
    'st_disable'      => $st_disable,
    'store_level'     => $store_level,
    'oppo_id'         => $oppo_id,
    'store_status'    => $store_status
));

$params['sort'] = $sort;
$params['desc'] = $desc;

$userStorage = Zend_Auth::getInstance()->getStorage()->read();
$group_id = $userStorage->group_id;

$QMarketType = new Application_Model_MarketType();
$market_type_list = $QMarketType->fetchAll(null, 'name');

$QOrg = new Application_Model_Org();
$org_result = $QOrg->fetchAll(null, 'org_name');
$this->view->org = $QOrg->get_cache();

$QSubArea = new Application_Model_SubArea();
$this->view->subArea = $QSubArea->get_cache();

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

if (in_array($group_id, My_Staff_Group::$allow_in_area_view) && !My_Staff_Permission_Area::view_all($userStorage->id)) {
    $params['asm'] = $userStorage->id;

    $QAsm = new Application_Model_Asm();
    $result_area = $QAsm->get_cache($userStorage->id);
    $params['area_permission'] = $result_area['area'];

} elseif ( in_array($group_id, array(SALES_ID,LEADER_ID,PGPB_ID) ) ) {

    if ($group_id == SALES_ID) { $params['sales_store'] = $userStorage->id; } 
    elseif ($group_id == LEADER_ID) { $params['leader_province'] = $userStorage->id; }

    $QStaff = new Application_Model_Staff();
    $result_area = $QStaff->getStaffArea($userStorage->id);
    $params['area_permission'] = $result_area['staff_area_id'];

} 

if ($group_id == AM_ID)
    $params['am'] = $userStorage->id;

if ($group_id == 39)
    $params['admin_bs'] = $userStorage->id;

$QArea = new Application_Model_Area();
$this->view->areas = $QArea->fetchAll(null, 'name');

$QRegionalMarket = new Application_Model_RegionalMarket();

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


if ($market_type) {
    $QMarketName = new Application_Model_MarketName();
         
    $where = array();   
    $where[] = $QMarketName->getAdapter()->quoteInto('market_type_id IN (?)', $market_type);

    if ( isset($params['area_permission']) && !empty($params['area_permission']) ) {
        $where[] = $QMarketName->getAdapter()->quoteInto('area_id IN (?)', $params['area_permission']);
    }

    $this->view->market_name = $QMarketName->fetchAll($where, 'name');
}

$store_level = array(
    '0'  => array('id' => 'S', 'name' => 'S'),
    '1'  => array('id' => 'A', 'name' => 'A'),
    '2'  => array('id' => 'B', 'name' => 'B'),
    '3'  => array('id' => 'C', 'name' => 'C'),
);

$this->view->store_level_list = $store_level;

$QModel = new Application_Model_Store();
$stores = $QModel->fetchPagination($page, $limit, $total, $params);

if ($export && $export == 1) {
    $loader = new Zend_Loader_PluginLoader();
    $loader->addPrefixPath('Export_', 'My/Application/Export2CSV');
    $sales = $loader->load('Sales');
    Export_Sales::store_list($stores);
}

$this->view->store_type  = $info;
$this->view->params = $params;
$this->view->sort = $sort;
$this->view->desc = $desc;
$this->view->stores = $stores;
$this->view->market_type = $market_type_list;

$this->view->limit = $limit;
$this->view->total = $total;
$this->view->url = HOST.'manage/store/'.( $params ? '?'.http_build_query($params).'&' : '?' );
$this->view->offset = $limit*($page-1);

$flashMessenger = $this->_helper->flashMessenger;
$messages = $flashMessenger->setNamespace('success')->getMessages();
$this->view->messages = $messages;

if($this->getRequest()->isXmlHttpRequest()) {
    $this->_helper->layout->disableLayout();

    $this->_helper->viewRenderer->setRender('store/partials/list');
} else
    $this->_helper->viewRenderer->setRender('store/index');