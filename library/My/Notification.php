<?php
class My_Notification extends Zend_Controller_Plugin_Abstract
{
    const STAFF      = 1;
    const TEAM       = 2;
    const DEPARTMENT = 3;
    const DISTRICT   = 4;
    const PROVINCE   = 5;
    const AREA       = 6;
    const GROUP      = 7;
    const ALL_STAFF  = 8;
    const OFFICER    = 9;
    const TITLE      = 10;

	public static function run() {
        $userStorage = Zend_Auth::getInstance()->getStorage()->read();

        if ($userStorage && isset($userStorage->id) && intval($userStorage->id)) {
            // Khởi tạo biến view
            $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
            if (null === $viewRenderer->view) $viewRenderer->initView();
            
            $view = $viewRenderer->view;

            //
            $QRegionalMarket = new Application_Model_RegionalMarket();
            $region_cache    = $QRegionalMarket->get_cache_all();
            
            $QNotification   = new Application_Model_Notification();
            $limit           = LIMITATION;
            $total_recent    = 0;
            $page            = 1;       
            $date            = date('Y-m-d H:i:s');

            $params = array(
                'read'      => null,
                'show_from' => $date,
                'show_to'   => $date,
                'staff_id'  => $userStorage->id,
                'filter'    => true,
                'status'    => 1,
                );

            // get recent notifications
            $recent_notifications = $QNotification->fetchPagination($page, $limit, $total_recent, $params);
            $view->recent_notifications = $recent_notifications;

            // use session variable to limit checking in one user's session
            $notification_ns = new Zend_Session_Namespace('Default');

            if (isset($notification_ns->notifi_shown) && $notification_ns->notifi_shown) {
                return false;
            } else {
                // get unread notifications
                $limit            = null;
                $total_display    = 0;
                $params['read']   = 0;
                $params['pop_up'] = 1;

                $to_display_notifications = $QNotification->fetchPagination($page, $limit, $total_display, $params);
                $view->to_display_notifications = $to_display_notifications;

                // mark as read for unread notifications
                $QNotificationRead = new Application_Model_NotificationRead();

                foreach ($to_display_notifications as $key => $notification) {
                    $data = array(
                        'notification_id' => $notification['id'],
                        'staff_id' => $userStorage->id,
                        );

                    try {
                        // $QNotificationRead->insert($data);
                    } catch (Exception $e) {}
                } // END foreach

                // session timeout: 15 mins
                $notification_ns->setExpirationSeconds(60*15, 'notifi_shown');
                $notification_ns->notifi_shown = 1;
            } // END if check session
	    } // END if check userStorage
	} // END func

