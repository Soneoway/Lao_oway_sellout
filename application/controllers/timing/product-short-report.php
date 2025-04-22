<?php
$from   = $this->getRequest()->getParam('from');
$to     = $this->getRequest()->getParam('to');
$model  = $this->getRequest()->getParam('model');
$area   = $this->getRequest()->getParam('area');

$params = array(
    'from'  => $from,
    'to'    => $to,
    'model' => $model,
    'area'  => $area,
);

$userStorage = Zend_Auth::getInstance()->getStorage()->read();
$group_id = $userStorage->group_id;

// My_Staff_Permission_Area::view_all // kiểm tra xem người này có toàn quyền xem tất cả các khu vực hay không
// viết xong rồi giờ nhìn vô éo nhớ nó là cái gì, phải comment lại cho chắc
if (in_array($userStorage->group_id, My_Staff_Group::$allow_in_area_view) && !My_Staff_Permission_Area::view_all($userStorage->id))
    $params['asm'] = $userStorage->id;

if ($userStorage->group_id == AM_ID)
    $params['am'] = $userStorage->id;

//check sale permission
if ($userStorage->group_id == SALES_ID)
    $params['sale_id'] = $userStorage->id;

//check pcm permission
if ($userStorage->group_id == PCM_ID)
    $params['pcm_id'] = $userStorage->id;

//check sale leader permission
if ($userStorage->group_id == LEADER_ID)
    $params['leader_id'] = $userStorage->id;

if ($userStorage->group_id == 39)
    $params['admin_bs'] = $userStorage->id;

$QTiming = new Application_Model_Timing();
$analytics = $count = 0;
$sales   = $QTiming->short_report_by_product($params, $analytics, $count);
$this->view->sales = $sales;

$QStore = new Application_Model_Store();
$this->view->store_cached = $QStore->get_cache();

$this->_helper->layout->disableLayout();
$this->_helper->viewRenderer->setRender('partials/short_report');