<?php

$store_id = $this->getRequest()->getParam('id');

$QStoreFocus = new Application_Model_StoreFocus();

// Remove Store Focus  
$where = $QStoreFocus->getAdapter()->quoteInto('store_id = ?', $store_id);
$QStoreFocus->delete($where);

$this->_redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : HOST.'manage/store-focus-list');