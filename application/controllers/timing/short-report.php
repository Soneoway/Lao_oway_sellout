<?php
$from            = $this->getRequest()->getParam('from');
$to              = $this->getRequest()->getParam('to');
$phone_number    = $this->getRequest()->getParam('phone_number');
$name            = $this->getRequest()->getParam('name');
$area            = $this->getRequest()->getParam('area');
$regional_market = $this->getRequest()->getParam('regional_market');
$staff_id        = $this->getRequest()->getParam('staff_id');
$brand_id        = $this->getRequest()->getParam('brand_id');

$params = array(
    'from'            => $from,
    'to'              => $to,
    'phone_number'    => $phone_number,
    'name'            => $name,
    'area'            => $area,
    'regional_market' => $regional_market,
    'staff_id'        => $staff_id,
    'brand_id'        => $brand_id
);

$userStorage = Zend_Auth::getInstance()->getStorage()->read();
$group_id = $userStorage->group_id;

// My_Staff_Permission_Area::view_all // kiểm tra xem người này có toàn quyền xem tất cả các khu vực hay không
// viết xong rồi giờ nhìn vô éo nhớ nó là cái gì, phải comment lại cho chắc
if (in_array($userStorage->group_id, My_Staff_Group::$allow_in_area_view) && !My_Staff_Permission_Area::view_all($userStorage->id))

if($userStorage->group_id == ASM_ID)
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

$QArea = new Application_Model_Area();
//$areas = $QArea->get_cache();

if ( in_array($userStorage->group_id, array(RM_ID, RMSTANDBY_ID)) ) {
    
    $areas = $QArea->getAreaByAsmTable($userStorage->id);

    $data = array();
    foreach($areas as $item) {
        $data[] = $item['id'];
    }

    $params['rgm_area'] = implode(',', $data);

} else {
    $areas = $QArea->fetchAll(null, 'name');
}
    
$QTiming = new Application_Model_Timing();
$analytics = $count = 0;
$sales   = $QTiming->report_by_staff($params, $analytics, $count);
$this->view->sales = $sales;

$QStore = new Application_Model_Store();
$this->view->store_cached = $QStore->get_cache();

$this->_helper->layout->disableLayout();
$this->_helper->viewRenderer->setRender('partials/short_report');