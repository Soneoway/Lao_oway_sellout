<?php
$id = $this->getRequest()->getParam('id');
$flashMessenger = $this->_helper->flashMessenger;

try {
    $QArea = new Application_Model_SubArea();
    $sub_area = $QArea->find($id);
    $sub_area = $sub_area->current();

    if (!$sub_area) throw new Exception("Invalid ID");
    
    //if ($sub_area['parent'] == 0) throw new Exception("Invalid district");
    
    $where = $QArea->getAdapter()->quoteInto('id = ?', $id);
    $QArea->delete($where);

    $cache = Zend_Registry::get('cache');
    $cache->remove('asm_cache');

    $flashMessenger->setNamespace('success')->addMessage('Done');
} catch (Exception $e) {
    $flashMessenger->setNamespace('error')->addMessage($e->getMessage());
}

$this->_redirect(HOST.'manage/sub-area');