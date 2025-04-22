<?php

$this->_helper->layout->disableLayout();
$this->_helper->viewRenderer->setNoRender();
$flashMessenger     = $this->_helper->flashMessenger;

$userStorage        = Zend_Auth::getInstance()->getStorage()->read();
$QLog               = new Application_Model_Log();
$ip                 = $this->getRequest()->getServer('REMOTE_ADDR');
$db                 = Zend_Registry::get('db');
$back_url           = HOST.'trainer/type-asset';
$QTrainerTypeAsset  = new Application_Model_TrainerTypeAsset();

if($this->getRequest()->getMethod()=='POST')
{
    try
    {
        $db->beginTransaction();
        $asset = $this->getRequest()->getParam('asset');
        $id    = $this->getRequest()->getParam('id');
        $data  = array(
            'name'       => $asset
        );

        $whereAsset = $QTrainerTypeAsset->getAdapter()->quoteInto('id = ?',$id);
        $rowAsset   = $QTrainerTypeAsset->fetchRow($whereAsset);

        if(!$rowAsset)
        {
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['created_by'] = $userStorage->id;
            $QTrainerTypeAsset->insert($data);

            // to do log
            $info = array('INSERT-TYPE-ASSET','new'=>$data);
            $QLog->insert( array (
                'info'          => json_encode($info),
                'user_id'       => $userStorage->id,
                'ip_address'    => $ip,
                'time'          => date('Y-m-d H:i:s'),
            ) );
        }
        else
        {
            $data['updated_at'] = date('Y-m-d H:i:s');
            $data['updated_by'] = $userStorage->id;
            $QTrainerTypeAsset->update($data,$whereAsset);

            // to do log
            $info = array('UPDATE-TYPE-ASSET','new'=>$data,'old'=>$rowAsset);
            $QLog->insert( array (
                'info'          => json_encode($info),
                'user_id'       => $userStorage->id,
                'ip_address'    => $ip,
                'time'          => date('Y-m-d H:i:s'),
            ) );

        }

        $db->commit();
        $flashMessenger ->setNamespace('success')->addMessage('Done');
    }
    catch(Exception $e)
    {
        $db->rollback();
        $flashMessenger ->setNamespace('error')->addMessage($e->getMessage());
    }
}

$this->redirect($back_url);
