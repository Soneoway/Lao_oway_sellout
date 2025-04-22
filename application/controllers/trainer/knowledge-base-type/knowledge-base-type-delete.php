<?php
$this->_helper->layout->disableLayout();
$this->_helper->viewRenderer->setNoRender();

$back_url   = $_SERVER['HTTP_REFERER'] ? $_SERVER['HTTP_REFERER']:'/trainer/knowledge-base-type';

$flashMessenger     = $this->_helper->flashMessenger;
$userStorage        = Zend_Auth::getInstance()->getStorage()->read();
$db                 = Zend_Registry::get('db');

try
{
    $db->beginTransaction();

    $id               = $this->getRequest()->getParam('id');
    $QType            = new Application_Model_SalesKnowledgeBaseType();
    $whereID          = $QType->getAdapter()->quoteInto('id = ?',$id);
    $rowID            = $QType->delete($whereID);

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
