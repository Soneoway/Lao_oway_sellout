<?php
$flashMessenger             = $this->_helper->flashMessenger;
$back_url                   = $_SERVER['HTTP_REFERER'] ? $_SERVER['HTTP_REFERER']:'/trainer/orders-out';
$this->view->back_url       = $back_url;

try
{
    $id                     = $this->getRequest()->getParam('id');
    $QTrainerOrderOut       = new Application_Model_TrainerOrderOut();
    $QTrainerOrderOutDetail = new Application_Model_TrainerOrderOutDetail();

    $whereID    = array();
    $whereID[]  = $QTrainerOrderOut->getAdapter()->quoteInto('id = ?',$id);
    $whereID[]  = $QTrainerOrderOut->getAdapter()->quoteInto('del = ? OR del IS NULL',0);
    $row        = $QTrainerOrderOut->fetchRow($whereID);

    if($row)
    {
        if($row['confirmed_at'])
            throw new Exception('Order out is confirmed !');

        $this->view->date = $row['date'];
        $whereOrderOutDetail   = array();
        $whereOrderOutDetail[] = $QTrainerOrderOutDetail->getAdapter()->quoteInto('order_out_id = ?',$id);
        $whereOrderOutDetail[] = $QTrainerOrderOutDetail->getAdapter()->quoteInto('del = ? OR del IS NULL',0);
        $rowsOrderOutDetail    = $QTrainerOrderOutDetail->fetchAll($whereOrderOutDetail);

        if($rowsOrderOutDetail->count())
        {
            $this->view->order_out    = $rowsOrderOutDetail;
            $this->view->order_out_id = $id;
        }

    }

    $this->view->list_trainer = $QTrainerOrderOut->getStaffTrainer();

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

}
catch (Exception $e)
{
    $flashMessenger ->setNamespace('error')->addMessage($e->getMessage());
    echo '<script>setTimeout(function(){parent.location.href="'.HOST.'trainer/orders-out"})</script>';;
}
