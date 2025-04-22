<?php
class Application_Model_SalesTeamStaff extends Zend_Db_Table_Abstract
{
	protected $_name = 'sales_team_staff';


    function get_sale_cache(){
        $cache      = Zend_Registry::get('cache');
        $result     = $cache->load($this->_name.'_sale_cache');

        if ($result === false) {

            $db = Zend_Registry::get('db');

            $select = $db->select()
                ->from(array('p' => 'sales_team_staff'),
                    array('p.*'))
                ->where('p.is_leader <> ?', 1);

            $select->where('p.del = ?', 0);

            $data = $db->fetchAll($select);

            $leaders = $this->get_leader();


            $result = array();

            if ($data){
                foreach ($data as $item){
                    if (isset($leaders[$item['sales_team_id']]) and $leaders[$item['sales_team_id']])
                        $result[$item['staff_id']] = $leaders[$item['sales_team_id']];
                }
            }
            $cache->save($result, $this->_name.'_sale_cache', array(), null);
        }
        return $result;
    }

    function get_leader_by_date($time, $member_id){
        $db = Zend_Registry::get('db');


        $select = $db->select()
            ->from(array('p' => 'sales_team_staff_log'),
                array('p.*'))
            ->where("( p.joined_at <= '$time' AND p.released_at >= '$time' ) OR ( p.joined_at <= '$time' AND p.released_at IS NULL )")
            ->where('p.staff_id = ?', $member_id);
            
        $data = $db->fetchAll($select);

        if ($data){
            $leader = $teams = null;

            foreach ($data as $item)
                $teams[] = $item['sales_team_id'];

            $teams = array_filter($teams);

            //search leader
            $select = $db->select()
                ->from(array('p' => 'sales_team_staff_log'),
                    array('p.*'))
                ->where("( p.joined_at <= '$time' AND p.released_at >= '$time' ) OR ( p.joined_at <= '$time' AND p.released_at IS NULL )")
                ->where('p.is_leader = ?', 1)
                ->where('p.sales_team_id IN (?)', $teams);

            $data = $db->fetchAll($select);

            if ($data)
                foreach ($data as $item)
                    $leader[] = $item['staff_id'];


            return $leader;
        }
        return null;
    }

    private function get_leader(){
        $db = Zend_Registry::get('db');

        $select = $db->select()
            ->from(array('p' => 'sales_team_staff'),
                array('p.*'))
            ->join(array('s' => 'staff'),
                's.id = p.staff_id',
                array('s.firstname', 's.lastname', 's.group_id'))
            ->where('p.is_leader = ?', 1);

        $select->where('p.del = ?', 0);

        $data = $db->fetchAll($select);

        $return = array();

        if ($data)
            foreach ($data as $item){
                $return[$item['sales_team_id']][] = $item['firstname'].' '.$item['lastname'];
            }

        return $return;
    }
}
