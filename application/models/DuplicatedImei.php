<?php
class Application_Model_DuplicatedImei extends Zend_Db_Table_Abstract
{
	protected $_name = 'duplicated_imei';

    function fetchPagination($page, $limit, &$total, $params){
        $db = Zend_Registry::get('db');

        $select = $db->select()
            ->from(array('p' => $this->_name),
                array(new Zend_Db_Expr('SQL_CALC_FOUND_ROWS p.id'), 'p.*'));

        // xử lý ngày
        if (isset($params['from_date']) && $params['from_date'] && isset($params['to_date']) && $params['to_date']) {
			$from = explode('/', $params['from_date']);
			$from = $from[2].'-'.$from[1].'-'.$from[0] . ' 00:00:00';
			$to = explode('/', $params['to_date']);
			$to = $to[2].'-'.$to[1].'-'.$to[0] . ' 23:59:59';		
		} elseif (isset($params['from_date']) && $params['from_date']) {
			$from = explode('/', $params['from_date']);
			$from = $from[2].'-'.$from[1].'-'.$from[0] . ' 00:00:00';
			$to = date('Y-m-d 23:59:59');
		} elseif (isset($params['to_date']) && $params['to_date']) {
			$to = explode('/', $params['to_date']);
			$to = $to[2].'-'.$to[1].'-'.$to[0] . ' 23:59:59';
			$from = date_sub(date_create(), new DateInterval('P30D'))->format('Y-m-d 00:00:00');
		} else {
			$from = date_sub(date_create(), new DateInterval('P30D'))->format('Y-m-d 00:00:00');
			$to = date('Y-m-d 23:59:59');
		}

		// Tìm theo ngày chấm công
        $select->where('p.date >= ?', $from);
        $select->where('p.date <= ?', $to);
        
        // tìm theo IMEI
        if (isset($params['imei']) and $params['imei'])
            $select->where('p.imei = ?', $params['imei']);

        // For Second Timing ///////////////////////////////////////////////////////////////////////
        $select->join(array('s'=>'staff'), 's.id=p.staff_id', array('s.firstname', 's.lastname', 's.email', 's.phone_number'))
        	->join(array('st'=>'store'), 'st.id=p.store_id', array('st.name'));

        if (isset($params['staff_id']) and $params['staff_id'])
            $select->where('p.staff_id = ?', $params['staff_id']);

        if (isset($params['name']) and $params['name']) 
            $select->where('CONCAT(s.firstname, " ",s.lastname) LIKE ?', '%'.$params['name'].'%');

        if (isset($params['email']) and $params['email']) {
        	$params['email'] = preg_replace('/'.EMAIL_SUFFIX.'/', '', $params['email']);
            $select->where('s.email LIKE ?', $params['email'].EMAIL_SUFFIX);
        }

        if (isset($params['phone_number']) and $params['phone_number']) {
            $select->where('s.phone_number = ?', $params['phone_number']);
        }

        //
    	$userStorage = Zend_Auth::getInstance()->getStorage()->read();
    	$uid = $userStorage->id;
    	$group_id = $userStorage->group_id;

        // Nếu sếp vào xem thì 
        if ( $group_id == BOARD_ID || $group_id == SALES_EXT_ID || $uid == SUPERADMIN_ID ) {

        	// chỉ lấy các báo cáo mà ASM đã duyệt
            $select->where('p.asm_check = 1 AND p.asm_at <> 0 AND p.asm_at IS NOT NULL');
        	// $select->where('p.asm_check = 0');

        	// Tìm các báo cáo đã/chưa xử lý dành cho sếp
        	if ( isset($params['solved']) && $params['solved'] == 1 ) {
	        	$select->where('p.solved = ?', 1);
	        } elseif ( isset($params['solved']) && $params['solved'] == 0 ) {
	        	$select->where('p.solved = ?', 0);
	        }

    	// Nếu ASM thì 
        } elseif (  in_array( $group_id, array(ASM_ID, ASMSTANDBY_ID, SALES_ADMIN_ID) ) ) {
            $QModel = new Application_Model_Asm();
            $list_regions = $QModel->get_cache($uid);

            if ($list_regions && isset($list_regions['district']))
                $list_regions = $list_regions['district'];

            // lấy các báo cáo ASM chưa duyệt
            $select->where('p.solved = 0 AND p.asm_check = 0 AND (p.asm_at = 0 OR p.asm_at IS NULL)');

            // Tìm các báo cáo đã/chưa xử lý dành cho ASM
            if ( isset($params['solved']) && $params['solved'] == 1 ) {
                $select->where('p.asm_check = ?', 1);
            } elseif ( isset($params['solved']) && $params['solved'] == 0 ) {
                $select->where('p.asm_check = ?', 0);
            }
        } else {

        	// Tìm các báo cáo đã/chưa xử lý dành cho SUPERADMIN
        	if ( isset($params['solved']) && $params['solved'] == 1 ) {
	        	$select->where('p.solved = ?', 1);
	        } elseif ( isset($params['solved']) && $params['solved'] == 0 ) {
	        	$select->where('p.solved = ?', 0);
	        }
        }

        $QRegionalMarket = new Application_Model_RegionalMarket();

        if ( in_array( $group_id, array(ASM_ID, ASMSTANDBY_ID, SALES_ADMIN_ID) ) ) {
            if (is_array($list_regions) && count($list_regions) > 0)
                $select->where('st.district IN (?)', $list_regions);
            else
                $select->where('1=0');
            
        } elseif (in_array( $group_id, array(ADMINISTRATOR_ID, BOARD_ID, SALES_EXT_ID) )) {
        }
        
        if (isset($params['area_id']) && $params['area_id']) {
            $list_district = $QRegionalMarket->get_district_by_area_cache($params['area_id']);

            if (is_array($list_district) && count($list_district) > 0)
                $select->where('st.district IN (?)', $list_district);
            else
                $select->where('1=0');
        }

        // tìm theo tỉnh
        if (isset($params['regional_market']) and intval($params['regional_market']) > 0)
            $select->where('st.regional_market = ?', $params['regional_market']);

        // tìm theo store
        if (isset($params['store']) and $params['store'])
            $select->where('p.store_id = ?', $params['store']);
        // END Second Timing ///////////////////////////////////////////////////////////////////////

        // For First Timing ///////////////////////////////////////////////////////////////////////
        $select->join(array('ss'=>'staff'), 'ss.id=p.staff_id_first', array(
			'firstname_first'    => 'ss.firstname',
			'lastname_first'     => 'ss.lastname',
			'email_first'        => 'ss.email',
			'phone_number_first' => 'ss.phone_number',
    	));

        // tìm tên
        if (isset($params['name_first']) and $params['name_first']) 
            $select->where('CONCAT(ss.firstname, " ",ss.lastname) LIKE ?', '%'.$params['name_first'].'%');

        // tìm email
        if (isset($params['email_first']) and $params['email_first']) {
        	$params['email_first'] = preg_replace('/'.EMAIL_SUFFIX.'/', '', $params['email_first']);
            $select->where('ss.email LIKE ?', $params['email_first'].EMAIL_SUFFIX);
        }

        // tìm số dt
        if (isset($params['phone_number_first']) and $params['phone_number_first']) {
            $select->where('ss.phone_number = ?', $params['phone_number_first']);
        }

        // tìm theo area
        if (isset($params['area_id_first']) and $params['area_id_first']){
            $QRegionalMarket = new Application_Model_RegionalMarket();
            
            if (is_array($params['area_id_first'])) {
                $where = $QRegionalMarket->getAdapter()->quoteInto('area_id IN (?)', $params['area_id_first']);
            } else {
                $where = $QRegionalMarket->getAdapter()->quoteInto('area_id = ?', $params['area_id_first']);
            }

            $regional_markets = $QRegionalMarket->fetchAll($where);
            $tem = array();
            foreach ($regional_markets as $regional_market)
                $tem[] = $regional_market->id;

            $select->where('ss.regional_market IN (?)', $tem);
        }

        // tìm theo tỉnh
        if (isset($params['regional_market_first']) and intval($params['regional_market_first']) > 0)
            $select->where('ss.regional_market = ?', $params['regional_market_first']);

        // tìm theo store
        if (isset($params['store_first']) and $params['store_first']) {
        	$QTiming = new Application_Model_Timing();
        	$where = $QTiming->getAdapter()->quoteInto('store = ?', $params['store_first']);
        	$timings = $QTiming->fetchAll($where);

        	$timings_arr = array();
        	foreach ($timings as $t_id => $timing) {
        		$timings_arr[] = $timing['id'];
        	}

        	$QTimingSales = new Application_Model_TimingSale();
        	$where = $QTimingSales->getAdapter()->quoteInto('timing_id IN (?)', $timings_arr);
        	$timing_sales = $QTimingSales->fetchAll($where);

        	$timing_sales_arr = array();
        	foreach ($timing_sales as $ts_id => $timing_sale) {
        		$timing_sales_arr[] = $timing_sale['id'];
        	}

        	$select->where('p.timing_sales_first IN (?)', $timing_sales_arr);
        }
        // END First Timing ///////////////////////////////////////////////////////////////////////

        $select->order(array('p.id DESC'));

        if( ( ( isset($params['export']) && $params['export'] != 1 ) || !isset($params['export']) ) && $limit)
        	$select->limitPage($page, $limit);

        $result = $db->fetchAll($select);
        $total = $db->fetchOne("select FOUND_ROWS()");
        return $result;
    }

