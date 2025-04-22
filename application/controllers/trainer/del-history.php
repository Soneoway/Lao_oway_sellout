<?php
$this->_helper->layout->disableLayout();
$this->_helper->viewRenderer->setNoRender();
if($this->getRequest()->getMethod()=='POST')
{
    $db = Zend_Registry::get('db');
    $db->beginTransaction();
    $userStorage    = Zend_Auth::getInstance()->getStorage()->read();
    $QLog           = new Application_Model_Log();
    $ip             = $this->getRequest()->getServer('REMOTE_ADDR');

    try {
        $id    = $this->getRequest()->getParam('id');

        $QStaffStaffRewardWarning           = new Application_Model_StaffRewardWarning();
        $whereDelStaffStaffRewardWarning    = $QStaffStaffRewardWarning->getAdapter()->quoteInto('id = ?',$id);
        $rowStaffStaffRewardWarning         = $QStaffStaffRewardWarning->fetchRow($whereDelStaffStaffRewardWarning);
        if($rowStaffStaffRewardWarning and count($rowStaffStaffRewardWarning))
        {
            $dataDel = array(
                'del' => 1
            );
            $QStaffStaffRewardWarning->update($dataDel,$whereDelStaffStaffRewardWarning);
            // to do log
            $info = array("Del History Reward Warning" => $whereDelStaffStaffRewardWarning );
            $QLog->insert(array(
                'info'          => json_encode($info),
                'user_id'       => $userStorage->id,
                'ip_address'    => $ip,
                'time'          => date('Y-m-d H:i:s'),
            ));

            echo 1;
        }
        else
        {
            echo 2;
        }

        $db->commit();

    }
    catch (Exception $e)
    {
        echo 2;
        $db->rollback();
    }
}