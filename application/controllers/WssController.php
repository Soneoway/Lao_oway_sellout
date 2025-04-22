<?php
class WssController extends My_Controller_Action
{
    private $wssURI;
    private $namespace;

    public function init()
    {
        error_reporting(0);
        ini_set("display_error", -1);
        set_time_limit(0);
        ini_set('memory_limit', -1);

        require_once 'My'.DIRECTORY_SEPARATOR.'nusoap'.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'nusoap.php';

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $this->wssURI = HOST.'wss';
        $this->namespace = 'OPPO';

        doAuthenticate();
    }

    public function indexAction()
    {
        //Create a new soap server
        $server = new soap_server(null, array('uri' => $this->wssURI));

        /*$server->soap_defencoding = 'utf-8';
        $server->decode_utf8 = false;*/

        //Configure our WSDL
        $server->configureWSDL($this->namespace, $this->namespace, $this->wssURI);
        $server->wsdl->schemaTargetNamespace = $this->namespace;

        // ---------------- wsAddNotification
        $server->register(
            'wsAddNotification',
            array(
                'title'    => 'xsd:string',
                'content'  => 'xsd:string',
                'category' => 'xsd:integer',
                'show_to'  => 'xsd:array',
                'pop_up'   => 'xsd:integer',
                'type'     => 'xsd:integer',
            ),
            array(),    // output parameters
            $this->namespace,
            $this->namespace.'#wsAddNotification',
            'rpc',
            'encoded',
            'Add notification'
        );

        // Complex Array Keys and Types ++++++++++++++++++++++++++++++++++++++++++
        $server->wsdl->addComplexType('areaInfo','complexType','struct','all','',
            array(
                'id' => array('name'=>'id','type'=>'xsd:int'),
                'name' => array('name'=>'name','type'=>'xsd:string')
            )
        );
        // *************************************************************************

        // Complex Array ++++++++++++++++++++++++++++++++++++++++++
        $server->wsdl->addComplexType('areaInfoArray','complexType','array','','SOAP-ENC:Array',
            array(),
            array(
                array(
                    'ref' => 'SOAP-ENC:arrayType',
                    'wsdl:arrayType' => 'tns:areaInfo[]'
                )
            ),
            'tns:areaInfo'
        );


        // Complex job ++++++++++++++++++++++++++++++++++++++++++
        $server->wsdl->addComplexType('jobInfo','complexType','struct','all','',
            array(
                'id' => array('name'=>'id','type'=>'xsd:int'),
                'title' => array('name'=>'title','type'=>'xsd:string'),
                'status' => array('name'=>'status','type'=>'xsd:int'),
                'from' => array('name' => 'from', 'type' => 'xsd:string'),
                'to'   => array('name' => 'to', 'type' => 'xsd:string'),
                'category' => array('name' => 'category', 'type' => 'xsd:int'),
                'salary' => array('name' => 'salary', 'type' => 'xsd:int'),
                'year' => array('name' => 'year', 'type' => 'xsd:int'),
                'area_id' => array('name' => 'area_id', 'type' => 'xsd:int'),
                'regional_market_id' => array('name' => 'regional_market_id', 'type' => 'xsd:int'), 
                'content' => array('name'=>'content','type'=>'xsd:string'), 
                'created_at' => array('name' => 'created_at', 'type' => 'xsd:string'),
            )
        );

         // Complex Array ++++++++++++++++++++++++++++++++++++++++++
        $server->wsdl->addComplexType('jobInfoArray','complexType','array','','SOAP-ENC:Array',
            array(),
            array(
                array(
                    'ref' => 'SOAP-ENC:arrayType',
                    'wsdl:arrayType' => 'tns:jobInfo[]'
                )
            ),
            'tns:jobInfo'
        );

        // Complex Array Keys and Types ++++++++++++++++++++++++++++++++++++++++++
        $server->wsdl->addComplexType('provinceInfo','complexType','struct','all','',
            array(
                'id' => array('name'=>'id','type'=>'xsd:int'),
                'name' => array('name'=>'name','type'=>'xsd:string'),
                'parent' => array('name'=>'parent','type'=>'xsd:int')
            )
        );
        // *************************************************************************

        // Complex Array ++++++++++++++++++++++++++++++++++++++++++
        $server->wsdl->addComplexType('provinceInfoArray','complexType','array','','SOAP-ENC:Array',
            array(),
            array(
                array(
                    'ref' => 'SOAP-ENC:arrayType',
                    'wsdl:arrayType' => 'tns:provinceInfo[]'
                )
            ),
            'tns:provinceInfo'
        );

        // Complex Array Keys and Types ++++++++++++++++++++++++++++++++++++++++++
        $server->wsdl->addComplexType('diamondLevelInfo','complexType','struct','all','',
            array(
                'id'                  => array('name' => 'id','type' => 'xsd:int'),
                'name'                => array('name' => 'name','type' => 'xsd:string'),
                'sales_from'          => array('name' => 'sales_from', 'type' => 'xsd:integer'),
                'sales_to'            => array('name' => 'sales_to', 'type' => 'xsd:integer'),
                'sales_market_share'  => array('name' => 'sales_market_share', 'type' => 'xsd:decimal'),
                'figure_market_share' => array('name' => 'figure_market_share', 'type' => 'xsd:decimal'),
            )
        );
        // *************************************************************************

        // Complex Array ++++++++++++++++++++++++++++++++++++++++++
        $server->wsdl->addComplexType('diamondLevelInfoArray','complexType','array','','SOAP-ENC:Array',
            array(),
            array(
                array(
                    'ref' => 'SOAP-ENC:arrayType',
                    'wsdl:arrayType' => 'tns:diamondLevelInfo[]'
                )
            ),
            'tns:diamondLevelInfo'
        );

        // Complex Array Keys and Types ++++++++++++++++++++++++++++++++++++++++++
        $server->wsdl->addComplexType('storeInfo','complexType','struct','all','',
            array(
                'id'       => array('name' => 'id','type' => 'xsd:integer'),
                'name'     => array('name' => 'name','type' => 'xsd:string'),
                'company_address'  => array('name' => 'company_address','type' => 'xsd:string'),
                'loyalty_plan'  => array('name' => 'loyalty_plan','type' => 'xsd:string'),
                'district' => array('name' => 'district','type' => 'xsd:integer'),
                'd_id'     => array('name' => 'd_id','type' => 'xsd:integer'),
                'del'     => array('name' => 'del','type' => 'xsd:integer'),
            )
        );
        // *************************************************************************

        // Complex Array ++++++++++++++++++++++++++++++++++++++++++
        $server->wsdl->addComplexType('storeInfoArray','complexType','array','','SOAP-ENC:Array',
            array(),
            array(
                array(
                    'ref' => 'SOAP-ENC:arrayType',
                    'wsdl:arrayType' => 'tns:storeInfo[]'
                )
            ),
            'tns:storeInfo'
        );

        // Complex Array Keys and Types ++++++++++++++++++++++++++++++++++++++++++
        $server->wsdl->addComplexType('params','complexType','struct','all','',
            array(
                'name'  => array('name' => 'name','type' => 'xsd:string'),
                'value' => array('name' => 'value','type' => 'xsd:string'),
            )
        );

        // Complex Array ++++++++++++++++++++++++++++++++++++++++++
        $server->wsdl->addComplexType('paramsArray','complexType','array','','SOAP-ENC:Array',
            array(),
            array(
                array(
                    'ref' => 'SOAP-ENC:arrayType',
                    'wsdl:arrayType' => 'tns:params[]'
                )
            ),
            'tns:params'
        );

        // Complex Array Keys and Types ++++++++++++++++++++++++++++++++++++++++++

        // Complex Array ++++++++++++++++++++++++++++++++++++++++++
        $server->wsdl->addComplexType('selloutInfoArray','complexType','array','','SOAP-ENC:Array',
            array(),
            array(
                array(
                    'ref' => 'SOAP-ENC:arrayType',
                    'wsdl:arrayType' => 'tns:selloutInfo[]'
                )
            ),
            'tns:selloutInfo'
        );

        // ---------------- wsGetAreaByStaffCode
        $server->register(
            'wsGetAreaByStaffCode',
            array(),
            array('area_list' => 'tns:areaInfoArray'),    // output parameters
            $this->namespace,
            $this->namespace.'#wsGetAreaByStaffCode',
            'rpc',
            'encoded',
            'Get All Area By Staff Code'
        );

        // ---------------- wsGetAllArea
        $server->register(
            'wsGetAllArea',
            array(),
            array('area_list' => 'tns:areaInfoArray'),    // output parameters
            $this->namespace,
            $this->namespace.'#wsGetAllArea',
            'rpc',
            'encoded',
            'Get all Area from Center'
        );

        // ---------------- wsGetAllJob
        $server->register(
            'wsGetAllJob',
            array(
                'page'   => 'xsd:integer',
                'limit'  => 'xsd:integer',
                'params' => 'xsd:string',
            ),
            array('job_list' => 'xsd:Array'),    // output parameters
            $this->namespace,
            $this->namespace.'#wsGetAllJob',
            'rpc',
            'encoded',
            'Get all Job from Center'
        );

         // ---------------- Insert CV
        $server->register(
            'wsInsertCV',
            array(
                'data'   => 'xsd:string',
                'token'  => 'xsd:string',
                'auth'   => 'xsd:string'
            ),
            array('result' => 'xsd:Array'),    // output parameters
            $this->namespace,
            $this->namespace.'#wsInsertCV',
            'rpc',
            'encoded',
            'Insert CV from webservice'
        );


        // ---------------- wsGetAllJobDetail
        $server->register(
            'wsGetDetailJob',
            array(               
                'id' => 'xsd:integer',
            ),
            array('job_list' => 'xsd:Array'),    // output parameters
            $this->namespace,
            $this->namespace.'#wsGetDetailJob',
            'rpc',
            'encoded',
            'Get detail Job from Center'
        );

        // ---------------- wsGetAllProvince
        $server->register(
            'wsGetAllProvince',
            array(),
            array('province_list' => 'tns:provinceInfoArray'),    // output parameters
            $this->namespace,
            $this->namespace.'#wsGetAllProvince',
            'rpc',
            'encoded',
            'Get all province'
        );

        // ---------------- wsGetProvinceByAreaId
        $server->register(
            'wsGetProvinceByAreaId',
            array('id' => 'xsd:integer',),
            array('province_list' => 'xsd:Array'),    // output parameters
            $this->namespace,
            $this->namespace.'#wsGetProvinceByAreaId',
            'rpc',
            'encoded',
            'Get province by area id'
        );

        // ---------------- wsGetDistrictByProvinceId
        $server->register(
            'wsGetDistrictByProvinceId',
            array('id' => 'xsd:integer',),
            array('district_list' => 'tns:provinceInfoArray'),    // output parameters
            $this->namespace,
            $this->namespace.'#wsGetDistrictByProvinceId',
            'rpc',
            'encoded',
            'Get district by province id'
        );

        // ---------------- wsGetAllDiamondLevel
        $server->register(
            'wsGetAllDiamondLevel',
            array(),
            array('list' => 'tns:diamondLevelInfoArray'),    // output parameters
            $this->namespace,
            $this->namespace.'#wsGetAllDiamondLevel',
            'rpc',
            'encoded',
            'Get all diamond level'
        );

        // ---------------- wsGetStore
        $server->register(
            'wsGetStore',
            array(
                'page'   => 'xsd:integer',
                'limit'  => 'xsd:integer',
                'params' => 'tns:paramsArray',
            ),
            array(
                'list' => 'tns:storeInfoArray',
                'total' => 'xsd:integer',
            ),    // output parameters
            $this->namespace,
            $this->namespace.'#wsGetStore',
            'rpc',
            'encoded',
            'Get store list'
        );

        // ---------------- wsLogin
        $server->register('wsLogin',                    // method name
            array(

                'username'      => 'xsd:string',
                'password'      => 'xsd:string',
                'system'        => 'xsd:string',
                'time'          => 'xsd:int',
                'authentication'=> 'xsd:string',

            ),          // input parameters
            array('return' => 'xsd:Array'),    // output parameters
            $this->namespace,                         // namespace
            $this->namespace . '#wsLogin',                   // soapaction
            'rpc',                                    // style
            'encoded',                                // use
            'wsLogin'        // documentation
        );

        // ---------------- wsGetAllStaff
        $server->register('wsGetAllStaff',                    // method name
            array(
                'params'        => 'xsd:Array',
                'system'        => 'xsd:string',
                'time'          => 'xsd:int',
                'authentication'=> 'xsd:string',

            ),          // input parameters
            array('return' => 'xsd:Array'),    // output parameters
            $this->namespace,                         // namespace
            $this->namespace . '#wsGetAllStaff',                   // soapaction
            'rpc',                                    // style
            'encoded',                                // use
            'wsGetAllStaff'        // documentation
        );

        // ---------------- wsSetDistributorStockLog
        $server->register(
            'wsSetDistributorStockLog',
            array(
                'params' => 'tns:paramsArray',
            ),
            array(),    // output parameters
            $this->namespace,
            $this->namespace.'#wsSetDistributorStockLog',
            'rpc',
            'encoded',
            'Get wsSetDistributorStockLog'
        );

        // ---------------- wsGetStoreByStrId
        $server->register(
            'wsGetStoreByStrId',
            array(
                'str_id' => 'xsd:string',
            ),
            array(
                'result' => 'xsd:Array',
            ),    // output parameters
            $this->namespace,
            $this->namespace.'#wsGetStoreByStrId',
            'rpc',
            'encoded',
            'Get Store By String Id'
        );

        // ---------------- wsGetStoreRandom
        $server->register(
            'wsGetStoreRandom',
            array(
                'params' => 'xsd:Array',
            ),
            array(
                'return' => 'xsd:Array',
            ),    // output parameters
            $this->namespace,
            $this->namespace.'#wsGetStoreRandom',
            'rpc',
            'encoded',
            'Get Store Random'
        );
        
        // ---------------- wsGetStoreByLeader
        $server->register(
            'wsGetStoreByLeader',
            array(
                'params' => 'xsd:Array',
            ),
            array(
                'return' => 'xsd:Array',
            ),    // output parameters
            $this->namespace,
            $this->namespace.'#wsGetStoreByLeader',
            'rpc',
            'encoded',
            'Get Store Random'
        );
    // NEW FUNCTION PungPond //
        $server->register(
            'wsGetStoreList',
            array(
                'params' => 'xsd:paramsArray',
            ),
            array(
                'result' => 'xsd:Array',
            ),    // output parameters
            $this->namespace,
            $this->namespace.'#wsGetStoreList',
            'rpc',
            'encoded',
            'Get Store By String Id'
        );

        $server->register(
            'wsGetStoreListDid',
            array(
                'params' => 'xsd:string',
            ),
            array(
                'result' => 'xsd:Array',
            ),    // output parameters
            $this->namespace,
            $this->namespace.'#wsGetStoreListDid',
            'rpc',
            'encoded',
            'Get Store By String Id'
        );
        $server->register(
            'wsStoreDidForCreate',
            array(
                'params' => 'xsd:paramsArray',
            ),
            array(
                'result' => 'xsd:string',
            ),    // output parameters
            $this->namespace,
            $this->namespace.'#wsStoreDidForCreate',
            'rpc',
            'encoded',
            'Get Store By String Id'
        );

        $server->register(
            'wsGetSelloutByProduct',
            array(
                'params' => 'xsd:paramsArray',
            ),
            array(
                'result' => 'xsd:Array',
            ),    // output parameters
            $this->namespace,
            $this->namespace.'#wsGetSelloutByProduct',
            'rpc',
            'encoded',
            'Get Sellout By Product,Color,Store,Timing Date'
        );

        $server->register(
            'wsGetSellinByProduct',
            array(
                'params' => 'xsd:paramsArray',
            ),
            array(
                'result' => 'xsd:Array',
            ),    // output parameters
            $this->namespace,
            $this->namespace.'#wsGetSellinByProduct',
            'rpc',
            'encoded',
            'Get Sellin By Product,Color,Distributor,Sellin Date'
        );

        $server->register(
            'wsGetAllDistributor',
            array(
                'params' => 'xsd:paramsArray',
            ),
            array(
                'result' => 'xsd:Array',
            ),    // output parameters
            $this->namespace,
            $this->namespace.'#wsGetAllDistributor',
            'rpc',
            'encoded',
            'Get Distributor from warehouse'
        );

        $server->register(
            'wsGetAllGood',
            array(
                'params' => 'xsd:paramsArray',
            ),
            array(
                'result' => 'xsd:Array',
            ),    // output parameters
            $this->namespace,
            $this->namespace.'#wsGetAllGood',
            'rpc',
            'encoded',
            'Get Good from warehouse'
        );

        $server->register(
            'wsGetAllGoodColor',
            array(
                'params' => 'xsd:paramsArray',
            ),
            array(
                'result' => 'xsd:Array',
            ),    // output parameters
            $this->namespace,
            $this->namespace.'#wsGetAllGoodColor',
            'rpc',
            'encoded',
            'Get Good Color from warehouse'
        );

        $server->register(
            'wsGetRemainStock',
            array(
                'params' => 'xsd:paramsArray',
            ),
            array(
                'result' => 'xsd:Array',
            ),    // output parameters
            $this->namespace,
            $this->namespace.'#wsGetRemainStock',
            'rpc',
            'encoded',
            'Get Remain Stock By Dealer from warehouse'
        );

        $server->register(
            'wsInsertUser',
            array(
                'params' => 'xsd:paramsArray',
            ),
            array(
                'result' => 'xsd:Array',
            ),    // output parameters
            $this->namespace,
            $this->namespace.'#wsInsertUser',
            'rpc',
            'encoded',
            'Insert User By HR System'
        );

        $server->register(
            'wsStoreListInvastment',
            array(
                'page' => 'xsd:string',
                'limit' => 'xsd:string',
                'total' => 'xsd:string',
                'params' => 'xsd:paramsArray',
            ),
            array(
                'result' => 'xsd:Array',
            ),    // output parameters
            $this->namespace,
            $this->namespace.'#wsStoreListInvastment',
            'rpc',
            'encoded',
            'Get Store By String Id'
        );
        $POST_DATA = isset($GLOBALS['HTTP_RAW_POST_DATA']) ? $GLOBALS['HTTP_RAW_POST_DATA'] : '';
        
        //$POST_DATA = file_get_contents('php://input');
        $server->register(
            'wsGetSellOut',
            array(
                'params' => 'xsd:Array',
            ),
            array(
                'result' => 'xsd:Array',
            ),    // output parameters
            $this->namespace,
            $this->namespace.'#wsGetSellOut',
            'rpc',
            'encoded',
            'Get Sellin By Product,Color,Distributor,Sellin Date'
        );
        
        $server->register(
            'wsGetShopSellout',
            array(
                'params' => 'xsd:Array',
            ),
            array(
                'result' => 'xsd:Array',
            ),    // output parameters
            $this->namespace,
            $this->namespace.'#wsGetShopSellout',
            'rpc',
            'encoded',
            'Get Sellin By Product,Color,Distributor,Sellin Date'
        );
        $server->register(
            'wsStoreListAll',
            array(
                'params' => 'xsd:Array',
            ),
            array(
                'result' => 'xsd:Array',
            ),    // output parameters
            $this->namespace,
            $this->namespace.'#wsStoreListAll',
            'rpc',
            'encoded',
            'Get Sellin By Product,Color,Distributor,Sellin Date'
        );
        $server->register(
            'wsStoreAddress',
            array(
                'params' => 'xsd:Array',
            ),
            array(
                'result' => 'xsd:Array',
            ),    // output parameters
            $this->namespace,
            $this->namespace.'#wsStoreAddress',
            'rpc',
            'encoded',
            'Get Sellin By Product,Color,Distributor,Sellin Date'
        );
        $server->register(
            'wsStoreMarketType',
            array(
                'params' => 'xsd:Array',
            ),
            array(
                'result' => 'xsd:Array',
            ),    // output parameters
            $this->namespace,
            $this->namespace.'#wsStoreMarketType',
            'rpc',
            'encoded',
            'Get Sellin By Product,Color,Distributor,Sellin Date'
        );
        $server->register(
            'wsGetAllStoreCli',
            array(
                'params' => 'xsd:Array',
            ),
            array(
                'result' => 'xsd:Array',
            ),    // output parameters
            $this->namespace,
            $this->namespace.'#wsGetAllStoreCli',
            'rpc',
            'encoded',
            'Get Sellin By Product,Color,Distributor,Sellin Date'
        );
        $server->register(
            'wsGetCountAllStoreCli',
            array(
                'params' => 'xsd:Array',
            ),
            array(
                'result' => 'xsd:Array',
            ),    // output parameters
            $this->namespace,
            $this->namespace.'#wsGetCountAllStoreCli',
            'rpc',
            'encoded',
            'Get Sellin By Product,Color,Distributor,Sellin Date'
        );
        $server->register(
            'wsUpdateStoreforTerade',
            array(
                'params' => 'xsd:Array',
            ),
            array(
                'result' => 'xsd:Array',
            ),    // output parameters
            $this->namespace,
            $this->namespace.'#wsUpdateStoreforTerade',
            'rpc',
            'encoded',
            'Get Store for Update,Trade'
        );
        $server->register(
            'wsGetAllAreaCli',
            array(
                'params' => 'xsd:Array',
            ),
            array(
                'result' => 'xsd:Array',
            ),    // output parameters
            $this->namespace,
            $this->namespace.'#wsGetAllAreaCli',
            'rpc',
            'encoded',
            'Get Sellin By Product,Color,Distributor,Sellin Date'
        );
        $server->register(
            'wsGetAllRegionalMarketCli',
            array(
                'params' => 'xsd:Array',
            ),
            array(
                'result' => 'xsd:Array',
            ),    // output parameters
            $this->namespace,
            $this->namespace.'#wsGetAllRegionalMarketCli',
            'rpc',
            'encoded',
            'Get Sellin By Product,Color,Distributor,Sellin Date'
        );
          $server->register(
            'wsGetStoreStaff',
            array(
                'params' => 'xsd:Array',
            ),
            array(
                'result' => 'xsd:Array',
            ),    // output parameters
            $this->namespace,
            $this->namespace.'#wsGetStoreStaff',
            'rpc',
            'encoded',
            'Get Store Staff'
        );
          $server->register(
            'wsGetStoreByAreaSellOut',
            array(
                'params' => 'xsd:Array',
            ),
            array(
                'result' => 'xsd:Array',
            ),    // output parameters
            $this->namespace,
            $this->namespace.'#wsGetStoreByAreaSellOut',
            'rpc',
            'encoded',
            'Get Store + sell out by Area'
        );
          $server->register(
            'wsGetGrandArea',
            array(),
            array('result' => 'xsd:Array'),    // output parameters
            $this->namespace,
            $this->namespace.'#wsGetGrandArea',
            'rpc',
            'encoded',
            'Get all Grand area for trade report area storage'
        );
        // ---------------- wsCreateUserHr
        $server->register(
            'wsCreateUserHr',
            array(
                'staff_code' => 'xsd:string',
            ),
            array(
                'result' => 'xsd:Array',
            ),    // output parameters
            $this->namespace,
            $this->namespace.'#wsCreateUserHr',
            'rpc',
            'encoded',
            'Create HR User'
        );
        // ---------------- wsGetLeaderByStaffCode
        $server->register(
            'wsGetLeaderByArea',
            array(
                'staff_code' => 'xsd:string',
            ),
            array(
                'result' => 'xsd:Array',
            ),    // output parameters
            $this->namespace,
            $this->namespace.'#wsGetLeaderByArea',
            'rpc',
            'encoded',
            'Get Leader List By Staff Code'
        );
        $server->service($POST_DATA);
    }
}

