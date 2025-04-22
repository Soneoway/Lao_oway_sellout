<?php
class Application_Model_MonthlyBirthdayPc extends Zend_Db_Table_Abstract
{
	protected $_name = 'monthly_birthday_pc';

	public function getMbpByArea($params) {

        $db = Zend_Registry::get('db');

        $get = array(
        	'area_id'		=> 'a.id',
            'area_name'		=> 'a.name',
            'month_year'	=> 'mbp.month_year',
            'cnt_rd'        => new Zend_Db_Expr("COUNT(CASE WHEN g.id IN (28,35) THEN mbp.staff_code END)"),
            'cnt_assist'    => new Zend_Db_Expr("COUNT(CASE WHEN g.id IN (33,34) THEN mbp.staff_code END)"),
            'cnt_asm'       => new Zend_Db_Expr("COUNT(CASE WHEN g.id IN (5,16) THEN mbp.staff_code END)"),
            'cnt_sale'      => new Zend_Db_Expr("COUNT(CASE WHEN g.id IN (9,31) THEN mbp.staff_code END)"),
            'cnt_pc'        => new Zend_Db_Expr("COUNT(CASE WHEN g.id = 4 THEN mbp.staff_code END)"),
            'cnt_admin'     => new Zend_Db_Expr("COUNT(CASE WHEN g.id = 12 THEN mbp.staff_code END)"),
            'cnt_pcm_leader'=> new Zend_Db_Expr("COUNT(CASE WHEN g.id = 36 THEN mbp.staff_code END)"),
            'cnt_pcm'       => new Zend_Db_Expr("COUNT(CASE WHEN g.id = 17 THEN mbp.staff_code END)"),
            'cnt_tms'       => new Zend_Db_Expr("COUNT(CASE WHEN g.id = 37 THEN mbp.staff_code END)"),
            'cnt_bm'        => new Zend_Db_Expr("COUNT(CASE WHEN g.id = 30 THEN mbp.staff_code END)"),
            'cnt_total'		=> new Zend_Db_Expr("COUNT(mbp.staff_code)"),

            'cnt_hr_approve_by' => new Zend_Db_Expr("COUNT(mbp.hr_by)"),
            'cnt_admin_approve' => new Zend_Db_Expr("COUNT(mbp.admin_by)"),
            'cnt_mgt_approve'   => new Zend_Db_Expr("COUNT(mbp.mgt_by)"),
        );

        $select = $db->select()
            ->from(array('a' => 'area'), $get)
            ->joinLeft(array('mbp' => 'monthly_birthday_pc'), 
            	"	a.name = mbp.area_name COLLATE utf8_unicode_ci
            		AND mbp.month_year = '".$params['month_year']."'
            	", array())
            ->joinLeft(array('g' => 'group'), 'mbp.staff_group COLLATE utf8_unicode_ci = g.name', array())
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

        // echo $select; die;
        $result = $db->fetchAll($select);
        return $result;

    }

    public function getMonthlyBirthdayPcDetails($params) {

        $db = Zend_Registry::get('db');

        $get = array(
            'mhl_id'        => 'mbp.id',
            'area_name'     => 'mbp.area_name',
            'staff_code'    => 'mbp.staff_code',
            'staff_name'    => 'mbp.staff_name',
            'staff_group'   => 'mbp.staff_group',
            'phone_number'  => 'mbp.phone_number',
            'asm_name'      => 'mbp.asm_name',
            'rd_name'       => 'mbp.rd_name',
            'month_year'    => 'mbp.month_year',

            'created_by_code'   => 's.code',
            'created_by_name'   => new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)"),
            'created_by_group'  => 'g.name',
            'created_at'        => 'mbp.created_at',
        );

        $select = $db->select()
            ->from(array('mbp' => 'monthly_birthday_pc'), $get)
            ->joinLeft(array('a' => 'area') , 'mbp.area_name COLLATE utf8_unicode_ci = a.name', array())
            ->joinLeft(array('s' => 'staff'), 'mbp.created_by = s.id'   , array())
            ->joinLeft(array('g' => 'group'), 's.group_id = g.id'       , array())
            ->where('mbp.month_year = ?', $params['month_year']);
            // ->order(array('mbp.area_name ASC','mbp.staff_code'));

        if (isset($params['flag']) && $params['flag'] == 1) {
            $select->group('mbp.asm_name');
            $select->order(new Zend_Db_Expr("COUNT(mbp.asm_name) DESC"));
        } else {
            $select->order(array('mbp.area_name ASC','mbp.staff_code'));
        }

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

        // echo $select; die;
        $result = $db->fetchAll($select);
        return $result;

    }

}
