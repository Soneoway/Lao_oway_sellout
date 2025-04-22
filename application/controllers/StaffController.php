<?php

class StaffController extends My_Controller_Action
{
    private $list_teams, $data, $list_titles;

    public function init()
    {
        $this->list_teams = array(
            SALES_TEAM,
            SALES_ADMIN_TEAM,
            TRAINING_TEAM,
            );
    }


    /*- configure PG salary action */

    public function configPgAction()
    {
        $sort = $this->getRequest()->getParam('sort', '');
        $desc = $this->getRequest()->getParam('desc', 1);
        $page = $this->getRequest()->getParam('page', 1);

        $limit = LIMITATION;

        $total = 0;

        $params['sort'] = $sort;
        $params['desc'] = $desc;
        $params['get_salary_pg'] = 1;

        $QRegionalMarket = new Application_Model_RegionalMarket();

        $provinces = $QRegionalMarket->fetchPagination($page, $limit, $total, $params);

        $this->view->desc = $desc;
        $this->view->sort = $sort;
        $this->view->provinces = $provinces;
        $this->view->params = $params;
        $this->view->limit = $limit;
        $this->view->total = $total;
        $this->view->url = HOST . 'staff/config-pg' . ($params ? '?' . http_build_query
            ($params) . '&' : '?');

        $this->view->offset = $limit * ($page - 1);

        $flashMessenger = $this->_helper->flashMessenger;
        $messages = $flashMessenger->setNamespace('success')->getMessages();
        $this->view->messages = $messages;
    }


    public function configPgEditAction()
    {
        $province_id = $this->getRequest()->getParam('province_id');
        $QModel = new Application_Model_SalaryPg();

        if ($province_id)
        {

            $where = $QModel->getAdapter()->quoteInto('province_id = ?', $province_id);

            $item = $QModel->fetchRow($where);

            $this->view->item = $item;

            //get province
            $QRegionalMarket = new Application_Model_RegionalMarket();

            $where = $QRegionalMarket->getAdapter()->quoteInto('id = ?', $province_id);

            $province = $QRegionalMarket->fetchRow($where);

            $this->view->province = $province;
        }

        if ($this->getRequest()->getMethod() == 'POST')
        {

            $base_salary = $this->getRequest()->getParam('base_salary');
            $probation_salary = $this->getRequest()->getParam('probation_salary');
            $bonus_salary = $this->getRequest()->getParam('bonus_salary');
            $allowance_1 = $this->getRequest()->getParam('allowance_1');
            $allowance_2 = $this->getRequest()->getParam('allowance_2');
            $allowance_3 = $this->getRequest()->getParam('allowance_3');
            $kpi = trim($this->getRequest()->getParam('kpi'));
            $kpi_1 = trim($this->getRequest()->getParam('kpi_1'));


            $data = array(
                'base_salary' => $base_salary,
                'bonus_salary' => $bonus_salary,
                'allowance_1' => $allowance_1,
                'allowance_2' => $allowance_2,
                'allowance_3' => $allowance_3,
                'probation_salary' => $probation_salary,
                'kpi' => $kpi,
                'kpi_1' => $kpi_1,
                );

            if (isset($item) and $item) //neu co record nay roi

                $QModel->update($data, 'province_id = ' . $province_id);

            else
            {
                $data['province_id'] = $province_id;
                $QModel->insert($data);
            }

            $back_url = $this->getRequest()->getParam('back_url');

            $this->_redirect($back_url ? $back_url : HOST . 'staff/config-pg');
        }

        $flashMessenger = $this->_helper->flashMessenger;
        $messages = $flashMessenger->setNamespace('error')->getMessages();
        $this->view->messages = $messages;

        //back url
        $this->view->back_url = $this->getRequest()->getServer('HTTP_REFERER');
    }

    /* - configure sale salary action  */

    public function configSalesAction()
    {
        $sort = $this->getRequest()->getParam('sort', '');
        $desc = $this->getRequest()->getParam('desc', 1);
        $page = $this->getRequest()->getParam('page', 1);

        $limit = LIMITATION;

        $total = 0;

        $params['sort'] = $sort;
        $params['desc'] = $desc;
        $params['get_salary_sales'] = 1;

        $QRegionalMarket = new Application_Model_RegionalMarket();

        $provinces = $QRegionalMarket->fetchPagination($page, $limit, $total, $params);

        $this->view->desc = $desc;
        $this->view->sort = $sort;
        $this->view->provinces = $provinces;
        $this->view->params = $params;
        $this->view->limit = $limit;
        $this->view->total = $total;
        $this->view->url = HOST . 'staff/config-sales' . ($params ? '?' .
            http_build_query($params) . '&' : '?');

        $this->view->offset = $limit * ($page - 1);

        $flashMessenger = $this->_helper->flashMessenger;
        $messages = $flashMessenger->setNamespace('success')->getMessages();
        $this->view->messages = $messages;

    }

    /* configure sale edit action*/

    public function configSalesEditAction()
    {
        $province_id = $this->getRequest()->getParam('province_id');
        $QModel = new Application_Model_SalarySales();

        if ($province_id)
        {

            $where = $QModel->getAdapter()->quoteInto('province_id = ?', $province_id);

            $item = $QModel->fetchRow($where);

            $this->view->item = $item;

            //get province
            $QRegionalMarket = new Application_Model_RegionalMarket();

            $where = $QRegionalMarket->getAdapter()->quoteInto('id = ?', $province_id);

            $province = $QRegionalMarket->fetchRow($where);

            $this->view->province = $province;
        }

        if ($this->getRequest()->getMethod() == 'POST')
        {

            $base_salary = $this->getRequest()->getParam('base_salary');
            $bonus_salary = $this->getRequest()->getParam('bonus_salary');
            $allowance_1 = $this->getRequest()->getParam('allowance_1');
            $allowance_2 = $this->getRequest()->getParam('allowance_2');
            $allowance_3 = $this->getRequest()->getParam('allowance_3');
            $probation_salary = $this->getRequest()->getParam('probation_salary');
            $kpi = $this->getRequest()->getParam('kpi');

            $data = array(
                'base_salary' => $base_salary,
                'bonus_salary' => $bonus_salary,
                'allowance_1' => $allowance_1,
                'allowance_2' => $allowance_2,
                'allowance_3' => $allowance_3,
                'probation_salary' => $probation_salary,
                'kpi' => $kpi,
                );

            if (isset($item) and $item)
                $QModel->update($data, 'province_id = ' . $province_id);

            else
            {
                $data['province_id'] = $province_id;
                $QModel->insert($data);
            }

            $back_url = $this->getRequest()->getParam('back_url');

            $this->_redirect($back_url ? $back_url : HOST . 'staff/config-sales');
        }

        $flashMessenger = $this->_helper->flashMessenger;
        $messages = $flashMessenger->setNamespace('error')->getMessages();
        $this->view->messages = $messages;

        //back url
        $this->view->back_url = $this->getRequest()->getServer('HTTP_REFERER');
    }

    public function contractTermAction()
    {
        $sort = $this->getRequest()->getParam('sort', '');
        $desc = $this->getRequest()->getParam('desc', 1);
        $page = $this->getRequest()->getParam('page', 1);
        $name = $this->getRequest()->getParam('name');
        $department = $this->getRequest()->getParam('department');
        $off = $this->getRequest()->getParam('off', 1);
        $team = $this->getRequest()->getParam('team');
        $regional_market = $this->getRequest()->getParam('regional_market');
        $district = $this->getRequest()->getParam('district');
        $area_id = $this->getRequest()->getParam('area_id');
        $note = $this->getRequest()->getParam('note');
        $sname = $this->getRequest()->getParam('sname', 0);
        $code = $this->getRequest()->getParam('code');
        $s_assign = $this->getRequest()->getParam('s_assign');
        $ood = $this->getRequest()->getParam('ood');
        $email = $this->getRequest()->getParam('email');
        $contract_term = $this->getRequest()->getParam('contract_term');
        $is_officer = $this->getRequest()->getParam('is_officer');
        $ready_print = $this->getRequest()->getParam('ready_print');
        $date = $this->getRequest()->getParam('date');
        $month = $this->getRequest()->getParam('month');
        $year = $this->getRequest()->getParam('year');
        $tags = $this->getRequest()->getParam('tags');
        $title = $this->getRequest()->getParam('title');
        $company_id = $this->getRequest()->getParam('company_id');
        $no_print = $this->getRequest()->getParam('no_print');
        $from = $this->getRequest()->getParam('from', date('01/m/Y'));
        $to = $this->getRequest()->getParam('to', date('d/m/Y'));
        $QStaff = new Application_Model_Staff();
        if ($tags and is_array($tags))
            $tags = $tags;
        else
            $tags = null;

        //check if export
        $export = $this->getRequest()->getParam('export', 0);
        $expired_contract = $this->getRequest()->getParam('expired_contract', 0);

        if (!$sname)
            $limit = LIMITATION;
        else
            $limit = null;

        $total = 0;

        $params = array_filter(array(
            'name' => $name,
            'department' => $department,
            'off' => $off,
            'team' => $team,
            'regional_market' => $regional_market,
            'district' => $district,
            'area_id' => $area_id,
            'note' => $note,
            'code' => $code,
            's_assign' => $s_assign,
            'ood' => $ood,
            'email' => $email,
            's_assign' => $s_assign,
            'ready_print' => $ready_print,
            'ood' => $ood,
            'email' => $email,
            'date' => $date,
            'month' => $month,
            'year' => $year,
            'is_officer' => $is_officer,
            'sname' => $sname,
            'tags' => $tags,
            'title' => $title,
            'company_id' => $company_id,
            'export' => $export,
            'contract_term' => $contract_term,
            'no_print' => $no_print,
            'from' => $from,
            'to' => $to,
            ));

        if ($export == 1)
        {
            $this->_exportContractPrintingLog($params);
            exit;
        }


        $params['sort'] = $sort;
        $params['desc'] = $desc;

        $QArea = new Application_Model_Area();
        $this->view->areas = $QArea->fetchAll(null, 'name');

        $QRegionalMarket = new Application_Model_RegionalMarket();
        $QSalarySales = new Application_Model_SalarySales();
        $QSalaryPG = new Application_Model_SalaryPg();

        $this->view->regional_markets = $QRegionalMarket->get_cache();

        if ($regional_market)
        {
            if (is_array($regional_market) && count($regional_market))
                $where = $QRegionalMarket->getAdapter()->quoteInto('parent IN (?)', $regional_market);
            else
                $where = $QRegionalMarket->getAdapter()->quoteInto('parent = ?', $regional_market);

            $this->view->districts = $QRegionalMarket->fetchAll($where, 'name');
        }


        //get salary sales
        $params['salary_sales'] = 1;

        $staffs = $QStaff->fetchPagination($page, $limit, $total, $params);

        if ($export == 2)
        {
            $staffs = $QStaff->fetchPagination($page, null, $total, $params);
            $this->_exportExcel($staffs);
        }
        $luong = array();

        foreach ($staffs as $k => $v)
        {
            //sale team
            if ($v['title'] == SALES_TITLE || $v['team'] == SALES_ACCESSORIES_TITLE)
            {
                $where = array();
                $where[] = $QSalarySales->getAdapter()->quoteInto('province_id = ?', $v['regional_market']);
                $luong[$v['id']] = $QSalarySales->fetchRow($where);
            }

            //pg team
            if ($v['title'] == PGPB_TITLE)
            {
                $where1 = array();
                $where1[] = $QSalaryPG->getAdapter()->quoteInto('province_id = ?', $v['regional_market']);
                $luong[$v['id']] = $QSalaryPG->fetchRow($where1);
            }

        }

        // var_dump($luong);exit;


        //get department
        $QDepartment = new Application_Model_Department();
        $this->view->departments = $QDepartment->get_cache();

        //get teams
        $QTeam = new Application_Model_Team();


        $recursiveDeparmentTeamTitle = $QTeam->get_recursive_cache();
        $this->view->recursiveDeparmentTeamTitle = $recursiveDeparmentTeamTitle;


        $this->view->luong = $luong;

        $this->view->team_all = $QTeam->get_cache();

        $QCompany = new Application_Model_Company();
        $this->view->companies = $QCompany->get_cache();

        $QModel = new Application_Model_ContractTerm();
        $this->view->contract_terms = $QModel->fetchAll();
        $this->view->list_contract = $QModel->get_cache();

        $userStorage = Zend_Auth::getInstance()->getStorage()->read();

        $this->view->userStorage = $userStorage;

        $this->view->desc = $desc;
        $this->view->sort = $sort;
        $this->view->staffs = $staffs;
        $this->view->params = $params;
        $this->view->limit = $limit;
        $this->view->total = $total;

        $this->view->url = HOST . 'staff/contract-term' . ($params ? '?' .
            http_build_query($params) . '&' : '?');

        $this->view->offset = $limit * ($page - 1);

        $flashMessenger = $this->_helper->flashMessenger;
        $this->view->messages_success = $flashMessenger->setNamespace('success')->
            getMessages();
        $this->view->messages = $flashMessenger->setNamespace('error')->getMessages();

        $this->view->current_url = trim($this->getRequest()->getRequestUri(), '/');

        if ($this->getRequest()->isXmlHttpRequest())
        {
            $this->_helper->layout->disableLayout();

            if ($sname)
            {
                $QRegion = new Application_Model_RegionalMarket();
                $regional_markets = $QRegion->fetchAll();

                $rm = array();
                foreach ($regional_markets as $key => $value)
                {
                    $rm[$value['id']] = $value['name'];
                }
                $this->view->rm_list = $rm;

                $this->_helper->viewRenderer->setRender('partials/searchname');
            } elseif ($s_assign)
            {
                $this->_helper->viewRenderer->setRender('partials/staff');
            } else
                $this->_helper->viewRenderer->setRender('partials/list');
        }
    }

    public function contractTermOfficeAction()
    {
        $sort = $this->getRequest()->getParam('sort', '');
        $desc = $this->getRequest()->getParam('desc', 1);
        $page = $this->getRequest()->getParam('page', 1);
        $name = $this->getRequest()->getParam('name');
        $department = $this->getRequest()->getParam('department');
        $off = $this->getRequest()->getParam('off', 1);
        $team = $this->getRequest()->getParam('team');
        $regional_market = $this->getRequest()->getParam('regional_market');
        $district = $this->getRequest()->getParam('district');
        $area_id = $this->getRequest()->getParam('area_id');
        $note = $this->getRequest()->getParam('note');
        $sname = $this->getRequest()->getParam('sname', 0);
        $code = $this->getRequest()->getParam('code');
        $s_assign = $this->getRequest()->getParam('s_assign');
        $ood = $this->getRequest()->getParam('ood');
        $email = $this->getRequest()->getParam('email');
        $contract_term = $this->getRequest()->getParam('contract_term');
        $is_officer = $this->getRequest()->getParam('is_officer');
        $ready_print = $this->getRequest()->getParam('ready_print');
        $date = $this->getRequest()->getParam('date');
        $month = $this->getRequest()->getParam('month');
        $year = $this->getRequest()->getParam('year');
        $tags = $this->getRequest()->getParam('tags');
        $title = $this->getRequest()->getParam('title');
        $company_id = $this->getRequest()->getParam('company_id');
        $no_print = $this->getRequest()->getParam('no_print');
        $from = $this->getRequest()->getParam('from', date('01/m/Y'));
        $to = $this->getRequest()->getParam('to', date('d/m/Y'));

        if ($tags and is_array($tags))
            $tags = $tags;
        else
            $tags = null;

        //check if export
        $export = $this->getRequest()->getParam('export', 0);
        $expired_contract = $this->getRequest()->getParam('expired_contract', 0);

        if (!$sname)
            $limit = LIMITATION;
        else
            $limit = null;

        $total = 0;

        $params = array_filter(array(
            'name' => $name,
            'department' => $department,
            'off' => $off,
            'team' => $team,
            'regional_market' => $regional_market,
            'district' => $district,
            'area_id' => $area_id,
            'note' => $note,
            'code' => $code,
            's_assign' => $s_assign,
            'ood' => $ood,
            'email' => $email,
            's_assign' => $s_assign,
            'ready_print' => $ready_print,
            'ood' => $ood,
            'email' => $email,
            'date' => $date,
            'month' => $month,
            'year' => $year,
            'is_officer' => $is_officer,
            'sname' => $sname,
            'tags' => $tags,
            'title' => $title,
            'company_id' => $company_id,
            'export' => $export,
            'contract_term' => $contract_term,
            'no_print' => $no_print,
            'from' => $from,
            'to' => $to,
            ));

        if ($export == 1)
        {
            $this->_exportContractPrintingLog($params);
            exit;
        }

        $params['sort'] = $sort;
        $params['desc'] = $desc;

        $QArea = new Application_Model_Area();
        $this->view->areas = $QArea->fetchAll(null, 'name');

        $QRegionalMarket = new Application_Model_RegionalMarket();
        $QSalarySales = new Application_Model_SalarySales();
        $QSalaryPG = new Application_Model_SalaryPg();


        $this->view->regional_markets = $QRegionalMarket->get_cache();


        if ($regional_market)
        {
            if (is_array($regional_market) && count($regional_market))
                $where = $QRegionalMarket->getAdapter()->quoteInto('parent IN (?)', $regional_market);
            else
                $where = $QRegionalMarket->getAdapter()->quoteInto('parent = ?', $regional_market);

            $this->view->districts = $QRegionalMarket->fetchAll($where, 'name');
        }

        $QStaff = new Application_Model_Staff();

        //get salary sales
        $params['is_officer'] = 1;

        $staffs = $QStaff->fetchPagination($page, $limit, $total, $params);

        $luong = array();

        foreach ($staffs as $k => $v)
        {
            //sale team
            if ($v['is_officer'] == 1)
            {
                $where = array();
                $where[] = $QSalarySales->getAdapter()->quoteInto('province_id = ?', $v['regional_market']);
                $luong[$v['id']] = $QSalarySales->fetchRow($where);
            }

        }



        //get department
        //get department
        $QDepartment = new Application_Model_Department();
        $this->view->departments = $QDepartment->get_cache();

        //get teams
        $QTeam = new Application_Model_Team();


        $recursiveDeparmentTeamTitle = $QTeam->get_recursive_cache();
        $this->view->recursiveDeparmentTeamTitle = $recursiveDeparmentTeamTitle;

        $this->view->luong = $luong;

        $this->view->team_all = $QTeam->get_cache();

        $QCompany = new Application_Model_Company();
        $this->view->companies = $QCompany->get_cache();

        $QModel = new Application_Model_ContractTerm();
        $this->view->contract_terms = $QModel->fetchAll();
        $this->view->list_contract = $QModel->get_cache();

        $userStorage = Zend_Auth::getInstance()->getStorage()->read();

        $this->view->userStorage = $userStorage;

        $this->view->desc = $desc;
        $this->view->sort = $sort;
        $this->view->staffs = $staffs;
        $this->view->params = $params;
        $this->view->limit = $limit;
        $this->view->total = $total;

        $this->view->url = HOST . 'staff/contract-term-office' . ($params ? '?' .
            http_build_query($params) . '&' : '?');

        $this->view->offset = $limit * ($page - 1);

        $flashMessenger = $this->_helper->flashMessenger;
        $this->view->messages_success = $flashMessenger->setNamespace('success')->
            getMessages();
        $this->view->messages = $flashMessenger->setNamespace('error')->getMessages();

        $this->view->current_url = trim($this->getRequest()->getRequestUri(), '/');

        if ($this->getRequest()->isXmlHttpRequest())
        {
            $this->_helper->layout->disableLayout();

            if ($sname)
            {
                $QRegion = new Application_Model_RegionalMarket();
                $regional_markets = $QRegion->fetchAll();

                $rm = array();
                foreach ($regional_markets as $key => $value)
                {
                    $rm[$value['id']] = $value['name'];
                }
                $this->view->rm_list = $rm;

                $this->_helper->viewRenderer->setRender('partials/searchname');
            } elseif ($s_assign)
            {
                $this->_helper->viewRenderer->setRender('partials/staff');
            } else
                $this->_helper->viewRenderer->setRender('partials/list');
        }
    }

    /* update contract function */

