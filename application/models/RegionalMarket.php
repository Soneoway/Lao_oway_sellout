<?php
class Application_Model_RegionalMarket extends Zend_Db_Table_Abstract
{
    protected $_name = 'regional_market';

    function fetchPagination($page, $limit, &$total, $params)
    {
        $db = Zend_Registry::get('db');

        $select = $db->select()->from(array('p' => $this->_name), array(new Zend_Db_Expr
                ('SQL_CALC_FOUND_ROWS p.id'), 'p.*'));

        $select->joinLeft(array('a' => 'area'), 'p.area_id = a.id', array('area_name' =>
                'a.name'));
        $select->joinLeft(array('r' => $this->_name), 'r.parent=p.id', array('number_district' =>
                'COUNT(r.id)'));

        if (isset($params['name']) and $params['name'])
            $select->where('p.name LIKE ?', '%' . $params['name'] . '%');

        if (isset($params['area_id']) and $params['area_id']) {
            if (is_array($params['area_id'])) {
                $select->where('p.area_id IN (?)', $params['area_id']);
            } else {
                $select->where('p.area_id = ?', $params['area_id']);
            }
        }

        if (isset($params['get_salary_sales']) and $params['get_salary_sales']) {
            $select->joinLeft(array('ss' => 'salary_sales'), 'p.id = ss.province_id', array
                (
                'ss.base_salary',
                'ss.bonus_salary',
                'ss.allowance_1',
                'ss.allowance_2',
                'ss.allowance_3',
                'ss.probation_salary'
                ));
        }

        if (isset($params['get_salary_pg']) and $params['get_salary_pg']) {
            $select->joinLeft(array('ss' => 'salary_pg'), 'p.id = ss.province_id', array
                (
                'ss.base_salary',
                'ss.probation_salary',
                'ss.bonus_salary',
                'ss.allowance_1',
                'ss.allowance_2',
                'ss.allowance_3',
                'kpi',
                'kpi_1'
                ));
        }

        if (isset($params['parent']) && $params['parent']) {
            $select->where('p.parent = ?', $params['parent']);
        } else {
            $select->where('p.parent = 0', 1);
        }

        $select->order('p.name', 'COLLATE utf8_unicode_ci ASC');

        $select->group('p.id');


        if ($limit)
            $select->limitPage($page, $limit);

        $result = $db->fetchAll($select);
        $total = $db->fetchOne("select FOUND_ROWS()");
        return $result;
    }

    function fetchPaginationDistrict($page, $limit, &$total, $params)
    {
        $db = Zend_Registry::get('db');

        $select = $db->select()->from(array('p' => $this->_name), array(new Zend_Db_Expr
                ('SQL_CALC_FOUND_ROWS p.id'), 'district_name' => 'p.name'));

        $select->joinLeft(array('a' => $this->_name), 'p.parent = a.id', array('province_name' =>
                'a.name'));

        $select->where('p.parent <> 0', 0);

        if (isset($params['name']) and $params['name'])
            $select->where('p.name LIKE ?', '%' . $params['name'] . '%');

        if (isset($params['province']) && $params['province'])
            $select->where('a.id = ?', $params['province']);

        $select->order('p.name', 'COLLATE utf8_unicode_ci ASC');

        if ($limit)
            $select->limitPage($page, $limit);

        $result = $db->fetchAll($select);
        $total = $db->fetchOne("select FOUND_ROWS()");
        return $result;
    }

    /**
     * List of provinces
     * @return [type] [description]
     */
    function get_cache()
    {
        $cache = Zend_Registry::get('cache');
        $result = $cache->load($this->_name . '_cache');

        if ($result === false) {
            $where = $this->getAdapter()->quoteInto('parent = ?', 0);
            $data = $this->fetchAll($where, 'name');

            $result = array();
            if ($data) {
                foreach ($data as $item) {
                    $result[$item->id] = $item->name;
                }
            }
            $cache->save($result, $this->_name . '_cache', array(), null);
        }
        return $result;
    }

