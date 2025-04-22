<?php
class Application_Model_StockShopLogs extends Zend_Db_Table_Abstract {
	protected $_name = 'stock_shop_log';

	/**
	 * Get all staff id with perrmisson view reports of all areas
	 * @return array - staff id list
	 */
	function fetchPagination($page, $limit, &$total, $params) {
		$db          = Zend_Registry::get('db');
		$userStorage = Zend_Auth::getInstance()->getStorage()->read();
		$data        = array();

		$permission_group = array(ASM_ID, SALES_ID, PGPB_ID);

		$QStore = new Application_Model_Store();

		$select = $db->select()
		             ->from(array('ss' => $this->_name),
			array(
				new Zend_Db_Expr('SQL_CALC_FOUND_ROWS ss.id'), 'ss.*',
				'st_id'       => 'st.id',
				'st_name'     => 'st.name',
				'st_area'     => 'a.name',
				'st_province' => 'rm.name',
				'st_district' => 'rm2.name',
				'sale_name'   => new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)"),
				'sale_group'  => 'g.name',
				'sum_total'   => new Zend_Db_Expr("SUM(ss.total_imei)"),
				'sum_success' => new Zend_Db_Expr("SUM(ss.success_imei)")
			));

		$select->joinLeft(array('st'  => 'store'), 'ss.store_id = st.id', array());
		$select->joinLeft(array('rm'  => 'regional_market'), 'st.regional_market = rm.id', array());
		$select->joinLeft(array('rm2' => 'regional_market'), 'st.district = rm2.id', array());
		$select->joinLeft(array('a'   => 'area'), 'rm.area_id = a.id', array());
		$select->joinLeft(array('s'   => 'staff'), 'ss.created_by = s.id', array());
		$select->joinLeft(array('g'   => 'group'), 's.group_id = g.id', array());
		$select->where('st.del IS NULL');
		$select->group(array('DATE(ss.created_at)', 'ss.created_by', 'ss.store_id'));
		$select->order(array('ss.created_at DESC', 'a.name ASC'));

		if (isset($params['store_id']) and $params['store_id']) {
			$select->where('st.id LIKE ?', '%'.$params['store_id'].'%');
		}

		if (isset($params['name']) and $params['name']) {
			$select->where('st.name LIKE ?', '%'.$params['name'].'%');
		}

		if (isset($params['area_id']) && $params['area_id']) {
			if (is_array($params['area_id']) && count($params['area_id'])) {
				$select->where('a.id IN (?)', $params['area_id']);
			} elseif (is_numeric($params['area_id'])) {
				$select->where('a.id = ?', intval($params['area_id']));
			} else {

				$select->where('1=0', 1);
			}
		}

		if (isset($params['regional_market']) && $params['regional_market']) {
			if (is_array($params['regional_market']) && count($params['regional_market'])) {
				$select->where('rm.id IN (?)', $params['regional_market']);
			} elseif (is_numeric($params['regional_market'])) {
				$select->where('rm.id = ?', intval($params['regional_market']));
			} else {

				$select->where('1=0', 1);
			}
		}

		if (isset($params['from']) && $params['from']) {
			$from = date_create_from_format("d/m/Y", $params['from'])->format("Y-m-d");
			$select->where('ss.created_at >= ?', $from.' 00:00:00');
		}
		if (isset($params['to']) && $params['to']) {
			$to = date_create_from_format("d/m/Y", $params['to'])->format("Y-m-d");
			$select->where('ss.created_at <= ?', $to.' 23:59:59');
		}

		if (isset($params['staff_name']) && $params['staff_name']) {
			$select->where('CONCAT(s.firstname, " ",s.lastname) LIKE ?', '%'.$params['staff_name'].'%');
		}

