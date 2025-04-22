<?php
$this->_helper->layout->disableLayout();
$this->_helper->viewRenderer->setNoRender();

$back_url   = $_SERVER['HTTP_REFERER'] ? $_SERVER['HTTP_REFERER']:'/trainer/list-product-info';

$flashMessenger     = $this->_helper->flashMessenger;
$userStorage        = Zend_Auth::getInstance()->getStorage()->read();
$QLog               = new Application_Model_Log();
$ip                 = $this->getRequest()->getServer('REMOTE_ADDR');
$db                 = Zend_Registry::get('db');

try
{

    $db->beginTransaction();

    $id               = $this->getRequest()->getParam('id');
    $QTrainerOrder    = new Application_Model_NewsInfo();
    $whereID          = $QTrainerOrder->getAdapter()->quoteInto('id = ?',$id);
    $rowID            = $QTrainerOrder->delete($whereID);
    
    // if($rowID)
    // {
    //     $dataDel = array(
    //         'del'=>1
    //     );

    //     // if($rowID['confirmed_at'])
    //     //     throw new Exception('Order Is Confirmed - Not Delete !');


    //     $QTrainerOrder->update($dataDel,$whereID);

    //     //to do log
    //     $info = array('Del Trainer Product Info','Product ID'=>$id,'value'=>$rowID);
    //     $QLog->insert( array (
    //         'info'          => json_encode($info),
    //         'user_id'       => $userStorage->id,
    //         'ip_address'    => $ip,
    //         'time'          => $currentTime,
    //     ) );

    // }

    $db->commit();
    $flashMessenger ->setNamespace('success')->addMessage('Done');
    $this->_redirect($back_url);
}
catch (Exception $e)
{
    $db->rollback();
    $flashMessenger ->setNamespace('error')->addMessage($e->getMessage());
    $this->_redirect( $back_url);
}
