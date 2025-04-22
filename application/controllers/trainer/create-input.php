<?php
$back_url                   = $_SERVER['HTTP_REFERER'] ? $_SERVER['HTTP_REFERER']:'/trainer/orders';
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

// edit
try
{
    $id                  = $this->getRequest()->getParam('id');
    $QTrainerOrder       = new Application_Model_TrainerOrder();
    $QTrainerOrderDetail = new Application_Model_TrainerOrderDetail();

    $whereID   = array();
    $whereID[] = $QTrainerOrder->getAdapter()->quoteInto('id = ?',$id);
    $whereID[] = $QTrainerOrder->getAdapter()->quoteInto('del = ? OR del IS NULL',0);
    $rowID     = $QTrainerOrder->fetchRow($whereID);

    if($rowID)
    {
        $this->view->date = $rowID['date'];

        if($rowID['confirmed_at'])
            throw new Exception('Order Is Confirmed - Not Edit !');

        // lay thong tin detail
        $whereOrderDetailID   = array();
        $whereOrderDetailID[] = $QTrainerOrderDetail->getAdapter()->quoteInto('order_id = ?',$id);
        $whereOrderDetailID[] = $QTrainerOrderDetail->getAdapter()->quoteInto('del = ? OR del IS NULL',0);
        $rowsDetail           = $QTrainerOrderDetail->fetchAll($whereOrderDetailID);
        if($rowsDetail->count())
        {
            $this->view->order       = $rowsDetail;
            $this->view->order_id    = $id;
        }

    }
}
catch (Exception $e)
{
   $flashMessenger ->setNamespace('error')->addMessage($e->getMessage());
   echo '<script>setTimeout(function(){parent.location.href="'.HOST.'trainer/orders"})</script>';;
}

