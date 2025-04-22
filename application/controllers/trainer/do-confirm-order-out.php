<?php
$this->_helper->layout->disableLayout();
$this->_helper->viewRenderer->setNoRender();

$flashMessenger     = $this->_helper->flashMessenger;
$userStorage        = Zend_Auth::getInstance()->getStorage()->read();
$QLog               = new Application_Model_Log();
$ip                 = $this->getRequest()->getServer('REMOTE_ADDR');
$db                 = Zend_Registry::get('db');

try
{

    $db->beginTransaction();

    $QTrainerOrderOut          = new Application_Model_TrainerOrderOut();
    $QTrainerOrderOutDetail    = new Application_Model_TrainerOrderOutDetail();
    $QTrainerInventoryAsset    = new Application_Model_TrainerInventoryAsset();
    $QTypeAssetTrainer         = new Application_Model_TrainerTypeAsset();
    $cachedAsset               = $QTypeAssetTrainer->get_cache();

    $QTrainerInventoryHistoryAsset = new Application_Model_TrainerInventoryHistoryAsset();


    if($this->getRequest()->getMethod() == 'POST')
    {

        echo '<link href="/css/bootstrap.min.css" rel="stylesheet">';

        $order_out_id          = $this->getRequest()->getParam('order_out_id');
        $currentTime           = date('Y-m-d H:i:s');

        $whereOrderOut   = array();
        $whereOrderOut[] = $QTrainerOrderOut->getAdapter()->quoteInto('id = ?',$order_out_id);
        $whereOrderOut[] = $QTrainerOrderOut->getAdapter()->quoteInto('del = ? OR del IS NULL',0);
        $rowOrderOut    = $QTrainerOrderOut->fetchRow($whereOrderOut);

        if($rowOrderOut['confirmed_at'])
            throw new Exception('SN IS COMPLETED - NOT CONFIRMED AGAIN !');

        if($rowOrderOut)
        {
            $whereOrderOutDetail   = array();
            $whereOrderOutDetail[] = $QTrainerOrderOutDetail->getAdapter()->quoteInto('order_out_id = ?',$order_out_id);
            $whereOrderOutDetail[] = $QTrainerOrderOutDetail->getAdapter()->quoteInto('del = ? OR del IS NULL',0);
            $rowsOrderOutDetail    = $QTrainerOrderOutDetail->fetchAll($whereOrderOutDetail);

            if($rowsOrderOutDetail->count())
            {
                foreach($rowsOrderOutDetail as $key => $value)
                {
                    $whereInventoryAsset = $QTrainerInventoryAsset->getAdapter()->quoteInto('asset_id = ?',$value['asset_id']);
                    $rowInventoryAsset   = $QTrainerInventoryAsset->fetchRow($whereInventoryAsset);
                    if($rowInventoryAsset)
                    {

                        $oldQuantity = $rowInventoryAsset['total_quantity'];

                        if(!$oldQuantity)
                            throw new Exception ($cachedAsset[$value['asset_id']].' not in inventory asset !');

                        $newQuantity = $oldQuantity - $value['quantity'];

                        if($newQuantity < 0)
                            throw new Exception ($cachedAsset[$value['asset_id']].' Quantity not enough !');

                        $id          = $rowInventoryAsset['id'];
                        $whereID     = $QTrainerInventoryAsset->getAdapter()->quoteInto('id = ?', $id);
                        $rowUpdate   = $QTrainerInventoryAsset->fetchRow($whereID);
                        if($rowUpdate)
                        {
                            $dataUpdate  = array(
                                'asset_id'      => $rowInventoryAsset['asset_id'],
                                'total_quantity'=> $newQuantity
                            );


                            /**  INVENTORY HISTORY GHI LAI   **/
                            $params = array(
                                'asset_id'      => $rowInventoryAsset['asset_id'],
                                'quantity_out'  => $value['quantity'],
                                'ip'            => $ip,
                                'userStorage'   => $userStorage,
                                'time'          => $currentTime
                            );

                            $resultHistory = $QTrainerInventoryHistoryAsset->updateInventoryHistory($params);

                            if($resultHistory['code']!=0)
                                throw new Exception($resultHistory['message']);

                            // bat dau update
                            $QTrainerInventoryAsset->update($dataUpdate,$whereID);

                            // to do log
                            $info = array('UPDATE_INVENTORY_ASSET_OUT','new'=>$dataUpdate,'old'=>$rowUpdate);
                            $QLog->insert( array (
                                'info'          => json_encode($info),
                                'user_id'       => $userStorage->id,
                                'ip_address'    => $ip,
                                'time'          => $currentTime,
                            ) );
                        }

                    }
                    else
                    {
                        throw new Exception(' Can Not Found Asset !');
                    }

                }
                // bat dau update status cua sn do lai nha bồ ken
                $dataUpdateStatusSn = array(
                    'status'      => 2,
                    'confirmed_at'=> $currentTime,
                    'confirmed_by'=> $userStorage->id
                );

                $QTrainerOrderOut->update($dataUpdateStatusSn,$whereOrderOut);

                // to do log
                $info = array('UPDATE ORDER STATUS','new'=>$dataUpdateStatusSn);
                $QLog->insert( array (
                    'info'          => json_encode($info),
                    'user_id'       => $userStorage->id,
                    'ip_address'    => $ip,
                    'time'          => $currentTime,
                ) );
            }




        }
        else
        {
            throw new Exception('Can not found SN');
        }

        $db->commit();
        $flashMessenger ->setNamespace('success')->addMessage('Done');
        echo '<script>setTimeout(function(){parent.location.href="'.HOST.'trainer/orders-out"})</script>';
    }
}
catch (Exception $e)
{
    $db->rollback();
    echo '<script>window.parent.document.getElementById("iframe").height = \'40px\';</script>';
    echo '<script>window.parent.unblockUI();</script>';
    echo '<div class="alert alert-error">Failed - '.$e->getMessage().'</div>';

}