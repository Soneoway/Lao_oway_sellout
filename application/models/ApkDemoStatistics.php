<?php

class Application_Model_ApkDemoStatistics extends Zend_Db_Table_Abstract
{
    function fetchPagination($page, $limit, &$total, $params)
    {
        $d1 = explode('/', $params['from']);
        $from = $d1[2] . '-' . $d1[1] . '-' . $d1[0];

        $d2 = explode('/', $params['to']);
        $to = $d2[2] . '-' . $d2[1] . '-' . $d2[0];

        $db = Zend_Registry::get('db');

        $get = array(
            'id' => new Zend_Db_Expr('SQL_CALC_FOUND_ROWS ast.id'),
            'imei' => 'ast.imei',
            'system_model' => 'ast.system_model',
            'system_version' => 'ast.system_version',
            'location' => 'ast.location',
            'total_usage' => new Zend_Db_Expr('COUNT(DISTINCT(asd.id))'),
            'coverage_time' => new Zend_Db_Expr('SEC_TO_TIME(ROUND(AVG(TIMESTAMPDIFF(SECOND, asd.start_time, asd.end_time))))'),
        );

        $select = $db->select()
            ->from(array('ast' => 'apk_statistics'), $get)
            ->join(array('asd' => 'apk_statistics_data'), 'asd.statistic_id = ast.id', array());

        $select->where('asd.start_time >= ?', $from . ' 00:00:00');
        $select->where('asd.end_time <= ?', $to . ' 23:59:59');

        $select->group('asd.statistic_id');
        $select->order('ast.imei ASC');

        if ($limit)
            $select->limitPage($page, $limit);

        $result = $db->fetchAll($select);
        $total = $db->fetchOne("select FOUND_ROWS()");

        return $result;
    }

    function ApkDemoStatisticDetail($params)
    {
        $d1 = explode('/', $params['from']);
        $from = $d1[2] . '-' . $d1[1] . '-' . $d1[0];

        $d2 = explode('/', $params['to']);
        $to = $d2[2] . '-' . $d2[1] . '-' . $d2[0];

        $db = Zend_Registry::get('db');

        $get = array(
            'id' => new Zend_Db_Expr('SQL_CALC_FOUND_ROWS asd.id'),
            'run_time' => new Zend_Db_Expr('SEC_TO_TIME(ROUND(TIMESTAMPDIFF(SECOND, asd.start_time, asd.end_time)))'),
            'start_time' => 'asd.start_time',
            'end_time' => 'asd.end_time',
        );

        $select = $db->select()
            ->from(array('asd' => 'apk_statistics_data'), $get);

        $select->where('asd.start_time >= ?', $from . ' 00:00:00');
        $select->where('asd.end_time <= ?', $to . ' 23:59:59');

        $select->where('asd.statistic_id = ?', $params['id']);
        $select->order('asd.start_time ASC');

        $result = $db->fetchAll($select);

        return $result;
    }

