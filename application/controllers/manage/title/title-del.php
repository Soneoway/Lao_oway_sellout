<?php
$id = $this->getRequest()->getParam('id');

$QModel = new Application_Model_Title();
$where = $QModel->getAdapter()->quoteInto('id = ?', $id);
$QModel->delete($where);

//remove cache
$cache = Zend_Registry::get('cache');
$cache->remove('title_cache');

$this->_redirect('/manage/title');