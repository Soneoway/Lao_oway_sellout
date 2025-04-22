<?php
$sort            = $this->getRequest()->getParam('sort');
$desc            = $this->getRequest()->getParam('desc', 1);
$export          = $this->getRequest()->getParam('export', 0);
$from            = $this->getRequest()->getParam('from', date('01/m/Y') );
$to              = $this->getRequest()->getParam('to', date('d/m/Y') );
$area            = $this->getRequest()->getParam('area');

$userStorage = Zend_Auth::getInstance()->getStorage()->read();

$params = array(
    'sort'            => $sort,
    'desc'            => $desc,
    'from'            => $from,
    'to'              => $to,
    'area'            => $area,
    'export'          => $export,
);

if ( in_array($userStorage->group_id, My_Staff_Group::$allow_in_area_view) && !My_Staff_Permission_Area::view_all($userStorage->id)) {
    $area_list = array();
    
    $QAsm = new Application_Model_Asm();
    $list_regions = $QAsm->get_cache($userStorage->id);
    $list_regions = isset($list_regions['area']) && is_array($list_regions['area']) ? $list_regions['area'] : array();
        
    $this->view->viewed_area_id = $list_regions;
}

$QTiming = new Application_Model_Timing();
$this->view->userStorage = $userStorage;

if (isset($export) && $export) {
    $sales = $QTiming->report_by_area($params);
    $this->_exportExcelByarea($sales, $params);
    exit;
}

$data = $QTiming->report_by_area($params);

$params['get_total_sales'] = true;
$total_sales = $QTiming->report_by_area($params);

//get total money
$total_money = $total_sales['total_value'];
$sales = array();

//tính point
foreach($data as $item){
    $point = ( $total_money > 0 and ($item ['region_share']/100) > 0 ) ?  round ( ($item ['total_value']/$total_money) * 60 / ($item ['region_share']/100), 2 ) : 0;
    $val = $item;
    $val['point'] = $point;
    $sales[] = $val;
}

unset($params['get_total_sales']);
usort($sales, array($this, 'cmp'));

$this->view->total_sales      = $total_sales['total_quantity'];
$this->view->sales            = $sales;
$this->view->url              = HOST.'timing/analytics-area'.( $params ? '?'.http_build_query($params).'&' : '?' );
$this->view->desc             = $desc;
$this->view->current_col      = $sort;
$this->view->to               = $to;
$this->view->from             = $from;
$this->view->params           = $params;