    /**
     * List if provinces with area_id
     * @return [type] [description]
     */
    function get_cache_all()
    {
        $cache = Zend_Registry::get('cache');
        $result = $cache->load($this->_name . '_all_cache');

        if ($result === false) {
            $where = $this->getAdapter()->quoteInto('parent = ?', 0);
            $data = $this->fetchAll($where, 'name');

            $result = array();
            if ($data) {
                foreach ($data as $item) {
                    $temp = array();
                    $temp['name'] = $item->name;
                    $temp['area_id'] = $item->area_id;
                    $result[$item->id] = $temp;
                }
            }
            $cache->save($result, $this->_name . '_all_cache', array(), null);
        }
        return $result;
    }

    function get_HCMC()
    {
        $cache = Zend_Registry::get('cache');
        $result = $cache->load($this->_name . '_HCMC_cache');

        if ($result === false) {
            $db = Zend_Registry::get('db');

            $select = $db->select()->from(array('p' => $this->_name), array('p.id'))->where('p.area_id IN (?)',
                array(
                HCMC1,
                HCMC2,
                HCMC3,
                HCMC4));

            $data = $db->fetchAll($select);

            $result = array();
            if ($data) {
                foreach ($data as $item) {
                    $result[] = $item['id'];
                }
            }
            $cache->save($result, $this->_name . '_HCMC_cache', array(), null);
        }
        return $result;
    }

    /**
     * List of provinces group by area_id
     * @param  [type] $area_id [description]
     * @return [type]          [description]
     */
    function get_region_cache($area_id = null)
    {
        $cache = Zend_Registry::get('cache');
        $result = $cache->load($this->_name . '_region_cache');

        if ($result === false || (!is_null($area_id) && !isset($result[$area_id]))) {
            $where = $this->getAdapter()->quoteInto('parent = ?', 0);
            $data = $this->fetchAll($where, 'name');
            $result = array();

            if ($data) {
                foreach ($data as $item) {
                    if (!isset($result[$item['area_id']]))
                        $result[$item['area_id']] = array();

                    $result[$item['area_id']][] = $item['id'];
                }
            }

            $cache->save($result, $this->_name . '_region_cache', array(), null);
        }

        return is_null($area_id) ? $result : (isset($result[$area_id]) ? $result[$area_id] : false);
    }

    /**
     * List of all districts
     * @return [type] [description]
     */
    function get_district_cache()
    {
        $cache = Zend_Registry::get('cache');
        $result = $cache->load($this->_name . '_district_cache');

        if ($result === false) {
            $where = $this->getAdapter()->quoteInto('parent IS NOT NULL AND parent <> ?', 0);
            $data = $this->fetchAll($where, 'name');

            $result = array();

            if ($data) {
                foreach ($data as $item) {
                    $result[$item->id] = array('name' => $item->name, 'parent' => $item->parent);
                }
            }

            $cache->save($result, $this->_name . '_district_cache', array(), null);
        }
        return $result;
    }

    /**
     * List of all districts
     * @return [type] [description]
     */
    function get_district_by_province_cache($province_id = null)
    {
        $cache = Zend_Registry::get('cache');
        $result = $cache->load($this->_name . '_district_by_province_cache');

        if ($result === false) {
            $where = $this->getAdapter()->quoteInto('parent IS NOT NULL AND parent <> ?', 0);
            $data = $this->fetchAll($where, 'name');

            $result = array();

            if ($data) {
                foreach ($data as $item) {
                    if (!isset($result[ $item['parent'] ]))
                        $result[ $item['parent'] ] = array();

                    $result[$item['parent']][ $item->id ] = array('name' => $item->name, 'parent' => $item->parent);
                }
            }

            $cache->save($result, $this->_name . '_district_by_province_cache', array(), null);
        }

        return is_null($province_id) ? $result : (isset($result[$province_id]) ? $result[$province_id] : false);
    }

