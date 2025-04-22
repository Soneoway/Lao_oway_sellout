<?php
class Application_Model_KpiByPrice extends Zend_Db_Table_Abstract
{
    protected $_name = 'kpi_by_price';

    public function get_list($from, $to)
    {
        $where = array();
        $where[] = $this->getAdapter()->quoteInto('DATE(from_date) >= ?', $from);
        $where[] = $this->getAdapter()->quoteInto('DATE(from_date) <= ?', $to);
        $where[] = $this->getAdapter()->quoteInto('DATE(to_date) >= ?', $from);
        $where[] = $this->getAdapter()->quoteInto('DATE(to_date) <= ?', $to);

        $log = $this->fetchAll($where);

        $log_list = array();

        foreach ($log as $key => $value) {
            $log_list[ $value['good_id'] ][] = array(
                                                     'from' => $value['from_date'],
                                                     'to' => $value['to_date'],
                                                     );
        }

        return $log_list;
    }
}