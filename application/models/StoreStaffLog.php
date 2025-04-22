<?php
class Application_Model_StoreStaffLog extends Zend_Db_Table_Abstract
{
    protected $_name = 'store_staff_log';

    function fetchPagination($page, $limit, &$total, $params){
        $db = Zend_Registry::get('db');

        $select = $db->select()
            ->from(array('p' => $this->_name),
                array(new Zend_Db_Expr('SQL_CALC_FOUND_ROWS p.id'), 'p.*'));

        $select
            ->joinLeft(array('s' => 'staff'), 'p.staff_id = s.id', array('s.email'))
            ->join(array('st' => 'store'), 'st.id=p.store_id', array('store_id' => 'st.id', 'store_name' => 'st.name', 'st.district'))
            ->join(array('o' => 'org'), 'st.org_dealer=o.org_id', array('store_type' => 'o.org_name'))
            ->join(array('rm' => 'regional_market'), 'st.regional_market = rm.id', array())
            ->join(array('a' => 'area'), 'rm.area_id = a.id', array('area_id' => 'a.id'))
            ->joinLeft(array('d' => WAREHOUSE_DB.'.distributor'), 'st.d_id = d.id', array('d_id' => 'd.id', 'd_name' => 'd.title'))
            ->joinLeft(array('stg' => 'store_target'), 
                "
                    st.id = stg.store_id 
                    AND stg.good_id IS NULL 
                    AND stg.from_date <= now()
                    AND stg.to_date >= now() 
                ", 
                array('target' => 'stg.target', 'punish' => 'stg.punish', 'reward' => 'stg.reward'))
            /*
            ->joinLeft(array('stg_hero' => 'store_target'), 
                "
                    st.id = stg_hero.store_id 
                    AND stg_hero.good_id = 142 
                    AND stg_hero.from_date <= now()
                    AND stg_hero.to_date >= now() 
                ", 
                array('punish_hero' => 'stg_hero.punish', 'reward_hero' => 'stg_hero.reward'))
            */
            ->where('p.is_leader <> 2');


        if (isset($params['email']) and $params['email'])
            $select->where('s.email LIKE ?', str_replace(EMAIL_SUFFIX, '', $params['email']).EMAIL_SUFFIX);

        if (isset($params['staff_id']) and $params['staff_id'])
            $select->where('s.id = ?', $params['staff_id']);

        if (isset($params['from']) and $params['from'] && isset($params['to']) and $params['to']) {
            $select->where('FROM_UNIXTIME(p.joined_at, "%Y-%m-%d") <= ?', $params['to']);
            $select->where('p.released_at IS NULL OR FROM_UNIXTIME(p.released_at, "%Y-%m-%d") > ?', $params['from']);
        }

        $order_str = ' released_at IS NULL DESC, released_at DESC, joined_at DESC';
       
        $select->order(new Zend_Db_Expr($order_str));

        if ($limit)
            $select->limitPage($page, $limit);

        $result = $db->fetchAll($select);

        if ($limit)
            $total = $db->fetchOne("select FOUND_ROWS()");
        return $result;
    }

    /**
     * Kiểm tra xem chấm công vào lúc timing_time, tại cửa hàng store_id có...
     *     ...thuộc quản lý của staff_id hay không
     * @param  int  $staff_id    - id của người muốn check hàng
     * @param  int  $store_id    - id cửa hàng
     * @param  datetime  $timing_time - thời gian của chấm công (timing for)
     * @param  boolean $is_leader - true: là sales; 
     *                            false: là PG
     *                            NULL: không quan tâm, chỉ cần biết nó thuộc cửa hàng
     * @return boolean              - true: thuộc thằng đó; 
     *                                false: không thuộc
     */
    public function belong_to($staff_id, $store_id, $timing_time, $is_leader = NULL)
    {
        $where = array();
        $where[] = $this->getAdapter()->quoteInto('store_id = ?', $store_id);
        $where[] = $this->getAdapter()->quoteInto('staff_id = ?', $staff_id);

        if ( ! is_null( $is_leader ) )
            if ($is_leader)
                $where[] = $this->getAdapter()->quoteInto('is_leader = ?', 1);
            else
                $where[] = $this->getAdapter()->quoteInto('is_leader = ?', 0);
            
        $where[] = $this->getAdapter()
            ->quoteInto('? >= FROM_UNIXTIME(joined_at, \'%Y-%m-%d\') AND ( ? < FROM_UNIXTIME(released_at, \'%Y-%m-%d\') OR released_at IS NULL OR released_at = 0 )',
                date('Y-m-d', strtotime($timing_time)));

        return $this->fetchRow($where) ? true : false;
    }

