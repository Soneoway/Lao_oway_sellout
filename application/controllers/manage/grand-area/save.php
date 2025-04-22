<?php

    $id                 = $this->getRequest()->getParam('id');
    $grand_name         = $this->getRequest()->getParam('grand_name');
    $area               = $this->getRequest()->getParam('area');
    $area               = is_array($area) ? array_unique( array_filter( $area ) ) : array();

    $userStorage = Zend_Auth::getInstance()->getStorage()->read();
    $QGrandArea         = new Application_Model_GrandArea();
    $QGrandAreaList     = new Application_Model_GrandAreaList();
$db = Zend_Registry::get('db');
$db->beginTransaction();
try { 
if (!$id) {

    if (!$grand_name || !$area) {
       $flashMessenger = $this->_helper->flashMessenger;
       $this->view->messages = $flashMessenger->setNamespace('error')->addMessage('Please insret Data');
    }else{

        $dataA = array(
            'name'          => $grand_name,
            'created_at'    => date('Y-m-d H:i:s'),
            'created_by'    => $userStorage->id
            );
       $id = $QGrandArea->insert($dataA);

    if (isset($area) && $area) {
        foreach ($area as $value) {
             $All_area = $QGrandAreaList->fetchAreaAll($value);
                foreach ($All_area as $x => $val) {
                    $where = $QGrandAreaList->getAdapter()->quoteInto('id = ?', $val['id']);
                    $QGrandAreaList->delete($where);
                }
            $data = array(
                'grand_area_id' => $id,
                'area'  => $value,
                );

            $QGrandAreaList->insert($data);
        }
    }
    }
    


    $flashMessenger = $this->_helper->flashMessenger;
    $this->view->messages = $flashMessenger->setNamespace('success')->addMessage('Success');
    $db->commit();
    $this->_redirect(HOST.'manage/grand-area');
} else {
    $grand_area = $QGrandArea->getGrandArealist($id);
// echo "<pre>";
// print_r($grand_area);
        $area_old = array();
        foreach ($grand_area as $key => $value) {
            $area_old[] = $value['area'];
        }

        $grand_name_old = $grand_area[0]['name'];
        
        if ( $grand_name != $grand_name_old ) {
            $data = array(
                    'updated_at'    => date('Y-m-d H:i:s'),
                    'updated_by'    => $userStorage->id,
                    'name'          => $grand_name,
            );
                $where = $QGrandArea->getAdapter()->quoteInto('id = ?', $grand_area[0]['grand_id']);
                $QGrandArea->update($data,$where);
        }
    
        foreach ($area as $k => $val) {
            if (!in_array($val,$area_old)) {

                $All_area = $QGrandAreaList->fetchAreaAll($val);
                foreach ($All_area as $x => $data) {
                    $where = $QGrandAreaList->getAdapter()->quoteInto('id = ?', $data['id']);
                    $QGrandAreaList->delete($where);
                }
                print_r($All_area);
                
                $data = array(
                    'grand_area_id' => $id,
                    'area'          => $val,
                );
                $dataA = array(
                    'updated_at'    => date('Y-m-d H:i:s'),
                    'updated_by'    => $userStorage->id,
            );
                $where = $QGrandArea->getAdapter()->quoteInto('id = ?', $grand_area[0]['grand_id']);

                $QGrandArea->update($dataA,$where);
                $QGrandAreaList->insert($data);
            }else{
                
               
               
            }
        }
        // die;
        $diff = array_diff($area_old,$area);
        foreach ($diff as $k => $val) {
                $where = array();
                $where[] = $QGrandAreaList->getAdapter()->quoteInto('grand_area_id = ?', $id);
                $where[] = $QGrandAreaList->getAdapter()->quoteInto('area = ?', $val);
                $QGrandAreaList->delete($where );
        }

    
    $flashMessenger = $this->_helper->flashMessenger;
    $this->view->messages = $flashMessenger->setNamespace('success')->addMessage('Success');

   
    $db->commit();
    $this->_redirect(HOST.'manage/grand-area');

}
} catch (Exception $e) {
    $db->rollBack();
    echo "Fail! : ".$e;
    $flashMessenger->setNamespace('error')->addMessage($e->getMessage());
    //exit;
}