function doAuthenticate(){
    if(isset($_SERVER['PHP_AUTH_USER']) and isset($_SERVER['PHP_AUTH_PW']) )
    {
        if($_SERVER['PHP_AUTH_USER']==WS_USERNAME and $_SERVER['PHP_AUTH_PW']=WS_PASSWORD )
            return true;
        else
            return  false;
    }
}

function wsGetAllStaff(array $params, $system, $time, $authentication){

    $rs = array('code' => 0);
    try {
        if (!$system
            or !$time
            or !$authentication){

            $code = 2;
            throw new Exception('The parameter is invalid');

        }

        // check authenticate
        $AUTHENTICATE_KEY = unserialize(AUTHENTICATE_KEY);
        if (
            !isset($AUTHENTICATE_KEY[strtoupper($system)])
            or md5($time.'0pp0'.$AUTHENTICATE_KEY[strtoupper($system)]) != $authentication
        ) {
            $code = 3;

            throw new Exception('Authentication is invalid');
        }

        // check time
        /*if (time() - $time > 30){
            $code = 4;
            throw new Exception('Time is invalid');
        }*/
        // End of check authenticate

        $db = Zend_Registry::get('db');

        $select = $db->select()
            ->from(array('p' => 'staff'),
                array('p.id','p.firstname','p.lastname','p.email'));

        if (isset($params['group_id']) and $params['group_id']){
            if (strtoupper($system)=='CS') {
                $select->join(array('scp' => 'staff_cs_priviledge'), 'p.id=scp.staff_id AND scp.type=' . STAFF_CS_PRIVILEGE_TYPE_GROUP, array());
                $gr = explode(',', $params['group_id']);
                $select->where('scp.value IN (?)', $gr);
            }
        }

        $rs['data'] = $db->fetchAll($select);

    } catch (Exception $e){
        $rs['code'] = isset($code) ? ($code and $message = $e->getMessage()) : (1 and $message = 'Unknown Error');
        $rs['message'] = $message;
    }
    return $rs;
}

