<?php
$sort                   = $this->getRequest()->getParam('sort', '');
$desc                   = $this->getRequest()->getParam('desc', 1);
$asset_id               = $this->getRequest()->getParam('asset_id');
$userStorage            = Zend_Auth::getInstance()->getStorage()->read();


$params = array(
    'asset_id'          => $asset_id
);

$params['sort'] = $sort;
$params['desc'] = $desc;

$QTrainerTypeAsset          = new Application_Model_TrainerTypeAsset();
$QTrainerInventoryAsset            = new Application_Model_TrainerInventoryAsset();
$cached_Asset               = $QTrainerTypeAsset->get_cache();
$this->view->cached_asset   = $cached_Asset;

$page                       = $this->getRequest()->getParam('page', 1);
$limit                      = LIMITATION;
$total                      = 0;
$result                     = $QTrainerInventoryAsset->fetchPagination($page, $limit, $total, $params);

$this->view->list    = $result;
$this->view->limit   = $limit;
$this->view->total   = $total;
$this->view->params  = $params;
$this->view->desc    = $desc;
$this->view->sort    = $sort;
$this->view->url     = HOST.'trainer/inventory-asset'.( $params ? '?'.http_build_query($params).'&' : '?' );
$this->view->offset  = $limit*($page-1);


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