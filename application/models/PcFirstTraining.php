<?php
class Application_Model_PcFirstTraining extends Zend_Db_Table_Abstract
{
	protected $_name = 'pc_first_training';

	function fetchPagination($page, $limit, &$total, $params){

        $bkk_list = array(
            81,82,83,85,86,87,115,90,91,92,
            93,113,94,95,96,88,89,117,110,111,
            112,97,109,98,99,100,101,102,114,103,
            104,105,116,106,107,108);

		$d1 = explode('/', $params['from']);
        $from = $d1[2].'-'.$d1[1].'-'.$d1[0];

        $d2 = explode('/', $params['to']);
        $to = $d2[2].'-'.$d2[1].'-'.$d2[0];

        $db = Zend_Registry::get('db');

        if (isset($params['export']) && $params['export'] == 1) { 

            $get = array(
                'prefix'        => 'pf.name_th',
                'prefix_en'     => 'pf.name_en',
                'gender_name'   => new Zend_Db_Expr("(CASE WHEN pft.gender = 0 THEN 'F' ELSE 'M' END)"),
                'firstname'     => 'pft.firstname',
                'lastname'      => 'pft.lastname',
                'firstname_en'  => 'pft.firstname_en',
                'lastname_en'   => 'pft.lastname_en',
                'area_name'     => 'a.name',
                'province_name' => 'rm.name',
                'phone_number'  => 'pft.phone_number',
                'dob'           => 'pft.dob',
                'email'         => 'pft.email',
                'shirt_size'    => new Zend_Db_Expr(
                    "CASE 
                        WHEN pft.shirt_size = 1 THEN 'XXS'
                        WHEN pft.shirt_size = 2 THEN 'SS' 
                        WHEN pft.shirt_size = 3 THEN 'S'
                        WHEN pft.shirt_size = 4 THEN 'M'
                        WHEN pft.shirt_size = 5 THEN 'L'
                        WHEN pft.shirt_size = 6 THEN 'XL'
                        WHEN pft.shirt_size = 7 THEN '2XL'
                        WHEN pft.shirt_size = 8 THEN '3XL'
                        WHEN pft.shirt_size = 9 THEN '5XL' END"),
                'check_in'      => new Zend_Db_Expr("DATE(pft.check_in)"),
                'public_id'     => 'pft.public_id',
                'joined_at'     => 'pft.joined_at',
            );

        } else {

            $get = array(
                'pft_id'        => new Zend_Db_Expr("SQL_CALC_FOUND_ROWS pft.id"),
                'check_in'      => 'pft.check_in',
                'area_name'     => 'a.name',
                'public_id'     => 'pft.public_id',
                'prefix'        => 'pf.name_th',
                'prefix_en'     => 'pf.name_en',
                'staff_name'    => new Zend_Db_Expr("CONCAT(TRIM(pft.firstname), ' ', TRIM(pft.lastname))"),
                'staff_name_en' => new Zend_Db_Expr("CONCAT(TRIM(pft.firstname_en), ' ', TRIM(pft.lastname_en))"),
                'marital_status'=> 'ms.name_th',
                'dob'           => 'pft.dob',
                'email'         => 'pft.email',
                'phone_number'  => 'pft.phone_number',
                'group_name'    => 'g.name',
                'filepic'       => 'pft.filepic',
                'gender_name'   => new Zend_Db_Expr("(CASE WHEN pft.gender = 0 THEN 'F' ELSE 'M' END)"),
                'joined_at'     => new Zend_Db_Expr("DATE(pft.joined_at)"),
                'sso_hospital_1'=> new Zend_Db_Expr("(SELECT name FROM sso_hospital WHERE id = pft.sso_hospital_1)"),
                'sso_hospital_2'=> new Zend_Db_Expr("(SELECT name FROM sso_hospital WHERE id = pft.sso_hospital_2)"),
                'sso_hospital_3'=> new Zend_Db_Expr("(SELECT name FROM sso_hospital WHERE id = pft.sso_hospital_3)"),
            );

        }

        $select = $db->select()
            ->from(array('pft' => 'pc_first_training'), $get)
            ->join(array('g'  => 'group'), 'pft.group_id = g.id', array())
            ->join(array('rm' => 'regional_market'), 'pft.province_id = rm.id', array())
            ->join(array('a'  => 'area'), 'rm.area_id = a.id', array())
            ->join(array('pf' => 'prefixs'), 'pft.prefix = pf.id', array())
            ->joinLeft(array('ms' => 'marital_status'), 'pft.marital_status = ms.id', array())
            ->order(array('DATE(pft.check_in) DESC', 'pft.firstname ASC', 'pft.lastname ASC'));

        $select->where('pft.check_in >= ?', $from.' 00:00:00');
        $select->where('pft.check_in <= ?', $to.' 23:59:59');

        // Add Filter Public ID
        if (isset($params['public_id']) && $params['public_id']) {
        	$select->where('pft.public_id = ?', $params['public_id']);
        }

        // Add Filter Staff Name
        if (isset($params['staff_name']) && $params['staff_name']) {
        	$select->where("CONCAT(pft.firstname, ' ', pft.lastname) LIKE ?", "%".$params['staff_name']."%");
        }

        if (isset($params['chk_bkk']) && $params['chk_bkk']) {
            $select->where('a.name LIKE ?', "BKK%");
        } else {

            // Add Filter Area
            if (isset($params['area_id']) && $params['area_id']) {
                if (is_array($params['area_id']) && count($params['area_id']))
                    $select->where('rm.area_id IN (?)', $params['area_id']);
                elseif (is_numeric($params['area_id']))
                    $select->where('rm.area_id = ?', intval($params['area_id']));
                else
                    $select->where('1=0', 1);
            }

            // Add Filter Province
            if (isset($params['regional_market']) && $params['regional_market']) {
                if (is_array($params['regional_market']) && count($params['regional_market']))
                    $select->where('s.regional_market IN (?)', $params['regional_market']);
                elseif (is_numeric($params['regional_market']))
                    $select->where('s.regional_market = ?', intval($params['regional_market']));
                else
                    $select->where('1=0', 1);
            }

        }



        // check ermission ASM, ASM Stand by, Sale Admin, Traning
        if ( isset($params['asm']) && $params['asm'] ) {
            $QAsm = new Application_Model_Asm();
            $list_regions = $QAsm->get_cache($params['asm']);
            $list_regions = isset($list_regions['province']) && is_array($list_regions['province']) ? $list_regions['province'] : array();

            if (count($list_regions) > 0)
                $select->where( 'rm.id IN (?)', $list_regions);
            else
                $select->where('1=0', 1);
        }

        if ($limit)
            $select->limitPage($page, $limit);

        //print_r($params);
        //echo $select;
        $result = $db->fetchAll($select);

        if ($limit)
            $total = $db->fetchOne("select FOUND_ROWS()");

        
        return $result;
    }

    function getStaffDetails($pft_id) {

        $db = Zend_Registry::get('db');

        $get = array(
        	'pft_id'		=> 'pft.id',
            'public_id' 	=> 'pft.public_id',
            'staff_name_en' => new Zend_Db_Expr("CONCAT(TRIM(pft.firstname_en), ' ', TRIM(pft.lastname_en))"),
            'staff_name_th' => new Zend_Db_Expr("CONCAT(TRIM(pft.firstname), ' ', TRIM(pft.lastname))"),
            'gender_name'	=> new Zend_Db_Expr("(CASE WHEN pft.gender = 0 THEN 'Female' ELSE 'Male' END)"),
            'dob'			=> 'pft.dob',
            'email'			=> 'pft.email',
            'phone_number'	=> 'pft.phone_number',
            'area_name'		=> 'a.name',
            'group_name'	=> 'g.name',
            'check_in'		=> 'pft.check_in',
            'shirt_size'	=> new Zend_Db_Expr(
            	"CASE 
					WHEN pft.shirt_size = 1 THEN 'XXS'
					WHEN pft.shirt_size = 2 THEN 'SS' 
					WHEN pft.shirt_size = 3 THEN 'S'
					WHEN pft.shirt_size = 4 THEN 'M'
					WHEN pft.shirt_size = 5 THEN 'L'
					WHEN pft.shirt_size = 6 THEN 'XL'
					WHEN pft.shirt_size = 7 THEN '2XL'
					WHEN pft.shirt_size = 8 THEN '3XL'
					WHEN pft.shirt_size = 9 THEN '5XL' END"),
            'filepic'		=> 'pft.filepic',
            'created_by'    => new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)"),
            'created_at'    => 'pft.created_at',
        );

        $select = $db->select()
            ->from(array('pft' 	=> 'pc_first_training'), $get)
            ->join(array('g'	=> 'group')				, 'pft.group_id = g.id'		, array())
            ->join(array('rm' 	=> 'regional_market')	, 'pft.province_id = rm.id'	, array())
            ->join(array('a' 	=> 'area')				, 'rm.area_id = a.id'		, array())
            ->join(array('s' 	=> 'staff')				, 'pft.created_by = s.id'	, array())
            ->where('pft.id = ?', $pft_id);
        
        //echo $select; die;
        $result = $db->fetchRow($select);
        return $result;
    }

}
