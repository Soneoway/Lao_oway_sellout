<?php
class Application_Model_StaffTrainer extends Zend_Db_Table_Abstract
{
    protected $_name = 'staff_trainer';

    public function getAreaTrainer($staff_id)
    {
        //remove cache
        $cache = Zend_Registry::get('cache');
        $cache->remove('asm_cache');
        $cache->remove('regional_market_cache');
        $cache->remove('regional_market_district_cache');
        $cache->remove('area_cache');

        $QAsm                = new Application_Model_Asm();
        $userStorage         = Zend_Auth::getInstance()->getStorage()->read();
        $QRegionalMarket     = new Application_Model_RegionalMarket();
        $cachedRegionalMarket = $QRegionalMarket->get_cache();
        $QArea               = new Application_Model_Area();
        $allDistrict         = $QRegionalMarket->get_district_cache();
        $cachedArea          = $QArea->get_cache();
        $cachedAsm           = $QAsm->get_cache($staff_id);
        $area                = $cachedAsm['area'];
        $province            = $cachedAsm['province'];
        $district            = $cachedAsm['district'];
        $result              = array();
        $areaStaff           = array();
        $provinceStaff       = array();
        $districtStaff       = array();

        if($area and count($area))
        {
            foreach ($area as $key => $value)
            {
                $areaStaff[$value] = $cachedArea[$value];
            }
            $result['area']      = $areaStaff;
        }

        if($province and count($province))
        {
            foreach ($province as $key => $value)
            {
                if($value)
                $provinceStaff[$value] = $cachedRegionalMarket[$value];
            }
            $result['province']  = $provinceStaff;
        }

        if($district and count($district))
        {
            foreach ($district as $key => $value)
            {
                if($value)
                    $districtStaff[$value] = $allDistrict[$value]['name'];
            }
            $result['district']  = $districtStaff;
        }

        $full_rights_trainer = unserialize(FULL_RIGHTS_TRAINER);

        if(in_array($userStorage->title,$full_rights_trainer) || $userStorage->group_id == ADMINISTRATOR_ID)
        {
            $result['area']      = $cachedArea;
            $result['province']  = $cachedRegionalMarket;
            $arrDistrictAll      = array();
            foreach ($allDistrict as $key => $value)
            {
                $arrDistrictAll[$key] = $value['name'];
            }
            $result['district']  = $arrDistrictAll;
        }

        return $result;
    }

    public function checkPGPBSALE($staff_id)
    {
        $QStaff             = new Application_Model_Staff();
        $whereStaff         = $QStaff->getAdapter()->quoteInto('id = ?',$staff_id);
        $rowStaff           = $QStaff->fetchRow($whereStaff);
        $result             = null;

        if(!$rowStaff)
        {
            $result = 'Can Find Information PGPB';

        }
        else
        {
            if(!in_array($rowStaff['title'],array(PGPB_TITLE,SALE_SALE_SALE)))
            {
                $result = 'The Record Not PGPB OR SALE';
            }

            if($rowStaff['status']!= 1)
            {
                $result = 'PGPB OR SALE is disabled';
            }
        }

        return $result;
    }

    public function checkAreaPGSALEISAreaTrainer($staff_id_Pg,$staff_id_trainer)
    {
        $result             = array();
        $userStorage        = Zend_Auth::getInstance()->getStorage()->read();
        $QStaffTrainer      = new Application_Model_StaffTrainer();
        $area_trainer       = null;

        $QStaff     = new Application_Model_Staff();
        $whereStaff = $QStaff->getAdapter()->quoteInto('id = ?',$staff_id_Pg);
        $rowStaff   = $QStaff->fetchRow($whereStaff);
        if($rowStaff)
        {
            $regionalMarket = $rowStaff['regional_market'];
            $Allvalue       = $QStaffTrainer->getAreaTrainer($staff_id_trainer);
            $province       = array_keys($Allvalue['province']);

            $full_rights_trainer = unserialize(FULL_RIGHTS_TRAINER);

            if(in_array($regionalMarket,$province) || $userStorage->group_id == ADMINISTRATOR_ID || in_array($userStorage->title,$full_rights_trainer)  )
            {

            }
            else
            {
                $result = "Area PG Not True Area Trainer";
            }
        }
        else
        {
            $result = 'Can not found PG OR SALE';
        }

        return $result;
    }

