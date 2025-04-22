<?php
$id = $this->getRequest()->getParam('id');

$QModel = new Application_Model_Area();
$where = $QModel->getAdapter()->quoteInto('id = ?', $id);
$QModel->delete($where);

//remove cache
$cache = Zend_Registry::get('cache');
$cache->remove('area_cache');
$cache->remove('area_cache2');
$cache->remove('area_ASM_cache');
$cache->remove('regional_market_HCM_cache');
$cache->remove('asm_cache');

$this->_redirect('/manage/area');