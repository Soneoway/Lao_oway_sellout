<?php

class Application_Model_PcResingnation extends Zend_Db_Table_Abstract
{
    protected $_name = 'pc_resingnation';

    function fetchPagination($page, $limit, &$total, $params)
    {
        $d1 = explode('/', $params['from']);
        $from = $d1[2].'-'.$d1[1].'-'.$d1[0];

        $d2 = explode('/', $params['to']);
        $to = $d2[2].'-'.$d2[1].'-'.$d2[0];

        $channel_list = array(
            'Brandshop' => '16,18,21,22,23,31,33,34,37,39,40',
            'Dealer'    => '1,19,20,26',
            'KR'        => '27,32,38',
            'AWN'       => '10,12,41',
            'True'      => '3,42',
            'Dtac'      => '5,43',
            'Com 7'     => '9,44',
            'Jaymart'   => '2,30',
        );

        $db = Zend_Registry::get('db');

        $channel = "(CASE";
        $channel .= " WHEN o.org_id IN (16,18,21,22,23,31,33,34,37,39,40) THEN 'Brandshop'";
        $channel .= " WHEN o.org_id IN (1,19,20,26) THEN 'Dealer'";
        $channel .= " WHEN o.org_id IN (27,32,38) THEN 'KR'";
        $channel .= " WHEN o.org_id IN (10,12,41) THEN 'AWN'";
        $channel .= " WHEN o.org_id IN (3,42) THEN 'True'";
        $channel .= " WHEN o.org_id IN (5,43) THEN 'Dtac'";
        $channel .= " WHEN o.org_id IN (9,44) THEN 'Com 7'";
        $channel .= " WHEN o.org_id IN (2,30) THEN 'Jaymart'";
        $channel .= " ELSE o.org_name";
        $channel .= " END)";

        $aaa_get = array(
            'area_id' => 'rm.area_id',
            'channel2' => new Zend_Db_Expr($channel),
            'cnt_pc' => new Zend_Db_Expr('COUNT(DISTINCT CASE WHEN s.off_date IS NULL THEN s.id END)'),
            'cnt_resign' => new Zend_Db_Expr('COUNT(DISTINCT CASE WHEN s.off_date IS NOT NULL THEN s.id END)'),
            'cnt_request_resign' => new Zend_Db_Expr('COUNT(DISTINCT pr.staff_id)'),
        );

        $AAA = $db->select()
            ->from(array('s' => 'staff'), $aaa_get)
            ->join(array('ss' => 'store_staff_log'), 's.id = ss.staff_id', array())
            ->join(array('st' => 'store'), 'ss.store_id = st.id', array())
            ->join(array('o' => 'org'), 'st.org_dealer = o.org_id', array())
            ->join(array('rm' => 'regional_market'), 'st.regional_market = rm.id', array())
            ->joinLeft(array('pr' => 'pc_resingnation'), "pr.status <> 'N' AND pr.off_date >= '$from' AND pr.off_date <= '$to' AND pr.staff_id = s.id", array())
            ->where('ss.is_leader = ?', 0)
            ->where(new Zend_Db_Expr("FROM_UNIXTIME(ss.released_at) >= '$from 00:00:00' OR ss.released_at IS NULL"))
            ->group('rm.area_id')
            ->group('channel2');

        $get = array(
            'area_id' => new Zend_Db_Expr('SQL_CALC_FOUND_ROWS a.id'),
            'area_name' => 'a.name',
            'org_id' => 'o.org_id',
            'channel' => new Zend_Db_Expr($channel),
            'cnt_pc' => new Zend_Db_Expr('COALESCE(AAA.cnt_pc, 0)'),
            'cnt_request_resign' => new Zend_Db_Expr('COALESCE(AAA.cnt_request_resign, 0)'),
            'cnt_resign' => new Zend_Db_Expr('COALESCE(AAA.cnt_resign, 0)')
        );

        $select = $db->select()
            ->from(array('a' => 'area'), $get)
            ->join(array('o' => 'org'), '', array())
            ->joinLeft(array('AAA' => $AAA), 'AAA.area_id = a.id AND AAA.channel2 = '.$channel, array())
//            ->where('o.org_id NOT IN (8,15,28)')
            ->group('a.id')
            ->group('channel')
            ->order('a.name')
            ->order('o.org_name');

        if (isset($params['org_id']) && $params['org_id']) {
            $tmp = array();
            foreach ($params['org_id'] as $channel) {
                $tmp[] = is_numeric($channel) ? $channel : $channel_list[$channel];
            }
            $channels = implode(",", $tmp);
            $select->where('o.org_id IN (?)', explode(",", $channels));
        }

        if (isset($params['area_id']) && $params['area_id']) {
            if (is_array($params['area_id']) && count($params['area_id']))
                $select->where('a.id IN (?)', $params['area_id']);
            elseif (is_numeric($params['area_id']))
                $select->where('a.id = ?', intval($params['area_id']));
            else
                $select->where('1=0', 1);
        }

//        echo $select;

        if ($limit)
            $select->limitPage($page, $limit);

        $result = $db->fetchAll($select);

        if ($limit)
            $total = $db->fetchOne("select FOUND_ROWS()");

        return $result;
    }

