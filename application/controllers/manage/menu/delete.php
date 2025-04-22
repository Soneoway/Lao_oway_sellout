<?php
$id = $this->getRequest()->getParam('id');

$QModel = new Application_Model_Menu();
$where = $QModel->getAdapter()->quoteInto('id = ?', $id);
$QModel->delete($where);

//remove cache
$cache = Zend_Registry::get('cache');
$cache->remove('menu');

$this->_redirect('/manage/menu');