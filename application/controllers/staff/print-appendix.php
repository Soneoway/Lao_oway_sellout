<?php
$this->_helper->layout->disableLayout();
$back_url = $this->getRequest()->getParam('back_url');
$this->view->back_url = $back_url;
$ids = $this->getRequest()->getParam('id');
$contract_type = $this->getRequest()->getParam('contract_type');
$QArea = new Application_Model_Area();
$QSalarySales = new Application_Model_SalarySales();
$QSalaryPG = new Application_Model_SalaryPg();
$QLog = new Application_Model_StaffPrintLog();
$QContract = new Application_Model_ContractTerm();
$QStaffAddress = new Application_Model_StaffAddress();
$QRegionalMarket = new Application_Model_RegionalMarket();
$QTeam = new Application_Model_Team();
$areas = $QArea->get_cache();
$regional_markets = $QRegionalMarket->get_cache_all();
$contract_name = $QContract->get_cache();
$this->view->areas = $QArea->fetchAll();
$staffs = array();

$userStorage = Zend_Auth::getInstance()->getStorage()->read();

if (is_array($ids) && $ids)
{

    $staff_transfer_data = array();

    foreach ($ids as $key => $v)
    {

        $QStaffTransfer = new Application_Model_StaffTransfer();
        $staffRowset = $QStaffTransfer->find($v);
        $staffTransfer = $staffRowset->current();

        $QStaff = new Application_Model_Staff();
        $staffRowset = $QStaff->find($staffTransfer['staff_id']);
        $staff = $staffRowset->current();

        //update as printed
        $data = array(
            'printed_at' => date('Y-m-d h:i:s')
        );
        $where = $QStaffTransfer->getAdapter()->quoteInto('id = ? ' , $v);
        $QStaffTransfer->update($data, $where);
        $staff_transfer_data[$staffTransfer['staff_id']] = $staffTransfer['created_at'];

        //update ngay het han hop dong va ngay bat dau hop dong

        if (isset($staff['contract_signed_at']) and $staff['contract_signed_at'] == '')
        {
            $staff['contract_signed_at'] = $staff['joined_at'];
        }

        if (!$staff['birth_place'])
        {
            $where = array();
            $where[] = $QStaffAddress->getAdapter()->quoteInto('staff_id = ? ', $staff['id']);
            $where[] = $QStaffAddress->getAdapter()->quoteInto('address_type = ?', 4);
            $staff_adress = $QStaffAddress->fetchRow($where);
            $address = $staff_adress['address'] . ' , ' . $staff_adress['ward'];
            $regional_market = $staff_adress['district'];
            $Area_result = $QRegionalMarket->find($regional_market);
            $result_set = $Area_result->current();
            $area = $result_set['parent'];
            $regional_cache = $QRegionalMarket->get_district_cache($area);
            $regional_market = $regional_cache[$regional_market];
            $area = $regional_markets[$area];
            $staff['birth_place'] = $area['name'];
        }

        if (!$staff['address'])
        {
            $where = array();
            $where[] = $QStaffAddress->getAdapter()->quoteInto('staff_id = ? ', $staff['id']);
            $where[] = $QStaffAddress->getAdapter()->quoteInto('address_type = ?', 4);
            $staff_adress = $QStaffAddress->fetchRow($where);

            $address = $staff_adress['address'] . ' , ' . $staff_adress['ward'];
            $regional_market = $staff_adress['district'];

            $Area_result = $QRegionalMarket->find($regional_market);
            $result_set = $Area_result->current();
            $area = $result_set['parent'];

            $regional_cache = $QRegionalMarket->get_district_cache($area);


            $regional_market = $regional_cache[$regional_market];


            $area = $regional_markets[$area];
            if (isset($regional_market['name']))
                $address = $address . ' , ' . $regional_market['name'];

            if (isset($area))
                $address = $address . ' , ' . $area['name'];

            $staff['address'] = $address;
        }


        $start = $staff['contract_signed_at'];
        $end = $staff['contract_expired_at'];
        $start = $end;

        // $print_time = intval($staff['print_time']) + 1;

        //$data = array('print_time' => $print_time, );


        //$where = array();
        //$where[] = $QStaff->getAdapter()->quoteInto('id = ?', $staff['id']);
        ///$QStaff->update($data, $where);

        //luu log nhan vien

        $ip = $this->getRequest()->getServer('REMOTE_ADDR');


        //pg team
        if ($staff['title'] == PGPB_TITLE)
        {
            $where = array();
            $where[] = $QSalaryPG->getAdapter()->quoteInto('province_id = ?', $staff['regional_market']);
            $luong = $QSalaryPG->fetchRow($where);
        } else
        {
            $where = array();
            $where[] = $QSalarySales->getAdapter()->quoteInto('province_id = ?', $staff['regional_market']);
            $luong = $QSalarySales->fetchRow($where);
        }


        $info = 'Print contract term type = ' . $contract_name[$staff['contract_term']] .
            ' <br/>for: ' . $staff['firstname'] . '  ' . $staff['lastname'] .
            ' <br/> Form : ' . $staff['contract_signed_at'] . '<br/> To :' . $staff['contract_expired_at'];

        $QLog->insert(array(
            'info' => $info,
            'user_id' => $userStorage->id,
            'object' => $staff['id'],
            'ip_address' => $ip,
            'time' => date('Y-m-d H:i:s'),
            'contract_term' => $staff['contract_term'],
            'log_type' => STAFF_PRINT_LOG_PRINT,
            'from_date' => $staff['contract_signed_at'],
            'to_date' => $staff['contract_signed_at'],
            'title' => $staff['title'],
            'regional_market' => $staff['regional_market'],
            'base_salary' => $luong['base_salary'],
            'bonus_salary' => $luong['bonus_salary'],
            'allowance_1' => $luong['allowance_1'],
            'allowance_2' => $luong['allowance_2'],
            'allowance_3' => $luong['allowance_3'],
            'probation_salary' => $luong['probation_salary'],
            'kpi' => $luong['kpi']));

        $staffs[] = $staff;
    }

    $this->view->staff_transfer = $staff_transfer_data;
}



