<?php
if (defined("LOCK_TIMING") && LOCK_TIMING) {
    $this->_helper->viewRenderer->setRender('lock');
    return;
}

$userStorage = Zend_Auth::getInstance()->getStorage()->read();
$user_id = $userStorage->id;
$group_id = $userStorage->group_id;

//$QTime = new Application_Model_Time();
//$check_status = $QTime->checkInStatus($user_id);

/*
if ($user_id != SUPERADMIN_ID || !in_array($group_id, array(
        ADMINISTRATOR_ID,
        HR_ID,
        HR_EXT_ID,
        BOARD_ID)))
{

    if(!$check_status)
    {
        $this->_redirect(HOST.'time/create');
    }
}
*/

$page            = $this->getRequest()->getParam('page', 1);
$name            = $this->getRequest()->getParam('name');
$department      = $this->getRequest()->getParam('department');
$from_date       = $this->getRequest()->getParam('from_date', date('01/m/Y'));
$to_date         = $this->getRequest()->getParam('to_date', date('d/m/Y'));
$team            = $this->getRequest()->getParam('team');
$area_id         = $this->getRequest()->getParam('area_id');
$regional_market = $this->getRequest()->getParam('regional_market');
$district        = $this->getRequest()->getParam('district');
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
    'district'        => $district,
    'sales_team'      => $sales_team,
    'store'           => $store,
    'imei'            => $imei,
    'email'           => $email,
));

$params['sort'] = $sort;
$params['desc'] = $desc;



if ( $user_id == SUPERADMIN_ID || in_array( $group_id, array(ADMINISTRATOR_ID, HR_ID, HR_EXT_ID, BOARD_ID) ) ) {}

elseif ($group_id == PGPB_ID)
    $params['staff_id'] = $user_id;

elseif ($group_id == SALES_ID)
    $params['sale'] = $user_id;

elseif ($group_id == LEADER_ID)
    $params['leader'] = $user_id;

elseif (in_array($userStorage->group_id, My_Staff_Group::$allow_in_area_view))
    $params['asm'] = $user_id;

elseif ($group_id == BM_ID)
    $params['staff_id'] = $user_id;

else
    $this->_redirect(HOST);

$QTiming = new Application_Model_Timing();
$Timings = $QTiming->fetchPagination($page, $limit, $total, $params);

unset($params['leader']);
unset($params['sale']);
unset($params['asm']);
unset($params['staff_id']);

$this->view->timings = $Timings;

$this->view->params = $params;
$this->view->limit = $limit;
$this->view->total = $total;
$this->view->url = HOST.'timing/'.( $params ? '?'.http_build_query($params).'&' : '?' );
$this->view->offset = $limit*($page-1);

$QArea = new Application_Model_Area();
//$this->view->areas = $QArea->fetchAll(null, 'name');

if ( in_array($userStorage->group_id, array(RM_ID, RMSTANDBY_ID)) ) {
    $areas = $QArea->getAreaByAsmTable($userStorage->id);
} else {
    $areas = $QArea->fetchAll(null, 'name');
}

$this->view->areas = $areas;

$QRegionalMarket = new Application_Model_RegionalMarket();
$this->view->regional_market_all = $QRegionalMarket->get_cache();

$QStore = new Application_Model_Store();
$this->view->store_cached = $QStore->get_cache();

if ($area_id) {
    $where = $QRegionalMarket->getAdapter()->quoteInto('area_id = ?', $area_id);
    $this->view->regional_markets = $QRegionalMarket->fetchAll($where, 'name');
}


if ($regional_market) {
    //get district
    $where = $QRegionalMarket->getAdapter()->quoteInto('parent = ?', $regional_market);
    $this->view->districts = $QRegionalMarket->fetchAll($where, 'name');
}


if ($district) {
    //get store
    $where = array();
    $where[] = $QStore->getAdapter()->quoteInto('district = ?', $district);
    $where[] = $QStore->getAdapter()->quoteInto('(del IS NULL OR del = 0)', 1);
    
    $this->view->stores = $QStore->fetchAll($where, 'name');
}


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