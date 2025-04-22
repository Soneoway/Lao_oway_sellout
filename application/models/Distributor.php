<?php
class Application_Model_Distributor extends Zend_Db_Table_Abstract
{
    protected $_name = 'distributor';
    protected $_schema = WAREHOUSE_DB;

    function fetchPagination($page, $limit, &$total, $params){
        $db = Zend_Registry::get('db');

        $select = $db->select()
        ->from(array('p' => WAREHOUSE_DB.'.'.$this->_name),
            array(new Zend_Db_Expr('SQL_CALC_FOUND_ROWS p.id'), 'p.*'))
        ->joinLeft(array('s' => 'store'), 'p.id=s.d_id', array('total' => new Zend_Db_Expr('SUM(
            CASE
            WHEN s.id IS NULL THEN 0
            WHEN s.del = 0 OR s.del IS NULL THEN 1 ELSE 0 
            END
        )')))
        ->join(array('dt' => 'regional_market'), 'p.district=dt.id', array('district_name' => 'dt.name'))
        ->join(array('pr' => 'regional_market'), 'pr.id=dt.parent', array('province_name' => 'pr.name'))
        ->join(array('a' => 'area'), 'a.id=pr.area_id', array('area_name' => 'a.name'));

        if (isset($params['name']) and $params['name'])
            $select->where('p.title LIKE ?', '%'.$params['name'].'%');

        if (isset($params['district_id']) and $params['district_id'])
            $select->where('dt.id = ?', $params['district_id']);

        elseif (isset($params['region_id']) and $params['region_id'])
            $select->where('pr.id = ?', $params['region_id']);

        elseif (isset($params['area_id']) and $params['area_id'])
            $select->where('a.id = ?', $params['area_id']);

        if (isset($params['no_parent']) and $params['no_parent'])
            $select->where('p.parent = ?', 0);

        $select->where('p.del IS NULL OR p.del = ?', 0);

        $order_str = $collate = '';

        if (isset($params['sort']) and $params['sort']) {
            $collate = '';

            if (in_array($params['sort'], array('name', 'title', 'unames', 'district', 'province', 'area')))
                $collate = ' COLLATE utf8_unicode_ci ';

            $desc = (isset($params['desc']) and $params['desc'] == 1) ? ' DESC ' : ' ASC ';

            switch ( $params['sort'] ) {
                case 'district':
                $order_str = 'dt.`name`';
                break;
                case 'province':
                $order_str = 'pr.`name`';
                break;
                case 'area':
                $order_str = 'a.`name`';
                break;
                case 'total':
                $order_str = 'total';
                break;

                default:
                $order_str = 'p.`'.$params['sort'] . '` ';
                break;
            }

            $order_str .=  $collate . $desc;

            $select->order(new Zend_Db_Expr($order_str));
        }

        $select
        ->group('p.id')
        ->order('p.title', 'COLLATE utf8_unicode_ci ASC');

        if ($limit)
            $select->limitPage($page, $limit);

        $result = $db->fetchAll($select);

        if ($limit)
            $total = $db->fetchOne("select FOUND_ROWS()");
        
        return $result;
    }

    public function count_store($id)
    {
        if (!$id) return false;

        $QStore = new Application_Model_Store();

        if ($id != 'null')
            $where = $QStore->getAdapter()->quoteInto('(del=0 OR del IS NULL) AND d_id = ?', $id);
        else
            $where = $QStore->getAdapter()->quoteInto('(del=0 OR del IS NULL) AND (d_id IS NULL OR d_id = 0)', 1);

        $stores = $QStore->fetchAll($where);

        if ($stores) {
            return $stores->count();
        }

        return false;
    }

    function get_cache(){
        $cache      = Zend_Registry::get('cache');
        $result     = $cache->load($this->_name.'_cache');

        if ($result === false) {
            $db = Zend_Registry::get('db');

            $select = $db->select()
            ->from(array('p' => WAREHOUSE_DB.'.'.$this->_name),
                array('p.*'))
            ->joinLeft(array('r' => 'regional_market'), 'r.id=p.district', array('district_name' => 'r.name'))
            ->joinLeft(array('r2' => 'regional_market'), 'r2.id=r.parent', array('region_name' => 'r2.name'))
            ->joinLeft(array('a' => 'area'), 'a.id=r2.area_id', array('area_name' => 'a.name'));

            $data = $db->fetchAll($select);

            $result = array();
            foreach ($data as $item){
                $result[$item['id']] = array(
                    'id'            => $item['id'],
                    'title'         => $item['title'],
                    'district'      => $item['district'],
                    'district_name' => $item['district_name'],
                    'region_name'   => $item['region_name'],
                    'area_name'     => $item['area_name'],
                    'add'           => $item['add'],
                );
            }

            $cache->save($result, $this->_name.'_cache', array(), null);
        }
        return $result;
    }

    function getAll_Distributor($params) {
        $db = Zend_Registry::get('db');
        $select = $db->select()
        ->from(array('d' => WAREHOUSE_DB.'.'.$this->_name),array('d.*'));

        if($params['limit']) { $select->limit($params['limit'],$params['offset']); }
        else { 
            $result = $db->fetchAll($select);
            $total = $db->fetchOne("select FOUND_ROWS()"); 
            return $total;
        }

        $result = $db->fetchAll($select);
        return $result;
    }


    function getSuperiorDistributor($warehouse_id)
    {
        $db = Zend_Registry::get('db');
        $select = $db->select()
        ->from(array('p' => WAREHOUSE_DB.'.distributor'),array('p.title'));
        $select->joinLeft(array('w' => WAREHOUSE_DB.'.warehouse'),'w.id = p.agent_warehouse_id',array('w.name'));

        $select->where('w.id =?',$warehouse_id);

        $result = $db->fetchAll($select);
        return $result;

    }
}                                                      
