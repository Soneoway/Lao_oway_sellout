<?php
$page        = $this->getRequest()->getParam('page', 1);
$name        = $this->getRequest()->getParam('name');
$area_id     = $this->getRequest()->getParam('area_id');
$region_id   = $this->getRequest()->getParam('region_id');
$district_id = $this->getRequest()->getParam('district_id');
$sort        = $this->getRequest()->getParam('sort', 'title');
$desc        = $this->getRequest()->getParam('desc', 0);

$params = array(
    'name'        => $name,
    'area_id'     => $area_id,
    'region_id'   => $region_id,
    'district_id' => $district_id,
    'sort'        => $sort,
    'desc'        => $desc,
    // 'no_parent' => 1,
);

$limit = LIMITATION;
$total = 0;

$QDistributor = new Application_Model_Distributor();
$QArea = new Application_Model_Area();
$QRegion = new Application_Model_RegionalMarket();
$this->view->areas = $QArea->get_cache();

if ($area_id)
    $this->view->regions = $QRegion->nget_province_by_area_cache($area_id);

if ($region_id)
    $this->view->districts = $QRegion->nget_district_by_province_cache($region_id);

$distributors = $QDistributor->fetchPagination($page, $limit, $total, $params);

$this->view->distributors = $distributors;
$this->view->params       = $params;
$this->view->limit        = $limit;
$this->view->total        = $total;
$this->view->desc         = $desc;
$this->view->sort         = $sort;
$this->view->offset       = $limit*($page-1);
$this->view->url          = HOST.'manage/dealer'.( $params ? '?'.http_build_query($params).'&' : '?' );

$flashMessenger           = $this->_helper->flashMessenger;

$this->view->messages       = $flashMessenger->setNamespace('success')->getMessages();
$this->view->messages_error = $flashMessenger->setNamespace('error')->getMessages();

$this->_helper->viewRenderer->setRender('dealer/index');