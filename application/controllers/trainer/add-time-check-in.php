<?php
$userStorage    = Zend_Auth::getInstance()->getStorage()->read();
$id             = $this->getRequest()->getParam('id', $userStorage->id);
$time           = $this->getRequest()->getParam('time');
$user_id        = $userStorage->id;
$group_id       = $userStorage->group_id;

$cache = Zend_Registry::get('cache');
$cache->remove('staff_training_cache');
$cache->remove('staff_cache');

$QLog           = new Application_Model_Log();
$ip             = $this->getRequest()->getServer('REMOTE_ADDR');

$flashMessenger                 = $this->_helper->flashMessenger;
$messages                       = $flashMessenger->setNamespace('success')->getMessages();
$this->view->messages_success   = $messages;
$messages                       = $flashMessenger->setNamespace('error')->getMessages();
$this->view->messages           = $messages;

// bat dâu get khuu vuc
$QStaffTrainer      = new Application_Model_StaffTrainer();
$areaStaff          = $QStaffTrainer->getAreaTrainer($user_id);
$this->view->areas  = $areaStaff['area'];

// team
$QTeam      = new Application_Model_Team();
//remove cache
$cache = Zend_Registry::get('cache');
$cache->remove('team_cache');
$cachedTeam = $QTeam->get_cache();
$this->view->teams =$cachedTeam;

// date
$this->view->date     = date('d/m/Y');
$this->view->staff_id = $user_id;

