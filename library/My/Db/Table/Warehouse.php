<?php
class My_Db_Table_Warehouse extends Zend_Db_Table_Abstract
{
    public function init() 
    {
        $this->_schema = Zend_Registry::get('warehouseDbName');
    }
}