    public function updateContractAction()
    {
        $this->_helper->layout->disableLayout();
        $back_url = $this->getRequest()->getParam('back_url');
        $this->view->back_url = $back_url;
        $ids = $this->getRequest()->getParam('id');
        $contract_type = $this->getRequest()->getParam('contract_type');
        $print_time = $this->getRequest()->getParam('print_time');
        $QArea = new Application_Model_Area();
        $QSalarySales = new Application_Model_SalarySales();
        $QSalaryPG = new Application_Model_SalaryPg();
        $QLog = new Application_Model_StaffPrintLog();

        $this->view->areas = $QArea->fetchAll();
        $staffs = array();
        $contract_name = array();

        $userStorage = Zend_Auth::getInstance()->getStorage()->read();

        try
        {
            if (is_array($ids) && $ids)
            {

                foreach ($ids as $key => $v)
                {

                    $QStaff = new Application_Model_Staff();
                    $staffRowset = $QStaff->find($v);
                    $staff = $staffRowset->current();

                    //update ngay het han hop dong va ngay bat dau hop dong

                    if (empty($staff['contract_signed_at']))
                    {
                        $staff['contract_signed_at'] = $staff['joined_at'];
                    }

                    if (isset($contract_type) and $contract_type == '')
                    {
                        $data = array();
                        $data['print_time'] = intval($print_time);
                        $where = array();
                        $where[] = $QStaff->getAdapter()->quoteInto('id = ?', $staff['id']);
                        $QStaff->update($data, $where);

                    } else
                    {
                        $start = $staff['contract_signed_at'];

                        if ($staff['print_time'] != 0)
                        {
                            $end = date("Y-m-d", strtotime($staff['contract_expired_at'] . "+ 1 days"));
                            $start = $end;
                        }

                        //hop dong thu viec
                        if (isset($contract_type) and $contract_type == CONTRACT_TERM_LABOUR)
                        {
                            if(empty($end))
                                $end = $staff['contract_signed_at'];

                            if($staff['department'] == DEPARTMENT_WARRANTY_CENTER)
                                $end = date("Y-m-d", strtotime($end . "+ 29 days"));
                            else
                                $end = date("Y-m-d", strtotime($end . "+ 59 days"));

                            $contract_name[$staff['id']] = "Thử việc";
                        }

                        //hop dong 1 nam
                        if (isset($contract_type) and $contract_type == CONTRACT_TERM_12_MONTH)
                        {
                            if (isset($staff['title']) and $staff['title'] == PGPB_TITLE)
                            {
                                $start = date("Y-m-d", strtotime($start . "+ 4 days"));
                                $end = date("Y-m-d", strtotime($end . "+ 5 days"));
                            }

                            else if (isset($staff['title']) and $staff['title'] == SALES_TITLE and $staff['contract_term'] ==
                                CONTRACT_TERM_SEASONAL_CHALLENGE)
                            {
                                $start = date("Y-m-d", strtotime($start . "+ 4 days"));
                                $end = date("Y-m-d", strtotime($end . "+ 5 days"));
                            }



                            $end = date("Y-m-d", strtotime($end . "+ 364 days"));
                            $contract_name[$staff['id']] = "12 Tháng";
                        }

                        //hop dong th?i v?
                        if (isset($contract_type) and $contract_type == CONTRACT_TERM_SEASONAL)
                        {
                            $end = date("Y-m-d", strtotime($end . "+ 84 days"));
                            $contract_name[$staff['id']] = "Th?i v?";
                        }

                        //hop dong thoi vu thu thach cho sale
                        if (isset($contract_type) and $contract_type == CONTRACT_TERM_SEASONAL_CHALLENGE and
                            $staff['title'] == SALES_TITLE)
                        {

                            $end = date("Y-m-d", strtotime($end . "+ 29 days"));
                            $contract_name[$staff['id']] = "Th?i v?";
                        }

                        //hop dong thoi vu thu thach cho pg
                        if (isset($contract_type) and $contract_type == CONTRACT_TERM_SEASONAL_CHALLENGE and
                            $staff['title'] == PGPB_TITLE)
                        {
                            $start = date("Y-m-d", strtotime($start . "+ 4 days"));
                            $end = date("Y-m-d", strtotime($end . "+ 5 days"));
                            $end = date("Y-m-d", strtotime($end . "+ 29 days"));
                            $contract_name[$staff['id']] = "Thử việc";
                        }

                        //chua in hop dong
                        if (isset($contract_type) and $contract_type == CONTRACT_TERM_NOT_YET)
                        {
                            $end = '';
                            $start = '';
                            $contract_name[$staff['id']] = "";

                        }

                        //hop dong 3 nam
                        if (isset($contract_type) and $contract_type == CONTRACT_TERM_32_MONTH)
                        {
                            $end = date("Y-m-d", strtotime($end . "+ 1094 days"));
                            $contract_name[$staff['id']] = "36 tháng";
                        }

                        //hop dong khong thoi han
                        if (isset($contract_type) and $contract_type == CONTRACT_TERM_UNLIMITED)
                        {
                            $contract_name[$staff['id']] = "Không xác định thời hạn";
                        }




                        $data = array(
                        'contract_signed_at' => $start,
                        'contract_expired_at' => $end

                        );


                        if (isset($contract_type) and $contract_type)
                            $data['contract_term'] = $contract_type;

                        if (isset($contract_type) and $contract_type)
                            $data['print_time'] = 0;
                        else
                        {
                            $data['print_time'] = intval($print_time);
                        }


                        $staff['contract_signed_at'] = $start;
                        $staff['contract_expired_at'] = $end;


                        $where = array();
                        $where[] = $QStaff->getAdapter()->quoteInto('id = ?', $staff['id']);

                        $QStaff->update($data, $where);


                        //luu log nhan vien

                        $ip = $this->getRequest()->getServer('REMOTE_ADDR');

                        $info = 'Update contract term type = ' . $contract_name[$staff['id']] . ' for: ' .
                            $staff['firstname'] . ' ' . $staff['lastname'] . ' <br/> Form : ' . $staff['contract_signed_at'] .
                            ' To :' . $staff['contract_expired_at'];


                        //sale team
                        if ($staff['title'] == SALES_TITLE || $staff['title'] == SALES_ACCESSORIES_TITLE)
                        {
                            $where = array();
                            $where[] = $QSalarySales->getAdapter()->quoteInto('province_id = ?', $staff['regional_market']);
                            $luong = $QSalarySales->fetchRow($where);
                        }

                        //pg team
                        if ($staff['title'] == PGPB_TITLE)
                        {
                            $where = array();
                            $where[] = $QSalaryPG->getAdapter()->quoteInto('province_id = ?', $staff['regional_market']);
                            $luong = $QSalaryPG->fetchRow($where);
                        }

                        $db = Zend_Registry::get('db');
                        $selectTitle = $db->select()->from(array('p' => 'team'), array('name'))->where('id = ?',
                            $staff['title']);
                        $title = $db->fetchOne($selectTitle);

                        $QLog->insert(array(
                            'info' => $info,
                            'user_id' => $userStorage->id,
                            'object' => $staff['id'],
                            'ip_address' => $ip,
                            'time' => date('Y-m-d H:i:s'),
                            'contract_term' => $contract_type,
                            'log_type' => STAFF_PRINT_LOG_UPDATE,
                            'from_date' => $staff['contract_signed_at'],
                            'to_date' => $staff['contract_expired_at'],
                            'title' => $title,
                            'regional_market' => $staff['regional_market'],
                            'base_salary' => $luong['base_salary'] ? $luong['base_salary'] : 0,
                            'bonus_salary' => $luong['bonus_salary'] ? $luong['bonus_salary'] : 0,
                            'allowance_1' => $luong['allowance_1'] ? $luong['allowance_1'] : 0,
                            'allowance_2' => $luong['allowance_2'] ? $luong['allowance_2'] : 0,
                            'allowance_3' => $luong['allowance_3'] ? $luong['allowance_3'] : 0,
                            'probation_salary' => $luong['probation_salary'] ? $luong['probation_salary'] : 0,
                            'kpi' => $luong['kpi'] ? $luong['kpi'] : '',
                        ));
                        $staffs[] = $staff;
                    }
                }
            }
        }
        catch (exception $e)
        {
            var_dump($e);
            exit;
        }

        echo '1';
        exit;
    }
    /* update contract function */

    public function updateContractOfficeAction()
    {
        $this->_helper->layout->disableLayout();
        $back_url = $this->getRequest()->getParam('back_url');
        $ids = $this->getRequest()->getParam('id');
        $contract_type = $this->getRequest()->getParam('contract_type');
        $print_time = $this->getRequest()->getParam('print_time');
        $QArea = new Application_Model_Area();
        $QSalarySales = new Application_Model_SalarySales();
        $QSalaryPG = new Application_Model_SalaryPg();
        $QLog = new Application_Model_StaffPrintLog();

        $this->view->back_url = $back_url;
        $this->view->areas = $QArea->fetchAll();
        $staffs = array();
        $contract_name = array();

        $userStorage = Zend_Auth::getInstance()->getStorage()->read();

        try
        {
            if (is_array($ids) && $ids)
            {

                foreach ($ids as $key => $v)
                {

                    $QStaff = new Application_Model_Staff();
                    $staffRowset = $QStaff->find($v);
                    $staff = $staffRowset->current();

                    //update ngay het han hop dong va ngay bat dau hop dong

                    if (!isset($staff['contract_signed_at']))
                    {
                        $staff['contract_signed_at'] = $staff['join_date'];
                    }
                    if (isset($contract_type) and $contract_type == '')
                    {
                        $data = array();
                        $data['print_time'] = intval($print_time);
                        $where = array();
                        $where[] = $QStaff->getAdapter()->quoteInto('id = ?', $staff['id']);
                        $QStaff->update($data, $where);

                    } else
                    {

                        $start = $staff['contract_signed_at'];
                        if ($staff['print_time'] != 0)
                            $end = date("Y-m-d", strtotime($staff['contract_expired_at'] . "+ 1 days"));
                        $start = $end;

                        //hop dong thu viec
                        if (isset($contract_type) and $contract_type == '2')
                        {
                            $end = date("Y-m-d", strtotime($end . "+ 57 days"));
                            $contract_name[$staff['id']] = "Thử việc";
                        }

                        //hop dong 1 nam
                        if (isset($contract_type) and $contract_type == '1')
                        {
                            if (isset($staff['is_officer']) and $staff['is_officer'])
                            {
                                $start = date("Y-m-d", strtotime($start . "+ 4 days"));
                                $end = date("Y-m-d", strtotime($end . "+ 5 days"));
                            }

                            $end = date("Y-m-d", strtotime($end . "+ 364 days"));
                            $contract_name[$staff['id']] = "12 tháng";
                        }

                        //chua in hop dong
                        if (isset($contract_type) and $contract_type == '5')
                        {
                            $end = '';
                            $start = '';
                            $contract_name[$staff['id']] = "";
                        }

                        //hop dong 3 nam
                        if (isset($contract_type) and $contract_type == '6')
                        {
                            $end = date("Y-m-d", strtotime($end . "+ 1094 days"));
                            $contract_name[$staff['id']] = "36 tháng";
                        }

                        //hop dong khong thoi han
                        if (isset($contract_type) and $contract_type == '7')
                        {
                            $contract_name[$staff['id']] = "Không xác định thời hạn";
                        }


                        $data = array(
                            'contract_signed_at' => $start,
                            'contract_expired_at' => $end,

                            );

                        if (isset($contract_type) and $contract_type)
                            $data['contract_term'] = $contract_type;

                        if (isset($contract_type) and $contract_type)
                            $data['print_time'] = 0;
                        else
                        {
                            $data['print_time'] = intval($print_time);
                        }


                        $staff['contract_signed_at'] = $start;
                        $staff['contract_expired_at'] = $end;

                        $where = array();
                        $where[] = $QStaff->getAdapter()->quoteInto('id = ?', $staff['id']);


                        $QStaff->update($data, $where);

                        //luu log nhan vien

                        $ip = $this->getRequest()->getServer('REMOTE_ADDR');

                        $info = 'Update contract term type = ' . $contract_name[$staff['id']] . ' for: ' .
                            $staff['firstname'] . ' ' . $staff['lastname'] . ' <br/> Form : ' . $staff['contract_signed_at'] .
                            ' To :' . $staff['contract_expired_at'];

                        $QLog->insert(array(
                            'info' => $info,
                            'user_id' => $userStorage->id,
                            'object' => $staff['id'],
                            'ip_address' => $ip,
                            'time' => date('Y-m-d H:i:s'),
                            ));

                        $staffs[] = $staff;
                    }
                }
            }
        }
        catch (exception $e)
        {
            echo '-1';
            exit;
        }

        echo '1';
        exit;
    }

