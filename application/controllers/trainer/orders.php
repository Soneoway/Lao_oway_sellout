<?php
$sort                   = $this->getRequest()->getParam('sort', '');
$desc                   = $this->getRequest()->getParam('desc', 1);
$sn                     = $this->getRequest()->getParam('sn');
$asset_id               = $this->getRequest()->getParam('asset_id');
$confirmed_at_from      = $this->getRequest()->getParam('confirmed_at_from');
$confirmed_at_to        = $this->getRequest()->getParam('confirmed_at_to');
$created_at_from        = $this->getRequest()->getParam('created_at_from');
$created_at_to          = $this->getRequest()->getParam('created_at_to');
$status                 = $this->getRequest()->getParam('status');
$name                   = $this->getRequest()->getParam('name');
$userStorage            = Zend_Auth::getInstance()->getStorage()->read();


$params = array(
    'sn'                => $sn,
    'asset_id'          => $asset_id,
    'confirmed_at_from' => $confirmed_at_from,
    'confirmed_at_to'   => $confirmed_at_to,
    'created_at_from'   => $created_at_from,
    'created_at_to'     => $created_at_to,
    'status'            => $status
);

$params['sort'] = $sort;
$params['desc'] = $desc;


$QTrainerOrder         = new Application_Model_TrainerOrder();
$page           = $this->getRequest()->getParam('page', 1);
$limit          = LIMITATION;
$total          = 0;
$result         = $QTrainerOrder->fetchPagination($page, $limit, $total, $params);

$this->view->list    = $result;
$this->view->limit   = $limit;
$this->view->total   = $total;
$this->view->params  = $params;
$this->view->desc    = $desc;
$this->view->sort    = $sort;
$this->view->url     = HOST.'trainer/orders'.( $params ? '?'.http_build_query($params).'&' : '?' );
$this->view->offset  = $limit*($page-1);

$QStaff              = new Application_Model_Staff();
$staffs_cached       = $QStaff->get_cache();
$this->view->staffs_cached = $staffs_cached;


$QTrainerTypeAsset          = new Application_Model_TrainerTypeAsset();
$whereTypeAsset             = $QTrainerTypeAsset->getAdapter()->quoteInto('del = ? OR del IS NULL',0);
$type_asset                 = $QTrainerTypeAsset->fetchAll($whereTypeAsset);
$arrayTypeAsset             = array();
if($type_asset->count())
{
    foreach($type_asset as $key => $value)
    {
        $arrayTypeAsset[$value['id']] = $value['name'];
    }
}

$this->view->type_asset     = $arrayTypeAsset;

$flashMessenger       = $this->_helper->flashMessenger;
$messages             = $flashMessenger->setNamespace('success')->getMessages();
$this->view->messages = $messages;
$messages_error       = $flashMessenger->setNamespace('error')->getMessages();
$this->view->messages_error = $messages_error;