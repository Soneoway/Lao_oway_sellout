<?php
if ($this->getRequest()->getMethod() == 'POST'){
    $QModel = new Application_Model_Team();
    
    $id            = $this->getRequest()->getParam('id');
    $name          = $this->getRequest()->getParam('name');
    $department_id = $this->getRequest()->getParam('department_id');
    $email         = $this->getRequest()->getParam('email');

    $data = array(
        'name'          => $name,
        'department_id' => $department_id,
        'email'         => $email,
    );

    if ($id){
        $where = $QModel->getAdapter()->quoteInto('id = ?', $id);

        $QModel->update($data, $where);
    } else {
        $QModel->insert($data);
    }

    //remove cache
    $cache = Zend_Registry::get('cache');
    $cache->remove('team_cache');
    $cache->remove('team_all_cache');

    $flashMessenger = $this->_helper->flashMessenger;
    $flashMessenger->setNamespace('success')->addMessage('Done!');
}

$back_url = $this->getRequest()->getParam('back_url');

$this->_redirect( ( $back_url ? $back_url : HOST.'manage/team' ) );