<?php
$this->_helper->layout->disableLayout();
$this->_helper->viewRenderer->setNoRender();
$flashMessenger     = $this->_helper->flashMessenger;

$userStorage        = Zend_Auth::getInstance()->getStorage()->read();
$QLog               = new Application_Model_Log();
$ip                 = $this->getRequest()->getServer('REMOTE_ADDR');
$db                 = Zend_Registry::get('db');
$back_url           = HOST.'trainer/list-new-staff';
$QStaffTraining     = new Application_Model_StaffTraining();

try
{
    $db->beginTransaction();
    $id                     = $this->getRequest()->getParam('id');

    if(isset($id) and $id)
    {
        $whereNewStaff   =  array();
        $whereNewStaff[] = $QStaffTraining->getAdapter()->quoteInto('id = ?',$id);
        $whereNewStaff[] = $QStaffTraining->getAdapter()->quoteInto('del = ? OR del IS NULL',0);
        $rowNewStaff     = $QStaffTraining->fetchRow($whereNewStaff);
        if($rowNewStaff)
        {
            $data = array(
                'del' => 1
            );
            $QStaffTraining->update($data,$whereNewStaff);
            // to do log
            $info = array('DEL-NEW-PG-FOR-TRAINING','old'=>$rowNewStaff,'new'=>$data);
            $QLog->insert( array (
                'info'          => json_encode($info),
                'user_id'       => $userStorage->id,
                'ip_address'    => $ip,
                'time'          => date('Y-m-d H:i:s'),
            ) );
        }
        else
        {
            throw new Exception('Staff Is Not Found !');
        }
    }
    else
    {
        throw new Exception(' Not Found  Staff ! ');
    }

    $db->commit();
    $flashMessenger ->setNamespace('success')->addMessage('Done');
}
catch(Exception $e)
{
    $db->rollback();
    $flashMessenger ->setNamespace('error')->addMessage($e->getMessage());
}


$this->redirect($back_url);