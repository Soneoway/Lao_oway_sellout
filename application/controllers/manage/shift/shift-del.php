<?php
$userStorage = Zend_Auth::getInstance()->getStorage()->read();
$flashMessenger = $this->_helper->flashMessenger;

if ($userStorage->group_id != SUPERADMIN_ID) {

    $flashMessenger->setNamespace('error')->addMessage('No permission!');
    $this->_redirect('/manage/shift');
} else {

    $id     = $this->getRequest()->getParam('id');

    $QShift = new Application_Model_Shift();

    if ($id) {

        $QShift = new Application_Model_Shift();
        $shift_rs = $QShift->find($id);
        $shift = $shift_rs->current();

        if ($shift) {
            $where = $QShift->getAdapter()->quoteInto('id = ?', $id);
            $QShift->delete($where);

            $cache = Zend_Registry::get('cache');
            $cache->remove('shift_cache');

            $flashMessenger->setNamespace('success')->addMessage('Done!');
        } else {
            $flashMessenger->setNamespace('error')->addMessage('Wrong ID!');
        }

    }
}

$this->_redirect('/manage/shift');