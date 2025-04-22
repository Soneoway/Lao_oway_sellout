<?php
$id = $this->getRequest()->getParam('id');
if (isset($id)) {
    $db = Zend_Registry::get('db');

        $QModel = new Application_Model_CheckListTopic();
        $rowset = $QModel->find($id);
        $info = $rowset->current();

        $get = array(
            'title_id' => 'clqt.id',
            'title' => 'clqt.title',
        );

        $q = $db->select()
            ->from(array('clq' => 'check_list_questions'), $get)
            ->join(array('clqt' => 'check_list_questions_title'), 'clqt.id = clq.question_title_id', array())
            ->join(array('clt' => 'check_list_topic'), 'clt.id = clq.topic_id', array())
            ->where('clt.enable = ?', 'Y')
            ->where('clq.topic_id = ?', $id)
            ->order('clq.sort', 'asc')
            ->group('clqt.id');

        $query_questions = $db->fetchAll($q);

    $this->view->info = $info;
    $this->view->question_title = $query_questions;
}

$flashMessenger = $this->_helper->flashMessenger;
$messages = $flashMessenger->setNamespace('error')->getMessages();
$this->view->messages = $messages;
//back url
$this->view->back_url = $this->getRequest()->getServer('HTTP_REFERER');
$this->_helper->viewRenderer->setRender('/manage-shop-check/create');