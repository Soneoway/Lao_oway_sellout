<?php
ini_set('max_execution_time', 90);
$page            = $this->getRequest()->getParam('page', 1);
$limit = LIMITATION;
$total = $total2 = 0;


$by      = $this->getRequest()->getParam('by');
$distributor_type      = $this->getRequest()->getParam('distributor_type');
$distributor_id        = $this->getRequest()->getParam('distributor_id');
$distributor_name      = $this->getRequest()->getParam('distributor_name');
$type      = $this->getRequest()->getParam('type');
$store_id        = $this->getRequest()->getParam('store_id');
$store_name      = $this->getRequest()->getParam('store_name');
$export          = $this->getRequest()->getParam('export', 0);
$area_id         = $this->getRequest()->getParam('area_id');
$regional_market = $this->getRequest()->getParam('regional_market');
$district        = $this->getRequest()->getParam('district');
$from            = $this->getRequest()->getParam('from', date('Y-m-01'));
$to              = $this->getRequest()->getParam('to', date('Y-m-d'));



$params = array(
    'by'            => $by,
    'type'            => $type,
    'store_id'        => $store_id,
    'store_name'      => $store_name,
    'distributor_type'=> $distributor_type,
    'distributor_id'        => $distributor_id,
    'distributor_name'      => $distributor_name,
    'area_id'         => $area_id,
    'regional_market' => $regional_market,
    'district'        => $district,
    'from'            => $from,
    'to'              => $to, 
    'export'          => $export,
);

//////////////////////search org///////////////////////////////////////////


$QOrg = new Application_Model_Org();
$where = "";
$org = $QOrg->getAll($where);
$this->view->org = $org;
$where = array('27','32','38');
 // $where = $QOrg->getAdapter()->quoteInto("o.store_type_ido = 1 OR o.org_id IN('27','32')");
 // $where = $QOrg->getAdapter()->quoteInto('org_id IN (?)', array('27','32'));
// $where = "o.store_type_ido = '1' OR o.org_id IN('27','32')";
$distributor_type_select = $QOrg->getAll($where);
$this->view->distributor_type = $distributor_type_select;
//////////////////////start search area////////////////////////////////
$QArea = new Application_Model_Area();
$this->view->areas = $QArea->fetchAll(null, 'name');

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
////////////////////////////////////end search area ////////////////////////////////////////////
$QTiming = new Application_Model_Timing();

if ( isset($export) && $export ) {

    if ($export == 1) { 

        if($by == 2){

            $sellout_export = $QTiming->getAmSellout($page, $limit, $total, $params);    
            $this->_exportExcelSellin($sellout_export);

        } else {

            $sellin_export = $QTiming->getAmSellin($page, $limit, $total, $params);    
            $this->_exportExcelSellin($sellin_export);

        }
       
    }

} else {

    if ($type || $distributor_type || $type != "" || $distributor_type!="") {   
        
        if($by == 2){
            
            $sellout = $QTiming->getAmSellout($page, $limit, $total, $params);
            $this->view->sellout = $sellout;

            $params['get_total_sales'] = true;
            $total_sales = $QTiming->getAmSellout(null, null, $total2, $params);

        } else {

            $sellin = $QTiming->getAmSellin($page, $limit, $total, $params);
            $this->view->sellout = $sellin;

            $params['get_total_sales'] = true;
            $total_sales = $QTiming->getAmSellin(null, null, $total2, $params);
        }
        
        $this->view->total_sales = $total_sales;
        unset($params['get_total_sales']);
    } 

}

$this->view->params = $params;
$this->view->limit = $limit;
$this->view->total = $total;
$this->view->url = HOST.'timing/am-sellout/'.( $params ? '?'.http_build_query($params).'&' : '?' );
$this->view->offset = $limit*($page-1);

$flashMessenger = $this->_helper->flashMessenger;
$messages = $flashMessenger->setNamespace('success')->getMessages();
$this->view->messages = $messages;

