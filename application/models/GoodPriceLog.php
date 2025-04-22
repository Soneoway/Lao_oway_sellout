<?php
class Application_Model_GoodPriceLog extends Zend_Db_Table_Abstract
{
    protected $_name = 'good_price_log';
    protected $_schema = WAREHOUSE_DB;

    public function get_list($from, $to)
    {
        $db = Zend_Registry::get('db');

        $_from = sprintf("CASE WHEN p.from_date < '%s' THEN '%s' ELSE p.from_date END", $from, $from);
        $_to = sprintf("CASE WHEN p.to_date > '%s' OR p.to_date IS NULL THEN '%s' ELSE p.to_date END", $to, $to);
        $_color = sprintf("CASE WHEN p.color_id IS NULL THEN 0 ELSE p.color_id END");

        $select = $db->select()
            ->from(array('p' => $this->_schema.'.'.$this->_name), array(
                'from_date' => new Zend_Db_Expr($_from), 
                'to_date'   => new Zend_Db_Expr($_to), 
                'p.good_id', 
                'color_id'  => new Zend_Db_Expr($_color),
                'p.price',
            ))
            ->where('DATE(from_date) <= ?', $to)
            ->where('DATE(to_date) IS NULL OR DATE(to_date) >= ?', $from);

        $log = $db->fetchAll($select);

        $log_list = array();

        foreach ($log as $key => $value) {
            $log_list[ $value['good_id'] ][$value['color_id']][] = array(
                'from'  => $value['from_date'],
                'to'    => $value['to_date'],
                'price' => $value['price'],
            );
        }

        return $log_list;
    }
}