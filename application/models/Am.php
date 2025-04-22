<?php
class Application_Model_Am extends Zend_Db_Table_Abstract
{
    protected $_name = 'am';

    function fetchPagination($page, $limit, &$total, $params){

        $Exception = array(
            28394,  // VP
            2107,   // Mo
            7684,   // Neung
            15570,  // Vivi
            10970,  // Sine
            4773,   // X
            
            33897,  // KA : Channal Online
            8767,   // KR-1
            4379,   // KR-3
        );

        $db = Zend_Registry::get('db');

        $get = array(
            'staff_id'      => 's.id', 
            'staff_code'    => 's.code', 
            'staff_name'    => new Zend_Db_Expr("CONCAT(s.firstname,' ', s.lastname)"),
            'staff_email'   => 's.email',
            'staff_group'   => 's.group_id',
        );

        $select = $db->select()
            ->from(array('s' => 'staff'), $get)
            ->where('s.status = 1')
            ->where('s.group_id = ?', AM_ID)
            ->where('s.off_date IS NULL');
            

        // Check for Filter on AM Commission
        if (isset($params['flag']) && $params['flag'] == 1) {

            $select->where('s.id NOT IN (?)', $Exception);
            $select->group('s.id');

        } else {
            $select->joinLeft(array('am' => $this->_name), 'am.staff_id = s.id', array('am.org_id'));
            $select->order('staff_name ASC');
        }

        // if ($limit)
            // $select->limitPage($page, $limit);

        //echo $select; //die;
        $result = $db->fetchAll($select);
        //$total = $db->fetchOne("select FOUND_ROWS()");
        return $result;
    }

    public function get_cache($staff_id = null) {
        
        $cache      = Zend_Registry::get('cache');
        $result     = $cache->load($this->_name.'_cache');

        if ($result === false || ( !is_null($staff_id) && !isset($result[$staff_id]) )) {
            $db = Zend_Registry::get('db');

            $select = $db->select()
                ->from(array('am' => $this->_name, array('am.staff_id')))
                ->joinLeft(array('o' => 'org'), 'am.org_id = o.org_id', array('store_type' => 'o.org_id'));

            $data = $db->fetchAll($select);

            $result = array();

            if ($data){
                foreach ($data as $item){
                    if (!isset($result[$item['staff_id']]))
                        $result[$item['staff_id']] = array('store_type' => array());

                    $result[$item['staff_id']]['store_type'][] = $item['store_type'] ;
                }

                foreach ($result as $_staff_id => $value) {
                    $result[ $_staff_id ]['store_type'] = array_unique($value['store_type']);
                }
            }

            $cache->save($result, $this->_name.'_cache', array(), null);
        }

        return is_null($staff_id) ? $result : ( isset($result[$staff_id]) ? $result[$staff_id] : false );
    }

    // Get Store Type List By Staff ID
    public function getOrgByStaff($staff_id) {

        $kr_id = array(33,27,34,32,37,38);

        $db = Zend_Registry::get('db');

        $get = array(
            'staff_id'      => 'am.staff_id',
            'channel_id'    => 'o.org_id',
            'channel_name'  => 'o.org_name',
        );

        $select = $db->select()
            ->from(array('am'  => $this->_name), $get)
            ->join(array('o'  => 'org'), 'am.org_id = o.org_id', array())
            ->where('am.staff_id = ?', $staff_id)
            ->where('o.store_type_id <> ?', 3)
            ->where('o.org_id NOT IN (?)', $kr_id)
            ->order('o.org_name ASC');

        //echo $select; die;
        $result = $db->fetchAll($select);
        return $result;

    }

    public function getAmForTotal($params) {

        $Exception = array(
            28394,  // VP
            2107,   // Mo
            7684,   // Neung
            15570,  // Vivi
            10970,  // Sine
            4773,   // X
            
            33897,  // KA : Channal Online
            8767,   // KR-1
            4379,   // KR-3
        );

        $kr_id = array(33,27,34,32,37,38);
        $operator_id = array(12,19,5,20,3,26,10);

        $db = Zend_Registry::get('db');

        $get = array(
            'staff_id'      => 's.id', 
            'staff_code'    => 's.code', 
            'staff_name'    => new Zend_Db_Expr("CONCAT(s.firstname,' ', s.lastname)"),
            'group_name'    => 's.group_id',
            'base_kpi'      => 'ab.kpi',
            'org_name'      => 'o.org_name',
        );

        $select = $db->select()
            ->from(array('s' => 'staff'), $get)
            ->join(array('g' => 'group')    , 's.group_id = g.id', array())
            ->join(array('ab'=> 'am_base')  , 
                "   s.id = ab.staff_id 
                    AND ab.from_date <= '".$params['from']."'
                    AND ab.to_date >= '".$params['to']."'
                " ,array())
            ->join(array('o'=> 'org')  , 
                "   ( 
                        CASE 
                            WHEN ab.org_id = 12 THEN o.org_id IN (12,19,10) 
                            WHEN ab.org_id = 5 THEN o.org_id IN (5,20) 
                            WHEN ab.org_id = 2 THEN o.org_id IN (2,30) 
                            WHEN ab.org_id = 3 THEN o.org_id IN (3,26) 
                            ELSE o.org_id = ab.org_id 
                        END 
                    )
                " ,array())
            ->where('s.status = ?', 1)
            ->where('s.off_date IS NULL', 1)
            ->where('s.group_id = ?', AM_ID)
            ->where('o.org_id NOT IN (?)', $kr_id)
            ->where('s.id NOT IN (?)', $Exception)
            ->order(array('staff_name ASC', 'o.org_name ASC'));

        if (isset($params['export']) && $params['export']) {
            switch ($params['export']) {
                case 1: $select->where('o.org_id NOT IN (?)', $operator_id); break;
                case 2: $select->where('o.org_id IN (?)', $operator_id); break;
                default: break;
            }
        }

        if (isset($params['export_flag']) && $params['export_flag']) {
            switch ($params['export_flag']) {
                case 3: $select->where('o.org_id NOT IN (?)', $operator_id); break;
                case 4: $select->where('o.org_id IN (?)', $operator_id); break;
                default: break;
            }
        }

        //echo $select; die;
        $result = $db->fetchAll($select);
        return $result;

    }

    // Get Store Type List By Staff Code [KA]
    public function getKaByStaffCode($staff_code) {

        $operator_id = array(3,5,10,12);

        $db = Zend_Registry::get('db');

        $get = array(
            'channel_id' => 'o.org_id',
        );

        $select = $db->select()
            ->from(array('am'=> $this->_name), $get)
            ->join(array('o' => 'org')  , 'am.org_id = o.org_id', array())
            ->join(array('s' => 'staff'), 'am.staff_id = s.id'  , array())
            ->where('s.code = ?', $staff_code)
            ->where('o.store_type_id = ?', 1)
            ->where('o.org_id NOT IN (?)', $operator_id)
            ->order('o.org_id ASC');

        //echo $select; die;
        $result = $db->fetchAll($select);
        return $result;

    }

}