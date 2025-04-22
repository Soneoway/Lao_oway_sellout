<?php
if ($this->getRequest()->getMethod() == 'POST') {
    $QModel = new Application_Model_ProductInfoContent();

    $id = $this->getRequest()->getParam('id');
    $title_id = $this->getRequest()->getParam('title_id');
    $name = $this->getRequest()->getParam('name');
    $content = $this->getRequest()->getParam('content');
    $enable = $this->getRequest()->getParam('enable', 'Y');

    $userStorage = Zend_Auth::getInstance()->getStorage()->read();


    $data = array(
        'title_id' => $title_id,
        'name' => $name,
        'content' => $content,
        'enable' => $enable,
        'updated_at' => date('Y-m-d H:i:s'),
        'updated_by' => $userStorage->id,
    );

    if ($id) {
        $where = $QModel->getAdapter()->quoteInto('id = ?', $id);
        $QModel->update($data, $where);
    } else {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['created_by'] = $userStorage->id;
        $QModel->insert($data);
    }

    $flashMessenger = $this->_helper->flashMessenger;
    $flashMessenger->setNamespace('success')->addMessage('Done!');
}

$back_url = $this->getRequest()->getParam('back_url');
$this->_redirect(($back_url ? $back_url : HOST . 'trainer/index'));