    function get_timing_sales_info_cache($id = 0)
    {
		$cache  = Zend_Registry::get('cache');
		$result = $cache->load('timing_sales_info_cache');

		if (!$result || empty($result[$id])) {

	    	$sql = "SELECT
						st.`name`,
						t.`from`,
						s.firstname,
						s.lastname,
						s.email,
						s.phone_number,
						ts.photo,
						t.created_at,
						t.approved_at,
						ss.firstname AS a_firstname,
						ss.lastname AS a_lastname,
						c.`name` AS customer_name,
						c.address AS customer_address,
						c.phone_number AS customer_phone
					FROM
						timing_sale ts
					INNER JOIN timing t ON ts.timing_id = t.id
					AND ts.id = ?
					LEFT JOIN staff s ON s.id = t.staff_id
					LEFT JOIN staff ss ON t.approved_by = ss.id
					LEFT JOIN store st ON t.store = st.id
					LEFT JOIN customer c ON ts.customer_id = c.id";

			$db = Zend_Registry::get('db');
			$res = $db->query($sql, array($id));

			if (!$res) {
				return false;
			} else {
				$res = $res->fetch();

				if (!$res) {
					$sql = "SELECT
								st.`name`,
								t.`from`,
								s.firstname,
								s.lastname,
								s.email,
								s.phone_number,
								ts.photo,
								t.created_at,
								t.approved_at,
								ss.firstname AS a_firstname,
								ss.lastname AS a_lastname,
								c.`name` AS customer_name,
								c.address AS customer_address,
								c.phone_number AS customer_phone
							FROM
								timing_sale_trash ts
							INNER JOIN timing t ON ts.timing_id = t.id
							AND ts.id = ?
							LEFT JOIN staff s ON s.id = t.staff_id
							LEFT JOIN staff ss ON t.approved_by = ss.id
							LEFT JOIN store st ON t.store = st.id
							LEFT JOIN customer c ON ts.customer_id = c.id";

					$res = $db->query($sql, array($id));

					$res = $res->fetch();
				}
			}

			if (!$result) {
				$result = array();
			}
			
			$result[$id] = array(
				'name'              => $res['name'],
				'from'              => $res['from'],
				'firstname'         => $res['firstname'],
				'lastname'          => $res['lastname'],
				'email'             => $res['email'],
				'phone_number'      => $res['phone_number'],
				'photo'             => $res['photo'],
				'customer_name'     => $res['customer_name'],
				'customer_phone'    => $res['customer_phone'],
				'customer_address'  => $res['customer_address'],
				'created_at'        => $res['created_at'],
				'approved_at'       => $res['approved_at'],
				'approve_firstname' => $res['a_firstname'],
				'approve_lastname'  => $res['a_lastname'],
			);
			
			$cache->save($result, 'timing_sales_info_cache', array(), null);
		}

		return $result[$id];
    }

