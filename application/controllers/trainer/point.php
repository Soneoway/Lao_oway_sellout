<?php
// dau tien minh lay staff_id
$staffID            = $this->getRequest()->getParam('staff_id');
$userStorage        = Zend_Auth::getInstance()->getStorage()->read();
$QStaff             = new Application_Model_Staff();
$whereStaff         = $QStaff->getAdapter()->quoteInto('id = ?',$staffID);
$rowStaff           = $QStaff->fetchRow($whereStaff);
$this->view->staff  = $rowStaff;

$back_url = $_SERVER['HTTP_REFERER'];
$this->view->back_url = $back_url;

$userStorage                = Zend_Auth::getInstance()->getStorage()->read();
$QLog                       = new Application_Model_Log();
$ip                         = $this->getRequest()->getServer('REMOTE_ADDR');
$flashMessenger             = $this->_helper->flashMessenger;
$QStaffPoint                = new Application_Model_StaffPoint();

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
$rows               = $QStaffPoint->fetchPagination($page, $limit, $total, $params);

$this->view->params         = $params;
$this->view->staffHistory   = $rows;
$this->view->limit          = $limit;
$this->view->total          = $total;
$this->view->url            = HOST.'trainer/point'.( $params ? '?'.http_build_query($params).'&' : '?' );
$this->view->offset         = $limit*($page-1);
// save and update
if($this->getRequest()->getMethod()=='POST')
{
    $db = Zend_Registry::get('db');
    $db->beginTransaction();

    try {
        $point     = $this->getRequest()->getParam('point');
        $monthYear = $this->getRequest()->getParam('month_year');
        $edit      = $this->getRequest()->getParam('edit',null);

        $month = explode("-", $monthYear)[0];
        $year  = explode("-", $monthYear)[1];

        if($point > 100 || $point < 0 || !$point)
        {
            throw new Exception('point is greater than 100 OR less than 0 Or Null');
        }

        if(!$monthYear)
        {
            throw new Exception('Can not found Month Or Year');
        }

        if($month > 12 || $month < 1)
        {
            throw new Exception('Month is not true');
        }

        $data = array(
            'staff_id'  => $staffID,
            'point'     => $point,
            'month'     => $month,
            'year'      => $year
        );

        // get value row
        $whereStaffPoint   = array();
        $whereStaffPoint[] = $QStaffPoint->getAdapter()->quoteInto('staff_id = ?',$staffID);
        $whereStaffPoint[] = $QStaffPoint->getAdapter()->quoteInto('month = ?',$month);
        $whereStaffPoint[] = $QStaffPoint->getAdapter()->quoteInto('year = ?',$year);
        $rowPointPG        = $QStaffPoint->fetchRow($whereStaffPoint);

        if($rowPointPG and $edit)
        {
            $data['updated_at'] = date('Y-m-d H:i:s');
            $data['updated_by'] = $userStorage->id;

            $QStaffPoint->update($data,$whereStaffPoint);
            $info = array('Update Management Point PG', 'new' => $data, 'old'=>$rowPointPG);
            $QLog->insert(array(
                'info'       => json_encode($info),
                'user_id'    => $userStorage->id,
                'ip_address' => $ip,
                'time'       => date('Y-m-d H:i:s'),
            ));
        }
        else if ($rowPointPG)
        {
            throw new Exception('POINT OF MONTH HAVED');
        }
        else
        {
            // insert
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['created_by'] = $userStorage->id;

            $QStaffPoint->insert($data);
            // to do log
            $info = array('Insert Management Point PG', 'new' => $data);
            $QLog->insert(array(
                'info'       => json_encode($info),
                'user_id'    => $userStorage->id,
                'ip_address' => $ip,
                'time'       => date('Y-m-d H:i:s'),
            ));
        }

        $db->commit();
        $flashMessenger->setNamespace('success')->addMessage('Done!');
        $this->_redirect(HOST . 'trainer');

    } catch (Exception $e)
    {
        $db->rollback();
        $flashMessenger->setNamespace('error')->addMessage($e->getMessage());
        $this->_redirect(HOST . 'trainer');
    }
}