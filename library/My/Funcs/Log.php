<?php
class Log extends Zend_Controller_Plugin_Abstract{

	/**
	 * Usage:
	 * 		Log::w(data = array(), LogGroup::Staff, LogType::Access);
	 */
	public static function w($before, $after, $object = 0, $group = 0, $type = 0, $time = null) {
		$userStorage = Zend_Auth::getInstance()->getStorage()->read();
		$ip = '';

		if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
		    $ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
		    $ip = $_SERVER['REMOTE_ADDR'];
		}

		$table = '';
        $time = $time ? $time : date('Y-m-d H:i:s');

		switch ($group) {
			case LogGroup::Staff:
				$QLog = new Application_Model_StaffLog();

				$log_id = $QLog->insert( array(
					'object'     => $object,
					'group'      => $group,
					'type'       => $type,
					'before'     => serialize($before),
					'after'      => serialize($after),
					'user_id'    => $userStorage->id,
					'ip_address' => $ip,
					'time'       => date('Y-m-d H:i:s'),
		        ) );

                //self::log_detail($before, $after, $log_id, $object, strtotime($time));

				break;

			default:
				$QLog = new Application_Model_Log();

				$QLog->insert( array(
		        	'object'     => $object,
		        	'group'      => $group,
		        	'type'       => $type,
					'info'       => serialize($info),
					'user_id'    => $userStorage->id,
					'ip_address' => $ip,
					'time'       => date('Y-m-d H:i:s'),
		        ) );

				break;
		}
	}

    /**
         * Kiểm tra các thông tin có thay đổi gì không
         *     - có thì ghi log lại
         *     - không thì thôi
         * @param  array $before - mảng các thông tin trước thay đổi
         * @param  array $after  - mảng các thông tin sau thay đổi
         * @param  int $object - id staff được cập nhật thông tin
         * @return void
    */
    private static function log_detail($before, $after, $log_id, $object, $time) {
        // Kiểm tra xem các thông tin nào thay đổi
        // chỉ kiểm tra thông tin mình cần log thôi
        $old_region = isset($before['regional_market']) ? $before['regional_market'] : 0;
        $new_region = isset($after['regional_market']) ? $after['regional_market'] : 0;

        $old_group = isset($before['group_id']) ? $before['group_id'] : 0;
        $new_group = isset($after['group_id']) ? $after['group_id'] : 0;

        $old_department = isset($before['department']) ? $before['department'] : 0;
        $new_department = isset($after['department']) ? $after['department'] : 0;

        $old_team = isset($before['team']) ? $before['team'] : 0;
        $new_team = isset($after['team']) ? $after['team'] : 0;

        $old_company = isset($before['company_id']) ? $before['company_id'] : 0;
        $new_company = isset($after['company_id']) ? $after['company_id'] : 0;

        $old_status = isset($before['status']) ? $before['status'] : 0;
        $new_status = isset($after['status']) ? $after['status'] : 0;

        // Thông tin nào thay đổi thì ghi log lại
        if ($old_region != $new_region) {
            self::log_detail_info($log_id, $object, My_Staff_Info_Type::Region, $old_region, $new_region, $time);
        }

        if ($old_group != $new_group) {
            self::log_detail_info($log_id, $object, My_Staff_Info_Type::Group, $old_group, $new_group, $time);
        }

        if ($old_department != $new_department) {
            self::log_detail_info($log_id, $object, My_Staff_Info_Type::Department, $old_department, $new_department, $time);
        }

        if ($old_team != $new_team) {
            self::log_detail_info($log_id, $object, My_Staff_Info_Type::Team, $old_team, $new_team, $time);
        }

        if ($old_company != $new_company) {
            self::log_detail_info($log_id, $object, My_Staff_Info_Type::Company, $old_company, $new_company, $time);
        }

        if ($old_status != $new_status) {
            self::log_detail_info($log_id, $object, My_Staff_Info_Type::Status, $old_status, $new_status, $time);
        }
    }

    /**
     * Giúp việc chèn dữ liệu vào bảng log đơn giản hơn
     * Bao gồm cập nhật thời gian chấm dứt thông tin (group, region...) cũ
     *     và thời gian bắt đầu thông tin mới
     * @param  int $object    - id của staff được ghi log
     * @param  StaffInfoType/enum $type      - loại thông tin được log
     * @param  int $old_value - id giá trị cũ
     * @param  int $new_value - id giá trị mới
     * @return void
     */
    private static function log_detail_info($log_id, $object, $type, $old_value, $new_value, $time = null) {
        $QStaffLogDetail = new Application_Model_StaffLogDetail();

        $time = $time ? $time : time();

        $where = array();
        $where[] = $QStaffLogDetail->getAdapter()->quoteInto('object = ?', $object);
        $where[] = $QStaffLogDetail->getAdapter()->quoteInto('info_type = ?', $type);
        $where[] = $QStaffLogDetail->getAdapter()->quoteInto('current_value = ?', $old_value);
        $where[] = $QStaffLogDetail->getAdapter()->quoteInto('to_date IS NULL OR to_date=0', 1);

        $data = array('to_date' => $time);
        $QStaffLogDetail->update($data, $where);

        $data = array(
            'log_id'        => $log_id,
            'object'        => $object,
            'info_type'     => $type,
            'old_value'     => $old_value,
            'current_value' => $new_value,
            'from_date'     => $time,
            );

        $QStaffLogDetail->insert($data);
    }

    public static function check_date_range($object, $type, $time)
    {
        $QStaffLogDetail = new Application_Model_StaffLogDetail();
        $where = array();
        $where[] = $QStaffLogDetail->getAdapter()->quoteInto('object = ?', $object);
        $where[] = $QStaffLogDetail->getAdapter()->quoteInto('info_type = ?', $type);
        $where[] = $QStaffLogDetail->getAdapter()->quoteInto('from_date <= ?', $time);
        $where[] = $QStaffLogDetail->getAdapter()->quoteInto('to_date IS NOT NULL AND to_date > ?', $time);

        $log = $QStaffLogDetail->fetchRow($where);

        if ($log) return false;
        return true;
    }
}

include_once APPLICATION_PATH.'/../library/My/Class/Enum.php';

class LogGroup extends CosEnum {
	const Staff      = 1;
	const User       = 2;
	const Timing     = 3;
	const Sales_team = 4;
	const Store      = 5;
	const Area       = 6;
}

class LogType extends CosEnum {
    const Insert = 1;
    const Update = 2;
    const Delete = 3;
    const Access = 4;
}

// không xài class này nữa -- 2015-01-13
// Các loại dữ liệu cần log
// Khi log thêm loại mới thì thêm một const, tăng số thứ tự lên
// class StaffInfoType extends CosEnum {
//     const Area       = 1;
//     const Region     = 2;
//     const Group      = 3;
//     const Team       = 4;
//     const Department = 5;
//     const Status     = 6;
// }