    public function printAction()
    {
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
        $areas = $QArea->get_cache();
        $regional_markets = $QRegionalMarket->get_cache_all();


        $contract_name = $QContract->get_cache();

        $this->view->areas = $QArea->fetchAll();
        $staffs = array();

        $userStorage = Zend_Auth::getInstance()->getStorage()->read();

        if (is_array($ids) && $ids)
        {

            foreach ($ids as $key => $v)
            {

                $QStaff = new Application_Model_Staff();
                $staffRowset = $QStaff->find($v);
                $staff = $staffRowset->current();

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
        $this->view->teams = $QModel->fetchAll();

        $flashMessenger = $this->_helper->flashMessenger;
        $messages = $flashMessenger->setNamespace('error')->getMessages();
        $this->view->messages = $messages;

        $messages_success = $flashMessenger->setNamespace('success')->getMessages();
        $this->view->messages_success = $messages_success;


    }

    public function printLogAction()
    {
        $object = $this->getRequest()->getParam('object');

        if ($object)
        {
            $QStaffLog = new Application_Model_StaffPrintLog();
            $QRegionalMarket = new Application_Model_RegionalMarket();
            $regional = $QRegionalMarket->get_cache();

            $QContractTerm = new Application_Model_ContractTerm();
            $contract_term = $QContractTerm->get_cache();

            $from = $this->getRequest()->getParam('from');
            $to = $this->getRequest()->getParam('to');
            $page = $this->getRequest()->getParam('page', 1);
            $limit = LIMITATION;
            $total = 0;

            $params = array(
                'object' => $object,
                'from' => $from,
                'to' => $to,
                );

            $log_res = $QStaffLog->fetchPagination($page, $limit, $total, $params);
            $logs = array();

            foreach ($log_res as $k => $log)
            {
                $logs[] = array(
                    'print_log_id' => $log['id'],
                    'time' => $log['time'],
                    'user_id' => $log['user_id'],
                    'ip' => $log['ip_address'],
                    'info' => $log['info'],
                    'object' => $log['object'],
                    'base_salary' => $log['base_salary'],
                    'bonus_salary' => $log['bonus_salary'],
                    'allowance_1' => $log['allowance_1'],
                    'allowance_2' => $log['allowance_2'],
                    'allowance_3' => $log['allowance_3'],
                    'contract_term' => isset($contract_term[$log['contract_term']]) ? $contract_term[$log['contract_term']] :
                        '',
                    'regional_market' => isset($regional[$log['regional_market']]) ? $regional[$log['regional_market']] :
                        '',
                    'probation_salary' => $log['probation_salary'],
                    'from_date' => $log['from_date'],
                    'to_date' => $log['to_date'],
                    'kpi' => $log['kpi'],
                    'title' => $log['title'],
                    );
            }

            $n = count($logs);


            $QStaff = new Application_Model_Staff();
            $QGroup = new Application_Model_Group();
            $QContractTerm = new Application_Model_ContractTerm();
            $QContractType = new Application_Model_ContractType();
            $QRegionalMarket = new Application_Model_RegionalMarket();

            $this->view->staffs = $QStaff->get_cache();
            $this->view->regionalMarket = $QRegionalMarket->get_cache();
            $this->view->group = $QGroup->get_cache();
            $this->view->contractTerm = $QContractTerm->get_cache();
            $this->view->contractType = $QContractType->get_cache();
            $this->view->object = $object;

            $this->view->logs = $logs;
            $this->view->params = $params;
            $this->view->limit = $limit;
            $this->view->total = $total;
            $this->view->url = HOST . 'staff/print-log' . ($params ? '?' . http_build_query
                ($params) . '&' : '?');

            $this->view->offset = $limit * ($page - 1);

            $flashMessenger = $this->_helper->flashMessenger;
            $messages = $flashMessenger->setNamespace('error')->getMessages();
            $this->view->messages = $messages;

            $messages_success = $flashMessenger->setNamespace('success')->getMessages();
            $this->view->messages_success = $messages_success;
        } else
        {
            $this->_redirect(HOST . 'staff/contract-term');
        }
    }

    public function indexAction()
    {

        // $this->_helper->viewRenderer->setNoRender(true);
        // echo "<br/><div align='center'><img alt='under_construction' src='/img/under-construction-sign.png'></div><br/>";

        $sort            = $this->getRequest()->getParam('sort', '');
        $desc            = $this->getRequest()->getParam('desc', 1);
        $page            = $this->getRequest()->getParam('page', 1);
        $name            = $this->getRequest()->getParam('name');
        $department      = $this->getRequest()->getParam('department');
        $off             = $this->getRequest()->getParam('off', 1);
        $team            = $this->getRequest()->getParam('team');
        $regional_market = $this->getRequest()->getParam('regional_market');
        $district        = $this->getRequest()->getParam('district');
        $area_id         = $this->getRequest()->getParam('area_id');
        $note            = $this->getRequest()->getParam('note');
        $sname           = $this->getRequest()->getParam('sname', 0);
        $code_tmp            = $this->getRequest()->getParam('code');
        $s_assign        = $this->getRequest()->getParam('s_assign');
        $ood             = $this->getRequest()->getParam('ood');
        $email           = $this->getRequest()->getParam('email');
        $is_officer      = $this->getRequest()->getParam('is_officer');
        $date            = $this->getRequest()->getParam('date');
        $month           = $this->getRequest()->getParam('month');
        $year            = $this->getRequest()->getParam('year');
        $tags            = $this->getRequest()->getParam('tags');
        $title           = $this->getRequest()->getParam('title');
        $company_id      = $this->getRequest()->getParam('company_id');
        $need_approve    = $this->getRequest()->getParam('need_approve');
        $pc_stand_by     = $this->getRequest()->getParam('pc_stand_by');
	$group_id     = $this->getRequest()->getParam('group_id');

        $userStorage = Zend_Auth::getInstance()->getStorage()->read();

        if ($tags and is_array($tags))
            $tags = $tags;
        else
            $tags = null;

        //check if export
        $export = $this->getRequest()->getParam('export', 0);

        if (!$sname)
            $limit = LIMITATION;
        else
            $limit = null;

        $total = 0;
if($code_tmp){$code = explode("\r\n", $code_tmp);} 
        $params = array_filter(array(
            'name' => $name,
            'department' => $department,
            'off' => $off,
            'team' => $team,
            'regional_market' => $regional_market,
            'district' => $district,
            'area_id' => $area_id,
            'note' => $note,
            'code' => $code,
            's_assign' => $s_assign,
            'ood' => $ood,
            'email' => $email,
            's_assign' => $s_assign,
            'ood' => $ood,
            'email' => $email,
            'date' => $date,
            'month' => $month,
            'year' => $year,
            'is_officer' => $is_officer,
            'sname' => $sname,
            'tags' => $tags,
            'title' => $title,
            'company_id' => $company_id,
            'export' => $export,
            'need_approve' => $need_approve,
            'pc_stand_by' => $pc_stand_by,
	    'group_id' => $group_id,
            ));

        $params['sort'] = $sort;
        $params['desc'] = $desc;

        if ( $userStorage->group_id == PCM_ID ) { $params['pcm_id'] = $userStorage->id; } 
        if ( $userStorage->group_id == TRAINING_TEAM_ID ) { $params['training_id'] = $userStorage->id; } 

        $QArea = new Application_Model_Area();
        $this->view->areas = $QArea->fetchAll(null, 'name');

        $QRegionalMarket = new Application_Model_RegionalMarket();

        if ($area_id)
        {
            if (is_array($area_id) && count($area_id))
                $where = $QRegionalMarket->getAdapter()->quoteInto('area_id IN (?)', $area_id);
            else
                $where = $QRegionalMarket->getAdapter()->quoteInto('area_id = ?', $area_id);

            $this->view->regional_markets = $QRegionalMarket->fetchAll($where, 'name');
        }

        if ($regional_market)
        {
            if (is_array($regional_market) && count($regional_market))
                $where = $QRegionalMarket->getAdapter()->quoteInto('parent IN (?)', $regional_market);
            else
                $where = $QRegionalMarket->getAdapter()->quoteInto('parent = ?', $regional_market);

            $this->view->districts = $QRegionalMarket->fetchAll($where, 'name');
        }

        $QStaff = new Application_Model_Staff();
        $StaffCount = $QStaff->checkrequest($params);

        if ($export == 1)
        {
            $staffs = $QStaff->fetchPagination($page, null, $total, $params);
            $this->_exportExcel($staffs);
        }

        //reuqest checkrequest from model staff.php
                $params['get_total_count'] == 0;
        $QStaff = new Application_Model_Staff();
        $totalstaffbyposition = $QStaff->checkrequest($params);
        
        $this->view->totalstaffbyposition = $totalstaffbyposition;

	$QGroup = new Application_Model_Group();
        $this->view->groups = $QGroup->fetchAll();

        $staffs = $QStaff->fetchPagination($page, $limit, $total, $params);

        $QTeam = new Application_Model_Team();
        $recursiveDeparmentTeamTitle = $QTeam->get_recursive_cache();

        $this->view->recursiveDeparmentTeamTitle = $recursiveDeparmentTeamTitle;

        $QCompany = new Application_Model_Company();
        $this->view->companies = $QCompany->get_cache();


        $this->view->userStorage = $userStorage;

        $this->view->desc = $desc;
        $this->view->sort = $sort;
        $this->view->staffs = $staffs;
        $this->view->params = $params;
        $this->view->limit = $limit;
        $this->view->total = $total;
        $this->view->url = HOST . 'staff/' . ($params ? '?' . http_build_query($params) .
            '&' : '?');

        $this->view->offset = $limit * ($page - 1);

        $flashMessenger = $this->_helper->flashMessenger;
        $messages = $flashMessenger->setNamespace('success')->getMessages();
        $this->view->messages_success = $messages;

        $messages = $flashMessenger->setNamespace('error')->getMessages();
        $this->view->messages = $messages;

        $this->view->current_url = trim($this->getRequest()->getRequestUri(), '/');

        if ($this->getRequest()->isXmlHttpRequest())
        {
            $this->_helper->layout->disableLayout();

            if ($sname)
            {
                $QRegion = new Application_Model_RegionalMarket();
                $regional_markets = $QRegion->fetchAll();

                $rm = array();
                foreach ($regional_markets as $key => $value)
                {
                    $rm[$value['id']] = $value['name'];
                }
                $this->view->rm_list = $rm;

                $this->_helper->viewRenderer->setRender('partials/searchname');
            } elseif ($s_assign)
            {
                $this->_helper->viewRenderer->setRender('partials/staff');
            } else
                $this->_helper->viewRenderer->setRender('partials/list');
        }
    }

    public function quickEditAction()
    {
        $back_url = $this->getRequest()->getParam('back_url');
        $this->view->back_url = $back_url;
        $id = $this->getRequest()->getParam('id');

        $QArea = new Application_Model_Area();
        $this->view->areas = $QArea->fetchAll();

        if ($id)
        {
            $QStaff = new Application_Model_Staff();
            $staffRowset = $QStaff->find($id);
            $staff = $staffRowset->current();

            $this->view->staff = $staff;

            //get area & province
            $QRegionalMarket = new Application_Model_RegionalMarket();
            $rowset = $QRegionalMarket->find($staff->regional_market);

            if ($rowset)
            {
                $this->view->regional_market = $regional_market = $rowset->current();
                $where = $QRegionalMarket->getAdapter()->quoteInto('area_id = ?', $regional_market['area_id']);

                $this->view->regional_markets = $QRegionalMarket->fetchAll($where);

                $rowset = $QArea->find($regional_market['area_id']);
                $this->view->area = $rowset->current();
            }
        }

        $QGroup = new Application_Model_Group();
        $this->view->groups = $QGroup->fetchAll();

        $QModel = new Application_Model_ContractType();
        $this->view->contract_types = $QModel->fetchAll();

        $QModel = new Application_Model_ContractTerm();
        $this->view->contract_terms = $QModel->fetchAll();

        $QModel = new Application_Model_Department();
        $this->view->departments = $QModel->fetchAll();

        $QModel = new Application_Model_Team();
        $this->view->teams = $QModel->fetchAll();

        $QModel = new Application_Model_Religion();
        $this->view->religions = $QModel->fetchAll();

        $QModel = new Application_Model_Nationality();
        $this->view->nationalities = $QModel->fetchAll();

        $flashMessenger = $this->_helper->flashMessenger;
        $messages = $flashMessenger->setNamespace('error')->getMessages();
        $this->view->messages = $messages;

        $messages_success = $flashMessenger->setNamespace('success')->getMessages();
        $this->view->messages_success = $messages_success;
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setRender('quick_edit');
        //back url

    }

    public function listBasicAction()
    {
        require_once 'staff' . DIRECTORY_SEPARATOR . 'list-basic.php';
    }

    public function listBasicRecordAction()
    {
        require_once 'staff' . DIRECTORY_SEPARATOR . 'list-basic-record.php';
    }

    public function createBasicAction()
    {
        require_once 'staff' . DIRECTORY_SEPARATOR . 'create-basic.php';
    }

    public function saveBasicAction()
    {
        require_once 'staff' . DIRECTORY_SEPARATOR . 'save-basic.php';
    }

    public function approveBasicAction()
    {
        require_once 'staff' . DIRECTORY_SEPARATOR . 'approve-basic.php';
    }

    public function viewBasicAction()
    {
        require_once 'staff' . DIRECTORY_SEPARATOR . 'view-basic.php';
    }

    public function createAction()
    {
        // $this->_helper->viewRenderer->setNoRender(true);
        // echo "<br/><div align='center'><img alt='under_construction' src='/img/under-construction-sign.png'></div><br/>";

        //edit staff
        $id = $this->getRequest()->getParam('id');

        $QArea = new Application_Model_Area();
        $this->view->areas = $QArea->get_cache();

        $QRegionalMarket = new Application_Model_RegionalMarket();
        $this->view->all_province_cache = $QRegionalMarket->get_cache();

        if ($id)
        {
            $QStaff = new Application_Model_Staff();
            $staffRowset = $QStaff->find($id);
            $staff = $staffRowset->current();

            $this->view->staff = $staff;

            //kiem tra xem no co bi lock k

            $QTimeStaffExpired = new Application_Model_TimeStaffExpired();
            $where = array();
            $where[] = $QTimeStaffExpired->getAdapter()->quoteInto('staff_id = ?' , $staff['id'] );
            $where[] = $QTimeStaffExpired->getAdapter()->quoteInto('approved_at is null' , null);
            $result = $QTimeStaffExpired->fetchRow($where);

            if($result) {

                $this->view->lock = 1;
            }

            //get area & province
            $rowset = $QRegionalMarket->find($staff->regional_market);

            if ($rowset)
            {
                $this->view->regional_market = $regional_market = $rowset->current();
                $where = $QRegionalMarket->getAdapter()->quoteInto('area_id = ?', $regional_market['area_id']);

                $this->view->regional_markets = $QRegionalMarket->fetchAll($where);

                $rowset = $QArea->find($regional_market['area_id']);
                $this->view->area = $rowset->current();
            }

            // get tags
            $QTag = new Application_Model_Tag();
            $QTagObject = new Application_Model_TagObject();

            $where = array();
            $where[] = $QTagObject->getAdapter()->quoteInto('object_id = ?', $id);
            $where[] = $QTagObject->getAdapter()->quoteInto('type = ?', TAG_STAFF);

            $a_tags = array();

            $tags_object = $QTagObject->fetchAll($where);
            if ($tags_object)
                foreach ($tags_object as $to)
                {
                    $where = $QTag->getAdapter()->quoteInto('id = ?', $to['tag_id']);
                    $tag = $QTag->fetchRow($where);
                    if ($tag)
                        $a_tags[] = $tag['name'];
                }

            $this->view->a_tags = $a_tags;

            // get addresses
            $QStaffAddress = new Application_Model_StaffAddress();
            $district_cache = $QRegionalMarket->get_district_cache();
            $this->view->district_cache = $district_cache;

            // ------------------------- get permanent address
            $where = array();
            $where[] = $QStaffAddress->getAdapter()->quoteInto('staff_id = ?', $id);
            $where[] = $QStaffAddress->getAdapter()->quoteInto('address_type = ?',
                My_Staff_Address::Permanent);
            $permanent_address = $QStaffAddress->fetchRow($where);
            $this->view->permanent_address = $permanent_address;

            if (isset($permanent_address['district']) && isset($district_cache[$permanent_address['district']]))
                $this->view->permanent_address_districts = $QRegionalMarket->
                    get_district_by_province_cache($district_cache[$permanent_address['district']]['parent']);
            // ------------------------- get temporary address
            $where = array();
            $where[] = $QStaffAddress->getAdapter()->quoteInto('staff_id = ?', $id);
            $where[] = $QStaffAddress->getAdapter()->quoteInto('address_type = ?',
                My_Staff_Address::Temporary);
            $temporary_address = $QStaffAddress->fetchRow($where);
            $this->view->temporary_address = $temporary_address;

            if (isset($temporary_address['district']) && isset($district_cache[$temporary_address['district']]))
                $this->view->temporary_address_districts = $QRegionalMarket->
                    get_district_by_province_cache($district_cache[$temporary_address['district']]['parent']);

            // ------------------------- get birth certificate address
            $where = array();
            $where[] = $QStaffAddress->getAdapter()->quoteInto('staff_id = ?', $id);
            $where[] = $QStaffAddress->getAdapter()->quoteInto('address_type = ?',
                My_Staff_Address::Birth_Certificate);
            $birth_address = $QStaffAddress->fetchRow($where);
            $this->view->birth_address = $birth_address;

            if (isset($birth_address['district']) && isset($district_cache[$birth_address['district']]))
                $this->view->birth_address_districts = $QRegionalMarket->
                    get_district_by_province_cache($district_cache[$birth_address['district']]['parent']);

            // ------------------------- get ID card address
            $where = array();
            $where[] = $QStaffAddress->getAdapter()->quoteInto('staff_id = ?', $id);
            $where[] = $QStaffAddress->getAdapter()->quoteInto('address_type = ?',
                My_Staff_Address::ID_Card);
            $id_card_address = $QStaffAddress->fetchRow($where);
            $this->view->id_card_address = $id_card_address;

            if (isset($id_card_address['district']) && isset($district_cache[$id_card_address['district']]))
                $this->view->id_card_address_districts = $QRegionalMarket->
                    get_district_by_province_cache($district_cache[$id_card_address['district']]['parent']);

            // ------------------------- Get other logs
            $QStaffEducation = new Application_Model_StaffEducation();
            $where = $QStaffEducation->getAdapter()->quoteInto('staff_id = ?', $id);
            $this->view->education = $QStaffEducation->fetchAll($where);

            $QStaffExperience = new Application_Model_StaffExperience();
            $where = $QStaffExperience->getAdapter()->quoteInto('staff_id = ?', $id);
            $this->view->experience = $QStaffExperience->fetchAll($where);

            $QStaffRelative = new Application_Model_StaffRelative();
            $where = $QStaffRelative->getAdapter()->quoteInto('staff_id = ?', $id);
            $this->view->relative = $QStaffRelative->fetchAll($where);
            // ------------------------- Get transfer logs
            $QStaffLogDetail = new Application_Model_StaffLogDetail();
            $where = $QStaffLogDetail->getAdapter()->quoteInto('object = ?', $id);
            $this->view->log_detail = $QStaffLogDetail->fetchAll($where, 'from_date DESC');

            // ------------------------- Get change log - need approval
            // $wf = new Application_Model_Workflow();
            // $this->view->change_log = $wf->get('staff', array($id));
            // get Transfer


            $this->view->staff_id = $id;
        }

        $QCompany = new Application_Model_Company();
        $this->view->companies = $QCompany->get_cache();

        $QGroup = new Application_Model_Group();
        $this->view->groups = $QGroup->get_cache();

        $QModel = new Application_Model_ContractType();
        $this->view->contract_types = $QModel->fetchAll();

        $QModel = new Application_Model_ContractTerm();
        $this->view->contract_terms = $QModel->fetchAll();

        $QModel = new Application_Model_Department();
        $this->view->departments = $QModel->fetchAll();

        //get teams
        $QTeam = new Application_Model_Team();
        $recursiveDeparmentTeamTitle = $QTeam->get_recursive_cache();

        $this->view->recursiveDeparmentTeamTitle = $recursiveDeparmentTeamTitle;
        $this->view->teamsCached = $QTeam->get_cache();

        $QModel = new Application_Model_Religion();
        $this->view->religions = $QModel->fetchAll();

        $QModel = new Application_Model_Nationality();
        $this->view->nationalities = $QModel->fetchAll();

        $flashMessenger = $this->_helper->flashMessenger;
        $messages = $flashMessenger->setNamespace('error')->getMessages();
        $this->view->messages = $messages;

        $messages_success = $flashMessenger->setNamespace('success')->getMessages();
        $this->view->messages_success = $messages_success;

        $back_url = $this->getRequest()->getParam('back_url');
        //back url
        $this->view->back_url = $back_url ? $back_url : ($this->getRequest()->getServer
            ('HTTP_REFERER') ? $this->getRequest()->getServer('HTTP_REFERER') : '/staff');
    }

    public function saveAction()
    {
        require_once 'staff' . DIRECTORY_SEPARATOR . 'save.php';
    }

    /*
     * In phu luc hop dong
     * */
    public function printAppendixAction()
    {
        require_once 'staff' . DIRECTORY_SEPARATOR . 'print-appendix.php';
    }

    public function jobTitleAction()
    {
        $QTeam = new Application_Model_Team();
        $whereDepartment[] = $QTeam->getAdapter()->quoteInto('parent_id = ?', 0);
        $whereDepartment[] = $QTeam->getAdapter()->quoteInto('del = ? OR del IS NULL', 0);
        $listDepartment = $QTeam->fetchAll($whereDepartment);
        $this->view->listDepartment = $listDepartment;

        $userStorage = Zend_Auth::getInstance()->getStorage()->read();
        $QLog = new Application_Model_Log();
        $ip = $this->getRequest()->getServer('REMOTE_ADDR');
        $flashMessenger = $this->_helper->flashMessenger;
        $messages = $flashMessenger->setNamespace('success')->getMessages();
        $this->view->message_success = $messages;
        $messages_error = $flashMessenger->setNamespace('error')->getMessages();
        $this->view->messages = $messages_error;

        if ($this->getRequest()->getMethod() == 'POST')
        {
            // Department
            $db = Zend_Registry::get('db');
            $db->beginTransaction();

            try
            {
                $idDepartment = $this->getRequest()->getParam('idDepartment');
                $idTeam = $this->getRequest()->getParam('idTeam');
                $idJobTitle = $this->getRequest()->getParam('idJobTitle');
                $addDepartment = $this->getRequest()->getParam('addDepartment');
                $editDepartment = $this->getRequest()->getParam('editDepartment');
                $delDepartment = $this->getRequest()->getParam('delDepartment');
                $addTeam = $this->getRequest()->getParam('addTeam');
                $editTeam = $this->getRequest()->getParam('editTeam');
                $deleteTeam = $this->getRequest()->getParam('deleteTeam');
                $addJobTitle = $this->getRequest()->getParam('addJobTitle');
                $addJobTitleVn = $this->getRequest()->getParam('addJobTitleVn');
                $editJobTitle = $this->getRequest()->getParam('editJobTitle');
                $editJobTitleVn = $this->getRequest()->getParam('editJobTitleVn');
                $delJobTitle = $this->getRequest()->getParam('delJobTitle');

                if ($addDepartment)
                {
                    // add
                    $dataAdd = array('name' => $addDepartment, 'parent_id' => 0);
                    $QTeam->insert($dataAdd);
                    //to do log
                    $info = array('Add Department', 'new' => $dataAdd);
                    $QLog->insert(array(
                        'info' => json_encode($info),
                        'user_id' => $userStorage->id,
                        'ip_address' => $ip,
                        'time' => date('Y-m-d H:i:s'),
                        ));

                }
                if ($editDepartment)
                {
                    // edit
                    $whereUpdateDepartment = $QTeam->getAdapter()->quoteInto('id = ?', $idDepartment);
                    $rowDepartment = $QTeam->fetchRow($whereUpdateDepartment);
                    if ($rowDepartment)
                    {
                        $dataUpdate = array('name' => $editDepartment);
                        $QTeam->update($dataUpdate, $whereUpdateDepartment);
                        //to do log
                        $info = array(
                            'Update Department' => $idDepartment,
                            'new' => $dataUpdate,
                            'old' => $rowDepartment);
                        $QLog->insert(array(
                            'info' => json_encode($info),
                            'user_id' => $userStorage->id,
                            'ip_address' => $ip,
                            'time' => date('Y-m-d H:i:s'),
                            ));
                    }

                }
                if ($delDepartment)
                {
                    // del
                    $whereDelDepartment = $QTeam->getAdapter()->quoteInto('id = ?', $delDepartment);
                    $rowDepartment = $QTeam->fetchRow($whereDelDepartment);
                    if ($rowDepartment)
                    {
                        $dataDel = array('del' => 1);
                        $QTeam->update($dataDel, $whereDelDepartment);
                        //to do log
                        $info = array(
                            'Del Department' => $idDepartment,
                            'new' => $dataDel,
                            'old' => $rowDepartment);
                        $QLog->insert(array(
                            'info' => json_encode($info),
                            'user_id' => $userStorage->id,
                            'ip_address' => $ip,
                            'time' => date('Y-m-d H:i:s'),
                            ));
                    }
                }
                if ($addTeam)
                {
                    if (!$idDepartment)
                    {
                        throw new Exception('NOT CHOOSE DEPARTMENT');
                    }

                    $dataAddTeam = array(
                        'name' => $addTeam,
                        'parent_id' => $idDepartment,
                        'del' => 0);

                    $QTeam->insert($dataAddTeam);

                    // to do log
                    $info = array('Add Team', 'new' => $dataAddTeam);
                    $QLog->insert(array(
                        'info' => json_encode($info),
                        'user_id' => $userStorage->id,
                        'ip_address' => $ip,
                        'time' => date('Y-m-d H:i:s'),
                        ));
                }
                if ($editTeam)
                {
                    if (!$idTeam)
                    {
                        throw new Exception('Team is Not True');
                    }
                    $whereTeam = array();
                    $whereTeam[] = $QTeam->getAdapter()->quoteInto('id = ?', $idTeam);
                    $whereTeam[] = $QTeam->getAdapter()->quoteInto('del <> ?', 1);
                    $rowTeam = $QTeam->fetchRow($whereTeam);

                    if ($rowTeam)
                    {
                        $dataEditTeam = array('name' => $editTeam);
                        $QTeam->update($dataEditTeam, $whereTeam);
                        // to do log
                        $info = array(
                            'Edit Team',
                            'new' => $dataEditTeam,
                            'old' => $rowTeam);
                        $QLog->insert(array(
                            'info' => json_encode($info),
                            'user_id' => $userStorage->id,
                            'ip_address' => $ip,
                            'time' => date('Y-m-d H:i:s'),
                            ));
                    } else
                    {
                        throw new Exception('Team Is Not True');
                    }

                }
                if ($deleteTeam)
                {
                    // delete
                    $whereDeleteTeam = $QTeam->getAdapter()->quoteInto('id = ?', $deleteTeam);
                    $rowDeleteTeam = $QTeam->fetchRow($whereDeleteTeam);
                    if ($rowDeleteTeam)
                    {
                        $dataDel = array('del' => 1);
                        $QTeam->update($dataDel, $whereDeleteTeam);

                        // to do log
                        $info = array(
                            'Del Team',
                            'new' => $dataDel,
                            'old' => $rowDeleteTeam);
                        $QLog->insert(array(
                            'info' => json_encode($info),
                            'user_id' => $userStorage->id,
                            'ip_address' => $ip,
                            'time' => date('Y-m-d H:i:s'),
                            ));

                    } else
                    {
                        throw new Exception('Not Team');
                    }

                }
                if ($addJobTitle)
                {
                    if (!$idTeam)
                    {
                        throw new Exception('NOT CHOOSE TEAM');
                    }

                    $dataAddJobTitle = array(
                        'name' => $addJobTitle,
                        'name_vn' => $addJobTitleVn,
                        'parent_id' => $idTeam,
                        'del' => 0);

                    $QTeam->insert($dataAddJobTitle);

                    // to do log
                    $info = array('Add JobTile', 'new' => $dataAddJobTitle);
                    $QLog->insert(array(
                        'info' => json_encode($info),
                        'user_id' => $userStorage->id,
                        'ip_address' => $ip,
                        'time' => date('Y-m-d H:i:s'),
                        ));
                }
                if ($editJobTitle)
                {
                    if (!$idJobTitle)
                    {
                        throw new Exception('JobTitle is Not True');
                    }
                    $whereJobTitle = array();
                    $whereJobTitle[] = $QTeam->getAdapter()->quoteInto('id = ?', $idJobTitle);
                    $whereJobTitle[] = $QTeam->getAdapter()->quoteInto('del <> ?', 1);
                    $rowJobTitle = $QTeam->fetchRow($whereJobTitle);

                    if ($rowJobTitle)
                    {
                        $dataEditJobTitle = array(
                            'name' => $editJobTitle,
                            'name_vn' => $editJobTitleVn,
                            );
                        $QTeam->update($dataEditJobTitle, $whereJobTitle);
                        // to do log
                        $info = array(
                            'Edit Team',
                            'new' => $dataEditJobTitle,
                            'old' => $rowJobTitle);
                        $QLog->insert(array(
                            'info' => json_encode($info),
                            'user_id' => $userStorage->id,
                            'ip_address' => $ip,
                            'time' => date('Y-m-d H:i:s'),
                            ));
                    } else
                    {
                        throw new Exception('Team Is Not True');
                    }

                }
                if ($delJobTitle)
                {
                    // delete
                    $whereDeleteJobTitle = $QTeam->getAdapter()->quoteInto('id = ?', $delJobTitle);
                    $rowDeleteJobTitle = $QTeam->fetchRow($whereDeleteJobTitle);
                    if ($rowDeleteJobTitle)
                    {
                        $dataDel = array('del' => 1);
                        $QTeam->update($dataDel, $whereDeleteJobTitle);

                        // to do log
                        $info = array(
                            'Del Job Title',
                            'new' => $dataDel,
                            'old' => $rowDeleteJobTitle);
                        $QLog->insert(array(
                            'info' => json_encode($info),
                            'user_id' => $userStorage->id,
                            'ip_address' => $ip,
                            'time' => date('Y-m-d H:i:s'),
                            ));

                    } else
                    {
                        throw new Exception('Not JobTitle');
                    }

                }
                $db->commit();

                $flashMessenger->setNamespace('success')->addMessage('Done!');
                $this->_redirect(HOST . 'staff/job-title');
            }
            catch (exception $e)
            {
                $db->rollback();
                $flashMessenger->setNamespace('error')->addMessage($e->getMessage());
                $this->_redirect(HOST . 'staff/job-title');
            }

        }

    }

    public function getTeamAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        if ($this->getRequest()->getMethod() == 'POST')
        {

            $departmentID = $this->getRequest()->getParam('department_id');
            $QTeam = new Application_Model_Team();
            $whereTeam = array();
            $whereTeam[] = $QTeam->getAdapter()->quoteInto('parent_id = ?', $departmentID);
            $whereTeam[] = $QTeam->getAdapter()->quoteInto('del = ? OR del IS NULL', 0);
            $Teams = $QTeam->fetchAll($whereTeam);
            if ($Teams)
            {
                echo json_encode($Teams->toArray());
            }

        }
    }

    public function getJobTitleAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        if ($this->getRequest()->getMethod() == 'POST')
        {

            $teamID = $this->getRequest()->getParam('team_id');
            $QTeam = new Application_Model_Team();
            $whereJobTile = array();
            $whereJobTile[] = $QTeam->getAdapter()->quoteInto('parent_id = ?', $teamID);
            $whereJobTile[] = $QTeam->getAdapter()->quoteInto('del = ? OR del IS NULL', 0);
            $jobTitle = $QTeam->fetchAll($whereJobTile);
            if ($jobTitle)
            {
                echo json_encode($jobTitle->toArray());
            }

        }
    }

    public function delAction()
    {
        $id = $this->getRequest()->getParam('id');

        $staff = new Application_Model_Staff();
        $where = $staff->getAdapter()->quoteInto('id = ?', $id);
        $staff->delete($where);

        $userStorage = Zend_Auth::getInstance()->getStorage()->read();
        $QLog = new Application_Model_Log();
        $ip = $this->getRequest()->getServer('REMOTE_ADDR');
        $info = "STAFF - Delete (" . $id . ")";
        //todo log
        $QLog->insert(array(
            'info' => $info,
            'user_id' => $userStorage->id,
            'ip_address' => $ip,
            'time' => date('Y-m-d H:i:s'),
            ));

        $back_url = $this->getRequest()->getServer('HTTP_REFERER');

        $this->_redirect(($back_url ? $back_url : HOST . 'staff'));
    }

