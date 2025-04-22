<?php
$id = $this->getRequest()->getParam('dealer_id');
$page = $this->getRequest()->getParam('page', 1);
$name = $this->getRequest()->getParam('name');
$area            = $this->getRequest()->getParam('area');
$regional_market = $this->getRequest()->getParam('regional_market');
$district        = $this->getRequest()->getParam('district');
$export             = $this->getRequest()->getParam('export');

$QArea            = new Application_Model_Area();
$areas            = $QArea->get_cache();

$QRegionalMarket  = new Application_Model_RegionalMarket();

$userStorage = Zend_Auth::getInstance()->getStorage()->read();
$group_id = $userStorage->group_id;

$params = array(
    'name' => $name,
    'area' => $area,
    'regional_market' => $regional_market,
    'district' => $district,
    'export' => $export,
);

if (in_array($group_id, My_Staff_Group::$allow_in_area_view) && !My_Staff_Permission_Area::view_all($userStorage->id)) {
    $params['asm'] = $userStorage->id;
} elseif ($group_id == SALES_ID) {
    $params['sales_store'] = $userStorage->id;
} elseif ($group_id == LEADER_ID) {
    $params['leader_province'] = $userStorage->id;
}

if ($area) {
    $where = $QRegionalMarket->getAdapter()->quoteInto('area_id = ?', $area);
    $this->view->regional_markets = $QRegionalMarket->fetchAll($where, 'name');
}

if ($regional_market) {
    $where = $QRegionalMarket->getAdapter()->quoteInto('parent = ?', $regional_market);
    $this->view->districts = $QRegionalMarket->fetchAll($where, 'name');
}

$limit = LIMITATION;

$QTiming = new Application_Model_Timing();
$QDistributor = new Application_Model_Distributor();
$total = 0;

$result = $QTiming->report_inventory_by_dealer($page, $limit, $total, $params);

$this->view->params            = $params;
$this->view->areas            = $areas;
$distributorCached = $QDistributor->get_cache();
$this->view->distributorCached = $distributorCached;
$this->view->result = $result;
$this->view->offset = $limit*($page-1);
$this->view->total  = $total;
$this->view->limit  = $limit;
$this->view->url    = HOST.'timing/analytics-inventory-by-dealer'.( $params ? '?'.http_build_query($params).'&' : '?' );

if (isset($export) && $export) {

    set_time_limit(0);
    ini_set('memory_limit', -1);

    require_once 'PHPExcel.php';
    $PHPExcel = new PHPExcel();
    $heads = array(
        'Dealer ID',
        'Dealer Name',
        'Area',
        'Province',
        'Total Sell In',
        'Total Activated',
        'Inventory',
    );

    $PHPExcel->setActiveSheetIndex(0);
    $sheet    = $PHPExcel->getActiveSheet();

    $alpha    = 'A';
    $index    = 1;
    foreach($heads as $key)
    {
        $sheet->setCellValue($alpha.$index, $key);
        $alpha++;
    }
    $index    = 2;

    $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini');
    $config = $config->toArray();

    $con=mysqli_connect($config['resources']['db_tunnel']['params']['host'],$config['resources']['db_tunnel']['params']['username'],$config['resources']['db_tunnel']['params']['password'],$config['resources']['db_tunnel']['params']['dbname'],$config['resources']['db_tunnel']['params']['port']);

    // Check connection
    if (mysqli_connect_errno())
    {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }

    $resultSql = mysqli_query($con,$result);
    while($row = @mysqli_fetch_array($resultSql)){
        $alpha    = 'A';
        $sheet->setCellValue($alpha++.$index, $row['id']);
        $sheet->setCellValue($alpha++.$index, (isset($distributorCached[$row['id']]['title']) ? $distributorCached[$row['id']]['title'] : '') );
        $sheet->setCellValue($alpha++.$index, (isset($distributorCached[$row['id']]['area_name']) ? $distributorCached[$row['id']]['area_name'] : '') );
        $sheet->setCellValue($alpha++.$index, (isset($distributorCached[$row['id']]['region_name']) ? $distributorCached[$row['id']]['region_name'] : '') );
        $sheet->setCellValue($alpha++.$index, (isset($row['sum_sum2']) ? $row['sum_sum2'] : 0) );
        $sheet->setCellValue($alpha++.$index, (isset($row['sum_sum1']) ? $row['sum_sum1'] : 0) );
        $sheet->setCellValue($alpha++.$index, ( (isset($row['sum_sum2']) ? $row['sum_sum2'] : 0) - (isset($row['sum_sum1']) ? $row['sum_sum1'] : 0)) );
        $index++;
    }

    $filename = 'REPORT_INVENTORY_BY_DEALER_'.date('d-m-Y H:i:s');
    $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

    $objWriter->save('php://output');
    exit;
}