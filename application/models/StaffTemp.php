<?php
class Application_Model_StaffTemp extends Zend_Db_Table_Abstract
{
    protected $_name = 'staff_temp';

    function fetchPagination($page, $limit, &$total, $params){
        $db = Zend_Registry::get('db');

        if ($limit) {
            $select = $db->select()
                ->from(array('p' => 'staff_temp'),
                    array(new Zend_Db_Expr('SQL_CALC_FOUND_ROWS DISTINCT p.id'), 'p.*'));
            $select->joinLeft(array('s'=>'staff'),'p.staff_id = s.id',array('s.email'));
        } else {
            $select = $db->select()
                ->from(array('p' => 'staff_temp'),
                    array(new Zend_Db_Expr('DISTINCT p.id'), 'p.*'));
        }

        if (isset($params['name']) and $params['name']) {
            $select->where('CONCAT(p.firstname, " ",p.lastname) LIKE ?', '%'.$params['name'].'%');
            //            $select->orwhere('p.email LIKE ?', '%'.$params['name'].'%');

        }

        if (isset($params['company_id']) and $params['company_id']) {
            $select->where('p.company_id = ?', $params['company_id']);
        }

        if (isset($params['team']) and $params['team']) {
            if (is_array($params['team']) && count($params['team']) > 0) {
                $select->where('p.team IN (?)', $params['team']);
            } else {
                $select->where('1=0', 1);
            }
        }

        if (isset($params['contract_term']) and $params['contract_term']) {
            if (is_array($params['contract_term']) && count($params['contract_term']) > 0) {
                $select->where('p.contract_term IN (?)', $params['contract_term']);
            } else {
                $select->where('1=0', 1);
            }
        }

        if (isset($params['department']) and $params['department']) {
            if (is_array($params['department']) && count($params['department']) > 0) {
                $select->where('p.department IN (?)', $params['department']);
            } else {
                $select->where('1=0', 1);
            }
        }

        if (isset($params['title']) and $params['title']) {
            if (is_array($params['title']) && count($params['title']) > 0) {
                $select->where('p.title IN (?)', $params['title']);
            } else {
                $select->where('1=0', 1);
            }
        }

        if (isset($params['is_officer']) and intval($params['is_officer']) > 0){
            if ($params['is_officer'] == 1)
                $select->where('p.is_officer = ?', 1);
            elseif ($params['is_officer'] == 2)
                $select->where('p.is_officer = ?', 0);
        }

        if (isset($params['is_approved']) and intval($params['is_approved']) >= -1){
             $select->where('p.is_approved = ?', $params['is_approved']);
        }

        if (isset($params['email']) and $params['email']) {
            $params['email'] = preg_replace('/'.EMAIL_SUFFIX.'/', '', $params['email']);
            $select->where('p.email LIKE ?', $params['email'].EMAIL_SUFFIX);
        }

        if (isset($params['date']) and $params['date'])
        {
            $date = explode(',', $params['date']);
            $select->where("DAY(STR_TO_DATE(dob, '%d/%m/%Y')) IN (?)", $date);
        }

        if (isset($params['month']) and $params['month'])
        {
            $date = explode(',', $params['month']);
            $select->where("MONTH(STR_TO_DATE(dob, '%d/%m/%Y')) IN (?)", $date);
        }

        if(isset($params['month_off']) and $params['month_off'])
        {
            $select
                ->joinLeft(array('mo' => 'off_date_add'), 'p.id = mo.staff_id ' , array('staff_id' => 'mo.staff_id'));
            $select->where('MONTH(mo.date) <> ?' , $params['month_off'] )->orWhere('MONTH(mo.date) is null' , null);
        }

        if (isset($params['year']) and $params['year'])
        {
            $date = explode(',', $params['year']);
            $select->where("YEAR(STR_TO_DATE(dob, '%d/%m/%Y')) IN (?)", $date);
        }

        if ( ( isset($params['off']) and intval($params['off']) > 0 ) || ( isset($params['sname']) and $params['sname'] == 1 ) )
            $select->where('p.off_date is '
                . ( $params['off'] == 2 ? 'not' : '')
                .' null');

        // list
        if (isset($params['exp']) and $params['exp'])
            $select->where('p.contract_expired_at > NOW()');

        if (isset($params['sales_team_id']) and $params['sales_team_id'])
            $select->where('p.sales_team_id = ?', $params['sales_team_id']);

        if (isset($params['is_leader']) and $params['is_leader'])
            $select->where('p.is_leader = ?', $params['is_leader']);

        if (isset($params['distribution_id']) and $params['distribution_id'])
            $select->where('p.distribution_id = ?', $params['distribution_id']);

        if (isset($params['note']) and $params['note'])
            $select->where('p.note LIKE ?', '%'.$params['note'].'%');

        if (isset($params['code']) and $params['code'])
            $select->where('p.code LIKE ?', '%'.$params['code'].'%');

        if (isset($params['has_photo']) and $params['has_photo'])
            $select->where('p.photo IS NOT NULL AND p.photo <> \'\'', 1);

        if (isset($params['ood']) and $params['ood']){
            $select->where(' DATEDIFF(p.contract_expired_at,NOW()) <= ?', 30);
            /*$select->where(' DATEDIFF(NOW(),p.contract_expired_at) >= ?', 0);*/
        }

        if (isset($params['listTitles']) and $params['listTitles'])
            $select->where('p.title IN (?)', $params['listTitles']);


        if (isset($params['tags']) and $params['tags']){
            $select->join(array('ta_ob' => 'tag_object'),
                '
                    p.id = ta_ob.object_id
                    AND ta_ob.type = '.TAG_STAFF.'
                ',
                array());
            $select->join(array('ta' => 'tag'),
                '
                    ta.id = ta_ob.tag_id
                ',
                array());

            $select->where('ta.name IN (?)', $params['tags']);
        }

        if (isset($params['area_id']) and $params['area_id'] && !(isset($params['regional_market']) and $params['regional_market'])){
            $QRegionalMarket = new Application_Model_RegionalMarket();

            if (is_array($params['area_id']) && count($params['area_id']) > 0) {
                $where = $QRegionalMarket->getAdapter()->quoteInto('area_id IN (?)', $params['area_id']);
            } else {
                $where = $QRegionalMarket->getAdapter()->quoteInto('area_id = ?', $params['area_id']);
            }

            $regional_markets = $QRegionalMarket->fetchAll($where);
            $tem = array();

            foreach ($regional_markets as $regional_market)
                $tem[] = $regional_market->id;

            if (is_array($tem) && count($tem) > 0)
                $select->where('p.regional_market IN (?)', $tem);
            else
                $select->where('1=0', 1);

        } elseif (isset($params['regional_market']) and $params['regional_market']) {
            if (is_array($params['regional_market']) && count($params['regional_market']) > 0) {
                $select->where('p.regional_market IN (?)', $params['regional_market']);
            } else {
                $select->where('1=0', 1);
            }
        }

        if (isset($params['listAreaIds']) and $params['listAreaIds'] == -1)
            $select->where('1=0', 1);

        if (isset($params['listAreaIds']) and $params['listAreaIds']){
            $QRegionalMarket = new Application_Model_RegionalMarket();

            if (is_array($params['listAreaIds']) && count($params['listAreaIds']) > 0) {
                $where = $QRegionalMarket->getAdapter()->quoteInto('area_id IN (?)', $params['listAreaIds']);
            } else {
                $where = $QRegionalMarket->getAdapter()->quoteInto('area_id = ?', $params['listAreaIds']);
            }

            $regional_markets = $QRegionalMarket->fetchAll($where);
            $tem = array();

            foreach ($regional_markets as $regional_market)
                $tem[] = $regional_market->id;

            if (is_array($tem) && count($tem) > 0)
                $select->where('p.regional_market IN (?)', $tem);
            else
                $select->where('1=0', 1);
        }

        if (isset($params['title']) and $params['title']) {
            if (is_array($params['title']) && count($params['title']) > 0) {
                $select->where('p.title IN (?)', $params['title']);
            } else {
                $select->where('1=0', 1);
            }
        }

        if (isset($params['s_assign']) && $params['s_assign']) {
            $QDeparment = new Application_Model_Department();
            $where = $QDeparment->getAdapter()->quoteInto('name LIKE ?', 'KINH DOANH');
            $department_id = $QDeparment->fetchAll($where)->current()->id;
            $select->where('p.department = ?', $department_id);
        }

        $order_str = '';

        if (isset($params['sort']) and $params['sort']) {
            $collate = ' ';
            $desc = (isset($params['desc']) and $params['desc'] == 1) ? ' DESC ' : ' ASC ';

            if ($params['sort'] == 'name'){
                $collate = ' COLLATE utf8_unicode_ci ';
                $order_str .= ' CONCAT(p.firstname, " ",p.lastname) '.$collate . $desc;
            } elseif ( /*$params['sort'] == 'title' || */$params['sort'] == 'department' || $params['sort'] == 'team' ) {
                $collate = ' COLLATE utf8_unicode_ci ';
                $order_str .= $collate . $desc;
            } else {
                $order_str = 'p.`'.$params['sort'] . '` ' . $collate . $desc;
            }

            $select->order(new Zend_Db_Expr($order_str));
        }

        if ($limit)
            $select->limitPage($page, $limit);


        $result = $db->fetchAll($select);

        if ($limit)
            $total = $db->fetchOne("select FOUND_ROWS()");
        return $result;
    }

