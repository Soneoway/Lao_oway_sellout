<?php

class Application_Model_QuestionsAnswerLog extends Zend_Db_Table_Abstract
{
    protected $_name = 'questions_answer_log';

    function fetchPagination($page, $limit, &$total, $params)
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');
        error_reporting(~E_ALL);
        ini_set("display_error", '0');

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
            'qa_id' => new Zend_Db_Expr("SQL_CALC_FOUND_ROWS qa.id"),
            'area_id' => 'a.id',
            'area_name' => 'a.name',
            'province_name' => 'rm.name',
            'staff_id' => 'qa.staff_id',
            'group_name' => 'g.name',
            'pcm_id' => 's2.id',
            'pcm_name' => new Zend_Db_Expr("CONCAT(s2.firstname, ' ', s2.lastname)"),
            'staff_code' => 's.code',
            'staff_name' => new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)"),
            'pc_stand_by' => new Zend_Db_Expr("(CASE WHEN s.pc_stand_by = 1 THEN 'Yes' ELSE 'No' END)"),
            'created_at' => 'qa.created_at',
            'topic_id' => 'qh.id',
            'topic' => 'qh.name',
            'score_total' => new Zend_Db_Expr("COUNT('qh.id')*1"),
            'score' => new Zend_Db_Expr("SUM(CASE WHEN qa.created_at = qa.updated_at AND qc.correct = 'Y' THEN 1 ELSE 0 END)"),
        );

        $select = $db->select()
            ->from(array('qa' => 'questions_answer_log'), $get)
            ->join(array('s' => 'staff'), 'qa.staff_id = s.id', array())
            ->join(array('rm' => 'regional_market'), 's.regional_market = rm.id', array())
            ->join(array('a' => 'area'), 'rm.area_id = a.id', array())
            ->join(array('q' => 'questions'), 'qa.question_id = q.id', array())
            ->join(array('qh' => 'questions_header'), 'q.head_id = qh.id', array())
            ->join(array('qc' => 'questions_choice'), 'qa.choice_id = qc.id', array())
            ->join(array('g' => 'group'), 'g.id = s.group_id', array())
            ->joinLeft(array('s2' => 'staff'), 's2.id = qa.pcm_id', array())
            ->order(array('qa.created_at DESC'));

        $select->group(array('q.head_id', 's.id'));
        $select->where('qa.created_at >= ?', $from . ' 00:00:00');
        $select->where('qa.created_at <= ?', $to . ' 23:59:59');

        // Add Filter Staff Code
        if (isset($params['staff_code']) && $params['staff_code']) {
            $select->where('s.code = ?', $params['staff_code']);
        }

        // Add Filter Staff Name
        if (isset($params['staff_name']) && $params['staff_name']) {
            $select->where("CONCAT(s.firstname, ' ', s.lastname) LIKE ?", "%" . $params['staff_name'] . "%");
        }

        // Add Filter Topic E-Test
        if (isset($params['head_id']) && $params['head_id']) {
            if (is_array($params['head_id']) && count($params['head_id']))
                $select->where('q.head_id IN (?)', $params['head_id']);
            elseif (is_numeric($params['head_id']))
                $select->where('q.head_id = ?', intval($params['head_id']));
            else
                $select->where('1=0', 1);
        }

        // Add Filter Staff Group
        if (isset($params['staff_group']) && $params['staff_group']) {
            if (is_array($params['staff_group']) && count($params['staff_group']))
                $select->where('s.group_id IN (?)', $params['staff_group']);
            elseif (is_numeric($params['head_id']))
                $select->where('s.group_id = ?', intval($params['staff_group']));
            else
                $select->where('1=0', 1);
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
                    $select->where('s.regional_market IN (?)', $params['regional_market']);
                elseif (is_numeric($params['regional_market']))
                    $select->where('s.regional_market = ?', intval($params['regional_market']));
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

        if ($limit)
            $select->limitPage($page, $limit);

        //print_r($params);
        //echo $select;
        $result = $db->fetchAll($select);

        if ($limit)
            $total = $db->fetchOne("select FOUND_ROWS()");


        return $result;
    }

    function getQuestionsHeader()
    {

        $db = Zend_Registry::get('db');

        $select = $db->select()
            ->from('questions_header')
            ->order(array('id DESC'));

        $result = $db->fetchAll($select);

        return $result;
    }

    function getQuestionsChoice($topic_id)
    {
        $db = Zend_Registry::get('db');

        $get = array(
            'questions' => 'q.questions',
        );

        $select = $db->select()
            ->from(array('q' => 'questions'), $get)
            ->where('q.head_id IN(?)', $topic_id)
            ->order(array('id ASC'));

        $arr = $db->fetchAll($select);

        $result = array();
        for ($i=0;$i<count($arr);$i++){
            $result[] = ($i+1).'.'.$arr[$i]['questions'];
        }

        return $result;
    }

    function getChoiceAnswer($staff_id, $topic_id)
    {
        $db = Zend_Registry::get('db');

        $get = array(
            'answer' => 'qc.answer',
            'choice' => 'qc.choice',
        );

        $select = $db->select()
            ->from(array('qa' => 'questions_answer_log'), $get)
            ->join(array('qc' => 'questions_choice'), 'qa.choice_id = qc.id', array())
            ->join(array('q' => 'questions'), 'qa.question_id = q.id', array())
            ->where('qa.staff_id = ?', $staff_id)
            ->where('q.head_id = ?', $topic_id)
            ->order(array('q.id ASC'));

        //echo $select; die;
        $result = $db->fetchAll($select);
        return $result;
    }

    function getPcmName($area_id)
    {
        //todo
        return $area_id;
    }

    function getPcmLeader($area_id)
    {
        $db = Zend_Registry::get('db');

        $get = array(
            'name' => new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)"),
        );

        $select = $db->select()
            ->from(array('asm' => 'asm'), $get)
            ->join(array('s' => 'staff'), 'asm.staff_id = s.id', array())
            ->where('s.group_id = 36')
            ->where('asm.area_id <> 48')
            ->where('asm.area_id = ?', $area_id)
            ->where('s.off_date IS NULL');

        //echo $select; die;
        $result = $db->fetchAll($select);

        return $result;
    }

    function getDetailAnswer($staff_id, $head_id)
    {
        $db = Zend_Registry::get('db');

        $get = array(
            'qa_id' => new Zend_Db_Expr("SQL_CALC_FOUND_ROWS qa.id"),
            'area_id' => 'a.id',
            'area_name' => 'a.name',
            'staff_id' => 'qa.staff_id',
            'staff_code' => 's.code',
            'staff_name' => new Zend_Db_Expr("CONCAT(s.firstname, ' ', s.lastname)"),
            'group_name' => 'g.name',
            'created_at' => 'qa.created_at',
            'topic' => 'qh.name',
            'score_total' => new Zend_Db_Expr("COUNT('qh.id')*1"),
            'score' => new Zend_Db_Expr("SUM(CASE WHEN qa.created_at = qa.updated_at AND qc.correct = 'Y' THEN 1 ELSE 0  END)"),
        );

        $select = $db->select()
            ->from(array('qa' => 'questions_answer_log'), $get)
            ->join(array('s' => 'staff'), 'qa.staff_id = s.id', array())
            ->join(array('rm' => 'regional_market'), 's.regional_market = rm.id', array())
            ->join(array('a' => 'area'), 'rm.area_id = a.id', array())
            ->join(array('q' => 'questions'), 'qa.question_id = q.id', array())
            ->join(array('qh' => 'questions_header'), 'q.head_id = qh.id', array())
            ->join(array('qc' => 'questions_choice'), 'qa.choice_id = qc.id', array())
            ->join(array('g' => 'group'), 'g.id = s.group_id', array());

        $select->group(array('q.head_id', 's.id'));

        $select->where('s.id = ?', $staff_id);
        $select->where('q.head_id = ?', $head_id);

        //echo $select; die;
        $result = $db->fetchRow($select);

        return $result;
    }

    function getQuestions($topic_id)
    {
        $db = Zend_Registry::get('db');

        $get = array(
            'id' => 'q.id',
            'questions' => 'q.questions',
        );

        $select = $db->select()
            ->from(array('q' => 'questions'), $get)
            ->where('q.head_id IN(?)', $topic_id)
            ->order(array('id ASC'));

        $result = $db->fetchAll($select);

        return $result;
    }

    function getChoice($topic_id)
    {
        $db = Zend_Registry::get('db');

        $get = array(
            'id' => 'qc.id',
            'head_id' => 'q.head_id',
            'question_id' => 'qc.question_id',
            'answer' => 'qc.answer',
            'choice' => 'qc.choice',
            'correct' => 'qc.correct',
        );

        $select = $db->select()
            ->from(array('qc' => 'questions_choice'), $get)
            ->join(array('q' => 'questions'), 'qc.question_id = q.id', array())
            ->where('q.head_id IN(?)', $topic_id)
            ->order(array('qc.choice ASC'));

        $choice = $db->fetchAll($select);

        $result = array();
        foreach ($choice as $key => $value) {
            $result[$value['question_id']][] = $choice[$key];
        }

        return $result;
    }

    function getChoiceAnswerLog($staff_id, $topic_id)
    {
        $db = Zend_Registry::get('db');

        $get = array(
            'id' => 'qa.id',
            'choice_id' => 'qa.choice_id',
            'question_id' => 'qa.question_id',
            'created_at' => 'qa.created_at',
            'updated_at' => 'qa.updated_at',
        );

        $select = $db->select()
            ->from(array('qa' => 'questions_answer_log'), $get)
            ->join(array('q' => 'questions'), 'qa.question_id = q.id', array())
            ->where('qa.staff_id = ?', $staff_id)
            ->where('q.head_id IN(?)', $topic_id);

        $choice = $db->fetchAll($select);

        $result = array();
        foreach ($choice as $key => $value) {
            $result[$value['choice_id']] = $choice[$key];
        }

        return $result;
    }

    function getPcMarketName($staff_id)
    {
        $db = Zend_Registry::get('db');

        $get = array(
            'name' => 'mn.name',
        );

        $select = $db->select()
            ->from(array('ss' => 'store_staff'), $get)
            ->join(array('sm' => 'store_market'), 'sm.store_id = ss.store_id', array())
            ->join(array('mn' => 'market_name'), 'mn.id = sm.market_name_id', array())
            ->where('ss.staff_id = ?', $staff_id);

        $result = $db->fetchAll($select);

        return $result;
    }

    function getPcShopLevel($staff_id)
    {
        $db = Zend_Registry::get('db');

        $get = array(
            'shop_level' => 'st.store_grade',
        );

        $select = $db->select()
            ->from(array('ss' => 'store_staff'), $get)
            ->join(array('st' => 'store'), 'ss.store_id = st.id', array())
            ->where('ss.staff_id = ?', $staff_id)
            ->order(new Zend_Db_Expr("(CASE 
                        WHEN st.store_grade = 'S' THEN 1 
                        WHEN st.store_grade = 'A' THEN 2
                        WHEN st.store_grade = 'B' THEN 3
                        WHEN st.store_grade = 'C' THEN 4
                        ELSE 5
                    END) ASC"));

        $result = $db->fetchAll($select);

        return $result;
    }

}
