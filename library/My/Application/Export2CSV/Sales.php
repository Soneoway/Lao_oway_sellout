<?php
/**
* Tổng hợp các function để export dữ liệu cho Sales
*/
class Export_Sales
{
	
	function __construct()
	{
		
	}

	/**
     * Thống kê số máy, tiền kpi cho sales, xuất ra file CSV
     */
    public static function duplicated_imei($dup_imeis, $imei_model, $points, $timing_sales)
    {
    	////////////////////////////////////////////////////
    	/////////////////// KHỞI TẠO ĐỂ XUẤT CSV
    	////////////////////////////////////////////////////
    	set_time_limit(0);
        error_reporting(0);
        ini_set('display_error', 0);
        $filename = 'Duplicated IMEIs - '.date('d-m-Y H-i-s');
        // output headers so that the file is downloaded rather than displayed
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename='.$filename.'.csv');
        // echo "\xEF\xBB\xBF"; // UTF-8 BOM
        echo chr(239) . chr(187) . chr(191); // UTF-8 BOM
        $output = fopen('php://output', 'w');

    	$head = array(
    		'No.',
    		'IMEI',
    		'Model',
    		'Activated At',
    		'#',
    		'First Staff',
    		'1st Email',
    		'1st Phone Number',
    		'1st Timing For',
    		'1st Store',
    		'1st Customer Name',
    		'1st Customer Phone',
    		'1st Customer Address',
    		'1st Have Photo',
    		
    		'#',
    		'Second Staff',
    		'2nd Email',
    		'2nd Phone Number',
    		'2nd Timing For',
    		'2nd Store',
    		'2nd Customer Name',
    		'2nd Customer Phone',
    		'2nd Customer Address',
    		'2nd Have Photo',
    		'2nd Note',
    		'Approve For',
    		);

    	fputcsv($output, $head);

    	////////////////////////////////////////////////////
    	/////////////////// Xuất
    	////////////////////////////////////////////////////
    	$no = 1;
    	foreach ($dup_imeis as $k=>$imei) {
    		$row = array();
    		$tmp = $timing_sales[ $imei['timing_sales_first'] ];

    		$row[] = $no++;
    		$row[] = '="'.$imei['imei'].'"';

    		if( isset( $imei_model[ $imei['imei'] ] ) )
    			$row[] = $imei_model[ $imei['imei'] ]['product_name'] . '/'.$imei_model[ $imei['imei'] ]['color_name'];
    		else
    			$row[] = '';

    		if( isset( $imei_model[ $imei['imei'] ]['activated_at'] ) )
    			$row[] = '="'.date('d/m/Y H:i:s', strtotime($imei_model[ $imei['imei'] ]['activated_at'])).'"';
    		else
    			$row[] = '';

    		$row[] = '|';

    		$row[] = $tmp['firstname'].' '.$tmp['lastname'];

    		if(!empty($tmp['email'])) {
    			list($email, $domain) =  explode('@',$tmp['email']);
    			$row[] = $email;
    		} else {
    			$row[] = '';
    		}

    		$row[] = !empty($tmp['phone_number']) ? ('="'.$tmp['phone_number'].'"') : '';
			$row[] = $tmp['from'] ? ('="'.date('d/m/Y', strtotime($tmp['from'])).'"') : '';
			$row[] = !empty($tmp['name']) ? $tmp['name'] : '';
			$row[] = !empty($tmp['customer_name']) ? $tmp['customer_name'] : '';
			$row[] = !empty($tmp['customer_phone']) ? ('="'.$tmp['customer_phone'].'"') : '';
			$row[] = !empty($tmp['customer_address']) ? $tmp['customer_address'] : '';
			$row[] = !empty($tmp['photo']) ? 'X' : '';

			$row[] = '|';

    		$row[] = $imei['firstname'].' '.$imei['lastname'];;

    		if(!empty($imei['email'])) {
    			list($email, $domain) =  explode('@',$imei['email']);
    			$row[] = $email;
    		} else {
    			$row[] = '';
    		}

    		$row[] = !empty($imei['phone_number']) ? ('="'.$imei['phone_number'].'"') : '';
			$row[] = $imei['date'] ? ('="'.date('d/m/Y', strtotime($imei['date'])).'"') : '';
			$row[] = !empty($imei['name']) ? $imei['name'] : '';
			$row[] = !empty($imei['customer_name']) ? $imei['customer_name'] : '';
			$row[] = !empty($imei['customer_phone']) ? ('="'.$imei['customer_phone'].'"') : '';
			$row[] = !empty($imei['customer_address']) ? $imei['customer_address'] : '';
			$row[] = !empty($imei['photo']) ? 'X' : '';
			$row[] = !empty($imei['note']) ? trim($imei['note']) : '';
			$row[] = $imei['staff_id'] == $imei['staff_win'] ? '2' : ( $imei['staff_id_first'] == $imei['staff_win'] ? '1' : '0' );

    		fputcsv($output, $row);
    	}

    	exit;
    }
}
