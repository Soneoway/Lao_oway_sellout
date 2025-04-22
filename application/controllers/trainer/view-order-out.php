<?php
$back_url                   = $_SERVER['HTTP_REFERER'] ? $_SERVER['HTTP_REFERER']:'/trainer/orders-out';
$this->view->back_url       = $back_url;

$id         = $this->getRequest()->getParam('id');
$QTrainerOrderOut  = new Application_Model_TrainerOrderOut();

$whereID      = array();
$whereID[]    = $QTrainerOrderOut->getAdapter()->quoteInto('id = ?',$id);
$whereID[]    = $QTrainerOrderOut->getAdapter()->quoteInto('del = ? OR del IS NULL',0);
$rowOrderOut  = $QTrainerOrderOut->fetchRow($whereID);

if($rowOrderOut)
{
    $this->view->date           = $rowOrderOut['date'];

    $QTrainerOrderOutDetail       = new Application_Model_TrainerOrderOutDetail();
    $whereTrainerOrderOutDetail   = array();
    $whereTrainerOrderOutDetail[] = $QTrainerOrderOutDetail->getAdapter()->quoteInto('order_out_id = ?',$id);
    $whereTrainerOrderOutDetail[] = $QTrainerOrderOutDetail->getAdapter()->quoteInto('del = ? OR del IS NULL',0);
    $rowsOrderOutDetail           = $QTrainerOrderOutDetail->fetchAll($whereTrainerOrderOutDetail);
    if($rowsOrderOutDetail->count())
    {
        $this->view->order_out = $rowsOrderOutDetail;
    }

}

$this->view->list_trainer   = $QTrainerOrderOut->getStaffTrainer();

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