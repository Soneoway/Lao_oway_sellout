<?php
/**
* Tổng hợp các function để export dữ liệu cho HR
*/
class Export_Hr
{
	
	function __construct()
	{
		
	}

	/**
     * Thống kê số máy, tiền kpi cho sales, xuất ra file CSV
     */
    public static function sales_commission($params = array())
    {
    	////////////////////////////////////////////////////
    	/////////////////// KHỞI TẠO ĐỂ XUẤT CSV
    	////////////////////////////////////////////////////
    	set_time_limit(0);
        error_reporting(0);
        ini_set('display_error', 0);
        $filename = 'Sales KPI - '.date('d-m-Y H-i-s');
        // output headers so that the file is downloaded rather than displayed
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename='.$filename.'.csv');
        // echo "\xEF\xBB\xBF"; // UTF-8 BOM
        echo chr(239) . chr(187) . chr(191); // UTF-8 BOM
        $output = fopen('php://output', 'w');

        ////////////////////////////////////////////////////
    	/////////////////// TỔNG HỢP DỮ LIỆU
    	////////////////////////////////////////////////////
    	$from = $params['from'];
    	$to = $params['to'];

        $QArea = new Application_Model_Area();
    	$areas = $QArea->get_cache();

		$QGood        = new Application_Model_Good();
		$QGoodKpi     = new Application_Model_GoodKpi();
		$products_res = $QGood->fetchAll('cat_id = '. PHONE_CAT_ID);
		$products     = array();

		foreach ($products_res as $key => $value) {
            $kpi = $QGoodKpi->fetchRow('good_id = '.$value['id']);

			$products[ $value['id'] ] = array(
				'name'  => $value['name'],
				'price' => $value['price_1'],
				'kpi'   => ($kpi['sales_kpi'] ? $kpi['sales_kpi'] : 0),
				);

		}

		$QRegionalMarket = new Application_Model_RegionalMarket();
		$rms = $QRegionalMarket->fetchAll();
		$regional_markets = array();

		foreach ($rms as $key => $value) {
			$regional_markets[$value['id']] = $value;
		}

        $heads = array(
            'STT',
            'Mã NV',
            'Khu vực',
            'Tỉnh',
            'Tên NV',
            'Thử việc',
        );

        // biến này dùng để sắp xếp doanh số từng máy đúng vị trí so với tên máy trên tiêu đề
		$product_tmp = array();

		foreach ($products as $key => $value) {
        	$heads[] = $value['name'];
        	$product_tmp[] = $key;
        }

        $heads[] = 'Tổng';
        $heads[] = 'Tổng KPI';

        fputcsv($output, $heads);

        // mảng tạm chứa các id của sales
    	$s_arr = array();

    	$db = Zend_Registry::get('db');

    	// Lấy danh sách sales_id trong các timing (distinct)
		$sql = "SELECT DISTINCT
				`t`.`sales_id`
			FROM
				`timing` AS `t`
			WHERE
				(t.approved_at <> 0)
			AND(t.approved_at <> '')
			AND(t.approved_at IS NOT NULL)
			AND(t.`from` >= ?)
			AND(t.`from` <= ?)
			AND(t.sales_id IS NOT NULL)
			";
    	$sales_list = $db->query($sql, array($from, $to));

    	foreach ($sales_list as $key => $s) {
    		$s_arr[] = $s['sales_id'];
    	}

    	// Lấy danh sách sales hiện tại trong list staff
		$select = $db->select()
    		->from(array('t'=>'staff'), array('t.*'))
    		->where('t.group_id = ?', SALES_ID);

    	$sales_list = $db->fetchAll($select);
    	foreach ($sales_list as $key => $s) {
    		$s_arr[] = $s['id'];
    	}

    	// tổng hợp 2 list lại, bỏ id trùng
    	$sales_list = array_unique($s_arr);

    	// lấy info các staff trong $sales_list
		$select = $db->select()
			->from(array('s'=>'staff'), array('s.id', 's.firstname', 's.lastname', 's.code', 's.regional_market', 's.joined_at'))
			->where('s.id IN (?)', $sales_list);

		// biến này chứa kết quả query
		$staff_info_res = $db->fetchAll($select);

		// biến này là mảng tổng hợp lại từ kết quả query
		$staff_info = array();

		foreach ($staff_info_res as $sk => $sv) {
			$staff_info[ $sv['id'] ] = array(
				'name'            => $sv['firstname'] . ' ' . $sv['lastname'],
				'code'            => $sv['code'],
				'joined_at'       => $sv['joined_at'],
				'regional_market' => isset( $regional_markets[ $sv['regional_market'] ] ) ? $regional_markets[ $sv['regional_market'] ]['name'] : '#',
				'area'            => isset( $areas[ $regional_markets[ $sv['regional_market'] ]['area_id'] ] ) ? $areas[ $regional_markets[ $sv['regional_market'] ]['area_id'] ] : '#',
				);
		}

		// thống kê doanh số theo từng máy/từng sales man
    	$sql = "SELECT
					t.sales_id,
					COUNT(ts.product_id)AS `number`,
					ts.product_id
				FROM
					timing_sale ts
				INNER JOIN timing t ON t.id = ts.timing_id
				AND t.`from` >= ?
				AND t.`from` <= ?
				AND t.approved_at <> 0
				AND t.approved_at <> ''
				AND t.approved_at IS NOT NULL
				GROUP BY
					t.sales_id,
					ts.product_id
				";

		// kết quả query
		$sales_by_product_res = $db->query($sql, array($from, $to));

		// tổng hợp lại theo từng sales
		$sales_by_product = array();

		foreach ($sales_by_product_res as $key => $row) {
			if ( ! isset( $sales_by_product[ $row['sales_id'] ] ) ) {
				$sales_by_product[ $row['sales_id'] ] = array();
			}

			// mỗi dòng của mảng này là 1 sales man // mỗi dòng của sales man ứng với 1 product_id và số lượng tương ứng
			$sales_by_product[ $row['sales_id'] ][ $row['product_id'] ] = $row['number'];
		}

		$i = 1;

		////////////////////////////////////////////////////
    	/////////////////// XUẤT RA CSV
    	////////////////////////////////////////////////////
		foreach ($staff_info as $staff_id => $staff) {
			$row = array();

			$row[] = $i++;
			$row[] = $staff['code'];
			$row[] = $staff['area'];
			$row[] = $staff['regional_market'];
			$row[] = $staff['name'];

			// kiểm tra xem thử việc hay chính thức (1 tháng=30 ngày)
			$row[] = My_Custom_Date::is_between($from, $to, $staff['joined_at'], 60) ? 'X' : '-';

			$sum = 0; // tổng tiền kpi
			$num = 0; // tổng số máy

			if ( isset( $sales_by_product[ $staff_id ] ) ) {
				$d = count($row);

				foreach ($sales_by_product[ $staff_id ] as $product_id => $number) {
					foreach ($product_tmp as $pk => $pid) {
						if ($product_id == $pid) {
							$row[ $pk + $d ] = $number;
							$num += $number;
							$sum += $number * $products[$pid]['kpi'];
							break;
						}
					}
				}

				for ($j = 0; $j < $d + count($product_tmp); $j++) { 
					if ( ! isset( $row[ $j ] ) ) {
						$row[ $j ] = '0';
					}
				}

				ksort($row);
			}

			$row[] = $num;
			$row[] = $sum;

			fputcsv($output, $row);
		}

    	exit;
    }

