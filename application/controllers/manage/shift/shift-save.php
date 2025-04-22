<?php
$id     = $this->getRequest()->getParam('id');
$name   = $this->getRequest()->getParam('name');
$start  = $this->getRequest()->getParam('timepicker_start');
$end    = $this->getRequest()->getParam('timepicker_end');
$status = $this->getRequest()->getParam('status', 0);

if ($this->getRequest()->getMethod() == 'POST') {
    $QShift = new Application_Model_Shift();

    $data = array(
        'name'   => $name,
        'start'  => $start,
        'end'    => $end,
        'status' => $status,
    );

    if ($id) {
        $where = $QShift->getAdapter()->quoteInto('id = ?', $id);
        $QShift->update($data, $where);
    } else {
        $QShift->insert($data);
    }

    $flashMessenger = $this->_helper->flashMessenger;
    $flashMessenger->setNamespace('success')->addMessage('Done!');

    $cache = Zend_Registry::get('cache');
    $cache->remove('shift_cache');
}

$this->_redirect('/manage/shift');