$luong = array();

foreach ($staffs as $k => $v)
{
    //sale team

    //pg team
    if ($v['title'] == PGPB_TITLE)
    {
        $where1 = array();
        $where1[] = $QSalaryPG->getAdapter()->quoteInto('province_id = ?', $v['regional_market']);
        $luong[$v['id']] = $QSalaryPG->fetchRow($where1);
    } else
    {
        $where = array();
        $where[] = $QSalarySales->getAdapter()->quoteInto('province_id = ?', $v['regional_market']);
        $luong[$v['id']] = $QSalarySales->fetchRow($where);
    }
}

$this->view->contract_name = $contract_name;
$this->view->luong = $luong;
$this->view->staff = $staffs;

$QGroup = new Application_Model_Group();
$this->view->groups = $QGroup->fetchAll();

$QModel = new Application_Model_ContractType();
$this->view->contract_types = $QModel->fetchAll();

$QModel = new Application_Model_ContractTerm();
$this->view->contract_terms = $QModel->fetchAll();

$QModel = new Application_Model_Department();
$this->view->departments = $QModel->fetchAll();

$QRegionalMarket = new Application_Model_RegionalMarket();

$this->view->regional_markets = $QRegionalMarket->get_cache();


$QModel = new Application_Model_Team();

$teams = $QModel->fetchAll();
$teams_data = array();
foreach ($teams as $k => $v)
{
    $teams_data[$v['id']] = $v['name_vn'];
}


$this->view->teams = $teams_data;

$flashMessenger = $this->_helper->flashMessenger;
$messages = $flashMessenger->setNamespace('error')->getMessages();
$this->view->messages = $messages;

$messages_success = $flashMessenger->setNamespace('success')->getMessages();
$this->view->messages_success = $messages_success;