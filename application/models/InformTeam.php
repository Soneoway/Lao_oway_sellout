<?php
class Application_Model_InformTeam extends Zend_Db_Table_Abstract
{
    protected $_name = 'inform_team';

    function check_view($notification_id)
    {
        $userStorage = Zend_Auth::getInstance()->getStorage()->read();

        if (!$userStorage || !$notification_id) return false;

        $QRegionalMarket = new Application_Model_RegionalMarket();
        $region_cache = $QRegionalMarket->get_cache_all();

        $QNotification = new Application_Model_Inform();
        $total = 0;
        $limit = 1;
        $page = 1;
        $params = array(
            'id'          => $notification_id,
            'staff_id'    => $userStorage->id,
            'area_id'     => isset($region_cache[ $userStorage->regional_market ]['area_id']) ? $region_cache[ $userStorage->regional_market ]['area_id'] : 0,
            'province_id' => $userStorage->regional_market,
            'department'  => $userStorage->department,
            'team'        => $userStorage->team,
            'all_staff'   => 1,
            'officer'     => $userStorage->is_officer,
            'status'      => 1,
            );

        $total = $QNotification->fetchPagination($page, $limit, $total, $params);

        return $total;
    }
}