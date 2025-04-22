<?php
$this->_helper->layout->disableLayout();
$this->_helper->viewRenderer->setNoRender(true);

$id = $this->getRequest()->getParam('id');
$remark = $this->getRequest()->getParam('remark');
$status = $this->getRequest()->getParam('status_id');

$userStorage = Zend_Auth::getInstance()->getStorage()->read();
foreach ($id as $ti_id) {
    if ($ti_id) {
        $QTimingIssue = new Application_Model_TimingIssue();
        $where = $QTimingIssue->getAdapter()->quoteInto('id = ?', $ti_id);

        $data = array(
            'remark'    => $remark,
            'status'    => $status,
            'updated_by'=> $userStorage->id,
            'updated_at'=> date('Y-m-d H:i:s'),
        );

        // Submit Button
        if ($status == 4) { unset($data['status']); }

        $result = $QTimingIssue->update($data,$where);

        $flashMessenger = $this->_helper->flashMessenger;
    }
}
if ($result == 1) {
    $flashMessenger->setNamespace('success')->addMessage('Done!');
} else {
    $flashMessenger->setNamespace('error')->addMessage($e->getMessage());
}

$back_url = $this->getRequest()->getParam('back_url');

echo '<script>parent.location.href="'. ( !empty( $back_url ) ? ($back_url) : (HOST.'timing/timing-issue') ).'"</script>';