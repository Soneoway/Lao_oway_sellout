<?php
$this->_helper->layout->disableLayout();
$this->_helper->viewRenderer->setNoRender();
$itemModel          = new Application_Model_PushNotification();
$id = $this->getRequest()->getParam('id');
$display = $this->getRequest()->getParam('display');
$result = array();

    $data = $itemModel->getPushNotification();

    if ($data){
        $result['push_msg'] = $data;
        $result['success'] = "COMPLETE";
    } else {
        $result['success'] = "FAIL";
    }

echo json_encode($result);