<?php
if ($this->getRequest()->getMethod() == 'POST'){
    $QModel       = new Application_Model_Area();

    $id           = $this->getRequest()->getParam('id');
    $name         = $this->getRequest()->getParam('name');
    $region_share = $this->getRequest()->getParam('region_share');
    $pic          = $this->getRequest()->getParam('pic');
    $email        = $this->getRequest()->getParam('email');

    $data = array(
        'name'         => $name,
        'region_share' => $region_share,
        'leader_ids'   => $pic,
        'email'        => $email,
    );

    if ($id){
        $where = $QModel->getAdapter()->quoteInto('id = ?', $id);

        $QModel->update($data, $where);
    } else {
        $QModel->insert($data);
    }

    //remove cache
    $cache = Zend_Registry::get('cache');
    $cache->remove('area_cache');
    $cache->remove('area_cache2');
    $cache->remove('area_ASM_cache');
    $cache->remove('asm_cache');

    $flashMessenger = $this->_helper->flashMessenger;
    $flashMessenger->setNamespace('success')->addMessage('Done!');
}

$back_url = $this->getRequest()->getParam('back_url');

$this->_redirect( ( $back_url ? $back_url : HOST.'manage/area' ) );