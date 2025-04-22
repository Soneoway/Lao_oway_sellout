<?php
class Application_Model_TimePermission extends Zend_Db_Table_Abstract
{
	protected $_name = 'time_permission';
    
    function fetchPagination($page, $limit, &$total, $params){
        $db = Zend_Registry::get('db');

        $select = $db->select();

        if (isset($params['group_staff']) and $params['group_staff']){
            $select_fields = array(new Zend_Db_Expr('SQL_CALC_FOUND_ROWS DISTINCT p.id'), 'p.*' );           

            $select
                ->from(array('p' => $this->_name),
                    $select_fields
                )
                ->group('p.staff_id');

        } 
        else
        {
             $select->from(array('p' => $this->_name),
                array(new Zend_Db_Expr('SQL_CALC_FOUND_ROWS DISTINCT p.id'), 'p.*'));
        }
        
        if(isset($params['staff_id']) and $params['staff_id'])
        {
            $select->where('staff_id = ?' , $params['staff_id']);
        }
         
        if ($limit)
            $select->limitPage($page, $limit);
        $result = $db->fetchAll($select);
		
		
		
        $total = $db->fetchOne("select FOUND_ROWS()");
        return $result;
    }
    
    
    function getTeam($staff_id)
    {
        $db = Zend_Registry::get('db');
        if (isset($staff_id) ) {
            $select = $db->select()->from(array('p' => $this->_name), array('p.team'));
            $select->join(array('t' => 'team'), 'p.team = t.id', array('team_id' => 't.id', 'team_name' => 't.name'));
            $select->where('staff_id = ? ', $staff_id);
            $select->where('type = ?', '1');
            $result = $db->fetchAll($select,'team');            
            return $result;
        } else
            return - 1;
    }
    
    function getPermission($staff_id)
    {
        $db = Zend_Registry::get('db');
        if (isset($staff_id) ) {
            $select = $db->select()->from(array('p' => $this->_name), array('p.*'));
            $select->where('staff_id = ? ', $staff_id);
            $result = $db->fetchAll($select);            
            return $result;
        } else
            return false;
    }
    
    function getArea($staff_id)
    {
        $db = Zend_Registry::get('db');
        if (isset($staff_id) ) {
            $select = $db->select()->from(array('p' => $this->_name), array('p.area_id'));
            $select->join(array('a' => 'area'), 'p.area_id = a.id', array('area_id' => 'a.id', 'area_name' => 'a.name'));
            $select->where('staff_id = ? ', $staff_id);
            $select->where('type = ?', '2');
            $result = $db->fetchAll($select,'area_id');
            return $result;
        } else
            return - 1;
    }
    
    public function get_region_cache($staff_id = null)
    {
        $cache      = Zend_Registry::get('cache');
        $result     = $cache->load($this->_name.'_region_new_cache');

        if ($result === false || ( !is_null($staff_id) && !isset($result[$staff_id]) )) {
            $data = $this->fetchAll();
            $result = array();

            if ($data){
                $QRegion = new Application_Model_RegionalMarket();

                foreach ($data as $item){
                    if (!isset($result[$item['staff_id']]))
                        $result[$item['staff_id']] = array();

                    $where = $QRegion->getAdapter()->quoteInto('area_id = ?', $item['area_id']);
                    $regions = $QRegion->fetchAll($where);
                    
                    if ($regions)
                        foreach ($regions as $reg)
                            $result[$item['staff_id']][] = $reg['id'];                    
                }
            }

            $cache->save($result, $this->_name.'_region_new_cache', array(), null);
        }

        return is_null($staff_id) ? $result : ( isset($result[$staff_id]) ? $result[$staff_id] : false );
    }
    
    public function get_team_cache($staff_id = null)
    {
        $cache      = Zend_Registry::get('cache');
        $result     = $cache->load($this->_name.'_team_new_cache');

        if ($result === false || ( !is_null($staff_id) && !isset($result[$staff_id]) )) {
            $data = $this->fetchAll();
            $result = array();
            
            if ($data){
                $QTeam = new Application_Model_Team();

                foreach ($data as $item){
                   if (!isset($result[$item['staff_id']]))
                        $result[$item['staff_id']] = array();

                    $where = $QTeam->getAdapter()->quoteInto('id = ?', $item['team']);
                    $teams = $QTeam->fetchAll($where);
                    
                    
                    if ($teams)
                        foreach ($teams as $reg)
                            $result[$item['staff_id']][] = $reg['id'];                    
                }
            }

            $cache->save($result, $this->_name.'_team_new_cache', array(), null);
        }
        
        return is_null($staff_id) ? $result : ( isset($result[$staff_id]) ? $result[$staff_id] : false );
    }
    
}