    /**
     * List of all districts
     * @return [type] [description]
     */
    function get_district_by_area_cache($area_id = null)
    {
        $cache = Zend_Registry::get('cache');
        $result = $cache->load($this->_name . '_district_by_area_cache');

        if ($result === false) {
            $db = Zend_Registry::get('db');
            $sql = "SELECT DISTINCT r.area_id, d.id
                        FROM regional_market r
                        INNER JOIN regional_market d ON r.id=d.parent
                        ORDER BY r.area_id";

            $data = $db->query($sql);

            $result = array();

            if ($data) {
                foreach ($data as $item) {
                    if (!isset($result[ $item['area_id'] ]))
                        $result[ $item['area_id'] ] = array();

                    $result[ $item['area_id'] ][] = $item['id'];
                }
            }

            $cache->save($result, $this->_name . '_district_by_area_cache', array(), null);
        }

        return is_null($area_id) ? $result : (isset($result[$area_id]) ? $result[$area_id] : false);
    }

    /********************************************************/
    /* viết lại mớ hàm get_cache cho nó gọn, đẹp, tiện dụng */

    /**
     * Xếp tất cả các huyện vào tỉnh
     * Phục vụ cho các combobox
     *
     * Nếu không có province_id thì trả về nguyên cái mảng lớn
     * nếu có thì trả về mảng con tương ứng
     * @param  integer $province_id [description]
     * @return array array(
     *     id tỉnh => array(
     *         id huyện => tên huyện
     *     )
     * )
     */
    public function nget_district_by_province_cache($province_id = null)
    {
        $cache = Zend_Registry::get('cache');
        $result = $cache->load($this->_name . '_nget_district_by_province_cache');

        if ($result === false) {
            $where = $this->getAdapter()->quoteInto('parent IS NOT NULL AND parent <> ?', 0);
            $data = $this->fetchAll($where, new Zend_Db_Expr('name collate utf8_unicode_ci ASC'));

            $result = array();
            if ($data)
                foreach ($data as $item)
                    $result[ $item->parent ][ $item->id ] = $item->name;

            $cache->save($result, $this->_name . '_nget_district_by_province_cache', array(), null);
        }

        return !is_null($province_id)
            ? ( isset($result[ $province_id ]) && $result[ $province_id ] ? $result[ $province_id ] : false )
            : $result;
    }

    /**
     * Danh sách tỉnh của các huyện
     * Phục vụ cho các combobox
     *
     * Trường hợp lưu trữ bằng district, cần tìm id tỉnh của district, để chọn trên combobox
     * @param  integer $province_id [description]
     * @return array array(
     *     id huyện => array(
     *         id tỉnh
     *     )
     * )
     */
    public function nget_province_id_by_district_cache($district_id = null)
    {
        $cache = Zend_Registry::get('cache');
        $result = $cache->load($this->_name . '_nget_province_id_by_district_cache');

        if ($result === false) {
            $where = $this->getAdapter()->quoteInto('parent IS NOT NULL AND parent <> ?', 0);
            $data = $this->fetchAll($where, new Zend_Db_Expr('name collate utf8_unicode_ci ASC'));

            $result = array();
            if ($data)
                foreach ($data as $item)
                    $result[ $item->id ] = $item->parent;

            $cache->save($result, $this->_name . '_nget_province_id_by_district_cache', array(), null);
        }

        return !is_null( $district_id )
            ? ( isset($result[ $district_id ]) && $result[ $district_id ] ? $result[ $district_id ] : false )
            : $result;
    }

    /**
     * Danh sách area id của các huyện
     * Phục vụ cho các combobox
     *
     * Trường hợp lưu trữ bằng district, cần tìm id area của district, để chọn trên combobox
     * @param  integer $province_id [description]
     * @return array array(
     *     id huyện => array(
     *         id area
     *     )
     * )
     */
    public function nget_area_id_by_district_cache($district_id = null)
    {
        $cache = Zend_Registry::get('cache');
        $result = $cache->load($this->_name . '_nget_area_id_by_district_cache');

        if ($result === false) {
            $db = Zend_Registry::get('db');
            $select = $db->select()
                ->from(array('d' => $this->_schema.'.'.$this->_name), array('district_id' => 'd.id'))
                ->join(array('p' => $this->_schema.'.'.$this->_name), 'd.parent=p.id', array())
                ->join(array('a' => $this->_schema.'.area'), 'p.area_id=a.id', array('area_id' => 'a.id'));

            $data = $db->fetchAll($select);

            $result = array();
            if ($data)
                foreach ($data as $item)
                    $result[ $item['district_id'] ] = $item['area_id'];

            $cache->save($result, $this->_name . '_nget_area_id_by_district_cache', array(), null);
        }

        return !is_null( $district_id )
            ? ( isset($result[ $district_id ]) && $result[ $district_id ] ? $result[ $district_id ] : false )
            : $result;
    }

