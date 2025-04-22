<?php

$userStorage = Zend_Auth::getInstance()->getStorage()->read();
$flashMessenger = $this->_helper->flashMessenger;

if (!$userStorage) {
    $flashMessenger->setNamespace('error')->addMessage('Invalid user');
    $this->_redirect(HOST);
}

$QTimingSaleExpired = new Application_Model_TimingSaleExpired();
$total = 0;
$limit = LIMITATION;

$page   = $this->getRequest()->getParam('page', 1);
$from   = $this->getRequest()->getParam('from', date('01/m/Y'));
$to     = $this->getRequest()->getParam('to', date('d/m/Y'));

$area_id         = $this->getRequest()->getParam('area_id');
$regional_market = $this->getRequest()->getParam('regional_market');
$district        = $this->getRequest()->getParam('district');
$store           = $this->getRequest()->getParam('store');
$imei            = $this->getRequest()->getParam('imei');
$email           = $this->getRequest()->getParam('email');
$type            = $this->getRequest()->getParam('type', 'all');

$export = $this->getRequest()->getParam('export', 0);

$params = array(
    'from'            => $from,
    'to'              => $to,
    'area_id'         => $area_id,
    'regional_market' => $regional_market,
    'district'        => $district,
    'store'           => $store,
    'imei'            => $imei,
    'email'           => $email,
);

$params[$type] = 1;
$params['type'] = $type;

switch ($userStorage->group_id) {
    case PGPB_ID:
    case BM_ID:
        $params['staff_id'] = $userStorage->id;
        break;
    case SALES_ID:
        $params['sale'] = $userStorage->id;
        break;
    case LEADER_ID:
        $params['leader'] = $userStorage->id;
        break;
    case PCDB_ID:
        $params['pcdb'] = $userStorage->id;
        break;
    case ASM_ID:
        $params['asm'] = $userStorage->id;
        break;
    case ASMSTANDBY_ID:
    case RM_ID:
        $params['rd'] = $userStorage->id;
        break;
    case RMSTANDBY_ID:

    default:
        break;
}

if ($export && $export == 1) {
    // $expires = $QTimingSaleExpired->fetchPagination(1, null, $total, $params);
    // $this->_exportExcelExpired($expires);
    exit;
}

$this->view->expires = $QTimingSaleExpired->fetchPagination($page, $limit, $total, $params);
unset($params['asm']);
unset($params['staff_id']);
unset($params['sale']);
unset($params['leader']);

$this->view->url     = HOST.'timing/expired'.( $params ? '?'.str_replace('&', '&amp;', http_build_query($params)).'&' : '?' );
$this->view->offset  = $limit*($page-1);
$this->view->limit   = $limit;
$this->view->total   = $total;
$this->view->type    = $type;
$this->view->params  = $params;

$QArea = new Application_Model_Area();
$this->view->areas = $QArea->fetchAll(null, 'name');

$QRegionalMarket = new Application_Model_RegionalMarket();
$this->view->regional_market_all = $QRegionalMarket->get_cache();

$QStore = new Application_Model_Store();
$this->view->store_cache = $QStore->get_cache();

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

$QGood = new Application_Model_Good();
$this->view->good_cache = $QGood->get_cache();

$QGoodColor = new Application_Model_GoodColor();
$this->view->color_cache = $QGoodColor->get_cache();

$flashMessenger = $this->_helper->flashMessenger;
$this->view->messages = $flashMessenger->setNamespace('success')->getMessages();
$this->view->messages_error = $flashMessenger->setNamespace('error')->getMessages();

if ($this->getRequest()->isXmlHttpRequest()) {
    $this->_helper->layout->disableLayout();
    $this->_helper->viewRenderer->setRender('partials/expired');
}