    /**
     * Thống kê số máy, tiền kpi cho pg, xuất ra file CSV
     */
    public static function pg_commission($params = array())
    {
    	////////////////////////////////////////////////////
    	/////////////////// KHỞI TẠO ĐỂ XUẤT CSV
    	////////////////////////////////////////////////////
    	set_time_limit(0);
        error_reporting(0);
        ini_set('display_error', 0);
        $filename = 'PG KPI - '.date('d-m-Y H-i-s');
        // output headers so that the file is downloaded rather than displayed
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename='.$filename.'.csv');
        echo "\xEF\xBB\xBF"; // UTF-8 BOM
        $output = fopen('php://output', 'w');

        ////////////////////////////////////////////////////
    	/////////////////// TỔNG HỢP DỮ LIỆU
    	////////////////////////////////////////////////////
    	$from = $params['from'];
    	$to = $params['to'];

    	$heads = array(
            'STT',
            'Mã NV',
            'Khu vực',
            'Tỉnh',
            'Nhân viên',
            'Thử việc',
        );

        $QGood        = new Application_Model_Good();
        $QGoodKpi     = new Application_Model_GoodKpi();
        $products_res = $QGood->fetchAll('cat_id = '. PHONE_CAT_ID);
        $products     = array();

        foreach ($products_res as $key => $value) {
            $kpi = $QGoodKpi->fetchRow('good_id = '.$value['id']);

            $products[ $value['id'] ] = array(
                'name'  => $value['name'],
                'price' => $value['price_1'],
                'kpi'   => ($kpi['pg_kpi'] ? $kpi['pg_kpi'] : 0),
            );

            $heads[] = $value['name'];

            $product_tmp[] = $value['id'];
        }

        $heads[] = 'Tổng';
        $heads[] = 'Tổng KPI';

        fputcsv($output, $heads);

        $QArea = new Application_Model_Area();
    	$areas = $QArea->get_cache();

    	$QRegionalMarket = new Application_Model_RegionalMarket();
		$rms = $QRegionalMarket->fetchAll();
		$regional_markets = array();

		foreach ($rms as $key => $value) {
			$regional_markets[$value['id']] = $value;
		}

    	$db = Zend_Registry::get('db');

    	// lấy hết các pg, kể cả từng làm pg trong tháng
		$sql = "SELECT DISTINCT `s`.`id`, `s`.`lastname`, `s`.`firstname`, `s`.`code`, `s`.`regional_market`, `s`.`joined_at`, rm.area_id
    			FROM `staff` AS `s`
				  INNER JOIN `regional_market` AS `rm`
					ON s.regional_market=rm.id
				  INNER JOIN `timing` AS `t`
					ON t.staff_id=s.id
				WHERE s.group_id = ? OR
						s.id IN (
							SELECT DISTINCT sl.staff_id FROM sales_team_staff_log sl
							WHERE sl.is_leader = 0
								AND (
									sl.joined_at <= ?
									AND (
										sl.released_at IS NULL 
											OR sl.released_at >= ?
										)
								)
								AND sl.staff_id <> 0
						)
    	";

    	// tất cả pg
    	$staffs_all = $db->query($sql, array( PGPB_ID, $to, $from ));

    	$staff_areas = array(); // chia pg theo khu vực
    	$all_staff_id = array(); // tất cả id của pg, dùng cho câu sql ở dưới

    	foreach($staffs_all as $s_k => $s_v) {
    		if ( ! isset( $staff_areas[ $s_v['area_id'] ] ) ) {
    			$staff_areas[ $s_v['area_id'] ] = array();
    		}

    		if ( isset( $regional_markets[ $s_v['regional_market'] ]['area_id'] ) ) {
    			
	    		$staff_areas[ $regional_markets[ $s_v['regional_market'] ]['area_id'] ][ $s_v['id'] ] = array(
						'code'            => $s_v['code'],
						'joined_at'       => $s_v['joined_at'],
						'area'            => isset( $areas[ $regional_markets[ $s_v['regional_market'] ]['area_id'] ] ) ? $areas[ $regional_markets[ $s_v['regional_market'] ]['area_id'] ] : '#',
						'regional_market' => isset( $regional_markets[ $s_v['regional_market'] ] ) ? $regional_markets[ $s_v['regional_market'] ]['name'] : '#',
						'name'            => $s_v['firstname'] . ' ' . $s_v['lastname'],
					);
    		}

    		$all_staff_id[] = $s_v['id'];
    	}

    	$all_staff_id_str = '';
    	foreach ($all_staff_id as $k => $id) {
    		$all_staff_id_str .= $id .',';
    	}

    	$all_staff_id_str = trim($all_staff_id_str, ',');

        $sql = "SELECT
					t.staff_id,
					COUNT(ts.product_id)AS `number`,
					ts.product_id
				FROM
					timing_sale ts
				INNER JOIN timing t ON ts.timing_id = t.id
					AND t.`from` >= ?
					AND t.`from` <= ?
					AND t.approved_at <> 0
					AND t.approved_at <> ''
					AND t.approved_at IS NOT NULL 
					AND t.staff_id IN ($all_staff_id_str)
				GROUP BY
					t.staff_id, ts.product_id
			";

		$sales = $db->query($sql, array($from, $to));
		$sales_list = array();

		foreach ($sales as $key => $value) {
			if ( ! isset( $sales_list[ $value['staff_id'] ] ) ) {
				$sales_list[ $value['staff_id'] ] = array();
			}

			$sales_list[ $value['staff_id'] ][ $value['product_id'] ] = $value['number'];
		}

		$i = 1;


		////////////////////////////////////////////////////
    	/////////////////// XUẤT RA CSV
    	////////////////////////////////////////////////////
		foreach ($staff_areas as $area_id => $staff_area) {

			foreach ($staff_area as $staff_id => $staff) {
				$row = array();

				$row[] = $i++;
				$row[] = $staff['code'];
				$row[] = $staff['area'];
				$row[] = $staff['regional_market'];
				$row[] = $staff['name'];

				// kiểm tra xem thử việc hay chính thức (1 tháng=30 ngày)
				$row[] = My_Custom_Date::is_between($from, $to, $staff['joined_at'], 30) ? 'X' : '-';
				$sum = 0; // tổng tiền kpi
				$num = 0; // tổng số máy

				if ( isset( $sales_list[ $staff_id ] ) ) {
					$d = count($row);

					foreach ($sales_list[ $staff_id ] as $product_id => $number) {
						
						foreach ($product_tmp as $pk => $pid) {
							if ($product_id == $pid) {
								$row[ $pk + $d ] = $number;
								$num += $number;
								$sum += $number * $products[ $pid ]['kpi'];
								break;
							}
						}
					}

					for ($j = 0; $j < $d + count($product_tmp); $j++) { 
						if ( ! isset( $row[ $j ] ) ) {
							$row[ $j ] = '0';
						}
					}

					ksort($row);
				}

				$row[] = $num;
				$row[] = $sum;

				fputcsv($output, $row);
			}
		}

        exit;
    }

