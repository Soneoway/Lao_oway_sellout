<?php
if ($this->getRequest()->getMethod() == 'POST') {
    $QModel = new Application_Model_SalesKnowledgeBaseType();

    $id = $this->getRequest()->getParam('id');
    $name = $this->getRequest()->getParam('name');
    $enable = $this->getRequest()->getParam('enable');

    $data = array(
        'name' => $name,
        'enable' => $enable,
    );

    if ($id) {
        $where = $QModel->getAdapter()->quoteInto('id = ?', $id);
        $QModel->update($data, $where);
    } else {
        $QModel->insert($data);
    }

    $flashMessenger = $this->_helper->flashMessenger;
    $flashMessenger->setNamespace('success')->addMessage('Done!');
}

$back_url = $this->getRequest()->getParam('back_url');
$this->_redirect(($back_url ? $back_url : HOST . 'trainer/knowledge-base-type'));