function wsLogin($username, $password, $system, $time, $authentication){

    $rs = array('code' => 0);
    try {
        if (!$username
            or !$password
            or !$system
            or !$time
            or !$authentication){

            $code = 2;
            throw new Exception('The parameter is invalid');

        }

        // check authenticate
        $AUTHENTICATE_KEY = unserialize(AUTHENTICATE_KEY);
        if (
            !isset($AUTHENTICATE_KEY[strtoupper($system)])
            or md5($time.'0pp0'.$AUTHENTICATE_KEY[strtoupper($system)]) != $authentication
        ) {
            $code = 3;
            throw new Exception('Authentication is invalid');
        }

        // check time
        /*if (time() - $time > 30){
            $code = 4;
            throw new Exception('Time is invalid');
        }*/
        // End of check authenticate

        $login = My_Staff_Login::auth(array(
            'username' => $username,
            'password' => $password,
        ), $system);

        if ($login['code'] != 0){
            $code = $login['code'];
            throw new Exception($login['message']);
        }
        $rs['data'] = $login['data'];
    } catch (Exception $e){
        $rs['code'] = isset($code) ? ($code and $message = $e->getMessage()) : (1 and $message = 'Unknown Error');
        $rs['message'] = $message;
    }
    return $rs;
}

function wsAddNotification($title, $content, $category, $show_to, $pop_up, $type) {
    $content = serialize( $show_to );

    return My_Notification::add($title, $content, $category, $show_to, $pop_up, $type);
}

