<?php
class Application_Model_DateSpecial extends Zend_Db_Table_Abstract
{
	protected $_name = 'date_special';
    
    function get_date($month)
    {
    	$sql = "
            SELECT DAY(p.date) as date , p.`team` , p.`type`
            FROM date_special as p
			WHERE MONTH(p.date) = ?
			";

		$db = Zend_Registry::get('db');
		$result = $db->query($sql, $month);
		$result = $result->fetchAll();

		if ($result) {
			return $result;
		} else {
			return null;
		}
    }
}
