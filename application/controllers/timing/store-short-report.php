<?php
$from            = $this->getRequest()->getParam('from');
$to              = $this->getRequest()->getParam('to');
$name            = $this->getRequest()->getParam('name');
$area            = $this->getRequest()->getParam('area');
$regional_market = $this->getRequest()->getParam('regional_market');
$store_id        = $this->getRequest()->getParam('store_id');
$brand_id        = $this->getRequest()->getParam('brand_id');

$params = array(
    'from'            => $from,
    'to'              => $to,
    'name'            => $name,
    'area'            => $area,
    'regional_market' => $regional_market,
    'store_id'        => $store_id,
    'brand_id'        => $brand_id
);

$QTiming = new Application_Model_Timing();
$sales   = $QTiming->short_report_by_store($params);
$this->view->sales = $sales;

$this->_helper->layout->disableLayout();
$this->_helper->viewRenderer->setRender('partials/store_short_report');