    public function disableAction()
    {
        $id = $this->getRequest()->getParam('id');

        $staff = new Application_Model_Staff();
        $staffRowset = $staff->find($id);
        $staff_result = $staffRowset->current();

        $where = $staff->getAdapter()->quoteInto('id = ?', $id);

        $data = array('status' => 0);
        $staff->update($data, $where);

        // Send to TMS
        $data['code'] = $staff_result['code'];
        foreach($data as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
        $data2 = rtrim($fields_string, '&');
/*
        $ch = curl_init();

        //curl_setopt($ch, CURLOPT_URL,"http://trade.oppo.in.th/trade/wsonoffstaff");
        curl_setopt($ch, CURLOPT_URL,"http://tmk.oppo.in.th/api/disable-staff-to-trade");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data2);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $server_output = curl_exec($ch);
        curl_close ($ch);
*/
        $userStorage = Zend_Auth::getInstance()->getStorage()->read();
        $QLog = new Application_Model_Log();
        $ip = $this->getRequest()->getServer('REMOTE_ADDR');
        $info = "STAFF - Disable (" . $id . ")";
        //todo log
        $QLog->insert(array(
            'info' => $info,
            'user_id' => $userStorage->id,
            'ip_address' => $ip,
            'time' => date('Y-m-d H:i:s'),
            ));

        $back_url = $this->getRequest()->getServer('HTTP_REFERER');

        $this->_redirect(($back_url ? $back_url : HOST . 'staff'));
    }

    public function enableAction()
    {
        $id = $this->getRequest()->getParam('id');

        $staff = new Application_Model_Staff();
        $where = $staff->getAdapter()->quoteInto('id = ?', $id);

        $data = array('status' => 1);
        $staff->update($data, $where);

        $userStorage = Zend_Auth::getInstance()->getStorage()->read();
        $QLog = new Application_Model_Log();
        $ip = $this->getRequest()->getServer('REMOTE_ADDR');
        $info = "STAFF - Enable (" . $id . ")";
        //todo log
        $QLog->insert(array(
            'info' => $info,
            'user_id' => $userStorage->id,
            'ip_address' => $ip,
            'time' => date('Y-m-d H:i:s'),
            ));

        $back_url = $this->getRequest()->getServer('HTTP_REFERER');

        $this->_redirect(($back_url ? $back_url : HOST . 'staff'));
    }

    public function disableBlukAction()
    {
        $list_id = $this->getRequest()->getParam('id');
        foreach ($list_id as $id) {
            if ($id) {
                $staff = new Application_Model_Staff();
                $staffRowset = $staff->find($id);
                $staff_result = $staffRowset->current();

                $where = $staff->getAdapter()->quoteInto('id = ?', $id);

                $data = array('status' => 0);
                $staff->update($data, $where);

                // Send to TMS
                $data['code'] = $staff_result['code'];
                foreach($data as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
                $data2 = rtrim($fields_string, '&');

                $userStorage = Zend_Auth::getInstance()->getStorage()->read();
                $QLog = new Application_Model_Log();
                $ip = $this->getRequest()->getServer('REMOTE_ADDR');
                $info = "STAFF - Disable (" . $id . ")";
                //todo log
                $QLog->insert(array(
                    'info' => $info,
                    'user_id' => $userStorage->id,
                    'ip_address' => $ip,
                    'time' => date('Y-m-d H:i:s'),
                    ));
            }
        }

                $back_url = $this->getRequest()->getServer('HTTP_REFERER');

                $this->_redirect(($back_url ? $back_url : HOST . 'staff'));
    }

    public function enableBlukAction()
    {
        $list_id = $this->getRequest()->getParam('id');
        foreach ($list_id as $id) {
            if ($id) {
                $staff = new Application_Model_Staff();
                $where = $staff->getAdapter()->quoteInto('id = ?', $id);

                $data = array('status' => 1);
                $staff->update($data, $where);

                $userStorage = Zend_Auth::getInstance()->getStorage()->read();
                $QLog = new Application_Model_Log();
                $ip = $this->getRequest()->getServer('REMOTE_ADDR');
                $info = "STAFF - Enable (" . $id . ")";
                //todo log
                $QLog->insert(array(
                    'info' => $info,
                    'user_id' => $userStorage->id,
                    'ip_address' => $ip,
                    'time' => date('Y-m-d H:i:s'),
                    ));
            }
        }

        $back_url = $this->getRequest()->getServer('HTTP_REFERER');

        $this->_redirect(($back_url ? $back_url : HOST . 'staff'));
    }

    public function searchAction()
    {
        $term = $this->getRequest()->getParam('term');
        $department = $this->getRequest()->getParam('department');
        $team = $this->getRequest()->getParam('team');
        $date = $this->getRequest()->getParams('date');
        $where = array();
        $QStaff = new Application_Model_Staff();
        $where[] = $QStaff->getAdapter()->quoteInto('CONCAT(firstname, " ",lastname) LIKE ?',
            '%' . $term . '%');
        if ($department)
            $where[] = $QStaff->getAdapter()->quoteInto('department = ?', $department);

        if ($team)
            $where[] = $QStaff->getAdapter()->quoteInto('team = ?', $team);
        if ($date)
            $where[] = $QStaff->getAdapter()->quoteInto('date = ?', $date);
        $staffs = $QStaff->fetchAll($where);

        $data = array();

        if ($staffs)
            foreach ($staffs as $staff)
            {
                $data[] = array(
                    'id' => $staff->id,
                    'value' => $staff->firstname . ' ' . $staff->lastname,
                    );
            }

        echo json_encode($data);
        exit;
    }

    private function _exportExcel($data)
    {
        $db = Zend_Registry::get('db');
        set_time_limit(0);
        error_reporting(0);
        ini_set('memory_limit', -1);
        require_once 'PHPExcel.php';
        $cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp;
        $cacheSettings = array('memoryCacheSize' => '128MB');
        PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);
        $PHPExcel = new PHPExcel();
        $heads = array(
	    'ID',
            'Code',
            'First name',
            'Last name',
            // 'First name [EN]',
            // 'Last name [EN]',
            // 'Company',
            //'Department',
            'Team',
            'Title',
            'Group',
            'Area',
            'Province',
            'Is Officer',
            // 'Contract type',
            // 'Contract signed at',
            // 'Contract term',
            // 'Contract expired at',
            'Joined at',
            'Off date',
            'Off type',
            'Gender',
            'Date of birth',
            // 'Level',
            // 'Certificate',
            // 'Temporary Address',
            // 'Permanent address',
            // 'Birth place',
            // 'ID Card Address',
            // 'ID Card Address Street',
            // 'ID Card Address Ward',
            // 'ID Card Address District',
            // 'ID Card Address Province',
            // 'Temporary Address',
            // 'Temporary Address Street',
            // 'Temporary Address Ward',
            // 'Temporary Address District',
            // 'Temporary Address Province',
            // 'Permanent address',
            // 'Permanent address Street',
            // 'Permanent address Ward',
            // 'Permanent address District',
            // 'Permanent address Province',
            // 'Birth place Street',
            // 'Birth place Ward',
            // 'Birth place District',
            // 'Birth place Province',
            // 'ID number',
            // 'ID place',
            // 'ID date',
            // 'Social insurance number',
            // 'Social insurance time',
            // 'Personal tax',
            // 'Family allowances registered',
            // 'Nationality',
            // 'Religion',
            'Phone number',
            'Email',
            'Status',
            //'Note',
            //'Tags',
            //'Additional Info',
            'Shirt Size',
            'PC Stand By'
            );

        $PHPExcel->setActiveSheetIndex(0);
        $sheet = $PHPExcel->getActiveSheet();

        $alpha = 'A';
        $index = 1;
        foreach ($heads as $key)
        {
            $sheet->setCellValue($alpha . $index, $key);
            $alpha++;
        }
        $index = 2;

        //get department
        $QDepartment = new Application_Model_Department();
        $departments = $QDepartment->get_cache();

        //get teams
        $QTeam = new Application_Model_Team();
        $teams = $QTeam->get_cache();

        //get titles
        /*$QTitle = new Application_Model_Title();
        $titles = $QTitle->get_cache();*/

        $QArea = new Application_Model_Area();
        $areas = $QArea->get_cache();

        //get regional markets
        $QRegionalMarket = new Application_Model_RegionalMarket();
        $regional_markets = $QRegionalMarket->get_cache_all();

        //get contract type
        $QContractType = new Application_Model_ContractType();
        $contract_types = $QContractType->get_cache();

        //get contract term
        $QContractTerm = new Application_Model_ContractTerm();
        $contract_terms = $QContractTerm->get_cache();

        //get contract term
        $QNationality = new Application_Model_Nationality();
        $nationalities = $QNationality->get_cache();

        //get contract term
        $QReligion = new Application_Model_Religion();
        $religions = $QReligion->get_cache();

        $QCompany = new Application_Model_Company();
        $companies = $QCompany->get_cache();

        $QTag = new Application_Model_Tag();
        $QTagObject = new Application_Model_TagObject();

        $QGroup = new Application_Model_Group();
        $group_list = $QGroup->get_cache();

        foreach ($data as $item) {

            $alpha = 'A';
	    $sheet->setCellValue($alpha++ . $index, $item['id']);
            $sheet->getCell($alpha++ . $index)->setValueExplicit($item['code'],
                PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue($alpha++ . $index, $item['firstname']);
            $sheet->setCellValue($alpha++ . $index, $item['lastname']);
            //$sheet->setCellValue($alpha++ . $index, $item['firstname_en']);
            //$sheet->setCellValue($alpha++ . $index, $item['lastname_en']);
            //$sheet->setCellValue($alpha++ . $index, isset($companies[$item['company_id']]) ? $companies[$item['company_id']] : '');
            //$sheet->setCellValue($alpha++ . $index, isset($teams[$item['department']]) ? $teams[$item['department']] : '');
            $sheet->setCellValue($alpha++ . $index, isset($teams[$item['team']]) ? $teams[$item['team']] : '');
            $sheet->setCellValue($alpha++ . $index, isset($teams[$item['title']]) ? $teams[$item['title']] : '');

            // Add Group
            $sheet->setCellValue($alpha++ . $index, isset($group_list[$item['group_id']]) ? $group_list[$item['group_id']] : '');

            $sheet->setCellValue($alpha++ . $index, isset($areas[$regional_markets[$item['regional_market']]['area_id']]) ?
                $areas[$regional_markets[$item['regional_market']]['area_id']] : '');
            $sheet->setCellValue($alpha++ . $index, isset($regional_markets[$item['regional_market']]['name']) ?
                $regional_markets[$item['regional_market']]['name'] : '');
            $sheet->setCellValue($alpha++ . $index, $item['is_officer'] ? 'X' : '');
            //$sheet->getCell($alpha++ . $index)->setValueExplicit((isset($contract_types[$item['contract_type']]) ?
                //$contract_types[$item['contract_type']] : ''), PHPExcel_Cell_DataType::
                //TYPE_STRING);
            //$sheet->setCellValue($alpha++ . $index, ($item['contract_signed_at'] ? date('d/m/Y',
            //     strtotime($item['contract_signed_at'])) : ''));
            // $sheet->setCellValue($alpha++ . $index, isset($contract_terms[$item['contract_term']]) ?
            //     $contract_terms[$item['contract_term']] : '');
            // $sheet->setCellValue($alpha++ . $index, ($item['contract_expired_at'] ? date('d/m/Y',
            //     strtotime($item['contract_expired_at'])) : ''));
            $sheet->setCellValue($alpha++ . $index, ($item['joined_at'] ? date('d/m/Y',
                strtotime($item['joined_at'])) : ''));
            $sheet->setCellValue($alpha++ . $index, ($item['off_date'] ? date('d/m/Y',
                strtotime($item['off_date'])) : ''));
            $sheet->setCellValue($alpha++ . $index, isset(My_Staff_Status_Off::$name[$item['off_type']]) ?
                My_Staff_Status_Off::$name[$item['off_type']] : '');
            $sheet->setCellValue($alpha++ . $index, ($item['gender'] == 1 ? 'Male' : 'Female'));
            $sheet->setCellValue($alpha++ . $index, $item['dob']);
            // $sheet->setCellValue($alpha++ . $index, $item['d_level']);
            // $sheet->setCellValue($alpha++ . $index, $item['d_certificate']);

            // $sheet->setCellValue($alpha++ . $index, $item['address']);
            // // SELECT ADDRESS
            // $select = $db->select()->from(array('s' => 'staff_address'), array(
            //     'type' => 's.address_type',
            //     'address' => 's.address',
            //     'ward' => 's.ward',
            //     'district' => 'r.name',
            //     'province' => 'r2.name'))->join(array('r' => 'regional_market'),
            //     'r.id = s.district', array())->join(array('r2' => 'regional_market'),
            //     'r.parent = r2.id', array())->where('s.staff_id = ?', $item['id'])->order('s.address_type ASC');
            // $arrAddress = $db->fetchAll($select);
            // $arrTmpAddress = array();
            // foreach ($arrAddress as $add):
            //     $arrTmpAddress[$add['type']] = $add;
            // endforeach;
            // unset($arrAddress);

            // if (isset($arrTmpAddress[My_Staff_Address::ID_Card]))
            // {
            //     $sheet->setCellValue($alpha++ . $index, $arrTmpAddress[My_Staff_Address::
            //         ID_Card]['address']);
            //     $sheet->setCellValue($alpha++ . $index, $arrTmpAddress[My_Staff_Address::
            //         ID_Card]['ward']);
            //     $sheet->setCellValue($alpha++ . $index, $arrTmpAddress[My_Staff_Address::
            //         ID_Card]['district']);
            //     $sheet->setCellValue($alpha++ . $index, $arrTmpAddress[My_Staff_Address::
            //         ID_Card]['province']);
            // } else
            // {
            //     $sheet->setCellValue($alpha++ . $index, '');
            //     $sheet->setCellValue($alpha++ . $index, '');
            //     $sheet->setCellValue($alpha++ . $index, '');
            //     $sheet->setCellValue($alpha++ . $index, '');
            // }

            // $sheet->setCellValue($alpha++ . $index, $item['temporary_address']);
            // if (isset($arrTmpAddress[My_Staff_Address::Temporary]))
            // {
            //     $sheet->setCellValue($alpha++ . $index, $arrTmpAddress[My_Staff_Address::
            //         Temporary]['address']);
            //     $sheet->setCellValue($alpha++ . $index, $arrTmpAddress[My_Staff_Address::
            //         Temporary]['ward']);
            //     $sheet->setCellValue($alpha++ . $index, $arrTmpAddress[My_Staff_Address::
            //         Temporary]['district']);
            //     $sheet->setCellValue($alpha++ . $index, $arrTmpAddress[My_Staff_Address::
            //         Temporary]['province']);
            // } else
            // {
            //     $sheet->setCellValue($alpha++ . $index, '');
            //     $sheet->setCellValue($alpha++ . $index, '');
            //     $sheet->setCellValue($alpha++ . $index, '');
            //     $sheet->setCellValue($alpha++ . $index, '');
            // }


            // $sheet->setCellValue($alpha++ . $index, $item['permanent_address']);
            // if (isset($arrTmpAddress[My_Staff_Address::Permanent]))
            // {
            //     $sheet->setCellValue($alpha++ . $index, $arrTmpAddress[My_Staff_Address::
            //         Permanent]['address']);
            //     $sheet->setCellValue($alpha++ . $index, $arrTmpAddress[My_Staff_Address::
            //         Permanent]['ward']);
            //     $sheet->setCellValue($alpha++ . $index, $arrTmpAddress[My_Staff_Address::
            //         Permanent]['district']);
            //     $sheet->setCellValue($alpha++ . $index, $arrTmpAddress[My_Staff_Address::
            //         Permanent]['province']);
            // } else
            // {
            //     $sheet->setCellValue($alpha++ . $index, '');
            //     $sheet->setCellValue($alpha++ . $index, '');
            //     $sheet->setCellValue($alpha++ . $index, '');
            //     $sheet->setCellValue($alpha++ . $index, '');
            // }

            // if (isset($arrTmpAddress[My_Staff_Address::Birth_Certificate]))
            // {
            //     $sheet->setCellValue($alpha++ . $index, $arrTmpAddress[My_Staff_Address::
            //         Birth_Certificate]['address']);
            //     $sheet->setCellValue($alpha++ . $index, $arrTmpAddress[My_Staff_Address::
            //         Birth_Certificate]['ward']);
            //     $sheet->setCellValue($alpha++ . $index, $arrTmpAddress[My_Staff_Address::
            //         Birth_Certificate]['district']);
            //     $sheet->setCellValue($alpha++ . $index, $arrTmpAddress[My_Staff_Address::
            //         Birth_Certificate]['province']);
            // } else
            // {
            //     $sheet->setCellValue($alpha++ . $index, '');
            //     $sheet->setCellValue($alpha++ . $index, '');
            //     $sheet->setCellValue($alpha++ . $index, '');
            //     $sheet->setCellValue($alpha++ . $index, '');
            // }

            // /*
            // $sheet->setCellValue($alpha++ . $index, $item['address']);
            // $sheet->setCellValue($alpha++ . $index, $item['temporary_address']);
            // $sheet->setCellValue($alpha++ . $index, $item['permanent_address']);
            // $sheet->setCellValue($alpha++ . $index, $item['birth_place']);
            // */
            // $sheet->getCell($alpha++ . $index)->setValueExplicit($item['ID_number'],
            //     PHPExcel_Cell_DataType::TYPE_STRING);
            // $sheet->setCellValue($alpha++ . $index, $item['ID_place']);
            // $sheet->setCellValue($alpha++ . $index, ($item['ID_date'] ? date('d/m/Y',
            //     strtotime($item['ID_date'])) : ''));
            // $sheet->getCell($alpha++ . $index)->setValueExplicit($item['social_insurance_number'],
            //     PHPExcel_Cell_DataType::TYPE_STRING);
            // $sheet->setCellValue($alpha++ . $index, $item['social_insurance_time']);
            // $sheet->setCellValue($alpha++ . $index, $item['personal_tax']);
            // $sheet->setCellValue($alpha++ . $index, $item['family_allowances_registered']);
            // $sheet->setCellValue($alpha++ . $index, isset($nationalities[$item['nationality']]) ?
            //     $nationalities[$item['nationality']] : '');
            // $sheet->setCellValue($alpha++ . $index, isset($religions[$item['religion']]) ? $religions[$item['religion']] :
            //    '');
            $sheet->getCell($alpha++ . $index)->setValueExplicit($item['phone_number'],
                PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue($alpha++ . $index, $item['email']);
            $sheet->setCellValue($alpha++ . $index, My_Staff_Status::get($item['status']));
            // $sheet->setCellValue($alpha++ . $index, $item['note']);

            // // get tags
            // $where = array();
            // $where[] = $QTagObject->getAdapter()->quoteInto('type = ?', TAG_STAFF);
            // $where[] = $QTagObject->getAdapter()->quoteInto('object_id = ?', $item['id']);

            // $tags = $QTagObject->fetchAll($where);
            // $tags_arr = array();

            // foreach ($tags as $_key => $_value)
            //     $tags_arr[] = $_value['tag_id'];

            // if (is_array($tags_arr) && count($tags_arr))
            //     $where = $QTag->getAdapter()->quoteInto('id IN (?)', $tags_arr);
            // else
            //     $where = $QTag->getAdapter()->quoteInto('1=0', 1);

            // $tags = $QTag->fetchAll($where);
            // $tags_str = '';

            // foreach ($tags as $_key => $_value)
            //     $tags_str .= $_value['name'];

            // $sheet->setCellValue($alpha++ . $index, $tags_str);

            // $sheet->setCellValue($alpha++ . $index, $item['additional_info']);

            // add shirt size
            switch ($item['shirt_size']) {
                case 1  : $shirt_size = 'XXS';  break;
                case 2  : $shirt_size = 'SS';   break;
                case 3  : $shirt_size = 'S';    break;
                case 4  : $shirt_size = 'M';    break;
                case 5  : $shirt_size = 'L';    break;
                case 6  : $shirt_size = 'XL';   break;
                case 7  : $shirt_size = '2XL';  break;
                case 8  : $shirt_size = '3XL';  break;
                case 9  : $shirt_size = '5XL';  break;
                default : $shirt_size = '-';    break; 
            }

            $sheet->setCellValue($alpha++ . $index, $shirt_size);

            if ( $item['pc_stand_by'] == 1 ) { $pc_stand_by = "Yes"; }
            else { $pc_stand_by = "No"; }

            $sheet->setCellValue($alpha++ . $index, $pc_stand_by);

            $index++;
        }

        $filename = 'Staffs_' . date('d/m/Y');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');

        $objWriter->save('php://output');

        exit;
    }

    private function _formatDate($date)
    {
        if (!$date)
            return null;

        $date = trim($date);

        $temp = explode('/', $date);
        $formatedDate = (isset($temp[2]) ? $temp[2] : '0000') . '-' . (isset($temp[1]) ?
            $temp[1] : '01') . '-' . (isset($temp[0]) ? $temp[0] : '01');
        return $formatedDate;
    }

    public function analyticsAction()
    {
        $region = $this->getRequest()->getParam('regional_market', '');
        $QStaff = new Application_Model_Staff();

        if ($region != '')
        { // hiÃƒÂ¡Ã‚Â»Ã†â€™n thÃƒÂ¡Ã‚Â»Ã¢â‚¬Â¹ theo vÃƒÆ’Ã‚Â¹ng

            $limit = 20;
            $total = 0;
            $page = $this->getRequest()->getParam('page', 1);

            $department = $this->getRequest()->getParam('department', -1);
            $sort = $this->getRequest()->getParam('sort', '');
            $desc = $this->getRequest()->getParam('desc', 1);
            $this->view->desc = $desc;
            $this->view->current_col = $sort;

            $params = array(
                'off' => 1,
                'regional_market' => $region,
                'department' => $department,
                'sort' => $sort,
                'desc' => $desc,
                );

            $QStaff = new Application_Model_Staff();

            $QTitle = new Application_Model_Title();
            $this->view->titles = $QTitle->get_cache();

            //get regional market
            $QRegion = new Application_Model_RegionalMarket();
            $regions = $QRegion->get_cache();
            $this->view->regions = $regions;

            $staffs = $QStaff->fetchPagination($page, $limit, $total, $params);

            $this->view->staffs = $staffs;
            $this->view->region = $region;
            $this->view->limit = $limit;
            $this->view->total = $total;
            $this->view->url = HOST . 'staff/analytics' . ($params ? '?' . http_build_query
                ($params) . '&' : '?');
            $this->view->offset = $limit * ($page - 1);

            // thÃƒÂ¡Ã‚Â»Ã¢â‚¬Ëœng kÃƒÆ’Ã‚Âª theo phÃƒÆ’Ã‚Â²ng ban
            $data_by_region = $QStaff->analytics_by_region($region);
            $new_data = array();
            foreach ($data_by_region as $key => $value)
            {
                $tmp = array();

                $tmp[] = $departments[$value['department']];
                $tmp[] = intval($value['department']);
                $tmp[] = intval($value['qty']);

                $new_data[] = $tmp;
            }

            if ($department != -1)
            {
                $this->view->department = $department;
            }
            $this->view->data = json_encode($new_data);

            if ($this->getRequest()->isXmlHttpRequest())
            {
                $this->_helper->layout->disableLayout();
                $this->_helper->viewRenderer->setRender('partials/analytics');
            } else
            {
                $this->_helper->viewRenderer->setRender('analytics-region');
            }

        } else
        { // HiÃƒÂ¡Ã‚Â»Ã†â€™n thÃƒÂ¡Ã‚Â»Ã¢â‚¬Â¹ tÃƒÂ¡Ã‚Â»Ã¢â‚¬Â¢ng thÃƒÂ¡Ã‚Â»Ã†â€™

            $data = $QStaff->analytics();
            $data2 = $QStaff->analytics_by_area();

            //get regional markets
            $QRegionalMarket = new Application_Model_RegionalMarket();
            $regional_markets = $QRegionalMarket->get_cache();

            $QArea = new Application_Model_Area();
            $areas = $QArea->get_cache();

            $this->view->regional_markets = $regional_markets;
            $this->view->areas = $areas;

            $sum = 0;
            $new_data = array();
            foreach ($data as $key => $value)
            {
                $tmp = array();

                $tmp[] = isset($regional_markets[$value['regional_market']]) ? $regional_markets[$value['regional_market']] :
                    'UNDEFINED';
                $tmp[] = intval($value['regional_market']);
                $tmp[] = intval($value['qty']);

                $sum += $value['qty'];
                $new_data[] = $tmp;
            }

            $new_data2 = array();
            foreach ($data2 as $key => $value)
            {
                $tmp = array();

                $tmp[] = isset($areas[$value['id']]) ? trim($areas[$value['id']]) : 'UNDEFINED';
                $tmp[] = intval($value['id']);
                $tmp[] = intval($value['qty']);

                $new_data2[] = $tmp;
            }

            $this->view->sum = $sum;
            $this->view->data = json_encode($new_data);
            $this->view->data2 = json_encode($new_data2);
        }

    }

    public function analyticsByAreaAction()
    {
        $QArea = new Application_Model_Area();
        $areas = $QArea->get_cache();

        $QTeam = new Application_Model_Team();
        $where = $QTeam->getAdapter()->quoteInto('parent_id IN (?)', implode(',', $this->
            list_teams));
        $teams = $QTeam->fetchAll($where);

        $team_arr = array();
        $list_title = array();

        foreach ($teams as $key => $value)
        {
            $list_title[] = $value['id'];
            $team_arr[$value['id']] = $value['name'];
        }

        $this->list_titles = $list_title;

        $this->view->areas = $areas;
        $this->view->teams = $team_arr;

        $staffs = array();

        foreach ($areas as $k => $area)
        {
            $staffs[$k] = $this->get_sales_staff_area($k);
        }

        $out = array();

        $tmp = array();
        $tmp[] = "Area";
        foreach ($this->list_titles as $key => $value)
        {
            if (!isset($team_arr[$value]))
            {
                continue;
            }
            $tmp[] = $team_arr[$value];
        }
        $out[] = $tmp;

        foreach ($areas as $key => $area)
        {
            $tmp = array();
            $tmp[] = $area;

            foreach ($this->list_titles as $team)
            {
                $tmp[] = isset($staffs[$key][$team]) ? intval($staffs[$key][$team]) : 0;
            }

            $out[] = $tmp;

        }

        $this->view->staffs = $staffs;
        $this->view->out = json_encode($out);
        $this->view->list_teams = $this->list_titles;

    }

    private function contractAdd($staff_id, $joined_at)
    {
        $QStaff = new Application_Model_Staff();
        $QSalaryPG = new Application_Model_SalaryPg();
        $QSalarySales = new Application_Model_SalarySales();
        $userStorage = Zend_Auth::getInstance()->getStorage()->read();
        $staff_rowset = $QStaff->find($staff_id);
        $staff = $staff_rowset->current();
        $title = $staff['title'];
        $data = array();
        try
        {

            $contract_name = '';
            if (intval($title) == PGPB_TITLE)
            {
                $data['contract_term'] = 3;
                $data['contract_signed_at'] = date("Y-m-d", strtotime($joined_at . "+ 0 days"));
                $data['contract_expired_at'] = date("Y-m-d", strtotime($joined_at . "+ 84 days"));
                $contract_name = 'Thử việc';
            } elseif($staff['is_officer'] == 0)
            {
                //if (intval($title) == SALES_TITLE) {
                $data['contract_term'] = 2;
                $data['contract_signed_at'] = date("Y-m-d", strtotime($joined_at . "+ 0 days"));
                $data['contract_expired_at'] = date("Y-m-d", strtotime($joined_at . "+ 59 days"));
                $contract_name = 'Thử việc';
                //}
            }else{
                return array('code' => 1, 'message' => 'success');
            }
            //luu log contract

            $QLog = new Application_Model_StaffPrintLog();
            $ip = $this->getRequest()->getServer('REMOTE_ADDR');

            $info = 'Insert contract term type = ' . $contract_name . ' for: ' . $staff['firstname'] .
                ' ' . $staff['lastname'] . ' <br/> Form : ' . $data['contract_signed_at'] .
                ' To :' . $data['contract_expired_at'];


            $where = array();
            $where[] = $QStaff->getAdapter()->quoteInto('id = ?', $staff_id);
            $QStaff->update($data, $where);

            if ($staff['title'] == SALES_TITLE || $staff['title'] == SALES_ACCESSORIES_TITLE)
            {
                $where = array();
                $where[] = $QSalarySales->getAdapter()->quoteInto('province_id = ?', $staff['regional_market']);
                $luong = $QSalarySales->fetchRow($where);
            }

            //pg team
            if ($staff['title'] == PGPB_TITLE)
            {
                $where = array();
                $where[] = $QSalaryPG->getAdapter()->quoteInto('province_id = ?', $staff['regional_market']);
                $luong = $QSalaryPG->fetchRow($where);
            }

            $db = Zend_Registry::get('db');
            $selectTitle = $db->select()->from(array('p' => 'team'), array('name'))->where('id = ?',
                $staff['title']);
            $title = $db->fetchOne($selectTitle);

            $QLog->insert(array(
                'info' => $info,
                'user_id' => $userStorage->id,
                'object' => $staff['id'],
                'ip_address' => $ip,
                'time' => date('Y-m-d H:i:s'),
                'contract_term' => $data['contract_term'],
                'log_type' => STAFF_PRINT_LOG_INSERT,
                'from_date' => $data['contract_signed_at'],
                'to_date' => $data['contract_expired_at'],
                'title' => $title,
                'regional_market' => $staff['regional_market'],
                'base_salary' => (isset($luong['base_salary']) and $luong['base_salary']) ? $luong['base_salary'] : null,
                'bonus_salary' => (isset($luong['bonus_salary']) and $luong['bonus_salary']) ? $luong['bonus_salary'] : null,
                'allowance_1' => (isset($luong['allowance_1']) and $luong['allowance_1']) ? $luong['allowance_1'] : null,
                'allowance_2' => (isset($luong['allowance_2']) and $luong['allowance_2']) ? $luong['allowance_2'] : null,
                'allowance_3' => (isset($luong['allowance_3']) and $luong['allowance_3']) ? $luong['allowance_3'] : null,
                'probation_salary' => (isset($luong['probation_salary']) and $luong['probation_salary']) ?
                    $luong['probation_salary'] : null,
                'kpi' => (isset($luong['kpi']) and $luong['kpi']) ? $luong['kpi'] : null,
                ));
            //successful
            return array('code' => 1, 'message' => 'success');
        }
        catch (exception $e)
        {
            return array('code' => -1, 'message' => $e->getMessage());
        }
    }

    public function analyticsGeneralAction()
    {
        $staffs = $this->get_staff_general();
        $out = array();
        $tmp = array();
        $tmp[] = "Department";
        $tmp[] = "Staff";
        $out[] = $tmp;
        foreach ($staffs as $key => $v)
        {
            $tmp = array();
            $tmp[] = $v['department_name'];
            $tmp[] = intval($v['total']);
            $out[] = $tmp;
        }

        $this->view->out = json_encode($out);

        $staff = array();
        $department = array();
        $sum = 0;

        foreach ($staffs as $k => $v)
        {
            $staff[] = $v['total'];
            $sum += isset($v['total']) ? $v['total'] : 0;
            $department[] = $v['department_name'];
        }

        $tmp = array($department, $staff);

        $this->view->staffs = $tmp;
        $this->view->sum = $sum;
    }

    public function checkAction()
    {

        $id = $this->getRequest()->getParam('id');
        $code = $this->getRequest()->getParam('code');
        $email = $this->getRequest()->getParam('email');

        $where = array();
        $QStaff = new Application_Model_Staff();

        if ($code)
            $where[] = $QStaff->getAdapter()->quoteInto('code = ?', $code);

        if ($email)
            $where[] = $QStaff->getAdapter()->quoteInto('email = ?', $email);

        if (sizeof($where) == 0)
        {
            echo "-1";
            exit;
        }

        /*$where[] = $QStaff->getAdapter()->quoteInto('off_date IS NULL ', null);*/

        $where[] = $QStaff->getAdapter()->quoteInto('id != ?', intval($id));

        $staffs = $QStaff->fetchAll($where);

        if (isset($staffs[0]))
            echo '1';
        else
            echo '0';

        exit;
    }

    public function loadAreaAction() {

        $grand_area_id = $this->getRequest()->getParam('grand_area_id');

        $db = Zend_Registry::get('db');

        $get = array(
            'id'    => 'a.id',
            'name'  => 'a.name',
        );

        $select = $db->select()
            ->from(array('ga'  => 'grand_area'), $get)
            ->join(array('gal' => 'grand_area_list'), 'ga.id = gal.grand_area_id', array())
            ->join(array('a'   => 'area'), 'gal.area = a.id', array())
            ->order('a.name ASC');

        if ($grand_area_id != 'null') {
            if (is_array($grand_area_id) && $grand_area_id) { $select->where('ga.id IN (?)', $grand_area_id); } 
            else { $select->where('ga.id = ?', $grand_area_id); }

            $area_list = $db->fetchAll($select);
        } else {
            $QArea = new Application_Model_Area();
            $area_list = $QArea->fetchAll(null, 'name')->toArray();
        }

        echo json_encode( $area_list );
        exit;
    }

    public function loadProvinceAction()
    {
        $area_id = $this->getRequest()->getParam('area_id');

        $QRegionalMarket = new Application_Model_RegionalMarket();
        if (is_array($area_id) && $area_id)
            $where = $QRegionalMarket->getAdapter()->quoteInto('area_id IN (?)', $area_id);
        else
            $where = $QRegionalMarket->getAdapter()->quoteInto('area_id = ?', $area_id);

        echo json_encode($QRegionalMarket->fetchAll($where, 'name')->toArray());
        exit;
    }

    public function loadThAmphureAction()
    {
        $th_province_id = $this->getRequest()->getParam('th_province_id');

        $QThAmphure = new Application_Model_ThAmphure();
        if (is_array($th_province_id) && $th_province_id)
            $where = $QThAmphure->getAdapter()->quoteInto('province_id IN (?)', $th_province_id);
        else
            $where = $QThAmphure->getAdapter()->quoteInto('province_id = ?', $th_province_id);

        echo json_encode($QThAmphure->fetchAll($where, 'name_th')->toArray());
        exit;
    }

    public function loadThDistrictAction()
    {
        $th_amphure_id = $this->getRequest()->getParam('th_amphure_id');

        $QThDistrict = new Application_Model_ThDistrict();

        $where = array();
        $where[] = $QThDistrict->getAdapter()->quoteInto('zipcode <> ?', 0);

        if (is_array($th_amphure_id) && $th_amphure_id)
            $where[] = $QThDistrict->getAdapter()->quoteInto('amphure_id IN (?)', $th_amphure_id);
        else
            $where[] = $QThDistrict->getAdapter()->quoteInto('amphure_id = ?', $th_amphure_id);

        echo json_encode($QThDistrict->fetchAll($where, 'name_th')->toArray());
        exit;
    }

    public function loadMobileModelAction()
    {
        $brand_id = $this->getRequest()->getParam('brand_id');

        $QMobileModel = new Application_Model_MobileModel();
        if (is_array($brand_id) && $brand_id)
            $where = $QMobileModel->getAdapter()->quoteInto('brand_id IN (?)', $brand_id);
        else
            $where = $QMobileModel->getAdapter()->quoteInto('brand_id = ?', $brand_id);

        echo json_encode($QMobileModel->fetchAll($where, 'name')->toArray());
        exit;
    }

    public function loadRdAction() {

        $area_id = $this->getRequest()->getParam('area_id');

        $db = Zend_Registry::get('db');

        $exception = array('5800892');

        $get = array(
            'staff_id'    => 's.id',
            'staff_code'  => 's.code',
            'staff_name'  => new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)"),
        );

        $select = $db->select()
            ->from(array('asm' => 'asm'), $get)
            ->join(array('s' => 'staff'), 'asm.staff_id = s.id', array())
            ->where('s.off_date IS NULL', 1) 
            ->where('s.status = ?', 1)
            ->where('s.group_id IN (?)', array(RM_ID,RMSTANDBY_ID))
            ->where('s.code NOT IN (?)', $exception)
            ->order('s.code ASC');

        if ($area_id != 'null') {
            if (is_array($area_id) && $area_id) { $select->where('asm.area_id IN (?)', $area_id); } 
            else { $select->where('asm.area_id = ?', $area_id); }
        } 

        $rd_list = $db->fetchAll($select);

        echo json_encode( $rd_list );
        exit;
    }

    public function loadAsmAction() {

        $area_id = $this->getRequest()->getParam('area_id');

        $db = Zend_Registry::get('db');

        $get = array(
            'staff_id'    => 's.id',
            'staff_code'  => 's.code',
            'staff_name'  => new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)"),
        );

        $select = $db->select()
            ->from(array('a'  => 'area'), $get)
            ->join(array('rm' => 'regional_market'), 'a.id = rm.area_id', array())
            ->join(array('asm'=> 'asm'), 'asm.area_id = (CASE WHEN asm.type = 2 THEN rm.area_id ELSE rm.id END)', array())
            ->join(array('s'  => 'staff'), 'asm.staff_id = s.id', array())
            ->where('s.off_date IS NULL', 1) 
            ->where('s.status = ?', 1)
            ->where('s.group_id IN (?)', array(ASM_ID,ASMSTANDBY_ID))
            ->group('s.id')
            ->order('s.code ASC');

        if ($area_id != 'null') {
            if (is_array($area_id) && $area_id) { $select->where('a.id IN (?)', $area_id); } 
            else { $select->where('a.id = ?', $area_id); }
        } 

        $asm_list = $db->fetchAll($select);

        echo json_encode( $asm_list );
        exit;
    }

    // Load Sale List By Area
    public function loadSaleAction() {

        $area_id = $this->getRequest()->getParam('area_id'); 
        $province_id = $this->getRequest()->getParam('province_id'); 

        $db = Zend_Registry::get('db');

        $get = array(
            // 'id'    => 's.id',
            // 'name'  => new Zend_Db_Expr("CONCAT(s.code,' | ', s.firstname, ' ', s.lastname)"),

            'id'    => new Zend_Db_Expr("(CASE WHEN s.id IS NULL THEN CONCAT(0,'|',a.id) ELSE s.id END)"),
            'name'  => new Zend_Db_Expr(
                "(CASE WHEN s.id IS NULL THEN CONCAT(a.name,' | No Sale') ELSE CONCAT(a.name, ' | ', s.code,' | ', s.firstname, ' ', s.lastname) END)"),
        );

        $select = $db->select()
            ->from(array('st' => 'store'), $get)
            ->join(array('rm' => 'regional_market') , 'st.regional_market = rm.id'  , array())
            ->join(array('a'  => 'area')            , 'rm.area_id = a.id'           , array())
            ->joinLeft(array('ss' => 'store_staff') , 'st.id = ss.store_id AND ss.is_leader = 1', array())
            ->joinLeft(array('s'  => 'staff')       , 'ss.staff_id = s.id AND s.group_id = 9'   , array())
            ->group(array('s.id','a.id'))
            ->order(array('a.name ASC','s.code ASC'));

        if ($area_id != 'null') { $select->where('a.id IN (?)', $area_id); } 
        else { $select->where('1 = 0', 1); }

        // Check Province Permission
        if ( isset($province_id) &&  $province_id ) {
            $select->where('rm.id IN (?)', explode("|", $province_id));
        } else {
            $select->where('a.id IN (?)', $area_id);
        }

        // echo $select; exit;
        $sale_list = $db->fetchAll($select);

        echo json_encode( $sale_list );
        exit;

    }

    public function loadSalesTeamAction()
    {
        $regional_market = $this->getRequest()->getParam('district');

        $QStore = new Application_Model_Store();
        $where = array();
        if (is_array($regional_market) && count($regional_market) > 0)
            $where[] = $QStore->getAdapter()->quoteInto('district IN (?)', $regional_market);
        else
            $where[] = $QStore->getAdapter()->quoteInto('district = ?', $regional_market);

        $where[] = $QStore->getAdapter()->quoteInto('(del IS NULL OR del = 0)', 1);
        $stores = $QStore->fetchAll($where, 'name');

        echo json_encode(array('stores' => $stores->toArray()));

        exit;
    }

    public function loadSalesByProvienceAction(){
        $provience = $this->getRequest()->getParam('regional_market');

        $QStore = new Application_Model_Store();
        $where = array();
         if (is_array($regional_market) && count($regional_market) > 0)
            $where[] = $QStore->getAdapter()->quoteInto('regional_market IN (?)', $provience);
        else
            $where[] = $QStore->getAdapter()->quoteInto('regional_market = ?', $provience);

        $where[] = $QStore->getAdapter()->quoteInto('(del IS NULL OR del = 0)', 1);
        $stores = $QStore->fetchAll($where, 'name');

        echo json_encode(array('stores' => $stores->toArray()));

        exit;
    }

    public function assignAction()
    {
        $id = $this->getRequest()->getParam('id', 0);
        $regional_market = $this->getRequest()->getParam('regional_market', 0);

        if ($id > 0 && $regional_market > 0)
        {
            $staff = new Application_Model_Staff();
            $where = $staff->getAdapter()->quoteInto('id = ?', $id);
            $data = array('regional_market' => $regional_market);
            $n = $staff->update($data, $where);

            if (!empty($n) && $n > 0)
            {
                echo json_encode(array('result' => '1'));
                exit;
            }
        }
        echo json_encode(array('result' => '0'));
        exit;
    }

    public function unassignAction()
    {
        $id = $this->getRequest()->getParam('id', 0);

        if ($id > 0)
        {
            $staff = new Application_Model_Staff();
            $where = $staff->getAdapter()->quoteInto('id = ?', $id);
            $data = array('regional_market' => null);
            $n = $staff->update($data, $where);
        }

        $flashMessenger = $this->_helper->flashMessenger;
        $messages = $flashMessenger->setNamespace('success')->getMessages();
        $this->view->messages = $messages;

        $this->_redirect(HOST . 'manage/assign');
    }

    public function logAction()
    {

        set_time_limit(0);
        ini_set('memory_limit', '-1');
        error_reporting(~E_ALL);
        ini_set("display_error", '0');
        
        $object = $this->getRequest()->getParam('object');

        if ($object)
        {
            $QStaffLog = new Application_Model_StaffLog();

            $from = $this->getRequest()->getParam('from');
            $to = $this->getRequest()->getParam('to');
            $page = $this->getRequest()->getParam('page', 1);
            $limit = LIMITATION;
            $total = 0;

            $params = array(
                'object' => $object,
                'from' => $from,
                'to' => $to,
                );

            $log_res = $QStaffLog->fetchPagination($page, $limit, $total, $params);
            $logs = array();

            foreach ($log_res as $k => $log)
            {
                $logs[] = array(
                    'time' => $log['time'],
                    'user_id' => $log['user_id'],
                    'ip' => $log['ip_address'],
                    'before' => unserialize($log['before']),
                    'after' => unserialize($log['after']),
                    'diffs' => array(),
                    'type' => $log['type'],
                    );
            }

            $n = count($logs);

            for ($i = 0; $i < $n; $i++)
            {
                $logs[$i]['diffs'] = array_diff_assoc($logs[$i]['after'], $logs[$i]['before']);
            }

            $QStaff = new Application_Model_Staff();
            $QReligion = new Application_Model_Religion();
            $QDepartment = new Application_Model_Department();
            $QTeam = new Application_Model_Team();
            $QNationality = new Application_Model_Nationality();
            $QArea = new Application_Model_Area();
            $QRegionalMarket = new Application_Model_RegionalMarket();
            $QGroup = new Application_Model_Group();
            $QContractTerm = new Application_Model_ContractTerm();
            $QContractType = new Application_Model_ContractType();

            $this->view->staffs = $QStaff->get_cache();
            $this->view->religion = $QReligion->get_cache();
            $this->view->department = $QDepartment->get_cache();
            $this->view->team = $QTeam->get_cache();
            $this->view->nationality = $QNationality->get_cache();
            $this->view->area = $QArea->get_cache();
            $this->view->regionalMarket = $QRegionalMarket->get_cache();
            $this->view->group = $QGroup->get_cache();
            $this->view->contractTerm = $QContractTerm->get_cache();
            $this->view->contractType = $QContractType->get_cache();

            $this->view->labels = array(
                'code' => 'Code',
                'contract_type' => 'Contract Type',
                'contract_signed_at' => 'Contract Signed At',
                'contract_term' => 'Contract Term',
                'contract_expired_at' => 'Contract Expired At',
                'department' => 'Department',
                'team' => 'Team',
                'firstname' => 'Firstname',
                'lastname' => 'Lastname',
                'title' => 'Title',
                'joined_at' => 'Joined At',
                'off_date' => 'Off Date',
                'gender' => 'Gender',
                'dob' => 'DOB',
                'certificate' => 'Certificate',
                'level' => 'Level',
                'temporary_address' => 'Temporary Address',
                'address' => 'Address',
                'regional_market' => 'Regional Market',
                'permanent_address' => 'Permanent Address',
                'birth_place' => 'Birth Place',
                'native_place' => 'Native Place',
                'ID_number' => 'ID Number',
                'ID_place' => 'ID Place',
                'ID_date' => 'ID Date',
                'nationality' => 'Nationality',
                'religion' => 'Religion',
                'phone_number' => 'Phone Number',
                'note' => 'Note',
                'additional_info' => 'Additional Info',
                'email' => 'Email',
                'group_id' => 'Group',
                'social_insurance_time' => 'Social Insurance Time',
                'social_insurance_number' => 'Social Insurance Number',
                'personal_tax' => 'Personal Tax',
                'family_allowances_registered' => 'Family Allowances Registered',
                'created_at' => 'Created At',
                'created_by' => 'Created By',
                'photo' => 'Photo',
                'last_login' => 'Last Login',
                'is_officer' => 'Is Head Office',
                );

            $this->view->logs = $logs;
            $this->view->params = $params;
            $this->view->limit = $limit;
            $this->view->total = $total;
            $this->view->url = HOST . 'staff/log' . ($params ? '?' . http_build_query($params) .
                '&' : '?');

            $this->view->offset = $limit * ($page - 1);

        } else
        {
            $this->_redirect(HOST . 'staff');
        }
    }

    public function allLogAction()
    {
        $id = $this->getRequest()->getParam('id');
        $QStaffLog = new Application_Model_StaffLog();
        $QStaff = new Application_Model_Staff();

        $this->view->staffs = $QStaff->get_cache();

        // hiÃƒÂ¡Ã‚Â»Ã†â€™n thÃƒÂ¡Ã‚Â»Ã¢â‚¬Â¹ chi tiÃƒÂ¡Ã‚ÂºÃ‚Â¿t 1 log
        if ($id)
        {
            $log = $QStaffLog->find($id);
            $log = $log->current();

            if ($log)
            {
                $this->view->log = array(
                    'id' => $log['id'],
                    'before' => unserialize($log['before']),
                    'after' => unserialize($log['after']),
                    'ip' => $log['ip'],
                    'time' => $log['time'],
                    'user_id' => $log['user_id'],
                    'object' => $log['object'],
                    'type' => $log['type'],
                    );
            } else
            {
                $this->_redirect(HOST . 'staff/all-log');
            }

            // hiÃƒÂ¡Ã‚Â»Ã†â€™n thÃƒÂ¡Ã‚Â»Ã¢â‚¬Â¹ danh sÃƒÆ’Ã‚Â¡ch
        } else
        {

            $from = $this->getRequest()->getParam('from');
            $to = $this->getRequest()->getParam('to');
            $page = $this->getRequest()->getParam('page', 1);
            $limit = 1;
            $total = 0;

            $params = array(
                'id' => $id,
                'from' => $from,
                'to' => $to,
                );

            $log_res = $QStaffLog->fetchPagination($page, $limit, $total, $params);

            $this->view->logs = $logs;
            $this->view->params = $params;
            $this->view->limit = $limit;
            $this->view->total = $total;
            $this->view->url = HOST . 'staff/all-log' . ($params ? '?' . http_build_query($params) .
                '&' : '?');

            $this->view->offset = $limit * ($page - 1);

        }
    }

    public function photoAction()
    {

    }

    public function photoLoadAction()
    {
        $this->_helper->layout->disableLayout();

        $QStaff = new Application_Model_Staff();
        $QRegionalMarket = new Application_Model_RegionalMarket();
        $page = $this->getRequest()->getParam('page', 1);

        $limit = LIMITATION * 2;
        $total = 0;

        $params = array(
            'page' => $page,
            'has_photo' => 1,
            );


        $this->view->staffs = $QStaff->fetchPagination($page, $limit, $total, $params);
        $this->view->regions = $QRegionalMarket->get_cache();
    }

    public function companyAction()
    {
        $page = $this->getRequest()->getParam('page', 1);
        $name = $this->getRequest()->getParam('name');
        $limit = LIMITATION;
        $total = 0;

        $params = array('name' => $name, );

        $QCompany = new Application_Model_Company();
        $this->view->companies = $QCompany->fetchPagination($page, $limit, $total, $params);
        $this->view->limit = $limit;
        $this->view->total = $total;
        $this->view->url = HOST . 'staff/company/' . ($params ? '?' . http_build_query($params) .
            '&' : '?');
        $this->view->offset = $limit * ($page - 1);
    }

    public function companyEditAction()
    {
        $id = $this->getRequest()->getParam('id');

        $QCompany = new Application_Model_Company();
        $company = $QCompany->find($id);
        $company = $company->current();

        if (!$company)
        {
            $flashMessenger = $this->_helper->flashMessenger;
            $flashMessenger->setNamespace('error')->addMessage('Invalid ID.');
            $this->_redirect(HOST . 'staff/company');
        }

        $this->view->company = $company;

        //get department
        $QDepartment = new Application_Model_Department();
        $this->view->departments = $QDepartment->get_cache();

        //get teams
        $QTeam = new Application_Model_Team();
        $this->view->teams = $QTeam->get_cache();

        $QArea = new Application_Model_Area();
        $this->view->areas = $QArea->fetchAll(null, 'name');
    }

    public function companySearchAction()
    {
        if ($this->getRequest()->isXmlHttpRequest())
        {
            $this->_helper->layout->disableLayout();
            $this->_helper->viewRenderer->setRender('partials/list-company');
        } else
        {
            exit;
        }

        $sort = $this->getRequest()->getParam('sort', '');
        $desc = $this->getRequest()->getParam('desc', 1);

        $page = $this->getRequest()->getParam('page', 1);
        $name = $this->getRequest()->getParam('name');
        $department = $this->getRequest()->getParam('department');
        $off = $this->getRequest()->getParam('off', 1);
        $team = $this->getRequest()->getParam('team');
        $regional_market = $this->getRequest()->getParam('regional_market');
        $area_id = $this->getRequest()->getParam('area_id');
        $email = $this->getRequest()->getParam('email');
        $is_officer = $this->getRequest()->getParam('is_officer');
        $tags = $this->getRequest()->getParam('tags');

        if ($tags and is_array($tags))
            $tags = $tags;
        else
            $tags = null;

        $limit = LIMITATION;

        $total = 0;

        $params = array_filter(array(
            'name' => $name,
            'department' => $department,
            'off' => $off,
            'team' => $team,
            'regional_market' => $regional_market,
            'area_id' => $area_id,
            'email' => $email,
            'is_officer' => $is_officer,
            ));

        $params['sort'] = $sort;
        $params['desc'] = $desc;

        $QStaff = new Application_Model_Staff();
        $staffs = $QStaff->fetchPagination($page, $limit, $total, $params);

        $userStorage = Zend_Auth::getInstance()->getStorage()->read();
        $this->view->userStorage = $userStorage;

        //get department
        $QDepartment = new Application_Model_Department();
        $this->view->departments = $QDepartment->get_cache();

        //get teams
        $QTeam = new Application_Model_Team();
        $this->view->teams = $QTeam->get_cache();

        $QCompany = new Application_Model_Company();
        $this->view->companies = $QCompany->get_cache();

        $QArea = new Application_Model_Area();
        $this->view->areas = $QArea->get_cache();

        $QRegionalMarket = new Application_Model_RegionalMarket();
        $this->view->regions = $QRegionalMarket->get_cache_all();

        $this->view->desc = $desc;
        $this->view->sort = $sort;
        $this->view->staffs = $staffs;
        $this->view->params = $params;
        $this->view->limit = $limit;
        $this->view->total = $total;
        $this->view->url = HOST . 'staff/company-search' . ($params ? '?' .
            http_build_query($params) . '&' : '?');

        $this->view->offset = $limit * ($page - 1);
        $this->view->current_url = trim($this->getRequest()->getRequestUri(), '/');
    }

    public function casualWorkerAction()
    {
        $sort = $this->getRequest()->getParam('sort', '');
        $desc = $this->getRequest()->getParam('desc', 1);
        $page = $this->getRequest()->getParam('page', 1);
        $name = $this->getRequest()->getParam('name');
        $area_id = $this->getRequest()->getParam('area_id');

        $limit = LIMITATION;
        $total = 0;

        $params = array(
            'sort' => $sort,
            'desc' => $desc,
            'name' => $name,
            'area_id' => $area_id,
            );

        $QCasual = new Application_Model_CasualWorker();
        $staffs = $QCasual->fetchPagination($page, $limit, $total, $params);

        $this->view->desc = $desc;
        $this->view->sort = $sort;
        $this->view->staffs = $staffs;
        $this->view->params = $params;
        $this->view->limit = $limit;
        $this->view->total = $total;
        $this->view->url = HOST . 'staff/casual-worker' . ($params ? '?' .
            http_build_query($params) . '&' : '?');

        $QArea = new Application_Model_Area();
        $this->view->areas = $QArea->get_cache();

        $QRegion = new Application_Model_RegionalMarket();
        $this->view->regions = $QRegion->get_cache_all();

        $this->view->offset = $limit * ($page - 1);

    }

    private function get_sales_staff_area($area_id = 0)
    {
        $db = Zend_Registry::get('db');

        $select = $db->select()->from(array('s' => 'staff'), array('total' =>
                'COUNT(s.id)'))->join(array('r' => 'regional_market'),
            's.regional_market = r.id', array('province_name' => 'r.name'))->join(array('a' =>
                'area'), 'r.area_id = a.id', array('area_name' => 'a.name'))->join(array('t' =>
                'team'), 't.id = s.title', array('title_name' => 't.name', 'title_id' => 't.id'))->
            join(array('tm' => 'team'), 'tm.id = t.parent_id', array('team_name' =>
                'tm.name', 'team_id' => 'tm.id'))->where('s.off_date = 0 OR s.off_date IS NULL OR s.off_date = \'\'')->
            group('t.id')->having('t.id IN (?)', $this->list_titles);

        if (isset($area_id) && $area_id)
            $select->where('a.id = ?', $area_id);

        $result = $db->fetchAll($select);

        $staffs = array();
        foreach ($result as $k => $v)
        {
            $staffs[$v['title_id']] = $v['total'];
        }

        return $staffs;
    }

    private function get_staff_general()
    {
        $db = Zend_Registry::get('db');

        $select = $db->select()->from(array('s' => 'staff'), array('total' =>
                'COUNT(s.id)'))->join(array('t' => 'team'), 't.id=s.title', array())->join(array
            ('tm' => 'team'), 'tm.id=t.parent_id', array())->join(array('d' => 'team'),
            'd.id=tm.parent_id', array('department_name' => 'd.name'))->where('s.off_date = 0 OR s.off_date IS NULL OR s.off_date = \'\' ')->
            group('d.id');

        $result = $db->fetchAll($select);
        return $result;
    }

    public function priviledgeAction()
    {
        $staff_id = $this->getRequest()->getParam('id');

        $QStaffPriveledge = new Application_Model_StaffPriviledge();

        $where = $QStaffPriveledge->getAdapter()->quoteInto('staff_id = ?', $staff_id);

        $result = $QStaffPriveledge->fetchRow($where);

        $personal_accesses = isset($result['access']) ? json_decode($result['access']) : null;

        $this->view->id = isset($result['id']) ? $result['id'] : null;

        //get group access
        if (!$personal_accesses)
        {
            $db = Zend_Registry::get('db');
            $select = $db->select()->from(array('p' => 'staff'), array());

            $select->join(array('g' => 'group'), 'p.group_id = g.id', array('g.access'));

            $select->where('p.id = ?', $staff_id);

            $result = $db->fetchRow($select);

            $group_accesses = isset($result['access']) ? json_decode($result['access']) : null;

            $this->view->default_page = isset($result['default_page']) ? $result['default_page'] : null;

            $this->view->accesses = $group_accesses;

            $personal_menus = (isset($result['menu']) and $result['menu']) ? explode(',', $result['menu']) : null;

        } else
        {

            $personal_menus = (isset($result['menu']) and $result['menu']) ? explode(',', $result['menu']) : null;

            $this->view->accesses = $personal_accesses;

            $this->view->default_page = isset($result['default_page']) ? $result['default_page'] : null;
        }

        $this->view->staff_id = $staff_id;

        //get all controller and action
        $front = $this->getFrontController();
        $acl = array();

        foreach ($front->getControllerDirectory() as $module => $path)
        {

            foreach (scandir($path) as $file)
            {

                if (strstr($file, "Controller.php") !== false)
                {

                    include_once $path . DIRECTORY_SEPARATOR . $file;

                    foreach (get_declared_classes() as $class)
                    {

                        if (is_subclass_of($class, 'Zend_Controller_Action'))
                        {

                            $controller = lcfirst(substr($class, 0, strpos($class, "Controller")));
                            $tem = '';

                            for ($i = 0; $i < strlen($controller); $i++)
                            {
                                $char = $controller[$i];
                                if (ord($char) < 97)
                                    $tem .= '-' . chr(ord($char) + 32);
                                else
                                    $tem .= $char;
                            }
                            $controller = $tem;

                            $actions = array();

                            foreach (get_class_methods($class) as $action)
                            {

                                if (strstr($action, "Action") !== false)
                                {
                                    $action = substr($action, 0, strpos($action, "Action"));
                                    $tem = '';

                                    for ($i = 0; $i < strlen($action); $i++)
                                    {
                                        $char = $action[$i];
                                        if (ord($char) < 97)
                                            $tem .= '-' . chr(ord($char) + 32);
                                        else
                                            $tem .= $char;
                                    }
                                    $actions[] = $tem;
                                }
                            }
                        }
                    }

                    $acl[$module][$controller] = $actions;
                }
            }
        }
        //set current method
        $actions = array();
        foreach (get_class_methods($this) as $action)
        {
            if (strstr($action, "Action") !== false)
            {
                $action = substr($action, 0, strpos($action, "Action"));
                $tem = '';

                for ($i = 0; $i < strlen($action); $i++)
                {
                    $char = $action[$i];
                    if (ord($char) < 97)
                        $tem .= '-' . chr(ord($char) + 32);
                    else
                        $tem .= $char;
                }
                $actions[] = $tem;
            }
        }

        $acl[$this->getRequest()->getModuleName()][$this->getRequest()->
            getControllerName()] = $actions;

        $this->view->acl = $acl;


        //menu
        $QMenu = new Application_Model_Menu();
        $where = $QMenu->getAdapter()->quoteInto('group_id = ?', 1);
        $menus = $QMenu->fetchAll($where, array('parent_id', 'position'));
        foreach ($menus as $menu)
            $this->add_row($menu->id, $menu->parent_id, $menu->title);

        $this->view->menus = $this->generate_list($personal_menus);

        //back url
        $this->view->back_url = $this->getRequest()->getServer('HTTP_REFERER');
    }

    public function priviledgeSaveAction()
    {

        if ($this->getRequest()->getMethod() == 'POST')
        {


            $id = $this->getRequest()->getParam('id');
            $staff_id = $this->getRequest()->getParam('staff_id');
            $default_page = $this->getRequest()->getParam('default_page');
            $menus = $this->getRequest()->getParam('menus');

            $raw_access = $this->getRequest()->getParam('access');
            $access = array();
            if (is_array($raw_access))
            {
                foreach ($raw_access as $module => $item)
                    foreach ($item as $controller => $item_2)
                        foreach ($item_2 as $action => $item_3)
                        {
                            if ($item_3)
                                $access[] = $module . '::' . $controller . '::' . $action;
                        }
            }


            $QStaffPriveledge = new Application_Model_StaffPriviledge();

            $data = array(
                'staff_id' => $staff_id,
                'default_page' => ($default_page ? $default_page : null),
                'menu' => (is_array($menus) ? implode(',', $menus) : null),
                'access' => json_encode($access),
                );

            if ($id)
            {
                $where = $QStaffPriveledge->getAdapter()->quoteInto('id = ?', $id);

                $QStaffPriveledge->update($data, $where);
            } else
            {
                $QStaffPriveledge->insert($data);
            }

            //remove cache
            $cache = Zend_Registry::get('cache');
            $cache->remove('staff_priviledge_cache');

            $flashMessenger = $this->_helper->flashMessenger;
            $flashMessenger->setNamespace('success')->addMessage('Done!');
        }

        $back_url = $this->getRequest()->getParam('back_url');

        $this->_redirect(($back_url ? $back_url : HOST . 'staff'));
    }

    private function generate_list($group_menus)
    {
        return $this->ul(0, '', $group_menus);
    }

    function ul($parent = 0, $attr = '', $group_menus = null)
    {
        static $i = 1;
        $indent = str_repeat("\t\t", $i);
        if (isset($this->data[$parent]))
        {
            if ($attr)
            {
                $attr = ' ' . $attr;
            }
            $html = "\n$indent";
            $html .= "<ul$attr>";
            $i++;
            foreach ($this->data[$parent] as $row)
            {
                $child = $this->ul($row['id'], '', $group_menus);
                $html .= "\n\t$indent";
                $html .= '<li>';
                $html .= '<input value="' . $row['id'] . '" ' . (($group_menus and in_array($row['id'],
                    $group_menus)) ? 'checked' : '') . ' type="checkbox" id="menus_' . $row['id'] .
                    '" name="menus[]"><label>' . $row['label'] . '</label>';
                if ($child)
                {
                    $i--;
                    $html .= $child;
                    $html .= "\n\t$indent";
                }
                $html .= '</li>';
            }
            $html .= "\n$indent</ul>";
            return $html;
        } else
        {
            return false;
        }
    }

    private function add_row($id, $parent, $label)
    {
        $this->data[$parent][] = array('id' => $id, 'label' => $label);
    }

    private function get_address($staff_id = null)
    {
        if (is_null($staff_id))
            return false;

        // get addresses
        $QStaffAddress = new Application_Model_StaffAddress();
        $district_cache = $QRegionalMarket->get_district_cache();
        $this->view->district_cache = $district_cache;

        // ------------------------- get permanent address
        $where = array();
        $where[] = $QStaffAddress->getAdapter()->quoteInto('staff_id = ?', $id);
        $where[] = $QStaffAddress->getAdapter()->quoteInto('address_type = ?',
            My_Staff_Address::Permanent);
        $permanent_address = $QStaffAddress->fetchRow($where);
        $this->view->permanent_address = $permanent_address;

        if (isset($permanent_address['district']) && isset($district_cache[$permanent_address['district']]))
            $this->view->permanent_address_districts = $QRegionalMarket->
                get_district_by_province_cache($district_cache[$permanent_address['district']]['parent']);
        // ------------------------- get temporary address
        $where = array();
        $where[] = $QStaffAddress->getAdapter()->quoteInto('staff_id = ?', $id);
        $where[] = $QStaffAddress->getAdapter()->quoteInto('address_type = ?',
            My_Staff_Address::Temporary);
        $temporary_address = $QStaffAddress->fetchRow($where);
        $this->view->temporary_address = $temporary_address;

        if (isset($temporary_address['district']) && isset($district_cache[$temporary_address['district']]))
            $this->view->temporary_address_districts = $QRegionalMarket->
                get_district_by_province_cache($district_cache[$temporary_address['district']]['parent']);

        // ------------------------- get birth certificate address
        $where = array();
        $where[] = $QStaffAddress->getAdapter()->quoteInto('staff_id = ?', $id);
        $where[] = $QStaffAddress->getAdapter()->quoteInto('address_type = ?',
            My_Staff_Address::Birth_Certificate);
        $birth_address = $QStaffAddress->fetchRow($where);
        $this->view->birth_address = $birth_address;

        if (isset($birth_address['district']) && isset($district_cache[$birth_address['district']]))
            $this->view->birth_address_districts = $QRegionalMarket->
                get_district_by_province_cache($district_cache[$birth_address['district']]['parent']);

        // ------------------------- get ID card address
        $where = array();
        $where[] = $QStaffAddress->getAdapter()->quoteInto('staff_id = ?', $id);
        $where[] = $QStaffAddress->getAdapter()->quoteInto('address_type = ?',
            My_Staff_Address::ID_Card);
        $id_card_address = $QStaffAddress->fetchRow($where);
        $this->view->id_card_address = $id_card_address;

        if (isset($id_card_address['district']) && isset($district_cache[$id_card_address['district']]))
            $this->view->id_card_address_districts = $QRegionalMarket->
                get_district_by_province_cache($district_cache[$id_card_address['district']]['parent']);

        // ------------------------- Get other logs
        $QStaffEducation = new Application_Model_StaffEducation();
        $where = $QStaffEducation->getAdapter()->quoteInto('staff_id = ?', $id);
        $this->view->education = $QStaffEducation->fetchAll($where);

        $QStaffExperience = new Application_Model_StaffExperience();
        $where = $QStaffExperience->getAdapter()->quoteInto('staff_id = ?', $id);
        $this->view->experience = $QStaffExperience->fetchAll($where);

        $QStaffRelative = new Application_Model_StaffRelative();
        $where = $QStaffRelative->getAdapter()->quoteInto('staff_id = ?', $id);
        $this->view->relative = $QStaffRelative->fetchAll($where);
    }

    public function saveTransferAction()
    {
        $this->_helper->layout()->disableLayout(true);
        $staff_id = $this->getRequest()->getParam('staff_id');
        $area_id = $this->getRequest()->getParam('area_id');
        $regional_market = $this->getRequest()->getParam('regional_market');
        $company = $this->getRequest()->getParam('company');
        $status = $this->getRequest()->getParam('status');
        $department = $this->getRequest()->getParam('department');
        $team = $this->getRequest()->getParam('team');
        $group = $this->getRequest()->getParam('group');
        $title = trim($this->getRequest()->getParam('title'));
        $from_date = $this->getRequest()->getParam('from_date');
        $transfer_id = $this->getRequest()->getParam('transfer_id');

        if (!$company)
        {
            exit(json_encode(array('status' => 0, 'message' => 'Company is required')));
        }

        if (!$area_id)
        {
            exit(json_encode(array('status' => 0, 'message' => 'Area is required')));
        }

        if (!$regional_market)
        {
            exit(json_encode(array('status' => 0, 'message' => 'Province is required')));
        }


        if (!$department)
        {
            exit(json_encode(array('status' => 0, 'message' => 'Department is required')));
        }

        /*
        if(!$status){
        exit(
        json_encode(array('status'=>0,'message'=>'Status is required'))
        );
        }
        */

        if (!$team)
        {
            exit(json_encode(array('status' => 0, 'message' => 'Team is required')));
        }


        if (!$group)
        {
            exit(json_encode(array('status' => 0, 'message' => 'Group is required')));
        }

        /*
        if(!$title){
        exit(
        json_encode(array('status'=>0,'message'=>'Title is required'))
        );
        }
        */
        if (!$from_date)
        {
            exit(json_encode(array('status' => 0, 'message' => 'From date is required')));
        }

        $transfer_data = array();
        $transfer_data['staff_id'] = $staff_id;
        $transfer_data['from_date'] = $from_date;
        $transfer_data['transfer_id'] = $transfer_id;
        $transfer_data['info'] = array(
            My_Staff_Info_Type::Area => $area_id,
            My_Staff_Info_Type::Region => $regional_market,
            My_Staff_Info_Type::Company => $company,
            My_Staff_Info_Type::Department => $department,
            My_Staff_Info_Type::Team => $team,
            My_Staff_Info_Type::Group => $group,
            My_Staff_Info_Type::Title => $title);

        $option = 'add';
        if ($transfer_id)
        {
            $option = 'update';
        }
        $result = My_Staff_Log::transfer($staff_id, $transfer_data, $option);

        if ($result)
        {
            exit(json_encode(array(
                'status' => 1,
                'message' => 'Success',
                )));

        } else
        {
            exit(json_encode(array('status' => 0, 'message' =>
                    'Can not update, please try again!')));
        }
    }

    public function delTransferAction()
    {
        $transfer_id = $this->getRequest()->getParam('transfer_id');
        exit(json_encode(My_Staff_Log::delete($transfer_id)));
    }

    public function updateTransferDataAction()
    {
        /**
         * @for: admin
         * @description: init data transfer
         */
        $this->_helper->layout()->disableLayout(true);
        $this->_helper->viewRenderer->setNoRender(true);
        $QStaffTransfer = new Application_Model_StaffTransfer();
        $QStaffLogDetail = new Application_Model_StaffLogDetail();
        $db = Zend_Registry::get('db');
        $userStorage = Zend_Auth::getInstance()->getStorage()->read();
        $select = $db->select()->from(array('s' => 'staff'), array('s.*'));
        $result = $db->fetchAll($select);

        $db->beginTransaction();
        $date = date('Y-m-d');
        try
        {
            foreach ($result as $item)
            {
                $dataTransfer = array(
                    'staff_id' => $item['id'],
                    'from_date' => $item['joined_at'],
                    'created_at' => $date,
                    'created_by' => $userStorage->id,
                    'note' => 'initFirstData',
                    );
                $transfer_id = $QStaffTransfer->insert($dataTransfer);

                $selectRegion = $db->select()->from(array('r' => 'regional_market'), 'r.*')->
                    where('r.id = ?', intval($item['regional_market']));

                $rowRegion = $db->fetchRow($selectRegion);

                $arrInfoType = array(
                    My_Staff_Info_Type::Company => $item['company_id'],
                    My_Staff_Info_Type::Area => $rowRegion['area_id'],
                    My_Staff_Info_Type::Region => $item['regional_market'],
                    My_Staff_Info_Type::Department => $item['department'],
                    My_Staff_Info_Type::Team => $item['team'],
                    My_Staff_Info_Type::Group => $item['group_id'],
                    My_Staff_Info_Type::Title => $item['title'],
                    );

                //update staff log detail
                foreach ($arrInfoType as $key => $value)
                {
                    $dataLog = array(
                        'transfer_id' => $transfer_id,
                        'object' => $item['id'],
                        'from_date' => (strtotime($item['joined_at'])) ? strtotime($item['joined_at']) : null,
                        'info_type' => $key,
                        'current_value' => ($key == My_Staff_Info_Type::Title) ? trim($value) : intval($value),
                        );
                    $QStaffLogDetail->insert($dataLog);
                }

            }
            $db->commit();
        }
        catch (exception $e)
        {
            $db->rollBack();
            exit(json_encode(array('code' => 1, 'message' => $e->getMessage())));
        }
        exit(json_encode(array('code' => 2, 'message' => 'Success')));

    }

    public function transferReportAction()
    {
        $from = $this->getRequest()->getParam('from');
        $to = $this->getRequest()->getParam('to');
        $page = $this->getRequest()->getParam('page', 1);
        $export = $this->getRequest()->getParam('export');
        $QStaff = new Application_Model_Staff();
        $limit = LIMITATION;
        $total = 0;
        $params = array('from' => $from, 'to' => $to);

        if ($export)
        {
            $transfers = $QStaff->fetchPaginationTransfer($page, null, $total, $params);
            $this->_exportTransfer($transfers);
        }
        $transfers = $QStaff->fetchPaginationTransfer($page, $limit, $total, $params);

        $this->view->transfers = $transfers;
        $this->view->limit = $limit;
        $this->view->total = $total;
        $this->view->url = HOST . 'group/' . ($params ? '?' . http_build_query($params) .
            '&' : '?');
        $this->view->offset = $limit * ($page - 1);

    }

    private function _exportTransfer($data)
    {
        $db = Zend_Registry::get('db');
        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();
        $heads = array(
            'STT',
            'Staff Code',
            'Staff Name',
            'Old company',
            'Old Department',
            'Old Team',
            'Old Title',
            'Old Area',
            'Old Province',
            'Old Group',
            'Transfer Time',
            'company',
            'Department',
            'Team',
            'Title',
            'Area',
            'Province',
            'Group');

        $PHPExcel->setActiveSheetIndex(0);
        $sheet = $PHPExcel->getActiveSheet();
        $alpha = 'A';
        $index = 1;
        foreach ($heads as $key)
        {
            $sheet->setCellValue($alpha . $index, $key);
            $alpha++;
        }
        $index = 2;
        $intCount = 1;
        $color = 'FF0000';
        try
        {
            foreach ($data as $item)
            {
                $alpha = 'A';
                $arrCurrent = array_combine(explode(',', $item['info_types']), explode(',', $item['current_values']));
                $arrOld = array_combine(explode(',', $item['info_types']), explode(',', $item['old_values']));
                $sheet->setCellValue($alpha++ . $index, $intCount++);
                $sheet->getCell($alpha++ . $index)->setValueExplicit($item['staff_code'],
                    PHPExcel_Cell_DataType::TYPE_STRING);
                $sheet->getCell($alpha++ . $index)->setValueExplicit($item['fullname'],
                    PHPExcel_Cell_DataType::TYPE_STRING);

                // old value
                $select = $db->select()->from(array('c' => 'company'), array('name'))->where('id = ?',
                    $arrOld[My_Staff_Info_Type::Company]);
                $company_name = $db->fetchOne($select);
                $sheet->getCell($alpha . $index)->setValueExplicit($company_name,
                    PHPExcel_Cell_DataType::TYPE_STRING); //company
                if ($arrOld[My_Staff_Info_Type::Company] != $arrCurrent[My_Staff_Info_Type::
                    Company])
                {
                    $sheet->getStyle($alpha . $index)->applyFromArray(array('fill' => array('type' =>
                                PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('rgb' => $color))));
                }
                $alpha++;
                $select = $db->select()->from(array('t1' => 'team'), array('title' => 't1.name'))->
                    join(array('t2' => 'team'), 't1.parent_id = t2.id', array('team' => 't2.name'))->
                    join(array('t3' => 'team'), 't2.parent_id = t3.id', array('department' =>
                        't3.name'))->where('t1.id = ?', $arrOld[My_Staff_Info_Type::Title]);
                $team = $db->fetchRow($select);
                $sheet->getCell($alpha . $index)->setValueExplicit($team['department'],
                    PHPExcel_Cell_DataType::TYPE_STRING); //department
                if ($arrOld[My_Staff_Info_Type::Department] != $arrCurrent[My_Staff_Info_Type::
                    Department])
                {
                    $sheet->getStyle($alpha . $index)->applyFromArray(array('fill' => array('type' =>
                                PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('rgb' => $color))));
                }

                $alpha++;
                $sheet->getCell($alpha . $index)->setValueExplicit($team['team'],
                    PHPExcel_Cell_DataType::TYPE_STRING); //team
                if ($arrOld[My_Staff_Info_Type::Team] != $arrCurrent[My_Staff_Info_Type::Team])
                {
                    $sheet->getStyle($alpha . $index)->applyFromArray(array('fill' => array('type' =>
                                PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('rgb' => $color))));
                }

                $alpha++;
                $sheet->getCell($alpha . $index)->setValueExplicit($team['title'],
                    PHPExcel_Cell_DataType::TYPE_STRING); //title
                if ($arrOld[My_Staff_Info_Type::Title] != $arrCurrent[My_Staff_Info_Type::Title])
                {
                    $sheet->getStyle($alpha . $index)->applyFromArray(array('fill' => array('type' =>
                                PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('rgb' => $color))));
                }

                $alpha++;
                $select = $db->select()->from(array('r' => 'regional_market'), array('regional_name' =>
                        'r.name', 'area_name' => 'a.name'))->join(array('a' => 'area'),
                    'a.id = r.area_id', array())->where('r.id = ?', $arrOld[My_Staff_Info_Type::
                    Region]);
                $regional = $db->fetchRow($select);
                $sheet->getCell($alpha . $index)->setValueExplicit($regional['area_name'],
                    PHPExcel_Cell_DataType::TYPE_STRING); //area
                if ($arrOld[My_Staff_Info_Type::Area] != $arrCurrent[My_Staff_Info_Type::Area])
                {
                    $sheet->getStyle($alpha . $index)->applyFromArray(array('fill' => array('type' =>
                                PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('rgb' => $color))));
                }

                $alpha++;
                $sheet->getCell($alpha . $index)->setValueExplicit($regional['regional_name'],
                    PHPExcel_Cell_DataType::TYPE_STRING); //region
                if ($arrOld[My_Staff_Info_Type::Region] != $arrCurrent[My_Staff_Info_Type::
                    Region])
                {
                    $sheet->getStyle($alpha . $index)->applyFromArray(array('fill' => array('type' =>
                                PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('rgb' => $color))));
                }
                $alpha++;
                $select = $db->select()->from(array('g' => 'group'), array('name'))->where('g.id = ?',
                    $arrOld[My_Staff_Info_Type::Group]);
                $group_name = $db->fetchOne($select);
                $sheet->getCell($alpha . $index)->setValueExplicit($group_name,
                    PHPExcel_Cell_DataType::TYPE_STRING); //group
                if ($arrOld[My_Staff_Info_Type::Group] != $arrCurrent[My_Staff_Info_Type::Group])
                {
                    $sheet->getStyle($alpha . $index)->applyFromArray(array('fill' => array('type' =>
                                PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('rgb' => $color))));
                }

                $alpha++;
                $sheet->getCell($alpha . $index)->setValueExplicit(date('d/m/Y', strtotime($item['transfer_time'])),
                    PHPExcel_Cell_DataType::TYPE_STRING); //time transfer
                $alpha++;
                //current transfer
                $select = $db->select()->from(array('c' => 'company'), array('name'))->where('c.id = ?',
                    $arrCurrent[My_Staff_Info_Type::Company]);
                $company_name = $db->fetchOne($select);
                $sheet->getCell($alpha . $index)->setValueExplicit($company_name,
                    PHPExcel_Cell_DataType::TYPE_STRING); //company
                if ($arrOld[My_Staff_Info_Type::Company] != $arrCurrent[My_Staff_Info_Type::
                    Company])
                {
                    $sheet->getStyle($alpha . $index)->applyFromArray(array('fill' => array('type' =>
                                PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('rgb' => $color))));
                }
                $alpha++;
                $select = $db->select()->from(array('t1' => 'team'), array('title' => 't1.name'))->
                    join(array('t2' => 'team'), 't1.parent_id = t2.id', array('team' => 't2.name'))->
                    join(array('t3' => 'team'), 't2.parent_id = t3.id', array('department' =>
                        't3.name'))->where('t1.id = ?', $arrCurrent[My_Staff_Info_Type::Title]);
                $team = $db->fetchRow($select);
                $sheet->getCell($alpha . $index)->setValueExplicit($team['department'],
                    PHPExcel_Cell_DataType::TYPE_STRING); //department
                if ($arrOld[My_Staff_Info_Type::Department] != $arrCurrent[My_Staff_Info_Type::
                    Department])
                {
                    $sheet->getStyle($alpha . $index)->applyFromArray(array('fill' => array('type' =>
                                PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('rgb' => $color))));
                }
                $alpha++;
                $sheet->getCell($alpha . $index)->setValueExplicit($team['team'],
                    PHPExcel_Cell_DataType::TYPE_STRING); //team
                if ($arrOld[My_Staff_Info_Type::Team] != $arrCurrent[My_Staff_Info_Type::Team])
                {
                    $sheet->getStyle($alpha . $index)->applyFromArray(array('fill' => array('type' =>
                                PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('rgb' => $color))));
                }

                $alpha++;
                $sheet->getCell($alpha . $index)->setValueExplicit($team['title'],
                    PHPExcel_Cell_DataType::TYPE_STRING); //title
                if ($arrOld[My_Staff_Info_Type::Title] != $arrCurrent[My_Staff_Info_Type::Title])
                {
                    $sheet->getStyle($alpha . $index)->applyFromArray(array('fill' => array('type' =>
                                PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('rgb' => $color))));
                }
                $alpha++;
                $select = $db->select()->from(array('r' => 'regional_market'), array('regional_name' =>
                        'r.name', 'area_name' => 'a.name'))->join(array('a' => 'area'),
                    'a.id = r.area_id', array())->where('r.id = ?', $arrCurrent[My_Staff_Info_Type::
                    Region]);
                $regional = $db->fetchRow($select);
                $sheet->getCell($alpha . $index)->setValueExplicit($regional['area_name'],
                    PHPExcel_Cell_DataType::TYPE_STRING); //area
                if ($arrOld[My_Staff_Info_Type::Area] != $arrCurrent[My_Staff_Info_Type::Area])
                {
                    $sheet->getStyle($alpha . $index)->applyFromArray(array('fill' => array('type' =>
                                PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('rgb' => $color))));
                }
                $alpha++;
                $sheet->getCell($alpha . $index)->setValueExplicit($regional['regional_name'],
                    PHPExcel_Cell_DataType::TYPE_STRING); //region
                if ($arrOld[My_Staff_Info_Type::Region] != $arrCurrent[My_Staff_Info_Type::
                    Region])
                {
                    $sheet->getStyle($alpha . $index)->applyFromArray(array('fill' => array('type' =>
                                PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('rgb' => $color))));
                }
                $alpha++;
                $select = $db->select()->from(array('g' => 'group'), array('name'))->where('g.id = ?',
                    $arrCurrent[My_Staff_Info_Type::Group]);
                $group_name = $db->fetchOne($select);
                $sheet->getCell($alpha . $index)->setValueExplicit($group_name,
                    PHPExcel_Cell_DataType::TYPE_STRING); //group
                if ($arrOld[My_Staff_Info_Type::Group] != $arrCurrent[My_Staff_Info_Type::Group])
                {
                    $sheet->getStyle($alpha . $index)->applyFromArray(array('fill' => array('type' =>
                                PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('rgb' => $color))));
                }
                $alpha++;
                $index++;
            }
        }
        catch (exception $e)
        {
            echo $e->getMessage();
            exit;
        }
        $filename = 'Transfer_Report_' . date('d_m_Y');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
        $objWriter->save('php://output');
        exit;
    }

    private function _exportPictureAdd($params)
    {
        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();
        $heads = array(
            'STT',
            'Staff Code',
            'Firstname',
            'Lastname',
            'Email',
            'Team',
            'Title',
            'Area');
        $sheet = $PHPExcel->getActiveSheet();
        $alpha = 'A';
        $index = 1;
        foreach ($heads as $key)
        {
            $sheet->setCellValue($alpha . $index, $key);
            $alpha++;
        }

        $db = Zend_Registry::get('db');
        $select = $db->select()->from(array('s' => 'staff'), array(
            's.id',
            's.code',
            's.email',
            's.firstname',
            's.lastname',
            's.contract_expired_at',
            's.regional_market',
            's.title',
            's.department',
            's.team',
            's.contract_term',
        ))->joinLeft(array('r' => 'regional_market'), 's.regional_market = r.id', array('r.area_id'))->where('s.photo IS NULL')->where('s.status = 1',null);

        if (isset($params['name']) && $params['name'])
            $select->where('CONCAT(s.firstname, \' \', s.lastname) LIKE ?', '%' . $params['name'] .
                '%');


        if (isset($params['email']) && $params['email'])
            $select->where('s.email LIKE ?', str_replace(EMAIL_SUFFIX, '', $params['email']) .
                EMAIL_SUFFIX);



        if (isset($params['department']) and $params['department']) {
            if (is_array($params['department']) && count($params['department']) > 0) {
                $select->where('s.department IN (?)', $params['department']);
            } else {
                $select->where('1=0', 1);
            }
        }

        if(empty($params['area_id']))
        {
            $this->_redirect(HOST.'staff/list-basic-record');
        }

        if (isset($params['area_id']) and $params['area_id'] && !(isset($params['regional_market']) and $params['regional_market'])){
            $QRegionalMarket = new Application_Model_RegionalMarket();

            if (is_array($params['area_id']) && count($params['area_id']) > 0) {
                $where = $QRegionalMarket->getAdapter()->quoteInto('area_id IN (?)', $params['area_id']);
            } else {
                $where = $QRegionalMarket->getAdapter()->quoteInto('area_id = ?', $params['area_id']);
            }

            $regional_markets = $QRegionalMarket->fetchAll($where);
            $tem = array();

            foreach ($regional_markets as $regional_market)
                $tem[] = $regional_market->id;

            if (is_array($tem) && count($tem) > 0)
                $select->where('s.regional_market IN (?)', $tem);
            else
                $select->where('1=0', 1);

        } elseif (isset($params['regional_market']) and $params['regional_market']) {
            if (is_array($params['regional_market']) && count($params['regional_market']) > 0) {
                $select->where('s.regional_market IN (?)', $params['regional_market']);
            } else {
                $select->where('1=0', 1);
            }
        }

        $staffs = $db->fetchAll($select);

        $QDepartment = new Application_Model_Department();
        $departments = $QDepartment->get_cache();

        $QTeam = new Application_Model_Team();
        $teams = $QTeam->get_cache();

        $QRegionalMarket = new Application_Model_RegionalMarket();
        $regional_markets = $QRegionalMarket->get_cache();

        $QContractTerm = new Application_Model_ContractTerm();
        $contract_terms = $QContractTerm->get_cache();

        $QArea = new Application_Model_Area();
        $area = $QArea->get_cache();

        $index = 2;
        $intCount = 1;

        try
        {
            if ($staffs)
            {
                foreach ($staffs as $_key => $item)
                {
                    $alpha = 'A';

                    $sheet->setCellValue($alpha++ . $index, $intCount++);
                    $sheet->getCell($alpha++ . $index)->setValueExplicit($item['code'],
                        PHPExcel_Cell_DataType::TYPE_STRING);
                    $sheet->setCellValue($alpha++ . $index, $item['firstname']);
                    $sheet->setCellValue($alpha++ . $index, $item['lastname']);
                    $sheet->setCellValue($alpha++ . $index, $item['email']);
                    $sheet->setCellValue($alpha++ . $index, isset($teams[$item['team']]) ? $teams[$item['team']] :
                        '');
                    $sheet->setCellValue($alpha++ . $index, isset($teams[$item['title']]) ? $teams[$item['title']] :
                        '');
                    $sheet->setCellValue($alpha++  . $index, isset($area[$item['area_id']]) ? $area[$item['area_id']] :
                        '');

                    $sheet->setCellValue($alpha++ . $index, isset($regional_markets[$item['regional_market']]) ?
                        $regional_markets[$item['regional_market']] : '');


                    $index++;
                }
            }
        }
        catch (exception $e)
        {

        }

        $filename = ' Expired Additional Pictures ' . date('d-m-Y H-i-s');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
        $objWriter->save('php://output');
        exit;
    }

    private function _exportContractPrintingLog($params)
    {
        require_once 'PHPExcel.php';
        $staff_print_manager = 3246;

        $PHPExcel = new PHPExcel();
        $heads = array(
            'STT',
            'Staff Code',
            'Firstname',
            'Lastname',
            'Email',
            'Department',
            'Team',
            'Title',
            'Province',
            'Base Salary',
            'Bonus Salary',
            'Food Salary',
            'Fuel Salary',
            'Phone Salary',
            'Probation Salary',
            'Contract',
            'From',
            'To',
            'Time Update',
            'KPI');

        $PHPExcel->setActiveSheetIndex(0);
        $sheet = $PHPExcel->getActiveSheet();
        $alpha = 'A';
        $index = 1;
        foreach ($heads as $key)
        {
            $sheet->setCellValue($alpha . $index, $key);
            $alpha++;
        }

        $db = Zend_Registry::get('db');
        $select = $db->select()->from(array('p' => 'staff_print_log'), array('p.*'))->
            join(array('s' => 'staff'), 's.id=p.object', array(
            's.firstname',
            's.lastname',
            's.email',
            's.code',
            's.team',
            's.department'));

        if (isset($params['name']) && $params['name'])
            $select->where('CONCAT(s.firstname, \' \', s.lastname) LIKE ?', '%' . $params['name'] .
                '%');

        if (isset($params['email']) && $params['email'])
            $select->where('s.email LIKE ?', str_replace(EMAIL_SUFFIX, '', $params['email']) .
                EMAIL_SUFFIX);

        if (isset($params['from']) && $params['from'])
            $select->where('p.time >= ?', DateTime::createFromFormat('d/m/Y', $params['from'])->
                format('Y-m-d 00:00:00'));

        if (isset($params['to']) && $params['to'])
            $select->where('p.time <= ?', DateTime::createFromFormat('d/m/Y', $params['to'])->
                format('Y-m-d 23:59:59'));

        //  $select->where('s.title in (?)' , array(PGPB_TITLE, SALES_LEADER_TILE , SALES_ACCESSORIES_TITLE ,SALES_TITLE));
        $select->where('p.user_id = ? ', $staff_print_manager);

        $data = $db->fetchAll($select);

        $index = 2;
        $intCount = 1;

        $QDepartment = new Application_Model_Department();
        $departments = $QDepartment->get_cache();

        $QTeam = new Application_Model_Team();
        $teams = $QTeam->get_cache();

        $QRegionalMarket = new Application_Model_RegionalMarket();
        $regional_markets = $QRegionalMarket->get_cache();

        $QContractTerm = new Application_Model_ContractTerm();
        $contract_terms = $QContractTerm->get_cache();

        try
        {
            if ($data)
                foreach ($data as $item)
                {
                    $alpha = 'A';

                    $sheet->setCellValue($alpha++ . $index, $intCount++);
                    $sheet->getCell($alpha++ . $index)->setValueExplicit($item['code'],
                        PHPExcel_Cell_DataType::TYPE_STRING);
                    $sheet->setCellValue($alpha++ . $index, $item['firstname']);
                    $sheet->setCellValue($alpha++ . $index, $item['lastname']);
                    $sheet->setCellValue($alpha++ . $index, $item['email']);
                    $sheet->setCellValue($alpha++ . $index, isset($departments[$item['department']]) ?
                        $departments[$item['department']] : '');
                    $sheet->setCellValue($alpha++ . $index, isset($teams[$item['team']]) ? $teams[$item['team']] :
                        '');
                    $sheet->setCellValue($alpha++ . $index, $item['title']);
                    $sheet->setCellValue($alpha++ . $index, isset($regional_markets[$item['regional_market']]) ?
                        $regional_markets[$item['regional_market']] : '');
                    $sheet->setCellValue($alpha++ . $index, $item['base_salary']);
                    $sheet->setCellValue($alpha++ . $index, $item['bonus_salary']);
                    $sheet->setCellValue($alpha++ . $index, $item['allowance_1']);
                    $sheet->setCellValue($alpha++ . $index, $item['allowance_2']);
                    $sheet->setCellValue($alpha++ . $index, $item['allowance_3']);
                    $sheet->setCellValue($alpha++ . $index, $item['probation_salary']);
                    $sheet->setCellValue($alpha++ . $index, isset($contract_terms[$item['contract_term']]) ?
                        $contract_terms[$item['contract_term']] : '');
                    $sheet->setCellValue($alpha++ . $index, isset($item['from_date']) && strtotime($item['from_date']) ?
                        date('m/d/Y', strtotime($item['from_date'])) : '');
                    $sheet->setCellValue($alpha++ . $index, isset($item['to_date']) && strtotime($item['to_date']) ?
                        date('m/d/Y', strtotime($item['to_date'])) : '');
                    $sheet->setCellValue($alpha++ . $index, isset($item['time']) && strtotime($item['time']) ?
                        date('m/d/Y', strtotime($item['time'])) : '');
                    $sheet->setCellValue($alpha++ . $index, $item['kpi']);
                    $index++;
                }
        }
        catch (exception $e)
        {
        }

        $filename = 'Contract Printing Report ' . date('d-m-Y H-i-s');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
        $objWriter->save('php://output');
        exit;
    }

    public function contractRevisionListAction()
    {
        $id = $this->getRequest()->getParam('object');
        $flashMessenger = $this->_helper->flashMessenger;

        try
        {
            if (!$id || !intval($id))
                throw new Exception("Invalid ID", 1);

            $QStaff = new Application_Model_Staff();
            $where = $QStaff->getAdapter()->quoteInto('id = ?', $id);
            $staff_check = $QStaff->fetchRow($where);

            if (!$staff_check)
                throw new Exception("Invalid staff", 2);

            $QStaffPrintLog = new Application_Model_StaffPrintLog();

            $page = $this->getRequest()->getParam('page', 1);
            $total = 0;
            $limit = LIMITATION;
            $params = array('object' => intval($id));
            $params['revision'] = true;

            $this->view->logs = $QStaffPrintLog->fetchPagination($page, $limit, $total, $params);
            $this->view->params = $params;
            $this->view->limit = $limit;
            $this->view->total = $total;

            unset($params['revision']);

            $this->view->url = HOST . 'staff/contract-revision-list' . ($params ? '?' .
                http_build_query($params) . '&' : '?');
            $this->view->offset = $limit * ($page - 1);

            $this->view->staff = $staff_check;

            $this->view->back_url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] :
                'staff/contract-term';
        }
        catch (exception $e)
        {
            $flashMessenger->setNamespace('error')->addMessage(sprintf("[%d] %s", $e->
                getCode(), $e->getMessage()));
            $this->_redirect(HOST . 'staff/contract-term');
        }
    }