    /**
     * Danh sách huyện cùng tỉnh của một huyện
     * Phục vụ cho các combobox
     *
     * Trường hợp lưu trữ bằng district, cần tìm các huyện cùng tỉnh của huyện
     * @param  integer $province_id [description]
     * @return array array(
     *     id huyện => array(
     *         id tỉnh
     *     )
     * )
     */
    public function nget_district_by_district_cache($district_id)
    {
        $province_id = $this->nget_province_id_by_district_cache($district_id);
        if (!$province_id) return false;
        $districts = $this->nget_district_by_province_cache( $province_id );
        return $districts;
    }

    /**
     * Xếp tất cả các ID huyện vào tỉnh
     * Phục vụ cho các chỗ search, lấy id ra cho gọn nhẹ
     *
     * Nếu không có province_id thì trả về nguyên cái mảng lớn
     * nếu có thì trả về mảng con tương ứng
     * @param  integer $province_id [description]
     * @return array array(
     *     id tỉnh => array(
     *         id huyện
     *     )
     * )
     */
    public function nget_district_id_by_province_cache($province_id = null)
    {
        $cache = Zend_Registry::get('cache');
        $result = $cache->load($this->_name . '_nget_district_id_by_province_cache');

        if ($result === false) {
            $where = $this->getAdapter()->quoteInto('parent IS NOT NULL AND parent <> ?', 0);
            $data = $this->fetchAll($where, new Zend_Db_Expr('name collate utf8_unicode_ci ASC'));

            $result = array();
            if ($data)
                foreach ($data as $item)
                    $result[ $item->parent ][] = $item->id;

            $cache->save($result, $this->_name . '_nget_district_id_by_province_cache', array(), null);
        }

        return !is_null($province_id)
            ? ( isset($result[ $province_id ]) && $result[ $province_id ] ? $result[ $province_id ] : false )
            : $result;
    }

    /**
     * Xếp tất cả các ID huyện vào khu vực
     * Phục vụ cho các chỗ search, lấy id ra cho gọn nhẹ
     *
     * Nếu không có area_id thì trả về nguyên cái mảng lớn
     * nếu có thì trả về mảng con tương ứng
     * @param  integer $area_id [description]
     * @return array array(
     *     id khu vực => array(
     *         id huyện
     *     )
     * )
     */
    public function nget_district_id_by_area_cache($area_id = null)
    {

    }

    /**
     * Xếp tất cả các tỉnh vào khu vực
     * Phục vụ cho các combobox
     *
     * Nếu không có area_id thì trả về nguyên cái mảng lớn
     * nếu có thì trả về mảng con tương ứng
     * @param  integer $area_id [description]
     * @return array array(
     *     id khu vực => array(
     *         id tỉnh => tên tỉnh
     *     )
     * )
     */
    public function nget_province_by_area_cache($area_id = null)
    {
        $cache = Zend_Registry::get('cache');
        $result = $cache->load($this->_name . '_nget_province_by_area_cache');

        if ($result === false) {
            $where = $this->getAdapter()->quoteInto('area_id IS NOT NULL AND area_id <> ?', 0);
            $data = $this->fetchAll($where, new Zend_Db_Expr('name collate utf8_unicode_ci ASC'));

            $result = array();
            if ($data)
                foreach ($data as $item)
                    $result[ $item->area_id ][ $item->id ] = $item->name;

            $cache->save($result, $this->_name . '_nget_province_by_area_cache', array(), null);
        }

        return !is_null($area_id)
            ? ( isset($result[ $area_id ]) && $result[ $area_id ] ? $result[ $area_id ] : false )
            : $result;
    }

