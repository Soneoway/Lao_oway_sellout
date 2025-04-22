<?php
$id = $this->getRequest()->getParam('id');

$QModel = new Application_Model_ContractTerm();
$where = $QModel->getAdapter()->quoteInto('id = ?', $id);
$QModel->delete($where);

//remove cache
$cache = Zend_Registry::get('cache');
$cache->remove('contract_term_cache');

$this->_redirect('/manage/contract-term');