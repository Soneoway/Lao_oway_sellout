<?php
$store_id = $this->getRequest()->getParam('store_id');
$from = $this->getRequest()->getParam('from');
$to = $this->getRequest()->getParam('to');

$params = array(
    'store_id' 	=> $store_id,
    'from' 		=> $from,
    'to' 		=> $to,
);

$QTiming = new Application_Model_Timing();
$sales   = $QTiming->short_report_by_stock_shop_sellout($params);
$this->view->sales = $sales;

$this->_helper->layout->disableLayout();
$this->_helper->viewRenderer->setRender('partials/stock-shop-sellout-short-report');