function wsGetAreaByStaffCode($staff_code) {
    
    $QAsm = new Application_Model_Asm;
    return $QAsm->getAreaByStaffCode($staff_code);
    
}

/**
 * [wsGetAllArea description]
 * @return array('id' => $id, 'name' => $name)
 */
function wsGetAllArea() {
    
    $QArea = new Application_Model_Area();
    return $QArea->get_index_cache();
    
    /*
    $params['from_date'] = '2016-04-01 00:00:00';
    $params['to_date'] = '2016-04-03 23:59:59';

    $QTiming = new Application_Model_Timing();
    return  $QTiming->getSellOut_BI($params['from_date'], $params['to_date']);*/
}

/**
 * [wsGetAllProvince description]
 * @return array('id' => $id, 'name' => $name, 'area_id' => $area_id)
 */
function wsGetAllProvince() {
    $QRegion = new Application_Model_RegionalMarket();
    return $QRegion->get_index_cache();
}

/**
 * [wsGetProvinceByAreaId description]
 * @param  integer $id area_id
 * @return array('id' => $id, 'name' => $name, 'area_id' => $area_id)
 */
function wsGetProvinceByAreaId($id) {
    $QRegion = new Application_Model_RegionalMarket();
    return $QRegion->get_index_by_area_cache($id);
   
}

