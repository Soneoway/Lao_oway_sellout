<?php
$page            = $this->getRequest()->getParam('page', 1);
$name            = $this->getRequest()->getParam('name');
$address         = $this->getRequest()->getParam('address');
$area_id         = $this->getRequest()->getParam('area_id');
$staff_email     = $this->getRequest()->getParam('staff_email');
$regional_market = $this->getRequest()->getParam('regional_market');
$have_leader     = $this->getRequest()->getParam('have_leader', 0);
$no_leader       = $this->getRequest()->getParam('no_leader', 0);
$pending         = $this->getRequest()->getParam('pending', 0);
$sort            = $this->getRequest()->getParam('sort', 'leader');
$export          = $this->getRequest()->getParam('export', 0);
$desc            = $this->getRequest()->getParam('desc', 0);

$limit = LIMITATION;
$total = 0;

$userStorage = Zend_Auth::getInstance()->getStorage()->read();

$params = array_filter(array(
    'name'            => $name,
    'address'         => $address,
    'area_id'         => $area_id,
    'regional_market' => $regional_market,
    'staff_email'     => $staff_email,
    'have_leader'     => ($no_leader == 0 && $pending == 0) ? 1 : 0,
    'no_leader'       => $no_leader,
    'pending'         => $pending,
    'export'          => $export,
));

if ($userStorage->group_id == LEADER_ID) 
    $params['for_leader'] = $userStorage->id;
elseif (in_array($userStorage->group_id, My_Staff_Group::$allow_in_area_view))
    $params['for_asm'] = $userStorage->id;

$this->view->group_id = $userStorage->group_id;

$params['sort'] = $sort;
$params['desc'] = $desc;

$QArea = new Application_Model_Area();
$this->view->areas = $QArea->fetchAll();

if ($area_id) {
    $QRegionalMarket = new Application_Model_RegionalMarket();
    $where = $QRegionalMarket->getAdapter()->quoteInto('area_id = ?', $area_id);

    $this->view->regional_markets = $QRegionalMarket->fetchAll($where);
}

$QModel = new Application_Model_Store();
$stores = $QModel->fetchPaginationLeader($page, $limit, $total, $params);

$leader_arr = array();

foreach ($stores as $store) {
    if ( ! isset( $leader_arr[ $store['staff_id'] ] ) )
        $leader_arr[ $store['staff_id'] ] = array();

    $leader_arr[ $store['staff_id'] ][] = $store;
}

unset($params['for_leader']);
unset($params['for_asm']);

// if ($export && $export == 1) {
//     $loader = new Zend_Loader_PluginLoader();
//     $loader->addPrefixPath('Export_', 'My/Application/Export2CSV');
//     $sales = $loader->load('Sales');
//     Export_Sales::store_list($stores);
// }

$QStaff = new Application_Model_Staff();
$this->view->staffs = $QStaff->get_all_cache();

$this->view->params = $params;
$this->view->sort = $sort;
$this->view->desc = $desc;
$this->view->leaders = $leader_arr;

$this->view->limit = $limit;
$this->view->total = $total;
$this->view->url = HOST.'manage/store-leader/'.( $params ? '?'.http_build_query($params).'&' : '?' );
$this->view->offset = $limit*($page-1);

$flashMessenger = $this->_helper->flashMessenger;
$messages = $flashMessenger->setNamespace('success')->getMessages();
$this->view->messages = $messages;

$this->_helper->viewRenderer->setRender('store/leader');