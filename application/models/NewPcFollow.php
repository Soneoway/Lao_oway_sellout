<?php
class Application_Model_NewPcFollow extends Zend_Db_Table_Abstract
{
    protected $_name = 'pc_follow_result';

    function fetchPagination($page, $limit, &$total, $params)
    {
        $bkk_list = array(
            81, 82, 83, 85, 86, 87, 115, 90, 91, 92,
            93, 113, 94, 95, 96, 88, 89, 117, 110, 111,
            112, 97, 109, 98, 99, 100, 101, 102, 114, 103,
            104, 105, 116, 106, 107, 108);

        $d1 = explode('/', $params['from']);
        $from = $d1[2] . '-' . $d1[1] . '-' . $d1[0];

        $d2 = explode('/', $params['to']);
        $to = $d2[2] . '-' . $d2[1] . '-' . $d2[0];

        $db = Zend_Registry::get('db');


        $get = array(
            'id'            => new Zend_Db_Expr('SQL_CALC_FOUND_ROWS s.id'),
            'area'          => 'a.name',
            'staff_name'    => new Zend_Db_Expr('CONCAT(s.code, " : ", s.firstname, " ", s.lastname)'),
            'joined_at'     => 's.joined_at',
            'staff_level'   => 's.staff_level',
            'date_diff'     => new Zend_Db_Expr('DATEDIFF(NOW(), s.joined_at)+1'),
            'cnt_number'    => new Zend_Db_Expr('COUNT(DISTINCT(AAA.pfr_id))'),
            'expire_status' => new Zend_Db_Expr('CASE WHEN DATEDIFF(NOW(), s.joined_at)+1 > 45 THEN "Y" ELSE "N" END'),
            'status'        => new Zend_Db_Expr('(CASE 
                                                    WHEN s.staff_level = 1 THEN "Y" 
                                                    WHEN a.name LIKE "BKK%" AND COUNT(DISTINCT(AAA.pfr_id)) >= 3 AND (SELECT status FROM pc_follow_result WHERE pc_id = s.id ORDER BY created_at DESC LIMIT 1) = "Y" THEN "Y" 
                                                    WHEN a.name NOT LIKE "BKK%" AND COUNT(DISTINCT(AAA.pfr_id)) >= 2 AND (SELECT status FROM pc_follow_result WHERE pc_id = s.id ORDER BY created_at DESC LIMIT 1) = "Y" THEN "Y" 
                                                ELSE "N" END)'),
        );

        $get_aaa = array(
            'pfr_id'        => 'pfr.id',
            'pc_id'         => 'pfr.pc_id',
            'created_at'    => 'pfr.created_at',
            'status_result' => 'pfr.status',
        );

        $AAA = $db->select()
            ->from(array('pfr' => 'pc_follow_result'), $get_aaa);

        $select = $db->select()
            ->from(array('s' => 'staff'), $get)
            ->join(array('rm'=> 'regional_market'), 'rm.id = s.regional_market', array())
            ->join(array('a' => 'area'), 'a.id = rm.area_id', array())
            ->joinLeft(array('AAA' => $AAA), 'AAA.pc_id = s.id ');

        $select->where('s.group_id = ?', 4);
        $select->where('s.off_date IS NULL');

        $select->where('s.joined_at >= ?', $from . ' 00:00:00');
        $select->where('s.joined_at <= ?', $to . ' 23:59:59');

        // Add Filter Staff Code
        if (isset($params['staff_code']) && $params['staff_code']) {
            $select->where('s.code = ?', $params['staff_code']);
        }

        // Add Filter Staff Name
        if (isset($params['staff_name']) && $params['staff_name']) {
            $select->where("CONCAT(s.firstname, ' ', s.lastname) LIKE ?", "%" . $params['staff_name'] . "%");
        }
        // Add Filter Status
        if (isset($params['status']) && $params['status']) {
            $select->having('status IN(?)', $params['status']);
        }

        if (isset($params['chk_bkk']) && $params['chk_bkk']) {
            $select->where('rm.area_id IN (?)', $bkk_list);
        } else {

            // Add Filter Area
            if (isset($params['area_id']) && $params['area_id']) {
                if (is_array($params['area_id']) && count($params['area_id']))
                    $select->where('rm.area_id IN (?)', $params['area_id']);
                elseif (is_numeric($params['area_id']))
                    $select->where('rm.area_id = ?', intval($params['area_id']));
                else
                    $select->where('1=0', 1);
            }

            // Add Filter Province
            if (isset($params['regional_market']) && $params['regional_market']) {
                if (is_array($params['regional_market']) && count($params['regional_market']))
                    $select->where('rm.id IN (?)', $params['regional_market']);
                elseif (is_numeric($params['regional_market']))
                    $select->where('rm.id = ?', intval($params['regional_market']));
                else
                    $select->where('1=0', 1);
            }
        }

        // check permission ASM, ASM Stand by, Sale Admin, Traning
        if (isset($params['asm']) && $params['asm']) {
            $QAsm = new Application_Model_Asm();
            $list_regions = $QAsm->get_cache($params['asm']);
            $list_regions = isset($list_regions['province']) && is_array($list_regions['province']) ? $list_regions['province'] : array();

            if (count($list_regions) > 0)
                $select->where('rm.id IN (?)', $list_regions);
            else
                $select->where('1=0', 1);
        }

        $select->group('s.id');
        $select->order('s.joined_at DESC');

        if ($limit)
            $select->limitPage($page, $limit);

        $result = $db->fetchAll($select);
        $total = $db->fetchOne("select FOUND_ROWS()");

        return $result;
    }

