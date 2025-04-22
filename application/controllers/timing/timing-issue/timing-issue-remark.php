<?php

$ti_id = $this->getRequest()->getParam('ti_id');
$userStorage = Zend_Auth::getInstance()->getStorage()->read();

$QTimingIssue = new Application_Model_TimingIssue();

$where = $QTimingIssue->getAdapter()->quoteInto('id = ?', $ti_id);
$result = $QTimingIssue->fetchRow($where);

$this->view->ti_id = $ti_id;
$this->view->group_id = $userStorage->group_id;
$this->view->issue_type = $result['issue_type'];
$this->view->remark = $result['remark'];

$this->_helper->layout->disableLayout();
$this->_helper->viewRenderer->setRender('timing-issue/timing-issue-remark');

?>