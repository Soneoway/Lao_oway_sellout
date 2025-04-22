<?php
$this->_helper->layout->disableLayout();
$this->_helper->viewRenderer->setNoRender();

$back_url   = $_SERVER['HTTP_REFERER'] ? $_SERVER['HTTP_REFERER']:'/trainer/product-info-new';

$flashMessenger     = $this->_helper->flashMessenger;
$userStorage        = Zend_Auth::getInstance()->getStorage()->read();
$QLog               = new Application_Model_Log();
$ip                 = $this->getRequest()->getServer('REMOTE_ADDR');
$db                 = Zend_Registry::get('db');

try
{
    $db->beginTransaction();

    $id               = $this->getRequest()->getParam('id');
    $Qknowledge       = new Application_Model_ProductInfoContent();
    $whereID          = $Qknowledge->getAdapter()->quoteInto('id = ?',$id);
    $rowID            = $Qknowledge->delete($whereID);

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
