<?php
// dau tien minh lay staff_id
$staffID            = $this->getRequest()->getParam('staff_id');
$userStorage        = Zend_Auth::getInstance()->getStorage()->read();
$QStaff             = new Application_Model_Staff();
$whereStaff         = $QStaff->getAdapter()->quoteInto('id = ?',$staffID);
$rowStaff           = $QStaff->fetchRow($whereStaff);
$this->view->staff  = $rowStaff;
$url_after_submit = HOST."trainer/reward-warning?staff_id=".$staffID;

$back_url = $_SERVER['HTTP_REFERER'];
$this->view->back_url = $back_url;

$userStorage                = Zend_Auth::getInstance()->getStorage()->read();
$QLog                       = new Application_Model_Log();
$ip                         = $this->getRequest()->getServer('REMOTE_ADDR');
$flashMessenger             = $this->_helper->flashMessenger;
$messages                   = $flashMessenger->setNamespace('success')->getMessages();
$this->view->message_success = $messages;
$messages_error              = $flashMessenger->setNamespace('error')->getMessages();
$this->view->messages        = $messages_error;
$QStaffRewardWarning         = new Application_Model_StaffRewardWarning();

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

// get history
$page               = $this->getRequest()->getParam('page', 1);
$params             = array(
    'staff_id'      =>$staffID
);
$limit              = LIMITATION;
$total              = 0;
$rows               = $QStaffRewardWarning->fetchPagination($page, $limit, $total, $params);

$this->view->params         = $params;
$this->view->staffHistory   = $rows;
$this->view->limit          = $limit;
$this->view->total          = $total;
$this->view->url            = HOST.'trainer/reward-warning'.( $params ? '?'.http_build_query($params).'&' : '?' );
$this->view->offset         = $limit*($page-1);

// save and update
if($this->getRequest()->getMethod()=='POST')
{
    $db = Zend_Registry::get('db');
    $db->beginTransaction();

    try {

        $type      = $this->getRequest()->getParam('type');
        $content   = $this->getRequest()->getParam('content');
        $monthYear = $this->getRequest()->getParam('month_year');

        $month = explode("-", $monthYear)[0];
        $year  = explode("-", $monthYear)[1];

        if(!$type || !$content)
        {
            throw new Exception('Can not found Type Or Content');
        }

        if(!$monthYear)
        {
            throw new Exception('Can not found Month Or Year');
        }

        $data = array(
            'staff_id'  => $staffID,
            'type'      => $type,
            'content'   => $content,
            'month'     => $month,
            'year'      => $year
        );

        // insert
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['created_by'] = $userStorage->id;

        $QStaffRewardWarning->insert($data);
        // to do log
        $info = array('Insert Reward Warning PG', 'new' => $data);
        $QLog->insert(array(
            'info'       => json_encode($info),
            'user_id'    => $userStorage->id,
            'ip_address' => $ip,
            'time'       => date('Y-m-d H:i:s'),
        ));


        $db->commit();
        $flashMessenger->setNamespace('success')->addMessage('Done!');
        $this->_redirect($url_after_submit);

    }
    catch (Exception $e)
    {
        $db->rollback();
        $flashMessenger->setNamespace('error')->addMessage($e->getMessage());
        $this->_redirect($url_after_submit);
    }
}