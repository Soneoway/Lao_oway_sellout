<?php
$page            = $this->getRequest()->getParam('page', 1);
$name            = $this->getRequest()->getParam('name');
$department      = $this->getRequest()->getParam('department');
$from_date       = $this->getRequest()->getParam('from_date');
$to_date         = $this->getRequest()->getParam('to_date');
$team            = $this->getRequest()->getParam('team');
$area_id         = $this->getRequest()->getParam('area_id');
$regional_market = $this->getRequest()->getParam('regional_market');
$sales_team      = $this->getRequest()->getParam('sales_team');
$store           = $this->getRequest()->getParam('store');
$imei            = $this->getRequest()->getParam('imei');
$email           = $this->getRequest()->getParam('email');
$sort            = $this->getRequest()->getParam('sort', 'p.id');
$desc            = $this->getRequest()->getParam('desc', 1);

$this->view->desc        = $desc;
$this->view->current_col = $sort;
$limit = LIMITATION;
$total = 0;

$params = array_filter(array(
    'name'            => $name,
    'department'      => $department,
    'from_date'       => $from_date,
    'to_date'         => $to_date,
    'team'            => $team,
    'area_id'         => $area_id,
    'regional_market' => $regional_market,
    'sales_team'      => $sales_team,
    'store'           => $store,
    'imei'            => $imei,
    'email'           => $email,
));

$params['sort'] = $sort;
$params['desc'] = $desc;

$userStorage = Zend_Auth::getInstance()->getStorage()->read();
$user_id = $userStorage->id;
$group_id = $userStorage->group_id;

if ( $user_id == SUPERADMIN_ID || in_array( $group_id, array(ADMINISTRATOR_ID, HR_ID, HR_EXT_ID, BOARD_ID) ) ) {
} elseif ($group_id == PGPB_ID) {
    $params['staff_id'] = $user_id;
} elseif ($group_id == SALES_ID) {
    $params['sale'] = $user_id;
} elseif (in_array( $group_id, array(ASM_ID, ASMSTANDBY_ID) )) {
    $params['asm'] = $user_id;
} else {
    $this->_redirect(HOST);
}

$QArea = new Application_Model_Area();
$this->view->areas = $QArea->fetchAll();

$QRegionalMarket = new Application_Model_RegionalMarket();

/*if ($group_id == SALES_ID) {


    $params['regional_market'] = $userStorage->regional_market;

    $where = $QRegionalMarket->getAdapter()->quoteInto('id = ?', $userStorage->regional_market);


    $result = $QRegionalMarket->fetchRow($where);

    $area = isset($result['area_id']) ? $result['area_id'] : null;

    $params['area'] = $area;

    $this->view->isSales = true;
}*/

if (isset($area) and $area) {

    $where = $QRegionalMarket->getAdapter()->quoteInto('area_id = ?', $area);

    $this->view->regional_markets = $QRegionalMarket->fetchAll($where);

}

$QTiming = new Application_Model_Timing2();

$Timings = $QTiming->fetchPagination($page, $limit, $total, $params);

$this->view->timings = $Timings;

$this->view->params = $params;
$this->view->limit = $limit;
$this->view->total = $total;
$this->view->url = HOST.'timing/manage-not-activated'.( $params ? '?'.http_build_query($params).'&' : '?' );
$this->view->offset = $limit*($page-1);

$QArea = new Application_Model_Area();
$this->view->areas = $QArea->fetchAll();

$QRegionalMarket = new Application_Model_RegionalMarket();
$this->view->regional_market_all = $QRegionalMarket->get_cache();

$QStore = new Application_Model_Store();
$this->view->store_cached = $QStore->get_cache();

if ($area_id) {
    $where = $QRegionalMarket->getAdapter()->quoteInto('area_id = ?', $area_id);

    $this->view->regional_markets = $QRegionalMarket->fetchAll($where);
}


if ($regional_market) {

    //get sales teams
    $QSalesTeam = new Application_Model_SalesTeam();

    $where = array();

    $where[] = $QSalesTeam->getAdapter()->quoteInto('regional_market = ?', $regional_market);

    $where[] = $QSalesTeam->getAdapter()->quoteInto('del = ?', 0);


    $this->view->sales_teams = $QSalesTeam->fetchAll($where, 'name');

    //get store
    $where = $QStore->getAdapter()->quoteInto('regional_market = ?', $regional_market);
    $this->view->stores = $QStore->fetchAll($where, 'name');
}

//get teams
$QTeam = new Application_Model_Team();
$this->view->teams = $QTeam->get_cache();

//get teams
$QTitle = new Application_Model_Title();
$this->view->titles = $QTitle->get_cache();

//get department
$QDepartment = new Application_Model_Department();
$this->view->departments = $QDepartment->get_cache();


$flashMessenger = $this->_helper->flashMessenger;
$messages = $flashMessenger->setNamespace('success')->getMessages();
$messages_error = $flashMessenger->setNamespace('error')->getMessages();
$this->view->messages = $messages;
$this->view->messages_error = $messages_error;

//get product count
$staff_ids = array();
if ($Timings) {
    foreach ($Timings as $item)
        $staff_ids[] = $item['staff_id'];

    $this->view->product_count = $this->count_product_per_staff($staff_ids);
}

if($this->getRequest()->isXmlHttpRequest()) {
    $this->_helper->layout->disableLayout();
    $this->_helper->viewRenderer->setRender('partials/index');
}