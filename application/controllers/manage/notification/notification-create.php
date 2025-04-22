<?php
$id = $this->getRequest()->getParam('id');

if ($id) {
    $QModel = new Application_Model_Notification();
    $rowset = $QModel->find($id);
    $notification = $rowset->current();

    if (!$notification) $this->_redirect(HOST.'manage/notification');

    $this->view->notification = $notification;

    $QNotificationFile = new Application_Model_NotificationFile();
    $where = $QNotificationFile->getAdapter()->quoteInto('notification_id = ?', $id);
    $files = $QNotificationFile->fetchAll($where);

    $this->view->files = $files;

    $QStaff = new Application_Model_Staff();

    $QRegionalMarket = new Application_Model_RegionalMarket();
    $this->view->regional_market_all = $QRegionalMarket->get_cache();

    $QNotificationObject = new Application_Model_NotificationObject();
    $where               = $QNotificationObject->getAdapter()->quoteInto('notification_id = ?', $id);
    $old_objects         = $QNotificationObject->fetchAll($where);

    $old_department_objects = array();
    $old_team_objects       = array();
    $old_title_objects      = array();
    $old_area_objects       = array();
    $old_staff_objects      = array();
    $officer                = 0;
    $all_staff              = 0;

    // sắp xếp theo nhóm các đối tượng
    foreach ($old_objects as $_key => $_value) {
        switch ($_value['type']) {
            case My_Notification::DEPARTMENT:
                $old_department_objects[] = $_value['object_id'];
                break;

            case My_Notification::STAFF:
                $old_staff_objects[] = $_value['object_id'];                    
                break;

            case My_Notification::AREA:
                $old_area_objects[] = $_value['object_id'];                    
                break;

            case My_Notification::TEAM:
                $old_team_objects[] = $_value['object_id'];                    
                break;

            case My_Notification::TITLE:
                $old_title_objects[] = $_value['object_id'];                    
                break;

            case My_Notification::ALL_STAFF:
                $all_staff = $_value['object_id'];                    
                break;

            case My_Notification::OFFICER:
                $officer = $_value['object_id'];                    
                break;

            default:
                throw new Exception("Invalid object type");
                break;
        }
    }

    if ($old_staff_objects)
        $where = $QStaff->getAdapter()->quoteInto('id IN (?)', $old_staff_objects);
    else
        $where = $QStaff->getAdapter()->quoteInto('1=0', 1);

    $staff_list = $QStaff->fetchAll($where);

    $this->view->old_department_objects = $old_department_objects;
    $this->view->old_team_objects       = $old_team_objects;
    $this->view->old_title_objects      = $old_title_objects;
    $this->view->old_area_objects       = $old_area_objects;
    $this->view->old_staff_objects      = $staff_list;
    $this->view->officer                = $officer;
    $this->view->all_staff              = $all_staff;

    $this->view->userStorage = Zend_Auth::getInstance()->getStorage()->read();
}

$QTeam                 = new Application_Model_Team();
$QArea                 = new Application_Model_Area();
$QRegionalMarket       = new Application_Model_RegionalMarket();
$QNotificationCategory = new Application_Model_NotificationCategory();

$this->view->team_cache            = $QTeam->get_recursive_cache();
$this->view->area_cache            = $QArea->get_cache();
$this->view->region_cache          = $QRegionalMarket->get_cache();
$this->view->category_cache        = $QNotificationCategory->get_full_cache();
$this->view->categories            = $QNotificationCategory->get_cache();
$this->view->category_string_cache = $QNotificationCategory->get_string_cache();

$flashMessenger       = $this->_helper->flashMessenger;
$messages             = $flashMessenger->setNamespace('error')->getMessages();
$this->view->messages = $messages;

//back url
$this->view->back_url = $this->getRequest()->getServer('HTTP_REFERER');

$this->_helper->viewRenderer->setRender('notification/create');