<?php
$from_date       = $this->getRequest()->getParam('from_date', date('01/m/Y'));
$to_date         = $this->getRequest()->getParam('to_date', date('d/m/Y'));
$imei            = $this->getRequest()->getParam('imei');
$solved          = $this->getRequest()->getParam('solved', 2);
$export          = $this->getRequest()->getParam('export', 0);

$name            = $this->getRequest()->getParam('name');
$area_id         = $this->getRequest()->getParam('area_id');
$store           = $this->getRequest()->getParam('store');
$email           = $this->getRequest()->getParam('email');
$regional_market = $this->getRequest()->getParam('regional_market');

$name_first            = $this->getRequest()->getParam('name_first');
$area_id_first         = $this->getRequest()->getParam('area_id_first');
$store_first           = $this->getRequest()->getParam('store_first');
$email_first           = $this->getRequest()->getParam('email_first');
$regional_market_first = $this->getRequest()->getParam('regional_market_first');

$page = $this->getRequest()->getParam('page', 1);
$sort = $this->getRequest()->getParam('sort', 'p.created_at');
$desc = $this->getRequest()->getParam('desc', 1);

$this->view->desc        = $desc;
$this->view->current_col = $sort;
$limit = LIMITATION;
$total = 0;

$QArea = new Application_Model_Area();
$userStorage = Zend_Auth::getInstance()->getStorage()->read();
$group_id = $userStorage->group_id;
$uid = $userStorage->id;

$params = array_filter(array(
	'imei'                  => $imei,
	'from_date'             => $from_date,
	'to_date'               => $to_date,
	
	'name'                  => $name,
	'area_id'               => $area_id,
	'regional_market'       => $regional_market,
	'store'                 => $store,
	'email'                 => $email,
	
	'name_first'            => $name_first,
	'area_id_first'         => $area_id_first,
	'regional_market_first' => $regional_market_first,
	'store_first'           => $store_first,
	'email_first'           => $email_first,
	
	'export'                => $export,
));

$params['solved'] = $solved;
$params['sort']   = $sort;
$params['desc']   = $desc;
$QDuplicatedImei  = new Application_Model_DuplicatedImei();
$dup_imeis        = $QDuplicatedImei->fetchPagination($page, $limit, $total, $params);

$timing_sales = array();
$imei_model   = array();
$points       = array();

foreach ($dup_imeis as $key => $imei) {
    $timing_sales[ $imei['timing_sales_first'] ] = $QDuplicatedImei->get_timing_sales_info_cache($imei['timing_sales_first']);
    $imei_model[ $imei['imei'] ] = $QDuplicatedImei->get_imei_model_cache($imei['imei']);

    if ($group_id != ASM_ID) {
	    // tính điểm
	    $points[$imei['id']] = array(
			'first'  => 0,
			'second' => 0,
	    	);

	    if ( ! empty( $imei['photo'] ) ) {
	    	$points[$imei['id']]['second']++;
	    }

	    if ( ! empty( $imei['customer_name'] ) && ! empty( $imei['customer_phone'] )  ) {
	    	$points[$imei['id']]['second']++;
	    }

	    if ( ! empty( $timing_sales[ $imei['timing_sales_first'] ]['customer_name'] ) && ! empty( $timing_sales[ $imei['timing_sales_first'] ]['customer_phone'] ) ) {
	    	$points[$imei['id']]['first']++;
	    }

	    if ( ! empty( $timing_sales[ $imei['timing_sales_first'] ]['photo'] ) ) {
	    	$points[$imei['id']]['first']++;
	    }

	    if (isset($imei_model[ $imei['imei'] ]['activated_at'])) {
	    	
		    $a =  $imei_model[ $imei['imei'] ]['activated_at'];
		    $a = strtotime(date('Y-m-d 00:00:00', strtotime($a)));

		    $b =  $timing_sales[ $imei['timing_sales_first'] ]['from'];
		    $b = strtotime(date('Y-m-d 00:00:00', strtotime($b)));

		    $c =  $imei['date'];
		    $c = strtotime(date('Y-m-d 00:00:00', strtotime($c)));

		    if ( abs( $a - $b ) <=  abs( $a - $c ) ) {
		    	$points[$imei['id']]['first']++;
		    } else {
		    	$points[$imei['id']]['second']++;
		    }
	    }
	}
}

if ($export == 1) {
	$loader = new Zend_Loader_PluginLoader();
	$loader->addPrefixPath('Export_', 'My/Application/Export2CSV');
	$sales = $loader->load('Sales');
	Export_Sales::duplicated_imei($dup_imeis, $imei_model, $points, $timing_sales);
}

$this->view->areas = $QArea->get_cache();

$QRegionalMarket = new Application_Model_RegionalMarket();

if ($area_id) {
    $where = $QRegionalMarket->getAdapter()->quoteInto('area_id = ?', $area_id);
    $this->view->regional_markets = $QRegionalMarket->fetchAll($where);
}

if ($area_id_first) {
    $where = $QRegionalMarket->getAdapter()->quoteInto('area_id = ?', $area_id_first);
    $this->view->regional_markets_first = $QRegionalMarket->fetchAll($where);
}

$QStore = new Application_Model_Store();

if ($regional_market) {
    $where = $QStore->getAdapter()->quoteInto('regional_market = ?', $regional_market);
    $this->view->stores = $QStore->fetchAll($where);
}

if ($regional_market_first) {
    $where = $QStore->getAdapter()->quoteInto('regional_market = ?', $regional_market_first);
    $this->view->stores_first = $QStore->fetchAll($where);
}

$this->view->dup_imeis    = $dup_imeis;
$this->view->imei_model   = $imei_model;
$this->view->points       = $points;
$this->view->params       = $params;
$this->view->limit        = $limit;
$this->view->total        = $total;
$this->view->url          = HOST.'timing/imei-report'.( $params ? '?'.http_build_query($params).'&' : '?' );
$this->view->offset       = $limit*($page-1);
$this->view->timing_sales = $timing_sales;

$this->view->user_group   = $group_id;