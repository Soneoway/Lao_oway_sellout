<?php
/**
 * Created by PhpStorm.
 * User: hac
 * Date: 4/6/2015
 * Time: 4:25 PM
 */
class StaffPrintLogController extends My_Controller_Action{
    public function indexAction(){

    }

    public  function updateStaffPrintLogAction(){
        exit;
        $db             = Zend_Registry::get('db');
        $QStaffPrintLog = new Application_Model_StaffPrintLog();
        $userStorage    = Zend_Auth::getInstance()->getStorage()->read();
        $ip             = $this->getRequest()->getServer('REMOTE_ADDR');
        $select         = $db->select()
            ->from(array('p'=>'staff'),array('id','contract_term','contract_signed_at','contract_expired_at','title'))
            ->where('contract_term IN (1,6)')
        ;

        $result = $db->query($select);
        $db->beginTransaction();
        try{
            $selectError = $db->select()
                ->from(array('staff_print_log'),array('object'))
                ->where('from_date IS NULL')
                ->orWhere('to_date IS NULL')
                ->orWhere("from_date = '0000:00:00 00:00:00' OR to_date = '0000:00:00 00:00:00'")
                ->group('object')
            ;
            $resultError = $db->fetchAll($selectError);
            $arrError = array();
            foreach($resultError as $value){
                $arrError[] = $value['object'];
            }

            foreach($result as $key => $item){
                $selectLog = $db->select()
                    ->from(array('l'=>'staff_print_log'),array('l.*'))
                    ->where('l.object = ?',$item['id'])
                ;
                $rows = $db->fetchAll($selectLog);
                if(!$rows){
                    if($item['contract_term'] == 3){
                        $data = array(
                            'contract_term' => $item['contract_term'],
                            'time'          => date('Y-m-d H:i:s'),
                            'ip_address'    => $ip,
                            'user_id'       => $userStorage->id,
                            'log_type'      => 2,
                            'from_date'     => $item['contract_signed_at'],
                            'to_date'       => $item['contract_expired_at'],
                            'object'        => $item['id'],
                            'title'         => $item['title']
                        );
                        $QStaffPrintLog->insert($data);

                        //update 12 thang
                        $data = array(
                            'contract_term' => 1,
                            'time'          => date('Y-m-d H:i:s'),
                            'ip_address'    => $ip,
                            'user_id'       => $userStorage->id,
                            'log_type'      => 2,
                            'from_date'     => date('Y-m-d',strtotime($item['contract_signed_at'].' -1 Year')),
                            'to_date'       => $item['contract_signed_at'],
                            'object'        => $item['id'],
                            'title'         => $item['title']
                        );
                        $QStaffPrintLog->insert($data);
                    }
                    if($item['contract_term']== 1){
                        $data = array(
                            'contract_term' => $item['contract_term'],
                            'time'          => date('Y-m-d H:i:s'),
                            'ip_address'    => $ip,
                            'user_id'       => $userStorage->id,
                            'log_type'      => 2,
                            'from_date'     => $item['contract_signed_at'],
                            'to_date'       => $item['contract_expired_at'],
                            'object'        => $item['id'],
                            'title'         => $item['title']
                        );
                        $QStaffPrintLog->insert($data);
                    }
                }

            }

            if(in_array($item['id'],$arrError)){
                if($item['contract_term'] == 3){
                    $data = array(
                        'contract_term' => $item['contract_term'],
                        'time'          => date('Y-m-d H:i:s'),
                        'ip_address'    => $ip,
                        'user_id'       => $userStorage->id,
                        'log_type'      => 2,
                        'from_date'     => $item['contract_signed_at'],
                        'to_date'       => $item['contract_expired_at'],
                        'object'        => $item['id'],
                        'title'         => $item['title']
                    );
                    $QStaffPrintLog->insert($data);

                    //update 12 thang
                    $data = array(
                        'contract_term' => 1,
                        'time'          => date('Y-m-d H:i:s'),
                        'ip_address'    => $ip,
                        'user_id'       => $userStorage->id,
                        'log_type'      => 2,
                        'from_date'     => date('Y-m-d',strtotime($item['contract_signed_at'].' -1 Year')),
                        'to_date'       => $item['contract_signed_at'],
                        'object'        => $item['id'],
                        'title'         => $item['title']
                    );
                    $QStaffPrintLog->insert($data);
                }
                if($item['contract_term']== 1){
                    $data = array(
                        'contract_term' => $item['contract_term'],
                        'time'          => date('Y-m-d H:i:s'),
                        'ip_address'    => $ip,
                        'user_id'       => $userStorage->id,
                        'log_type'      => 2,
                        'from_date'     => $item['contract_signed_at'],
                        'to_date'       => $item['contract_expired_at'],
                        'object'        => $item['id'],
                        'title'         => $item['title']
                    );
                    $QStaffPrintLog->insert($data);
                }
            }

            $db->commit();
        }catch(Exception $e){
            $db->rollBack();
            echo $e->getMessage();
            exit;
        }
        echo 'Done!';
        exit;
    }

