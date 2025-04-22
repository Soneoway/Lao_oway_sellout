
<?php
class Application_Model_OppoStores extends Zend_Db_Table_Abstract
{
    protected $_name = 'store';
    protected $_schema = HROPPO_DB;

 function get_cache(){
        $cache      = Zend_Registry::get('cache');
        $result     = $cache->load($this->_name.'_cache');

        if ($result === false) {

            $where = array();
            //$where[] = $this->getAdapter()->quoteInto('activated_status =?',0);
            $data = $this->fetchAll();

            $result = array();
            if ($data){
                foreach ($data as $item){
                    $result[$item->id] = $item->name;
                }
            }
            $cache->save($result, $this->_name.'_cache', array(), null);
        }
        return $result;
    }

  function get_warehouse(){
        $db = Zend_Registry::get('db');

        $userStorage = Zend_Auth::getInstance()->getStorage()->read();

        $select = $db->select()
            ->from(array('w' => WAREHOUSE_DB.'.warehouse'),array('w.*'));
        $select->where('w.activated_status = 0');

        $resualt = $db->fetchAll($select);
        return $resualt;
    }

  function get_warehouse_staff($user_id){
      $db = Zend_Registry::get('db');

        $select = $db->select()
            ->from(array('w' => WAREHOUSE_DB.'.warehouse'),array('w.*'));
        $select->joinleft(array('s' => 'staff'),'s.warehouse_id = w.id',array());
        $select->where('w.activated_status = 0 ');
        $select->where('s.id =?', $user_id);

        $resualt = $db->fetchAll($select);
        return $resualt;
    }

    function get_whstaff($id){
      $db = Zend_Registry::get('db');

        $select = $db->select()
            ->from(array('ws' => 'warehouse_staff'),array('whs_id' => 'ws.warehouse_id'));
        $select->where('ws.staff_id =?', $id);

        $resualt = $db->fetchAll($select);
        return $resualt;
    }

}
