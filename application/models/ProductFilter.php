<?php
class Application_Model_ProductFilter extends Zend_Db_Table_Abstract
{
	protected $_name = 'product_filter';

	function get_cache() {

        $cache = Zend_Registry::get('cache');
        //$result = $cache->load(WAREHOUSE_DB.'_'.$this->_name.'_cache');

        if (!$result) {
            
            $db = Zend_Registry::get('db');

            $get = array(
            	'id'   => new Zend_Db_Expr("(CASE WHEN pf.product_id = 0 THEN '0' ELSE g.id END)"),
            	'name' => new Zend_Db_Expr("(CASE WHEN pf.product_id = 0 THEN 'Others' ELSE g.name END)"),
            	'desc' => new Zend_Db_Expr("(CASE WHEN pf.product_id = 0 THEN '' ELSE g.desc END)"),
            );

	        $select = $db->select()
	            ->from(array('pf' => 'product_filter'), $get)
	            ->joinLeft(array('g' => WAREHOUSE_DB.'.good'), 'pf.product_id = g.id', array())
	            ->order('pf.position_no ASC');

	        $data = $db->fetchAll($select);

            // print_r($data);

            $result = array();

            if ($data) {
                foreach ($data as $item){
                    $result[$item['id']] = $item;
                    $result[$item['id']]['name'] = "[".$item['name']."] ".$item['desc'];
                }
            }

            // $cache->save($result, WAREHOUSE_DB.'_'.$this->_name.'_cache2', array(), null);
        }
        return $result;
    }

}
