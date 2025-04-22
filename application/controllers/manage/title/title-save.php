<?php
if ($this->getRequest()->getMethod() == 'POST'){
    $QModel = new Application_Model_Title();

    $id = $this->getRequest()->getParam('id');
    $name = $this->getRequest()->getParam('name');
    $team_id = $this->getRequest()->getParam('team_id');

    $data = array(
        'name'    => $name,
        'team_id' => $team_id,
    );

    if ($id){
        $where = $QModel->getAdapter()->quoteInto('id = ?', $id);

        $QModel->update($data, $where);
    } else {
        $QModel->insert($data);
    }

    //remove cache
    $cache = Zend_Registry::get('cache');
    $cache->remove('title_cache');

    $flashMessenger = $this->_helper->flashMessenger;
    $flashMessenger->setNamespace('success')->addMessage('Done!');
}

$back_url = $this->getRequest()->getParam('back_url');

$this->_redirect( ( $back_url ? $back_url : HOST.'manage/title' ) );