    public static function staff_list($sql)
    {
        ////////////////////////////////////////////////////
        /////////////////// KHỞI TẠO ĐỂ XUẤT CSV
        ////////////////////////////////////////////////////
        set_time_limit(0);
        error_reporting(0);
        ini_set('display_error', 0);
        $filename = 'Staff List - '.date('d-m-Y H-i-s');
        // output headers so that the file is downloaded rather than displayed
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename='.$filename.'.csv');
        echo "\xEF\xBB\xBF"; // UTF-8 BOM
        $output = fopen('php://output', 'w');

        $heads = array(
            'Code',
            'First name',
            'Last name',
            'Department',
            'Team',
            'Title',
            'Area',
            'Province',
            'Contract type',
            'Contract signed at',
            'Contract term',
            'Contract expired at',
            'Joined at',
            'Off date',
            'Gender',
            'Date of birth',
            'Level',
            'Certificate',
            'Address',
            'Permanent address',
            'Birth place',
            'ID number',
            'ID place',
            'ID date',
            'Social insurance number',
            'Social insurance time',
            'Personal tax',
            'Family allowances registered',
            'Nationality',
            'Religion',
            'Phone number',
            'Email',
            'Note',
            'Additional Info',
        );

        $row = array();
        foreach ($heads as $value)
            $row[] = $value;
        
        fputcsv($output, $row);
        /////////////////////////////////////////////////

        //get department
        $QDepartment = new Application_Model_Department();
        $departments = $QDepartment->get_cache();

        //get teams
        $QTeam = new Application_Model_Team();
        $teams = $QTeam->get_cache();

        //get titles
        /*$QTitle = new Application_Model_Title();
        $titles = $QTitle->get_cache();*/

        $QArea = new Application_Model_Area();
        $areas = $QArea->get_cache();

        //get regional markets
        $QRegionalMarket = new Application_Model_RegionalMarket();
        $regional_markets = $QRegionalMarket->get_cache_all();

        //get contract type
        $QContractType = new Application_Model_ContractType();
        $contract_types = $QContractType->get_cache();

        //get contract term
        $QContractTerm = new Application_Model_ContractTerm();
        $contract_terms = $QContractTerm->get_cache();

        //get contract term
        $QNationality = new Application_Model_Nationality();
        $nationalities = $QNationality->get_cache();

        //get contract term
        $QReligion = new Application_Model_Religion();
        $religions = $QReligion->get_cache();

        ///////////////////////////////////////////////////////
        ///
        // get config
        $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini');
        $config = $config->toArray();
        // addition variable for short name
        $cf_main = $config['resources']['db']['params'];
        $db_local = new CosDb($cf_main['username'], $cf_main['password'], $cf_main['dbname']);
        $res = $db_local->query($sql);

        foreach ($res as $item) {
            $row = array();

            $row[] = '="' . $item['code']  .'"';
            $row[] = $item['firstname'];
            $row[] = $item['lastname'];
            $row[] = isset($departments[$item['department']]) ? $departments[$item['department']] : '';
            $row[] = isset($teams[$item['team']]) ? $teams[$item['team']] : '';
            $row[] = $item['title'];
            $row[] = isset($areas[$regional_markets[$item['regional_market']]['area_id']]) ? $areas[$regional_markets[$item['regional_market']]['area_id']] : '' ;
            $row[] = isset($regional_markets[$item['regional_market']]['name']) ? $regional_markets[$item['regional_market']]['name'] : '';
            $row[] = '="' . (isset($contract_types[$item['contract_type']]) ? $contract_types[$item['contract_type']] : '' )  .'"';
            $row[] = $item['contract_signed_at'] ? ('="' .date('d/m/Y', strtotime($item['contract_signed_at'])) .'"') : '';
            $row[] = isset($contract_terms[$item['contract_term']]) ? $contract_terms[$item['contract_term']] : '';
            $row[] = $item['contract_expired_at'] ? ('="' .date('d/m/Y', strtotime($item['contract_expired_at'])) .'"') : '';
            $row[] = '="' . $item['joined_at'] ? ('="' .date('d/m/Y', strtotime($item['joined_at'])) .'"') : '';
            $row[] = $item['off_date'] ? ('="' . date('d/m/Y', strtotime($item['off_date'])) .'"') : '';
            $row[] = $item['gender']==1 ? 'Nam' : 'Nữ';
            $row[] = '="' . $item['dob'] .'"';
            $row[] = $item['level'];
            $row[] = $item['certificate'];
            $row[] = $item['address'];
            $row[] = $item['permanent_address'];
            $row[] = $item['birth_place'];
            $row[] = '="' . $item['ID_number'] .'"';
            $row[] = $item['ID_place'];
            $row[] = ($item['ID_date'] ? ('="' .date('d/m/Y', strtotime($item['ID_date'])) .'"') : '');
            $row[] = '="' . $item['social_insurance_number'] .'"';
            $row[] = $item['social_insurance_time'];
            $row[] = $item['personal_tax'];
            $row[] = $item['family_allowances_registered'];
            $row[] = isset($nationalities[$item['nationality']]) ? $nationalities[$item['nationality']] : '';
            $row[] = isset($religions[$item['religion']]) ? $religions[$item['religion']] : '';
            $row[] = '="' . $item['phone_number'] .'"';
            $row[] = $item['email'];
            $row[] = $item['note'];
            $row[] = $item['additional_info'];
            
            fputcsv($output, $row);
        }

        $db_local->close();
        exit;
    }
}

class CosDb {
    public $DB_HOST;
    public $DB_PORT;
    public $DB_NAME;
    public $DB_USER;
    public $DB_PASS;
    private $link;

    public function __construct($user, $pass, $db_name, $host = 'localhost', $port = null) {
        $this->setInfo($user, $pass, $db_name, $host, $port);
        $this->link = mysqli_connect($this->DB_HOST, $this->DB_USER, $this->DB_PASS, $this->DB_NAME, $this->DB_PORT);

        if (!$this->link || $this->link->connect_errno) {
            printf("Connect failed: %s\n", $mysqli->connect_error);
            exit();
        }
    }

    private function setInfo($user, $pass, $db_name, $host, $port)
    {
        $this->DB_HOST = $host;
        $this->DB_PORT = is_null( $port ) ? ini_get("mysqli.default_port") : $port;
        $this->DB_NAME = $db_name;
        $this->DB_USER = $user;
        $this->DB_PASS = $pass;
    }

    public function query($sql)
    {
        $this->link->query("SET NAMES utf8;");
        $result = $this->link->query($sql);
        return $result;
    }

    public function close()
    {
        $this->link->close();
    }

    public function real_escape_string($value='')
    {
        return $this->link->real_escape_string($value);
    }
}