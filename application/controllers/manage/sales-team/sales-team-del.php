<?php
$id = $this->getRequest()->getParam('id');

$data = array('del'=> 1);

$QSalesTeam = new Application_Model_SalesTeam();

$where = $QSalesTeam->getAdapter()->quoteInto('id = ?', $id);

//update sales team
$QSalesTeam->update($data, $where);

$QSalesTeamStaff = new Application_Model_SalesTeamStaff();

$where = $QSalesTeamStaff->getAdapter()->quoteInto('sales_team_id = ?', $id);

$QSalesTeamStaff->delete($where);

$QSalesTeamStaffLog = new Application_Model_SalesTeamStaffLog();
$where = array();
$where[] = $QSalesTeamStaffLog->getAdapter()->quoteInto('sales_team_id = ?', $id);
$where[] = $QSalesTeamStaffLog->getAdapter()->quoteInto('released_at IS NULL', null);

$QSalesTeamStaffLog->update(array( 'released_at' => date('Y-m-d H:i:s') ), $where);

$QLog = new Application_Model_Log();
$userStorage = Zend_Auth::getInstance()->getStorage()->read();
$ip = $this->getRequest()->getServer('REMOTE_ADDR');
//todo log
$info = "SALES TEAM Delete (".$id.")";
$QLog->insert( array (
    'info' => $info,
    'user_id' => $userStorage->id,
    'ip_address' => $ip,
    'time' => date('Y-m-d H:i:s'),
) );

//remove cache
$cache = Zend_Registry::get('cache');
$cache->remove('sales_team_cache');

$cache->remove('sales_team_staff_sale_cache');

$this->_redirect('/manage/sales-team');