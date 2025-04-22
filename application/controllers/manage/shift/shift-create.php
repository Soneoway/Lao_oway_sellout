<?php
$id = $this->getRequest()->getParam('id');

if ($id) {
    $QShift = new Application_Model_Shift();
    $shift_rs = $QShift->find($id);
    $shift = $shift_rs->current();

    if (!$shift) {
        $this->_redirect('/manage/shift');
    } else {
        $this->view->shift = $shift;
    }
}
$this->_helper->viewRenderer->setRender('shift/create');