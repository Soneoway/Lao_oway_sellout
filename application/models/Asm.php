<?php
class Application_Model_Asm extends Zend_Db_Table_Abstract
{
    protected $_name = 'asm';

    function fetchPagination($page, $limit, &$total, $params){
        $db = Zend_Registry::get('db');

        $select = $db->select()
            ->from(array('s' => 'staff'),
                array(new Zend_Db_Expr('SQL_CALC_FOUND_ROWS s.id'), 's.firstname', 's.lastname', 's.email', 's.group_id'))
            ->joinLeft(array('p' => $this->_name), 'p.staff_id = s.id', array('p.area_id', 'p.partner'))
            ->where('s.group_id IN (?)', My_Staff_Group::$allow_in_area_view)
            ->where('s.status = 1 AND s.off_date IS NULL', 1);

        if (isset($params['name']) and $params['name'])
            $select->where('CONCAT(TRIM(s.firstname), " ", TRIM(s.lastname)) LIKE ?', '%'.$params['name'].'%');

        if (isset($params['email']) and $params['email'])
            $select->where('s.email LIKE ?', str_replace(EMAIL_SUFFIX, '', $params['email']).EMAIL_SUFFIX);

        $select->order('CONCAT(s.firstname, " ", s.lastname)', 'COLLATE utf8_unicode_ci ASC');

        // if ($limit)
            // $select->limitPage($page, $limit);

        $result = $db->fetchAll($select);
        $total = $db->fetchOne("select FOUND_ROWS()");
        return $result;
    }

    /**
     * Lấy danh sách các area/province/district theo từng ASM
     * @param  int $staff_id    Staff ID (Default null)
     * @return array            Format array( asm ID => array(list region) )
     */
    public function get_cache($staff_id = null)
    {
        $cache      = Zend_Registry::get('cache');
        $result     = $cache->load($this->_name.'_cache');

        if ($result === false || ( !is_null($staff_id) && !isset($result[$staff_id]) )) {
            $db = Zend_Registry::get('db');

            $select_area = $db->select()
                ->from(array('p' => $this->_name, array('p.staff_id')))
                ->joinLeft(array('r' => 'regional_market'), 'r.area_id=p.area_id', array('province' => 'r.id'))
                ->joinLeft(array('d' => 'regional_market'), 'd.parent=r.id', array('district' => 'd.id'))
                ->where('p.type = ?', 2);

            $select_province = $db->select()
                ->from(array('p' => $this->_name, array('p.staff_id')))
                ->joinLeft(array('r' => 'regional_market'), 'r.id=p.area_id', array('province' => 'r.id'))
                ->joinLeft(array('d' => 'regional_market'), 'd.parent=r.id', array('district' => 'd.id'))
                ->where('p.type = ?', 3);

            $select = $db->select()
                ->union(array($select_area, $select_province));

            $data = $db->fetchAll($select);

            $result = array();

            if ($data){
                foreach ($data as $item){
                    if (!isset($result[$item['staff_id']]))
                        $result[$item['staff_id']] = array('area' => array(), 'province' => array(), 'district' => array());

                    if ($item['type'] == My_Region::Area)
                        $result[$item['staff_id']]['area'][] = $item['area_id'] ;

                    $result[$item['staff_id']]['province'][] = $item['province'] ;
                    $result[$item['staff_id']]['district'][] = $item['district'] ;
                }

                foreach ($result as $_staff_id => $value) {
                    $result[ $_staff_id ]['area'] = array_unique($value['area']);
                    $result[ $_staff_id ]['province'] = array_unique($value['province']);
                    $result[ $_staff_id ]['district'] = array_unique($value['district']);
                }
            }

            $cache->save($result, $this->_name.'_cache', array(), null);
        }

        return is_null($staff_id) ? $result : ( isset($result[$staff_id]) ? $result[$staff_id] : false );
    }
    
