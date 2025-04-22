<?php
$staff_id = $this->getRequest()->getParam('staff_id');
$staff_name = $this->getRequest()->getParam('staff_name');
$date = $this->getRequest()->getParam('date');
$time = $this->getRequest()->getParam('time');

$params = array(
    'staff_id'      => $staff_id,
    'staff_name'      => $staff_name,
    'date'          => $date,
    'time'          => $time,
);

$this->view->params  = $params;

//$QuestionsAnswerLog = new Application_Model_QuestionsAnswerLog();
//$staff_list = $QuestionsAnswerLog->getDetailAnswer($staff_id, $head_id);
//
//$questions = $QuestionsAnswerLog->getQuestions($head_id);
//$choice = $QuestionsAnswerLog->getChoice($head_id);
//$choice_answer = $QuestionsAnswerLog->getChoiceAnswerLog($staff_id, $head_id);
//
//$this->view->staff_list = $staff_list;
//$this->view->questions = $questions;
//$this->view->choice = $choice;
//$this->view->choice_answer = $choice_answer;

$userStorage = Zend_Auth::getInstance()->getStorage()->read();
$this->view->group_id = $userStorage->group_id;

//back url
$this->view->back_url = $this->getRequest()->getServer('HTTP_REFERER');

$this->_helper->viewRenderer->setRender('/new-pc-follow/create');