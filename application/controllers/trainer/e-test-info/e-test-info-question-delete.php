<?php

if ($this->getRequest()->getMethod() == 'GET') {
    $db = Zend_Registry::get('db');

    $id = $this->getRequest()->getParam('id');
    $question_id = $this->getRequest()->getParam('question_id');

    $db->delete('questions', array(
        'id = ?' => $question_id,
    ));

    $db->delete('questions_choice', array(
        'question_id = ?' => $question_id,
    ));

    $back_url = $this->getRequest()->getParam('back_url');
    $this->_redirect(($back_url ? $back_url : HOST . 'trainer/e-test-info-create?id=' . $id));
} else {
    echo 'Error !!!';
}
