<?php
$pm_id = $this->getRequest()->getParam('id');

$QPunishMemo = new Application_Model_PunishMemo();

$where = $QPunishMemo->getAdapter()->quoteInto('id = ?', $pm_id);
$QPunishMemo->delete($where);

$this->_redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : HOST.'manage/penalty-charge?penalty_type_id=2');