    function get_imei_model_cache($imei = 0)
    {
    	$cache  = Zend_Registry::get('cache');
		$result = $cache->load('imei_model_cache');

    	

		if (!$result || empty($result[$imei]) || empty($result[$imei]['activated_at'])) {

	    	$sql = "SELECT
						i.imei_sn,
						p.`desc` AS product_name,
						c.`name` AS color_name
					FROM
						".WAREHOUSE_DB.'.'."`imei` i
					INNER JOIN ".WAREHOUSE_DB.'.'."good p ON i.good_id = p.id
					INNER JOIN ".WAREHOUSE_DB.'.'."good_color c ON i.good_color = c.id
					WHERE
						i.imei_sn = ?";

			$db = Zend_Registry::get('db');
			$res = $db->query($sql, array($imei));

			if (!$res) {
				return false;
			} else {
				$res = $res->fetch();
			}

			if (!$result) {
				$result = array();
			}
			
			$result[$imei] = array(
				'product_name' => $res['product_name'],
				'color_name'   => $res['color_name'],
			);


	    	$sql = "SELECT i.activated_at FROM ".WAREHOUSE_DB.'.'."imei_activation i
					WHERE i.imei_sn = ?";

			$res = $db->query($sql, array($imei));
			$res = $res->fetch();

			if ($res) {
				$result[$imei]['activated_at'] = $res['activated_at'];
			}
			
			$cache->save($result, 'imei_model_cache', array(), null);
		}

		return $result[$imei];
    }

    function get_model($imei = 0)
    {
    	$sql = "
            SELECT p.id AS product_id, c.id AS color_id
            FROM ".WAREHOUSE_DB.'.'."imei i
                INNER JOIN ".WAREHOUSE_DB.'.'."good_color c
                    ON i.good_color=c.id
                INNER JOIN ".WAREHOUSE_DB.'.'."good p
                    ON p.id = i.good_id
			WHERE i.imei_sn=?
			";

		$db = Zend_Registry::get('db');
		$model = $db->query($sql, array($imei));
		$model = $model->fetch();

		if ($model) {
			return $model;
		} else {
			return null;
		}
    }

    // function get_cache(){
    //     $cache      = Zend_Registry::get('cache');
    //     $result     = $cache->load($this->_name.'_cache');

    //     if ($result === false) {

    //         $data = $this->fetchAll();

    //         $result = array();
    //         if ($data){
    //             foreach ($data as $item){
    //                 $result[$item->id] = $item->name;
    //             }
    //         }
    //         $cache->save($result, $this->_name.'_cache', array(), null);
    //     }
    //     return $result;
    // }
}                                                      
