<?php
$cat_id           = $this->getRequest()->getParam('cat_id');
$s_id             = $this->getRequest()->getParam('s_id');
$status           = $this->getRequest()->getParam('status');
$stock 		      = $this->getRequest()->getParam('stock');

$params = array(
	'cat_id'	=> $cat_id,
	's_id'		=> $s_id,
	'status'	=> $status,
	'stock'		=> $stock
);

$QTiming    = new Application_Model_Timing();
$QBrand     = new Application_Model_Brand();

$params['get_total_count'] = 0;
$total_count = $QTiming->DistributorStockDetail($params);

$detail = $QTiming->DistributorStockDetail($params);

$this->view->brands = $QBrand->get_cache();
$this->view->params = $params;
$this->view->detail = $detail;
$this->view->total_count = $total_count;

$this->_helper->viewRenderer->setRender('distributor-doi/partials/stock_detail');


