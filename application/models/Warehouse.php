
<?php
class Application_Model_Warehouse extends Zend_Db_Table_Abstract
{
    protected $_name = 'warehouse';
    protected $_schema = WAREHOUSE_DB;

 function get_cache(){
        $cache      = Zend_Registry::get('cache');
        $result     = $cache->load($this->_name.'_cache');

        if ($result === false) {

            $where = array();
            $where[] = $this->getAdapter()->quoteInto('activated_status =?',0);
            $data = $this->fetchAll($where);

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

    function get_cache2(){
        $cache      = Zend_Registry::get('cache');

        if (!$result) {
            
            $where = array();
            $where[] = $this->getAdapter()->quoteInto('company_id = ?', 1);
            $where[] = $this->getAdapter()->quoteInto('activated_status = ?', 0);

            $data = $this->fetchAll($where);

            $result = array();
            if ($data){
                foreach ($data as $item){
                    $result[$item->id] = $item;
                    $result[$item->id]->name = "".$item->name."";
                }
            }
        }
        return $result;
    }

    function get_warehouse(){
        $db = Zend_Registry::get('db');

        $select = $db->select()
            ->from(array('w' => WAREHOUSE_DB.'.warehouse'),array('w.*'));
        $select->where('w.activated_status = 0 ');

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

}