		// ASM Permission
        if (isset($params['asm']) && $params['asm']) {
            $QAsm = new Application_Model_Asm();
            $list_regions = $QAsm->get_cache($params['asm']);
            $list_regions = isset($list_regions['district']) && is_array($list_regions['district']) ? $list_regions['district'] : array();

            if (count($list_regions) > 0)
                $select->where('st.district IN (?)', $list_regions);
            else
                $select->where('1=0', 1);
        }

        // Sale Permission
        if (isset($params['sale_id']) && intval($params['sale_id']) > 0) {
            $select
                ->joinRight(array('ssl' => 'store_staff_log'), 'ssl.store_id=st.id', array());

            $log_where = $this->getAdapter()->quoteInto('ssl.staff_id = ?', $params['sale_id']).
                        " AND " . $this->getAdapter()->quoteInto('ssl.is_leader = ?', 1).
                        " AND " . $this->getAdapter()->quoteInto('DATE(ss.created_at) >= FROM_UNIXTIME(ssl.joined_at, \'%Y-%m-%d\')', 1).
                        " AND (".
                            $this->getAdapter()->quoteInto('DATE(ss.created_at) < FROM_UNIXTIME(ssl.released_at, \'%Y-%m-%d\')', 1).
                            " OR " . $this->getAdapter()->quoteInto('ssl.released_at IS NULL', 1).
                            " OR " . $this->getAdapter()->quoteInto('ssl.released_at = 0', 1).
                        " ) ";

            $select->where($log_where);
        }

        // Leader Permission
        if (isset($params['leader_id']) && intval($params['leader_id']) > 0) {
            $select
                ->joinRight(array('ssl' => 'store_leader_log'), 'ssl.store_id=st.id', array());

            $log_where = $this->getAdapter()->quoteInto('ssl.staff_id = ?', $params['leader_id']).
                        " AND " . $this->getAdapter()->quoteInto('DATE(ss.created_at) >= FROM_UNIXTIME(ssl.joined_at, \'%Y-%m-%d\')', 1).
                        " AND (".
                            $this->getAdapter()->quoteInto('DATE(ss.created_at) < FROM_UNIXTIME(ssl.released_at, \'%Y-%m-%d\')', 1).
                            " OR " . $this->getAdapter()->quoteInto('ssl.released_at IS NULL', 1).
                            " OR " . $this->getAdapter()->quoteInto('ssl.released_at = 0', 1).
                        " ) ";

            $select->where($log_where);
        }

/*
        if (in_array($userStorage->group_id, $permission_group)) {

			$QStoreStaff = new Application_Model_StoreStaff();
			$where[]     = $QStoreStaff->getAdapter()->quoteInto('staff_id = ?', $userStorage->id);
			$result      = $QStoreStaff->fetchAll($where);

			$store_list                                              = array();
			for ($i = 0; $i < count($result); $i++) {$store_list[$i] = $result[$i]['store_id'];}

			$select->where('ss.store_id in (?)', $store_list);
		}
*/
		if ($limit) {
			$select->limitPage($page, $limit);
		}