/**
 * [wsGetDistrictByProvinceId description]
 * @param  integer $id province_id
 * @return array('id' => $id, 'name' => $name, 'parent' => $parent)
 */
function wsGetDistrictByProvinceId($id) {
    $QRegion = new Application_Model_RegionalMarket();
    return $QRegion->get_district_index_by_province_cache($id);
}

/**
 * [wsGetAllJob description]
 * @param  integer $page 
 * @param  integer $limit
 * @param  string $params
 * @return array()
 */
function wsGetAllJob($page , $limit , $params) {

    $QJob = new Application_Model_Job();
    $params = unserialize(base64_decode($params));
    $result = $QJob->fetchAllJob($params);
    
    $QJobCat = new Application_Model_JobCategory();
    $cat = $QJobCat->get_all();
    
    return array(
        'job_list' => $result,
        'job_cat' => $cat
    );
}
/**
 * [wsInsertCV description]
 * @param  string $data province_id
 * @return array()
 */
function wsInsertCV($params ,$params_data_company, $token , $auth)
{
    $QCv = new Application_Model_Cv();
    $QCvCompany = new Application_Model_CvCompanyOld();
    
    $params_data = unserialize(base64_decode($params));
    $params_data_company = unserialize(base64_decode($params_data_company));

    $token_revert = 'cuongdethuong' . $auth;
    $token_revert = md5($token_revert);

    if(!$token || !$auth)
           return array(
            'code' => -1,
            'message' => 'empty token'
            );


    if($token_revert != $token)
      return array(
            'code' => -1,
            'message' => 'invalid token'
        );

    if(!is_array($params_data))
        return array(
                'code' => -1,
                'message' => 'invalid params'
            );


    if(is_array($params_data))
    {

         try{
            $id = $QCv->insert($params_data);
            $params_data_company['cv_id'] = $id;
            foreach($params_data_company['old_name'] as $key=>$value){
                $data_old = array(
                    'name' =>  $value,
                    'position'  =>  $params_data_company['old_position'][$key],
                    'decription'  =>  $params_data_company['old_details'][$key],
                    'time'  =>  $params_data_company['old_time'][$key],
                    'cv_id' => $id
                );
               $id_com = $QCvCompany->insert($data_old);
            }
            return array(
                    'code' =>  0,
                    'message' => 'Done'
            );
         }
         catch(exception $e)
         {
            return array(
                    'code' =>  0,
                    'message' => $e->getMessage()
            );
         }
        
    }

}