    public function contractRevisionAction()
    {
        $id = $this->getRequest()->getParam('id');

        $flashMessenger = $this->_helper->flashMessenger;

        try
        {
            if (!$id)
                throw new Exception("Invalid ID", 1);

            $QStaffPrintLog = new Application_Model_StaffPrintLog();
            $where = $QStaffPrintLog->getAdapter()->quoteInto('id = ?', $id);
            $log = $QStaffPrintLog->fetchRow($where);

            if (!$log)
                throw new Exception("Invalid revision", 2);

            $QSalarySales = new Application_Model_SalarySales();
            $QSalaryPG = new Application_Model_SalaryPg();
            $QContract = new Application_Model_ContractTerm();
            $QStaffAddress = new Application_Model_StaffAddress();
            $QRegionalMarket = new Application_Model_RegionalMarket();
            $regional_markets = $QRegionalMarket->get_cache_all();
            $QStaff = new Application_Model_Staff();

            $where = $QStaff->getAdapter()->quoteInto('id = ?', $log['object']);
            $staff = $QStaff->fetchRow($where);

            if (!$staff)
                throw new Exception("Invalid staff", 3);

            $contract_name = $QContract->get_cache();

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

            if ($staff['title'] == 'SALE LEADER')
            {
                $staff['title'] = 'Nhân viên kinh doanh';
            }

            $start = $staff['contract_signed_at'];
            $end = $staff['contract_expired_at'];
            $start = $end;

            $luong = array();

            //sale team
            if ($staff['team'] == '75' || $staff['team'] == 147)
            {
                $where = array();
                $where[] = $QSalarySales->getAdapter()->quoteInto('province_id = ?', $staff['regional_market']);
                $luong[$staff['id']] = $QSalarySales->fetchRow($where);
            }

            //pg team
            if ($staff['team'] == '16')
            {
                $where1 = array();
                $where1[] = $QSalaryPG->getAdapter()->quoteInto('province_id = ?', $staff['regional_market']);
                $luong[$staff['id']] = $QSalaryPG->fetchRow($where1);
            }

            $this->view->contract_name = $contract_name;
            $this->view->luong = $luong;
            $staffs = array($staff);
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
            $this->view->teams = $QModel->fetchAll();

            $this->_helper->layout->disableLayout();
            $this->_helper->viewRenderer->setRender('print');
        }
        catch (exception $e)
        {
            $flashMessenger->setNamespace('error')->addMessage(sprintf("[%d] %s", $e->
                getCode(), $e->getMessage()));
            $this->_redirect(HOST . 'staff/contract-term');
        }
    }