    public function updateContractAction(){
        $db             = Zend_Registry::get('db');
        $QStaffPrintLog = new Application_Model_StaffPrintLog();
        $userStorage    = Zend_Auth::getInstance()->getStorage()->read();
        $ip             = $this->getRequest()->getServer('REMOTE_ADDR');
        $select         = $db->select()
            ->from(array('p'=>'staff'),array('id','contract_term','contract_signed_at','contract_expired_at','title'))
            ->where('contract_term IN (1,6)')
        ;

        $result = $db->query($select);
        $db->beginTransaction();
        try{
            foreach($result as $key => $item){
                $selectLog = $db->select()
                    ->from(array('l'=>'staff_print_log'),array('l.*'))
                    ->where('l.object = ?',$item['id'])
                    ->where('l.contract_term = 1')
                    ->where('l.log_type = 2')
                    ->where('l.from_date IS NOT NULL')
                    ->where('l.to_date IS NOT NULL')
                ;
                $row1 = $db->fetchRow($selectLog);
                if(!$row1){
                    $from = '';
                    $to   = '';
                    if($item['contract_term'] == 1){
                        $from = $item['contract_signed_at'];
                        $to   = $item['contract_expired_at'];
                    }else{
                        $from = date('Y-m-d',strtotime($item['contract_signed_at'].' -1 Year'));
                        $to   = $item['contract_signed_at'];
                    }
                    $data = array(
                        'contract_term' => 1,
                        'time'          => date('Y-m-d H:i:s'),
                        'ip_address'    => $ip,
                        'user_id'       => $userStorage->id,
                        'log_type'      => 2,
                        'from_date'     => $from,
                        'to_date'       => $to,
                        'object'        => $item['id'],
                        'title'         => $item['title']
                    );
                    $QStaffPrintLog->insert($data);
                }

                if($item['contract_term'] == 1){
                    continue;
                }    

                $selectLog = $db->select()
                    ->from(array('l'=>'staff_print_log'),array('l.*'))
                    ->where('l.object = ?',$item['id'])
                    ->where('l.contract_term = 6')
                    ->where('l.log_type = 2')
                    ->where('l.from_date IS NOT NULL')
                    ->where('l.to_date IS NOT NULL')
                ;
                $row2 = $db->fetchRow($selectLog);
                if(!$row2){
                    $from = $item['contract_signed_at'];
                    $to   = $item['contract_expired_at'];
                    $data = array(
                        'contract_term' => 6,
                        'time'          => date('Y-m-d H:i:s'),
                        'ip_address'    => $ip,
                        'user_id'       => $userStorage->id,
                        'log_type'      => 2,
                        'from_date'     => $from,
                        'to_date'       => $to,
                        'object'        => $item['id'],
                        'title'         => $item['title']
                    );
                    $QStaffPrintLog->insert($data);
                }
                
            }

            $db->commit();
        }catch(Exception $e){
            $db->rollBack();
            echo $e->getMessage();
            exit;
        }
        echo 'Done!';
        exit;
    }
}