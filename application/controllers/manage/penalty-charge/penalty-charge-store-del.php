<?php
$pms_id = $this->getRequest()->getParam('id');

$QPunishMemoStore = new Application_Model_PunishMemoStore();

$where = $QPunishMemoStore->getAdapter()->quoteInto('id = ?', $pms_id);
$QPunishMemoStore->delete($where);

$this->_redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : HOST.'manage/penalty-charge?penalty_type_id=1');