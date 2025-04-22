<?php

    $imei = trim($this->getRequest()->getParam('imei', ''));
    $imei = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $imei);
    $imei = explode("\n", $imei);

    if (count($imei) == 1 && $imei[0] == '') { exit; }
    $imei = array_unique(array_map('trim', $imei));

    set_time_limit(0);
    error_reporting(0);
    ini_set('display_error', 0);
    ini_set('memory_limit', -1);

    $filename = 'IMEIs Checking - '.date('d/m/Y h-m-s');
    // output headers so that the file is downloaded rather than displayed
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename='.$filename.'.csv');
    // echo "\xEF\xBB\xBF"; // UTF-8 BOM
    echo chr(239) . chr(187) . chr(191); // UTF-8 BOM
    $output = fopen('php://output', 'w');

    $heads = array(
        'No.',
        'IMEI Area',
        'IMEI Provience',
        'IMEI Store',
        'IMEI Distributor',
        'IMEI Warehouse',
        'Imei',
        'Model',
        'Color',
        'Product Type',
        'Area',
        'Province',
        'Store Code',
        'Store Name',
        'Verification Code',        
        'Verification Name',
        'Position',
        'Verification Time',
        'Activated Date',
        'Customer Name',
        'Customer Phone', 
        'Pre-Order',
    );

    fputcsv($output, $heads);

    $Qimei = new Application_Model_CheckImeiLog();
    $data = $Qimei->getCheckImei($imei);

    // reduce array dimension for check real imei
    for($i=0;$i<count($data);$i++) {
        $real_imei[$i] = $data[$i]['imei'];
    }

    $no = 1;
    $date2 = new DateTime();

    for ($i=0;$i<count($imei);$i++) {

        $row = array();

        if (in_array( trim($imei[$i]) , $real_imei )) { 

            $k = array_search( trim($imei[$i]), $real_imei);

            $row[] = $no++;
            $row[] = $data[$k]['imei_area'];
            $row[] = $data[$k]['imei_provience'];
            $row[] = $data[$k]['imei_distributor'];
            $row[] = $data[$k]['imei_store'];
            $row[] = $data[$k]['imei_warehouse'];

            $row[] = $data[$k]['imei'];
            $row[] = $data[$k]['good_name'];
            $row[] = $data[$k]['color_name'];
            switch($data[$k]['product_type']){
                case '1':
                    $model_type = 'Normal';
                    break;
                    case '3':
                    $model_type = 'Staff';
                    break;
                    case '5':
                    $model_type = 'APK';
                    break;
            };

            $row[] = $model_type;
            
            $row[] = $data[$k]['area_name'];
            $row[] = $data[$k]['province'];
            $row[] = $data[$k]['store_code'];
            $row[] = $data[$k]['store_name'];
            $row[] = $data[$k]['verification_code'];
            $row[] = $data[$k]['verification_name'];
            $row[] = $data[$k]['verification_position'];

            $row[] = $data[$k]['verification_date'];
            $row[] = $data[$k]['activated_date'];

            $row[] = $data[$k]['cus_name'];
            $row[] = '="'.$data[$k]['cus_phone'].'"';
            $row[] = $data[$k]['pre_order_status'];

        } else {

            $row[] = $no++;
            $row[] = '-';
            $row[] = '-';
            $row[] = '-';
            $row[] = '-';
            $row[] = '-';
            $row[] = $imei[$i];
            $row[] = '-';
            $row[] = '-';
            $row[] = '-';
            $row[] = '-';
            $row[] = '-';
            $row[] = '-';
            $row[] = '-';
            $row[] = '-';
            $row[] = '-';
            $row[] = '-';
            $row[] = '-';
            $row[] = '-';
            $row[] = '-';
            $row[] = '-';
            $row[] = '-';

        }
   

        fputcsv($output, $row);
    }

    exit;

?>