    /**
     * Xếp tất cả các id tỉnh vào khu vực
     * Phục vụ cho các combobox
     *
     * Nếu không có area_id thì trả về nguyên cái mảng lớn
     * nếu có thì trả về mảng con tương ứng
     * @param  integer $area_id [description]
     * @return array array(
     *     id khu vực => array(
     *         id tỉnh
     *     )
     * )
     */
    public function nget_province_id_by_area_cache($area_id = null)
    {
        $cache = Zend_Registry::get('cache');
        $result = $cache->load($this->_name . '_nget_province_id_by_area_cache');

        if ($result === false) {
            $where = $this->getAdapter()->quoteInto('area_id IS NOT NULL AND area_id <> ?', 0);
            $data = $this->fetchAll($where, new Zend_Db_Expr('name collate utf8_unicode_ci ASC'));

            $result = array();
            if ($data)
                foreach ($data as $item)
                    $result[ $item->area_id ][ ] = $item->id;

            $cache->save($result, $this->_name . '_nget_province_id_by_area_cache', array(), null);
        }

        return !is_null($area_id)
            ? ( isset($result[ $area_id ]) && $result[ $area_id ] ? $result[ $area_id ] : false )
            : $result;
    }

    /**
     * Lấy tất cả các tỉnh kèm tên
     * Phục vụ cho các combobox
     *
     * @return array array(
     *     id tỉnh => tên tỉnh
     * )
     */
    public function nget_all_province_cache()
    {
        $cache = Zend_Registry::get('cache');
        $result = $cache->load($this->_name . '_nget_all_province_cache');

        if ($result === false) {
            $where = $this->getAdapter()->quoteInto('parent = ?', 0);
            $data = $this->fetchAll($where, new Zend_Db_Expr('name collate utf8_unicode_ci ASC'));

            $result = array();
            if ($data)
                foreach ($data as $item)
                    $result[$item->id] = $item->name;

            $cache->save($result, $this->_name . '_nget_all_province_cache', array(), null);
        }

        return $result;
    }

    /**
     * Lấy tất cả các tỉnh kèm tên và area cha
     * Phục vụ cho các combobox
     *
     * @return array array(
     *     id tỉnh => array('name' => tên tỉnh, 'area_id' => id khu vực)
     * )
     */
    public function nget_all_province_with_area_cache()
    {
        $cache = Zend_Registry::get('cache');
        $result = $cache->load($this->_name . '_nget_all_province_with_area_cache');

        if ($result === false) {
            $where = $this->getAdapter()->quoteInto('parent = ?', 0);
            $data = $this->fetchAll($where, new Zend_Db_Expr('name collate utf8_unicode_ci ASC'));

            $result = array();
            if ($data)
                foreach ($data as $item)
                    $result[$item->id] = array('name' => $item->name, 'area_id' => $item->area_id);

            $cache->save($result, $this->_name . '_nget_all_province_with_area_cache', array(), null);
        }

        return $result;
    }

    /**
     * Xóa hết các cache region
     * @return [type] [description]
     */
    public function clearCache()
    {

    }
    public function get_index_by_area_cache($id)
    {
        $db = Zend_Registry::get('db');
        $select = $db->select()
            ->from(array('p' => $this->_name),
                array('p.*'));
        
            $select->where('p.area_id = ?', $id); 
            $province_list = $db->fetchAll($select);
           
        return $province_list;
    }
    function getAll_RegionalMarketWsCli($params) {
        $db = Zend_Registry::get('db');
        $select = $db->select()
                ->from(array('p' => $this->_name),array('p.*'));

        if($params['limit']) { $select->limit($params['limit'],$params['offset']); }
        else { 
            $result = $db->fetchAll($select);
            $total = $db->fetchOne("select FOUND_ROWS()"); 
            return $total;
        }

        $result = $db->fetchAll($select);
        return $result;
    }

    public function getAreaByProvince($province_id) {

        $db = Zend_Registry::get('db');

        $get = array(
            'id'   => 'a.id',
            'name' => 'a.name',
        ); 

        $select = $db->select()
            ->from(array('a'  => 'area'), $get)
            ->join(array('rm' => 'regional_market'), 'a.id = rm.area_id', array())
            ->where('rm.id IN (?)', $province_id)
            ->group('a.id')
            ->order(array('a.name ASC'));

        $result = $db->fetchAll($select);
        return $result;

    }

}
