<?php
/**
*
*/
class My_Staff
{
    /**
     * Khi nhân viên nghỉ (set ngày off_date), dùng phương thức này xóa hết các quyền liên qua khu vực, quyền cấp riêng
     * @param  [type] $staff_id [description]
     * @return [type]           [description]
     */
    public static function clear_all_roles($staff_id = null)
    {
        if (is_null($staff_id) || intval($staff_id) <= 0) throw new Exception("Invalid ID");

        $staff_id = intval($staff_id);

        $QStaff = new Application_Model_Staff();
        $staff_check = $QStaff->find($staff_id);
        $staff_check = $staff_check->current();

        if (!$staff_check) throw new Exception("Invalid staff ID");

        $QAsm = new Application_Model_Asm();
        $where = $QAsm->getAdapter()->quoteInto('staff_id = ?', $staff_id);
        $QAsm->delete($where);

        // $QAsm_Standby = new Application_Model_AsmStandby();
        // $where = $QAsm_Standby->getAdapter()->quoteInto('staff_id = ?', $staff_id);
        // $QAsm_Standby->delete($where);

        // $QSalesAdmin = new Application_Model_SalesAdmin();
        // $where = $QSalesAdmin->getAdapter()->quoteInto('staff_id = ?', $staff_id);
        // $QSalesAdmin->delete($where);

        $QStaffPriviledge = new Application_Model_StaffPriviledge();
        $where = $QStaffPriviledge->getAdapter()->quoteInto('staff_id = ?', $staff_id);
        $QStaffPriviledge->delete($where);
    }
/*
    public static function getOwnStores($staff_id)
    {
        $QLog     = new Application_Model_StoreStaffLog();
        $page     = 1;
        //$limit    = LIMITATION;
        $total    = 0;

        $params = array(
            'staff_id' => $staff_id,
            'from'     => date('Y-m-01'),
            'to'       => date('Y-m-d'),
        );

        $logs = $QLog->fetchPagination($page, null, $total, $params);

        $stores = array();

        foreach ($logs as $key => $item) {
            $stores[] = array(
                'store_id'      => $item['store_id'],
                'store_name'    => $item['store_name'],
                'store_id'      => $item['store_id'],
                'from'          => isset($item['joined_at']) ? date('d/m/Y', $item['joined_at']) : 'n/a',
                'to'            => isset($item['released_at']) ? date('d/m/Y', $item['released_at']) : '',
                'punish'        => $item['punish'],
                'target'        => $item['target'],
                'reward'        => $item['reward'],
                'punish_hero'   => $item['punish_hero'],
                'reward_hero'   => $item['reward_hero'],
                'area_id'       => $item['area_id']
            );
        }

        return $stores;
    }
*/

    public static function getOwnStores($staff_id, $group_id) {

        $QStoreStaff = new Application_Model_StoreStaff();

        $params = array( 
            'staff_id' => $staff_id, 
            'group_id' => $group_id,
        );

        if ($group_id == PGPB_ID) {

            $data = $QStoreStaff->getStoreListByPC($params);

        } else {

            $data = $QStoreStaff->getStoreList($params);

        }
        
        //print_r($data); die;
        return $data;
    }

    public static function getOwnPC($staff_id, $group_id) {

        $QStoreStaff = new Application_Model_StoreStaff();

        $params = array( 
            'staff_id' => $staff_id, 
            'group_id' => $group_id,
        );
            
        $data = $QStoreStaff->getPCList($params);

        //print_r($data); die;
        return $data;
    }


    public static function getOwnPCDB($staff_id, $group_id){
        $QStoreStaff = new Application_Model_StoreStaff();

        $params = array( 
            'staff_id' => $staff_id, 
            'group_id' => $group_id,
        );
            
        $data = $QStoreStaff->getPCDBList($params);

        //print_r($data); die;
        return $data;
    }

    public static function getSaleTarget($staff_id, $group_id) {

        $QStoreStaff = new Application_Model_StoreStaff();

        $params = array( 
            'staff_id' => $staff_id, 
            'group_id' => $group_id,
        );

        if ($group_id == SALES_ID) {

            $data = $QStoreStaff->getSaleTarget($params);

        }else{

            $data = array();
            
        }

        return $data;
    }


    public static function getImeiExpired($staff_id)
    {
        $QImei = new Application_Model_TimingSaleExpired();
        $result = $QImei->getAllImeiExpired($staff_id);
        return $result;
    }

    /**
     * Remove
     * @param  [type] $staff_id  [description]
     * @param  [type] $old_title [description]
     * @return [type]            [description]
     */
    public static function removeStoreForTransfer($staff_id, $old_title, $date)
    {
        if ($old_title == SALES_TITLE || $old_title == PGPB_TITLE)
            self::_removeStoreSale($staff_id, $date);
        elseif ($old_title == LEADER_TITLE) {
            self::_removeStoreSale($staff_id, $date);
            self::_removeStoreLeader($staff_id, $date);
        }
    }

    /**
     * [removeStoreSale description]
     * @param  [type] $staff_id [description]
     * @param  [type] $time     [description]
     * @return [type]           [description]
     */
    public static function _removeStoreSale($staff_id, $date)
    {
        $QStoreStaff = new Application_Model_StoreStaff();
        $where = $QStoreStaff->getAdapter()->quoteInto('staff_id = ?', intval($staff_id));
        $QStoreStaff->delete($where);

        $QStoreStaffLog = new Application_Model_StoreStaffLog();
        $where = array();
        $where[] = $QStoreStaffLog->getAdapter()->quoteInto('released_at = 0 OR released_at IS NULL', 1);
        $where[] = $QStoreStaffLog->getAdapter()->quoteInto('staff_id = ?', intval($staff_id));
        $data = array('released_at' => strtotime($date));
        $QStoreStaffLog->update($data, $where);
    }

    /**
     * [removeStoreLeader description]
     * @param  [type] $staff_id [description]
     * @param  [type] $time     [description]
     * @return [type]           [description]
     */
    private static function _removeStoreLeader($staff_id, $date)
    {
        $QStoreLeader = new Application_Model_StoreLeader();
        $where = $QStoreLeader->getAdapter()->quoteInto('staff_id = ?', intval($staff_id));
        $QStoreLeader->delete($where);

        $QStoreLeaderLog = new Application_Model_StoreLeaderLog();
        $where = array();
        $where[] = $QStoreLeaderLog->getAdapter()->quoteInto('released_at = 0 OR released_at IS NULL', 1);
        $where[] = $QStoreLeaderLog->getAdapter()->quoteInto('staff_id = ?', intval($staff_id));
        $data = array('released_at' => strtotime($date));
        $QStoreLeaderLog->update($data, $where);
    }
}