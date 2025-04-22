<?php
$id = $this->getRequest()->getParam('id');
$flashMessenger = $this->_helper->flashMessenger;

try {
    $QDistrict = new Application_Model_SubDistrict();
    $sub_istrict = $QDistrict->find($id);
    $sub_istrict = $sub_istrict->current();

    if (!$sub_istrict) throw new Exception("Invalid ID");
    
    //if ($sub_istrict['parent'] == 0) throw new Exception("Invalid district");
    
    $where = $QDistrict->getAdapter()->quoteInto('id = ?', $id);
    $QDistrict->delete($where);

    $cache = Zend_Registry::get('cache');
    $cache->remove('asm_cache');

    $flashMessenger->setNamespace('success')->addMessage('Done');
} catch (Exception $e) {
    $flashMessenger->setNamespace('error')->addMessage($e->getMessage());
}

$this->_redirect(HOST.'manage/sub-district');