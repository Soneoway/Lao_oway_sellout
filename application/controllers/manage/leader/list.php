<?php
// list Leaders
// if leader logged in, view only his name
// if asm, view leaders in his area
// else show all

// check condition
$userStorage = Zend_Auth::getInstance()->getStorage()->read();
$area_id     = $this->getRequest()->getParam('area_id');
$province    = $this->getRequest()->getParam('province');
$name        = $this->getRequest()->getParam('name');
$email       = $this->getRequest()->getParam('email');
$store       = $this->getRequest()->getParam('store');
$page        = $this->getRequest()->getParam('page', 1);
$sort        = $this->getRequest()->getParam('sort', 'name');
$desc        = $this->getRequest()->getParam('desc', 0);
$limit       = LIMITATION;
$total       = 0;

$params = array(
    'area_id'  => $area_id,
    'province' => $province,
    'name'     => $name,
    'email'    => $email,
    'store'    => $store,
    'sort'     => $sort,
    'desc'     => $desc,
    );

if (in_array($userStorage->group_id, My_Staff_Group::$allow_in_area_view)) {
    $params['asm'] = $userStorage->id;
} elseif ($userStorage->group_id == LEADER_ID) {
    $params['leader'] = $userStorage->id;
}

$QStaff = new Application_Model_Staff();
$this->view->leaders = $QStaff->fetchLeaderPagination($page, $limit, $total, $params);

$QArea = new Application_Model_Area();
$QRegionalMarket = new Application_Model_RegionalMarket();

if ($area_id) {
    $where = $QRegionalMarket->getAdapter()->quoteInto('area_id = ?', $area_id);
    $this->view->regions_search = $QRegionalMarket->fetchAll($where, 'name');
}

$this->view->areas   = $QArea->get_cache();
$this->view->regions = $QRegionalMarket->get_cache_all();
$this->view->params  = $params;
$this->view->limit   = $limit;
$this->view->total   = $total;
$this->view->desc    = $desc;
$this->view->sort    = $sort;
$this->view->offset  = $limit*($page-1);
$this->view->url     = HOST.'manage/leader'.( $params ? '?'.http_build_query($params).'&' : '?' );

$flashMessenger      = $this->_helper->flashMessenger;

$this->view->messages       = $flashMessenger->setNamespace('success')->getMessages();
$this->view->messages_error = $flashMessenger->setNamespace('error')->getMessages();

$this->_helper->viewRenderer->setRender('leader/list');
// get leader list

    