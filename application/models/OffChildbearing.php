<?php
class Application_Model_OffChildbearing extends Zend_Db_Table_Abstract
{
	protected $_name = 'off_childbearing';
    public function save($data = array(),$id = NULL){
        $db = Zend_Registry::get('db');
        $result  = array();
        $db->beginTransaction();
        try{
            if($id){
                $where = $this->getAdapter()->quoteInto('id = ?',$id);
                $this->update($data,$where);
            }else{
                $this->insert($data);
            }
            $result = array('code'=>0);
            $db->commit();
        }catch (Exception $e){
            $db->rollBack();
            $result = array('code'=>1,'message' => $e->getMessage());
        }
        return $result;
    }
}