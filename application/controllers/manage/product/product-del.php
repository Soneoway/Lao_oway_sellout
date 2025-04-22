<?php
$id = $this->getRequest()->getParam('id');

$QModel = new Application_Model_Product();
$where = $QModel->getAdapter()->quoteInto('id = ?', $id);
$QModel->delete($where);

//remove cache
$cache = Zend_Registry::get('cache');
$cache->remove('product_cache');
$cache->remove('product_cache2');

$this->_redirect('/manage/product');