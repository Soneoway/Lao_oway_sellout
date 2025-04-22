<?php

if ($this->getRequest()->getMethod() == 'GET') {
    $db = Zend_Registry::get('db');

    $id = $this->getRequest()->getParam('id');
    $title_id = $this->getRequest()->getParam('title_id');

    if ($id == 0) {
        $db->delete('pc_active_questions', array(
            'id = ?' => $title_id,
        ));

        $db->delete('pc_active_questions', array(
            'parent_id = ?' => $title_id,
        ));
    } else {
        $db->delete('check_list_questions_title', array(
            'id = ?' => $title_id,
        ));

        $db->delete('check_list_questions', array(
            'question_title_id = ?' => $title_id,
        ));
    }

    $back_url = $this->getRequest()->getParam('back_url');
    $this->_redirect(($back_url ? $back_url : HOST . 'trainer/manage-shop-check-create?id=' . $id));
} else {
    echo 'Error !!!';
}
