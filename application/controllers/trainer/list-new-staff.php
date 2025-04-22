<?php
$flashMessenger              = $this->_helper->flashMessenger;
$messages                    = $flashMessenger->setNamespace('success')->getMessages();
$this->view->message_success = $messages;
$messages_error              = $flashMessenger->setNamespace('error')->getMessages();
$this->view->messages        = $messages_error;

$userStorage                 = Zend_Auth::getInstance()->getStorage()->read();
$QLog                        = new Application_Model_Log();
$ip                          = $this->getRequest()->getServer('REMOTE_ADDR');
$db                          = Zend_Registry::get('db');

$QArea                       = new Application_Model_Area();
$this->view->cached_area     = $QArea->get_cache();

$QRegionalMarket                     = new Application_Model_RegionalMarket();
$this->view->cached_regional_market  = $QRegionalMarket->get_cache();

$cache = Zend_Registry::get('cache');
$cache->remove('asm_cache');
$cache->remove('regional_market_cache');

$page                        = $this->getRequest()->getParam('page', 1);
$name                        = $this->getRequest()->getParam('name');
$cmnd                        = $this->getRequest()->getParam('cmnd');
$area_id                     = $this->getRequest()->getParam('area_id');
$regional_market             = $this->getRequest()->getParam('regional_market');


$QStaffTrainer        = new Application_Model_StaffTrainer();
$QRegionalMarket      =  new Application_Model_RegionalMarket();
$getProvinceArea      = $QRegionalMarket->get_region_cache($area_id);
$cachedRegionalMarket = $QRegionalMarket->get_cache();

if($regional_market && $area_id)
{
    $resultProvinceSearch = array();

    $AreaTrainer     = $QStaffTrainer->getAreaTrainer($userStorage->id);

    $province        = array_keys($AreaTrainer['province']);

    $array_intersect = array_intersect($province,$getProvinceArea);

    foreach($array_intersect as $item )
    {
        $resultProvinceSearch[$item] = $cachedRegionalMarket[$item];
    }

    $this->view->province_search = $resultProvinceSearch;
}


$QStaffTraining              = new Application_Model_StaffTraining();

$params              = array_filter(array(
    'name'           => $name,
    'cmnd'           => $cmnd,
    'area_id'        => $area_id,
    'regional_market'=> $regional_market
));

$areaStaffTrainer               = $QStaffTrainer->getAreaTrainer($userStorage->id);
$this->view->area_trainer       = $areaStaffTrainer['area'];
$this->view->province_trainer   = $areaStaffTrainer['province'];

$limit                      = LIMITATION;
$total                      = 0;
$rows                       = $QStaffTraining->fetchPagination($page, $limit, $total, $params);

$this->view->params         = $params;
$this->view->list           = $rows;
$this->view->limit          = $limit;
$this->view->total          = $total;
$this->view->url            = HOST.'trainer/list-new-staff'.( $params ? '?'.http_build_query($params).'&' : '?' );
$this->view->offset         = $limit*($page-1);
$this->view->staff_id       = $userStorage->id;