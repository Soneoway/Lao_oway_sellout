<?php
class Application_Model_StoreLeaderLog extends Zend_Db_Table_Abstract
{
    protected $_name = 'store_leader_log';

    public function is_leader($staff_id, $store_id, $time)
    {
        $where = array();
        $where[] = $this->getAdapter()->quoteInto('store_id = ?', $store_id);
        $where[] = $this->getAdapter()->quoteInto('staff_id = ?', $staff_id);
        $where[] = $this->getAdapter()
            ->quoteInto('? >= FROM_UNIXTIME(joined_at, \'%Y-%m-%d\') AND ( ? < FROM_UNIXTIME(released_at, \'%Y-%m-%d\') OR released_at IS NULL OR released_at = 0 )',
                date('Y-m-d', strtotime($time)));

        return $this->fetchRow($where) ? true : false;
    }

    public function get_leader($store_id, $time)
    {
        $where = array();
        $where[] = $this->getAdapter()->quoteInto('store_id = ?', $store_id);

        $where[] = $this->getAdapter()->quoteInto(
            'FROM_UNIXTIME(joined_at, \'%Y-%m-%d\') <= ?', date('Y-m-d', strtotime($time))
            );

        $where[] = $this->getAdapter()->quoteInto(
            'released_at IS NULL OR released_at = 0 OR FROM_UNIXTIME(released_at, \'%Y-%m-%d\') > ?',
             date('Y-m-d', strtotime($time))
             );

        $result = $this->fetchRow($where);

        return $result && intval($result['staff_id']) > 0 ? $result['staff_id'] : false;
    }

    /**
     * Lấy danh sách các store mà staff đó quản lý từ $from đến $to
     * @param  int $staff_id  -  staff id
     * @param  datetime $time - thời gian để check
     * @return array          - mảng các store id
     */
    public function get_stores_cache($staff_id, $from, $to)
    {
        $cache      = Zend_Registry::get('cache');
        $result     = $cache->load($this->_name.'_cache');

        if ($result === false) {
            $db = Zend_Registry::get('db');
            $select = $db->select()
                ->distinct()
                ->from(array('p' => $this->_name), array('p.store_id', 'p.staff_id'))
                // ->where('staff_id = ?', $staff_id)
                ->where('FROM_UNIXTIME(joined_at, \'%Y-%m-%d\') <= ?', $to)
                ->where('released_at IS NULL OR released_at = 0 OR FROM_UNIXTIME(released_at, \'%Y-%m-%d\') > ?', $from);

            $data = $db->fetchAll($select);

            $result = array();

            foreach ($data as $value)
                $result[ $value['staff_id'] ][] = $value['store_id'];

            $cache->save($result, $this->_name.'_cache', array(), null);
        }

        return isset($result[ $staff_id ]) && count($result[ $staff_id ]) ? $result[ $staff_id ] : false;
    }
}