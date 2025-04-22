<?php
$staff = $this->getRequest()->getParam('staff');
    //$staff_id = is_array($staff_id) ? array_unique( array_filter( $staff_id ) ) : array();
$store = $this->getRequest()->getParam('store');
    // $store_id = is_array($store_id) ? array_unique( array_filter( $store_id ) ) : array();
$leader = $this->getRequest()->getParam('leader');
    // $leader_id = is_array($leader_id) ? array_unique( array_filter( $leader_id ) ) : array();
$joinat_st = $this->getRequest()->getParam('joinat');

if($staff){$staff_id = explode("\r\n", $staff);}  
if($store){$store_id = explode("\r\n", $store);}  
if($leader){$leader_id = explode("\r\n", $leader);}  

$joinat = strtotime($joinat_st);

$flashMessenger = $this->_helper->flashMessenger;

    $QStoreStaff = new Application_Model_StoreStaff();
    $QStoreStaffLog = new Application_Model_StoreStaffLog();

        if (isset($staff_id) && $staff_id) {
        for ($i=0;$i<count($staff_id);$i++) {
                $data = array(
                    'staff_id' => $staff_id[$i],
                    'store_id'  => $store_id[$i],
                    'is_leader'  => $leader_id[$i],
                    );

                $QStoreStaff->insert($data);
            }
        }

        if (isset($staff_id) && $staff_id) {
        for ($i=0;$i<count($staff_id);$i++) {
                $data = array(
                    'staff_id' => $staff_id[$i],
                    'store_id'  => $store_id[$i],
                    'is_leader'  => $leader_id[$i],
                    'joined_at'  => $joinat,
                    );

                $QStoreStaffLog->insert($data);
            }
        }

    
    $flashMessenger->setNamespace('success')->addMessage('Done');

$this->_redirect(HOST.'manage/store-staff');