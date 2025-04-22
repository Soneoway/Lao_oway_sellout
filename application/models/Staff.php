<?php
class Application_Model_Staff extends Zend_Db_Table_Abstract
{
    protected $_name = 'staff';

    /**
     * Một số config cục bộ
     * asm : các GROUP ID được xem dashboard vùng, ví dụ gồm ASM hay là gồm ASM và Sales Admin...3
     * -- viết tiếp nếu có config thêm
     * @var array
     */
    private $config = array(
        'asm' => array(
            ASM_ID,
            ASMSTANDBY_ID,
            SALES_ADMIN_ID,
            ),
        'ttl' => 60
        );

    function fetchPagination($page, $limit, &$total, $params){
        $db = Zend_Registry::get('db');

        if ($limit) {
            $select = $db->select()
                ->from(array('p' => $this->_name),
                    array(new Zend_Db_Expr('SQL_CALC_FOUND_ROWS DISTINCT p.id'), 'p.*'));
        } else {
            $select = $db->select()
                ->from(array('p' => $this->_name),
                    array(new Zend_Db_Expr('DISTINCT p.id'), 'p.*'));
        }

        $select->joinLeft(array('e'=>'staff_education'),'p.id = e.staff_id AND e.default_level = 1',array('d_level'=>'e.level','d_certificate'=>'field_of_study'));

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

        if(isset($params['salary_sales']) and $params['salary_sales'])
        {
            $select->where('p.title IN (?)', array(PGPB_TITLE,SALES_TITLE,SALES_ACCESSORIES_TITLE,SALES_LEADER_TITLE ,SALES_ACCESSORIES_LEADER_TITLE));
        }

        if (isset($params['is_officer']) and intval($params['is_officer']) > 0){
            if ($params['is_officer'] == 1)
                $select->where('p.is_officer = ? or p.team = 11 or p.title in (274,179,181)', 1);
            elseif ($params['is_officer'] == 2)
                $select->where('p.is_officer ? or p.team = 11 or p.title in (274,179,181)', 0);
                //$select->orWhere('p.team = ?', WARRANTY_CENTER);
        }

        if (isset($params['pc_stand_by']) and $params['pc_stand_by']){
            $select->where('p.pc_stand_by = ?', $params['pc_stand_by']);
        }

        if (isset($params['email']) and $params['email']) {
            $params['email'] = preg_replace('/'.EMAIL_SUFFIX.'/', '', $params['email']);
            $select->where('p.email LIKE ?', $params['email'].EMAIL_SUFFIX);
        }

        // Staff group
        if(isset($params['group_id']) && $params['group_id']){
            $select->where('p.group_id =?',$params['group_id']);
        }

        if (isset($params['date']) and $params['date'])
        {
            $date = explode(',', $params['date']);
            $select->where("DAY(STR_TO_DATE(p.dob, '%d/%m/%Y')) IN (?)", $date);
        }

        if (isset($params['month']) and $params['month'])
        {
            $date = explode(',', $params['month']);
            $select->where("MONTH(STR_TO_DATE(p.dob, '%d/%m/%Y')) IN (?)", $date);
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
            $select->where("YEAR(STR_TO_DATE(p.dob, '%d/%m/%Y')) IN (?)", $date);
        }

        if(isset($params['ready_print']) and $params['ready_print'])
        {

            if($params['ready_print'] == 1)
           {
             $select->where("p.address <> '' and p.birth_place <> ''  and p.ID_number <> '' and p.ID_place <> '' and p.ID_date <> '' and p.title <> '' and p.company_id <> 0");
           }
            elseif($params['ready_print'] == 2)
            {
                 $select->where("address = '' or birth_place = ''  or ID_number = '' or ID_place = '' or ID_date = '' or title = '' or company_id = 0");
            }

            elseif($params['ready_print'] == 3)
            {
                 $select->where("p.ID_number <> '' and p.ID_place <> '' and p.ID_date <> '' and p.title <> '' and p.company_id <> 0 and EXISTS (select * from staff_address sa2 where p.id = sa2.staff_id and sa2.address_type = 4 and sa2.address is not null)");


            }

            elseif($params['ready_print'] == 4)
            {
                 $select->where("p.ID_number = '' or p.ID_place = '' or p.ID_date = '' or p.title = '' or p.company_id = 0 or  and EXISTS (select * from staff_address sa2 where p.id = sa2.staff_id and sa2.address_type = 4 and sa2.address is not null)");
            }

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

        if ( ( isset($params['off']) and intval($params['off']) > 0 ) || ( isset($params['sname']) and $params['sname'] == 1 ) )
            $select->where('p.off_date is '
                    . ( $params['off'] == 2 ? 'not' : '')
                    .' null');

        // list
        if (isset($params['exp']) and $params['exp'])
            $select->where('p.contract_expired_at > NOW()');

        // Check onwork and off
         if(isset($params['off']) and $params['off'])
            $select->where('p.status=?', $params['off']);

        if (isset($params['sales_team_id']) and $params['sales_team_id'])
            $select->where('p.sales_team_id = ?', $params['sales_team_id']);

        if (isset($params['is_leader']) and $params['is_leader'])
            $select->where('p.is_leader = ?', $params['is_leader']);

        if (isset($params['distribution_id']) and $params['distribution_id'])
            $select->where('p.distribution_id = ?', $params['distribution_id']);

        if (isset($params['note']) and $params['note'])
            $select->where('p.note LIKE ?', '%'.$params['note'].'%');

      //  if (isset($params['code']) and $params['code'])
     //       $select->where('p.code LIKE ?', '%'.$params['code'].'%');
if ( isset($params['code']) && $params['code'] ) {
            $select->where('p.code IN (?)', $params['code']);}
        if (isset($params['has_photo']) and $params['has_photo'])
            $select->where('p.photo IS NOT NULL AND p.photo <> \'\'', 1);

        if (isset($params['ood']) and $params['ood']){
            $select->where(' DATEDIFF(p.contract_expired_at,NOW()) <= ?', 30);
            /*$select->where(' DATEDIFF(NOW(),p.contract_expired_at) >= ?', 0);*/
        }

        if (isset($params['no_print']) and $params['no_print']){
            $select->where('p.print_time = 0 OR p.contract_signed_at is NULL and p.contract_expired_at is NULL', '');
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

        if(isset($params['area_trainer_right']) and $params['area_trainer_right'])
        {
            $QRegionalMarket = new Application_Model_RegionalMarket();

            if (is_array($params['area_trainer_right']) && count($params['area_trainer_right']) > 0) {
                $whereRegionalMarketRight = $QRegionalMarket->getAdapter()->quoteInto('area_id IN (?)', $params['area_trainer_right']);
            } else {
                $whereRegionalMarketRight  = $QRegionalMarket->getAdapter()->quoteInto('area_id = ?', $params['area_trainer_right']);
            }

            $regional_markets_rights = $QRegionalMarket->fetchAll($whereRegionalMarketRight);
            $tem = array();

            foreach ($regional_markets_rights as $regional_markets_right)
                $tem[] = $regional_markets_right->id;

            if (is_array($tem) && count($tem) > 0)
                $select->where('p.regional_market IN (?)', $tem);
            else
                $select->where('1=0', 1);
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

        $select->group('p.id');
        $select->order('p.id desc');

        // Permission for Training Group
        if (isset($params['training_id']) && $params['training_id'] != 15565) {
            $select->join(array('ss' => 'store_staff'), 'p.id = ss.staff_id AND ss.is_leader = 0', array());
            $select->join(array('sto' => 'store'), 'ss.store_id = sto.id', array());

            $QAsm = new Application_Model_Asm();
            $list_regions = $QAsm->get_cache($params['training_id']);
            $list_regions = isset($list_regions['district']) && is_array($list_regions['district']) ? $list_regions['district'] : array();

            if (count($list_regions) > 0)
                $select->where('sto.district IN (?)', $list_regions);
            else
                $select->where('1=0', 1);
        }

        // Permission for Terry [Staff ID : 15565]
        if (isset($params['training_id']) && $params['training_id'] == 15565) {

            $select->join(array('rm' => 'regional_market'), 'p.regional_market = rm.id', array());
            $select->where('rm.area_id >= 73');
            $select->where('rm.area_id <= 117');
            $select->where('p.group_id IN (4,17,25)');

        }

        // Permission for PCM Group
        if (isset($params['pcm_id']) && $params['pcm_id']) {
            $select->join(array('ss' => 'store_staff'), 'p.id = ss.staff_id AND ss.is_leader = 0', array());
            $select->join(array('sto' => 'store'), 'ss.store_id = sto.id', array());

            $params['staff_id'] = $params['pcm_id'];
            $QStoreStaff = new Application_Model_StoreStaff();
            $tmp = $QStoreStaff->getStoreList($params);
            for ($i=0;$i<count($tmp);$i++) { $list_store[$i] = $tmp[$i]['store_id']; }
            
            if (count($list_store) > 0)
                $select->where('sto.id IN (?)', $list_store);
            else
                $select->where('1=0', 1);
        }

        //echo $select;
        // if ($limit)
        //    $select->limitPage($page, $limit);
        $result = $db->fetchAll($select);

        if ($limit)
            $total = $db->fetchOne("select FOUND_ROWS()");
        return $result;
    }

    function fetchLeaderPagination($page, $limit, &$total, $params){
        $db = Zend_Registry::get('db');

        if ($limit) {
            $select = $db->select()
                ->from(array('p' => $this->_name),
                    array(new Zend_Db_Expr('SQL_CALC_FOUND_ROWS DISTINCT p.id'), 'p.firstname', 'p.lastname', 'p.email', 'p.phone_number', 'p.regional_market'));
        } else {
            $select = $db->select()
                ->from(array('p' => $this->_name),
                    array(new Zend_Db_Expr('DISTINCT p.id'), 'p.firstname', 'p.lastname', 'p.email', 'p.phone_number', 'p.regional_market'));
        }

        $select
            ->distinct()
            ->joinLeft(array('sl' => 'store_leader'), 'sl.staff_id=p.id AND sl.status=1', array('current_store' => 'COUNT(DISTINCT sl.store_id)'))
            ->joinLeft(array('sll' => 'store_leader'), 'sll.staff_id=p.id AND sll.status=0', array('pending_store' =>'COUNT(DISTINCT sll.store_id)'))
            ->group('p.id');

        $select->where('p.group_id = ?', LEADER_ID);
        $select->where('p.status = 1');

        if (isset($params['name']) and $params['name']) {
            $select->where('CONCAT(p.firstname, " ",p.lastname) LIKE ?', '%'.$params['name'].'%');
        }

        if (isset($params['email']) and $params['email']) {
            $params['email'] = preg_replace('/'.EMAIL_SUFFIX.'/', '', $params['email']);
            $select->where('p.email LIKE ?', $params['email'].EMAIL_SUFFIX);
        }

        if (isset($params['area_id']) and $params['area_id'] && !(isset($params['province']) and $params['province'])){
            $QRegionalMarket = new Application_Model_RegionalMarket();
            $where = $QRegionalMarket->getAdapter()->quoteInto('area_id = ?', $params['area_id']);

            $regional_markets = $QRegionalMarket->fetchAll($where);
            $tem = array();

            foreach ($regional_markets as $regional_market)
                $tem[] = $regional_market->id;

            if (is_array($tem) && count($tem) > 0)
                $select->where('p.regional_market IN (?)', $tem);
            else
                $select->where('1=0', 1);
        } elseif (isset($params['province']) and $params['province']) {
            $select->where('p.regional_market = ?', $params['province']);
        }

        if (isset($params['store']) && $params['store']) {
            $QStore = new Application_Model_Store();
            $where = $QStore->getAdapter()->quoteInto('name LIKE ?', '%'.$params['store'].'%');
            $result = $QStore->fetchAll($where);

            $store_ids = array();
            foreach ($result as $key => $value) {
                $store_ids[] = $value['id'];
            }

            $select->where('sl.store_id IN (?) OR sll.store_id IN (?)', $store_ids);
        }

        // Filter for ASM and Leader
        if (isset($params['asm']) and $params['asm']) {
            $QAsm = new Application_Model_Asm();
            $list_regions = $QAsm->get_cache($params['asm']);
            $list_regions = isset($list_regions['province']) && is_array($list_regions['province']) ? $list_regions['province'] : array();

            if (count($list_regions))
                $select->where('p.regional_market IN (?)', $list_regions);
            else
                $select->where('1=0', 1);

        } elseif (isset($params['leader']) and $params['leader']) {
            $select->where('p.id = ?', $params['leader']);
        }

        $order_str = '';

        if (isset($params['sort']) and $params['sort']) {
            $collate = ' ';

            if ($params['sort'] == 'area_id') {
                $select
                    ->join(array('r' => 'regional_market'), 'r.id=p.regional_market', array())
                    ->join(array('a' => 'area'), 'a.id=r.area_id', array());
            } elseif ($params['sort'] == 'province') {
                $select
                    ->join(array('r' => 'regional_market'), 'r.id=p.regional_market', array());
            }

            $desc = (isset($params['desc']) and $params['desc'] == 1) ? ' DESC ' : ' ASC ';

            if (in_array($params['sort'], array('area_id', 'province', 'name'))) {
                $collate = ' COLLATE utf8_unicode_ci ';
            }

            if ($params['sort'] == 'name'){
                $order_str .= ' CONCAT(p.firstname, " ",p.lastname) '.$collate . $desc;

            } elseif ($params['sort'] == 'area_id') {
                $order_str .= ' a.name '.$collate . $desc;
            } elseif ($params['sort'] == 'province') {
                $order_str .= ' r.name '.$collate . $desc;
            }  elseif (in_array($params['sort'], array('current_store', 'pending_store'))) {
                $order_str .= $params['sort']. ' ' . $desc;
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

    function fetchSalesPgPagination($page, $limit, &$total, $params){

        $db = Zend_Registry::get('db');

        $get = array(
            'staff_id'          => new Zend_Db_Expr("SQL_CALC_FOUND_ROWS s.id"),
            'staff_code'        => 's.code',
            'staff_name'        => new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)"),
            'staff_email'       => 's.email',
            'staff_phone'       => 's.phone_number',
            'staff_off'         => 's.off_date',
            'group_name'        => 'g.name',
            'regional_market'   => 's.regional_market', 
            'current_store'     => new Zend_Db_Expr("COUNT(ss.store_id)"),
        );

        $select = $db->select()
            ->from(array('s' => $this->_name), $get)
            ->joinLeft(array('g' => 'group'), 's.group_id = g.id', array())
            ->joinLeft(array('ss' => 'store_staff'), 's.id = ss.staff_id', array())
            ->group('s.id');

        $select->where('s.group_id IN (?)', array(PGPB_ID, ASM_ID, SALES_ID, RM_ID, ASMSTANDBY_ID, AM_ID, BM_ID) );

        if (isset($params['name']) and $params['name']) {
            $select->where('CONCAT(s.firstname, " ",s.lastname) LIKE ?', '%'.$params['name'].'%');
        }

        if (isset($params['staff_code']) and $params['staff_code']) {
            $select->where('s.code = ?', $params['staff_code']);
        }

        if (isset($params['email']) and $params['email']) {
            $params['email'] = preg_replace('/'.EMAIL_SUFFIX.'/', '', $params['email']);
            $select->where('s.email LIKE ?', $params['email'].EMAIL_SUFFIX);
        }

        if ( isset($params['province']) and $params['province'] ) {
            $select->where('s.regional_market = ?', $params['province']);

        } elseif ( isset($params['area_id']) and $params['area_id'] ){
            $QRegionalMarket = new Application_Model_RegionalMarket();
            $where = $QRegionalMarket->getAdapter()->quoteInto('area_id = ?', $params['area_id']);

            $regional_markets = $QRegionalMarket->fetchAll($where);
            $tem = array();

            foreach ($regional_markets as $regional_market)
                $tem[] = $regional_market->id;

            if (is_array($tem) && count($tem) > 0)
                $select->where('s.regional_market IN (?)', $tem);
            else
                $select->where('1=0', 1);
        }

        if (isset($params['store']) && $params['store']) {
            $QStore = new Application_Model_Store();
            $where = $QStore->getAdapter()->quoteInto('name LIKE ?', '%'.$params['store'].'%');
            $result = $QStore->fetchAll($where);

            $store_ids = array();
            foreach ($result as $key => $value) {
                $store_ids[] = $value['id'];
            }

            $select->where('ss.store_id IN (?)', $store_ids);
        }

        // Filter for ASM
        if (isset($params['asm']) and $params['asm']) {
            $QAsm = new Application_Model_Asm();
            $list_regions = $QAsm->get_cache($params['asm']);
            $list_area = isset($list_regions['area']) && is_array($list_regions['area']) ? $list_regions['area'] : array();
            $list_regions = isset($list_regions['province']) && is_array($list_regions['province']) ? $list_regions['province'] : array();
            //print_r($list_regions);

/*
            if (count($list_regions))
                $select->where('p.regional_market IN (?)', $list_regions);
            else
                $select->where('1=0', 1);
*/
            // get casual worker 
/*
            $QCW = new Application_Model_CasualWorker();
            $where[] = $QCW->getAdapter()->quoteInto('area_id IN (?)', $list_area);
            $where[] = $QCW->getAdapter()->quoteInto('status = 1');
            $result_cw = $QCW->fetchAll($where);

            $list_CW = array();
            for ($i=0;$i<count($result_cw);$i++) {
                $list_CW[$i] = $result_cw[$i]['staff_id'];
            }
*/

/*
            if (count($list_CW))
                $select->orWhere('p.id IN (?)', $list_CW);
            else
                $select->where('1=0', 1);
*/
            if (count($list_regions) && count($list_CW)) {
                $select
                    ->where('( s.regional_market IN (?)', $list_regions)
                    ->orWhere('s.id IN (?) )', $list_CW);
            } elseif (count($list_regions) && !count($list_CW)) {
                $select->where('s.regional_market IN (?)', $list_regions);
            } elseif (!count($list_regions) && count($list_CW)) {
                $select->orWhere('s.id IN (?)', $list_CW);
            } else {
                $select->where('1=0', 1);
            }

        }

        if (isset($params['sales']) and $params['sales']) {
            $select->where('s.id = ?', $params['sales']);
        }

        if (isset($params['pg']) and $params['pg']) {
            $select->where('s.id = ?', $params['pg']);
        }

        if (isset($params['leader']) and $params['leader']) {
            $select->where('s.id = ?', $params['leader']);
        }

        $order_str = '';

        if (isset($params['sort']) and $params['sort']) {
            $collate = ' ';

            if ($params['sort'] == 'area_id') {
                $select
                    ->join(array('r' => 'regional_market'), 'r.id=s.regional_market', array())
                    ->join(array('a' => 'area'), 'a.id=r.area_id', array());
            } elseif ($params['sort'] == 'province') {
                $select
                    ->join(array('r' => 'regional_market'), 'r.id=s.regional_market', array());
            }

            $desc = (isset($params['desc']) and $params['desc'] == 1) ? ' DESC ' : ' ASC ';

            if (in_array($params['sort'], array('area_id', 'province', 'name'))) {
                $collate = ' COLLATE utf8_unicode_ci ';
            }

            if ($params['sort'] == 'name'){
                $order_str .= ' CONCAT(s.firstname, " ", s.lastname) '.$collate . $desc;

            } elseif ($params['sort'] == 'area_id') {
                $order_str .= ' a.name '.$collate . $desc;
            } elseif ($params['sort'] == 'province') {
                $order_str .= ' r.name '.$collate . $desc;
            }  elseif (in_array($params['sort'], array('current_store', 'pending_store'))) {
                $order_str .= $params['sort']. ' ' . $desc;
            } else {
                $order_str = 's.`'.$params['sort'] . '` ' . $collate . $desc;
            }

            $select->order(new Zend_Db_Expr($order_str));
        }

        if ($limit)
            $select->limitPage($page, $limit);

        //echo $select;die;
        $result = $db->fetchAll($select);

        if ($limit)
            $total = $db->fetchOne("select FOUND_ROWS()");

        return $result;
    }

    /**
     * Thống kê số nhân viên hiện tại, chia theo vùng kinh doanh
     * @author phamquocbuu
     * @version 2013-11-05 13:50
     */
    function analytics() {
        $db = Zend_Registry::get('db');

        $subselect = $db->select()->from('staff')
        //            ->where('contract_expired_at > NOW()')
            ->where('off_date IS NULL');

        $select = $db->select()->from($subselect, array(
                'qty'=>'COUNT(*)', 'regional_market'
                ))
            ->group('regional_market');

        $stmt = $db->query($select);
        $data = $stmt->fetchAll();

        return $data;
    }

    function analytics_by_area() {
        $db = Zend_Registry::get('db');

        $subselect = $db->select()->from('staff')
        //            ->where('contract_expired_at > NOW()')
            ->where('off_date IS NULL');

        $select = $db
            ->select()
            ->from(array( 's' => $subselect ), array('qty'=>'COUNT(*)') )
            ->joinleft(array('r' => 'regional_market'), 'r.id = s.regional_market', array())
            ->joinleft(array('a' => 'area'), 'a.id = r.area_id', array('id' => 'a.id'))
            ->group('a.id');

        $stmt = $db->query($select);
        $data = $stmt->fetchAll();

        return $data;
    }

    /**
     * Chi tiết nhân viên hiện tại theo từng vùng kinh doanh
     * @author phamquocbuu
     * @version 2013-11-06 14:40
     */
    function analytics_by_region($regional_market) {
        $db = Zend_Registry::get('db');

        $where = $db->quoteInto('regional_market LIKE ?', $regional_market);

        $subselect = $db->select()->from('staff', array('id', 'department'))
        //            ->where('contract_expired_at > NOW()')
            ->where('off_date IS NULL OR off_date = 0 OR off_date =\'\'')
            ->where($where);
        $select = $db->select()->from($subselect, array(
                'qty'=>'COUNT(*)', 'title'
                ))
            ->group('title');

        $stmt = $db->query($select);
        $data = $stmt->fetchAll();
        return $data;
    }

    function get_cache(){
        $cache      = Zend_Registry::get('cache');
        $result     = $cache->load($this->_name.'_cache');

        if ($result === false) {

            $data = $this->fetchAll();

            $result = array();
            if ($data){
                foreach ($data as $item){
                    $result[$item->id] = $item->firstname . ' ' . $item->lastname;
                }
            }
            $cache->save($result, $this->_name.'_cache', array(), null);
        }
        return $result;
    }

    function get_all_cache(){
        $cache      = Zend_Registry::get('cache');
        $result     = $cache->load($this->_name.'_all_cache');

        if ($result === false) {

            $data = $this->fetchAll();

            $result = array();
            if ($data){
                foreach ($data as $item){
                    $result[$item->id] = array(
                        'name' => $item->firstname . ' ' . $item->lastname,
                        'email' => $item->email,
                        'code' => $item->code,
                        );
                }
            }
            $cache->save($result, $this->_name.'_all_cache', array(), null);
        }
        return $result;
    }

    function get_all_sale_cache(){
        $cache      = Zend_Registry::get('cache');
        $result     = $cache->load($this->_name.'_all_cache');

        if ($result === false) {
            $where = $this->getAdapter()->quoteInto('title IN (?)', implode(',', array(SALES_TITLE, PGPB_TITLE, LEADER_TITLE)));
            $data = $this->fetchAll();

            $result = array();
            if ($data){
                foreach ($data as $item){
                    $result[$item->id] = array(
                        'name' => $item->firstname . ' ' . $item->lastname,
                        'email' => $item->email,
                        'code' => $item->code,
                        );
                }
            }
            $cache->save($result, $this->_name.'_all_cache', array(), null);
        }
        return $result;
    }

    /**
     * Lấy các cột trong bảng này
     */
    function get_cols()
    {
        $cols = $this->info(Zend_Db_Table_Abstract::COLS);
        return $cols;
    }

    /**
     * Function Build Query
     * Dựng câu query để select các staff theo những điều kiện nhất định
     *      để hiển thị ở các ô trong dashboard
     *
     * @param array $cols_existed   - các cột yêu cầu có sẵn giá trị
     * @param array $cols_needed    - các cột chưa có giá trị,
     *                              yêu cầu nhập giá trị mới vào
     * @return string - câu SQL lấy danh sách các staff theo yêu cầu
     *                      trường hợp dữ liệu vào rỗng, trống hoặc không có thực
     *                      thì không chọn staff nào cả
     */
    function build_query($cols_existed = array(), $cols_needed = array(), $for = 'staff')
    {
        // bắt buộc 2 mảng này phải có phần tử
        // không điền thì check thế quái nào
        if (count($cols_needed) == 0 || count($cols_existed) == 0 || trim($cols_needed[0]) == '' || trim($cols_existed[0]) == '') {
            return "SELECT * FROM " . $this->_name . " WHERE 0";
        }

        // danh sách các cột của table
        $cols = $this->get_cols();
        // $other = array_diff($cols, $exclude);

        // giá trị mặc định
        // chỉ check các cột này
        $default = array(
            'code'                    => 'NULL',
            'contract_type'           => '0',
            'contract_signed_at'      => 'NULL',
            'contract_term'           => '0',
            'contract_expired_at'     => 'NULL',
            'department'              => '0',
            'team'                    => '0',
            'firstname'               => 'NULL',
            'lastname'                => 'NULL',
            'title'                   => '',
            'joined_at'               => 'NULL',
            'dob'                     => '',
            'certificate'             => '',
            'level'                   => '',
            'temporary_address'       => 'NULL',
            'address'                 => '',
            'regional_market'         => '0',
            'permanent_address'       => '',
            'birth_place'             => '',
            'native_place'            => '',
            'ID_number'               => '',
            'ID_place'                => '',
            'ID_date'                 => 'NULL',
            'nationality'             => '0',
            'religion'                => '0',
            'phone_number'            => '',
            'email'                   => array('', 'NULL'),
            'group_id'                => '0',
            'social_insurance_time'   => '',
            'social_insurance_number' => '',
            'photo'                   => '',
            'off_date'                => array('', 'NULL'),
            );

        /**
         * @var bool
         * Dùng để check xem từng cụm ( ) đã được thêm phép so sánh nào chưa
         * Nếu có, thêm toán tử OR
         * ngược lại thì thôi
         */
        $flag = false;

        // Mẫu where hợp lệ: ( a OR b OR c ) AND ( d AND e AND f ) AND ( off_date IS NULL )
        $where = " ( ";

        // mớ này duyệt các điều kiện cần điền vào
        // yêu cầu điền đủ tất cả các cột mới cho qua,
        //      thiếu 1 cột cũng liệt kê staff đó ra
        // vì vậy dùng toán tử OR để check
        foreach($cols as $name) {
            // không có trong danh sách giá trị default thì bỏ qua
            //      (ví dụ created_at hay created_by thì check làm quái gì)
            if ( ! isset($default[$name]) ) continue;

            if ( in_array( $name, $cols_needed ) ) {

                if (is_array($default[ $name ])){

                    foreach ($default[ $name ] as $k=>$item){
                        $or = $k>0 ? 'OR' : '';
                        $where .= ' '.$or.' `' .$name. '`' .$this->get_type( $item );
                    }

                } else {

                    if( ! $flag )
                        $where .= "`".$name . "` ";
                    else
                        $where .= " OR `" . $name . "` ";

                    $flag = true;


                    $where .= $this->get_type( $default[ $name ] );
                }
            }
        }

        // kiểm tra biến where, phòng trường hợp các mảng trên không hợp lý,
        //      dẫn tới where chả có gì
        // đã vào tới build query thì phải xét đc 1 số cột nào đó đã có,
        //      và 1 số chưa có giá trị (nhận giá trị mặc định)
        // còn không thì không select staff nào cả
        $where .= (trim($where) != '(' ? ' ) AND (' : ' 0 ) AND (');

        /**
         * @var bool
         * Dùng để check xem từng cụm ( ) đã được thêm phép so sánh nào chưa
         * Nếu có, thêm toán tử AND
         * ngược lại thì thôi
         */
        $flag = false;

        // mớ này duyệt các điều kiện phải có sẵn
        // yêu cầu có đủ tất cả các cột
        // vì vậy dùng toán tử AND để check
        foreach($cols as $name) {
            // đã comment ở trên
            if ( ! isset($default[$name]) ) continue;

            if( in_array( $name, $cols_existed ) ) {
                if (is_array($default[ $name ])){

                    foreach ($default[ $name ] as $k=>$item){
                        if ( ! $flag )
                            $where .= " `" . $name . "` ";
                        else
                            $where .= " AND `" . $name . "` ";

                        $flag = true;
                        $where .= $this->get_type( $item, 1 );
                    }

                } else {

                    if ( ! $flag )
                        $where .= " `" . $name . "` ";
                    else
                        $where .= " AND `" . $name . "` ";

                    $flag = true;
                    $where .= $this->get_type( $default[ $name ], 1 );
                }

            }
        }

        // comment như đoạn trên
        $where .= (trim($where) != '(' ? ' ) ' : ' ( 0 ) ');

        // staff nghỉ rồi thì khỏi liệt kê ra
        $where .= " AND ( off_date IS NULL ) AND ( group_id <> ".BOARD_ID.") ";

        // xét xem có phải cho ASM/Sales Admin xem không
        // nếu phải thì lọc staff theo area
        $userStorage = Zend_Auth::getInstance()->getStorage()->read();

        if ( $for == 'asm' && in_array( $userStorage->group_id, $this->config['asm'] ) ) {
            // lấy theo khu vực mà thằng đó lead
            $QArea            = new Application_Model_Area();
            $QRegionalMarket  = new Application_Model_RegionalMarket();

            $user_id = $userStorage->id;

            if ($userStorage->group_id == SALES_ADMIN_ID) {
                $staff = $this->find($user_id);
                $staff = $staff->current();

                if ($staff) {
                    $region = $staff['regional_market'];
                    $region = $QRegionalMarket->find($region);
                    $region = $region->current();
                }
            }

            $where_2 = $QRegionalMarket->getAdapter()->quoteInto('area_id = ?', $region['area_id']);
            $regional_markets = $QRegionalMarket->fetchAll($where_2);

            $regional_markets_arr = array();

            foreach ($regional_markets as $regional_market) {
                $regional_markets_arr[] = $regional_market->id;
            }

            $where .= (count($regional_markets_arr) ? (" AND regional_market IN (".implode(',', $regional_markets_arr).") ") : '')." AND created_at >= '" . date_sub(date_create(), new DateInterval('P'.$this->config['ttl'].'D'))->format('Y-m-d H:i:s') . "' ";
        }

        $order = " ORDER BY id DESC";

        return "SELECT * FROM " . $this->_name . " WHERE " . $where . $order;
    }

    /**
     * Có câu query rồi thì chạy nó thôi
     */
    function get_by_step($cols_existed = array(), $cols_needed = array(), $for = 'staff')
    {
        $db     = Zend_Registry::get('db');
        $sql    = $this->build_query($cols_existed, $cols_needed, $for);
        return $db->query($sql);
    }

    /**
     * Dùng để build đoạn so sánh với giá trị mặc định
     * Ví dụ:   * mặc định : 0 và negative = 0 -> trả về ' = 0 '
     *          * mặc định : 0 và negative = 1 -> trả về ' <> 0 '
     */
    function get_type($value = '', $negative = 0)
    {
        switch ($value) {
            case 'NULL':
                return ' IS '. ( $negative == 1 ? ' NOT ' : '' ) . ' NULL ';
                break;
            case '0':
                return  ( $negative == 1 ? ' <> ' : '=' ) . ' 0 ';
                break;
            case '':
                return  ( $negative == 1 ? ' <> ' : '=' ) . ' \'\' ';
                break;

            default:
                return '';
                break;
        }
    }

    public function fetchPaginationTransfer($page, $limit, &$total, $params){
        if( isset($params['from']) AND $params['from']){
            $arrFrom = explode('/',$params['from']);
            $params['from'] = $arrFrom[2].'-'.$arrFrom[1].'-'.$arrFrom[0];
        }

        if( isset($params['to']) AND $params['to']){
            $arrFrom = explode('/',$params['to']);
            $params['to'] = $arrFrom[2].'-'.$arrFrom[1].'-'.$arrFrom[0];
        }

        $db = Zend_Registry::get('db');
        $arrCols = array(
            'staff_code'     => 's.code',
            'fullname'       => new Zend_Db_Expr('CONCAT(s.firstname," ",s.lastname)'),
            'transfer_time'  => 'p.from_date',
            'transfer_id'    => 'p.id',
            'info_types'     => new Zend_Db_Expr('GROUP_CONCAT(sl.info_type)'),
            'current_values' => new Zend_Db_Expr('GROUP_CONCAT(sl.current_value)'),
            'old_values'     => new Zend_Db_Expr('GROUP_CONCAT(sl.old_value)'),
        );

        $select = $db->select()
            ->from(array('p'=>'staff_transfer'),$arrCols)
            ->join(array('sl'=>'staff_log_detail'),'p.id = sl.transfer_id',array())
            ->join(array('s'=>'staff'),'s.id = p.staff_id',array())
            ->where('p.note IS NULL')
            ->where('s.off_date IS NULL')
            ->group('p.id')

        ;

        if( isset($params['transfer_id']) AND $params['transfer_id']){
            $select->where('p.id = ?',$params['transfer_id']);
        }

        if( isset($params['from']) AND $params['from']){
            $select->where('p.from_date >= ?',$params['from']);
        }

        if( isset($params['to']) AND $params['to']){
            $select->where('p.from_date <= ?',$params['to']);
        }

        $order_str = '';

        if (isset($params['sort']) and $params['sort']) {
            $collate = ' ';
            $desc    = (isset($params['desc']) and $params['desc'] == 1) ? ' DESC ' : ' ASC ';

            if ($params['sort'] == 'name'){
                $collate   = ' COLLATE utf8_unicode_ci ';
                $order_str .= ' CONCAT(s.firstname, " ",s.lastname) '.$collate . $desc;
            }else {
                $order_str = 'p.`'.$params['sort'] . '` ' . $collate . $desc;
            }

            $select->order(new Zend_Db_Expr($order_str));
        }else{
            $select->order('p.created_at DESC');
        }

        if ($limit)
            $select->limitPage($page, $limit);

        $result = $db->fetchAll($select);
        return $result;

    }

    function genStaffCode($joined_at=null){
        try {
            $joined_at = $joined_at ? $joined_at : date('Y-m-d');
            $firstDay = date('Y-m-01', strtotime($joined_at));

            $db = Zend_Registry::get('db');

            $select = $db->select()
                ->from(array('p'=>'staff'),array('count(id)'))
                /*->where('p.joined_at <= ?', $joined_at)*/
                ->where('p.joined_at >= ?', $firstDay)
            ;

            $total = $db->fetchOne($select);

            $filledUp = str_pad($total + 1, 4, '0', STR_PAD_LEFT);
            return date('ym', strtotime($joined_at . ' 00:00:00')).$filledUp;

        } catch (Exception $e){
            return null;
        }
    }

    function checkIsNotHeadOffice($department_id, $team_id, $title_id){
        $arrNotHeadOffice = unserialize(CONFIG_NOT_HEAD_OFFICE);
        if (isset($arrNotHeadOffice[$department_id]) and !is_array($arrNotHeadOffice[$department_id]) and $arrNotHeadOffice[$department_id] == $department_id)
            return true;

        if (isset($arrNotHeadOffice[$department_id][$team_id][$title_id]))
            return true;

        return false;
    }

    function insertstaffimport($data, $old_name, $new_name) {

        $db = Zend_Registry::get('db');
        $arr = array();
        
        $db->beginTransaction();

        try {
            // Attempt to execute one or more queries:
            $userStorage = Zend_Auth::getInstance()->getStorage()->read();

            for ($i=2;$i<count($data)+1;$i++) {

                // check gender
                switch ($data[$i]['B']) {
                    case "Mr":
                        $gender = '1';
                        break;
                    case "นาง":
                        $gender = '0';
                        break;
                    case "นางสาว":
                        $gender = '0';
                        break;
                    case "Ms":
                        $gender = '0';
                        break;
                    default:
                        $gender = '1';
                        break;
                }

                // check group and title
                $select = $db->select()
                    ->from(array('t1'=>'team'),
                        array(  
                            'title_id'  =>  't1.id',
                            'title_name'=>  't1.name',
                            'team_id'   =>  't2.id',
                            'team_name' =>  't2.name',
                            'dept_id'   =>  't3.id',
                            'dept_name' =>  't3.name',
                        )
                    )
                    ->join(array('t2'=>'team'), 't1.parent_id = t2.id',    array())
                    ->join(array('t3'=>'team'), 't2.parent_id = t3.id',    array())
                    ->where('t1.name = ?', $data[$i]['I'])
                    ->where('t2.name = ?', $data[$i]['H'])
                    ->where('t3.name = ?', $data[$i]['G']);
                $result = $db->fetchRow($select);

                if ($result) {
                    $departmentid   = $result['dept_id'];
                    $teamid         = $result['team_id'];
                    $titleid        = $result['title_id'];
                    $title_name     = $result['title_name'];

                    switch ($title_name) {
                        case "PC":
                            $groupid = '4';
                            break;
                        case "SALE":
                            $groupid = '9';
                            break;
                        case "ASM":
                            $groupid = '5';
                            break;
                        case "PCM":
                            $groupid = '21';
                            break;
                        case "RD":
                            $groupid = '28';
                            break;
                        default:
                            $groupid = '0';
                            break;
                    }
                } else {
                    throw new Exception('ข้อมูล Department / Team / Title ของพนักงาน '.$data[$i]['A'].' '.$data[$i]['C'].' '.$data[$i]['D'].' ไม่ถูกต้องค่ะ!');
                }

                // check regional_market 
                $select = $db->select()
                    ->from(array('r'=>'regional_market'),
                        array(  
                            'province_id'   =>  'r.id',
                            'province_name' =>  'r.name',
                            'area_id'       =>  'a.id',
                            'area_name'     =>  'a.name',
                        )
                    )
                    ->join(array('a'=>'area'), 'r.area_id = a.id', array())
                    ->where('r.name = ?', $data[$i]['K'])
                    ->where('a.name = ?', $data[$i]['J']);
                $result = $db->fetchRow($select);

                if ($result) {
                    $regional_market = $result['province_id'];
                    $area_id         = $result['area_id'];
                } else {
                    throw new Exception('ข้อมูล Province / Area ของพนักงาน '.$data[$i]['A'].' '.$data[$i]['C'].' '.$data[$i]['D'].' ไม่ถูกต้องค่ะ!');
                }

                if (isset($data[$i]['Q']) && $data[$i]['Q']) { 
                    switch ($data[$i]['Q']) {
                        case "XXS"  : $shirt_size = 1; break;
                        case "SS"   : $shirt_size = 2; break;
                        case "S"    : $shirt_size = 3; break;
                        case "M"    : $shirt_size = 4; break;
                        case "L"    : $shirt_size = 5; break;
                        case "XL"   : $shirt_size = 6; break;
                        case "2XL"  : $shirt_size = 7; break;
                        case "3XL"  : $shirt_size = 8; break;
                        case "5XL"  : $shirt_size = 9; break;
                        default: 
                            throw new Exception('ข้อมูล Size เสื้อของพนักงาน '.$data[$i]['A'].' '.$data[$i]['C'].' '.$data[$i]['D'].' ไม่ถูกต้องค่ะ!');
                            break;
                    }
                } else {
                    throw new Exception('ข้อมูล Size เสื้อของพนักงาน '.$data[$i]['A'].' '.$data[$i]['C'].' '.$data[$i]['D'].' ไม่ถูกต้องค่ะ!');
                }

                $email    = $data[$i]['A'].'@oppolaos.com';

                // change date format
                //$tmp_arr = explode('-',$data[$i]['L']);
                //$temp = $tmp_arr[2] ."-". $tmp_arr[1] ."-". $tmp_arr[0];
                //$join_at = date('Y-m-d',strtotime($temp));
                $join_at = date('Y-m-d',strtotime($data[$i]['L']));
                $dob = date('Y-m-d',strtotime($data[$i]['P']));

                if (isset($data[$i]['U']) && $data[$i]['U']) { $first_check_in = date('Y-m-d',strtotime($data[$i]['U'])); }
                else { $first_check_in = date('Y-m-d H:i:s'); }

                /*
                $tmp_arr2 = explode('-',$data[$i]['P']);
                $temp2 = $tmp_arr2[2] ."-". $tmp_arr2[1] ."-". $tmp_arr2[0];
                $dob = date('Y-m-d',strtotime($temp2));
                */

                if (isset($data[$i]['T']) && $data[$i]['T']) { $public_id = $data[$i]['T']; }
                else { $public_id = null; }

                $arr = array( 
                    'code'              => $data[$i]['A'],
                    'firstname'         => $data[$i]['C'],
                    'lastname'          => $data[$i]['D'],
                    'firstname_en'      => $data[$i]['E'],
                    'lastname_en'       => $data[$i]['F'],
                    'email'             => $email,
                    'password'          => md5($data[$i]['A']), // default password : staff code
                    'department'        => $departmentid,
                    'team'              => $teamid,
                    'title'             => $titleid,
                    'dob'               => $dob,
                    'group_id'          => $groupid,
                    'company_id'        => '1',
                    'joined_at'         => $join_at,
                    'gender'            => $gender,
                    'phone_number'      => str_replace('-','',$data[$i]['O']),
                    'regional_market'   => $regional_market,
                    'created_by'        => $userStorage->id,
                    'created_at'        => date('Y-m-d H:i:s'),
                    'shirt_size'        => $shirt_size,
                    'public_id'         => $public_id,
                );
                
                $db->insert('staff', $arr);
                $id = $db->lastInsertId();

                // Insert First Check In of PC First Training 
                if ($groupid == 4) {

                    $arr2 = array(
                        'store_id'      => 0,
                        'staff_id'      => $id,
                        'action_id'     => 1,
                        'check_in'      => $first_check_in,
                        'created_at'    => $first_check_in,
                        'updated_at'    => $first_check_in,
                        'filepic'       => $public_id.".jpg",
                    );

                    $db->insert('pc_check_in_log', $arr2);
                }

                // Insert Data to Trade Marketing System
                $toTrade = array(
                    'firstname'    => $data[$i]['C'],
                    'lastname'     => $data[$i]['D'],
                    'phone_number' => str_replace('-','',$data[$i]['O']),
                    'gender'       => $gender,
                    'email'        => $email,
                    'group_id'     => $groupid,
                    'area_id'      => $area_id,
                    'username'     => $data[$i]['A'],
                    'password'     => md5($data[$i]['A']),
                    'created_at'   => date('Y-m-d H:i:s'),
                    'created_by'   => '1',
                    'status'       => '1',
                );
               
                // $QWS = new Application_Model_WS();    
                // $QWS->_insertToTrade($toTrade);

/*                foreach($toTrade as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
                $data2 = rtrim($fields_string, '&');*/
/*
                $ch = curl_init();

                //curl_setopt($ch, CURLOPT_URL,"http://trade.oppo.in.th/trade/wsinsertstaff");
                curl_setopt($ch, CURLOPT_URL,"http://tmk.oppo.in.th/api/insert-staff-to-trade");
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data2);

                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $server_output = curl_exec($ch);
                curl_close ($ch);

                if ($server_output != 1) { throw new Exception("Failed on TMS! => ". $server_output); }
*/
            }

            // record upload log
            $QFileLog = new Application_Model_FileUploadLog();
            
            $cnt = $i-2;
            $data = array(
                'staff_id'          => $userStorage->id,
                'folder'            => '\public\upload_staff',
                'filename'          => $new_name,
                'type'              => 'staff upload',
                'real_file_name'    => $old_name,
                'uploaded_at'       => time(),
                'total'             => $cnt,
                'succeed'           => $cnt
            );

            $log_id = $QFileLog->insert($data);

            // If all succeed, commit the transaction and all changes
            // are committed at once.
            $db->commit();
            return array('result' => 'success', 'cnt' => $cnt);
         
        } catch (Exception $e) {
            // If any of the queries failed and threw an exception,
            // we want to roll back the whole transaction, reversing
            // changes made in the transaction, even those that succeeded.
            // Thus all changes are committed together, or none are.

            $db->rollBack();
            return array('result' => 'fail', 'msg' => $e->getMessage());
        }
   
    }

    function getStaffArea($staff_id) {

        $db = Zend_Registry::get('db');

        $get = array(
            'staff_id'          => 's.id',
            'staff_area_id'     => 'a.id',
            'staff_area_name'   => 'a.name'
        );

        $select = $db->select()
            ->from(array('s' => 'staff'), $get)
            ->join(array('rm' => 'regional_market'), 's.regional_market = rm.id' ,array())
            ->join(array('a' => 'area'), 'rm.area_id = a.id', array())
            ->where('s.id = ?', $staff_id);

        $result = $db->fetchRow($select);
        return $result;

    }

    // PC Level 
    function getStaffPcLevel($params) {

        $db = Zend_Registry::get('db');

        $select = $db->select()
            ->from(array('sta' => $this->_name),
                    array('staff_id'=> 'sta.id','staff_code'=> 'sta.code','sta.firstname','sta.lastname','sta.created_at','sta.group_id'))
            ->join(array('ss' => 'store_staff'),'ss.staff_id = sta.id', array())
            ->join(array('st' => 'store'), 'ss.store_id = st.id', array('store_id' => 'st.id', 'store_name' => 'st.name'))
            ->join(array('o' => 'org'), 'o.org_id = st.org_dealer', array('store_type' => 'o.org_name'))
            ->join(array('rm' => 'regional_market'), 'st.regional_market = rm.id', array('regional_market_id' => 'rm.id','regional_market_name'=> 'rm.name'))
            ->join(array('ar' => 'area'), 'ar.id = rm.area_id', array('area_name' => 'ar.name','area_id' => 'ar.id'))
            ->join(array('sm' => 'store_market'), 'st.id=sm.store_id', array())
            ->join(array('mn' => 'market_name'), 'sm.market_name_id=mn.id', array('market_name'=>'mn.name'))
            ->join(array('mt' => 'market_type'), 'mt.id=mn.market_type_id', array('market_type'=>'mt.name'))
            ->joinLeft(array('pcl' => 'pc_level'), 'pcl.id = sta.staff_level', array('level_id' => 'pcl.id','pcl.level_name'))
            ->where('sta.group_id = ?', '4');  

        if ( isset($params['staff_code']) && $params['staff_code'] ) {
            $select->where('sta.code = ?', $params['staff_code']);
        }

        if ( isset($params['name']) && $params['name'] ) {
            $select->where('sta.firstname LIKE ?', '%'.$params['name'].'%');
            $select->orWhere('sta.lastname LIKE ?', '%'.$params['name'].'%');
        }

        if ( isset($params['phone_number']) && $params['phone_number'] ) {
            $select->where('sta.phone_number LIKE ?', '%'.$params['phone_number'].'%');
        }

        if ( isset($params['area_id']) && $params['area_id'] ) {
            $select->where('ar.id IN (?)', $params['area_id']);
        }

        if (isset($params['regional_market']) and $params['regional_market']) {
            if (is_array($params['regional_market']) && count($params['regional_market']) > 0) {
                $select->where('rm.id IN (?)', $params['regional_market']);
            } else {
                $select->where('1=0', 1);
            }
        }

        if (isset($params['district']) and $params['district'])
            $select->where('st.district IN (?)', $params['district']);

        if (isset($params['store']) and $params['store'])
            $select->where('st.id = ?', $params['store']);

        if (isset($params['store_type']) and $params['store_type'])
            $select->where('o.org_id = ?', $params['store_type']);

        if ( isset($params['asm']) && $params['asm'] ) {
                $QAsm = new Application_Model_Asm();
                $list_regions = $QAsm->get_cache($params['asm']);
                $list_regions = isset($list_regions['district']) && is_array($list_regions['district']) ? $list_regions['district'] : array();

                if (count($list_regions) > 0)
                    $select->where( 'st.district IN (?)', $list_regions);
                else
                    $select->where('1=0', 1);
            }

        $select->group('sta.id');
        $select->group('st.id');
        $select->order(array('ss.staff_id'));

        // echo $select ;exit();
        $result = $db->fetchAll($select);

        return $result;

    }

    function fetchPagination_StaffResign($page, $limit, &$total, $params) {

        $d1 = explode('/', $params['from']);
        $from = $d1[2].'-'.$d1[1].'-'.$d1[0];

        $d2 = explode('/', $params['to']);
        $to = $d2[2].'-'.$d2[1].'-'.$d2[0];

        $db = Zend_Registry::get('db');

        $get = array(
            'staff_id'      => new Zend_Db_Expr('SQL_CALC_FOUND_ROWS s.id'),
            'staff_code'    => 's.code',
            'staff_name'    => new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)"),
            'staff_group'   => 'g.name',
            'staff_phone'   => 's.phone_number',
            'joined_at'     => 's.joined_at',
            'off_date'      => 's.off_date',
            'staff_area'    => 'a.name',
            'grand_area'    => 'ga.name',
        );

        $select = $db->select()
            ->from(array('s'  => 'staff'), $get)
            ->join(array('g'  => 'group')           , 's.group_id = g.id'           , array())
            ->join(array('rm' => 'regional_market') , 's.regional_market = rm.id'   , array())
            ->join(array('a'  => 'area')            , 'rm.area_id = a.id'           , array())
            ->join(array('gal'=> 'grand_area_list') , 'a.id = gal.area'             , array())
            ->join(array('ga' => 'grand_area')      , 'gal.grand_area_id = ga.id'   , array())
            ->where('s.off_date >= ?', $from)
            ->where('s.off_date <= ?', $to)
            ->order('s.off_date DESC');

        // Add Filter Grand Area [Table]
        if (isset($params['grand_area_id']) && $params['grand_area_id']) {
            if (is_array($params['grand_area_id']) && count($params['grand_area_id']))
                $select->where('ga.id IN (?)', $params['grand_area_id']);
            elseif (is_numeric($params['grand_area_id']))
                $select->where('ga.id = ?', intval($params['grand_area_id']));
            else
                $select->where('1=0', 1);
        }

        // Add Filter Area
        if (isset($params['area_id']) && $params['area_id']) {
            if (is_array($params['area_id']) && count($params['area_id']))
                $select->where('rm.area_id IN (?)', $params['area_id']);
            elseif (is_numeric($params['area_id']))
                $select->where('rm.area_id = ?', intval($params['area_id']));
            else
                $select->where('1=0', 1);
        }

        // Add Filter Staff Group
        if (isset($params['group_id']) && $params['group_id']) {
            if (is_array($params['group_id']) && count($params['group_id']))
                $select->where('s.group_id IN (?)', $params['group_id']);
            elseif (is_numeric($params['group_id']))
                $select->where('s.group_id = ?', intval($params['group_id']));
            else
                $select->where('1=0', 1);
        }

        // Permission for ASM / Sale Admin / Trainer
        if ( isset($params['asm']) && $params['asm'] ) {
            $QAsm = new Application_Model_Asm();
            $list_regions = $QAsm->get_cache($params['asm']);
            $list_regions = isset($list_regions['province']) && is_array($list_regions['province']) ? $list_regions['province'] : array();

            if (count($list_regions) > 0)
                $select->where( 's.regional_market IN (?)', $list_regions);
            else
                $select->where('1=0', 1);

            if ( in_array($params['asm_group'], array(TRAINING_TEAM_ID,36)) ) { $select->where('s.group_id = ?', PGPB_ID); }
        }

        if ($limit)
            $select->limitPage($page, $limit);

        //print_r($params);
        //echo $select;
        $result = $db->fetchAll($select);

        if ($limit)
            $total = $db->fetchOne("select FOUND_ROWS()");

        return $result;
    }


    function getStaffCreateHr($staff_code) {

        $db = Zend_Registry::get('db');

        $get = array(
            'staff_code'    => 's.code',
            'gender'        => 's.gender',
            'firstname'     => 's.firstname',
            'lastname'      => 's.lastname',
            'shirt_size'    => 's.shirt_size',
            'area_id'       => 'a.id',
            'area_name'     => 'a.name',
            'province_id'   => 'rm.id',
            'province_name' => 'rm.name',
            'citizen_id'    => new Zend_Db_Expr("COALESCE(s.public_id, NULL)"), 
            'tel_number'    => 's.phone_number',
            'group_id'      => 'g.id',
            'group_name'    => 'g.name',
            'start_date'    => 's.joined_at',

            'prefix_id'     => 's.prefix_id',
            'firstname_en'  => 's.firstname_en',
            'lastname_en'   => 's.lastname_en',

            'area_type'     => new Zend_Db_Expr("(CASE WHEN a.name LIKE 'BKK%' THEN 'BKK' ELSE 'UPC' END)"),

            'password'      => 's.password',
        );

        $select = $db->select()
            ->from(array('s' => 'staff'), $get)
            ->join(array('g' => 'group')            , 's.group_id = g.id'           , array())
            ->join(array('rm'=> 'regional_market')  , 's.regional_market = rm.id'   , array())
            ->join(array('a' => 'area')             , 'rm.area_id = a.id'           , array())
            ->where('s.code = ?', $staff_code);

        //echo $select;
        $result = $db->fetchRow($select);
        return json_encode($result);

    }

    public function updateGroupByHrChangePosition() {

        set_time_limit(0);
        ini_set('memory_limit', '-1');
        error_reporting(~E_ALL);
        ini_set("display_error", '0');

        $db = Zend_Registry::get('db');
        $db->beginTransaction();

        echo "Getting Start..."."\r\n";

        $QStore = new Application_Model_Store();

        try {

            echo "Start Transaction..."."\r\n";  

            // Get Staff List who has change position approved
            $get = array(
                'staff_id'          => 's.id',
                'staff_code'        => 's.code',
                'staff_name'        => new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)"),
                'current_group_id'  => 's.group_id',

                'regional_market'   => 's.regional_market',
                'department'        => 's.department',
                'team'              => 's.team',
                'title'             => 's.title',

                'new_store_id'      => 'sr.new_store_id',

                'new_position_id'   => 'cpm.position_code',
                'new_group_id'      => 'cpm.catty_group_id',

                'effective_date'    => new Zend_Db_Expr("(CASE WHEN sr.effective_date_position IS NULL THEN sr.effective_date_down_position ELSE sr.effective_date_position END)"),

            );

            $select = $db->select()
                ->from(array('s'   => 'staff'), $get)
                ->join(array('sr'  => HR_DB.'.staff_request')       , 's.code = sr.staff_code', array())
                ->join(array('cpm' => HR_DB.'.catty_position_map')  , "cpm.position_code = (CASE WHEN sr.new_position IS NULL THEN sr.down_position ELSE sr.new_position END)" , array())
                ->where('sr.hr_approve_status = ?', 1)
                ->where('s.group_id <> cpm.catty_group_id', 1);

            //echo $select."\r\n";
            $result = $db->fetchAll($select);

            // print_r($result); echo "\r\n";
            
            if (!empty($result)) {

                $data = array();
                for ($i=0;$i<count($result);$i++) {

                    echo "[".$result[$i]['staff_code']."] ".$result[$i]['staff_name'];

                    if ( strtotime($result[$i]['effective_date']) <= strtotime(date('Y-m-d')) ) {

                        switch ($result[$i]['new_group_id']) {
                            case 1:  $dept = 153; $team = 14;  $title = 249; break;
                            case 4:  $dept = 152; $team = 75;  $title = 182; break;
                            case 5:  $dept = 152; $team = 75;  $title = 179; break;
                            case 7:  $dept = 154; $team = 15;  $title = 226; break;
                            case 8:  $dept = 157; $team = 138; $title = 196; break;
                            case 9:  $dept = 152; $team = 75;  $title = 183; break;
                            case 10: $dept = 154; $team = 15;  $title = 229; break;
                            case 12: $dept = 152; $team = 75;  $title = 191; break;
                            case 16: $dept = 152; $team = 75;  $title = 181; break;
                            case 17: $dept = 152; $team = 133; $title = 175; break;
                            case 19: $dept = 152; $team = 148; $title = 167; break;
                            case 20: $dept = 152; $team = 131; $title = 268; break;
                            case 24: $dept = 149; $team = 9;   $title = 192; break;
                            case 26: $dept = 154; $team = 15;  $title = 227; break;
                            case 27: $dept = 152; $team = 75;  $title = 180; break;
                            case 28: $dept = 152; $team = 75;  $title = 293; break;
                            case 30: $dept = 152; $team = 75;  $title = 274; break;
                            case 31: $dept = 152; $team = 75;  $title = 183; break;
                            case 33: $dept = 152; $team = 75;  $title = 274; break;
                            case 34: $dept = 152; $team = 75;  $title = 274; break;
                            case 35: $dept = 152; $team = 75;  $title = 294; break;
                            case 36: $dept = 152; $team = 133; $title = 174; break;
                            case 37: $dept = 152; $team = 131; $title = 168; break;
                            case 38: $dept = 152; $team = 131; $title = 295; break;
                            case 39: $dept = 152; $team = 75;  $title = 191; break;
                            default: $dept = 0;   $team = 0;   $title = 0;   break;
                        }

                        $data = array(
                            'group_id'   => $result[$i]['new_group_id'],
                            'department' => $dept,
                            'team'       => $team,
                            'title'      => $title,

                            'updated_by' => 18,
                            'updated_at' => date('Y-m-d H:i:s'),
                        );

                        echo "<br/><pre>"; print_r($data);

                        // Check Change Area
                        if (!is_null($result[$i]['new_store_id'])) {

                            $store = $QStore->find($result[$i]['new_store_id']);
                            $store = $QStore->current();

                            print_r($store);

                            $data['regional_market'] = $store['regional_market'];

                            // Add Regional Market Log 
                            if ($result[$i]['regional_market'] != $data['regional_market']) { 

                                $rm_log = array(
                                    'staff_id'      => $result[$i]['staff_id'],
                                    'before_id'     => $result[$i]['regional_market'],
                                    'after_id'      => $data['regional_market'],
                                    'created_by'    => 18,
                                    'created_at'    => date('Y-m-d H:i:s')
                                );

                                // print_r($rm_log); 
                                $db->insert("staff_regional_market_log", $rm_log);
                            }

                        }
                        echo "AAA";    die;
                        $where = array();
                        $where['id = ?'] = $result[$i]['staff_id'];

                        // print_r($data); echo "\r\n";
                        $db->update('staff', $data, $where);

                        // Add Title Log 
                        if ($result[$i]['title'] != $title) { 

                            $title_log = array(
                                'staff_id'      => $result[$i]['staff_id'],
                                'before_id'     => $result[$i]['title'],
                                'after_id'      => $title,
                                'created_by'    => 18,
                                'created_at'    => date('Y-m-d H:i:s')
                            );

                            // print_r($title_log); echo "\r\n";
                            $db->insert("staff_title_log", $title_log);
                        }

                        // Add Group Log 
                        if ($result[$i]['current_group_id'] != $result[$i]['new_group_id']) { 

                            $group_log = array(
                                'staff_id'      => $result[$i]['staff_id'],
                                'before_id'     => $result[$i]['current_group_id'],
                                'after_id'      => $result[$i]['new_group_id'],
                                'created_by'    => 18,
                                'created_at'    => date('Y-m-d H:i:s')
                            );

                            // print_r($group_log); echo "\r\n";
                            $db->insert("staff_group_log", $group_log);
                        }

                        echo " : Update Successfully!"."\r\n";

                    } else {

                        echo " : Effective Date Not Today"."\r\n";

                    }
                    
                }

            }

            $db->commit();
            echo "End Transaction..."."\r\n";

        } catch (Exception $e) {
            $db->rollBack();
            echo "Fail! : ".$e;
        }

        exit;
    }

    function getPcLevelByArea($params){

        $db = Zend_Registry::get('db');

        $get = array(
            'area_id'       => 'a.id',
            'area_name'     => 'a.name',
            'level_senior'  => new Zend_Db_Expr("SUM(CASE WHEN s.staff_level = 2 THEN 1 ELSE 0 END)"),
            'level_advance' => new Zend_Db_Expr("SUM(CASE WHEN s.staff_level = 3 THEN 1 ELSE 0 END)"),
            'level_master'  => new Zend_Db_Expr("SUM(CASE WHEN s.staff_level = 4 THEN 1 ELSE 0 END)"),
            'total_pc'      => new Zend_Db_Expr("COUNT(s.id)"),
        );

        $select = $db->select()
            ->from(array('a'  => 'area'), $get)
            ->join(array('rm' => 'regional_market') , 'a.id = rm.area_id'           , array())
            ->join(array('s'  => 'staff')           , 'rm.id = s.regional_market'   , array())
            ->where('s.off_date IS NULL', 0)
            ->where('s.group_id = ?', PGPB_ID)
            ->group('a.id')
            ->order('a.name ASC');

        // Add Filter Area
        if (isset($params['area_id']) && $params['area_id']) {
            if (is_array($params['area_id']) && count($params['area_id']))
                $select->where('a.id IN (?)', $params['area_id']);
            elseif (is_numeric($params['area_id']))
                $select->where('a.id = ?', intval($params['area_id']));
            else
                $select->where('1=0', 1);
        }

        // check ermission ASM, ASM Stand by, Sale Admin, Traning
        if ( isset($params['asm']) && $params['asm'] ) {
            $QAsm = new Application_Model_Asm();
            $list_regions = $QAsm->get_cache($params['asm']);
            $list_regions = isset($list_regions['province']) && is_array($list_regions['province']) ? $list_regions['province'] : array();

            if (count($list_regions) > 0)
                $select->where( 'rm.id IN (?)', $list_regions);
            else
                $select->where('1=0', 1);
        }

        $result = $db->fetchAll($select);
        return $result;
    }

    function getPcLevelByAreaDetails($params){

        $db = Zend_Registry::get('db');

        $get = array(
            'area_id'       => 'a.id',
            'area_name'     => 'a.name',
            'staff_id'      => 's.id',
            'staff_code'    => 's.code',
            'staff_name'    => new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)"),
            'pc_stand_by'   => new Zend_Db_Expr("(CASE WHEN s.pc_stand_by = 1 THEN 'Yes' ELSE 'No' END)"),
            'level_status'  => 'pl.level_name',
        );

        $select = $db->select()
            ->from(array('a'  => 'area'), $get)
            ->join(array('rm' => 'regional_market') , 'a.id = rm.area_id'           , array())
            ->join(array('s'  => 'staff')           , 'rm.id = s.regional_market'   , array())
            ->joinLeft(array('pl' => 'pc_level')    , 's.staff_level = pl.id'       , array())
            ->where('s.off_date IS NULL', 0)
            ->where('s.group_id = ?', PGPB_ID)
            // ->where('s.staff_level IN (?)', array(2,3,4))
            ->where('a.id = ?', $params['area_id'])
            ->order(array('s.staff_level DESC', 's.code ASC'));

        if ( $params['type_id'] == 1 ) {
            $select->where('s.staff_level IN (?)', array(2,3,4));
        } 

        // echo $select;
        $result = $db->fetchAll($select);
        return $result;
    }

    function getPcLevelInfo($params){

        $db = Zend_Registry::get('db');

        $get = array(
            'flag_id'       => new Zend_Db_Expr("'1'"),
            'flag_name'     => new Zend_Db_Expr("'PC Level'"),
            'flag_date'     => 's.set_level_at',

            'staff_id'      => 's.id',
            'staff_code'    => 's.code',
            'staff_name'    => new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)"),
            'staff_level'   => 'pl.level_name',

            'set_by_code'   => 's2.code',
            'set_by_name'   => new Zend_Db_Expr("CONCAT(s2.firstname, ' ', s2.lastname)"),
            'set_by_group'  => 'g2.name',
        );

        $select = $db->select()
            ->from(array('s'  => 'staff'), $get)
            ->join(array('pl' => 'pc_level')    , 's.staff_level = pl.id'   , array())
            ->join(array('s2' => 'staff')       , 's.set_level_by = s2.id'  , array())
            ->join(array('g2' => 'group')       , 's2.group_id = g2.id'     , array())
            ->where('s.id = ?', $params['staff_id']);

        // echo $select;
        $result = $db->fetchAll($select);
        return $result;
    }

    function getShopBindingByPC($params){

        $db = Zend_Registry::get('db');

        $get = array(
            'flag_id'       => new Zend_Db_Expr("'2'"),
            'flag_name'     => new Zend_Db_Expr("'Shop Binding'"),
            'flag_date'     => new Zend_Db_Expr("FROM_UNIXTIME(ssl.joined_at)"),

            'st_id'         => 'st.id',
            'st_name'       => 'st.name',
            'st_type'       => 'o.org_name',
            'released_at'   => new Zend_Db_Expr("FROM_UNIXTIME(ssl.released_at)"),
        );

        $select = $db->select()
            ->from(array('ssl' => 'store_staff_log'), $get)
            ->join(array('st'  => 'store')  , 'ssl.store_id = st.id'    , array())
            ->join(array('o'   => 'org')    , 'st.org_dealer = o.org_id', array())
            ->where('ssl.is_leader = ?', 0)
            ->where('ssl.staff_id = ?', $params['staff_id']);

        // echo $select;
        $result = $db->fetchAll($select);
        return $result;
    }

    function getShopCheckByPC($params){

        $db = Zend_Registry::get('db');

        $get = array(
            'flag_id'    => new Zend_Db_Expr("'3'"),
            'flag_name'  => new Zend_Db_Expr("'Shop Check'"),
            'flag_date'  => 'cl.created_at',

            'topic_name' => 'ct.name',
            'sc_status'  => 'cl.status',
        );

        $select = $db->select()
            ->from(array('cl' => 'check_list_data_log'), $get)
            ->join(array('ct' => 'check_list_topic'), 'ct.id = cl.topic_id', array())
            ->where('cl.pc_id = ?', $params['staff_id']);

        // echo $select;
        $result = $db->fetchAll($select);
        return $result;
    }

    function getETestByPC($params){

        $db = Zend_Registry::get('db');

        $get = array(
            'month_flag'  => new Zend_Db_Expr("DATE_FORMAT(qa.created_at, '%Y-%m-15')"),
            'score_total' => new Zend_Db_Expr("COUNT(qc.id)"),
            'score_pass'  => new Zend_Db_Expr("COUNT(CASE WHEN qc.correct = 'Y' THEN qc.id END)"),
        );

        $select = $db->select()
            ->from(array('qa' => 'questions_answer_log'), $get)
            ->join(array('q'  => 'questions')           , 'qa.question_id = q.id'   , array())
            ->join(array('qh' => 'questions_header')    , 'q.head_id = qh.id'       , array())
            ->join(array('qc' => 'questions_choice')    , 'qa.choice_id = qc.id'    , array())
            ->where('qa.staff_id = ?', $params['staff_id'])
            ->group('DATE(qa.created_at)');

        if ( isset($params['from']) && $params['from'] ) { $select->where('qa.created_at >= ?', $params['from']); }
        if ( isset($params['to']) && $params['to'] ) { $select->where('qa.created_at <= ?', $params['to']); }

        $main_get = array(
            'flag_id'           => new Zend_Db_Expr("'4'"),
            'flag_name'         => new Zend_Db_Expr("'Monthly E-Test'"),
            'flag_date'         => 'AAA.month_flag',

            'sum_total_score'   => new Zend_Db_Expr("SUM(AAA.score_total)"),
            'sum_answer_score'  => new Zend_Db_Expr("SUM(AAA.score_pass)"),
            'question_pass'     => new Zend_Db_Expr("SUM(CASE WHEN AAA.score_total = AAA.score_pass THEN 1 ELSE 0 END)"),
            'question_total'    => new Zend_Db_Expr("COUNT(AAA.month_flag)"),
        ); 

        $main_select = $db->select()
            ->from(array('AAA' => $select), $main_get)
            ->group('AAA.month_flag')
            ->order('AAA.month_flag ASC');

        // echo $main_select;
        $result = $db->fetchAll($main_select);
        return $result;
    }

    function getPcLevelByChannel($params){

        $db = Zend_Registry::get('db');

        $get = array(
            'staff_id'    => 's.id',
            'staff_level' => 's.staff_level',
            'org_dealer'  => 'st.org_dealer',
        );

        $select = $db->select()
            ->from(array('s' => 'staff'), $get)
            ->join(array('ss'  => 'store_staff'), 's.id = ss.staff_id', array())
            ->join(array('st' => 'store'), 'ss.store_id = st.id', array())
            ->join(array('rm' => 'regional_market'), 'st.regional_market = rm.id', array())
            ->where('s.off_date IS NULL', 0)
            ->where('s.group_id = ?', PGPB_ID)
            ->group('s.id');

        if (isset($params['option']) && $params['option']) {
            if ($params['option'] == 1) {
                $select->having(new Zend_Db_Expr("COUNT(ss.staff_id) <= 1"));
            } else {
                $select->having(new Zend_Db_Expr("COUNT(ss.staff_id) > 1"));
            }
        }

//        // check ermission ASM, ASM Stand by, Sale Admin, Traning
        if ( isset($params['asm']) && $params['asm'] ) {
            $QAsm = new Application_Model_Asm();
            $list_regions = $QAsm->get_cache($params['asm']);
            $list_regions = isset($list_regions['province']) && is_array($list_regions['province']) ? $list_regions['province'] : array();

            if (count($list_regions) > 0)
                $select->where( 'rm.id IN (?)', $list_regions);
            else
                $select->where('1=0', 1);
        }

        $org_type = "CASE ";
        $org_type .= "WHEN o.org_id IN(2,4,6,7,9,11,13,14,15,17,24,25,29,30,35,36) THEN 'KA'";
        $org_type .= "WHEN o.org_id IN(3,5,8,10,12) THEN 'OPRT'";
        $org_type .= "WHEN o.org_id IN(16,18,21,22,23,31,33,34,37,39,40) THEN 'Brandshop'";
        $org_type .= "WHEN o.org_id IN(27,32,38) THEN 'KR'";
        $org_type .= "ELSE 'Dealer'";
        $org_type .= " END";

        $main_get = array(
            'org_type'      => new Zend_Db_Expr($org_type),
            'level_senior'  => new Zend_Db_Expr("SUM(CASE WHEN AAA.staff_level = 2 THEN 1 ELSE 0 END)"),
            'level_advance' => new Zend_Db_Expr("SUM(CASE WHEN AAA.staff_level = 3 THEN 1 ELSE 0 END)"),
            'level_master'  => new Zend_Db_Expr("SUM(CASE WHEN AAA.staff_level = 4 THEN 1 ELSE 0 END)"),
            'total_pc'      => new Zend_Db_Expr("COUNT(AAA.staff_id)"),
        );

        $main_select = $db->select()
            ->from(array('o' => 'org'), $main_get)
            ->join(array('AAA'  => $select), 'AAA.org_dealer = o.org_id', array())
            ->group('org_type')
            ->order('org_type');

        $result = $db->fetchAll($main_select);

        return $result;
    }

    function getPcLevelByChannelExport($params){

        $db = Zend_Registry::get('db');

        $get = array(
            'area_id'     => 'rm.area_id',
            'staff_id'    => 's.id',
            'staff_level' => 's.staff_level',
            'org_dealer'  => 'st.org_dealer',
        );

        $select = $db->select()
            ->from(array('s' => 'staff'), $get)
            ->join(array('ss'  => 'store_staff'), 's.id = ss.staff_id', array())
            ->join(array('st' => 'store'), 'ss.store_id = st.id', array())
            ->join(array('rm' => 'regional_market'), 'st.regional_market = rm.id', array())
            ->where('s.off_date IS NULL', 0)
            ->where('s.group_id = ?', PGPB_ID)
            ->group('s.id');

        if (isset($params['option']) && $params['option']) {
            if ($params['option'] == 1) {
                $select->having(new Zend_Db_Expr("COUNT(ss.staff_id) <= 1"));
            } else {
                $select->having(new Zend_Db_Expr("COUNT(ss.staff_id) > 1"));
            }
        }

//        // check ermission ASM, ASM Stand by, Sale Admin, Traning
        if ( isset($params['asm']) && $params['asm'] ) {
            $QAsm = new Application_Model_Asm();
            $list_regions = $QAsm->get_cache($params['asm']);
            $list_regions = isset($list_regions['province']) && is_array($list_regions['province']) ? $list_regions['province'] : array();

            if (count($list_regions) > 0)
                $select->where( 'rm.id IN (?)', $list_regions);
            else
                $select->where('1=0', 1);
        }

        $org_type = "CASE ";
        $org_type .= "WHEN o.org_id IN(2,4,6,7,9,11,13,14,15,17,24,25,29,30,35,36) THEN 'KA'";
        $org_type .= "WHEN o.org_id IN(3,5,8,10,12) THEN 'OPRT'";
        $org_type .= "WHEN o.org_id IN(16,18,21,22,23,31,33,34,37,39,40) THEN 'Brandshop'";
        $org_type .= "WHEN o.org_id IN(27,32,38) THEN 'KR'";
        $org_type .= "ELSE 'Dealer'";
        $org_type .= " END";

        $main_get = array(
            'area_name'     => 'a.name',
            'org_name'      => 'o.org_name',
            'org_type'      => new Zend_Db_Expr($org_type),
            'level_senior'  => new Zend_Db_Expr("SUM(CASE WHEN AAA.staff_level = 2 THEN 1 ELSE 0 END)"),
            'level_advance' => new Zend_Db_Expr("SUM(CASE WHEN AAA.staff_level = 3 THEN 1 ELSE 0 END)"),
            'level_master'  => new Zend_Db_Expr("SUM(CASE WHEN AAA.staff_level = 4 THEN 1 ELSE 0 END)"),
            'total_pc'      => new Zend_Db_Expr("COUNT(AAA.staff_id)"),
        );

        $main_select = $db->select()
            ->from(array('o' => 'org'), $main_get)
            ->join(array('AAA'  => $select), 'AAA.org_dealer = o.org_id', array())
            ->joinLeft(array('a' => 'area'), 'AAA.area_id = a.id', array())
            ->group('o.org_id')
            ->group('AAA.area_id')
            ->order('area_name')
            ->order('org_name');

        // Add Filter Area
        if (isset($params['area_id']) && $params['area_id']) {
            if (is_array($params['area_id']) && count($params['area_id']))
                $main_select->where('a.id IN (?)', $params['area_id']);
            elseif (is_numeric($params['area_id']))
                $main_select->where('a.id = ?', intval($params['area_id']));
            else
                $main_select->where('1=0', 1);
        }

        $result = $db->fetchAll($main_select);

        return $result;
    }
    function get_sales(){
        // $cache      = Zend_Registry::get('cache');
        // $result     = $cache->load($this->_name.'_cache');

        // if ($result === false) {
        //     $where1=$this->getAdapter()->quoteInto('group_id = ?',9);
        //     $data = $this->fetchAll($where1);

        //     $result = array();
        //     if ($data){
        //         foreach ($data as $item){
        //             $result[] = array(
        //                 'name' => $item->firstname,
        //                 'group_id' => $item->group_id,
        //                 'code' => $item->code,
        //                 );
        //         }
        //     }
        //     $cache->save($result, $this->_name.'_cache', array(), null);
        // }
        // return $result;
        $db = Zend_Registry::get('db');

        $select = $db->select()
            ->from(array('sta' => $this->_name), array('staff_id'=> 'sta.id','code'=> 'sta.code','name'=>'sta.firstname','sta.regional_market','sta.group_id'))
            ->join(array('reg' => 'regional_market'),'reg.id = sta.regional_market', array('reg.area_id'))
            ->join(array('ar' => 'area'),'ar.id = reg.area_id', array('area_n'=>'ar.name'))
            ->where('sta.group_id = ?', '9')
            ->where('sta.status = ?', '1')
            ->order('ar.name')
            ->order('sta.code');  

        // echo $select ;exit();
        $result = $db->fetchAll($select);

        return $result;

    }
    //get total by position staff
    function checkrequest($params){
        $db = Zend_Registry::get('db');

            $get=array(
                        'pc' => new Zend_Db_Expr("COUNT( CASE WHEN p.group_id = 4  THEN p.id END )" ),//by pc 
                        'asm' => new Zend_Db_Expr("COUNT( CASE WHEN p.group_id = 5  THEN p.id END )"),//by asm
                        'sales' => new Zend_Db_Expr("COUNT( CASE WHEN p.group_id = 9  THEN p.id END )"),//by sale
                        'rd' => new Zend_Db_Expr("COUNT( CASE WHEN p.group_id = 28  THEN p.id END )"),//by rd
                        'hr' => new Zend_Db_Expr("COUNT( CASE WHEN p.group_id = 7  THEN p.id END )"),//by hr
                        'pcm' => new Zend_Db_Expr("COUNT( CASE WHEN (p.group_id = 17 OR p.group_id = 21)  THEN p.id END )"),//by pcm
                        'pcdb' => new Zend_Db_Expr("COUNT( CASE WHEN p.group_id = 64  THEN p.id END )"),//by pcdb
			'hrsp' => new Zend_Db_Expr("COUNT( CASE WHEN p.group_id = 62  THEN p.id END )"),//by HR sp
			'other' => new Zend_Db_Expr("COUNT( CASE WHEN (p.group_id = 13 OR p.group_id = 1 OR p.group_id = 46 OR p.group_id = 45 OR p.group_id = 57 OR p.group_id = 55 OR p.group_id = 54 OR p.group_id = 44 OR p.group_id = 47 OR p.group_id = 48)  THEN p.id END )"),
                        'trainer' => new Zend_Db_Expr("COUNT( CASE WHEN p.group_id = 36  THEN p.id END )"),//by trainer
                        'translator' => new Zend_Db_Expr("COUNT( CASE WHEN p.group_id = 50  THEN p.id END )"),
                        'marketing' => new Zend_Db_Expr("COUNT( CASE WHEN p.group_id = 51  THEN p.id END )"),
                        'salemkt' => new Zend_Db_Expr("COUNT( CASE WHEN p.group_id = 49  THEN p.id END )"),
                        'branding' => new Zend_Db_Expr("COUNT( CASE WHEN p.group_id = 53  THEN p.id END )"),
                        'stock' => new Zend_Db_Expr("COUNT( CASE WHEN p.group_id = 30  THEN p.id END )"),
                        // //total off account
                        'disable' => new Zend_Db_Expr("COUNT( CASE WHEN p.status = 0 THEN p.id END )"),

                        // //off count by position
                        'off_pcm' => new Zend_Db_Expr("COUNT( CASE WHEN (p.group_id = 17 OR p.group_id = 21) THEN p.id END )"),
                        'Off_pcdb' => new Zend_Db_Expr("COUNT( CASE WHEN p.group_id = 64  THEN p.id END )"),
			'off_hrsp' => new Zend_Db_Expr("COUNT( CASE WHEN p.group_id = 62  THEN p.id END )"),//by HR sp
                        'Off_pc' => new Zend_Db_Expr("COUNT( CASE WHEN p.group_id = 4  THEN p.id END )"),
                        'off_asm' => new Zend_Db_Expr("COUNT( CASE WHEN p.group_id = 5  THEN p.id END )"),
                        'off_sale' => new Zend_Db_Expr("COUNT( CASE WHEN p.group_id = 9  THEN p.id END )"),
                        'off_rd' => new Zend_Db_Expr("COUNT( CASE WHEN p.group_id = 28 THEN p.id END )"),
                        'off_hr' => new Zend_Db_Expr("COUNT( CASE WHEN p.group_id = 7  THEN p.id END )"),
			'off_other' => new Zend_Db_Expr("COUNT( CASE WHEN (p.group_id = 13 OR p.group_id = 1 OR p.group_id = 46 OR p.group_id = 45 OR p.group_id = 57 OR p.group_id = 55 OR p.group_id = 54 OR p.group_id = 44 OR p.group_id = 47 OR p.group_id = 48)  THEN p.id END )"),
                        'off_trainer' => new Zend_Db_Expr("COUNT( CASE WHEN p.group_id = 36  THEN p.id END )"),
                        'off_translator' => new Zend_Db_Expr("COUNT( CASE WHEN p.group_id = 50  THEN p.id END )"),
                        'off_marketing' => new Zend_Db_Expr("COUNT( CASE WHEN p.group_id = 51  THEN p.id END )"),
                        'off_salemkt' => new Zend_Db_Expr("COUNT( CASE WHEN p.group_id = 49  THEN p.id END )"),
                        'off_branding' => new Zend_Db_Expr("COUNT( CASE WHEN p.group_id = 53  THEN p.id END )"),
                        'off_stock' => new Zend_Db_Expr("COUNT( CASE WHEN p.group_id = 30  THEN p.id END )"),  
			  );
            $select_p = $db->select()
            ->from(array('p' => 'staff'), $get);
        if(isset($params['off']) && $params['off']){
            $select_p->where('p.status =? ', $params['off']);
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
                    $select_p->where('p.regional_market IN (?)', $tem);
                else
                    $select_p->where('1=0', 1);

            } elseif (isset($params['regional_market']) and $params['regional_market']) {
                if (is_array($params['regional_market']) && count($params['regional_market']) > 0) {
                    $select_p->where('p.regional_market IN (?)', $params['regional_market']);
                } else {
                    $select_p->where('1=0', 1);
                }
            }

              if (isset($params['is_officer']) and intval($params['is_officer']) > 0){
                if ($params['is_officer'] == 1)
                    $select_p->where('p.is_officer = ? or p.team = 11 or p.title in (274,179,181)', 1);
                elseif ($params['is_officer'] == 0)
                    $select_p->where('p.is_officer ? or p.team = 11 or p.title in (274,179,181)', 0);
                    //$select->orWhere('p.team = ?', WARRANTY_CENTER);
            }

             if (isset($params['department']) and $params['department']) {
                if (is_array($params['department']) && count($params['department']) > 0) {
                    $select_p->where('p.department IN (?)', $params['department']);
                } else {
                    $select_p->where('1=0', 1);
                }
            }


            if (isset($params['team']) and $params['team']) {
                if (is_array($params['team']) && count($params['team']) > 0) {
                    $select_p->where('p.team IN (?)', $params['team']);
                } else {
                    $select_p->where('1=0', 1);
                }
            }

            if (isset($params['title']) and $params['title']) {
                if (is_array($params['title']) && count($params['title']) > 0) {
                    $select_p->where('p.title IN (?)', $params['title']);
                } else {
                    $select_p->where('1=0', 1);
                }
            }
                $result = $db->fetchRow($select_p);
            return $result;
        }
}