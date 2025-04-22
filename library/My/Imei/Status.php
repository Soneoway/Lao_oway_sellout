<?php
/**
* @author buu.pham
*/
class My_Imei_Status extends My_Type_Enum
{
    /*===========================*/
    /* IMEI Status
    /*===========================*/
    const Warehouse      = 1;
    const Scanned_Out    = 2;
    const On_The_Way     = 3;
    const Distributor    = 4;
    const Sold_Out       = 5;
    const Warranty       = 6;
    const On_Change_Sale = 7;
    const Changed_Sale   = 8;
    const Returned       = 9;

    /**
     * Lưu thông tin trạng thái IMEI
     * @param integer $imei   IMEI SN
     * @param integer $status Trạng thái IMEI (danh sách const ở trên)
     * @param array  $info   Thông tin bổ sung
     */
    public static function setStatus($imei, $status, array $info)
    {
        require_once 'My'.DIRECTORY_SEPARATOR.'nusoap'.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'nusoap.php';
        $client = new nusoap_client(WSS_WH_URI);

        $params = array(
            'imei' => $imei,
            'status' => $status,
            'info' => serialize( $info ),
        );

        try {
            $result = $client->call("setImeiStatus", $params);
        } catch (Exception $e) {
            // $e->getMessage(); // xử lý Exception
        }

        if($client->fault)
        {
            // xử lý lỗi
        }
        else
        {
            // thành công
        }

    }

    public static function getStatus($imei, $date)
    {
        require_once 'My'.DIRECTORY_SEPARATOR.'nusoap'.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'nusoap.php';
        $client = new nusoap_client(WSS_WH_URI);

        $params = array(
            'imei' => $imei,
            'date' => $date,
        );

        try {
            $result = $client->call("getImeiStatus", $params);
        } catch (Exception $e) {
            // $e->getMessage(); // xử lý Exception
        }

        if($client->fault)
        {
            // xử lý lỗi
        }
        else
        {
            // thành công
            return $result;
        }
    }

    public static function getStatusInfo($imei, $date, $status)
    {
        require_once 'My'.DIRECTORY_SEPARATOR.'nusoap'.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'nusoap.php';
        $client = new nusoap_client(WSS_WH_URI);

        $params = array(
            'imei'   => $imei,
            'date'   => $date,
            'status' => $status,
        );

        try {
            $result = $client->call("getImeiStatusInfo", $params);
        } catch (Exception $e) {
            // $e->getMessage(); // xử lý Exception
        }

        if($client->fault)
        {
            // xử lý lỗi
        }
        else
        {
            // thành công
            return unserialize( $result );
        }
    }
}
