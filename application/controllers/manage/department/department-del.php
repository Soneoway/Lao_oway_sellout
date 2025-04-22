<?php
$id = $this->getRequest()->getParam('id');

$QModel = new Application_Model_Department();
$where = $QModel->getAdapter()->quoteInto('id = ?', $id);
$QModel->delete($where);

//remove cache
$cache = Zend_Registry::get('cache');
$cache->remove('department_cache');

$this->_redirect('/manage/department');