    function getPcWorkingStatusDetail($params)
    {
        $d1 = explode('/', $params['from']);
        $from = $d1[2].'-'.$d1[1].'-'.$d1[0];

        $d2 = explode('/', $params['to']);
        $to = $d2[2].'-'.$d2[1].'-'.$d2[0];

        $org_id = '';
        switch ($params['channel']) {
            case 'Brandshop':
                $org_id = '16,18,21,22,23,31,33,34,37,39,40';
                break;
            case 'Dealer':
                $org_id = '1,19,20,26';
                break;
            case 'KR':
                $org_id = '27,32,38';
                break;
            case 'AWN':
                $org_id = '10,12,41';
                break;
            case 'True':
                $org_id = '3,42';
                break;
            case 'Dtac':
                $org_id = '5,43';
                break;
            case 'Com 7':
                $org_id = '9,44';
                break;
            case 'Jaymart':
                $org_id = '2,30';
                break;
            default:
                $org_id = $params['org_id'];
        }

        $channel_list = array(
            'Brandshop' => '16,18,21,22,23,31,33,34,37,39,40',
            'Dealer'    => '1,19,20,26',
            'KR'        => '27,32,38',
            'AWN'       => '10,12,41',
            'True'      => '3,42',
            'Dtac'      => '5,43',
            'Com 7'     => '9,44',
            'Jaymart'   => '2,30',
        );

        $db = Zend_Registry::get('db');

        $get = array(
            'staff_code' => 's.code',
            'staff_name' => new Zend_Db_Expr('CONCAT(s.firstname, " ", s.lastname)'),
            'joined_at' => 's.joined_at',
            'off_date' => 's.off_date'
        );

        $select = $db->select()
            ->from(array('s' => 'staff'), $get)
            ->join(array('ss' => 'store_staff_log'), 's.id = ss.staff_id', array())
            ->join(array('st' => 'store'), 'ss.store_id = st.id', array())
            ->join(array('rm' => 'regional_market'), 'st.regional_market = rm.id', array())
            ->joinLeft(array('pr' => 'pc_resingnation'), "pr.status <> 'N' AND pr.off_date >= '$from' AND pr.off_date <= '$to' AND pr.staff_id = s.id", array())
            ->where('ss.is_leader = ?', 0)
            ->where(new Zend_Db_Expr("FROM_UNIXTIME(ss.released_at) >= '$from 00:00:00' OR ss.released_at IS NULL"))
            ->where('st.org_dealer IN(?)', explode(',', $org_id))
            ->where('rm.area_id = ?', $params['area_id'])
            ->order('s.code')
            ->group('s.id');

        if ($params['type'] == 1) {
            $select->where('s.off_date IS NULL');
        } else if ($params['type'] == 2) {
            $select->where('pr.staff_id IS NOT NULL');
        } else if ($params['type'] == 3) {
            $select->where('s.off_date IS NOT NULL');
        }

        $result = $db->fetchAll($select);

        return $result;
    }

    function getAllChannel()
    {
    $db = Zend_Registry::get('db');

    $org_id = "(CASE 
                        WHEN o.org_id IN (16,18,21,22,23,31,33,34,37,39,40) THEN 'Brandshop'
                        WHEN o.org_id IN (1,19,20,26) THEN 'Dealer'
                        WHEN o.org_id IN (27,32,38) THEN 'KR'
                        WHEN o.org_id IN (10,12,41) THEN 'AWN'
                        WHEN o.org_id IN (3,42) THEN 'True'
                        WHEN o.org_id IN (5,43) THEN 'Dtac'
                        WHEN o.org_id IN (9,44) THEN 'Com 7'
                        WHEN o.org_id IN (2,30) THEN 'Jaymart'
                        ELSE o.org_id 
                    END)";

    $org_name = "(CASE 
                        WHEN o.org_id IN (16,18,21,22,23,31,33,34,37,39,40) THEN 'Brandshop'
                        WHEN o.org_id IN (1,19,20,26) THEN 'Dealer'
                        WHEN o.org_id IN (27,32,38) THEN 'KR'
                        WHEN o.org_id IN (10,12,41) THEN 'AWN'
                        WHEN o.org_id IN (3,42) THEN 'True'
                        WHEN o.org_id IN (5,43) THEN 'Dtac'
                        WHEN o.org_id IN (9,44) THEN 'Com 7'
                        WHEN o.org_id IN (2,30) THEN 'Jaymart'
                        ELSE o.org_name 
                    END)";

    $get = array(
        'org_id' => new Zend_Db_Expr($org_id),
        'org_name' => new Zend_Db_Expr($org_name),
    );

    $select = $db->select()
        ->from(array('o'  => 'org'), $get)
        ->group($org_name)
        ->order('org_name ASC');

    $result = $db->fetchAll($select);
    return $result;

    }
}