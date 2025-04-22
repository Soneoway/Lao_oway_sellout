<?php
class Application_Model_SubArea extends Zend_Db_Table_Abstract
{
    protected $_name = 'sub_area';

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

    function fetchPaginationSubArea($page, $limit, &$total, $params)
    {

        $db = Zend_Registry::get('db');

        $select = $db->select()->from(array('p' => $this->_name), array(new Zend_Db_Expr('SQL_CALC_FOUND_ROWS p.id'), 'p.*'));
        $select->joinLeft(array('ac' => 'area_control'),'p.id = ac.sub_area_id',array('sub_area_id' => 'p.id'));
        $select->joinLeft(array('sd' => 'sub_district'),'ac.sub_district = sd.id',array('village_name' => 'sd.name'));
        $select->joinLeft(array('rm' => 'regional_market'),'sd.district = rm.id',array('sub_district_name' => 'rm.name'));
        $select->joinLeft(array('rm2' => 'regional_market'),'rm.parent = rm2.id',array('province_name' => 'rm2.name'));
        $select->joinLeft(array('a' => 'area'),'rm2.area_id = a.id',array('area_name' => 'a.name','sub_area_status' => 'p.status'));
        $select->joinLeft(array('s' => 'staff'), 'p.staff_id = s.id', 
            array('sub_area_name' => 'p.name','staff_code' => 's.code','staff_name' => new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)")));

        $select->order('a.name', 'ASC');



        if (isset($params['name']) and $params['name']) {
            $select->where('p.name LIKE ?', '%' . $params['name'] . '%');
        }

        if (isset($params['staff_id']) && $params['staff_id']) {
            $select->where('s.id = ?', $params['staff_id']);
        }

        if (isset($params['status']) && $params['status']) {
            $select->where('p.status =?',$params['status']);
        }


        if ($limit)
            $select->limitPage($page, $limit);

        $result = $db->fetchAll($select);
        $total = $db->fetchOne("select FOUND_ROWS()");
        return $result;
    }

    function fetchPaginationControlArea($page, $limit, &$total, $params)
    {
        $db = Zend_Registry::get('db');

        $select = $db->select()->from(array('p' => 'area_control'), array());
        $select->join(array('a' => 'sub_area'), 'p.sub_area_id = a.id', array('sub_area_id' => 'a.id', 'sub_area_name' => 'a.name'));
        $select->join(array('b' => 'sub_district'), 'p.sub_district = b.id', array('district_name' => 'b.name'));
        $select->join(array('c' => 'staff'), 'a.staff_id = c.id', array('staff_code' => 'c.code','staff_name' => new Zend_Db_Expr("CONCAT(c.firstname, ' ', c.lastname)")));

       // $select->where('p.parent = ?', 9);

        if (isset($params['name']) and $params['name'])
            $select->where('p.name LIKE ?', '%' . $params['name'] . '%');

        if (isset($params['staff']) && $params['staff'])
            $select->where('a.id = ?', $params['staff']);

        ;//$select->order('.name', 'ASC');

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

    function get_subarea()
    {
        $cache = Zend_Registry::get('cache');
        $result = $cache->load($this->_name . '_cache');
        $db = Zend_Registry::get('db');
        $select = $db->select()->from(array('p' => 'staff'), array(new Zend_Db_Expr
            ('SQL_CALC_FOUND_ROWS p.id'), 'staff_id' => 'p.id', 'staff_code' => 'p.code', 'staff_name' => new Zend_Db_Expr("CONCAT(p.firstname, ' ', p.lastname)")));
        $select->where('p.group_id = ?', 9);
        $select->where('p.status = ?', 1);
        $select->order('p.code', 'ASC');

        $result = $db->fetchAll($select);
        $total = $db->fetchOne("select FOUND_ROWS()");
        $cache->save($result, $this->_name . '_cache', array(), null);
        return $result;
    }

    function get_sub_area()
    {
        $db = Zend_Registry::get('db');
        $select = $db->select()->from(array('p' => $this->_name), array('area_id' => 'p.id', 'area_name' => 'p.name'));

        $result = $db->fetchAll($select);
        return $result;
    }
    
}