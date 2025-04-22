<?php
class Application_Model_WS
{
    private $wssCenterURI;
    private $namespace;

    // public function __construct()
    // {
    //     error_reporting(1);
    //     require_once 'My'.DIRECTORY_SEPARATOR.'nusoap'.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'nusoap.php';
    //     $this->wssCenterURI = TRADE_URI.'wss';
    //     echo $this->wssCenterURI;
    //     $this->namespace = 'OPPO';
    // }

    



    public function _insertToTrade($data)
        {   
            require_once 'My'.DIRECTORY_SEPARATOR.'nusoap'.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'nusoap.php';
            $this->wssCenterURI = TRADE_URI.'wss';
            $this->namespace = 'OPPO';
            set_time_limit(0);
            ini_set('memory_limit', '-1');
            error_reporting(~E_ALL);
            ini_set("display_error", '0');
            $client = new nusoap_client($this->wssCenterURI);
            $result = array();
            $params = array(
                    'firstname'    => $data['firstname'],
                    'lastname'     => $data['lastname'],
                    'phone_number' => $data['phone_number'],
                    'gender'       => $data['gender'],
                    'email'        => $data['email'],
                    'area_id'      => $data['area_id'],
                    'username'     => $data['username'],
                    'password'     => $data['password'],
                    'created_at'   => $data['created_at'],
                    'created_by'   => '1',
                    'status'       => '1',
                    'group_id'     => $data['group_id'],
                );
          
          
          $result =  $client->call("wsInsertStaffCattyTest", array('params' => $params));
          echo "<pre>";
          print_r($result);
          // die;
          // return $result;
        }
    public function _updateToTrade($data)
        {   
            require_once 'My'.DIRECTORY_SEPARATOR.'nusoap'.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'nusoap.php';
            $this->wssCenterURI = TRADE_URI.'wss';
            $this->namespace = 'OPPO';
            set_time_limit(0);
            ini_set('memory_limit', '-1');
            error_reporting(~E_ALL);
            ini_set("display_error", '0');
            $client = new nusoap_client($this->wssCenterURI);
            $result = array();
            $params = array(
                    'firstname'    => $data['firstname'],
                    'lastname'     => $data['lastname'],
                    'phone_number' => $data['phone_number'],
                    'gender'       => $data['gender'],
                    'email'        => $data['email'],
                    'area_id'      => $data['area_id'],
                    'username'     => $data['code'],
                 // 'password'     => $data['password'],
                    'updated_at'   => $data['updated_at'],
                    'updated_by'   => '1',
                    'status'       => $data['status'],
                    'group_id'     => $data['group_id'],
                    'off_date'     => $data['off_date'],
                    'code_hidden'  => $data['code_hidden'],
                );
          
          
          $result =  $client->call("wsUpdateStaffCatty", array('params' => $params));
          echo "<pre>";
          print_r($result);
          // die;
          // return $result;
        }

    public function _insertStoreToTrade($data,$info)
        {   
            require_once 'My'.DIRECTORY_SEPARATOR.'nusoap'.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'nusoap.php';
            $this->wssCenterURI = TRADE_URI.'wss';
            $this->namespace = 'OPPO';
            set_time_limit(0);
            ini_set('memory_limit', '-1');
            error_reporting(~E_ALL);
            ini_set("display_error", '0');
            $client = new nusoap_client($this->wssCenterURI);
            $result = array();
            $params = array(
                        'name'              => $data['name'],
                        'store_id'          => $data['store_id'],
                        'store_code'        => $data['store_code'],
                        'company_name'      => $data['company_name'],
                        'company_address'   => $data['company_address'],
                        'shipping_address'  => $data['shipping_address'],
                        'phone_number'      => $data['phone_number'],
                        'regional_market'   => $data['regional_market'],
                        'district'          => $data['district'],
                        'sub_district'      => $data['sub_district'],
                        'org_dealer'        => $data['org_dealer'],
                        'agency_name'       => $data['agency_name'],
                        'contact_point'     => $data['contact_point'],
                        'contact_phone'     => $data['contact_phone'],
                        'contact_email'     => $data['contact_email'],
                        'mst'               => $data['mst'],
                        'rank'              => $data['rank'],
                        'd_id'              => $data['d_id'],
                        'created_at'        => $data['created_at'],
                        'created_by'        => $data['created_by'],
                        'id'                => $data['id'],
                        'info'              => $info,
                );
          
          
          $result =  $client->call("wsInsertStoreToTrade", array('params' => $params));

          echo "<pre>";
          print_r($result);
          
          // die;
          // return $result;
        }
    public function _updateStoreToTrade($data,$info)
        {   
            require_once 'My'.DIRECTORY_SEPARATOR.'nusoap'.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'nusoap.php';
            $this->wssCenterURI = TRADE_URI.'wss';
            $this->namespace = 'OPPO';
            set_time_limit(0);
            ini_set('memory_limit', '-1');
            error_reporting(~E_ALL);
            ini_set("display_error", '0');
            $client = new nusoap_client($this->wssCenterURI);
            $result = array();
            $params = array(
                        'name'              => $data['name'],
                        'store_id'          => $data['store_id'],
                        'store_code'        => $data['store_code'],
                        'company_name'      => $data['company_name'],
                        'company_address'   => $data['company_address'],
                        'shipping_address'  => $data['shipping_address'],
                        'phone_number'      => $data['phone_number'],
                        'regional_market'   => $data['regional_market'],
                        'district'          => $data['district'],
                        'sub_district'      => $data['sub_district'],
                        'org_dealer'        => $data['org_dealer'],
                        'agency_name'       => $data['agency_name'],
                        'contact_point'     => $data['contact_point'],
                        'contact_phone'     => $data['contact_phone'],
                        'contact_email'     => $data['contact_email'],
                        'mst'               => $data['mst'],
                        'rank'              => $data['rank'],
                        'd_id'              => $data['d_id'],
                        'updated_at'        => $data['updated_at'],
                        'updated_by'        => $data['updated_by'],
                        'id'                => $data['id'],
                        //'info'              => $info,
                );
          
          
          $result =  $client->call("wsUpdateStoreToTrade", array('params' => $params));

          echo "<pre>";
          print_r($result);
          
          // die;
          // return $result;
        } 
    public function _deleteStoreToTrade($data)
        {   
            require_once 'My'.DIRECTORY_SEPARATOR.'nusoap'.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'nusoap.php';
            $this->wssCenterURI = TRADE_URI.'wss';
            $this->namespace = 'OPPO';
            set_time_limit(0);
            ini_set('memory_limit', '-1');
            error_reporting(~E_ALL);
            ini_set("display_error", '0');
            $client = new nusoap_client($this->wssCenterURI);
            $result = array();
            $params = array(
                        // 'id'    => '600000001',
                        'id'    => $data['id'],
                        'del'   => $data['del'],
                        'd_id'  => $data['d_id'],
                );
          
          
          $result =  $client->call("wsDeleteStoreToTrade", array('params' => $params));

          echo "<pre>";
          print_r($result);
          
          // die;
          return $result;
        }                
}

