<?php

if ($this->getRequest()->getMethod() == 'POST') {
    $db = Zend_Registry::get('db');

    $question_id = $this->getRequest()->getParam('question_id');
    $head_id = $this->getRequest()->getParam('head_id');
    $questions = $this->getRequest()->getParam('questions');
    $title_sort = $this->getRequest()->getParam('title_sort');
    $choice_list = $this->getRequest()->getParam('choice_list');

    $userStorage = Zend_Auth::getInstance()->getStorage()->read();

//    echo '<pre>';
//    print_r($_POST);
//    echo '</pre>';
//    die;

    $choice_id = array();
    foreach ($choice_list as $item) {
        $choice_id[] = $item['id'];
    }

    //PC Active
    if ($head_id == 0) {
        $data = array(
            'sort' => $title_sort,
            'question' => $questions,
        );

        if ($question_id) {
            $db->update('pc_active_questions', $data, "id = $question_id");
            if (!empty($choice_list)) {
                //Query check
                $query = $db->select()
                    ->from(array('paq' => 'pc_active_questions'), 'paq.id')
                    ->where('paq.id NOT IN (?)', $choice_id)
                    ->where('paq.parent_id = ?', $question_id);
                $qchoice = $db->fetchAll($query);

                //Delete choice
                foreach ($qchoice as $item) {
                    $db->delete('pc_active_questions', array('id = ?' => $item['id']));
                }

                foreach ($choice_list as $item) {
                    $choice = array(
                        'parent_id' => $question_id,
                        'question' => $item['questions'],
                        'sort' => $item['sort'],
                    );
                    if ($item['id']) {
                        $db->update('pc_active_questions', $choice, "id = {$item['id']}");
                    } else {
                        $db->insert('pc_active_questions', $choice);
                    }
                }
            }
        } else {
            $db->insert('pc_active_questions', $data);
            if (!empty($choice_list)) {
                $last_id = $db->lastInsertId();
                foreach ($choice_list as $item) {
                    $choice = array(
                        'parent_id' => $last_id,
                        'question' => $item['questions'],
                        'sort' => $item['sort'],
                    );
                    $db->insert('pc_active_questions', $choice);
                }
            }
        }
        //Check List
    } else {
        $data = array(
            'title' => $questions,
        );

        if ($question_id) {
            $db->update('check_list_questions_title', $data, "id = $question_id");
            if (!empty($choice_list)) {
                //Query check
                $query = $db->select()
                    ->from(array('clq' => 'check_list_questions'), 'clq.id')
                    ->where('clq.id NOT IN (?)', $choice_id)
                    ->where('clq.question_title_id = ?', $question_id);
                $qchoice = $db->fetchAll($query);

                //Delete choice
                foreach ($qchoice as $item) {
                    $db->delete('check_list_questions', array('id = ?' => $item['id']));
                }

                foreach ($choice_list as $item) {
                    $choice = array(
                        'topic_id' => $head_id,
                        'question_title_id' => $question_id,
                        'questions' => $item['questions'],
                        'sort' => $item['sort'],
                    );
                    if ($item['id']) {
                        $db->update('check_list_questions', $choice, "id = {$item['id']}");
                    } else {
                        $db->insert('check_list_questions', $choice);
                    }
                }
            }
        } else {
            $db->insert('check_list_questions_title', $data);
            if (!empty($choice_list)) {
                $last_id = $db->lastInsertId();
                foreach ($choice_list as $item) {
                    $choice = array(
                        'topic_id' => $head_id,
                        'question_title_id' => $last_id,
                        'questions' => $item['questions'],
                        'sort' => $item['sort'],
                    );
                    $db->insert('check_list_questions', $choice);
                }
            }
        }
    }


    $flashMessenger = $this->_helper->flashMessenger;
    $flashMessenger->setNamespace('success')->addMessage('Done!');
}

$back_url = $this->getRequest()->getParam('back_url');
$this->_redirect(($back_url ? $back_url : HOST . 'trainer/manage-shop-check-create?id=' . $head_id));