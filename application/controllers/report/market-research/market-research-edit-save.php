<?php
$this->_helper->layout->disableLayout();
$this->_helper->viewRenderer->setNoRender(true);
$userStorage = Zend_Auth::getInstance()->getStorage()->read();
$QMarketResearch = new Application_Model_MarketResearch();

if ($this->getRequest()->getMethod() == 'POST'){
    $date      = $this->getRequest()->getParam('date');
    $store_code    = $this->getRequest()->getParam('store_code');
    $store_name    = $this->getRequest()->getParam('store_name');

    $st_status     = $this->getRequest()->getParam('st_status'); 
    $st_type       = $this->getRequest()->getParam('st_type');

    $b_oppo       = $this->getRequest()->getParam('b_oppo'); 
    $b_vivo       = $this->getRequest()->getParam('b_vivo'); 
    $b_samsung    = $this->getRequest()->getParam('b_samsung'); 
    $b_huawei     = $this->getRequest()->getParam('b_huawei'); 
    $b_realme     = $this->getRequest()->getParam('b_realme'); 
    $b_infinix    = $this->getRequest()->getParam('b_infinix'); 
    $b_honor      = $this->getRequest()->getParam('b_honor'); 
    $b_tecno      = $this->getRequest()->getParam('b_tecno');

    $pc_oppo      = $this->getRequest()->getParam('pc_oppo'); 
    $pc_vivo      = $this->getRequest()->getParam('pc_vivo'); 
    $pc_samsung   = $this->getRequest()->getParam('pc_samsung'); 

    $ss_xiaomi     = $this->getRequest()->getParam('ss_xiaomi'); 
    $ss_honor      = $this->getRequest()->getParam('ss_honor'); 
    $ss_tecno      = $this->getRequest()->getParam('ss_tecno'); 

    // Prepare the data for updating or inserting
    $data = array(
        'store_name'          => $store_name,
        'st_status'     => $st_status, 
        'st_type'     => $st_type, 
        'b_oppo'     => $b_oppo, 
        'b_vivo'     => $b_vivo, 
        'b_samsung'     => $b_samsung, 
        'b_huawei'     => $b_huawei, 
        'b_realme'     => $b_realme, 
        'b_infinix'     => $b_infinix, 
        'b_honor'     => $b_honor, 
        'b_tecno'     => $b_tecno, 
        'pc_oppo'     => $pc_oppo, 
        'pc_vivo'     => $pc_vivo, 
        'pc_samsung'     => $pc_samsung, 
        'ss_xiaomi'     => $ss_xiaomi, 
        'ss_honor'     => $ss_honor, 
        'ss_tecno'     => $ss_tecno, 
        'updated_by'        => $userStorage->id,
        'updated_at'        => date('Y-m-d H-i-s'),
    );

    // Check if the record exists
    $select = $QMarketResearch->select()
                             ->where('date = ?', $date)
                             ->where('store_code = ?', $store_code);

    // Execute the select query and check if record exists
    $existingRecord = $QMarketResearch->fetchRow($select);

    if ($existingRecord) {
        // Record exists, perform update
        $where = array(
            'date = ?' => $date,
            'store_code = ?' => $store_code
        );
        // Perform the update
        $result = $QMarketResearch->update($data, $where);
        $flashMessenger = $this->_helper->flashMessenger;
        $flashMessenger->setNamespace('success')->addMessage('ອัปเดตข้อมูลสำเร็จ');
    } else {
        // Record does not exist, perform insert
        $data['store_code'] = $store_code; // Add store_code to data for insert
        $result = $QMarketResearch->insert($data);
        $flashMessenger = $this->_helper->flashMessenger;
        $flashMessenger->setNamespace('success')->addMessage('บันทึกข้อมูลสำเร็จ');
    }
}

$this->_redirect(HOST.'report/market-research');
