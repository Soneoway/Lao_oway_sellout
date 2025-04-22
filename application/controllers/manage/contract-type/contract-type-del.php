<?php
$id = $this->getRequest()->getParam('id');

$QModel = new Application_Model_ContractType();
$where = $QModel->getAdapter()->quoteInto('id = ?', $id);
$QModel->delete($where);

//remove cache
$cache = Zend_Registry::get('cache');
$cache->remove('contract_type_cache');

$this->_redirect('/manage/contract-type');