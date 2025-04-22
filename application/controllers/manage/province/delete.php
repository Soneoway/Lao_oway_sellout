<?php
$id = $this->getRequest()->getParam('id');

$QModel = new Application_Model_RegionalMarket();
$where = $QModel->getAdapter()->quoteInto('id = ?', $id);
$QModel->delete($where);

//remove cache
$cache = Zend_Registry::get('cache');
$cache->remove('regional_market_cache');
$cache->remove('regional_market_HCM_cache');
$cache->remove('asm_cache');

$this->_redirect('/manage/province');