function wsGetDetailJob($id)
{
     $QJob = new Application_Model_Job();
     $jobCurrentset = $QJob->find($id);
     $result = $jobCurrentset->current()->toArray();
     
     $QJobRegional = new Application_Model_JobRegional();
     $job_regional = $QJobRegional->get_regional_by_id($id);
     

     return array(
        'job_list' => $result, 
        'job_regional' => $job_regional
     );
}


/**
 * [wsGetDistrictByAreaId description]
 * @param  integer $id province_id
 * @return array('id' => $id, 'name' => $name, 'parent' => $parent)
 */
function wsGetDistrictByAreaId($id) {
    return;
}

/**
 * [wsGetAllDiamondLevel description]
 * @return array(
 *     'id' => $id,
 *     'name' => $name,
 *     'sales_from' => $sales_from,
 *     'sales_to' => $sales_to,
 *     'sales_market_share' => $sales_market_share,
 *     'figure_market_share' => $figure_market_share
 * )
 */
function wsGetAllDiamondLevel()
{
    $QLoyaltyPlan = new Application_Model_LoyaltyPlan();
    return $QLoyaltyPlan->get_all_index_cache();
}

/**
 * $params = array(
 *     'page' => 1,
 *     'limit' => LIMITATION,
 *     'params' => array(
 *         array('name' => 'diamond_level', 'value' => 3),
 *         array('name' => 'diamond_month', 'value' => '01/05/2015'),
 *         array('name' => 'area_id', 'value' => serialize(array(1,2,3,4,5,6,7,10))),
 *         array('name' => 'diamond', 'value' => 1),
 *     ),
 * );
 */
