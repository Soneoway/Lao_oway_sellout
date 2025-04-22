<?php
$flashMessenger              = $this->_helper->flashMessenger;
$messages                    = $flashMessenger->setNamespace('success')->getMessages();
$this->view->message_success = $messages;
$messages_error              = $flashMessenger->setNamespace('error')->getMessages();
$this->view->messages        = $messages_error;

$userStorage                 = Zend_Auth::getInstance()->getStorage()->read();
$QLog                        = new Application_Model_Log();
$ip                          = $this->getRequest()->getServer('REMOTE_ADDR');
$db                          = Zend_Registry::get('db');

$page                        = $this->getRequest()->getParam('page', 1);
$sort                        = $this->getRequest()->getParam('sort', '');
$desc                        = $this->getRequest()->getParam('desc', 1);
$date                        = $this->getRequest()->getParam('date',null);
$description                 = $this->getRequest()->getParam('description',null);

$QStaffTeamBuildingReport           = new Application_Model_StaffTeamBuildingReport();
$staff_id                           = $userStorage->id;

$params             = array_filter(array(
    'staff_id'      =>$staff_id,
    'date'          =>$date,
    'description'   =>$description,
    'sort'          =>$sort,
    'desc'          =>$desc

));

$full_rights_trainer = unserialize(FULL_RIGHTS_TRAINER);

if(in_array($userStorage->title,$full_rights_trainer)  || $userStorage->group_id == ADMINISTRATOR_ID)
{
    unset($params['staff_id']);
}

$limit                      = LIMITATION;
$total                      = 0;
$rows                       = $QStaffTeamBuildingReport->fetchPagination($page, $limit, $total, $params);

$this->view->desc           = $desc;
$this->view->sort           = $sort;
$this->view->params         = $params;
$this->view->list           = $rows;
$this->view->limit          = $limit;
$this->view->total          = $total;
$this->view->url            = HOST.'trainer/list-team-building-report'.( $params ? '?'.http_build_query($params).'&' : '?' );
$this->view->offset         = $limit*($page-1);