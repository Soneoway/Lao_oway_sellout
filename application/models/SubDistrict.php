<?php
class Application_Model_SubDistrict extends Zend_Db_Table_Abstract
{
	protected $_name = 'sub_district';

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

	function fetchPaginationSubDistrict($page, $limit, &$total, $params)
    {
        $db = Zend_Registry::get('db');

        $select = $db->select()->from(array('p' => $this->_name), array(new Zend_Db_Expr
                ('SQL_CALC_FOUND_ROWS p.id'), 'sub_dist_name' => 'p.name'));

        $select->joinLeft(array('a' => 'regional_market'), 'p.district = a.id', array('district_name' =>
                'a.name'));

       // $select->where('p.parent <> 0', 0);

        if (isset($params['name']) and $params['name'])
            $select->where('p.name LIKE ?', '%' . $params['name'] . '%');

        if (isset($params['district']) && $params['district'])
            $select->where('a.id = ?', $params['district']);

        $select->order('p.name', 'ASC');

        // if ($limit)
        //     $select->limitPage($page, $limit);

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
           // $where = $this->getAdapter()->quoteInto('parent <> 0', 0);
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

    function get_subdist()
    {
        $cache = Zend_Registry::get('cache');
        $result = $cache->load($this->_name . '_cache');
        $db = Zend_Registry::get('db');
        $select = $db->select()->from(array('p' => 'regional_market'), array(new Zend_Db_Expr
                ('SQL_CALC_FOUND_ROWS p.id'), 'sub_dist_id' => 'p.id', 'sub_dist_name' => 'p.name', 'parents' => 'p.parent'));
        $select->where('p.parent <> 0', 0);
        $select->order('p.name', 'ASC');

        $result = $db->fetchAll($select);
        $total = $db->fetchOne("select FOUND_ROWS()");
        $cache->save($result, $this->_name . '_cache', array(), null);
        return $result;
    }

    function get_sub_district()
    {
        $db = Zend_Registry::get('db');
        $select = $db->select()->from(array('p' => $this->_name), array('dis_id' => 'p.id', 'dis_name' => 'p.name'));

        $result = $db->fetchAll($select);
        return $result;
    }

}                                                      
