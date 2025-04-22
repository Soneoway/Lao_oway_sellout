<?php
$back_url                   = $_SERVER['HTTP_REFERER'] ? $_SERVER['HTTP_REFERER']:'/trainer/orders-out';
$this->view->back_url       = $back_url;
$flashMessenger             = $this->_helper->flashMessenger;

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

try
{
    // danh sach trainer lay tu staff
    $QTrainerOrderOut  = new Application_Model_TrainerOrderOut();
    $this->view->list_trainer = $QTrainerOrderOut->getStaffTrainer();

    // edit
    $id             = $this->getRequest()->getParam('id');
    $whereID        = array();
    $whereID[]      = $QTrainerOrderOut->getAdapter()->quoteInto('id = ?',$id);
    $whereID[]      = $QTrainerOrderOut->getAdapter()->quoteInto('del = ? OR del IS NULL',0);
    $rowOrderOut    = $QTrainerOrderOut->fetchRow($whereID);

    if($rowOrderOut)
    {
        if($rowOrderOut['confirmed_at'])
            throw new Exception('Order Out IS Confirmed - Not edit !');

        $this->view->date = $rowOrderOut['date'];

        $QTrainerOrderOutDetail       = new Application_Model_TrainerOrderOutDetail();
        $whereTrainerOrderOutDetail   = array();
        $whereTrainerOrderOutDetail[] = $QTrainerOrderOutDetail->getAdapter()->quoteInto('order_out_id = ?',$id);
        $whereTrainerOrderOutDetail[] = $QTrainerOrderOutDetail->getAdapter()->quoteInto('del = ? OR del IS NULL',0);
        $rowsOrderOut                 = $QTrainerOrderOutDetail->fetchAll($whereTrainerOrderOutDetail);
        if($rowsOrderOut->count())
        {
            $this->view->order_out     = $rowsOrderOut;
            $this->view->order_out_id  = $id;
        }

    }
}
catch (Exception $e)
{
    $flashMessenger ->setNamespace('error')->addMessage($e->getMessage());
    echo '<script>setTimeout(function(){parent.location.href="'.HOST.'trainer/orders-out"})</script>';;
}