    function fetchStaffPagination($page, $limit, &$total, $params){
        $db = Zend_Registry::get('db');

        if ($limit) {
            $select = $db->select()
                ->from(array('p' => 'staff'),
                    array(new Zend_Db_Expr('SQL_CALC_FOUND_ROWS DISTINCT p.id'), 'p.*'));
        } else {
            $select = $db->select()
                ->from(array('p' => 'staff'),
                    array(new Zend_Db_Expr('DISTINCT p.id'), 'p.*'));
        }

        if (isset($params['name']) and $params['name']) {
            $select->where('CONCAT(p.firstname, " ",p.lastname) LIKE ?', '%'.$params['name'].'%');
            //            $select->orwhere('p.email LIKE ?', '%'.$params['name'].'%');

        }

        if (isset($params['company_id']) and $params['company_id']) {
            $select->where('p.company_id = ?', $params['company_id']);
        }

        if (isset($params['team']) and $params['team']) {
            if (is_array($params['team']) && count($params['team']) > 0) {
                $select->where('p.team IN (?)', $params['team']);
            } else {
                $select->where('1=0', 1);
            }
        }

        if (isset($params['contract_term']) and $params['contract_term']) {
            if (is_array($params['contract_term']) && count($params['contract_term']) > 0) {
                $select->where('p.contract_term IN (?)', $params['contract_term']);
            } else {
                $select->where('1=0', 1);
            }
        }

        if (isset($params['department']) and $params['department']) {
            if (is_array($params['department']) && count($params['department']) > 0) {
                $select->where('p.department IN (?)', $params['department']);
            } else {
                $select->where('1=0', 1);
            }
        }

        if (isset($params['title']) and $params['title']) {
            if (is_array($params['title']) && count($params['title']) > 0) {
                $select->where('p.title IN (?)', $params['title']);
            } else {
                $select->where('1=0', 1);
            }
        }

        if(isset($params['salary_sales']) and $params['salary_sales'])
        {
            $select->where('p.team IN (?)', array(16,75,147));
        }

        if (isset($params['is_officer']) and intval($params['is_officer']) > 0){
            if ($params['is_officer'] == 1)
                $select->where('p.is_officer = ?', 1);
            elseif ($params['is_officer'] == 2)
                $select->where('p.is_officer = ?', 0);
        }

        if (isset($params['email']) and $params['email']) {
            $params['email'] = preg_replace('/'.EMAIL_SUFFIX.'/', '', $params['email']);
            $select->where('p.email LIKE ?', $params['email'].EMAIL_SUFFIX);
        }

        if (isset($params['date']) and $params['date'])
        {
            $date = explode(',', $params['date']);
            $select->where("DAY(STR_TO_DATE(dob, '%d/%m/%Y')) IN (?)", $date);
        }

        if (isset($params['month']) and $params['month'])
        {
            $date = explode(',', $params['month']);
            $select->where("MONTH(STR_TO_DATE(dob, '%d/%m/%Y')) IN (?)", $date);
        }

        if(isset($params['month_off']) and $params['month_off'])
        {
            $select
                ->joinLeft(array('mo' => 'off_date_add'), 'p.id = mo.staff_id ' , array('staff_id' => 'mo.staff_id'));
            $select->where('MONTH(mo.date) <> ?' , $params['month_off'] )->orWhere('MONTH(mo.date) is null' , null);
        }

        if (isset($params['year']) and $params['year'])
        {
            $date = explode(',', $params['year']);
            $select->where("YEAR(STR_TO_DATE(dob, '%d/%m/%Y')) IN (?)", $date);
        }

        if ( ( isset($params['off']) and intval($params['off']) > 0 ) || ( isset($params['sname']) and $params['sname'] == 1 ) )
            $select->where('p.off_date is '
                . ( $params['off'] == 2 ? 'not' : '')
                .' null');

        // list
        if (isset($params['exp']) and $params['exp'])
            $select->where('p.contract_expired_at > NOW()');

        if (isset($params['sales_team_id']) and $params['sales_team_id'])
            $select->where('p.sales_team_id = ?', $params['sales_team_id']);

        if (isset($params['is_leader']) and $params['is_leader'])
            $select->where('p.is_leader = ?', $params['is_leader']);

        if (isset($params['distribution_id']) and $params['distribution_id'])
            $select->where('p.distribution_id = ?', $params['distribution_id']);

        if (isset($params['note']) and $params['note'])
            $select->where('p.note LIKE ?', '%'.$params['note'].'%');

        if (isset($params['code']) and $params['code'])
            $select->where('p.code LIKE ?', '%'.$params['code'].'%');

        if (isset($params['has_photo']) and $params['has_photo'])
            $select->where('p.photo IS NOT NULL AND p.photo <> \'\'', 1);

        if (isset($params['ood']) and $params['ood']){
            $select->where(' DATEDIFF(p.contract_expired_at,NOW()) <= ?', 30);
            /*$select->where(' DATEDIFF(NOW(),p.contract_expired_at) >= ?', 0);*/
        }


        if (isset($params['tags']) and $params['tags']){
            $select->join(array('ta_ob' => 'tag_object'),
                '
                    p.id = ta_ob.object_id
                    AND ta_ob.type = '.TAG_STAFF.'
                ',
                array());
            $select->join(array('ta' => 'tag'),
                '
                    ta.id = ta_ob.tag_id
                ',
                array());

            $select->where('ta.name IN (?)', $params['tags']);
        }

        if (isset($params['area_id']) and $params['area_id'] && !(isset($params['regional_market']) and $params['regional_market'])){
            $QRegionalMarket = new Application_Model_RegionalMarket();

            if (is_array($params['area_id']) && count($params['area_id']) > 0) {
                $where = $QRegionalMarket->getAdapter()->quoteInto('area_id IN (?)', $params['area_id']);
            } else {
                $where = $QRegionalMarket->getAdapter()->quoteInto('area_id = ?', $params['area_id']);
            }

            $regional_markets = $QRegionalMarket->fetchAll($where);
            $tem = array();

            foreach ($regional_markets as $regional_market)
                $tem[] = $regional_market->id;

            if (is_array($tem) && count($tem) > 0)
                $select->where('p.regional_market IN (?)', $tem);
            else
                $select->where('1=0', 1);

        } elseif (isset($params['regional_market']) and $params['regional_market']) {
            if (is_array($params['regional_market']) && count($params['regional_market']) > 0) {
                $select->where('p.regional_market IN (?)', $params['regional_market']);
            } else {
                $select->where('1=0', 1);
            }
        }

        if (isset($params['listTitles']) and $params['listTitles'])
            $select->where('p.title IN (?)', $params['listTitles']);

        if (isset($params['listAreaIds']) and $params['listAreaIds'] == -1)
            $select->where('1=0', 1);

        if (isset($params['listAreaIds']) and $params['listAreaIds']){
            $QRegionalMarket = new Application_Model_RegionalMarket();

            if (is_array($params['listAreaIds']) && count($params['listAreaIds']) > 0) {
                $where = $QRegionalMarket->getAdapter()->quoteInto('area_id IN (?)', $params['listAreaIds']);
            } else {
                $where = $QRegionalMarket->getAdapter()->quoteInto('area_id = ?', $params['listAreaIds']);
            }

            $regional_markets = $QRegionalMarket->fetchAll($where);
            $tem = array();

            foreach ($regional_markets as $regional_market)
                $tem[] = $regional_market->id;

            if (is_array($tem) && count($tem) > 0)
                $select->where('p.regional_market IN (?)', $tem);
            else
                $select->where('1=0', 1);
        }

        if (isset($params['title']) and $params['title']) {
            if (is_array($params['title']) && count($params['title']) > 0) {
                $select->where('p.title IN (?)', $params['title']);
            } else {
                $select->where('1=0', 1);
            }
        }

        $select->joinLeft(array('st' => 'staff_temp'),
            '
                p.id = st.staff_id
            ',
            array('staff_temp_id' => 'st.id', 'staff_temp_is_approved' => 'st.is_approved'));

        if (isset($params['need_approve']) and $params['need_approve']){
            $select->where('st.is_approved = ?', 0);
        }

        if (isset($params['s_assign']) && $params['s_assign']) {
            $QDeparment = new Application_Model_Department();
            $where = $QDeparment->getAdapter()->quoteInto('name LIKE ?', 'KINH DOANH');
            $department_id = $QDeparment->fetchAll($where)->current()->id;
            $select->where('p.department = ?', $department_id);
        }

        $order_str = '';

        if (isset($params['sort']) and $params['sort']) {
            $collate = ' ';
            $desc = (isset($params['desc']) and $params['desc'] == 1) ? ' DESC ' : ' ASC ';

            if ($params['sort'] == 'name'){
                $collate = ' COLLATE utf8_unicode_ci ';
                $order_str .= ' CONCAT(p.firstname, " ",p.lastname) '.$collate . $desc;
            } elseif ( /*$params['sort'] == 'title' || */$params['sort'] == 'department' || $params['sort'] == 'team' ) {
                $collate = ' COLLATE utf8_unicode_ci ';
                $order_str .= $collate . $desc;
            } else {
                $order_str = 'p.`'.$params['sort'] . '` ' . $collate . $desc;
            }

            $select->order(new Zend_Db_Expr($order_str));
        }

        if ($limit)
            $select->limitPage($page, $limit);


        $result = $db->fetchAll($select);

        if ($limit)
            $total = $db->fetchOne("select FOUND_ROWS()");
        return $result;
    }
}                                                      
