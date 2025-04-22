<?php
$this->_helper->layout->disableLayout();
$this->_helper->viewRenderer->setNoRender();

$back_url                   = $_SERVER['HTTP_REFERER'] ? $_SERVER['HTTP_REFERER']:'/trainer/orders-out';

$flashMessenger     = $this->_helper->flashMessenger;
$userStorage        = Zend_Auth::getInstance()->getStorage()->read();
$QLog               = new Application_Model_Log();
$ip                 = $this->getRequest()->getServer('REMOTE_ADDR');
$db                 = Zend_Registry::get('db');

try
{

    $db->beginTransaction();

    $id        = $this->getRequest()->getParam('id');
    $QTrainerOrderOut = new Application_Model_TrainerOrderOut();
    $whereID   = array();
    $whereID[] = $QTrainerOrderOut->getAdapter()->quoteInto('id = ?',$id);
    $whereID[] = $QTrainerOrderOut->getAdapter()->quoteInto('del = ? OR del IS NULL',0);
    $rowOrderOut  = $QTrainerOrderOut->fetchRow($whereID);

    if($rowOrderOut)
    {
        $dataDel = array(
            'del'=>1
        );


        if($rowOrderOut['confirmed_at'])
            throw new Exception('Order Is Confirmed - Not Delete !');


        $QTrainerOrderOut->update($dataDel,$whereID);

        //to do log
        $info = array('DEL ORDER OUT','Order ID'=>$id, 'value'=>$rowOrderOut);
        $QLog->insert( array (
            'info'          => json_encode($info),
            'user_id'       => $userStorage->id,
            'ip_address'    => $ip,
            'time'          => $currentTime,
        ) );

    }
    $db->commit();
    $flashMessenger ->setNamespace('success')->addMessage('Done');
    $this->_redirect($back_url);
}
catch (Exception $e)
{
    $db->rollback();
    $flashMessenger ->setNamespace('error')->addMessage($e->getMessage());
    $this->_redirect($back_url);
}
