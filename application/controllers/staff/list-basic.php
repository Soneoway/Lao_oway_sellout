<?php
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
$is_officer = $this->getRequest()->getParam('is_officer');
$date = $this->getRequest()->getParam('date');
$month = $this->getRequest()->getParam('month');
$year = $this->getRequest()->getParam('year');
$tags = $this->getRequest()->getParam('tags');
$title = $this->getRequest()->getParam('title');
$company_id = $this->getRequest()->getParam('company_id');
$need_approve = $this->getRequest()->getParam('need_approve');

if (!($tags and is_array($tags)))
    $tags = null;

if (!$sname)
    $limit = LIMITATION;
else
    $limit = null;

$QRegionalMarket = new Application_Model_RegionalMarket();

// check quyen view: neu la team sales admin moi gioi han
$listAreaIds = array();
if (CHECK_USER_EDIT_AREA){
    $userStorage = Zend_Auth::getInstance()->getStorage()->read();
    $QAsm = new Application_Model_Asm();

    // asm thi quan ly nhieu khuu vuc
    if($userStorage->title == SALES_ADMIN_TITLE)
    {
        $cachedASM     = $QAsm->get_cache($userStorage->id);
        $tem = $cachedASM['area'];
        if ($tem)
            $listAreaIds = $tem;
        else
            $listAreaIds = -1;
    }
}

$total = 0;

/*$is_officer = 2;*/

$params = array(
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
    'date' => $date,
    'month' => $month,
    'year' => $year,
    'is_officer' => $is_officer,
    'sname' => $sname,
    'tags' => $tags,
    'title' => $title,
    'company_id' => $company_id,
    'need_approve' => $need_approve,
    'listAreaIds' => $listAreaIds,
    'listTitles' => array(
        SALE_SALE_ASM,
        SALE_SALE_ASM_STANDBY,
        SALE_SALE_PGPB,
        SALE_SALE_SALE,
        SALE_SALE_SALE_LEADER,
        SALE_SALE_SALE_ADMIN,
        SALE_SALE_DELIVERY,

        SALE_ACCESSORIES_SALE_LEADER,
        SALE_ACCESSORIES_SALE,
        SALE_ACCESSORIES_SALE_ADMIN,

        SALE_DIGITAL_SALE_LEADER,
        SALE_DIGITAL_SALE,
        SALE_DIGITAL_SALE_ADMIN,
    ),
);

$params['sort'] = $sort;
$params['desc'] = $desc;

$QArea = new Application_Model_Area();
$this->view->areas = $QArea->fetchAll(null, 'name');

if ($area_id) {
    if (is_array($area_id) && count($area_id))
        $where = $QRegionalMarket->getAdapter()->quoteInto('area_id IN (?)', $area_id);
    else
        $where = $QRegionalMarket->getAdapter()->quoteInto('area_id = ?', $area_id);

    $this->view->regional_markets = $QRegionalMarket->fetchAll($where, 'name');
}

if ($regional_market) {
    if (is_array($regional_market) && count($regional_market))
        $where = $QRegionalMarket->getAdapter()->quoteInto('parent IN (?)', $regional_market);
    else
        $where = $QRegionalMarket->getAdapter()->quoteInto('parent = ?', $regional_market);

    $this->view->districts = $QRegionalMarket->fetchAll($where, 'name');
}

$QStaffTemp = new Application_Model_StaffTemp();

$staffs = $QStaffTemp->fetchStaffPagination($page, $limit, $total, $params);


$this->view->listAreaIds = $listAreaIds;
$this->view->desc = $desc;
$this->view->sort = $sort;
$this->view->staffs = $staffs;
$this->view->params = $params;
$this->view->limit = $limit;
$this->view->total = $total;
$this->view->url = HOST . 'staff/list-basic/' . ($params ? '?' . http_build_query($params) .
        '&' : '?');

$this->view->offset = $limit * ($page - 1);

$flashMessenger = $this->_helper->flashMessenger;
$messages = $flashMessenger->setNamespace('success')->getMessages();
$this->view->messages_success = $messages;

$messages = $flashMessenger->setNamespace('error')->getMessages();
$this->view->messages = $messages;

$this->view->current_url = trim($this->getRequest()->getRequestUri(), '/');

$QTeam = new Application_Model_Team();
$recursiveDeparmentTeamTitle = $QTeam->get_recursive_cache();

$this->view->recursiveDeparmentTeamTitle = $recursiveDeparmentTeamTitle;

$QCompany = new Application_Model_Company();
$this->view->companies = $QCompany->get_cache();