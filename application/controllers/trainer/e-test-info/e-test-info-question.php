<?php
$this->_helper->layout->disableLayout();
$this->_helper->viewRenderer->setNoRender();

if ($this->getRequest()->getMethod() == 'POST') {
    $id = $this->getRequest()->getParam('id');

    $db = Zend_Registry::get('db');

    $q = $db->select()
        ->from(array('q' => 'questions'))
        ->where('q.id = ?', $id);
    $questions = $db->fetchRow($q);

    $qc = $db->select()
        ->from(array('qc' => 'questions_choice'))
        ->where('qc.question_id = ?', $id)
        ->order('qc.id', 'ASC');
    $questions_choice = $db->fetchAll($qc);

    $result = $questions;
    $result['choice'] = $questions_choice;
    $result['msg'] = 'Success';
} else {
    $result['msg'] = 'Method not allowed';
}

echo json_encode($result);
