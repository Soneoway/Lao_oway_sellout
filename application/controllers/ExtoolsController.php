<?php
class ExtoolsController extends My_Controller_Action
{
    public function init()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        error_reporting(E_ALL);
        ini_set('max_execution_time',150000);
        ini_set("allow_url_fopen", 1);
        ini_set('display_errors', TRUE);
        ini_set('display_startup_errors', TRUE);
        date_default_timezone_set('Asia/Bangkok');
    }
/*
    public function getIMEIRegDate($imei_list) {

        $secret_key = '60eb46745ba9ce84582728aca7ed92dd';
        $appid      = 'ESA';
        $timestamp  = time();
        $sign       = $this->getSign($imei_list,$appid,$timestamp,$secret_key);

        $mainURL    = "https://esa.myoppo.com/ESAService/ThailandGetImeiRegTime.asmx?wsdl";
        $client     = new SoapClient($mainURL,
        array(
            "trace"      => 1,    // enable trace to view what is happening
            "exceptions" => 0,    // disable exceptions
            "cache_wsdl" => 0)    // disable any caching on the wsdl, encase you alter the wsdl server
        );

        $params = array(
            'timestamp' => $timestamp,
            'appid'     => $appid,
            'imeis'     => $imei_list,
            'sign'      => $sign,
        );

        $data = $client->GetImeiRegTime($params);
        $result_string =  $data->GetImeiRegTimeResult;
        $devided = simplexml_load_string($result_string);

        $act = "";
        if($devided->flag == 1){
            foreach($devided->DataRow as $item){
                $act = $item->REGDATE;
            }
        }

        return $act;
    }
*/
    public function getIMEIRegDateMulti($imei_list) {

        $act = [];
        $all_arr    = array_chunk($imei_list,100);

        foreach($all_arr as $arr_chunk) {

            try {

                $loop_imei_list = implode(",",$arr_chunk);
                $secret_key     = '60eb46745ba9ce84582728aca7ed92dd';
                $appid          = 'ESA';
                $timestamp      = time();
                $sign           = $this->getSign($loop_imei_list,$appid,$timestamp,$secret_key);
              
                // $mainURL = "https://esa.myoppo.com/ESAService/ThailandGetImeiRegTime.asmx?wsdl";
                $mainURL = HOST."esaoppo.wsdl";
                $client = new SoapClient($mainURL,
                array(
                    "trace"      => 1,    // enable trace to view what is happening
                    "exceptions" => 0,    // disable exceptions
                    "cache_wsdl" => 0)    // disable any caching on the wsdl, encase you alter the wsdl server
                    // "connection_timeout" => 10)
                );

                $params = array(
                    'timestamp' => $timestamp,
                    'appid' => $appid,
                    'imeis' => $loop_imei_list,
                    'sign' => $sign
                );

                $data = $client->GetImeiRegTime($params);
                print_r($data); 

                $result_string =  $data->GetImeiRegTimeResult;
                $devided = simplexml_load_string($result_string);
              
                if($devided->flag == 1){
                    foreach($devided->DataRow as $item){
                        $act[(string)$item->IMEI[0]] = (string)$item->REGDATE[0];
                    }
                }



            } catch (Exception $e) {
                echo "Fail! : ".$e;
            }

        }

        return $act;

    }

    public function getSign($imei_list,$appid,$timestamp,$secret_key) {
        $str  = "";
        $str .= "appidESA_imeis".$imei_list."_timestamp".$timestamp."_sercetkey".$secret_key;
        $k = strtoupper(md5($str));
        return $k;
    }

    public function indexAction() {

        $imei     = isset($_REQUEST['imei'])   ?  $_REQUEST['imei']:'';
        $action   = isset($_REQUEST['action']) ?  $_REQUEST['action']:'check';

        echo "<table border=1><tr><th>NO</th><th>IMEI</th><th>Factory : Activate Date</th></tr>";

        if (isset($_REQUEST['action'])) {

            $imei = trim($imei);
            $imeiArr = explode("\n", $imei);
            $imeiArr = array_filter($imeiArr, 'trim'); 

            $multi = [];
            $multi_imei = [];
            $act = "";

            foreach ($imeiArr as $k=>$line) {
                $line_imei = trim($line);
                if($line_imei != '') {
                    $multi[] = $line_imei;
                }
            }

            $k = $this->getIMEIRegDateMulti($multi);
            $i = 1;

            foreach($multi as $im){
                $act    = isset($k[$im]) ? $k[$im]:'';
                echo "<tr><td>".$i++."</td><td>".$im."</td><td>".$act."</td></tr>";
            }

            echo "</table>";

        }

        $chk = "";
        if ($action == 'check') { $chk = 'selected=selected'; }

        echo "<h3>Enter IMEI (Maximum 100 per times)</h3>";
        echo "<form action='' method=post>";
        echo "<input type='hidden' name='action' value='1' />";
        echo "<table>";
        echo    "<tr>";
        echo        "<td>";
        echo            "<strong>Sync or Check?</strong><br>";
        echo            "<select name='action'>";
        echo                "<option value='check' ".$chk.">CHECK</option>";
        echo            "</select>";
        echo        "</td>";
        echo    "</tr>";
        echo    "<tr>";
        echo        "<td>";
        echo            "<h4>IMEI List</h4>";
        echo            "<textarea  name='imei' cols=50 rows=15 required>".$imei."</textarea>";
        echo        "</td>";
        echo    "</tr>";
        echo    "<tr>";
        echo        "<td>";
        echo            "<input type='submit' value='ดำเนินการ' style='background-color:#ccc;padding:10px;' />";
        echo        "</td>";
        echo    "</tr>";
        echo "</table>";
        echo "</form>";

    }

}