    function reportExcelNewPcFollow($params)
    {
        $bkk_list = array(
            81, 82, 83, 85, 86, 87, 115, 90, 91, 92,
            93, 113, 94, 95, 96, 88, 89, 117, 110, 111,
            112, 97, 109, 98, 99, 100, 101, 102, 114, 103,
            104, 105, 116, 106, 107, 108);

        $d1 = explode('/', $params['from']);
        $from = $d1[2] . '-' . $d1[1] . '-' . $d1[0];

        $d2 = explode('/', $params['to']);
        $to = $d2[2] . '-' . $d2[1] . '-' . $d2[0];

        $db = Zend_Registry::get('db');

        $get = array(
            'id'            => new Zend_Db_Expr('SQL_CALC_FOUND_ROWS s.id'),
            'area'          => 'a.name',
            'staff_code'    => 's.code',
            'staff_name'    => new Zend_Db_Expr('CONCAT(s.firstname, " ", s.lastname)'),
            'market_name'    => 'mn.name',
            'store_id'    => 'st.id',
            'store_name'    => 'st.name',
            'store_type'    => 'stt.store_type_name',
            'joined_at'     => 's.joined_at',
            'date_diff'     => new Zend_Db_Expr('DATEDIFF(NOW(), s.joined_at)+1'),
            'expire_status' => new Zend_Db_Expr('CASE WHEN DATEDIFF(NOW(), s.joined_at)+1 > 45 THEN "Y" ELSE "N" END'),
            'cnt_number'    => new Zend_Db_Expr('COUNT(DISTINCT(AAA.pfr_id))'),
            'result_date'   => new Zend_Db_Expr('AAA.created_at'),
            'status'        => new Zend_Db_Expr('(CASE 
                                                    WHEN s.staff_level = 1 THEN "Y" 
                                                    WHEN a.name LIKE "BKK%" AND COUNT(DISTINCT(AAA.pfr_id)) >= 3 AND (SELECT status FROM pc_follow_result WHERE pc_id = s.id ORDER BY created_at DESC LIMIT 1) = "Y" THEN "Y" 
                                                    WHEN a.name NOT LIKE "BKK%" AND COUNT(DISTINCT(AAA.pfr_id)) >= 2 AND (SELECT status FROM pc_follow_result WHERE pc_id = s.id ORDER BY created_at DESC LIMIT 1) = "Y" THEN "Y" 
                                                ELSE "N" END)'),
        );

        $get_aaa = array(
            'pfr_id'        => 'pfr.id',
            'pc_id'         => 'pfr.pc_id',
            'pcm_code'      => 's.code',
            'pcm_name'      => new Zend_Db_Expr('CONCAT(s.firstname, " ", s.lastname)'),
            'created_at'    => 'pfr.created_at',
        );

        $AAA = $db->select()
            ->from(array('pfr' => 'pc_follow_result'), $get_aaa)
            ->join(array('s' => 'staff'), 's.id = pfr.pcm_id', array())
            ->group('pfr.id');

        $select = $db->select()
            ->from(array('s' => 'staff'), $get)
            ->join(array('rm'=> 'regional_market'), 'rm.id = s.regional_market', array())
            ->join(array('a' => 'area'), 'a.id = rm.area_id', array())
            ->joinLeft(array('ss' => 'store_staff'), 'ss.staff_id = s.id', array())
            ->joinLeft(array('st'  => 'store'), 'st.id = ss.store_id', array())
            ->joinLeft(array('o'   => 'org'), 'o.org_id = st.org_dealer', array())
            ->joinLeft(array('stt' => 'store_type'), 'stt.store_type_id = o.store_type_id', array())
            ->joinLeft(array('sm'  => 'store_market'), 'sm.store_id = ss.store_id', array())
            ->joinLeft(array('mn'  => 'market_name'), 'mn.id = sm.market_name_id', array())
            ->joinLeft(array('AAA' => $AAA), 'AAA.pc_id = s.id ');

        $select->where('s.group_id = ?', 4);
        $select->where('s.off_date IS NULL');

        $select->where('s.joined_at >= ?', $from . ' 00:00:00');
        $select->where('s.joined_at <= ?', $to . ' 23:59:59');

        // Add Filter Staff Code
        if (isset($params['staff_code']) && $params['staff_code']) {
            $select->where('s.code = ?', $params['staff_code']);
        }

        // Add Filter Staff Name
        if (isset($params['staff_name']) && $params['staff_name']) {
            $select->where("CONCAT(s.firstname, ' ', s.lastname) LIKE ?", "%" . $params['staff_name'] . "%");
        }
        // Add Filter Status
        if (isset($params['status']) && $params['status']) {
            $select->having('status IN(?)', $params['status']);
        }

        if (isset($params['chk_bkk']) && $params['chk_bkk']) {
            $select->where('rm.area_id IN (?)', $bkk_list);
        } else {

            // Add Filter Area
            if (isset($params['area_id']) && $params['area_id']) {
                if (is_array($params['area_id']) && count($params['area_id']))
                    $select->where('rm.area_id IN (?)', $params['area_id']);
                elseif (is_numeric($params['area_id']))
                    $select->where('rm.area_id = ?', intval($params['area_id']));
                else
                    $select->where('1=0', 1);
            }

            // Add Filter Province
            if (isset($params['regional_market']) && $params['regional_market']) {
                if (is_array($params['regional_market']) && count($params['regional_market']))
                    $select->where('rm.id IN (?)', $params['regional_market']);
                elseif (is_numeric($params['regional_market']))
                    $select->where('rm.id = ?', intval($params['regional_market']));
                else
                    $select->where('1=0', 1);
            }
        }

        // check permission ASM, ASM Stand by, Sale Admin, Traning
        if (isset($params['asm']) && $params['asm']) {
            $QAsm = new Application_Model_Asm();
            $list_regions = $QAsm->get_cache($params['asm']);
            $list_regions = isset($list_regions['province']) && is_array($list_regions['province']) ? $list_regions['province'] : array();

            if (count($list_regions) > 0)
                $select->where('rm.id IN (?)', $list_regions);
            else
                $select->where('1=0', 1);
        }

        $select->group('s.id');
        $select->order('s.joined_at DESC');

        $result = $db->fetchAll($select);

        return $result;
    }

