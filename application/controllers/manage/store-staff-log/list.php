<?php
$staff_code_tmp     = $this->getRequest()->getParam('staff_code');
$is_leader = $this->getRequest()->getParam('is_leader');
$store_id_tmp     = $this->getRequest()->getParam('store_id');
$store_name = $this->getRequest()->getParam('store_name');
$region_id     = $this->getRequest()->getParam('region_id');

if($staff_code_tmp){$staff_code = explode("\r\n", $staff_code_tmp);}  
if($store_id_tmp){$store_id = explode("\r\n", $store_id_tmp);}  

$params = array(
    'staff_code'     => $staff_code,
    'is_leader'   => $is_leader,
    'store_id'       => $store_id,
    'store_name'     => $store_name,
    'region_id'    => $region_id,
);

$QModel = new Application_Model_StoreStaffLog();
$this->view->storestaffs = $QModel->fetchPaginationStoreStaffLog($params);

$this->view->params = $params;
$this->view->url = HOST.'manage/store-staff-log/'.( $params ? '?'.http_build_query($params).'&' : '?' );

$flashMessenger = $this->_helper->flashMessenger;
$messages = $flashMessenger->setNamespace('error')->getMessages();
$messages_success = $flashMessenger->setNamespace('success')->getMessages();
$this->view->messages_success = $messages_success;
$this->view->messages = $messages;

if($this->getRequest()->isXmlHttpRequest()) {
    $this->_helper->layout->disableLayout();
    $this->_helper->viewRenderer->setRender('store-staff-log/partials/searchname');
} else {
    $this->_helper->viewRenderer->setRender('store-staff-log/index');
}