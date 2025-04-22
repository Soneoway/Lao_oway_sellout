<?php
if ($this->getRequest()->getMethod() == 'POST') {
    $QModel = new Application_Model_QuestionsHeader();

    $id = $this->getRequest()->getParam('id');
    $name = $this->getRequest()->getParam('name');
    $question_type= $this->getRequest()->getParam('question_type');
    $from_date = $this->getRequest()->getParam('from_date');
    $to_date = $this->getRequest()->getParam('to_date');
    $enable = $this->getRequest()->getParam('enable');

    $from_format = DateTime::createFromFormat('Y/m/d H:i', $from_date);
    $to_format = DateTime::createFromFormat('Y/m/d H:i', $to_date);

    $from = $from_format->format("Y-m-d H:i:00");
    $to = $to_format->format("Y-m-d H:i:00");

    $userStorage = Zend_Auth::getInstance()->getStorage()->read();

    $data = array(
        'name' => $name,
        'question_type' => $question_type,
        'from_date' => $from,
        'to_date' => $to,
    );

    if ($enable) {
        $data['enable'] = $enable;
    }

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
$this->_redirect(($back_url ? $back_url : HOST . 'trainer/index'));