<?php
if ($this->getRequest()->getMethod() == 'POST'){
    $QModel = new Application_Model_Department();

    $id = $this->getRequest()->getParam('id');
    $name = $this->getRequest()->getParam('name');
    $email = $this->getRequest()->getParam('email');

    $data = array(
        'name' => $name,
        'email' => $email,
    );

    if ($id){
        $where = $QModel->getAdapter()->quoteInto('id = ?', $id);

        $QModel->update($data, $where);
    } else {
        $QModel->insert($data);
    }

    //remove cache
    $cache = Zend_Registry::get('cache');
    $cache->remove('department_cache');
    $cache->remove('department_all_cache');

    $flashMessenger = $this->_helper->flashMessenger;
    $flashMessenger->setNamespace('success')->addMessage('Done!');
}

$back_url = $this->getRequest()->getParam('back_url');

$this->_redirect( ( $back_url ? $back_url : HOST.'manage/department' ) );