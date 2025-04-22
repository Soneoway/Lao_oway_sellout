<?php
class Application_Model_Area extends Zend_Db_Table_Abstract
{
	protected $_name = 'area';

    function fetchPagination($page, $limit, &$total, $params){
        $db = Zend_Registry::get('db');

        $select = $db->select()
            ->from(array('p' => $this->_name),
                array(new Zend_Db_Expr('SQL_CALC_FOUND_ROWS p.id'), 'p.*'));

        if (isset($params['name']) and $params['name'])
            $select->where('p.name LIKE ?', '%'.$params['name'].'%');

        if (isset($params['leader_ids']) and $params['leader_ids'])
            $select->where('FIND_IN_SET(?, p.leader_ids)', $params['leader_ids']);

        $select->order('p.name', 'COLLATE utf8_unicode_ci ASC');

        $select->limitPage($page, $limit);

        $result = $db->fetchAll($select);
        $total = $db->fetchOne("select FOUND_ROWS()");
        return $result;
    }

    function get_cache(){
        $cache      = Zend_Registry::get('cache');
        $result     = $cache->load($this->_name.'_cache');

        if ($result === false) {

            $data = $this->fetchAll(null, 'name');

            $result = array();
            if ($data){
                foreach ($data as $item){
                    $result[$item->id] = $item->name;
                }
            }
            $cache->save($result, $this->_name.'_cache', array(), null);
        }
        return $result;
    }

    function get_index_cache(){
        $cache      = Zend_Registry::get('cache');
        $result     = $cache->load($this->_name.'_index_cache');

        if ($result === false) {

            $data = $this->fetchAll(null, 'name');

            $result = array();
            if ($data)
                foreach ($data as $item)
                    $result[] = array(
                        'id' => $item->id,
                        'name' => $item->name
                    );

            $cache->save($result, $this->_name.'_index_cache', array(), null);
        }
        return $result;
    }

    function get_cache2(){
        $cache      = Zend_Registry::get('cache');
        $result     = $cache->load($this->_name.'_cache2');

        if ($result === false) {

            $data = $this->fetchAll(null, 'name');

            $result = array();
            if ($data){
                foreach ($data as $item){
                    $result[$item->id] = $item;
                }
            }
            $cache->save($result, $this->_name.'_cache2', array(), null);
        }
        return $result;
    }

    function get_ASM_cache(){
        $cache      = Zend_Registry::get('cache');
        $result     = $cache->load($this->_name.'_ASM_cache');

        if ($result === false) {

            $data = $this->fetchAll();

            $result = array();

            $QStaff = new Application_Model_Staff();

            if ($data){
                foreach ($data as $item){

                    $sStaffs = '';
                    //get staffs
                    if ($item['leader_ids']){
                        $ASM_ids = explode(',',$item['leader_ids']);
                        $where = $QStaff->getAdapter()->quoteInto('id IN (?)', $ASM_ids);

                        $ASMs = $QStaff->fetchAll($where);

                        if ($ASMs->count())
                            foreach ($ASMs as $k=>$ASM){
                                if ($k>0)
                                    $sStaffs .= "\n".$ASM->firstname.' '.$ASM->lastname;
                                else
                                    $sStaffs .= $ASM->firstname.' '.$ASM->lastname;
                            }
                    }

                    $result[$item->id] = $sStaffs;
                }
            }
            $cache->save($result, $this->_name.'_ASM_cache', array(), null);
        }
        return $result;
    }




