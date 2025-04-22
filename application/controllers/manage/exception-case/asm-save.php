<?php
$QModel = new Application_Model_ExceptionCase();

$id = $this->getRequest()->getParam('id');
$name = $this->getRequest()->getParam('name');
$value = $this->getRequest()->getParam('value');
$status = $this->getRequest()->getParam('status');
$s_result = $this->getRequest()->getParam('s_result');
$menus = $this->getRequest()->getParam('menus');

$flashMessenger = $this->_helper->flashMessenger;

if (!$s_result){

    $flashMessenger->setNamespace('error')->addMessage('Please choose someone!');

    $this->_redirect( ( $back_url ? $back_url : HOST.'manage/exception-case' ) );
}

//lấy exception
if ($value){

    $exception_case = json_decode($value, true);

    $exception_case = $exception_case ? $exception_case : array();

    $exception_case[$s_result] = $menus;

}


$data = array(
    'name' => $name,
    'status' => $status,
    'value' => json_encode($exception_case),
);

$where = $QModel->getAdapter()->quoteInto('id = ?', $id);
$QModel->update($data, $where);


//remove cache
$cache = Zend_Registry::get('cache');
$cache->remove('exception_case_cache');


$flashMessenger->setNamespace('success')->addMessage('Done!');

$back_url = $this->getRequest()->getParam('back_url');

$this->_redirect( ( $back_url ? $back_url : HOST.'manage/exception-case' ) );