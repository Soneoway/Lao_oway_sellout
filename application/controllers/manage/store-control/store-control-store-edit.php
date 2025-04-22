<?php

$store_control_id = $this->getRequest()->getParam('id');

$QStoreControl = new Application_Model_StoreControl();
$QStoreControlMap = new Application_Model_StoreControlMap();

$store_control_binding = $QStoreControl->store_control_binding($store_control_id);
$this->view->store_control_binding = $store_control_binding;

$store_list = $QStoreControlMap->store_list($store_control_id);
$this->view->store_list = $store_list;

$flashMessenger = $this->_helper->flashMessenger;
$messages = $flashMessenger->setNamespace('success')->getMessages();
$this->view->messages = $messages;

$this->view->back_url = $this->getRequest()->getServer('HTTP_REFERER');
$this->_helper->viewRenderer->setRender('store-control/store-edit');
