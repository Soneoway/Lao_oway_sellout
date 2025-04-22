<?php
class Application_Model_Brand extends Zend_Db_Table_Abstract
{
	protected $_name = 'brand';
    protected $_schema = WAREHOUSE_DB;

    function getProduct($brand_id) {
    	        $db = Zend_Registry::get('db');

        $select = $db->select()->from(array('p' => WAREHOUSE_DB.'.good'),array(new Zend_Db_Expr('SQL_CALC_FOUND_ROWS p.id'), 'p.*'))
                ->joinLeft(array('b' => WAREHOUSE_DB.'.brand'),'b.id = p.brand_id',array('brand_name' => 'b.name'))
                ->where('p.product_status IN (?)',array('1','2'));

        if(isset($brand_id) && $brand_id) 
            $select->where('p.brand_id IN (?)',$brand_id);

        $result = $db->fetchAll($select);
        return $result;
    }

    function getBrand($good_id) {
        $db = Zend_Registry::get('db');

        $select = $db->select()
            ->from(array('b' => WAREHOUSE_DB.'.brand'),array('brand_name' => 'b.name'))
            ->joinLeft(array('g' => WAREHOUSE_DB.'.good'),'g.brand_id = b.id',array())
            ->where('g.id =?',$good_id);

        $result = $db->fetchAll($select);
        return $result;
    }

   function allbrand() {
        $db = Zend_Registry::get('db');

        $select = $db->select()->from(array('p' => WAREHOUSE_DB.'.brand'),array(new Zend_Db_Expr('SQL_CALC_FOUND_ROWS p.id'), 'p.*'));

        $result = $db->fetchAll($select);
        return $result;
   }

   function get_cache(){
        $cache      = Zend_Registry::get('cache');
        $result     = $cache->load(WAREHOUSE_DB.'_'.$this->_name.'_cache');

        if ($result === false) {

            $db = Zend_Registry::get('db');

            $select = $db->select()
                ->from(array('p' => WAREHOUSE_DB.'.'.$this->_name),
                    array('p.*'));

            // $select->order(new Zend_Db_Expr('p.`name` COLLATE utf8_unicode_ci'));

            $data = $db->fetchAll($select);

            $result = array();
            if ($data){
                foreach ($data as $item){
                    $result[$item['id']] = $item['name'];
                }
            }
            $cache->save($result, WAREHOUSE_DB.'_'.$this->_name.'_cache', array(), null);
        }
        return $result;
    }

}