    /**
     * Lấy danh sách các store mà staff đó thuộc tại thời điểm $time
     * @param  int $staff_id  -  staff id
     * @param  datetime $time - thời gian để check
     * @return array          - mảng các store id
     */
    public function get_stores($staff_id, $time)
    {
        $where = array();
        $where[] = $this->getAdapter()->quoteInto('staff_id = ?', $staff_id);
        $where[] = $this->getAdapter()->quoteInto('FROM_UNIXTIME(joined_at, \'%Y-%m-%d\') <= ?', $time);
        $where[] = $this->getAdapter()->quoteInto('released_at IS NULL OR released_at = 0 OR FROM_UNIXTIME(released_at, \'%Y-%m-%d\') > ?', $time);

        $result = $this->fetchAll($where);

        $stores = array();

        foreach ($result as $value)
            $stores[] = $value['store_id'];

        return count($stores) > 0 ? $stores : false; 
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

    /**
     * Khi PG/Sales chấm công, hàm này giúp lấy...
     *     ...ID sales quản lý cửa hàng đó, vào ngày chấm công
     * @param  int $store_id - ID cửa hàng
     * @param  datetime $time     - Ngày chấm công
     * @return int           - ID sales quản lý cửa hàng
     */
    public function get_sales_man($store_id, $time)
    {
        $where = array();
        $where[] = $this->getAdapter()->quoteInto('is_leader = ?', 1);
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

      //get pcm
    public function get_pcm($store_id, $time)
    {
        $where = array();
        $where[] = $this->getAdapter()->quoteInto('is_leader = ?', 2);
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

    public function get_stock($store_id, $time)
    {
        $where = array();
        $where[] = $this->getAdapter()->quoteInto('is_leader = ?', 3);
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

     //get Asm 
   public function get_asm($store_id, $time)
    {
        $where = array();
        $where[] = $this->getAdapter()->quoteInto('is_leader = ?', 4);
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


    function short_report_storestafflog($params) {
        $db = Zend_Registry::get('db');

        $select = $db->select()
            ->from(array('sst' => 'store_staff_log'), array( 
                    'from_date' => new Zend_Db_Expr('FROM_UNIXTIME(sst.joined_at)'),
                    'to_date'   => new Zend_Db_Expr('FROM_UNIXTIME(sst.released_at)'),
                    'is_leader' => 'sst.is_leader'
                    ))
            ->joinLeft(array('st' => 'store'), 'sst.store_id = st.id', array('store_name' => 'st.name'))
            ->joinLeft(array('s' => 'staff'), 'sst.staff_id = s.id', array(
                'staff_code' => 's.code', 
                'off_date' => 's.off_date',
                'staff_name' => new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)")))
            ->joinLeft(array('g' => 'group'), 's.group_id = g.id', array('group_name' => 'g.name'))
            ->where('sst.store_id = ?', $params['store_id'])
            ->order('from_date ASC');

        $result = $db->fetchAll($select);
        return $result;
    }

    function get_date($store_id, $staff_id) {
        $db = Zend_Registry::get('db');
        $select = $db->select()
            ->from(array('sst' => 'store_staff_log'), array( 
                    'from_date' => new Zend_Db_Expr('FROM_UNIXTIME(sst.joined_at)'),
                    'to_date'   => new Zend_Db_Expr('FROM_UNIXTIME(sst.released_at)'),
                    ))
            ->where('sst.store_id = ?', $store_id)
            ->where('sst.staff_id = ?', $staff_id)
            ->order('from_date DESC');

        $result = $db->fetchRow($select);
        return $result;
    }

    function get_pcm_name($store_id, $from, $to) {
        $db = Zend_Registry::get('db');

        $select = $db->select()
            ->from(array('sst' => 'store_staff'), array( 
                    'pcm_code' => 's.code',
                    'pcm_name' => new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)")
                ))
            ->join(array('s' => 'staff'), 'sst.staff_id = s.id', array())
            ->where("sst.is_leader = 2")
            ->where("sst.store_id = ?", $store_id);
            //->where('? >= FROM_UNIXTIME(sst.joined_at, \'%Y-%m-%d\')', $from)
            //->where('( ? < FROM_UNIXTIME(sst.released_at, \'%Y-%m-%d\') OR sst.released_at IS NULL OR sst.released_at = 0 )', $to);

        $result = $db->fetchRow($select);
        return $result;
    }

    function fetchPaginationStoreStaffLog($params)
    {
        $db = Zend_Registry::get('db');

        $select = $db->select()->from(array('p' => 'store_staff_log'), array('ss_id' => 'p.id', 'leader' => 'p.is_leader', 'joinedat' => 'p.joined_at', 'releasedat' => 'p.released_at'));

        $select->join(array('a' => 'staff'), 'p.staff_id = a.id', array('staff_code' => 'a.code','staff_name' => new Zend_Db_Expr("CONCAT(a.firstname, ' ', a.lastname)")));
        $select->join(array('s' => 'store'), 'p.store_id = s.id', array('st_id' => 's.id', 'store_name' => 's.name'));

        $select->join(array('r' => 'regional_market'), 's.regional_market = r.id', array('region' => 'r.name'));

        if (isset($params['store_name']) and $params['store_name'])
            $select->where('s.name LIKE ?', '%' . $params['store_name'] . '%');

        if (isset($params['staff_code']) && $params['staff_code'])
            $select->where('a.code IN (?)', $params['staff_code']);

        if (isset($params['store_id']) and $params['store_id'])
            $select->where('s.id IN (?)', $params['store_id']);

        if (isset($params['is_leader']) && $params['is_leader'])
            $select->where('p.is_leader =?', $params['is_leader']);

        if (isset($params['region_id']) && $params['region_id'])
            $select->where('s.regional_market LIKE ?', '%'.$params['region_id'].'%');
        $select->order('p.id DESC');
        $select->group('p.id');
        

        $result = $db->fetchAll($select);
        return $result;
    }
}
