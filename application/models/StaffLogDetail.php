<?php
class Application_Model_StaffLogDetail extends Zend_Db_Table_Abstract
{
    protected $_name = 'staff_log_detail';

    /**
     * Lấy các record log của staff, theo loại, trong khoảng thời gian chỉ định
     * @param  int $staff_id - ID của staff cần lấy log
     * @param  int $type     - loại dữ liệu log
     * @param  int $from     - UNIX_TIMESTAMP ngày bắt đầu
     * @param  int $to       - UNIX_TIMESTAMP ngày kết thúc
     * @return cursor        - query result
     */
    public function get_history($staff_id, $type, $from, $to)
    {
        $where = array();
        $where[] = $this->getAdapter()->quoteInto('object = ?', $staff_id);
        $where[] = $this->getAdapter()->quoteInto('from_date <= ? AND to_date IS NULL', $from)
            . " OR ("
            . $this->getAdapter()->quoteInto('from_date >= ?', $from)
            . " AND "
            . $this->getAdapter()->quoteInto('from_date <= ?', $to)
            . " )";
        
        $where[] = $this->getAdapter()->quoteInto('info_type = ?', $type);
        return $this->fetchAll($where);
    }

    /**
     * Lấy danh sách các staff có thay đổi trong khoảng thời gian chỉ định
     * @param  int $type - loại dữ liệu cần lấy log
     * @param  int $from - UNIX_TIMESTAMP ngày bắt đầu
     * @param  int $to   - UNIX_TIMESTAMP ngày kết thúc
     * @return array     - mảng ID các staff có thay đổi
     */
    public function get_changed_staffs($type, $from, $to)
    {
        $where = array();
        $where[] = $this->getAdapter()->quoteInto('from_date <= ? AND to_date IS NULL', $from)
            . " OR ("
            . $this->getAdapter()->quoteInto('from_date >= ?', $from)
            . " AND "
            . $this->getAdapter()->quoteInto('from_date <= ?', $to)
            . " )";
        
        $where[] = $this->getAdapter()->quoteInto('info_type = ?', $type);
        $result = $this->fetchAll($where);
        $staffs = array();

        foreach ($result as $key => $value)
            $staffs[] = $value['object'];

        return array_unique($staffs);
    }
}