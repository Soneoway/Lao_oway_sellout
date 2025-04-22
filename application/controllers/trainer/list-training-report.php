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

$page                        = $this->getRequest()->getParam('page', 1);
$sort                        = $this->getRequest()->getParam('sort', '');
$desc                        = $this->getRequest()->getParam('desc', 1);
$date                        = $this->getRequest()->getParam('date',null);
$dealer                      = $this->getRequest()->getParam('dealer',null);
$store                       = $this->getRequest()->getParam('store',null);
$area                        = $this->getRequest()->getParam('area',null);
$province                    = $this->getRequest()->getParam('province',null);
$type                        = $this->getRequest()->getParam('type',null);
$from_date                   = $this->getRequest()->getParam('from_date',null);
$to_date                     = $this->getRequest()->getParam('to_date',null);

$QArea                       = new Application_Model_Area();
$this->view->cachedArea      = $QArea->get_cache();

$QRegionalMarket                  = new Application_Model_RegionalMarket();
$this->view->cachedRegionalMarket = $QRegionalMarket->get_cache();

$QStaffTrainingReport        = new Application_Model_StaffTrainingReport();
$staff_id                    = $userStorage->id;
$QStore                      = new Application_Model_Store();
$this->view->cachedStore     = $QStore->get_cache();

$QStaffTrainer               = new Application_Model_StaffTrainer();
$areaTrainer                 = $QStaffTrainer->getAreaTrainer($staff_id);
$regional_market             = $areaTrainer['district'];
$this->view->areaTrainer     = $areaTrainer['area'];
$this->view->provinceTrainer = $areaTrainer['province'];

$nameDealer                  = $QStaffTrainingReport->getDealerArea($regional_market);
$this->view->name_dealer     = $nameDealer;

if($dealer){
    //get store
    $whereStore     = array();
    $whereStore[]   = $QStore->getAdapter()->quoteInto('d_id = ?',$dealer);
    $whereStore[]   = $QStore->getAdapter()->quoteInto('del = ? OR del IS NULL',0);
    $rows           = $QStore->fetchAll($whereStore);

    $arrayStore = array();
    if($rows->count())
    {
        foreach($rows as $key => $value)
        {
            $arrayStore[$value['id']] = $value['name'];
        }
    }

    $this->view->list_store = $arrayStore;
}

$params             = array_filter(array(
    'staff_id'      =>$staff_id,
    'date'          =>$date,
    'dealer'        =>$dealer,
    'store'         =>$store,
    'type'          =>$type,
    'area'          =>$area,
    'province'      =>$province,
    'from_date'     =>$from_date,
    'to_date'       =>$to_date,
    'sort'          =>$sort,
    'desc'          =>$desc
));

$full_rights_trainer = unserialize(FULL_RIGHTS_TRAINER);

if(in_array($userStorage->title,$full_rights_trainer)  || $userStorage->group_id == ADMINISTRATOR_ID)
{
    unset($params['staff_id']);
}

$limit                      = LIMITATION;
$total                      = 0;
$rows                       = $QStaffTrainingReport->fetchPagination($page, $limit, $total, $params);

$this->view->desc           = $desc;
$this->view->sort           = $sort;
$this->view->params         = $params;
$this->view->list           = $rows;
$this->view->limit          = $limit;
$this->view->total          = $total;
$this->view->url            = HOST.'trainer/list-training-report'.( $params ? '?'.http_build_query($params).'&' : '?' );
$this->view->offset         = $limit*($page-1);