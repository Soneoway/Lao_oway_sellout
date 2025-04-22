<?php
$id = $this->getRequest()->getParam('id');

try {
    $flashMessenger = $this->_helper->flashMessenger;

    if (!$id) throw new Exception("Invalid ID");
    
    $QCategory = new Application_Model_InformCategory();
    $category_check = $QCategory->find($id);
    $category_check = $category_check->current();

    if (!$category_check) throw new Exception("Invalid ID");
    
    $where = $QCategory->getAdapter()->quoteInto('id = ?', $id);
    $QCategory->delete($where);

    $flashMessenger->setNamespace('success')->addMessage('Success');
    $this->_redirect(HOST.'manage/inform-category');
} catch (Exception $e) {
    $flashMessenger->setNamespace('error')->addMessage($e->getMessage());
    $this->_redirect(HOST.'manage/inform-category');
}