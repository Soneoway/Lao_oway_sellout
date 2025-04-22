<?php
set_time_limit(0);
ini_set('memory_limit', '200M');

$params        = $this->getRequest()->getParam('params');
$sort          = $this->getRequest()->getParam('sort', '');
$page          = $this->getRequest()->getParam('page', 1);
$desc          = $this->getRequest()->getParam('desc', 1);
$export        = $this->getRequest()->getParam('export');
$from          = $this->getRequest()->getParam('from');
$to            = $this->getRequest()->getParam('to');

$imei          = $this->getRequest()->getParam('imei');

$limit = LIMITATION;
$total = 0;

$QTimingControl = new Application_Model_TimingControl();
$QBrand  = new Application_Model_Brand();


$params = array_filter(array(
    'imei'    => $imei,
    'from'    => $from,
    'to'      => $to
));

$imei = explode("\r\n", $imei);
$data = $QTimingControl->fetchPagination($page, $limit, $total, $params);


$this->view->brands = $QBrand->get_cache();
$this->view->data = $data;
$this->view->desc   = $desc;
$this->view->sort   = $sort;
$this->view->params = $params;
$this->view->limit  = $limit;
$this->view->total  = $total;
$this->view->url    = HOST.'timing/timing-control'.( $params ? '?'.http_build_query($params).'&' : '?' );
$this->view->offset = $limit*($page-1);

if ($this->getRequest()->isXmlHttpRequest()) {
    $this->_helper->layout->disableLayout();
    $this->_helper->viewRenderer->setRender('timing/timing-control');
}