    private function _exportExpiredContract($params)
    {
        require_once 'PHPExcel.php';
        $PHPExcel = new PHPExcel();
        $heads = array(
            'STT',
            'Staff Code',
            'Firstname',
            'Lastname',
            'Email',
            'Department',
            'Team',
            'Title',
            'Province',
            'Contract Type',
            'Expired',
            );

        $PHPExcel->setActiveSheetIndex(0);
        $sheet = $PHPExcel->getActiveSheet();
        $alpha = 'A';
        $index = 1;
        foreach ($heads as $key)
        {
            $sheet->setCellValue($alpha . $index, $key);
            $alpha++;
        }

        $db = Zend_Registry::get('db');
        $select = $db->select()->from(array('s' => 'staff'), array(
            's.id',
            's.code',
            's.email',
            's.firstname',
            's.lastname',
            's.contract_expired_at',
            's.regional_market',
            's.title',
            's.department',
            's.team',
            's.contract_term'))->where('s.off_date IS NULL');

        if (isset($params['name']) && $params['name'])
            $select->where('CONCAT(s.firstname, \' \', s.lastname) LIKE ?', '%' . $params['name'] .
                '%');

        if (isset($params['email']) && $params['email'])
            $select->where('s.email LIKE ?', str_replace(EMAIL_SUFFIX, '', $params['email']) .
                EMAIL_SUFFIX);

        if (isset($params['from']) && $params['from'])
            $select->where('s.contract_expired_at >= ?', DateTime::createFromFormat('d/m/Y',
                $params['from'])->format('Y-m-d 00:00:00'));

        if (isset($params['to']) && $params['to'])
            $select->where('s.contract_expired_at <= ?', DateTime::createFromFormat('d/m/Y',
                $params['to'])->format('Y-m-d 23:59:59'));

        $staffs = $db->fetchAll($select);

        $QDepartment = new Application_Model_Department();
        $departments = $QDepartment->get_cache();

        $QTeam = new Application_Model_Team();
        $teams = $QTeam->get_cache();

        $QRegionalMarket = new Application_Model_RegionalMarket();
        $regional_markets = $QRegionalMarket->get_cache();

        $QContractTerm = new Application_Model_ContractTerm();
        $contract_terms = $QContractTerm->get_cache();

        $index = 2;
        $intCount = 1;

        try
        {
            if ($staffs)
            {
                foreach ($staffs as $_key => $item)
                {
                    $alpha = 'A';

                    $sheet->setCellValue($alpha++ . $index, $intCount++);
                    $sheet->getCell($alpha++ . $index)->setValueExplicit($item['code'],
                        PHPExcel_Cell_DataType::TYPE_STRING);
                    $sheet->setCellValue($alpha++ . $index, $item['firstname']);
                    $sheet->setCellValue($alpha++ . $index, $item['lastname']);
                    $sheet->setCellValue($alpha++ . $index, $item['email']);
                    $sheet->setCellValue($alpha++ . $index, isset($departments[$item['department']]) ?
                        $departments[$item['department']] : '');
                    $sheet->setCellValue($alpha++ . $index, isset($teams[$item['team']]) ? $teams[$item['team']] :
                        '');
                    $sheet->setCellValue($alpha++ . $index, $item['title']);
                    $sheet->setCellValue($alpha++ . $index, isset($regional_markets[$item['regional_market']]) ?
                        $regional_markets[$item['regional_market']] : '');
                    $sheet->setCellValue($alpha++ . $index, isset($contract_terms[$item['contract_term']]) ?
                        $contract_terms[$item['contract_term']] : '');
                    $sheet->setCellValue($alpha++ . $index, isset($item['contract_expired_at']) &&
                        strtotime($item['contract_expired_at']) ? date('m/d/Y', strtotime($item['contract_expired_at'])) :
                        '');

                    $index++;
                }
            }
        }
        catch (exception $e)
        {

        }

        $filename = ' Expired Contract Report ' . date('d-m-Y H-i-s');
        $objWriter = new PHPExcel_Writer_Excel2007($PHPExcel);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
        $objWriter->save('php://output');
        exit;
    }

