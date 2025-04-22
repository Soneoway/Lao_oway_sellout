<?php
$id = $this->getRequest()->getParam('id');
if ($id) {
    $QModel = new Application_Model_PushNotification();
    $rowset = $QModel->find($id);
    $notification = $rowset->current();
    $this->view->notification = $notification;

    // if ($area->leader_ids){
    //     $leader_ids = explode(',', $area->leader_ids);
    //     $QStaff = new Application_Model_Staff();
    //     $where = $QStaff->getAdapter()->quoteInto('id IN (?)', $leader_ids);

    //     $this->view->leaders = $QStaff->fetchAll($where);
    // }
}

$flashMessenger = $this->_helper->flashMessenger;
$messages = $flashMessenger->setNamespace('error')->getMessages();
$this->view->messages = $messages;
//back url
$this->view->back_url = $this->getRequest()->getServer('HTTP_REFERER');
$this->_helper->viewRenderer->setRender('create-push-notification');