function wsGetStore($page, $limit, $params)
{
    $total = 0;
    $new_params = array();



    foreach ($params as $key => $param)
        $new_params[ $param['name'] ] = is_array(@unserialize($param['value'])) ? unserialize($param['value']) : $param['value'];

    $QStore = new Application_Model_Store();
    $store_list = $QStore->fetchPagination($page, $limit, $total, $new_params);

    return array('list' => $store_list, 'total' => $total);
}

/**
 * @author buu.pham
 * @param  array $data = array(
 *     'distributor_id',
 *     'good_id',
 *     'color_id',
 *     'quantity',
 *     'source', // 1: Center; 2: Warehouse; 3: Warranty
 *     'type', // 1: Import; 2: Export; 3: Adjust
 *     'staff_id', // staff ID ứng với source
 *     'note',
 * )
 * @return void
 */
function wsSetDistributorStockLog($data)
{
    $params = array();

    foreach ($data as $key => $d)
        $params[ $d['name'] ] = is_array(@unserialize($d['value'])) ? unserialize($d['value']) : $d['value'];

    $QStockLog = new Application_Model_DistributorStockLog();

    switch ($data['type']) {
        case My_Sale_Source_Type::IMPORT:
            $QStockLog->import($params);
            break;
        case My_Sale_Source_Type::EXPORT:
            $QStockLog->export($params);
            break;
        case My_Sale_Source_Type::ADJUST:
            $QStockLog->adjust($params);
            break;

        default:
            break;
    }
}

function wsGetStoreRandom($params)
{
    $QStore = new Application_Model_Store();
    $result = $QStore->get_store_random($params);

    return array('return' => $result);
}
function wsGetStoreByLeader($email)
{
    $QStaff = new Application_Model_Staff();
    $QStore = new Application_Model_StoreLeaderLog();
    
    $currentTime = date('Y-m-d H:i:s');
    $where = $QStaff->getAdapter()->quoteInto("email = ?",$email);
    $staff = $QStaff->fetchRow($where);
    $result = $QStore->get_stores_cache($staff->id,$currentTime,$currentTime);

    return array('return' => $result);
}

// NEW FUNCTION PungPond //

function wsGetStoreList($params) {
    $QArea = new Application_Model_Area();
    return  $QArea->get_list_store($params);
}

function wsGetStoreListDid($params) {
    $QStore = new Application_Model_Store();
    return  $QStore->getDitributor($params);
}
function wsStoreDidForCreate($params) {
    $QStore = new Application_Model_Store();
    return  $QStore->getStoreList($params);
}

// ----- Analytics : Start -----
function wsGetSelloutByProduct($params) {
    $QTiming = new Application_Model_Timing();
    return $QTiming->getSellOut_BI($params);
}

function wsGetSellinByProduct($params) {
    $QTiming = new Application_Model_Timing();
    return $QTiming->getSellIn_BI($params);
}

function wsGetAllDistributor($params) {
    $QDistributor = new Application_Model_Distributor();
    return $QDistributor->getAll_Distributor($params);
}
function wsGetAllGood($params) {
    $QGood = new Application_Model_Good();
    return $QGood->getAll_Good($params);
}
function wsGetAllGoodColor($params) {
    $QGoodColor = new Application_Model_GoodColor();
    return $QGoodColor->getAll_GoodColor($params);
}
function wsGetRemainStock($params) {
    $QTiming = new Application_Model_Timing();
    return $QTiming->getRemainStock_BI($params);
}
// ----- Analytics : End -----

function wsStoreListInvastment($page, $limit, $total, $params) {
    $QStore = new Application_Model_Store();
    
    $Store = $QStore->fetchForInvastmentWS($page, $limit, $total, $params);
   $data = Array('result'=>$Store,'total'=>$total);
     return  $data;
   
}
function wsGetSellOut($params) {
    $QTiming = new Application_Model_Timing();
    $DataSellOut = $QTiming->GetSellOutForWebService($params);
   
     return  $DataSellOut ;
   
}
function wsGetShopSellout($params) {
    $QImeiKpi= new Application_Model_ImeiKpi();
    $ImeiKpi = $QImeiKpi->GetShopSelloutForWebService($params);
   
     return  $ImeiKpi ;
   
}

