<?php
class Application_Model_TimeStaffExpired extends Zend_Db_Table_Abstract
{
    protected $_name = 'time_staff_expired';

    function fetchPagination($page, $limit, &$total, $params){
        $db = Zend_Registry::get('db');
        $select = $db->select()
            ->from(array('p' => $this->_name),
                array(new Zend_Db_Expr('SQL_CALC_FOUND_ROWS p.id'), 'p.*'));

        $select->joinLeft(array('s' => 'staff'), 'p.staff_id = s.id', array(
            's.email',
            's.code',
            's.title',
            's.firstname',
            's.lastname',
            's.team',
            's.regional_market',
            's.phone_number'));

        if (isset($params['asm']) and $params['asm']) {
            $QAsm = new Application_Model_Asm();
            $list_regions = $QAsm->get_region_cache($params['asm']);

            if ($list_regions && is_array($list_regions) && count($list_regions) > 0)
                $select->where('s.regional_market IN (?)', $list_regions);
            else
                $select->where('1=0', 1);
            $select->where('s.team in (75,119)', '');

        } elseif (isset($params['asm_standby']) and $params['asm_standby']) {
            $QAsm = new Application_Model_AsmStandby();
            $list_regions = $QAsm->get_region_cache($params['asm_standby']);

            if ($list_regions && is_array($list_regions) && count($list_regions) > 0)
                $select->where('s.regional_market IN (?)', $list_regions);
            else
                $select->where('1=0', 1);
            $select->where('s.team in (75,119)', '');
        } elseif (isset($params['other']) and $params['other']) {
            $QTimePermission = new Application_Model_TimePermission();
            $list_regions = $QTimePermission->get_region_cache($params['other']);

            if ($list_regions && is_array($list_regions) && count($list_regions) > 0)
                $select->where('s.regional_market IN (?)', $list_regions);
            else
                $select->where('1=0', 1);

            ///////////////////////////////////////////////////////////////
            $list_teams = $QTimePermission->get_team_cache($params['other']);

            if ($list_teams && is_array($list_teams) && count($list_teams) > 0)
                $select->where('s.team IN (?)', $list_teams);
            else
                $select->where('1=0', 1);

        }

        if (isset($params['name']) and $params['name'])
            $select->where('p.name LIKE ?', '%'.$params['name'].'%');

        $select->where('approved_at is null' , null);
        $select->group('p.staff_id');

        $select->order('p.locked_at', 'DESC');
        $select->limitPage($page, $limit);
        $result = $db->fetchAll($select);
        $total = $db->fetchOne("select FOUND_ROWS()");
        return $result;
    }

}