    function getAnswerResult($staff_id)
    {
        $db = Zend_Registry::get('db');

        $get_aaa = array(
            'id'            => 'pfr.id',
            'pc_id'         => 'pfr.pc_id',
            'created_at'    => 'pfr.created_at',
            'status'        => 'pfr.status',
            'file_pic'        => 'pfr.filepic',
            'pcm_name'      => new Zend_Db_Expr('CONCAT(s.code, " : ", s.firstname, " ", s.lastname)'),
        );

        $select = $db->select()
            ->from(array('pfr' => 'pc_follow_result'), $get_aaa)
            ->join(array('s' => 'staff'), 's.id = pfr.pcm_id', array())
            ->where("pfr.pc_id = ?", $staff_id)
            ->order(array('pfr.created_at ASC'));

        $result = $db->fetchAll($select);

        return $result;
    }

    function getTopicTitle()
    {
        $db = Zend_Registry::get('db');

        $get = array(
            'topic' => 't1.name',
            'cnt'   =>  new Zend_Db_Expr('COUNT(t1.id)'),
        );

        $select = $db->select()
            ->from(array('t1' => 'pc_follow_topic'), $get)
            ->joinLeft(array('t2' => 'pc_follow_topic'), 't2.parent_id = t1.id')
            ->where('t1.parent_id = ?', 0)
            ->group('t1.id')
            ->order('t1.sort ASC');

        $result = $db->fetchAll($select);

        return $result;
    }

