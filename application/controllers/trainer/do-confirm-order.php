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

    $QTrainerOrder                 = new Application_Model_TrainerOrder();
    $QTrainerOrderDetail           = new Application_Model_TrainerOrderDetail();
    $QTrainerInventoryAsset        = new Application_Model_TrainerInventoryAsset();
    $QTrainerInventoryHistoryAsset = new Application_Model_TrainerInventoryHistoryAsset();

    if($this->getRequest()->getMethod() == 'POST')
    {

        echo '<link href="/css/bootstrap.min.css" rel="stylesheet">';

        $order_id     = $this->getRequest()->getParam('order_id');
        $currentTime  = date('Y-m-d H:i:s');

        $whereOrder   = array();
        $whereOrder[] = $QTrainerOrder->getAdapter()->quoteInto('id = ?',$order_id);
        $whereOrder[] = $QTrainerOrder->getAdapter()->quoteInto('del = ? OR del IS NULL',0);
        $rowOrder     = $QTrainerOrder->fetchRow($whereOrder);

        if($rowOrder)
        {
            if($rowOrder['confirmed_at'])
                throw new Exception('SN IS COMPLETED - NOT CONFIRMED AGAIN !');

            // lay sn detail
            $whereOrderDetail   = array();
            $whereOrderDetail[] = $QTrainerOrderDetail->getAdapter()->quoteInto('order_id = ?',$order_id);
            $whereOrderDetail[] = $QTrainerOrderDetail->getAdapter()->quoteInto('del = ? OR del IS NULL',0);
            $rowsOrderDetail    = $QTrainerOrderDetail->fetchAll($whereOrderDetail);

            foreach($rowsOrderDetail as $key => $value)
            {
                // kiem tra xem coi trong inventory no co chua neu co roi thi cong vao khong thi insert vao
                $whereInventoryAsset = $QTrainerInventoryAsset->getAdapter()->quoteInto('asset_id = ?',$value['asset_id']);
                $rowInventoryAsset   = $QTrainerInventoryAsset->fetchRow($whereInventoryAsset);

                if($rowInventoryAsset)
                {
                    // update + so luong
                    $oldQuantity = $rowInventoryAsset['total_quantity'];
                    $newQuantity = $oldQuantity+$value['quantity'];
                    $id          = $rowInventoryAsset['id'];
                    $whereID     = $QTrainerInventoryAsset->getAdapter()->quoteInto('id = ?', $id);
                    $rowUpdate   = $QTrainerInventoryAsset->fetchRow($whereID);
                    if($rowUpdate)
                    {
                        $dataUpdate  = array(
                            'asset_id'      => $rowInventoryAsset['asset_id'],
                            'total_quantity'=> $newQuantity
                        );


                        /**  INVENTORY HISTORY GHI LẠI   **/
                        $params = array(
                            'asset_id'      => $rowInventoryAsset['asset_id'],
                            'quantity_in'   => $value['quantity'],
                            'ip'            => $ip,
                            'userStorage'   => $userStorage,
                            'time'          => $currentTime
                        );

                        $resultHistory = $QTrainerInventoryHistoryAsset->updateInventoryHistory($params);

                        if($resultHistory['code']!=0)
                            throw new Exception($resultHistory['message']);


                        /***** UPDATE ******/
                        // bat dau update nha bồ
                        $QTrainerInventoryAsset->update($dataUpdate,$whereID);

                        // to do log
                        $info = array('UPDATE INVENTORY ASSET', 'asset_id'=>$rowInventoryAsset['asset_id'], 'value'=>array('new'=>$dataUpdate,'old'=>$rowUpdate));
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
                    // insert -- hot cai dau ai cung chhe buon qua
                    $dataInsert = array(
                        'asset_id'       => $value['asset_id'],
                        'total_quantity' => $value['quantity']
                    );

                    /**  INVENTORY HISTORY GHI LẠI   **/
                    $params = array(
                        'asset_id'      => $value['asset_id'],
                        'quantity_in'   => $value['quantity'],
                        'ip'            => $ip,
                        'userStorage'   => $userStorage,
                        'time'          => $currentTime
                    );

                    $resultHistory = $QTrainerInventoryHistoryAsset->updateInventoryHistory($params);

                    if($resultHistory['code']!=0)
                        throw new Exception($resultHistory['message']);

                    $QTrainerInventoryAsset->insert($dataInsert);

                    // to do log
                    $info = array('INSERT_INVENTORY_ASSET','asset_id'=>$value['asset_id'],'value'=>array('new'=>$dataInsert));
                    $QLog->insert( array (
                        'info'          => json_encode($info),
                        'user_id'       => $userStorage->id,
                        'ip_address'    => $ip,
                        'time'          => $currentTime,
                    ) );

                }

            }
            // bat dau update status cua sn do lai nha bồ ken
            $dataUpdateStatusSn = array(
                'status'      => 2,
                'confirmed_at'=> $currentTime,
                'confirmed_by'=> $userStorage->id
            );

            $QTrainerOrder->update($dataUpdateStatusSn,$whereOrder);

            // to do log
            $info = array('UPDATE ORDER STATUS','new'=>$dataUpdateStatusSn);
            $QLog->insert( array (
                'info'          => json_encode($info),
                'user_id'       => $userStorage->id,
                'ip_address'    => $ip,
                'time'          => $currentTime,
            ) );


        }
        else
        {
            throw new Exception('Can not found SN');
        }

        $db->commit();
        $flashMessenger ->setNamespace('success')->addMessage('Done');
        echo '<script>setTimeout(function(){parent.location.href="'.HOST.'trainer/orders"})</script>';
    }
}
catch (Exception $e)
{
    $db->rollback();
    echo '<script>window.parent.document.getElementById("iframe").height = \'40px\';</script>';
    echo '<script>window.parent.unblockUI();</script>';
    echo '<script>window.parent.scroll2Top();</script>';
    echo '<div class="alert alert-error">Failed - '.$e->getMessage().'</div>';

}