		// echo $select;
		$result = $db->fetchAll($select);
		$total  = $db->fetchOne("select FOUND_ROWS()");
		return $result;
	}

	function get_cache() {
		$cache  = Zend_Registry::get('cache');
		$result = $cache->load($this->_name.'_cache');

		if ($result === false) {

			$data = $this->fetchAll();

			$result = array();
			if ($data) {
				foreach ($data as $item) {
					$result[] = $item->staff_id;
				}
			}
			$cache->save($result, $this->_name.'_cache', array(), null);
		}

		return $result;
	}

	function getHistory($user_id) {

		$db = Zend_Registry::get('db');

		$select = $db->select()
		             ->from(array('ss' => $this->_name), array(
				's_id'                       => 's.id',
				's_code'                     => 's.code',
				's_name'                     => new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)"),
				'st_id'                      => 'ss.store_id',
				'st_name'                    => 'st.name',
				'success_imei'               => 'ss.success_imei',
				'total_imei'                 => 'ss.total_imei',
				'send_date'                  => 'ss.created_at',
			))
			->join(array('st' => 'store'), 'ss.store_id = st.id', array())
			->join(array('s'  => 'staff'), 'ss.created_by = s.id', array())
			->where('ss.created_by = ?', $user_id)
			->order('ss.created_at DESC')
			->limit(10, 0);

		//echo $select; die;
		$result = $db->fetchAll($select);
		return $result;
	}

	function salesScanPerformance($page, $limit, &$total, $params) {

		$userStorage = Zend_Auth::getInstance()->getStorage()->read();
		$db          = Zend_Registry::get('db');
		$select      = $db->select()->from
		(array('ss2' => $this->_name), array(
				'ss2.created_by',
				'COUNT(DISTINCT ss2.store_id) AS scan_store',
				'SUM(ss2.success_imei) AS sum_s_imei',
				'SUM(ss2.total_imei) AS sum_t_imei'));
		if (isset($params['goods']) && $params['goods'] || isset($params['color_id']) && $params['color_id']) {
			$goods    = '';
			$color_id = '';
			if (isset($params['goods']) && $params['goods']) {
				$goods = ' AND i.good_id in ('.implode(',', $params['goods']).')';
			}

			if (isset($params['color_id']) && $params['color_id']) {
				$color_id = ' AND i.good_color in ('.implode(',', $params['color_id']).')';
			}

			$select->join(array('i' => WAREHOUSE_DB.'.imei'), 'ss2.store_id = i.stock_shop_id '.$goods.$color_id, array());
		}

		if (isset($params['from']) && $params['from']) {
			$from = date_create_from_format("d/m/Y", $params['from'])->format("Y-m-d");
			$select->where('ss2.created_at >= ?', $from.' 00:00:00');
		}
		if (isset($params['to']) && $params['to']) {
			$to = date_create_from_format("d/m/Y", $params['to'])->format("Y-m-d");
			$select->where('ss2.created_at <= ?', $to.' 23:59:59');
		}

		$select->group('ss2.created_by');

		$countStore = $db->select()
		                 ->from(array('st' => 'store'), array(new Zend_Db_Expr('SQL_CALC_FOUND_ROWS s.id'),
				's.id',
				'sale_code' => 's.code',
				'sale_name' => new Zend_Db_Expr("CONCAT(s.firstname,' ',s.lastname)"),
				'group'     => 'g.name',
				'allstore'  => 'COUNT(st.id)',
				'scan_store' => 'AAA.scan_store',
				'created_by' => 'AAA.created_by',
				'sum_s_imei' => 'AAA.sum_s_imei',
				'sum_t_imei' => 'AAA.sum_t_imei',
			));
		$countStore->join(array('ss'      => 'store_staff'), 'st.id = ss.store_id and ss.is_leader = 1', array());
		$countStore->join(array('s'       => 'staff'), 'ss.staff_id = s.id AND s.off_date IS NULL', array());
		$countStore->join(array('g'       => 'group'), 's.group_id = g.id', array());
		$countStore->joinLeft(array('rm'  => 'regional_market'), 'st.regional_market = rm.id', array());
		$countStore->joinLeft(array('a'   => 'area'), 'rm.area_id = a.id', array('area'   => 'a.name'));
		$countStore->joinLeft(array('AAA' => $select), 'AAA.created_by = s.id', array());
		$countStore->where('st.del IS NULL');
		$countStore->where('st.rank <> 3');

		if (isset($params['staff_name']) and $params['staff_name']) {
			$countStore->where('CONCAT(s.firstname, " ",s.lastname) LIKE ?', '%'.$params['staff_name'].'%');
		}

		if (isset($params['name']) and $params['name']) {
			$countStore->where('st.name LIKE ?', '%'.$params['name'].'%');
		}

		if (isset($params['area_id']) and $params['area_id']) {
			if (is_array($params['area_id']) and count($params['area_id'])) {
				$countStore->where('a.id IN (?)', $params['area_id']);
			} elseif (is_numeric($params['area_id'])) {
				$countStore->where('a.id = ?', intval($params['area_id']));
			} else {

				$countStore->where('1=0', 1);
			}
		}

		// ASM Permission
        if (isset($params['asm']) && $params['asm']) {
            $QAsm = new Application_Model_Asm();
            $list_regions = $QAsm->get_cache($params['asm']);
            $list_regions = isset($list_regions['district']) && is_array($list_regions['district']) ? $list_regions['district'] : array();

            if (count($list_regions) > 0)
                $countStore->where('st.district IN (?)', $list_regions);
            else
                $countStore->where('1=0', 1);
        }

        // Sale Permission
        if (isset($params['sale_id']) && intval($params['sale_id']) > 0) {
            $countStore
                ->joinRight(array('ssl' => 'store_staff_log'), 'ssl.store_id=st.id', array());

            $log_where = $this->getAdapter()->quoteInto('ssl.staff_id = ?', $params['sale_id']).
                        " AND " . $this->getAdapter()->quoteInto('ssl.is_leader = ?', 1).
                        " AND " . $this->getAdapter()->quoteInto('? >= FROM_UNIXTIME(ssl.joined_at, \'%Y-%m-%d\')', $to).
                        " AND (".
                            $this->getAdapter()->quoteInto('? < FROM_UNIXTIME(ssl.released_at, \'%Y-%m-%d\')', $from).
                            " OR " . $this->getAdapter()->quoteInto('ssl.released_at IS NULL', 1).
                            " OR " . $this->getAdapter()->quoteInto('ssl.released_at = 0', 1).
                        " ) ";

            $countStore->where($log_where);
        }

        // Leader Permission
        if (isset($params['leader_id']) && intval($params['leader_id']) > 0) {
            $countStore
                ->joinRight(array('ssl' => 'store_leader_log'), 'ssl.store_id=st.id', array());

            $log_where = $this->getAdapter()->quoteInto('ssl.staff_id = ?', $params['leader_id']).
                        " AND " . $this->getAdapter()->quoteInto('? >= FROM_UNIXTIME(ssl.joined_at, \'%Y-%m-%d\')', $to).
                        " AND (".
                            $this->getAdapter()->quoteInto('? < FROM_UNIXTIME(ssl.released_at, \'%Y-%m-%d\')', $from).
                            " OR " . $this->getAdapter()->quoteInto('ssl.released_at IS NULL', 1).
                            " OR " . $this->getAdapter()->quoteInto('ssl.released_at = 0', 1).
                        " ) ";

            $countStore->where($log_where);
        }


		$countStore->group('s.id');
		$countStore->order('AAA.sum_s_imei DESC');

		if (isset($params['export']) and $params['export']) {
			
		}else{
            if ($limit) {
                $countStore->limitPage($page, $limit);
            }
        }
		// if (in_array($userStorage->group_id, $permission_group)) {

		// 	$QStoreStaff = new Application_Model_StoreStaff();
		// 	$where[]     = $QStoreStaff->getAdapter()->quoteInto('staff_id = ?', $userStorage->id);
		// 	$result      = $QStoreStaff->fetchAll($where);

		// 	$store_list                                              = array();
		// 	for ($i = 0; $i < count($result); $i++) {$store_list[$i] = $result[$i]['store_id'];}

		// 	$countStore->where('st.id in (?)', $store_list);
		// }
		//echo $countStore;
		$result = $db->fetchAll($countStore);
		$total  = $db->fetchOne("select FOUND_ROWS()");
		return $result;
	}

	function salesScanReport($params) {

		$userStorage = Zend_Auth::getInstance()->getStorage()->read();
		$db          = Zend_Registry::get('db');
		$selectAAA   = $db->select()->from(array('ssh' => HR_DB.'.stock_shop_log'), array('ssh.created_by', 'ssh.store_id'));
		if (isset($params['from']) && $params['from']) {
			$from = date_create_from_format("d/m/Y", $params['from'])->format("Y-m-d");
			$selectAAA->where('ssh.created_at >= ?', $from.' 00:00:00');
		}
		if (isset($params['to']) && $params['to']) {
			$to = date_create_from_format("d/m/Y", $params['to'])->format("Y-m-d");
			$selectAAA->where('ssh.created_at <= ?', $to.' 23:59:59');
		}

		$selectBBB = $db->select()->from(array('s2' => HR_DB.'.staff'), array('s2.id'));
		$selectBBB->where('s2.group_id = 5');
		$selectBBB->where('s2.off_date IS NULL');

		$selectCCC = $db->select()->from(array('s2' => HR_DB.'.staff'), array('s2.id'));
		$selectCCC->join(array('ssh'                => HR_DB.'.stock_shop_log'), 's2.id = ssh.created_by', array('ssh.store_id'));
		if (isset($params['from']) && $params['from']) {
			$from = date_create_from_format("d/m/Y", $params['from'])->format("Y-m-d");
			$selectCCC->where('ssh.created_at >= ?', $from.' 00:00:00');
		}
		if (isset($params['to']) && $params['to']) {
			$to = date_create_from_format("d/m/Y", $params['to'])->format("Y-m-d");
			$selectCCC->where('ssh.created_at <= ?', $to.' 23:59:59');
		}
		$selectCCC->where('s2.group_id = 5');
		$selectCCC->where('s2.off_date IS NULL');

		$selectDDD = $db->select()->from(array('s2' => HR_DB.'.staff'), array('s2.id'));
		$selectDDD->where('s2.group_id = 9');
		$selectDDD->where('s2.off_date IS NULL');

		$selectEEE = $db->select()->from(array('s2' => HR_DB.'.staff'), array('s2.id'));
		$selectEEE->join(array('ssh'                => HR_DB.'.stock_shop_log'), 's2.id = ssh.created_by', array('ssh.store_id'));
		if (isset($params['from']) && $params['from']) {
			$from = date_create_from_format("d/m/Y", $params['from'])->format("Y-m-d");
			$selectEEE->where('ssh.created_at >= ?', $from.' 00:00:00');
		}
		if (isset($params['to']) && $params['to']) {
			$to = date_create_from_format("d/m/Y", $params['to'])->format("Y-m-d");
			$selectEEE->where('ssh.created_at <= ?', $to.' 23:59:59');
		}
		$selectEEE->where('s2.group_id = 9');
		$selectEEE->where('s2.off_date IS NULL');

		

		$select = $db->select()
		             ->from(array('st' => HR_DB.'.store'), array(
				'store_all'				=> 'COUNT(DISTINCT st.id)',
				'store_org_all'			=> 'COUNT(DISTINCT(CASE WHEN o.store_type_id = 1 THEN st.id END))',
				'store_dealer_all'		=> 'COUNT(DISTINCT(CASE WHEN o.store_type_id IN (2,3) THEN st.id END))',
				'store_scan'			=> 'COUNT(DISTINCT AAA.store_id)',
				'asm_all'				=> 'COUNT(DISTINCT BBB.id)',
				'asm_scan'				=> 'COUNT(DISTINCT CCC.id)',
				'sale_all'				=> 'COUNT(DISTINCT DDD.id)',
				'sale_scan'				=> 'COUNT(DISTINCT EEE.id)'));
		$select->join(array('o'       	=> HR_DB.'.org')			, 'st.org_dealer = o.org_id'		, array());       
		$select->join(array('rm'       	=> HR_DB.'.regional_market'), 'st.regional_market = rm.id'		, array());
		$select->join(array('a'        	=> HR_DB.'.area')			, 'rm.area_id = a.id'				, array());
		$select->join(array('ss'       	=> HR_DB.'.store_staff')	, 'st.id = ss.store_id AND ss.is_leader = 1', array());
		$select->join(array('s'        	=> HR_DB.'.staff')			, 'ss.staff_id = s.id AND s.off_date IS NULL', array());
		$select->joinLeft(array('AAA'  	=> $selectAAA), 's.id = AAA.created_by AND st.id = AAA.store_id', array());
		$select->joinLeft(array('BBB'  	=> $selectBBB), 's.id = BBB.id', array());
		$select->joinLeft(array('CCC'  	=> $selectCCC), 's.id = CCC.id AND st.id = CCC.store_id', array());
		$select->joinLeft(array('DDD'  	=> $selectDDD), 's.id = DDD.id', array());
		$select->joinLeft(array('EEE'  	=> $selectEEE), 's.id = EEE.id AND st.id = EEE.store_id', array());
		$select->where('st.del IS NULL');
		$select->where('st.rank <> 3');

		if (isset($params['staff_name']) && $params['staff_name']) {
			$select->where('CONCAT(s.firstname, " ",s.lastname) LIKE ?', '%'.$params['staff_name'].'%');
		}

		if (isset($params['area_id']) && $params['area_id']) {
			if (is_array($params['area_id']) && count($params['area_id'])) {
				$select->where('a.id IN (?)', $params['area_id']);
			} elseif (is_numeric($params['area_id'])) {
				$select->where('a.id = ?', intval($params['area_id']));
			} else {

				$select->where('1=0', 1);
			}
		}

		// ASM Permission
        if (isset($params['asm']) && $params['asm']) {
            $QAsm = new Application_Model_Asm();
            $list_regions = $QAsm->get_cache($params['asm']);
            $list_regions = isset($list_regions['district']) && is_array($list_regions['district']) ? $list_regions['district'] : array();

            if (count($list_regions) > 0)
                $select->where('st.district IN (?)', $list_regions);
            else
                $select->where('1=0', 1);
        }

        // Sale Permission
        if (isset($params['sale_id']) && intval($params['sale_id']) > 0) {
            $select
                ->joinRight(array('ssl' => 'store_staff_log'), 'ssl.store_id=st.id', array());

            $log_where = $this->getAdapter()->quoteInto('ssl.staff_id = ?', $params['sale_id']).
                        " AND " . $this->getAdapter()->quoteInto('ssl.is_leader = ?', 1).
                        " AND " . $this->getAdapter()->quoteInto('? >= FROM_UNIXTIME(ssl.joined_at, \'%Y-%m-%d\')', $to).
                        " AND (".
                            $this->getAdapter()->quoteInto('? < FROM_UNIXTIME(ssl.released_at, \'%Y-%m-%d\')', $from).
                            " OR " . $this->getAdapter()->quoteInto('ssl.released_at IS NULL', 1).
                            " OR " . $this->getAdapter()->quoteInto('ssl.released_at = 0', 1).
                        " ) ";

            $select->where($log_where);
        }

        // Leader Permission
        if (isset($params['leader_id']) && intval($params['leader_id']) > 0) {
            $select
                ->joinRight(array('ssl' => 'store_leader_log'), 'ssl.store_id=st.id', array());

            $log_where = $this->getAdapter()->quoteInto('ssl.staff_id = ?', $params['leader_id']).
                        " AND " . $this->getAdapter()->quoteInto('? >= FROM_UNIXTIME(ssl.joined_at, \'%Y-%m-%d\')', $to).
                        " AND (".
                            $this->getAdapter()->quoteInto('? < FROM_UNIXTIME(ssl.released_at, \'%Y-%m-%d\')', $from).
                            " OR " . $this->getAdapter()->quoteInto('ssl.released_at IS NULL', 1).
                            " OR " . $this->getAdapter()->quoteInto('ssl.released_at = 0', 1).
                        " ) ";

            $select->where($log_where);
        }

		//echo $select; die;
		$result = $db->fetchRow($select);
		$total  = $db->fetchOne("select FOUND_ROWS()");
		return $result;
	}

    function salesScanImei($params) {

        $db = Zend_Registry::get('db');
        $select = $db->select()->from(array('st' => HR_DB.'.store'),
                    array(
                        'sum_success' => 'SUM(ssh.success_imei)',
                        'sum_total'   => 'SUM(ssh.total_imei)'));
        $select->join(array('rm'       => HR_DB.'.regional_market'), 'st.regional_market = rm.id', array());
        $select->join(array('a'        => HR_DB.'.area'), 'rm.area_id = a.id', array());
        $select->join(array('ss'       => HR_DB.'.store_staff'), 'st.id = ss.store_id and ss.is_leader = 1', array());
        $select->join(array('s'        => HR_DB.'.staff'), 'ss.staff_id = s.id ', array());  
        $select->join(array('ssh'        => HR_DB.'.stock_shop_log'), 's.id = ssh.created_by AND st.id = ssh.store_id', array()); 
        $select->where('st.del IS NULL');
		$select->where('st.rank <> 3'); 
            
		if (isset($params['staff_name']) && $params['staff_name']) {
			$select->where('CONCAT(s.firstname, " ",s.lastname) LIKE ?', '%'.$params['staff_name'].'%');
		}
        if (isset($params['from']) && $params['from']) {
            $from = date_create_from_format("d/m/Y", $params['from'])->format("Y-m-d");
            $select->where('ssh.created_at >= ?', $from.' 00:00:00');
        }
        if (isset($params['to']) && $params['to']) {
            $to = date_create_from_format("d/m/Y", $params['to'])->format("Y-m-d");
            $select->where('ssh.created_at <= ?', $to.' 23:59:59');
        }
        if (isset($params['area_id']) && $params['area_id']) {
            if (is_array($params['area_id']) && count($params['area_id'])) {
                $select->where('a.id IN (?)', $params['area_id']);
            } elseif (is_numeric($params['area_id'])) {
                $select->where('a.id = ?', intval($params['area_id']));
            } else {

                $select->where('1=0', 1);
            }
        }

        // ASM Permission
        if (isset($params['asm']) && $params['asm']) {
            $QAsm = new Application_Model_Asm();
            $list_regions = $QAsm->get_cache($params['asm']);
            $list_regions = isset($list_regions['district']) && is_array($list_regions['district']) ? $list_regions['district'] : array();

            if (count($list_regions) > 0)
                $select->where('st.district IN (?)', $list_regions);
            else
                $select->where('1=0', 1);
        }

        // Sale Permission
        if (isset($params['sale_id']) && intval($params['sale_id']) > 0) {
            $select
                ->joinRight(array('ssl' => 'store_staff_log'), 'ssl.store_id=st.id', array());

            $log_where = $this->getAdapter()->quoteInto('ssl.staff_id = ?', $params['sale_id']).
                        " AND " . $this->getAdapter()->quoteInto('ssl.is_leader = ?', 1).
                        " AND " . $this->getAdapter()->quoteInto('DATE(ssh.created_at) >= FROM_UNIXTIME(ssl.joined_at, \'%Y-%m-%d\')', 1).
                        " AND (".
                            $this->getAdapter()->quoteInto('DATE(ssh.created_at) < FROM_UNIXTIME(ssl.released_at, \'%Y-%m-%d\')', 1).
                            " OR " . $this->getAdapter()->quoteInto('ssl.released_at IS NULL', 1).
                            " OR " . $this->getAdapter()->quoteInto('ssl.released_at = 0', 1).
                        " ) ";

            $select->where($log_where);
        }

        // Leader Permission
        if (isset($params['leader_id']) && intval($params['leader_id']) > 0) {
            $select
                ->joinRight(array('ssl' => 'store_leader_log'), 'ssl.store_id=st.id', array());

            $log_where = $this->getAdapter()->quoteInto('ssl.staff_id = ?', $params['leader_id']).
                        " AND " . $this->getAdapter()->quoteInto('DATE(ssh.created_at) >= FROM_UNIXTIME(ssl.joined_at, \'%Y-%m-%d\')', 1).
                        " AND (".
                            $this->getAdapter()->quoteInto('DATE(ssh.created_at) < FROM_UNIXTIME(ssl.released_at, \'%Y-%m-%d\')', 1).
                            " OR " . $this->getAdapter()->quoteInto('ssl.released_at IS NULL', 1).
                            " OR " . $this->getAdapter()->quoteInto('ssl.released_at = 0', 1).
                        " ) ";

            $select->where($log_where);
        }
        

        $result = $db->fetchRow($select);
        return $result;
    }


    function getShopInventory($user_id) {

		$db = Zend_Registry::get('db');

		$select = $db->select()->from(array('st' => 'store'), array(
				'st_id'		=> 'st.id',
				'st_name'	=> 'st.name',
				'st_stock'	=> new Zend_Db_Expr("COUNT(CASE WHEN ts.imei IS NULL THEN i.imei_sn END)"),
			))
			->join(array('ss' => 'store_staff')			, 'st.id = ss.store_id AND ss.is_leader = 1'	, array())
			->join(array('s'  => 'staff')				, 'ss.staff_id = s.id'							, array())
			->join(array('i'  => WAREHOUSE_DB.'.imei')	, 'st.id = i.stock_shop_id'						, array())
			->joinLeft(array('ts'  => 'timing_sale')	, 'i.imei_sn = ts.imei'							, array())
			->where("i.stock_shop_date >= DATE_FORMAT(DATE_ADD(DATE_ADD(LAST_DAY(NOW()),INTERVAL 1 DAY),INTERVAL -1 MONTH),'%Y-%m-%d 00:00:00')")
			->where("i.stock_shop_date <= DATE_FORMAT(LAST_DAY(NOW()), '%Y-%m-%d 23:59:59')")
			->where("s.id = ?", $user_id)
			->group('st.id')
			->order('st_stock DESC');

		//echo $select; die;
		$result = $db->fetchAll($select);
		return $result;
	}

	function getShopInventorylow($user_id) {

		$db = Zend_Registry::get('db');

		$select = $db->select()->from(array('st' => 'store'), array(
				'st_id'		=> 'st.id',
				'st_name'	=> 'st.name',
				'st_scan'	=> new Zend_Db_Expr("COUNT(i.imei_sn)"),
				'st_stock'	=> new Zend_Db_Expr("COUNT(CASE WHEN ts.imei IS NULL THEN i.imei_sn END)"),
				'st_sellout'=> new Zend_Db_Expr("COUNT(CASE WHEN ts.imei IS NOT NULL THEN i.imei_sn END)")
			))
			->join(array('ss' => 'store_staff')			, 'st.id = ss.store_id AND ss.is_leader = 1'	, array())
			->join(array('s'  => 'staff')				, 'ss.staff_id = s.id'							, array())
			->join(array('i'  => WAREHOUSE_DB.'.imei')	, 'st.id = i.stock_shop_id'						, array())
			->joinLeft(array('ts'  => 'timing_sale')	, 'i.imei_sn = ts.imei'							, array())
			->where("i.stock_shop_date >= DATE_FORMAT(DATE_ADD(DATE_ADD(LAST_DAY(NOW()),INTERVAL 1 DAY),INTERVAL -1 MONTH),'%Y-%m-%d 00:00:00')")
			->where("i.stock_shop_date <= DATE_FORMAT(LAST_DAY(NOW()), '%Y-%m-%d 23:59:59')")
			->where("s.id = ?", $user_id)
			->group('st.id')
			->having('st_stock <= 10')
			->order('st_stock ASC');

		//echo $select; die;
		$result = $db->fetchAll($select);
		return $result;
	}

}