function wsStoreListAll() {
    $QStore = new Application_Model_Store();
    $Store = $QStore->getStoreListAll();
    return  $Store;
   
}
function wsStoreAddress($params) {
    $QStore = new Application_Model_Store();
    $Address = $QStore->getStoreAddress($params);
     return  $Address;
   
}
function wsGetCountAllStoreCli($params) {
    $QStore = new Application_Model_Store();
    $total = $QStore->getStoreAllForUodateTradeCount();
    return  $total;
   
}
function wsGetAllStoreCli($params) {
    $QStore = new Application_Model_Store();
    $store = $QStore->getAll_StoreWsCli($params);
    return  $store;
   
}
function wsGetAllAreaCli($params) {
    $QStore = new Application_Model_Area();
    $store = $QStore->getAll_AreaWsCli($params);
    return  $store;
   
}
function wsGetAllRegionalMarketCli($params) {
    $QRegionalMarket = new Application_Model_RegionalMarket();
    $regionalmarket = $QRegionalMarket->getAll_RegionalMarketWsCli($params);
    return  $regionalmarket;
   
}
function wsStoreMarketType($params) {
    $QStore = new Application_Model_Store();
    $type = $QStore->getStoreMarketType($params);
    // $type = "test";
    
     return  $type;
   
}

function wsUpdateStoreforTerade($params) {
    $QStore = new Application_Model_Store();

    $db = Zend_Registry::get('db');
    $select = $db->select()
            ->from(array('s' => 'store'),
                array('s.*'));

    $select->where('s.id > ?', $params['max_id']);       
    $result = $db->fetchAll($select); 
    
    return  $result;
   
}

function wsGetStoreStaff($params) {
   $db = Zend_Registry::get('db');
        $select = $db->select()
                ->from(array('ss' => HR_DB.'.store_staff'),array('ss.*'));
        $select->join(array('st' => HR_DB.'.staff'), 'st.id=ss.staff_id', array());
        $select->where('st.code = ?', $params);
        $result = $db->fetchAll($select);
        return $result;
   
}

// ----- HR : Start -----

function wsInsertUser($params) {

    $data = array();
    $result = array();

    $db = Zend_Registry::get('db');

    // Check User already exists
    $select = $db->select()
        ->from(array('s' => HR_DB.'.staff'), array('s.id'))
        ->where('s.code = ?', $params['staff_code']);
    $chk_result = $db->fetchRow($select);

    if ( empty($chk_result) ) {

        $QStaff = new Application_Model_Staff();

        // check gender
        switch ($params['prefix']) {
            case "นาย":
                $gender = '1';
                break;
            case "นาง":
                $gender = '0';
                break;
            case "นางสาว":
                $gender = '0';
                break;
            default:
                $gender = '1';
                break;
        }

        $email = $params['staff_code']."@oppo.in.th";

        $dob = date('Y-m-d H:i:s', strtotime($params['birth_date']));
        $start_date = date('Y-m-d H:i:s', strtotime($params['start_date']));

        $arr = array( 
            'code'              => $params['staff_code'],
            'firstname'         => $params['firstname'],
            'lastname'          => $params['lastname'],
            'email'             => $email,
            'password'          => md5($params['staff_code']), // default password : staff code
            // 'department'        => $departmentid,
            // 'team'              => $teamid,
            // 'title'             => $titleid,
            'dob'               => $dob,
            // 'group_id'          => $groupid,
            'company_id'        => '1',
            'joined_at'         => $start_date,
            'gender'            => $gender,
            'phone_number'      => str_replace('-','', $params['phone_number']),
            'regional_market'   => $params['regional_market'],
            'created_by'        => 18,
            'created_at'        => date('Y-m-d H:i:s'),
            'shirt_size'        => $params['shirt_size'],
            'public_id'         => $params['public_id'],
        );

        $insert_result = $QStaff->insert($arr);

        if ( $insert_result ) { $result = array('status' => 1, 'des' => 'Insert User Successfully'); } 
        else { $result = array('status' => 0, 'des' => 'Error!'); }
        
    } else {
        $result = array('status' => 0, 'des' => 'User already exists');
    }

    return $result; 
}

// ----- HR : End -----

function wsGetStoreByAreaSellOut($params) {
    $QStore = new Application_Model_Store();
    $DataSellOut = $QStore->getStoreByAreaSellOutForWebService($params);
  
     return  $DataSellOut ;
   
}

function wsGetGrandArea($params) {
     $db = Zend_Registry::get('db');

    // Check User already exists
    $select = $db->select()
        ->from(array('g' => 'grand_area'), array('g.name'))
        ->join(array('a' => 'grand_area_list'),'g.id = a.grand_area_id',array('a.area'));
    $grand = $db->fetchAll($select);
     return  $grand ;
   
}

// Create HR User
function wsCreateUserHr($staff_code) {
    $QStaff = new Application_Model_Staff();
    $Staff = $QStaff->getStaffCreateHr($staff_code);
    return $Staff;
}

// Get Area Leader By Staff Code
function wsGetLeaderByArea($staff_code) {
    $QAsm = new Application_Model_Asm();
    $Leader = $QAsm->getLeaderByStaffCode($staff_code);
    return $Leader;
}

// Get Store By Staff Code
function wsGetStoreByStrId($str_id) {
    $QStore = new Application_Model_Store();
    $Store = $QStore->getStoreById($str_id);
    return $Store;
}