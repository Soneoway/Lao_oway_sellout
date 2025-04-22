<?php
class Application_Model_LeaderLog extends Zend_Db_Table_Abstract
{
    protected $_name = 'leader_log';

    /**
     * Kiểm tra một staff có phải là leader của region tại thời điểm 
     * cụ thể hay không
     *     Function này có thể được sử dụng để kiểm tra
     *     xem chấm công (với store, timing time) có thuộc quản lý
     *     của staff (quyền leader) hay không
     * @param  int  $staff_id    - ID của staff cần kiểm tra
     * @param  int  $region      - ID của region cần kiểm tra
     * @param  datetime  $timing_time - Thời gian cần kiểm tra
     * @return boolean              true: Nó là leader
     *                              false: Không phải leader
     */
    public function check_leader_by_region($staff_id, $region, $timing_time) {
        $db = Zend_Registry::get('db');

        $select = $db->select()
            ->from(array('p' => $this->_name), array('p.*'))
            ->join(array('s' => 'store'), 's.regional_market=p.regional_market', array())
            ->where('s.regional_market = ?', $region)
            ->where('staff_id = ?', $staff_id)
            ->where('? >= FROM_UNIXTIME(from_date, \'%Y-%m-%d\') AND ( ? < FROM_UNIXTIME(to_date, \'%Y-%m-%d\') OR to_date IS NULL OR to_date = 0 )',
                date( 'Y-m-d', strtotime( $timing_time ) )
                );

        return $db->fetchAll($select) ? true : false;
    }

    /**
     * Kiểm tra một staff có phải là leader của store tại thời điểm 
     * cụ thể hay không
     *     Function này có thể được sử dụng để kiểm tra
     *     xem chấm công (với store, timing time) có thuộc quản lý
     *     của staff (quyền leader) hay không
     * @param  int  $staff_id    - ID của staff cần kiểm tra
     * @param  int  $store_id      - ID của store cần kiểm tra
     * @param  datetime  $timing_time - Thời gian chấm công
     * @return boolean              true: Nó là leader
     *                              false: Không phải leader
     */
    public function check_leader_by_store($staff_id, $store_id, $timing_time) {
        $db = Zend_Registry::get('db');

        $select = $db->select()
            ->from(array('p' => $this->_name), array('p.*'))
            ->join(array('s' => 'store'), 's.regional_market=p.regional_market', array())
            ->where('s.id = ?', $store_id)
            ->where('staff_id = ?', $staff_id)
            ->where('? >= FROM_UNIXTIME(from_date, \'%Y-%m-%d\') AND ( ? < FROM_UNIXTIME(to_date, \'%Y-%m-%d\') OR to_date IS NULL OR to_date = 0 )',
                date( 'Y-m-d', strtotime( $timing_time ) )
                );

        return $db->fetchAll($select) ? true : false;
    }

    /**
     * Lấy leader_id cho timing khi chấm công (tương tự sales_id)
     * @param  int $store_id ID cửa hàng chấm công
     * @param  datetime $time     Ngày chấm công (Y-m-d)
     * @return mixed           false: không tìm thấy
     *                         int: ID của leader tại thời điểm chấm công
     */
    public function get_leader($store_id, $time)
    {
        $db = Zend_Registry::get('db');

        $select = $db->select()
            ->from(array('p' => $this->_name), array('p.*'))
            ->join(array('s' => 'store'), 's.regional_market = p.regional_market', array());
        
        $log_where = $this->getAdapter()->quoteInto('s.id = ?', $store_id).
                    " AND " . $this->getAdapter()->quoteInto('DATE(?) >= FROM_UNIXTIME(p.from_date, \'%Y-%m-%d\')', $time).
                    " AND (".
                        $this->getAdapter()->quoteInto('DATE(?) < FROM_UNIXTIME(p.to_date, \'%Y-%m-%d\')', $time).
                        " OR " . $this->getAdapter()->quoteInto('p.to_date IS NULL', 1).
                        " OR " . $this->getAdapter()->quoteInto('p.to_date = 0', 1).
                    " ) ";

        $select->where($log_where);

        $result = $db->fetchRow($select);
        
        return $result && intval($result['staff_id']) > 0 ? $result['staff_id'] : false;
    }
}