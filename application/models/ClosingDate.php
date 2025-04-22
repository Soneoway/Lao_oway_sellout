<?php
class Application_Model_ClosingDate extends Zend_Db_Table_Abstract
{
    protected $_name = 'closing_date';

    function get_cache(){
        $cache      = Zend_Registry::get('cache');
        $result     = $cache->load($this->_name.'_cache');

        if ($result === false) {
            $db = Zend_Registry::get('db');
            $select = $db->select()
                ->from(
                    array('p' => $this->_name), 
                    array(
                        'max_closing_date' => new Zend_Db_Expr("MAX(p.closing_date)"),
                        'type' => 'p.type',
                    )
                )
                ->group('p.type');

            $data = $db->fetchAll($select);
            $result = array();

            if ($data)
                foreach ($data as $_key => $_item)
                    $result[ $_item['type'] ] = $_item['max_closing_date'];

            $cache->save($result, $this->_name.'_cache', array(), null);
        }
        return $result;
    }
}                                                      
