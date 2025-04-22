<?php
if ($this->getRequest()->getMethod() == 'POST') {
    $QModel = new Application_Model_CustomerSurvey();

    $id = $this->getRequest()->getParam('id');
    $topic = $this->getRequest()->getParam('topic');
    $url = $this->getRequest()->getParam('url');
    $enable = $this->getRequest()->getParam('enable');

    $userStorage = Zend_Auth::getInstance()->getStorage()->read();

    $data = array(
        'topic' => $topic,
        'url' => $url,
        'enable' => $enable ? $enable : 'N',
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