<?php

$id = $this->getRequest()->getParam('id');
$flashMessenger = $this->_helper->flashMessenger;

try
{
    if (!$id)
        throw new Exception("Invalid ID");
    $userStorage = Zend_Auth::getInstance()->getStorage()->read();
    $group_id = $userStorage->group_id;
    $QInform = new Application_Model_Inform();
    $inform = $QInform->find($id);
    $inform = $inform->current();

    $QInformFile = new Application_Model_InformFile();
    $where = $QInformFile->getAdapter()->quoteInto('inform_id = ?', $id);
    $files = $QInformFile->fetchAll($where);

    if (!$inform)
        throw new Exception("Invalid ID");

    $QInformTeam = new Application_Model_InformTeam();

    if ($QInformTeam->check_view($id) || in_array($group_id, array(
        ADMINISTRATOR_ID,
        HR_ID,
        HR_EXT_ID,
        BOARD_ID)))
    {
        $this->view->files = $files;
        $this->view->userStorage = $userStorage;
        $this->view->inform = $inform;
        $this->view->back_url = $this->getRequest()->getServer('HTTP_REFERER');
        $this->_helper->viewRenderer->setRender('inform/view');
    } else
    {
        throw new Exception("You cannot view this");
    }
}
catch (exception $e)
{
    //echo $e->getMessage();exit;
    $flashMessenger->setNamespace('error')->addMessage($e->getMessage());
    $this->_redirect(HOST . 'manage/inform');
}