     /**  Lấy danh sách các region theo từng ASM
     * @param  int $staff_id    Staff ID (Default null)
     * @return array            Format array( asm ID => array(list region) )
     */
    public function get_region_cache($staff_id = null)
    {
        $cache      = Zend_Registry::get('cache');
        $result     = $cache->load($this->_name.'_region_new_cache');

        if ($result === false || ( !is_null($staff_id) && !isset($result[$staff_id]) )) {
            $data = $this->fetchAll();
            $result = array();

            if ($data){
                $QRegion = new Application_Model_RegionalMarket();

                foreach ($data as $item){
                    if (!isset($result[$item['staff_id']]))
                        $result[$item['staff_id']] = array();

                    $where = $QRegion->getAdapter()->quoteInto('area_id = ?', $item['area_id']);
                    $regions = $QRegion->fetchAll($where);
                    
                    if ($regions)
                        foreach ($regions as $reg)
                            $result[$item['staff_id']][] = $reg['id'];                    
                }
            }

            $cache->save($result, $this->_name.'_region_new_cache', array(), null);
        }

        return is_null($staff_id) ? $result : ( isset($result[$staff_id]) ? $result[$staff_id] : false );
    }

    

    public function is_asm($staff_id, $store_id)
    {
        $QStore = new Application_Model_Store();
        $store = $QStore->find($store_id);
        $store = $store->current();

        if (!$store) return false;

        $regions = $this->get_cache($staff_id);

        if ($regions && is_array($regions))
            return in_array($store['district'], $regions['district']);

        return false;
    }

    public function get_asm_store($staff_id = null) {
      
        $db = Zend_Registry::get('db');

        $select = $db->select()
            ->from(array('am' => $this->_name), array('st.id'))
            ->joinLeft(array('ar' => 'area'), 'am.area_id = ar.id', array())
            ->joinLeft(array('re' => 'regional_market'), 'ar.id = re.area_id', array())
            ->joinLeft(array('st' => 'store'), 're.id=st.regional_market', array());

        $select->where('am.staff_id= ?',$staff_id);
        $data = $db->fetchAll($select);

        $result = array();
        // print_r($data);
        if ($data){ foreach ($data as $item){ $result[] = $item['id'] ; } }

        return $result;
    }

    public function get_asm_for_com($area_id) {

        $db = Zend_Registry::get('db');

        $leader_group = array(RM_ID, RMSTANDBY_ID, ASM_ID, ASMSTANDBY_ID);
        
        // $leader_partner = array(
        //     '5904291', '5600478', '6007285', 
        //     '6004977', '6004979', '6004978', '6006575', 
        //     '6004164', '6001647', 
        // ); 

        // 6000430, 6000620, 6001122, 
        
        $get = array(
            'area_id'       => 'a.id',
            'area_name'     => 'a.name',
            'staff_id'      => 's.id',
            'staff_code'    => 's.code',
            'staff_name'    => new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)"),
            'group_id'      => 'g.id',
            'group_name'    => 'g.name',
        );

        $select = $db->select()
            ->from(array('a' => 'area'), $get)
            ->join(array('rm' => 'regional_market') , 'rm.area_id = a.id'   , array())
            ->join(array('asm' => 'asm'), 
                "   (CASE WHEN asm.type = 3 THEN rm.id ELSE a.id END) = asm.area_id
                    AND asm.partner = 0 
                ", array())
            ->join(array('s' => 'staff')            , 'asm.staff_id = s.id' , array())
            ->join(array('g' => 'group')            , 's.group_id = g.id'   , array())
            ->where('s.off_date IS NULL')
            ->where('s.status = 1')
            //->where('s.code NOT IN (?)', $leader_partner)
            ->where('s.group_id IN (?)', $leader_group)
            ->where('a.id = ?', $area_id)
            ->group(array('a.id','s.id'))
            ->order(array("FIND_IN_SET(g.id, '27,16,5,35,28')", 'staff_name ASC'));

        $result = $db->fetchAll($select);
        return $result;

    }

   public function get_rm_list() {

        $db = Zend_Registry::get('db');

        $leader_group = array(RM_ID);
        
        $leader_partner = array(
            '5904291', '5600478', 
            '6004977', '6004979', '6004978', '6006575',  
            '6004164', '6001647',
        ); 

        // 6000430, 6000620, 6001122, '6007285', 

        $get = array(
            'staff_id'      => 's.id',
            'staff_code'    => 's.code',
            'staff_name'    => new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)"),
            'group_name'    => 'g.name',
        );

