<?php
class Application_Model_MonthlyKpiHeadcountList extends Zend_Db_Table_Abstract
{
	protected $_name = 'monthly_kpi_headcount_list';

	public function getMkhlByArea($params) {

        $db = Zend_Registry::get('db');

        $get = array(
        	'area_id'		=> 'a.id',
            'area_name'		=> 'a.name',
            'month_year'	=> 'mkhl.month_year',
            'cnt_assist' 	=> new Zend_Db_Expr("COUNT(CASE WHEN g.id IN (33,34) THEN mkhl.staff_code END)"),
            'cnt_sale'		=> new Zend_Db_Expr("COUNT(CASE WHEN g.id IN (9,31) THEN mkhl.staff_code END)"),
            'cnt_pcm_leader'=> new Zend_Db_Expr("COUNT(CASE WHEN g.id = 36 THEN mkhl.staff_code END)"),
            'cnt_pcm'		=> new Zend_Db_Expr("COUNT(CASE WHEN g.id = 17 THEN mkhl.staff_code END)"),
            'cnt_tms'		=> new Zend_Db_Expr("COUNT(CASE WHEN g.id = 37 THEN mkhl.staff_code END)"),
            'cnt_total'		=> new Zend_Db_Expr("COUNT(mkhl.staff_code)"),
        );

        $select = $db->select()
            ->from(array('a' => 'area'), $get)
            ->joinLeft(array('mkhl' => 'monthly_kpi_headcount_list'), 
            	"	a.name = mkhl.area_name COLLATE utf8_unicode_ci
            		AND mkhl.month_year = '".$params['month_year']."'
            	", array())
            ->joinLeft(array('g' => 'group'), 'mkhl.staff_group COLLATE utf8_unicode_ci = g.name', array())
            ->where('a.id NOT IN (?)', array(48,49,72))
            ->group('a.id')
            ->order('a.name ASC');

		// Add Filter Area
        if (isset($params['area_id']) && $params['area_id']) {
            if (is_array($params['area_id']) && count($params['area_id']))
                $select->where('a.id IN (?)', $params['area_id']);
            elseif (is_numeric($params['area_id']))
                $select->where('a.id = ?', intval($params['area_id']));
            else
                $select->where('1=0', 1);
        }

        // check ermission ASM, ASM Stand by, Sale Admin, Traning
        if ( isset($params['asm']) && $params['asm'] ) {
            $QAsm = new Application_Model_Asm();
            $list_regions = $QAsm->get_cache($params['asm']);
            $list_regions = isset($list_regions['area']) && is_array($list_regions['area']) ? $list_regions['area'] : array();

            if (count($list_regions) > 0)
                $select->where( 'a.id IN (?)', $list_regions);
            else
                $select->where('1=0', 1);
        }

        // Check If UG 
        if ( isset($params['staff_id']) && $params['staff_id'] ) {
            $select->where('g.id IN (?)', array(17,36));
        }

        // echo $select; die;
        $result = $db->fetchAll($select);
        return $result;

    }

    public function getMonthlyKpiHeadcountDetails($params) {

        $db = Zend_Registry::get('db');

        $get = array(
            'mhl_id'        => 'mkhl.id',
            'area_name'     => 'mkhl.area_name',
            'staff_code'    => 'mkhl.staff_code',
            'staff_name'    => 'mkhl.staff_name',
            'staff_nickname'=> 'mkhl.staff_nickname',
            'staff_group'   => 'mkhl.staff_group',
            'phone_number'  => 'mkhl.phone_number',
            'rd_code'       => 'mkhl.rd_code',
            'rd_name'       => 'mkhl.rd_name',
            'month_year'    => 'mkhl.month_year',
            'remark'        => 'mkhl.remark',

            'created_by_code'   => 's.code',
            'created_by_name'   => new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)"),
            'created_by_group'  => 'g.name',
            'created_at'        => 'mkhl.created_at',
        );

        $select = $db->select()
            ->from(array('mkhl' => 'monthly_kpi_headcount_list'), $get)
            ->joinLeft(array('a' => 'area') , 'mkhl.area_name COLLATE utf8_unicode_ci = a.name', array())
            ->joinLeft(array('s' => 'staff'), 'mkhl.created_by = s.id'   , array())
            ->joinLeft(array('g' => 'group'), 's.group_id = g.id'       , array())
            ->where('mkhl.month_year = ?', $params['month_year'])
            ->order(array('mkhl.area_name ASC','mkhl.staff_code'));

        // Add Filter Area
        if (isset($params['area_id']) && $params['area_id']) {
            if (is_array($params['area_id']) && count($params['area_id']))
                $select->where('a.id IN (?)', $params['area_id']);
            elseif (is_numeric($params['area_id']))
                $select->where('a.id = ?', intval($params['area_id']));
            else
                $select->where('1=0', 1);
        }

        // check ermission ASM, ASM Stand by, Sale Admin, Traning
        if ( isset($params['asm']) && $params['asm'] ) {
            $QAsm = new Application_Model_Asm();
            $list_regions = $QAsm->get_cache($params['asm']);
            $list_regions = isset($list_regions['area']) && is_array($list_regions['area']) ? $list_regions['area'] : array();

            if (count($list_regions) > 0)
                $select->where( 'a.id IN (?)', $list_regions);
            else
                $select->where('1=0', 1);
        }

        // Check If UG 
        if ( isset($params['staff_id']) && $params['staff_id'] ) {
            $select->where('mhl.staff_group IN (?)', array('TRAINING','TRAINING LEADER'));
        }

        // echo $select; die;
        $result = $db->fetchAll($select);
        return $result;

    }

}
