<?php
$this->_helper->layout->disableLayout();
$this->_helper->viewRenderer->setNoRender();

if ($this->getRequest()->getMethod() == 'POST') {
    $id = $this->getRequest()->getParam('id');
    $topic_id = $this->getRequest()->getParam('topic_id');

    $db = Zend_Registry::get('db');

    //PC Active
    if ($topic_id == 0) {
        $get = array(
            'id' => 'paq2.id',
            'questions' => 'paq2.question',
            'title' => 'paq.question',
            'title_sort' => 'paq.sort',
            'sort' => 'paq2.sort',
        );

        $query = $db->select()
            ->from(array('paq' => 'pc_active_questions'), $get)
            ->join(array('paq2' => 'pc_active_questions'), 'paq2.parent_id  = paq.id', array())
            ->where('paq2.parent_id = ?', $id)
            ->order('paq2.sort', 'asc');
    } else {
        $get = array(
            'id' => 'clq.id',
            'questions' => 'clq.questions',
            'title' => 'clqt.title',
            'sort' => 'clq.sort',
        );

        $query = $db->select()
            ->from(array('clq' => 'check_list_questions'), $get)
            ->join(array('clqt' => 'check_list_questions_title'), 'clqt.id = clq.question_title_id', array())
            ->where('clq.question_title_id = ?', $id)
            ->order('clq.sort', 'asc');
    }

    $query_questions = $db->fetchAll($query);

    $result['list'] = $query_questions;
    $result['msg'] = 'Success';
} else {
    $result['msg'] = 'Method not allowed';
}

echo json_encode($result);
