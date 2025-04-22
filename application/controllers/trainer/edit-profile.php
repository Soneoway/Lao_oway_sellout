<?php
$flashMessenger = $this->_helper->flashMessenger;
$userStorage    = Zend_Auth::getInstance()->getStorage()->read();
$staffID        = $this->getRequest()->getParam('staff_id');

$back_url = $_SERVER['HTTP_REFERER'];
$this->view->back_url = $back_url;

$QStaffTrainer        = new Application_Model_StaffTrainer();
try
{
    $checkPGSALE = $QStaffTrainer->checkPGPBSALE($staffID);
    if($checkPGSALE)
    {
        throw new Exception($checkPGSALE);
    }

    $checkAreaPGSALEISAreaTrainer = $QStaffTrainer->checkAreaPGSALEISAreaTrainer($staffID,$userStorage->id);

    if($checkAreaPGSALEISAreaTrainer)
    {
        throw new Exception($checkAreaPGSALEISAreaTrainer);
    }
}
catch (Exception $e)
{
    $flashMessenger->setNamespace('error')->addMessage($e->getMessage());
    $this->_redirect(HOST . 'trainer');
}

$whereStaffTrainer    = $QStaffTrainer->getAdapter()->quoteInto('staff_id = ?', $staffID);
$rowStaffTrainer      = $QStaffTrainer->fetchRow($whereStaffTrainer);

if ($rowStaffTrainer) {
    $this->view->staffTrainerProfile = $rowStaffTrainer;
}

$this->view->staffID = $staffID;

if ($this->getRequest()->getMethod() == 'POST') {

    $db = Zend_Registry::get('db');
    $db->beginTransaction();

    $trainer_evaluation_knowledge   = $this->getRequest()->getParam('trainer_evaluation_knowledge');
    $trainer_evaluation_skill       = $this->getRequest()->getParam('trainer_evaluation_skill');
    $trainer_evaluation_attitude    = $this->getRequest()->getParam('trainer_evaluation_attitude');
    $facebook_id                    = $this->getRequest()->getParam('facebook_id');
    $facebook_link                  = $this->getRequest()->getParam('facebook_link');

    try {

        $userStorage    = Zend_Auth::getInstance()->getStorage()->read();
        $QLog           = new Application_Model_Log();
        $ip             = $this->getRequest()->getServer('REMOTE_ADDR');

        $dataTrainer = array(
            'knowledge'     => $trainer_evaluation_knowledge,
            'skill'         => $trainer_evaluation_skill,
            'attitude'      => $trainer_evaluation_attitude,
            'facebook_id'   => $facebook_id,
            'facebook_link' => $facebook_link
        );


        if ($rowStaffTrainer) {
            // update
            $dataTrainer['updated_at'] = date('Y-m-d H:i:s');
            $dataTrainer['updated_by'] = $userStorage->id;
            $QStaffTrainer->update($dataTrainer, $whereStaffTrainer);
            //to do log
            $info = array(
                'UPDATED STAFF TRAINER EVALUATION' => $staffID,
                'new' => $dataTrainer,
                'old' => $rowStaffTrainer);
            $QLog->insert(array(
                'info'          => json_encode($info),
                'user_id'       => $userStorage->id,
                'ip_address'    => $ip,
                'time'          => date('Y-m-d H:i:s'),
            ));
        } else {
            // insert
            $dataTrainer['staff_id'] = $staffID;
            $dataTrainer['created_at'] = date('Y-m-d H:i:s');
            $dataTrainer['created_by'] = $userStorage->id;
            $idStaffTrainerEvaluation = $QStaffTrainer->insert($dataTrainer);
            //to do log
            $info = array("INSERT STAFF TRAINER EVALUATION" => $idStaffTrainerEvaluation,
                'new' => $dataTrainer);
            $QLog->insert(array(
                'info'          => json_encode($info),
                'user_id'       => $userStorage->id,
                'ip_address'    => $ip,
                'time'          => date('Y-m-d H:i:s'),
            ));
        }

        $db->commit();
        $flashMessenger->setNamespace('success')->addMessage("Done!");
        $this->_redirect(HOST . 'trainer');
    }
    catch (exception $e) {
        $db->rollback();
        $flashMessenger->setNamespace('error')->addMessage($e->getMessage());
        $this->_redirect(HOST . 'trainer');
    }

}