<?php
class Application_Model_MonthlyHeadcountList extends Zend_Db_Table_Abstract
{
	protected $_name = 'monthly_headcount_list';

	public function getMhlByArea($params) {

        $db = Zend_Registry::get('db');

        $get = array(
        	'area_id'		=> 'a.id',
            'area_name'		=> 'a.name',
            'month_year'	=> 'mhl.month_year',
            'cnt_rd'		=> new Zend_Db_Expr("COUNT(CASE WHEN g.id IN (28,35) THEN mhl.staff_code END)"),
            'cnt_assist' 	=> new Zend_Db_Expr("COUNT(CASE WHEN g.id IN (33,34) THEN mhl.staff_code END)"),
            'cnt_asm'		=> new Zend_Db_Expr("COUNT(CASE WHEN g.id IN (5,16) THEN mhl.staff_code END)"),
            'cnt_sale'		=> new Zend_Db_Expr("COUNT(CASE WHEN g.id IN (9,31) THEN mhl.staff_code END)"),
            'cnt_pcm_leader'=> new Zend_Db_Expr("COUNT(CASE WHEN g.id = 36 THEN mhl.staff_code END)"),
            'cnt_pcm'		=> new Zend_Db_Expr("COUNT(CASE WHEN g.id = 17 THEN mhl.staff_code END)"),
            'cnt_tms'		=> new Zend_Db_Expr("COUNT(CASE WHEN g.id = 37 THEN mhl.staff_code END)"),
            'cnt_total'		=> new Zend_Db_Expr("COUNT(mhl.staff_code)"),

            'cnt_hr_approve_by' => new Zend_Db_Expr("COUNT(mhl.hr_by)"),
            'cnt_admin_approve' => new Zend_Db_Expr("COUNT(mhl.admin_by)"),
            'cnt_mgt_approve'   => new Zend_Db_Expr("COUNT(mhl.mgt_by)"),
        );

        $select = $db->select()
            ->from(array('a' => 'area'), $get)
            ->joinLeft(array('mhl' => 'monthly_headcount_list'), 
            	"	a.name = mhl.area_name COLLATE utf8_unicode_ci
            		AND mhl.month_year = '".$params['month_year']."'
            	", array())
            ->joinLeft(array('g' => 'group'), 'mhl.staff_group COLLATE utf8_unicode_ci = g.name', array())
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

        // EXport UPC List
        if ( isset($params['export']) && $params['export'] == 3 ) {
            $select->where('a.name NOT LIKE ?', "BKK%");
        }

        // EXport BKK List
        if ( isset($params['export']) && $params['export'] == 4 ) {
            $select->where('a.name LIKE ?', "BKK%");
        }

        // echo $select; die;
        $result = $db->fetchAll($select);
        return $result;

    }

    public function getMonthlyHeadcountDetails($params) {

        $db = Zend_Registry::get('db');

        $get = array(
            'mhl_id'        => 'mhl.id',
            'area_name'     => 'mhl.area_name',
            'staff_code'    => 'mhl.staff_code',
            'staff_name'    => 'mhl.staff_name',
            'staff_group'   => 'mhl.staff_group',
            'phone_number'  => 'mhl.phone_number',
            'rd_name'       => 'mhl.rd_name',
            'month_year'    => 'mhl.month_year',

            'created_by_code'   => 's.code',
            'created_by_name'   => new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)"),
            'created_by_group'  => 'g.name',
            'created_at'        => 'mhl.created_at',
        );

        $select = $db->select()
            ->from(array('mhl' => 'monthly_headcount_list'), $get)
            ->joinLeft(array('a' => 'area') , 'mhl.area_name COLLATE utf8_unicode_ci = a.name', array())
            ->joinLeft(array('s' => 'staff'), 'mhl.created_by = s.id'   , array())
            ->joinLeft(array('g' => 'group'), 's.group_id = g.id'       , array())
            ->where('mhl.month_year = ?', $params['month_year'])
            ->order(array('mhl.area_name ASC','mhl.staff_code'));

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
