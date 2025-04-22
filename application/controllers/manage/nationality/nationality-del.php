<?php
$id = $this->getRequest()->getParam('id');

$QModel = new Application_Model_Nationality();
$where = $QModel->getAdapter()->quoteInto('id = ?', $id);
$QModel->delete($where);

//remove cache
$cache = Zend_Registry::get('cache');
$cache->remove('nationality_cache');

$this->_redirect('/manage/nationality');