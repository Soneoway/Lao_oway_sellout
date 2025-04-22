<?php
class Application_Model_OffDate extends Zend_Db_Table_Abstract
{
    protected $_name = 'off_date';

    function fetchPagination($page, $limit, &$total, $params)
    {
        $db = Zend_Registry::get('db');

        $select = $db->select()->from(array('p' => $this->_name), array(new Zend_Db_Expr
                ('SQL_CALC_FOUND_ROWS p.id'), 'p.*'));

        $select->distinct();
        //s?a di?u ki?n inner join -> left join on staff
        $select->joinLeft(array('s' => 'staff'), 'p.staff_id = s.id', array(
            's.email',
            's.firstname',
            's.lastname',
            's.department',
            's.team',
            's.title',
            's.regional_market',
            's.phone_number',
            's.code',
            's.contract_term'
            ));

        if (isset($params['name']) and $params['name'])
            $select->where('CONCAT(s.firstname, " ",s.lastname) LIKE ?', '%'.$params['name'].'%');

        if (isset($params['email']) and $params['email']) {
            $params['email'] = preg_replace('/' . EMAIL_SUFFIX . '/', '', $params['email']);
            $select->where('s.email LIKE ?', $params['email'] . EMAIL_SUFFIX);
        }


        if (isset($params['team']) and $params['team']) {
            $select->where('s.team in (?)', $params['team']);
        }

        if (isset($params['code']) and $params['code'])
            $select->where('s.code LIKE ?', '%' . $params['code'] . '%');

        $select->where('status = 1' , null);
        
        $select->order('p.id', 'COLLATE utf8_unicode_ci ASC');

        if ($limit)
			$select->limitPage($page, $limit);

        $result = $db->fetchAll($select);
        $total = $db->fetchOne("select FOUND_ROWS()");
        return $result;
    }


    function checkOff($staff_id, $month, $year)
    {


        $where = array();
        $where[] = $this->getAdapter()->quoteInto('staff_id = ?', $staff_id);
        $where[] = $this->getAdapter()->quoteInto('month = ?', $month);
        $where[] = $this->getAdapter()->quoteInto('year = ?', $year);
        $result = $this->fetchRow($where);

        if (!$result) {
            return - 1;
        } else
            return $result['date'];

    }

    function backupDatabase()
    {
        $obj = new Application_Model_OffDate();

        $obj2 = new Application_Model_OffDateBackup();

        try {


            //m� backup
            $sn = date('YmdHis') . substr(microtime(), 2, 4);

            $result = $obj->fetchAll();

            foreach ($result as $Row) {

                $data = $Row->toArray();
                $data['sn'] = $sn;
                $data['date_backup'] = date('Y-m-d h:i:s');
                $obj2->insert($data);

            }
            return 1;
        }
        catch (exception $e) {
            return - 1;
        }
    }

}
