<?php
$id = $this->getRequest()->getParam('id');
$note = $this->getRequest()->getParam('note');
$new = $note;

if ($id) {
    $QTiming = new Application_Model_Timing();
    $timingRowset = $QTiming->find($id);
    $timing = $timingRowset->current();

    if ($timing) {
        // check có phải là quản lý của store
        // tại ngày chấm công của timing
        $userStorage = Zend_Auth::getInstance()->getStorage()->read();
        $QStoreStaffLog = new Application_Model_StoreStaffLog();

     //   if ( $userStorage->id != $timing['staff_id'] && ! $QStoreStaffLog->belong_to($userStorage->id, $timing['store'], $timing['from'], true) ) {
//            echo '4';
//            exit;
//        }

        if ($note) {
            $userStorage = Zend_Auth::getInstance()->getStorage()->read();

            $note = $timing['note'] . "\n" . "* " . $userStorage->lastname . " [" . date('Y-m-d H:i:s') . "] " . $note;

            $data = array(
                'note' => $note,
                'note_updated' => 1,
                'note_read' => 0,
            );

            $where = $QTiming->getAdapter()->quoteInto('id = ?', $id);

            try {
                $QTiming->update($data, $where);

                $QLog = new Application_Model_Log();
                $ip = $this->getRequest()->getServer('REMOTE_ADDR');
                $info = "TIMING NOTE - Update (".$id.") - Add Content (".$new.")";
                //todo log
                $QLog->insert( array (
                    'info' => $info,
                    'user_id' => $userStorage->id,
                    'ip_address' => $ip,
                    'time' => date('Y-m-d H:i:s'),
                ) );

                echo '1';
                exit;
            } catch (Exception $e){
                echo '0';
                exit;
            }
        } else {
            $data = array(
                'note_updated' => 0,
                'note_read' => 1,
            );

            $where = $QTiming->getAdapter()->quoteInto('id = ?', $id);

            try {
                $QTiming->update($data, $where);
                echo '1';
                exit;
            } catch (Exception $e){
                echo '0';
                exit;
            }
        }
    }
}

echo '0';
exit;