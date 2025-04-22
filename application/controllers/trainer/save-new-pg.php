<?php
$this->_helper->layout->disableLayout();
$this->_helper->viewRenderer->setNoRender();
$flashMessenger     = $this->_helper->flashMessenger;

$userStorage        = Zend_Auth::getInstance()->getStorage()->read();
$QLog               = new Application_Model_Log();
$ip                 = $this->getRequest()->getServer('REMOTE_ADDR');
$db                 = Zend_Registry::get('db');
$back_url           = HOST.'trainer/add-time-check-in';
$QStaffTraining     = new Application_Model_StaffTraining();

if($this->getRequest()->getMethod()=='POST')
{
    try
    {
        $db->beginTransaction();
        $id                     = $this->getRequest()->getParam('id_new_staff',null);
        $firstname              = $this->getRequest()->getParam('firstname');
        $lastname               = $this->getRequest()->getParam('lastname');
        $cmnd                   = $this->getRequest()->getParam('cmnd');
        $gender                 = $this->getRequest()->getParam('gender');
        $area_id                = $this->getRequest()->getParam('area_id');
        $regional_market        = $this->getRequest()->getParam('regional_market');
        $saveAjax               = $this->getRequest()->getParam('save_ajax',null);

        $data  = array(
            'firstname'       => $firstname,
            'lastname'        => $lastname,
            'gender'          => $gender,
            'cmnd'            => $cmnd,
            'team'            => SALES_TEAM,
            'title'           => PGPB_TITLE,
            'area_id'         => $area_id,
            'regional_market' => $regional_market
        );
        $resultAjax   = array(
            'code'    => 0,
            'message' => null,
            'html'    => null
        );
        if(isset($id) and $id)
        {
            $whereNewStaff   =  array();
            $whereNewStaff[] = $QStaffTraining->getAdapter()->quoteInto('id = ?',$id);
            $whereNewStaff[] = $QStaffTraining->getAdapter()->quoteInto('del = ? OR del IS NULL',0);
            $rowNewStaff     = $QStaffTraining->fetchRow($whereNewStaff);
            if($rowNewStaff)
            {
                $data['updated_at'] = date('Y-m-d H:i:s');
                $data['updated_by'] = $userStorage->id;
                $QStaffTraining->update($data,$whereNewStaff);
                // to do log
                $info = array('UPDATE-NEW-PG-FOR-TRAINING','old'=>$rowNewStaff,'new'=>$data);
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
            $whereStaffTraining = $QStaffTraining->getAdapter()->quoteInto('cmnd = ?',$cmnd);
            $rowStaffTraining   = $QStaffTraining->fetchRow($whereStaffTraining);

            if(!$rowStaffTraining)
            {
                $data['created_at'] = date('Y-m-d H:i:s');
                $data['created_by'] = $userStorage->id;
                $idInsert = $QStaffTraining->insert($data);

                // to do log
                $info = array('INSERT-NEW-PG-FOR-TRAINING','new'=>$data);
                $QLog->insert( array (
                    'info'          => json_encode($info),
                    'user_id'       => $userStorage->id,
                    'ip_address'    => $ip,
                    'time'          => date('Y-m-d H:i:s'),
                ) );
            }
            else
            {
                throw new Exception('User is created !');
            }
        }

        $db->commit();

        if(isset($saveAjax) and $saveAjax)
        {
           $optionAdd = '<select name="a_staffresult[]" id="a_staffresult" multiple="multiple" size="10" class="span5">'.
                        '<option value="'.$idInsert.'">'.$firstname .' '.$lastname.' - SALE - PGPB - NHÂN VIÊN MỚI'.'</option></select>'.
                        '<input type="hidden" name="new_staff[]" value="'.$idInsert.'"/>';

            $resultAjax['code']    = 0;
            $resultAjax['message'] = 'DONE!';
            $resultAjax['html']    = $optionAdd;

            echo json_encode($resultAjax);
        }
        else
        {
            $flashMessenger ->setNamespace('success')->addMessage('Done');
            $this->redirect($back_url);
        }
    }
    catch(Exception $e)
    {
        $db->rollback();

        if(isset($saveAjax) and $saveAjax)
        {
            $resultAjax['code']    = 1;
            $resultAjax['message'] = $e->getMessage();
            $resultAjax['html']    = null;
            echo json_encode($resultAjax);
        }
        else
        {
            $flashMessenger ->setNamespace('error')->addMessage($e->getMessage());
            $this->redirect($back_url);
        }
    }
}

