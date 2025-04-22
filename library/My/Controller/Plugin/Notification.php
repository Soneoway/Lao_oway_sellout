<?php
class My_Notification extends Zend_Controller_Plugin_Abstract
{
	public function run() {
        $userStorage = Zend_Auth::getInstance()->getStorage()->read();

        if ($userStorage) {
            // Khởi tạo biến view
            $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
            if (null === $viewRenderer->view) {
                $viewRenderer->initView();
            }
            $view = $viewRenderer->view;

            // Lấy tất cả Notifi trong vòng 2 ngày để hiện trên biểu tượng quả đất
			$date = date_sub(date_create(), new DateInterval('P1D'))->format('Y-m-d');

			
			$group_id = @$userStorage->group_id;
			$uid      = @$userStorage->id;
			
			$QNotification = new Application_Model_Notification();
			$daily_notifis = $QNotification->get_not($group_id, $uid);
			
			$view->notifi_daily = $daily_notifis;

            // Dùng session để hạn chế việc kiểm tra trong cùng 1 phiên
            $notification_ns = new Zend_Session_Namespace('Default');

            if (isset($notification_ns->notifi_shown) && $notification_ns->notifi_shown) {
                return false;
            } else {
                $notification_ns->setExpirationSeconds(1500, 'notifi_shown');
                $notification_ns->notifi_shown = 1;
            }

            // Lấy những notifi mà user đã xem trên local // lưu của từng user
			$cache = Zend_Registry::get('cache');

            // Lấy tất cả notifi trong vòng 30 ngày
			$all_notifis = $QNotification->get_not($group_id, $uid, 30);

            // Lấy cache lưu notifi mà user đã xem trên server // lưu của tất cả user
            $server_notifis = $cache->load('server_notifis_cache');

            $notifi_list = array();

            foreach ($all_notifis as $k => $v) {
                // Kiểm tra user đã xem notifi này chưa
                if ( empty( $server_notifis[$uid][$v['id']] ) ) {

                    // chưa xem thì hiện ra và thêm vào danh sách đã xem
                    $notifi_list[$v['id']] = $v;
                    $server_notifis[$uid][$v['id']] = 1;
                }
            }

            $cache->save($server_notifis, 'server_notifis_cache', array(), null);

            $view->notifi_list = $notifi_list;

	    }
	}
}