<?php
$QModel = new Application_Model_ExceptionCase();

$id = $this->getRequest()->getParam('id');
$name = $this->getRequest()->getParam('name');
$value = $this->getRequest()->getParam('value');
$status = $this->getRequest()->getParam('status');


$data = array(
    'name' => $name,
    'status' => $status,
    'value' => json_encode($value),
);

if ($id){
    $where = $QModel->getAdapter()->quoteInto('id = ?', $id);

    $QModel->update($data, $where);
} else {
    $QModel->insert($data);
}

//remove cache
$cache = Zend_Registry::get('cache');
$cache->remove('exception_case_cache');

$flashMessenger = $this->_helper->flashMessenger;
$flashMessenger->setNamespace('success')->addMessage('Done!');

$back_url = $this->getRequest()->getParam('back_url');

$this->_redirect( ( $back_url ? $back_url : HOST.'manage/exception-case' ) );