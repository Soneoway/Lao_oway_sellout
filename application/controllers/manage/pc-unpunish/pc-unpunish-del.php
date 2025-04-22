<?php
$su_id = $this->getRequest()->getParam('id');

$QStaffUnPunish = new Application_Model_StaffUnPunish();

$where = $QStaffUnPunish->getAdapter()->quoteInto('id = ?', $su_id);
$QStaffUnPunish->delete($where);

$this->_redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : HOST.'manage/pc-unpunish');