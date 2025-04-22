<?php
// check condition
$userStorage = Zend_Auth::getInstance()->getStorage()->read();
$area_id     = $this->getRequest()->getParam('area_id');
$province    = $this->getRequest()->getParam('province');
$district    = $this->getRequest()->getParam('district');
$name        = $this->getRequest()->getParam('name');
$staff_code  = $this->getRequest()->getParam('staff_code');
$email       = $this->getRequest()->getParam('email');
$store       = $this->getRequest()->getParam('store');
$page        = $this->getRequest()->getParam('page', 1);
$sort        = $this->getRequest()->getParam('sort', 'name');
$desc        = $this->getRequest()->getParam('desc', 0);
$limit       = LIMITATION;
$total       = 0;

$params = array(
    'area_id'   => $area_id,
    'province'  => $province,
    'district'  => $district,
    'name'      => $name,
    'staff_code'=> $staff_code,
    'email'     => $email,
    'store'     => $store,
    'sort'      => $sort,
    'desc'      => $desc,
    );
PC::db($userStorage->group_id);
/**
 * Phân quyền view theo group
 */
if ( $userStorage->id == SUPERADMIN_ID 
        || in_array(
            $userStorage->group_id, 
            array(ADMINISTRATOR_ID, HR_ID, HR_EXT_ID, BOARD_ID, SALES_EXT_ID, 29, BM_ID, TRADE_MARKETING_ID,39,13) 
        ) 
) { }

elseif ( in_array($userStorage->group_id, My_Staff_Group::$allow_in_area_view) && !My_Staff_Permission_Area::view_all($userStorage->id) )
    $params['asm'] = $userStorage->id;

elseif ($userStorage->group_id == PGPB_ID)
    $params['pg'] = $userStorage->id;

elseif ($userStorage->group_id == SALES_ID)
    $params['sales'] = $userStorage->id;

// elseif ($userStorage->group_id == LEADER_ID)
    // $params['leader'] = $userStorage->id;

else
    $this->_redirect(HOST);

$QStaff = new Application_Model_Staff();
$this->view->staffs = $QStaff->fetchSalesPgPagination($page, $limit, $total, $params);

$QArea = new Application_Model_Area();
$QRegionalMarket = new Application_Model_RegionalMarket();

if ($area_id) {
    $where = $QRegionalMarket->getAdapter()->quoteInto('area_id = ?', $area_id);
    $this->view->regions_search = $QRegionalMarket->fetchAll($where, 'name');
}

if ($province) {
    $where = $QRegionalMarket->getAdapter()->quoteInto('parent = ?', $province);
    $this->view->district_search = $QRegionalMarket->fetchAll($where, 'name');
}

$this->view->areas   = $QArea->get_cache();
$this->view->regions = $QRegionalMarket->get_cache_all();
$this->view->params  = $params;
$this->view->limit   = $limit;
$this->view->total   = $total;
$this->view->desc    = $desc;
$this->view->sort    = $sort;
$this->view->offset  = $limit*($page-1);
$this->view->url     = HOST.'manage/sales-pg'.( $params ? '?'.http_build_query($params).'&' : '?' );

$flashMessenger      = $this->_helper->flashMessenger;

$this->view->messages       = $flashMessenger->setNamespace('success')->getMessages();
$this->view->messages_error = $flashMessenger->setNamespace('error')->getMessages();

$this->_helper->viewRenderer->setRender('sales-pg/list');
// get leader list

    