<?php

if ($this->getRequest()->getMethod() == 'POST') {
    $db = Zend_Registry::get('db');

    $question_id= $this->getRequest()->getParam('question_id');
    $head_id = $this->getRequest()->getParam('head_id');
    $questions = $this->getRequest()->getParam('questions');
    $choice_list = $this->getRequest()->getParam('choice_list');

    $userStorage = Zend_Auth::getInstance()->getStorage()->read();

    $choice_id = array();
    foreach ($choice_list as $item) {
        $choice_id[] = $item['choice_id'];
    }

//    echo '<pre>';
//    echo print_r($qchoice);
//    die;

    $data = array(
        'head_id' => $head_id,
        'questions' => $questions,
    );

    if ($question_id) {
        $db->update('questions', $data, "id = $question_id");
        if (!empty($choice_list)) {
            //Query check
            $query = $db->select()
                ->from(array('qc' => 'questions_choice'), 'qc.id')
                ->where('qc.id NOT IN (?)',$choice_id)
                ->where('qc.question_id = ?', $question_id);
            $qchoice = $db->fetchAll($query);

            //Delete choice
            foreach ($qchoice as $item) {
                $db->delete('questions_choice', array('id = ?' => $item['id']));
            }

            $char = range('A', 'Z');
            $i = 0;
            foreach ($choice_list as $item) {
                $choice = array(
                    'question_id' => $question_id,
                    'answer' => $item['answer'],
                    'choice' => $char[$i],
                    'correct' => $item['correct'],
                );
                if ($item['choice_id']) {
                    $db->update('questions_choice', $choice, "id = {$item['choice_id']}");
                } else {
                    $db->insert('questions_choice', $choice);
                }
                $i++;
            }
        }
    } else {
        $db->insert('questions', $data);
        if (!empty($choice_list)) {
            $last_id = $db->lastInsertId();
            $char = range('A', 'Z');
            $i = 0;
            foreach ($choice_list as $item) {
                $choice = array(
                    'question_id' => $last_id,
                    'answer' => $item['answer'],
                    'choice' => $char[$i],
                    'correct' => $item['correct'],
                );
                $db->insert('questions_choice', $choice);
                $i++;
            }
        }
    }

    $flashMessenger = $this->_helper->flashMessenger;
    $flashMessenger->setNamespace('success')->addMessage('Done!');
}

$back_url = $this->getRequest()->getParam('back_url');
$this->_redirect(($back_url ? $back_url : HOST . 'trainer/e-test-info-create?id='.$head_id));