    function fetchPaginationStaff($page, $limit, &$total, $params){

        $db = Zend_Registry::get('db');

        $name = 'staff';

        if ($limit) {
            $select = $db->select()
                ->from(array('p' => $name),
                    array(new Zend_Db_Expr('SQL_CALC_FOUND_ROWS DISTINCT p.id'), 'p.*'));
        } else {
            $select = $db->select()
                ->from(array('p' => $name),
                    array(new Zend_Db_Expr('DISTINCT p.id'), 'p.*'));
        }

        if (isset($params['team']) and $params['team']) {
            if (is_array($params['team']) && count($params['team']) > 0) {
                $select->where('p.team IN (?)', $params['team']);
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

        $select->joinLeft(array('sp' => 'staff_point'),
            'p.id = sp.staff_id and sp.month = '.date('m').' and sp.year = '.date('Y'),
            array('sp.point'));

        $select->joinLeft(array('_st' => 'staff_trainer'),
            'p.id = _st.staff_id',
            array('_st.staff_id as staff_trainer','_st.facebook_link'));

        $select->joinLeft(array('srw' => 'staff_reward_warning'),
            'p.id = srw.staff_id and srw.month = '.date('m').' and srw.year = '.date('Y').' and del = 0 OR del IS NULL ',
            array('srw.staff_id as staff_reward_warning','srw.content as content_reward_warning'));


        if (isset($params['need_approve']) and $params['need_approve']){
            $select->where('st.is_approved = ?', 0);
        }

        if ( ( isset($params['off']) and intval($params['off']) > 0 ) || ( isset($params['sname']) and $params['sname'] == 1 ) )
            $select->where('p.off_date is '
                . ( $params['off'] == 2 ? 'not' : '')
                .' null');


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

        if(isset($params['area_trainer_right']) and $params['area_trainer_right'] and !(isset($params['regional_market_right']) and $params['regional_market_right']))
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
        elseif (isset($params['regional_market_right']) and $params['regional_market_right']) {
            if (is_array($params['regional_market_right']) && count($params['regional_market_right']) > 0) {
                $select->where('p.regional_market IN (?)', $params['regional_market_right']);
            } else {
                $select->where('1=0', 1);
            }
        }


        $order_str = '';

        if (isset($params['sort']) and $params['sort']) {
            $collate = ' ';
            $desc = (isset($params['desc']) and $params['desc'] == 1) ? ' DESC ' : ' ASC ';

            if ($params['sort'] == 'name')
            {
                $collate = ' COLLATE utf8_unicode_ci ';
                $order_str .= ' CONCAT(p.firstname, " ",p.lastname) '.$collate . $desc;
            }
            else if ( $params['sort'] == 'department' || $params['sort'] == 'team' )
            {
                $collate = ' COLLATE utf8_unicode_ci ';
                $order_str .= $collate . $desc;
            }
            else if($params['sort'] == 'point')
                $order_str = 'sp.`'.$params['sort'] . '` ' . $desc;
            else {
                $order_str = 'p.`'.$params['sort'] . '` ' . $collate . $desc;
            }

            $select->order(new Zend_Db_Expr($order_str));
        }

        if (isset($params['name']) and $params['name']) {
            $select->where('CONCAT(p.firstname, " ",p.lastname) LIKE ?', '%'.$params['name'].'%');
        }


        $select->group('p.id');

        if ($limit)
            $select->limitPage($page, $limit);
        $result = $db->fetchAll($select);

        if ($limit)
            $total = $db->fetchOne("select FOUND_ROWS()");
        return $result;
    }

    function getInfoStaff($staff_id)
    {
        $db = Zend_Registry::get('db');

        $select = $db->select()
            ->from(array('p' => $this->_name),array('p.staff_id','p.knowledge','p.skill','p.attitude','p.facebook_id'));

        $select->where('p.staff_id = ?',$staff_id);

        $result = $db->fetchAll($select);

        return $result;

    }


}