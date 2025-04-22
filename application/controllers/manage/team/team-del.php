<?php
$id = $this->getRequest()->getParam('id');

$QModel = new Application_Model_Team();
$where = $QModel->getAdapter()->quoteInto('id = ?', $id);
$QModel->delete($where);

//remove cache
$cache = Zend_Registry::get('cache');
$cache->remove('team_cache');

$this->_redirect('/manage/team');