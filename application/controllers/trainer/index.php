<?php
$sort               = $this->getRequest()->getParam('sort', '');
$desc               = $this->getRequest()->getParam('desc', 1);
$page               = $this->getRequest()->getParam('page', 1);
$name               = $this->getRequest()->getParam('name');
$area_id            = $this->getRequest()->getParam('area_id',null);
$regional_market   = $this->getRequest()->getParam('regional_market',null);
$code               = $this->getRequest()->getParam('code');
$email              = $this->getRequest()->getParam('email');
$date               = $this->getRequest()->getParam('date');
$month              = $this->getRequest()->getParam('month');
$year               = $this->getRequest()->getParam('year');
$title              = $this->getRequest()->getParam('title');
$userStorage        = Zend_Auth::getInstance()->getStorage()->read();
$flashMessenger     = $this->_helper->flashMessenger;

$TITLE_PGPB_SALE    = array(PGPB_TITLE, SALE_SALE_SALE);

if($title)
{
    $TITLE_PGPB_SALE    =  array($title);
    $this->view->title  = $title;
}

$total              = 0;
$limit              = LIMITATION;

$area_id_login      = array();

$QStaffTrainer          = new Application_Model_StaffTrainer();

$params = array_filter(array(
    'name'              => $name,
    'title'             => $TITLE_PGPB_SALE,
    'area_id'           => $area_id,
    'regional_market'   => $regional_market,
    'code'              => $code,
    'email'             => $email,
    'date'              => $date,
    'month'             => $month,
    'year'              => $year,
    'off'               => My_Staff_Status::On
));
$params['sort']                 = $sort;
$params['desc']                 = $desc;

if( $userStorage->team == TRAINING_TEAM || $userStorage->group_id = ADMINISTRATOR_ID )
{
    $area_id_login = $QStaffTrainer->getAreaTrainer($userStorage->id);
}

if(isset($area_id_login['province']) and count($area_id_login['province']))
{

    $params['regional_market_right']     = array_keys($area_id_login['province']);

    $this->view->regional_markets  = $area_id_login['province'];
}

if(isset($area_id_login['area']) and count($area_id_login['area']) )
{

    $params['area_trainer_right']   = array_keys($area_id_login['area']);

    $this->view->area_trainer = $area_id_login['area'];
}

$QTeam = new Application_Model_Team();
$recursiveDepartmentTeamTitle = $QTeam->get_recursive_cache();
$this->view->recursiveDepartmentTeamTitle = $recursiveDepartmentTeamTitle;


$staffs = $QStaffTrainer->fetchPaginationStaff($page, $limit, $total, $params);

$this->view->desc       = $desc;
$this->view->sort       = $sort;
$this->view->staffs     = $staffs;
$this->view->params     = $params;
$this->view->limit      = $limit;
$this->view->total      = $total;
$this->view->url        = HOST . 'trainer/index' . ($params ? '?' . http_build_query($params) .'&' : '?');
$this->view->offset     = $limit * ($page - 1);

$messages = $flashMessenger->setNamespace('success')->getMessages();
$this->view->messages   = $messages;
$messages_error         = $flashMessenger->setNamespace('error')->getMessages();
$this->view->messages_error = $messages_error;