<?php
$page            = $this->getRequest()->getParam('page', 1);
$store_id        = $this->getRequest()->getParam('store_id');
$store_name      = $this->getRequest()->getParam('store_name');
$staff_code      = $this->getRequest()->getParam('staff_code');
$staff_name      = $this->getRequest()->getParam('staff_name');
$tmp_imei        = $this->getRequest()->getParam('imei');
$area_id         = $this->getRequest()->getParam('area_id');
$regional_market = $this->getRequest()->getParam('regional_market');
$district        = $this->getRequest()->getParam('district');
$from            = $this->getRequest()->getParam('from', date('01/m/Y'));
$to              = $this->getRequest()->getParam('to', date('d/m/Y'));
$issue_type_id   = $this->getRequest()->getParam('issue_type_id');
$status_id       = $this->getRequest()->getParam('status_id', array(0,1,2,3));
$export          = $this->getRequest()->getParam('export', 0);
$update          = $this->getRequest()->getParam('update');

$limit = LIMITATION;
$total = 0;

if ($tmp_imei) { $imei = explode("\n", $tmp_imei); }

$params = array(
    'store_id'        => $store_id,
    'store_name'      => $store_name,
    'staff_code'      => $staff_code,
    'staff_name'      => $staff_name,
    'imei'            => $imei,
    'area_id'         => $area_id,
    'regional_market' => $regional_market,
    'district'        => $district,
    'from'            => $from,
    'to'              => $to, 
    'issue_type_id'   => $issue_type_id,
    'status_id'       => $status_id,
    'export'          => $export,
);

$QTimingIssue = new Application_Model_TimingIssue();

if (isset($update) && $update = 1) {
    //$QTimingIssue->UpdateTimingIssue();
    $db = Zend_Registry::get('db');

    $sql = "    
        UPDATE hr.timing_issue AS ti 
        JOIN hr.timing_sale AS ts 
            ON ti.imei = ts.imei 
        SET ti.updated_by = '18', 
            ti.updated_at = '".date('Y-m-d H:i:s')."', 
            ti.remark = CASE WHEN ISNULL(ti.remark) THEN 'System : Already Timing' ELSE CONCAT(ti.remark,' \nSystem : Already Timing') END, 
            ti.status = 1 
        WHERE ti.status <> 1
    ";
    //echo $sql;
    $db->query( $sql );
}

//print_r($params);

$userStorage = Zend_Auth::getInstance()->getStorage()->read();
//$this->view->group_id = $userStorage->group_id;

if (in_array($userStorage->group_id, My_Staff_Group::$allow_in_area_view) && !My_Staff_Permission_Area::view_all($userStorage->id))
    $params['asm'] = $userStorage->id;

//check sale permission
if ($userStorage->group_id == SALES_ID)
    $params['sale_id'] = $userStorage->id;

//check pcm permission
if ($userStorage->group_id == PCM_ID)
    $params['pcm_id'] = $userStorage->id;

//check sale leader permission
if ($userStorage->group_id == LEADER_ID)
    $params['leader_id'] = $userStorage->id;

if ($userStorage->group_id == PGPB_ID)
    $params['pc_id'] = $userStorage->id;

if ($userStorage->group_id == BM_ID)
    $params['pc_id'] = $userStorage->id;

$QArea = new Application_Model_Area();
//$this->view->areas = $QArea->fetchAll(null, 'name');

if ( in_array($userStorage->group_id, array(RM_ID, RMSTANDBY_ID)) ) {
    $areas = $QArea->getAreaByAsmTable($userStorage->id);
} else {
    $areas = $QArea->fetchAll(null, 'name');
}

$this->view->areas = $areas;

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

$issue_type_list = array(
    '0'  => array('id' => '1', 'name' => 'Imei Not Exists'),
    '1'  => array('id' => '2', 'name' => 'Store Not Exists'),
    '2'  => array('id' => '3', 'name' => 'Distributor Not Exists'),
    '3'  => array('id' => '4', 'name' => 'Store Type / Distributor Type'),
    '4'  => array('id' => '5', 'name' => 'Store Chain 1 Not Exists'),
    '5'  => array('id' => '6', 'name' => 'Mapping Distributor'),
    '6'  => array('id' => '7', 'name' => 'IMEI APK / Demo'),
    '7'  => array('id' => '8', 'name' => 'IMEI Seven-Eleven'),
    '8'  => array('id' => '9', 'name' => 'IMEI Grade B'),
);

$this->view->issue_type_list = $issue_type_list;

$status_list = array(
    '0'  => array('id' => '0', 'name' => 'Waiting'),
    '1'  => array('id' => '1', 'name' => 'Finish'),
    '2'  => array('id' => '2', 'name' => 'Reject'),
    '3'  => array('id' => '3', 'name' => 'In Progress'),
);

$this->view->status_list = $status_list;


if ($export && $export == 1) {
    $timing_issue = $QTimingIssue->fetchPagination(null, null, $total, $params);
    $this->_exportTimingIssue($timing_issue,$params);
}

$timing_issue = $QTimingIssue->fetchPagination($page, $limit, $total, $params);

$params['get_total_count'] = 0;
$total_count = $QTimingIssue->fetchPagination(null, null, $total, $params);

$this->view->params = $params;
$this->view->timing_issue = $timing_issue;
$this->view->total_count = $total_count;

$this->view->limit = $limit;
$this->view->total = $total;
$this->view->url = HOST.'timing/timing-issue/'.( $params ? '?'.http_build_query($params).'&' : '?' );
$this->view->offset = $limit*($page-1);

$flashMessenger = $this->_helper->flashMessenger;
$messages = $flashMessenger->setNamespace('success')->getMessages();
$this->view->messages = $messages;

if($this->getRequest()->isXmlHttpRequest()) {
    $this->_helper->layout->disableLayout();

    $this->_helper->viewRenderer->setRender('timing-issue/partials/list');
} else
    $this->_helper->viewRenderer->setRender('timing-issue/index');