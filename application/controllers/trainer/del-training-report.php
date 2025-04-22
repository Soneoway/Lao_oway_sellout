<?php
$this->_helper->layout->disableLayout();
$this->_helper->viewRenderer->setNoRender();

$flashMessenger     = $this->_helper->flashMessenger;
$QStore             = new Application_Model_Store();
$userStorage        = Zend_Auth::getInstance()->getStorage()->read();
$QLog               = new Application_Model_Log();
$ip                 = $this->getRequest()->getServer('REMOTE_ADDR');
$db                 = Zend_Registry::get('db');

$back_url           = '/trainer/list-training-report';

try {
    $db->beginTransaction();
    $id = $this->getRequest()->getParam('id');
    $QStaffTrainingReport = new Application_Model_StaffTrainingReport();
    $QStaffTrainer        = new Application_Model_StaffTrainer();
    $whereTrainingReport  = $QStaffTrainingReport->getAdapter()->quoteInto('id = ?', $id);
    $row                  = $QStaffTrainingReport->fetchRow($whereTrainingReport);

    $areaTrainer          = $QStaffTrainer->getAreaTrainer($userStorage->id);
    $getArea              = $areaTrainer['area'];
    $getProvince          = $areaTrainer['province'];
    $getDistrict          = $areaTrainer['district'];

    if ($row) {

        if($row['type'] == TRAINING_REPORT_PARTNERS)
        {
            /*check edit */
            /* check store id with area of store */
            $whereDistrictStore         = $QStore->getAdapter()->quoteInto('id = ?',$row['store_id']);
            $rowDistrictStore           = $QStore->fetchRow($whereDistrictStore);
            if($rowDistrictStore)
            {
                $district               = $rowDistrictStore['district'];

                if(in_array($district,array_keys($getDistrict)) || $userStorage->group_id == ADMINISTRATOR_ID)
                {

                }
                else
                {
                    throw new Exception('Area Store NOT True with Area Login - Can not Edit');
                }
            }
            else
            {
                throw new Exception('NOT FOUND DISTRICT STORE');
            }
        }

        if($row['type'] == TRAINING_REPORT_INTERNALLY)
        {
            // kiem tra khu vuc co thuoc khuu vuc cua thang trainer khong

            if(!in_array($row['area'],array_keys($getArea)))
            {
                throw new Exception('Area Record Not True With Area User Login');
            }

            if(!in_array($row['province'],array_keys($getProvince)))
            {
                throw new Exception('Province Record Not True With Area User Login');
            }

        }

        // delete
        $dataDelete = array(
            'del'=> 1
        );
        $QStaffTrainingReport->update($dataDelete,$whereTrainingReport);

        // to do log
        $info = array('Delete Daily Report', 'new' => $dataDelete,'old'=>$row);
        $QLog->insert(array(
            'info'       => json_encode($info),
            'user_id'    => $userStorage->id,
            'ip_address' => $ip,
            'time'       => date('Y-m-d H:i:s'),
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