        $select = $db->select()
            ->from(array('s' => 'staff'), $get)
            ->join(array('g' => 'group'), 's.group_id = g.id'   , array())
            ->where('s.off_date IS NULL')
            ->where('s.status = 1')
            ->where('s.code NOT IN (?)', $leader_partner)
            ->where('s.group_id IN (?)', $leader_group)
            ->order(array('s.code ASC'));

        $result = $db->fetchAll($select);
        return $result;

    }


    public function getAreaProvinceByStaff($staff_id, $area_id) {

        $db = Zend_Registry::get('db');

        $get = array(
            'zone'          => new Zend_Db_Expr("(CASE WHEN asm.type = 2 THEN CONCAT('A:', a.name) ELSE CONCAT('P:', rm.name) END)"),
            'zone_type'     => new Zend_Db_Expr("(CASE WHEN asm.type = 2 THEN 'A' ELSE 'P' END)"),
            'province_id'   => 'rm.id',
        ); 

        $select = $db->select()
            ->from(array('asm' => 'asm'), $get)
            ->join(array('rm' => 'regional_market'), "asm.area_id = (CASE WHEN asm.type = 2 THEN rm.area_id ELSE rm.id END)", array())
            ->join(array('a'  => 'area'), "rm.area_id = a.id", array())
            ->where('asm.staff_id = ?', $staff_id)
            ->where('a.id = ?', $area_id)
            ->group('zone')
            ->order(array('zone ASC'));

        $result = $db->fetchAll($select);
        return $result;

    }

    public function getPCMByStoreID($store_id) {

        $db = Zend_Registry::get('db');

        $get = array(
            'zone'          => new Zend_Db_Expr("(CASE WHEN asm.type = 2 THEN CONCAT('A:', a.name) ELSE CONCAT('P:', rm.name) END)"),
            'staff_id'      => 's.id',
            'staff_code'    => 's.code',
            'staff_name'    => new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)"),
        );

        $select = $db->select()
            ->from(array('st' => 'store'), $get)
            ->join(array('rm' => 'regional_market'), "st.regional_market = rm.id", array())
            ->join(array('a'  => 'area'), 'rm.area_id = a.id', array())
            ->join(array('asm'=> 'asm'), "(CASE WHEN asm.type = 2 THEN a.id ELSE rm.id END) = asm.area_id", array())
            ->join(array('s'  => 'staff'), 'asm.staff_id = s.id', array())
            ->join(array('g'  => 'group'), 's.group_id = g.id', array())
            ->where('st.id = ?', $store_id)
            ->where('s.off_date IS NULL')
            ->where('s.group_id = ?', TRAINING_TEAM_ID)
            ->order(array('s.code ASC'));
        //echo $select; die;
        $result = $db->fetchAll($select);
        return $result;

    }

    public function getRDListForPCTotal($params) {

        $db = Zend_Registry::get('db');

        $leader_group = array(RM_ID, RMSTANDBY_ID);
        $exception = array('6000620','5700415','5800892');

        $get = array(
            'staff_id'      => 's.id',
            'staff_code'    => 's.code',
            'staff_name'    => new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)"),
            'staff_group'   => 'g.name',
            'area_id'       => 'a.id',
            'area_name'     => 'a.name',
        );

        $select = $db->select()
            ->from(array('s'    => 'staff'), $get)
            ->join(array('g'    => 'group') , 's.group_id = g.id'   , array())
            ->join(array('asm'  => 'asm')   , 's.id = asm.staff_id' , array())
            ->join(array('a'    => 'area')  , 'asm.area_id = a.id'  , array())
            ->where('s.off_date IS NULL')
            ->where('s.status = 1')
            ->where('s.group_id IN (?)', $leader_group)
            ->where('s.code NOT IN (?)', $exception)
            ->order(array('s.code ASC', 'a.name ASC'));

        if ( $params['report_type'] == 'BKK' ) { $select->where("a.name LIKE 'BKK%'", 1); }
        else { $select->where("a.name NOT LIKE 'BKK%'", 1); }

        //echo $select; die;
        $result = $db->fetchAll($select);
        return $result;

    }

    public function getAreaByStaffCode($staff_code) {

        $db = Zend_Registry::get('db');

        $get = array(
            'id'       => 'a.id',
        );

        $select = $db->select()
            ->from(array('s'    => 'staff'), $get)
            ->join(array('asm'  => 'asm')   , 's.id = asm.staff_id' , array())
            ->join(array('a'    => 'area')  , 'asm.area_id = a.id'  , array())
            ->where('s.off_date IS NULL')
            ->where('s.status = 1')
            ->where('s.code = ?', $staff_code)
            ->order(array('a.name ASC'));

        //echo $select; die;
        $result = $db->fetchAll($select);
        return $result;

    }

    function getLeaderByStaffCode($staff_code) {

        $db = Zend_Registry::get('db');

        $get = array(
            'area_id'       => 'a.id',
            'area_name'     => 'a.name',
            'province_id'   => 'rm.id',
            'province_name' => 'rm.name',
            'Zone'          => new Zend_Db_Expr(
                "CASE WHEN asm.type = 2 THEN CONCAT('Area : ' ,a.name) ELSE CONCAT('Province : ' ,rm.name) END"), 

            'staff_code'    => 's2.code',
            'staff_name'    => new Zend_Db_Expr("CONCAT(s2.firstname, ' ', s2.lastname)"),
            'group_id'      => 'g2.id',
            'group_name'    => 'g2.name',
        );

        $select = $db->select()
            ->from(array('s'  => 'staff'), $get)
            ->join(array('rm' => 'regional_market'),'s.regional_market = rm.id',array())
            ->join(array('a'  => 'area')    ,'rm.area_id = a.id'    ,array())
            ->join(array('asm'=> 'asm')     ,'asm.area_id = (CASE WHEN asm.type = 2 THEN rm.area_id ELSE rm.id END)',array())
            ->join(array('s2' => 'staff')   ,'asm.staff_id = s2.id' ,array())
            ->join(array('g2' => 'group')   ,'s2.group_id = g2.id'  ,array())
            ->where('s.code = ?', $staff_code)
            ->where('s2.off_date IS NULL', 1)
            ->where('s2.group_id IN (?)', array(ASM_ID,ASMSTANDBY_ID,RM_ID,RMSTANDBY_ID) );

        //echo $select;
        $result = $db->fetchAll($select);
        return json_encode($result);

    }

    function getAllStaffByStoreID($store_id) {

        $db = Zend_Registry::get('db');

        $get = array(
            'area_id'       => 'a.id',
            'area_name'     => 'a.name',
            'province_id'   => 'rm.id',
            'province_name' => 'rm.name',
            'Zone'          => new Zend_Db_Expr(
                "CASE WHEN asm.type = 2 THEN CONCAT('Area : ' ,a.name) ELSE CONCAT('Province : ' ,rm.name) END"), 

            'staff_code'    => 's.code',
            'staff_name'    => new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)"),
            'group_id'      => 'g.id',
            'group_name'    => 'g.name',
        );

        $select = $db->select()
            ->from(array('st' => 'store'), $get)
            ->join(array('rm' => 'regional_market'),'st.regional_market = rm.id',array())
            ->join(array('a'  => 'area')    ,'rm.area_id = a.id'    ,array())
            ->join(array('asm'=> 'asm')     ,'asm.area_id = (CASE WHEN asm.type = 2 THEN rm.area_id ELSE rm.id END)',array())
            ->join(array('s'  => 'staff')   ,'asm.staff_id = s.id'  ,array())
            ->join(array('g'  => 'group')   ,'s.group_id = g.id'    ,array())
            ->where('st.id = ?', $store_id)
            ->where('s.off_date IS NULL', 1)
            ->where('s.status = ?', 1)
            ->where('s.group_id IN (?)', array(ASM_ID,ASMSTANDBY_ID,RM_ID,RMSTANDBY_ID,TRAINING_TEAM_ID,SALES_ADMIN_ID,32,36,37,38) );

        //echo $select;
        $result = $db->fetchAll($select);
        return json_encode($result);

    }

    function getStaffByAreaID($area_id, $type) {

        $exception = array('5800892', '5600478','5901304','5901418','5800316');

        $db = Zend_Registry::get('db');

        $get = array(
            'staff_code'    => 's.code',
            'staff_name'    => new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)"),
            'group_id'      => 'g.id',
            'group_name'    => 'g.name',
        );

        $select = $db->select()
            ->from(array('asm' => 'asm'), $get)
            ->join(array('s'  => 'staff')   ,'asm.staff_id = s.id'  ,array())
            ->join(array('g'  => 'group')   ,'s.group_id = g.id'    ,array())
            ->where('asm.area_id = ?', $area_id)
            ->where('s.code NOT IN (?)', $exception)
            ->where('s.off_date IS NULL', 1)
            ->where('s.status = ?', 1);

        switch ($type) {
            case 1: $select->where('s.group_id IN (?)', array(RM_ID,RMSTANDBY_ID));     break;  // RD, RD Stand By
            case 2: $select->where('s.group_id IN (?)', array(ASM_ID,ASMSTANDBY_ID));   break;  // ASM, ASM Stand By
            case 3: $select->where('s.group_id IN (?)', array(SALES_ADMIN_ID));         break;  // Sale Admin
            case 4: $select->where('s.group_id IN (?)', array(36));                     break;  // PCM Leader 
            case 5: $select->where('s.group_id IN (?)', array(37,38));                  break;  // TMS, TMS Leader
            case 6: $select->where('s.group_id IN (?)', array(32));                     break;  // ABM
            default: $select->where('1=0', 1); break;
        }

        //echo $select;
        $result = $db->fetchAll($select);
        return $result;

    }

    public function getRdCodeByStaffCode($staff_code) {

        $db = Zend_Registry::get('db');

        $get = array(
            'area_id'   => 'a.id',
            'area_name' => 'a.name',
            'rd_id'     => 's2.id',
            'rd_code'   => 's2.code',
            'rd_name'   => new Zend_Db_Expr("CONCAT(s2.firstname, ' ', s2.lastname)"),
        );

        $select = $db->select()
            ->from(array('s'  => 'staff'), $get)
            ->join(array('rm' => 'regional_market') , 's.regional_market = rm.id'   , array())
            ->join(array('a'  => 'area')            , 'rm.area_id = a.id'           , array())
            ->join(array('asm' => 'asm')            , 'a.id = asm.area_id'          , array())
            ->join(array('s2'  => 'staff')          , 
                "   asm.staff_id = s2.id 
                    AND s2.group_id IN (28,35)
                    AND s2.off_date IS NULL 
                    AND s2.code <> '5800892'
                ", array())

            // ->where('s.off_date IS NULL')
            // ->where('s.status = 1')
            ->where('s.code = ?', $staff_code)
            ->order(array('a.name ASC'));

        //echo $select; die;
        $result = $db->fetchRow($select);
        return $result;

    }

    public function getAsmIDByAreaID($area_id) {
        $db = Zend_Registry::get('db');

        $select = $db->select()
            ->from(array('asm' => 'asm'),array('s.id'))
            ->joinLeft(array('ss' => 'store_staff'),'asm.staff_id = ss.staff_id and is_leader = 4',array())
            ->joinLeft(array('s' => 'staff'),'ss.staff_id = s.id',array())
            ->where('s.status =?',1)
            ->where('asm.area_id = ?',$area_id)
            ->group('s.id');

            // echo $select; die;

        $result = $db->fetchRow($select);
        return $result;
    }

public function getTrainnerArea($staff_id) {
        $db = Zend_Registry::get('db');

        $select = $db->select()
            ->from(array('asm' => 'asm'),array('a.id','a.name'))
            ->joinLeft(array('rm' => 'regional_market'),'rm.id = asm.area_id',array())
            ->joinLeft(array('a' => 'area'),'a.id = rm.area_id',array())
            ->where('asm.staff_id =?',$staff_id)
            ->group('a.id');

        $result = $db->fetchAll($select);
        return $result;
    }

}