if ($this->getRequest()->isPost())
{
    $this->_helper->layout->disableLayout();
    $this->_helper->viewRenderer->setNoRender();

    echo '<link href="' . HOST . '/css/bootstrap.min.css" rel="stylesheet">';

    // clone ben anh cuong qua
    $from_date  = $this->getRequest()->getParam('from_date');
    $staffs     = $this->getRequest()->getParam('a_staffresult');
    $areas      = $this->getRequest()->getParam('area');
    $teams      = $this->getRequest()->getParam('team');
    $new_staff  = $this->getRequest()->getParam('new_staff');

    $shift  = SHIFT_CHECK_IN_FOR_PG_TRAINING;
    $start  = $end = null;
    // shift
    $QShift     =  new Application_Model_Shift();
    $whereShift = $QShift->getAdapter()->quoteInto('id = ?',$shift);
    $rowShift   = $QShift->fetchRow($whereShift);
    if($rowShift)
    {
        $start = $rowShift['start'];
        $end   = $rowShift['end'];
    }
    $temp               = explode('/', $from_date);
    $temp2              = explode(':', $start);
    $temp3              = explode(':', $end);
    $formatedFrom       = $start . ':00';
    $formatedTo         = $end . ':00';
    $formatedDate       = (isset($temp[2]) ? $temp[2] : '1970') . '-' . (isset($temp[1]) ? $temp[1] : '01') . '-' . (isset($temp[0]) ? $temp[0] : '01');

    if (
        !(isset($temp[2]) and intval($temp[2]) >= 2013 and intval($temp[2]) <= 2020
        and isset($temp[1]) and intval($temp[1]) <= 12 and intval($temp[1]) >= 1
        and isset($temp[0]) and intval($temp[0]) <= 31 and intval($temp[0]) >= 1
        and isset($temp2[0]) and intval($temp2[0]) <= 23 and intval($temp2[0]) >= 0
        and isset($temp2[1]) and intval($temp2[1]) <= 59
        and intval($temp2[1]) >= 0 and isset($temp3[0]) and intval($temp3[0]) <= 23
        and intval($temp3[0]) >= 0 and isset($temp3[1]) and intval($temp3[1]) <= 59
        and intval($temp3[1]) >= 0)
    )
    {
        throw new Exception("Date format is error");
    }

    if (!$formatedDate)
    {
        throw new Exception("Failed - Please input Date for check in!");
    }

    if (!$staffs)
    {
        throw new Exception("Failed - Please select at Staff !");
    }

    $QStaff          = new Application_Model_Staff();
    $QStaffTraining  = new Application_Model_StaffTraining();
    $QTiming         = new Application_Model_Timing();
    $QTimingTraining = new Application_Model_TimingTraining();
    $QStoreStaff     = new Application_Model_StoreStaff();
    $QStaff          = new Application_Model_Staff();
    $cachedStaff     = $QStaff->get_cache();
    $cachedNewStaff  = $QStaffTraining->get_cache();

    $db = Zend_Registry::get('db');

    try
    {

        $db->beginTransaction();

        $datetime    = new DateTime($formatedDate);
        $day         = $datetime->format('d');
        $month       = $datetime->format('m');
        $date_timing = $formatedDate .' '. $formatedFrom;


        foreach ($staffs as $k => $staff_id)
        {
            // neu la nhân viên mới thì chèn vào bảng mới
            if(in_array($staff_id,$new_staff))
            {
                // check staff in staff_training
                $staff_new_rowset  = $QStaffTraining->find($staff_id);
                $staff_new         = $staff_new_rowset->current();

                if (!$staff_new)
                {
                    throw new Exception("Invalid staff New ".$cachedNewStaff[$staff_id]." Please try again");
                }

                // check coi nó da cham công ngay đó chưa
                if($staff_new['title'] == PGPB_TITLE )
                {
                    $whereTimingTraining   = array();
                    $whereTimingTraining[] = $QTimingTraining->getAdapter()->quoteInto('shift = ?',        $shift);
                    $whereTimingTraining[] = $QTimingTraining->getAdapter()->quoteInto('date(`from`) = ?', $formatedDate);
                    $whereTimingTraining[] = $QTimingTraining->getAdapter()->quoteInto('staff_id = ?',     $staff_id);
                    $resultStaffTraining   = $QTimingTraining->fetchRow($whereTimingTraining);
                    if (isset($resultStaffTraining) and $resultStaffTraining)
                    {
                        throw new Exception("Staff ".$cachedNewStaff[$staff_id]." is exist");
                    }

                    $system_log_new_staff = 'Add time check in  for PG new by System at ' . date('Y-m-d H:i:s');

                    $dataTraining = array(
                        'shift'       => $shift,
                        'from'        => $formatedDate . ' ' . $formatedFrom,
                        'to'          => $formatedDate . ' ' . $formatedTo,
                        'status'      => 1,
                        'note'        => $system_log_new_staff,
                        'approved_at' => date('Y-m-d H:i:s'),
                        'approved_by' => $user_id,
                        'created_at'  => date('Y-m-d H:i:s'),
                        'created_by'  => $userStorage->id,
                        'staff_id'    => $staff_id
                    );

                    $QTimingTraining->insert($dataTraining);
                    // to do log
                    $info = array('INSERT ADD TIME CHECK IN NEW STAFF FOR TRAINING','new'=>$dataTraining);
                    $QLog->insert(array(
                        'info'       => json_encode($info),
                        'user_id'    => $userStorage->id,
                        'ip_address' => $ip,
                        'time'       => date('Y-m-d H:i:s')
                    ));
                }
                else
                {
                    throw new Exception(' Staff '.$cachedNewStaff[$staff_id].' Is Not PGPB ! ');
                }

            }
            else
            {
                $staff_rowset   = $QStaff->find($staff_id);
                $staff          = $staff_rowset->current();

                if (!$staff)
                {
                    throw new Exception("Invalid staff ".$cachedStaff[$staff_id]." Please try again");
                }

                if($staff['title'] == PGPB_TITLE )
                {
                    $where   = array();
                    $where[] = $QTiming->getAdapter()->quoteInto('shift = ?',        $shift);
                    $where[] = $QTiming->getAdapter()->quoteInto('date(`from`) = ?', $formatedDate);
                    $where[] = $QTiming->getAdapter()->quoteInto('staff_id = ?',     $staff_id);
                    $result  = $QTiming->fetchRow($where);
                    if (isset($result) and $result)
                    {
                        throw new Exception(" staff ".$cachedStaff[$staff_id]." exist");
                    }
                    $store = $QStoreStaff->getStore($staff_id);

                    if (empty($store))
                    {
                        throw new Exception("Checking shop is error");
                    }

                    $system_log = 'Add time for PG by System at ' . date('Y-m-d H:i:s');

                    $data = array(
                        'shift'       => $shift,
                        'from'        => $formatedDate . ' ' . $formatedFrom,
                        'to'          => $formatedDate . ' ' . $formatedTo,
                        'store'       => $store,
                        'status'      => 1,
                        'note'        => $system_log,
                        'approved_at' => date('Y-m-d H:i:s'),
                        'created_at'  => date('Y-m-d H:i:s'),
                        'created_by'  => $userStorage->id,
                        'staff_id'    => $staff_id
                    );

                    $QTiming->insert($data);
                    // to do log
                    $info = array('INSERT ADD TIME CHECK IN','new'=>$data);
                    $QLog->insert(array(
                        'info'       => json_encode($info),
                        'user_id'    => $userStorage->id,
                        'ip_address' => $ip,
                        'time'       => date('Y-m-d H:i:s')
                    ));

                }
                else
                {
                    throw new Exception(' Staff '.$cachedStaff[$staff_id].' Is Not PGPB ! ');
                }
            }

        }
        $db->commit();
        echo '<script>window.parent.document.getElementById("iframe").height = \'40px\';</script>';
        echo '<div class="alert alert-success">Success All</div>';
        echo '<script>setTimeout(function(){parent.location.href="'.HOST.'trainer/add-time-check-in"},20)</script>';

    }
    catch (exception $e)
    {
        $db->rollback();
        echo '<script>window.parent.document.getElementById("iframe").height = \'40px\';</script>';
        echo '<div class="alert alert-error">Failed - '.$e->getMessage().'</div>';
        exit;
    }
}