    function ExportByStore($params)
    {
        $d1 = explode('/', $params['from']);
        $from = $d1[2] . '-' . $d1[1] . '-' . $d1[0];

        $d2 = explode('/', $params['to']);
        $to = $d2[2] . '-' . $d2[1] . '-' . $d2[0];

        $db = Zend_Registry::get('db');

        $get_aaa = array(
            'store_id' => 'ss.store_id',
            'imei' => 'ast.imei',
            'date_time' => new Zend_Db_Expr('DATE(asd.start_time)'),
            'count_view' => new Zend_Db_Expr('COUNT(DISTINCT(asd.id))'),
            'coverage' => new Zend_Db_Expr('AVG(TIMESTAMPDIFF(SECOND, asd.start_time, asd.end_time))'),
            'cnt_staff' => new Zend_Db_Expr('COUNT(DISTINCT(ss.staff_id))'),
        );

        $AAA = $db->select()
            ->from(array('ass' => 'apk_statistics_store'), $get_aaa)
            ->join(array('ast' => 'apk_statistics'), 'ast.imei = ass.imei', array())
            ->join(array('asd' => 'apk_statistics_data'), 'asd.statistic_id = ast.id', array())
            ->join(array('ss' => 'store_staff'), 'ss.store_id = ass.store_id', array())
            ->where("asd.start_time >= ?", $from . " 00:00:00")
            ->where("asd.end_time <= ?", $to . " 23:59:59")
            ->where("ss.is_leader = ?", 0)
            ->where(new Zend_Db_Expr('TIMESTAMPDIFF(SECOND, asd.start_time, asd.end_time) > ?'), 15)
            ->group(new Zend_Db_Expr('ss.store_id, DATE(asd.start_time)'));


        $get = array(
            'area_name' => 'a.name',
            'region' => 'rm.name',
            'store_id' => 'st.id',
            'store_name' => 'st.name',
            'imei' => 'AAA.imei',
            'total_usage' => 'AAA.count_view',
            'coverage_time' => new Zend_Db_Expr('SEC_TO_TIME(ROUND(AAA.coverage))'),
            'cnt_staff' => 'AAA.cnt_staff',
        );

        $select = $db->select()
            ->from(array('st' => 'store'), $get)
            ->join(array('AAA' => $AAA), 'AAA.store_id = st.id')
            ->join(array('rm' => 'regional_market'), 'rm.id = st.regional_market', array())
            ->join(array('a' => 'area'), 'a.id = rm.area_id', array());

        $select->order('area_name ASC');

        $result = $db->fetchAll($select);

        return $result;
    }

    function ExportByStaff($params)
    {
        $d1 = explode('/', $params['from']);
        $from = $d1[2] . '-' . $d1[1] . '-' . $d1[0];

        $d2 = explode('/', $params['to']);
        $to = $d2[2] . '-' . $d2[1] . '-' . $d2[0];

        $db = Zend_Registry::get('db');

        $get_aaa = array(
            'staff_code' => 's.code',
            'staff_name' => new Zend_Db_Expr('CONCAT(s.firstname, " ", s.lastname)'),
            'group_name' => 'g.name',
            'imei' => 'ast.imei',
            'area_id' => 'rm.area_id',
            'region' => 'rm.name',
            'date_time' => new Zend_Db_Expr('DATE(asd.start_time)'),
            'count_view' => new Zend_Db_Expr('COUNT(s.id)'),
            'coverage' => new Zend_Db_Expr('AVG(TIMESTAMPDIFF(SECOND, asd.start_time, asd.end_time))'),
        );

        $AAA = $db->select()
            ->from(array('asu' => 'apk_statistics_user'), $get_aaa)
            ->join(array('ast' => 'apk_statistics'), 'ast.imei = asu.imei', array())
            ->join(array('asd' => 'apk_statistics_data'), 'asd.statistic_id = ast.id', array())
            ->join(array('s' => 'staff'), 's.id = asu.staff_id', array())
            ->join(array('rm' => 'regional_market'), 'rm.id = s.regional_market', array())
            ->join(array('g' => 'group'), 'g.id = s.group_id', array())
            ->where("asd.start_time >= ?", $from . " 00:00:00")
            ->where("asd.end_time <= ?", $to . " 23:59:59")
            ->where(new Zend_Db_Expr('TIMESTAMPDIFF(SECOND, asd.start_time, asd.end_time) > ?'), 15)
            ->group(new Zend_Db_Expr('s.id, DATE(asd.start_time)'));

        $get = array(
            'area_name' => 'a.name',
            'region' => 'AAA.region',
            'staff_code' => 'AAA.staff_code',
            'staff_name' => 'AAA.staff_name',
            'group_name' => 'AAA.group_name',
            'imei' => 'AAA.imei',
            'total_usage' => 'AAA.count_view',
            'coverage_time' => new Zend_Db_Expr('SEC_TO_TIME(ROUND(AAA.coverage))')
        );

        $select = $db->select()
            ->from(array('a' => 'area'), $get)
            ->join(array('AAA' => $AAA), 'AAA.area_id = a.id');

        $select->order('area_name ASC');

        $result = $db->fetchAll($select);

        return $result;
    }
}