    /**
     *  My_Notification::add(
     *      'Test từ API 2', 
     *      'clgt asdasdasd', 
     *      1, 
     *      array(
     *          'all_staff' => 1, 
     *          'department' => array(2, 3, 4), 
     *          'staff' => '1,2,3,4,644'
     *      )
     *  );
     * @param [type]  $title    [description]
     * @param [type]  $content  [description]
     * @param [type]  $category [description]
     * @param array   $show_to  [description]
     * @param integer $pop_up   [description]
     */
    public static function add($title, $content, $category, $show_to = array(), $pop_up = 0, $type = My_Notification_Type::Normal)
    {
        $QModel = new Application_Model_Notification();
        
        $all_staff = isset( $show_to['all_staff'] ) ? 1 : 0;
        $officer   = isset( $show_to['officer'] ) ? 1 : 0;
        $pop_up    = intval( $pop_up ) ? 1 : 0 ;
        $category  = intval( $category ) ? $category : 0 ;
        $show_time_from = isset($show_to['from']) && ($tmp = DateTime::createFromFormat('d/m/Y H:i:s', $show_to['from'])) ? $tmp->format('Y-m-d H:i:s') : null;
        $show_time_to = isset($show_to['to']) && ($tmp = DateTime::createFromFormat('d/m/Y H:i:s', $show_to['to'])) ? $tmp->format('Y-m-d H:i:s') : null;

        $staff_objects = array();

        if (isset($show_to['staff']) && strlen($show_to['staff']))
            $staff_objects = explode(',', $show_to['staff']);

        $staff_objects = is_array($staff_objects) ? $staff_objects : array();

        $userStorage = Zend_Auth::getInstance()->getStorage()->read();

        $db = Zend_Registry::get('db');
        $db->beginTransaction();

        try {
            // kiểm tra category
            $QCategory = new Application_Model_NotificationCategory();
            $category_check = $QCategory->find($category);
            $category_check = $category_check->current();

            if (!$category_check) throw new Exception("Invalid category");

            // lưu nội dung
            $data = array(
                'title'       => htmlspecialchars($title, ENT_QUOTES, "UTF-8"),
                'content'     => htmlspecialchars($content, ENT_QUOTES, "UTF-8"),
                'category_id' => intval($category),
                'status'      => 1,
                'pop_up'      => $pop_up >= 1 ? 1 : 0,
                'type'        => $type,
            );

            if ($show_time_from) $data['show_from'] = $show_time_from;
            if ($show_time_to) $data['show_to'] = $show_time_to;

            $data['created_at'] = date('Y-m-d H:i:s');
            $data['created_by'] = $userStorage ? $userStorage->id : 0;
            $id = $QModel->insert($data);

            $add_department_objects = isset( $show_to['department'] ) && is_array( $show_to['department'] ) ? $show_to['department'] : array();
            $add_team_objects       = isset( $show_to['team'] ) && is_array( $show_to['team'] ) ? $show_to['team'] : array();
            $add_title_objects      = isset( $show_to['title'] ) && is_array( $show_to['title'] ) ? $show_to['title'] : array();
            $add_area_objects       = isset( $show_to['area'] ) && is_array( $show_to['area'] ) ? $show_to['area'] : array();
            $add_staff_objects      = $staff_objects;

            // update nhóm hiển thị
            $QNotificationObject = new Application_Model_NotificationObject();

            foreach ($add_department_objects as $_key => $_value) {
                $data = array(
                    'notification_id' => $id,
                    'object_id'       => $_value,
                    'type'            => My_Notification::DEPARTMENT,
                    );

                $QNotificationObject->insert($data);
            }

            ////////////////////////////////////////////////////////////////

            foreach ($add_team_objects as $_key => $_value) {
                $data = array(
                    'notification_id' => $id,
                    'object_id'       => $_value,
                    'type'            => My_Notification::TEAM,
                    );

                $QNotificationObject->insert($data);
            }

            ////////////////////////////////////////////////////////////////

            foreach ($add_title_objects as $_key => $_value) {
                $data = array(
                    'notification_id' => $id,
                    'object_id'       => $_value,
                    'type'            => My_Notification::TITLE,
                    );

                $QNotificationObject->insert($data);
            }

            ////////////////////////////////////////////////////////////////

            foreach ($add_area_objects as $_key => $_value) {
                $data = array(
                    'notification_id' => $id,
                    'object_id'       => $_value,
                    'type'            => My_Notification::AREA,
                    );

                $QNotificationObject->insert($data);
            }

            ////////////////////////////////////////////////////////////////

            foreach ($add_staff_objects as $_key => $_value) {
                $data = array(
                    'notification_id' => $id,
                    'object_id'       => $_value,
                    'type'            => My_Notification::STAFF,
                    );

                $QNotificationObject->insert($data);
            }

            ////////////////////////////////////////////////////////////////
            $data = array(
                'notification_id' => $id,
                'object_id'       => $all_staff,
                'type'            => My_Notification::ALL_STAFF,
                );

            $all_staff_id = $QNotificationObject->insert($data);
            
            if (!$all_staff_id) throw new Exception("Insert failed");
            
            ////////////////////////////////////////////////////////////////
            $data = array(
                'notification_id' => $id,
                'object_id'       => $officer,
                'type'            => My_Notification::OFFICER,
                );

            $officer_id = $QNotificationObject->insert($data);

            if (!$officer_id) throw new Exception("Insert failed");
            
            ////////////////////////////////////////////////////////////////

            $db->commit();

            return true;

        } catch (Exception $e) {
            // PC::db($e->getMessage());
            $db->rollback();
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }
}