<?php
class Application_Model_StaffUnPunish extends Zend_Db_Table_Abstract
{
	protected $_name = 'staff_unpunish';

	function getList($params) {

    	$db = Zend_Registry::get('db');

    	$get = array(
            'su_id'         => 'su.id',
            'con_01'		=> 'su.condition_01',
            'con_02'		=> 'su.condition_02',
            'unpunish_type'	=> 'ul.name',
            'from_date'		=> 'su.from_date',

            'area_name'     => 'a.name',
            'staff_code'    => 's.code',
            'staff_name'    => new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)"),
            'staff_group'   => 'g.name',     
        );

        $select = $db->select()
            ->from(array('su'=> 'staff_unpunish')	, $get)
            ->join(array('ul'=> 'unpunish_list')	, 'su.type_id = ul.id'			, array())
            ->join(array('s' => 'staff')			, 'su.staff_id = s.id'  		, array())
            ->join(array('g' => 'group')			, 's.group_id = g.id'   		, array())
			->join(array('rm'=> 'regional_market')	, 's.regional_market = rm.id'	, array())
			->join(array('a' => 'area')				, 'rm.area_id = a.id'   		, array())
			->where('su.from_date <= ?', $params['from'])
			->where('su.to_date >= ?', $params['to'])
            ->order('su.created_at ASC');

		if (isset($params['staff_code']) && $params['staff_code']) {
			$select->where('s.code = ?', $params['staff_code']);
		}

		if (isset($params['staff_name']) && $params['staff_name']) {
			$select->where('CONCAT(s.firstname, \' \',s.lastname) LIKE ?', "%".$params['staff_name']."%");
		}

		// Add Filter Area
        if (isset($params['area_id']) && $params['area_id']) {
            if (is_array($params['area_id']) && count($params['area_id']))
                $select->where('rm.area_id IN (?)', $params['area_id']);
            elseif (is_numeric($params['area_id']))
                $select->where('rm.area_id = ?', intval($params['area_id']));
            else
                $select->where('1=0', 1);
        }

        // Add Filter Un-Punish Type
        if (isset($params['unpunish_id']) && $params['unpunish_id']) {
            if (is_array($params['unpunish_id']) && count($params['unpunish_id']))
                $select->where('su.id IN (?)', $params['unpunish_id']);
            elseif (is_numeric($params['unpunish_id']))
                $select->where('su.id = ?', intval($params['unpunish_id']));
            else
                $select->where('1=0', 1);
        }

        // echo $select; die;
        $result = $db->fetchAll($select);
        return $result;

    }

    function getDetail($id) {

        $db = Zend_Registry::get('db');

        $get = array(
            'su_id'         => 'su.id',
            'from_date'     => new Zend_Db_Expr("DATE_FORMAT(su.from_date, '%d/%m/%Y')"),  
            'to_date'       => new Zend_Db_Expr("DATE_FORMAT(su.to_date, '%d/%m/%Y')"),  
            'con_01'		=> 'su.condition_01',
            'con_02'     	=> 'su.condition_02',
            'unpunish_id'	=> 'su.type_id',

            'staff_province'=> 'rm.name',
            'staff_id'      => 's.id',
            'staff_name'    => new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)"),
            'staff_email'   => 's.email',
        );

        $select = $db->select()
            ->from(array('su' => 'staff_unpunish'), $get)
            ->joinLeft(array('s' => 'staff')            , 'su.staff_id = s.id'          , array())
            ->joinLeft(array('rm'=> 'regional_market')  , 's.regional_market = rm.id'   , array())
            ->joinLeft(array('a' => 'area')             , 'rm.area_id = a.id'           , array())
            ->where('su.id = ?', $id);

        //echo $select; //die;
        $result = $db->fetchRow($select);
        return $result;

    }

    function insertstaffimport($data, $old_name, $new_name, $month_year) {

        $db = Zend_Registry::get('db');
        $arr = array();
        
        $db->beginTransaction();

        try {
            // Attempt to execute one or more queries:
            $userStorage = Zend_Auth::getInstance()->getStorage()->read();

            for ($i=2;$i<count($data)+1;$i++) {

                // check staff
                $select = $db->select()
                    ->from(array('s'=>'staff'),
                        array(  
                            'staff_id'   =>  's.id',
                            'staff_code' =>  's.code',
                            'firstname'  =>  's.firstname',
                            'lastname'   =>  's.lastname',
                        )
                    )
                    ->where('s.code = ?', $data[$i]['C']);
                $result = $db->fetchRow($select);

                if ($result) {
                    $staff_id = $result['staff_id'];
                } else {
                    throw new Exception('ไม่มีรหัสพนักงาน : '.$data[$i]['C'].' ในระบบค่ะ!');
                }

                $con_01 = $con_02 = $type_id = 0;

                // Check 200K
                if ( !in_array($data[$i]['F'], array('Yes','No')) ) { 
                    throw new Exception('Staff Code : '.$data[$i]['C'].' - Condition Type of 200K is Yes / No เท่านั้นค่ะ!');
                } else {
                    if ( trim($data[$i]['F']) == 'Yes' ) { $con_01 = 1; } 
                }

                // Check Index
                if ( !in_array($data[$i]['G'], array('Yes','No')) ) { 
                    throw new Exception('Staff Code : '.$data[$i]['C'].' - Condition Type of Index is Yes / No เท่านั้นค่ะ!');
                } else {
                    if ( trim($data[$i]['G']) == 'Yes' ) { $con_02 = 1; } 
                }

                // Check Un-Punish Type
                if ( isset($data[$i]['H']) && $data[$i]['H'] ) { 
                    if ( $data[$i]['H'] >= 1 && $data[$i]['H'] <= 8 ) { $type_id = $data[$i]['H']; } 
                    else { throw new Exception('Staff Code : '.$data[$i]['C'].' - Un-Punish Code Not Exist!'); }
                } 

                $arr = array( 
                    'staff_id'      => $staff_id,
                    'type_id'       => $type_id,
                    'from_date'     => date('Y-m-01', strtotime($month_year)),
                    'to_date'       => date('Y-m-t', strtotime($month_year)),
                    'condition_01'  => $con_01,
                    'condition_02'  => $con_02,
                    'created_by'    => $userStorage->id,
                    'created_at'    => date('Y-m-d H:i:s'),
                );
                
                $db->insert('staff_unpunish', $arr);
              
            }

            // record upload log
            $QFileLog = new Application_Model_FileUploadLog();
            
            $cnt = $i-2;
            $data = array(
                'staff_id'          => $userStorage->id,
                'folder'            => '\public\upload_pc_unpunish',
                'filename'          => $new_name,
                'type'              => 'pc-unpunish upload',
                'real_file_name'    => $old_name,
                'uploaded_at'       => time(),
                'total'             => $cnt,
                'succeed'           => $cnt
            );

            $log_id = $QFileLog->insert($data);

            // If all succeed, commit the transaction and all changes
            // are committed at once.
            $db->commit();
            return array('result' => 'success', 'cnt' => $cnt);
         
        } catch (Exception $e) {
            // If any of the queries failed and threw an exception,
            // we want to roll back the whole transaction, reversing
            // changes made in the transaction, even those that succeeded.
            // Thus all changes are committed together, or none are.

            $db->rollBack();
            return array('result' => 'fail', 'msg' => $e->getMessage());
        }
   
    }

}