<?php
$id = $this->getRequest()->getParam('id');

$QModel = new Application_Model_Inform();
$where = $QModel->getAdapter()->quoteInto('id = ?', $id);
$data = array('status' => 0);

try {
    $QModel->update($data, $where);
} catch (Exception $e) {
    
}

$this->_redirect('/manage/inform');