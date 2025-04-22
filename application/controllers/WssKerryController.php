<?php
class WssKerryController extends My_Controller_Action {

    public function testAction() {

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

/*
        $arr = array(
            'req' => array(
                'status' => array(
                    'con_no'        => '66279',
                    'status_code'   => '010',
                    'status_desc'   => 'Shipment picked-up',
                    'status_date'   => '2014-06-09 15:00:00',
                    'update_date'   => '2014-06-09 15:07:35',
                    'ref_no'        => 'REF-3359000187',
                    'location'      => 'Bangkok'
                    )
                )
            );
*/
        $arr = array(
            'req' => array(
                'status' => array(
                    'con_no'        => '66279',
                    'status_code'   => 'POD',
                    'status_desc'   => 'Delivery successfully',
                    'status_date'   => '2014-06-09 23:00:00',
                    'update_date'   => '2014-06-09 23:55:00',
                    'ref_no'        => 'SO590819-00592',
                    'location'      => 'Bangkok'
                    )
                )
            );

        //echo json_encode($arr); die;
        $data = json_encode($arr);
        //print_r($data);

        $url = HOST.'wss-kerry/shipmentupdate';

        $ch = curl_init( $url );
        curl_setopt( $ch, CURLOPT_POST, 1);
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt( $ch, CURLOPT_HEADER, 0);
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec( $ch );
        print_r($response);

/*
        //$url = 'http://202.183.215.38/ediwebapi/SmartEDI/shipment_info';
        $url = 'catty.local/wss-kerry/test2';
        $app_id = 'OPPO';
        $app_key = 'e2ac7ffe-1541-4884-b3e0-f73efa976b39';
        $content_type = 'application/json';

        $data = 'app_id=' . $app_id . '&app_key=' . $app_key . '&Content-Type=' . $content_type;

        $ch = curl_init( $url );
        curl_setopt( $ch, CURLOPT_POST, 1);
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt( $ch, CURLOPT_HEADER, 0);
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec( $ch );
        print_r($response);
*/
    }

    public function shipmentupdateAction() {

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        //print_r($data);

        $db = Zend_Registry::get('db');
        $db->beginTransaction();

        $QKSSL = new Application_Model_KerryShipmentStatusLog();
        $QDO = new Application_Model_DeliveryOrder();

        try {

            $tmp = array_keys($_POST);
            $data = json_decode($tmp[0],true);
            /*
print_r($_POST);
            $tmp = $_POST['data'];
print_r($tmp);
            $data = json_decode($tmp,true);
print_r($data);
            //print_r($data); die;*/

            // insert Log of Shipment Status
            $shipment_log = array(
                'tracking_no'           => $data['req']['status']['con_no'],
                'shipment_status_id'    => $data['req']['status']['status_code'],
                'shipment_status_date'  => $data['req']['status']['status_date'],
                'sn_ref'                => $data['req']['status']['ref_no']
            );

            $QKSSL->insert($shipment_log);

            // update Status of Delivery Order
            $DO_update = array(
                'real_receiver'     => $data['req']['status']['status_code'],
                'real_receive_time' => $data['req']['status']['status_date']
            );

            $DO_where = $QDO->getAdapter()->quoteInto('tracking_no = ?', $data['req']['status']['con_no']);

            $QDO->update($DO_update, $DO_where);

            $result = array(
                'res' => array(
                    'status' => array(
                        'status_code'   => '000',
                        'status_desc'   => 'Success Requisition'
                        )
                    )
                );

            // insert Log of Imei Receive
            if ($data['req']['status']['status_code'] == "POD") {
                $QIDR = new Application_Model_ImeiDistributorReceive();

                $imei_list = $QIDR->getImeiBySN($data['req']['status']['ref_no']);


                for ($i=0;$i<count($imei_list);$i++) {

                    $imei_log = array(
                        'imei_sn'               => $imei_list[$i]['imei_sn'],
                        'tracking_no'           => $data['req']['status']['con_no'],
                        'shipment_status_id'    => $data['req']['status']['status_code'],
                        'shipment_status_date'  => $data['req']['status']['status_date'],
                        'sn_ref'                => $data['req']['status']['ref_no']
                    );

                    //print_r($imei_log); echo $i; echo "<br/>"; 
                    $QIDR->insert($imei_log);

                }
                
            }

            $db->commit();

        } catch (Exception $e) {
            
            $db->rollBack();
            //echo "Fail! : ".$e;

            $result = array(
                'res' => array(
                    'status' => array(
                        'status_code'   => '999',
                        'status_desc'   => 'Unsuccessful Requisition / Undefined error exception, return windows exception message'
                        )
                    )
                );

        }

        //print_r($result);
        print_r(json_encode($result));
    }


}