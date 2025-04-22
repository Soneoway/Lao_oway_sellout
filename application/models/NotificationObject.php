<?php
class Application_Model_NotificationObject extends Zend_Db_Table_Abstract
{
    protected $_name = 'notification_object';

    function check_view($notification_id)
    {
        $userStorage = Zend_Auth::getInstance()->getStorage()->read();

        if (!$userStorage || !$notification_id) return false;

        $QRegionalMarket = new Application_Model_RegionalMarket();
        $region_cache = $QRegionalMarket->get_cache_all();

        $QNotification = new Application_Model_Notification();
        $total = 0;
        $limit = 1;
        $page = 1;
        $params = array(
            'id'       => $notification_id,
            'staff_id' => $userStorage->id,
            'filter'   => true,
            'status'   => 1,
            );

        $QNotification->fetchPagination($page, $limit, $total, $params);

        return $total;
    }
}