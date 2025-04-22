<?php
$id = $this->getRequest()->getParam('id');
$flashMessenger = $this->_helper->flashMessenger;

try {
    $QRegion = new Application_Model_RegionalMarket();
    $region = $QRegion->find($id);
    $region = $region->current();

    if (!$region) throw new Exception("Invalid ID");
    
    if ($region['parent'] == 0) throw new Exception("Invalid district");
    
    $where = $QRegion->getAdapter()->quoteInto('id = ?', $id);
    $QRegion->delete($where);

    $cache = Zend_Registry::get('cache');
    $cache->remove('asm_cache');

    $flashMessenger->setNamespace('success')->addMessage('Done');
} catch (Exception $e) {
    $flashMessenger->setNamespace('error')->addMessage($e->getMessage());
}

$this->_redirect(HOST.'manage/district');