    function getQuestionTitle()
    {
        $db = Zend_Registry::get('db');

        $get = array(
            'questions' => 't1.name',
        );

        $select = $db->select()
            ->from(array('t1' => 'pc_follow_topic'), $get)
            ->joinLeft(array('t2' => 'pc_follow_topic'), 't2.parent_id = t1.id')
            ->where('t1.content IS NOT NULL')
            ->group('t1.id')
            ->order('t1.parent_id ASC')
            ->order('t1.sort ASC');

        $data = $db->fetchAll($select);

        $result = array();
        foreach ($data as $value) {
            $result[] = $value['questions'];
        }

        return $result;
    }

    function getResultAnswer($staff_id, $date)
    {
        $db = Zend_Registry::get('db');

        $get_aaa = array(
            'status'         => 'r.status',
            'topic_id'       => 'r.topic_id',
        );

        $AAA = $db->select()
            ->from(array('r' => 'pc_follow_result'), $get_aaa)
            ->where("r.pc_id  = ?", $staff_id)
            ->where("DATE(r.created_at) = '$date'")
            ->group('r.topic_id');


        $get = array(
            'answer' => new Zend_Db_Expr('COALESCE(AAA.status, "-")'),
        );

        $select = $db->select()
            ->from(array('t' => 'pc_follow_topic'), $get)
            ->joinLeft(array('AAA' => $AAA), "AAA.topic_id = t.id")
            ->where('t.content IS NOT NULL')
            ->order('t.parent_id ASC')
            ->order('t.sort ASC');

        $data = $db->fetchAll($select);

        $result = array();
        foreach ($data as $value) {
            $result[] = $value['answer'];
        }

        return $result;
    }

    function getResultDetail($params)
    {
        $db = Zend_Registry::get('db');

        $get = array(
            'topic' => 'pft.name',
            'category' => 'pft.category',
            'questions' => 'pfq.questions',
            'answer' => 'tmp.answer',
        );

        $select = $db->select()
            ->from(array('tmp' => 'pc_follow_result_temp'), $get)
            ->join(array('pfr' => 'pc_follow_result'), 'pfr.id = tmp.result_id', array())
            ->join(array('pfq' => 'pc_follow_questions'), 'pfq.id = tmp.question_id', array())
            ->join(array('pft' => 'pc_follow_topic'), 'pft.id = pfq.topic_id', array())
            ->where('tmp.result_id = ?', $params['id'])
            ->order('pft.parent_id ASC')
            ->order('pft.sort ASC')
            ->order('pfq.parent_id ASC')
            ->order('pfq.sort ASC');

        $data = $db->fetchAll($select);

        $result = array();
        foreach ($data as $value) {
            unset($value['content']);

            $category = ($value['category'] == 1) ? 'Role play' : 'E-Test';

            $result["$category : ".$value['topic']][] = $value;
        }

        return $result;
    }
}
