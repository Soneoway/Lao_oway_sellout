<?php
$userStorage = Zend_Auth::getInstance()->getStorage()->read();
$id = $this->getRequest()->getParam('id');
$Timing = new Application_Model_Timing();
$timing = $Timing->find($id);
$timing = $timing->current();
if ($timing) {

    $flashMessenger = $this->_helper->flashMessenger;
    $back_url = $this->getRequest()->getServer('HTTP_REFERER');

    if ($userStorage->id != $timing->staff_id and $userStorage->group_id != ADMINISTRATOR_ID) {
        $QStoreStaffLog = new Application_Model_StoreStaffLog();
        $QLeaderLog = new Application_Model_StoreLeaderLog();

        if ( ! $QStoreStaffLog->belong_to($userStorage->id, $timing['store'], $timing['from'], true) ) {
            // check có phải là quản lý của store
            // tại ngày chấm công của timing
            // 
            if ($userStorage->group_id == SALES_ID)
                $salesTeamErr = 'Bạn không phải người quản lý của Store!';
            elseif ( $userStorage->group_id == LEADER_ID 
                    && $QLeaderLog->is_leader( $userStorage->id, 
                                                            $timing['store'], 
                                                            $timing['from'] ) )
                $salesTeamErr = 'Bạn không phải người quản lý của Store. Bạn chỉ được xem, không được xóa.';
            else
                $salesTeamErr = 'Bạn không phải người quản lý của Store.';


            $flashMessenger->setNamespace('error')->addMessage($salesTeamErr);
            $back_url = $this->getRequest()->getServer('HTTP_REFERER');
            $this->_redirect(($back_url ? $back_url : '/timing'));
        }
    }
    
    // chặn xóa khi đã approve
    if ($timing['approved_at']){
        $flashMessenger->setNamespace('error')->addMessage('Báo cáo này đã được xác nhận, bạn không thể xóa!');
        $this->_redirect(($back_url ? $back_url : '/timing'));
    }

    if ( strtotime(date('Y-m-d')) - strtotime(date('Y-m-d', strtotime($timing->from))) > TIME_LIMIT_DEL_TIMING &&  !in_array($userStorage->group_id, array(SUPERADMIN_ID,ADMINISTRATOR_ID)) ) {

        $day_limit = ceil( TIME_LIMIT_DEL_TIMING/(24*60*60) );
        $flashMessenger->setNamespace('error')->addMessage('Bạn chỉ được xóa chấm công của hôm nay và '.$day_limit.' ngày trước!');
    } else {

        try{

            $TimingSale = new Application_Model_TimingSale();
            $where = $TimingSale->getAdapter()->quoteInto('timing_id = ?', $id);

            $del_timing_sales = $TimingSale->fetchAll($where);

            if ($del_timing_sales){
                $old_date = explode(' ', $timing->from);
                $old_date = $old_date[0];
                $old_month = substr($old_date, 0, -3);

                //delete unused timing sales
                $TimingSale->delete($where);

                //delete photo folder
                foreach ( $del_timing_sales as $item ){
                    $un_timing_sales_id = $item->id;
                    //unlink folder
                    try {
                        $dirPath = APPLICATION_PATH.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'photo'.DIRECTORY_SEPARATOR.'timing_sales_new'.DIRECTORY_SEPARATOR. $old_month .DIRECTORY_SEPARATOR. $old_date .DIRECTORY_SEPARATOR. $un_timing_sales_id . DIRECTORY_SEPARATOR ;
                        foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dirPath, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST) as $path) {
                            $path->isFile() ? @unlink($path->getPathname()) : @rmdir($path->getPathname());
                        }
                        @rmdir($dirPath);
                    } catch (Exception $e){}
                }
            }

            $where = $Timing->getAdapter()->quoteInto('id = ?', $id);
            $Timing->delete($where);

        } catch (Exception $e){

        }

        $QLog = new Application_Model_Log();
        $ip = $this->getRequest()->getServer('REMOTE_ADDR');
        $info = "TIMING - Delete (".$id.")";
        //todo log
        $QLog->insert( array (
            'info' => $info,
            'user_id' => $userStorage->id,
            'ip_address' => $ip,
            'time' => date('Y-m-d H:i:s'),
        ) );
    }
}


$back_url = $this->getRequest()->getServer('HTTP_REFERER');

$this->_redirect(($back_url ? $back_url : '/timing'));