    public function delPrintLogAction()
    {
        $id = $this->getRequest()->getParam('id');
        $object = $this->getRequest()->getParam('object');
        $flashMessenger = $this->_helper->flashMessenger;
        $QStaffPrintLog = new Application_Model_StaffPrintLog();
        $db = Zend_Registry::get('db');
        $row = $QStaffPrintLog->find($id)->current();

        if ($row)
        {
            $db->beginTransaction();
            try
            {
                $row->delete();
                $db->commit();
                $flashMessenger->setNamespace('success')->addMessage('Done!');
                $this->_redirect(HOST . 'staff/print-log?object=' . $object);
            }
            catch (exception $e)
            {
                $db->rollBack();
                $flashMessenger->setNamespace('error')->addMessage($e->getMessage());
                $this->_redirect(HOST . 'staff/print-log?object=' . $object);
            }
        } else
        {
            $flashMessenger->setNamespace('error')->addMessage('Print Log not existed!');
            $this->_redirect(HOST . 'staff/print-log?object=' . $object);
        }


    }

    public function uploadPhotoAction()
    {
        require_once 'staff' . DIRECTORY_SEPARATOR . 'upload-photo.php';
    }

    public function importstaffAction() {

        $back_url = $this->getRequest()->getParam('back_url');
        $upload = new Zend_File_Transfer();

        $uploaded_dir = APPLICATION_PATH . DIRECTORY_SEPARATOR . '..' 
                . DIRECTORY_SEPARATOR .'public' . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'upload_staff' 
                . DIRECTORY_SEPARATOR . date('Y-m');

        if (!is_dir($uploaded_dir))
            @mkdir($uploaded_dir, 0777, true);

        // check file's name
        $path_info  = pathinfo($upload->getFileName());
        $filename   = $path_info['filename'];
        $extension  = $path_info['extension'];

        require_once 'PHPExcel.php';
                    $cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp; 
                    $cacheSettings = array( 'memoryCacheSize' => '8MB'); 
                    PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings); 
            