    function get_list_store($area_id)
    {


        $db = Zend_Registry::get('db');

        $select = $db->select()
            ->from(array('p' => $this->_name),
                array('p.id'));

        

        $select->joinLeft(array('r' => 'regional_market'),
            'r.area_id = p.id',
            array('regional_market_name' => 'r.name'));

        $select->joinLeft(array('s' => 'store'),
            's.regional_market = r.id',
            array('store_name' => 's.name', 'store_id' => 's.id'));


        $select->where('p.id in (?)', $area_id);

        $select->order(new Zend_Db_Expr('store_name  COLLATE utf8_unicode_ci'));

       
        $data = $db->fetchAll($select);

        
        $result = array();
        if ($data) {
            foreach ($data as $item) {
                $result[$item['store_id']] = $item['store_name'];
            }
        }

        return $data;
    }
    function getAll_AreaWsCli($params) {
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

    function getAreaByAsmTable($staff_id) {

        $db = Zend_Registry::get('db');

        $get = array(
            'id'   => 'a.id',
            'name' => 'a.name',
        );

        $select = $db->select()
            ->from(array('s'   => 'staff'), $get)
            ->join(array('asm' => 'asm'), 's.id = asm.staff_id', array())
            ->join(array('a'   => 'area'), 'asm.area_id = a.id', array())
            ->where('s.id = ?', $staff_id)  
            ->order(array('a.name ASC'));

        //echo $select; 
        $data = $db->fetchAll($select);

        //$result = array();
        //if ($data) { foreach ($data as $item) { $result[] = array('id' => $item->id, 'name' => $item->name); } }

        return $data;

    }

    function getAreaByGrandArea($grand_area_id) {

        $db = Zend_Registry::get('db');

        $get = array(
            'id'   => 'a.id',
            'name' => 'a.name',
        );

        $select = $db->select()
            ->from(array('ga'  => 'grand_area'), $get)
            ->join(array('gal' => 'grand_area_list'), 'ga.id = gal.grand_area_id', array())
            ->join(array('a'   => 'area'), 'gal.area = a.id', array())
            ->where('ga.id IN (?)', $grand_area_id)
            ->order('a.name ASC');

        //echo $select; 
        $result = $db->fetchAll($select);
        return $result;

    }

    function getAllAreaGrandBKK($params) {

        $db = Zend_Registry::get('db');

        $get = array(
            'area_id' => new Zend_Db_Expr(
                "   (CASE 
                        WHEN a.name like 'BKK-E1%' THEN 'BKK East-1'
                        WHEN a.name like 'BKK-E2%' THEN 'BKK East-2'
                        WHEN a.name like 'BKK-E3%' THEN 'BKK East-3'
                        WHEN a.name like 'BKK-E4%' THEN 'BKK East-4'
                        WHEN a.name like 'BKK-E5%' THEN 'BKK East-5'
                        WHEN a.name like 'BKK-W1%' THEN 'BKK West-1'
                        WHEN a.name like 'BKK-W2%' THEN 'BKK West-2'
                        WHEN a.name like 'BKK-W3%' THEN 'BKK West-3'
                        ELSE a.id 
                    END)
                "),
            'area_name' => new Zend_Db_Expr(
                "   (CASE 
                        WHEN a.name like 'BKK-E1%' THEN 'BKK East-1'
                        WHEN a.name like 'BKK-E2%' THEN 'BKK East-2'
                        WHEN a.name like 'BKK-E3%' THEN 'BKK East-3'
                        WHEN a.name like 'BKK-E4%' THEN 'BKK East-4'
                        WHEN a.name like 'BKK-E5%' THEN 'BKK East-5'
                        WHEN a.name like 'BKK-W1%' THEN 'BKK West-1'
                        WHEN a.name like 'BKK-W2%' THEN 'BKK West-2'
                        WHEN a.name like 'BKK-W3%' THEN 'BKK West-3'
                        ELSE a.name 
                    END)
                "),
        );

        $select = $db->select()
            ->from(array('a'  => 'area'), $get)
            ->where('a.id NOT IN (?)', array(48,49,72))
            ->group('area_name')
            ->order('a.name ASC');

        // ASM Permission
        if (isset($params['asm']) && $params['asm']) {
            $QAsm = new Application_Model_Asm();
            $list_regions = $QAsm->get_cache($params['asm']);
            $list_regions = isset($list_regions['area']) && is_array($list_regions['area']) ? $list_regions['area'] : array();

            if (count($list_regions) > 0)
                $select->where('a.id IN (?)', $list_regions);
            else
                $select->where('1=0', 1);
        }

        // echo $select; 
        $result = $db->fetchAll($select);
        return $result;

    }

}                                                      
