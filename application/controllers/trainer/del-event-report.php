<?php
$this->_helper->layout->disableLayout();
$this->_helper->viewRenderer->setNoRender();

$flashMessenger     = $this->_helper->flashMessenger;
$QStore             = new Application_Model_Store();
$userStorage        = Zend_Auth::getInstance()->getStorage()->read();
$regional_market    = $userStorage->regional_market;
$QLog               = new Application_Model_Log();
$ip                 = $this->getRequest()->getServer('REMOTE_ADDR');
$db                 = Zend_Registry::get('db');
$back_url           = '/trainer/list-event-report';

try {
    $db->beginTransaction();
    $id = $this->getRequest()->getParam('id');
    $QStaffEventReport = new Application_Model_StaffEventReport();
    $QStaffTrainer     = new Application_Model_StaffTrainer();
    $QAsm              = new Application_Model_Asm();
    $whereEventReport  = $QStaffEventReport->getAdapter()->quoteInto('id = ?', $id);
    $row               = $QStaffEventReport->fetchRow($whereEventReport);
    if ($row) {
        /*check del*/
        $whereDistrictStore         = $QStore->getAdapter()->quoteInto('id = ?',$row['store_id']);
        $rowDistrictStore           = $QStore->fetchRow($whereDistrictStore);
        if($rowDistrictStore)
        {
            $district             = $rowDistrictStore['district'];
            $districtTrainer      = $QStaffTrainer->getAreaTrainer($userStorage->id);
            if(in_array($district,array_keys($districtTrainer['district'])) || $userStorage->group_id == ADMINISTRATOR_ID)
            {

            }
            else
            {
                throw new Exception('Area Store NOT True with Area Login - Can not delete');
            }


        }
        else
        {
            throw new Exception('NOT FOUND DISTRICT STORE');
        }

        // delete
        $dataDelete = array(
            'del'=> 1
        );
        $QStaffEventReport->update($dataDelete,$whereEventReport);

        // to do log
        $info = array('Delete Daily Report', 'new' => $dataDelete,'old'=>$row);
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