        switch ($extension) {
                case 'xlsx':
                    $objReader = PHPExcel_IOFactory::createReader('Excel2007'); 
                    break;
                default:
                    $flashMessenger = $this->_helper->flashMessenger;
                    $flashMessenger->setNamespace('error')->addMessage('Upload Staff Fail: Invalid file extension');
                    unlink($uploaded_dir . DIRECTORY_SEPARATOR . $filename.'.'.$extension);
                    $this->_redirect(($back_url ? $back_url : HOST . 'staff'));
                    break;
            }

        $old_name = $filename.'.'.$extension;
        $new_name = 'UPLOAD_staff_'.date('Y-m-d H-i-s').'.'.$extension;

        // rename before upload
        $upload->addFilter('Rename',
                   array('target' => $uploaded_dir. DIRECTORY_SEPARATOR .$new_name,
                         'overwrite' => true));

        //$upload->setDestination($uploaded_dir);
        $upload->receive();

        chmod($uploaded_dir. DIRECTORY_SEPARATOR .$new_name, 777);
/*
        if (is_file($uploaded_dir . DIRECTORY_SEPARATOR . $old_name)){
            rename($uploaded_dir . DIRECTORY_SEPARATOR . $old_name, $uploaded_dir . DIRECTORY_SEPARATOR . $new_name);
        } else {
            $new_name = $old_name;
        }
*/
        $objReader->setReadDataOnly(true); 
        $objPHPExcel = $objReader->load($uploaded_dir . DIRECTORY_SEPARATOR . $new_name);
        $objWorksheet = $objPHPExcel->setActiveSheetIndex(0);

        $highestRow = $objWorksheet->getHighestRow(); // e.g. 10
        $total_order_row = $highestRow - STORE_CODE_LIST_ROW_START + 1;

        $highestColumn = $objWorksheet->getHighestColumn(); // e.g 'F'
        $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn); // e.g. 5

        $OpenFile = $uploaded_dir.DIRECTORY_SEPARATOR.$new_name;

        try {
            $objPHPExcel = PHPExcel_IOFactory::load($OpenFile);
        } catch(Exception $e) {
            die('Error loading file :' . $e->getMessage());
        }

        $sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);

        $QStaff = new Application_Model_Staff();
        $importstaff = $QStaff->insertstaffimport($sheetData, $old_name, $new_name);

        $flashMessenger = $this->_helper->flashMessenger;

        switch ($importstaff['result']) {
            case "success":
                $flashMessenger->setNamespace('success')->addMessage('Upload Staff Successful: Totals '. $importstaff['cnt'] . ' Staff!');
                break;
            case "fail":
                $flashMessenger->setNamespace('error')->addMessage('Upload Staff Fail: '. $importstaff['msg']);
                unlink($uploaded_dir . DIRECTORY_SEPARATOR . $new_name);
                break;
        }

        $this->_redirect(($back_url ? $back_url : HOST . 'staff'));
    }

    public function importPcUnpunishAction() {

        $month_year = date('Y-m-d', strtotime($_POST['txt_month_year']));
        $back_url = $this->getRequest()->getParam('back_url');

        $upload = new Zend_File_Transfer();

        $uploaded_dir = APPLICATION_PATH . DIRECTORY_SEPARATOR . '..' 
                . DIRECTORY_SEPARATOR .'public' . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'upload_pc_unpunish' 
                . DIRECTORY_SEPARATOR . date('Y-m');

        if (!is_dir($uploaded_dir))
            @mkdir($uploaded_dir, 0777, true);

        // check file's name
        $path_info  = pathinfo($upload->getFileName());
        $filename   = $path_info['filename'];
        $extension  = $path_info['extension'];

        require_once 'PHPExcel.php';
                    $cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp; 
                    $cacheSettings = array( 'memoryCacheSize' => '8MB'); 
                    PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings); 
            
        switch ($extension) {
                case 'xlsx':
                    $objReader = PHPExcel_IOFactory::createReader('Excel2007'); 
                    break;
                default:
                    $flashMessenger = $this->_helper->flashMessenger;
                    $flashMessenger->setNamespace('error')->addMessage('Upload PC Un-Punish Fail: Invalid file extension');
                    unlink($uploaded_dir . DIRECTORY_SEPARATOR . $filename.'.'.$extension);
                    $this->_redirect(($back_url ? $back_url : HOST . 'staff'));
                    break;
            }

        $old_name = $filename.'.'.$extension;
        $new_name = 'UPLOAD_PC_UnPunish_'.date('Y-m-d H-i-s').'.'.$extension;

        // rename before upload
        $upload->addFilter('Rename',
                   array('target' => $uploaded_dir. DIRECTORY_SEPARATOR .$new_name,
                         'overwrite' => true));

        //$upload->setDestination($uploaded_dir);
        $upload->receive();

        chmod($uploaded_dir. DIRECTORY_SEPARATOR .$new_name, 777);
/*
        if (is_file($uploaded_dir . DIRECTORY_SEPARATOR . $old_name)){
            rename($uploaded_dir . DIRECTORY_SEPARATOR . $old_name, $uploaded_dir . DIRECTORY_SEPARATOR . $new_name);
        } else {
            $new_name = $old_name;
        }
*/
        $objReader->setReadDataOnly(true); 
        $objPHPExcel = $objReader->load($uploaded_dir . DIRECTORY_SEPARATOR . $new_name);
        $objWorksheet = $objPHPExcel->setActiveSheetIndex(0);

        $highestRow = $objWorksheet->getHighestRow(); // e.g. 10
        $total_order_row = $highestRow - STORE_CODE_LIST_ROW_START + 1;

        $highestColumn = $objWorksheet->getHighestColumn(); // e.g 'F'
        $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn); // e.g. 5

        $OpenFile = $uploaded_dir.DIRECTORY_SEPARATOR.$new_name;

        try {
            $objPHPExcel = PHPExcel_IOFactory::load($OpenFile);
        } catch(Exception $e) {
            die('Error loading file :' . $e->getMessage());
        }

        $sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);

        $QStaffUnPunish = new Application_Model_StaffUnPunish();
        $importstaff = $QStaffUnPunish->insertstaffimport($sheetData, $old_name, $new_name, $month_year);

        $flashMessenger = $this->_helper->flashMessenger;

        switch ($importstaff['result']) {
            case "success":
                $flashMessenger->setNamespace('success')->addMessage('Upload PC Un-Punish Successful: Totals '. $importstaff['cnt'] . ' Staff!');
                break;
            case "fail":
                $flashMessenger->setNamespace('error')->addMessage('Upload PC Un-Punish Fail: '. $importstaff['msg']);
                unlink($uploaded_dir . DIRECTORY_SEPARATOR . $new_name);
                break;
        }

        $this->_redirect(($back_url ? $back_url : HOST .'manage/pc-unpunish?month_year='.date('F Y', strtotime($month_year))));
    }

    public function apiSavePcFirstTrainingAction() {

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $params = json_decode($this->getRequest()->getParam('params'),true);

        $db = Zend_Registry::get('db');
        $db->beginTransaction();

        try {

            for ($i=0;$i<count($params);$i++) {

                //Check Staff Exists
                $select_staff = $db->select()
                    ->from(array('pft' => 'pc_first_training'), array('pft_id' => 'pft.id'))
                    ->where('pft.public_id = ?', $params[$i]['public_id']);

                //echo $select_staff;
                $staff_check = $db->fetchRow($select_staff);

                $data = array(
                    'gender'            => $params[$i]['gender'],
                    'prefix'            => $params[$i]['prefix'],
                    'firstname'         => $params[$i]['firstname'],
                    'lastname'          => $params[$i]['lastname'],
                    'firstname_en'      => $params[$i]['firstname_en'],
                    'lastname_en'       => $params[$i]['lastname_en'],
                    'public_id'         => $params[$i]['public_id'],
                    'marital_status'    => $params[$i]['marital_status'],
                    'province_id'       => $params[$i]['province_id'],
                    'check_in'          => $params[$i]['check_in'],
                    'email'             => $params[$i]['email'],
                    'phone_number'      => $params[$i]['phone_number'],
                    'dob'               => $params[$i]['dob'],
                    'shirt_size'        => $params[$i]['shirt_size'],
                    'sso_hospital_1'    => $params[$i]['sso_hospital_1'],
                    'sso_hospital_2'    => $params[$i]['sso_hospital_2'],
                    'sso_hospital_3'    => $params[$i]['sso_hospital_3'],
                    'filepic'           => $params[$i]['filepic'],
                    'joined_at'         => $params[$i]['joined_at'],
                    'created_by'        => $params[$i]['created_by'],
                    'created_at'        => $params[$i]['created_at'],
                    'group_id'          => $params[$i]['group_id'],
                );

                if (isset($staff_check['pft_id']) && $staff_check['pft_id']) {

                    $where = array();
                    $where['public_id = ?'] = $params[$i]['public_id'];
                    $db->update('pc_first_training', $data, $where);

                } else {

                    $db->insert('pc_first_training', $data);
                }

            }
            
            $db->commit();

            $msg = 'SUCCESS';

        } catch (Exception $e) {
            $db->rollBack();
            $msg = 'FAIL : '.$e;
        }

        echo $msg;
        exit;
    }

    public function apiGetStaffInfoAction() {

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $staff_code = $this->getRequest()->getParam('staff_code');
    
        $QStaff = new Application_Model_Staff();
        $result = $QStaff->getStaffCreateHr($staff_code);

        echo $result;
        exit;
    }

    public function apiUpdateGroupByHrChangePositionAction() {

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
    
        $QStaff = new Application_Model_Staff();
        $result = $QStaff->updateGroupByHrChangePosition();

        exit;
    }

    public function apiGetAllStaffByStoreAction() {

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $store_id = $this->getRequest()->getParam('store_id');
    
        $QAsm = new Application_Model_Asm();
        $result = $QAsm->getAllStaffByStoreID($store_id);

        echo $result;
        exit;
    }

    public function apiGetAreaByStaffCodeAction() {

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $staff_code = $this->getRequest()->getParam('staff_code');
    
        $QAsm = new Application_Model_Asm();
        $result = $QAsm->getAreaByStaffCode($staff_code);

        echo json_encode($result);
        exit;
    }

    public function apiGetMarketNameByStaffCodeAction() {

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $staff_code = $this->getRequest()->getParam('staff_code');
    
        $QMarketName = new Application_Model_MarketName();
        $result = $QMarketName->getMarketNameByStaffCode($staff_code);

        echo json_encode($result);
        exit;
    }

    public function apiGetStoreByMarketNameStaffAction() {

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $staff_code     = $this->getRequest()->getParam('staff_code');
        $market_name_id = $this->getRequest()->getParam('market_name_id');

        $params = array(
            'staff_code'     => $staff_code,
            'market_name_id' => $market_name_id,
        );
    
        $QMarketName = new Application_Model_MarketName();
        $result = $QMarketName->getStoreByMarketNameStaff($params);

        echo json_encode($result);
        exit;
    }

    public function apiGetAmInfoByStaffCodeAction() {

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $staff_code = $this->getRequest()->getParam('staff_code');
    
        $QAm = new Application_Model_Am();
        $result = $QAm->getKaByStaffCode($staff_code);

        echo json_encode($result);
        exit;
    }

    public function apiGetRdCodeByStaffCodeAction() {

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $staff_code = $this->getRequest()->getParam('staff_code');
    
        $QAsm = new Application_Model_Asm();
        $result = $QAsm->getRdCodeByStaffCode($staff_code);

        echo json_encode($result);
        exit;
    }

}
