<?php
$this->_helper->layout->disableLayout();
$this->_helper->viewRenderer->setNoRender();

$flashMessenger     = $this->_helper->flashMessenger;
$userStorage        = Zend_Auth::getInstance()->getStorage()->read();
$QLog               = new Application_Model_Log();
$ip                 = $this->getRequest()->getServer('REMOTE_ADDR');
$db                 = Zend_Registry::get('db');
$back_url           = isset($_SERVER['HTTP_REFERER']) ? ($_SERVER['HTTP_REFERER']):'/trainer/list-team-building-report';

try {

    $db->beginTransaction();
    $id = $this->getRequest()->getParam('id');
    $QStaffTeamBuildingReport       = new Application_Model_StaffTeamBuildingReport();
    $whereStaffTeamBuildingReport   = $QStaffTeamBuildingReport->getAdapter()->quoteInto('id = ?', $id);
    $row                            = $QStaffTeamBuildingReport->fetchRow($whereStaffTeamBuildingReport);
    if ($row) {

        // delete
        $dataDelete = array(
            'del'=> 1
        );
        $QStaffTeamBuildingReport->update($dataDelete,$whereStaffTeamBuildingReport);

        // to do log
        $info = array('Delete Team Building Report', 'new' => $dataDelete,'old'=>$row);
        $QLog->insert(array(
            'info'       => json_encode($info),
            'user_id'    => $userStorage->id,
            'ip_address' => $ip,
            'time'       => date('Y-m-d H:i:s')
        ));

        $db->commit();
        $flashMessenger->setNamespace('success')->addMessage('Done!');
        $this->_redirect($back_url);
    }
}
catch(Exception $e)
{
    $db->rollback();
    $flashMessenger->setNamespace('error')->addMessage($e->getMessage());
    $this->_redirect($back_url);
}