<?php
class Application_Model_GoodColor extends Zend_Db_Table_Abstract
{
	protected $_name = 'good_color';
    protected $_schema = WAREHOUSE_DB;

    function get_cache(){
        $cache      = Zend_Registry::get('cache');
        $result     = $cache->load(WAREHOUSE_DB.'_'.$this->_name.'_cache');

        if ($result === false) {

            $data = $this->fetchAll();

            $result = array();
            if ($data){
                foreach ($data as $item){
                    $result[$item->id] = $item->name;
                }
            }
            $cache->save($result, WAREHOUSE_DB.'_'.$this->_name.'_cache', array(), null);
        }
        return $result;
    }

    function get_phone_colors($remove_list = array())
    {
    	$db = Zend_Registry::get('db');
    	$sql = "SELECT c.`name` AS color_name, c.`id` AS color_id, g.`name` AS good_name, g.`id` AS good_id
                FROM ".WAREHOUSE_DB.'.'.$this->_name." c
                    INNER JOIN ".WAREHOUSE_DB.".good_color_combined gc
                    ON c.id = gc.good_color_id
                    INNER JOIN ".WAREHOUSE_DB.".good g
                    ON gc.good_id = g.id
                    AND g.cat_id = ?";

        if (isset($remove_list) && count($remove_list) > 0) {
            $sql .= " WHERE NOT FIND_IN_SET(good_id, ?)";
            $remove_list = implode(',', $remove_list);
            
            $result = $db->query($sql, array(PHONE_CAT_ID, $remove_list));
        } else
            $result = $db->query($sql, array(PHONE_CAT_ID));


		$arr = array();

		foreach ($result as $key => $value) {
			$arr[$key] = $value;
		}

		return $arr;
    }

    // Function for Webservice
    function getAll_GoodColor($params) {
        $db = Zend_Registry::get('db');
        $select = $db->select()->from(array('gc' => WAREHOUSE_DB.'.'.$this->_name),array('gc.*'));

        $result = $db->fetchAll($select);
        return $result;
    }

}
