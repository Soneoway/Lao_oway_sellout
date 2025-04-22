<?php
class Application_Model_ReportCheckList extends Zend_Db_Table_Abstract
{
	protected $_name = 'check_list_data_log';

    function fetchPagination($page, $limit, &$total, $params){

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
            'id'             => new Zend_Db_Expr('SQL_CALC_FOUND_ROWS cdl.id'),
            'area'           => 'a.name',
            'topic'          => 'clt.name',
            'pc_code'        => 'pc.code',
            'pc_name'        => new Zend_Db_Expr('CONCAT(pc.code, " : ", pc.firstname, " ", pc.lastname)'),
            'pcm_code'       => 'pcm.code',
            'pcm_name'       => new Zend_Db_Expr('CONCAT(pcm.code, " : ", pcm.firstname, " ", pcm.lastname)'),
            'status'         => 'cdl.status',
            'solve'          => 'cdl.solve',
            'created_at'     => 'cdl.created_at',
            'filepic'        => 'cdl.filepic',
        );

        $select = $db->select()
            ->from(array('cdl' => $this->_name), $get)
            ->join(array('clt' => 'check_list_topic'), 'clt.id = cdl.topic_id', array())
            ->join(array('pc'  => 'staff'), 'pc.id = cdl.pc_id', array())
            ->join(array('pcm' => 'staff'), 'pcm.id = cdl.pcm_id', array())
            ->join(array('rm' =>  'regional_market'), 'rm.id = pc.regional_market', array())
            ->join(array('a' =>  'area'), 'a.id = rm.area_id', array());


        $select->where('cdl.created_at >= ?', $from . ' 00:00:00');
        $select->where('cdl.created_at <= ?', $to . ' 23:59:59');

        // Add Filter Staff Code
        if (isset($params['staff_code']) && $params['staff_code']) {
            $select->where('pc.code = ?', $params['staff_code']);
        }

        // Add Filter Staff Name
        if (isset($params['staff_name']) && $params['staff_name']) {
            $select->where("CONCAT(pc.firstname, ' ', pc.lastname) LIKE ?", "%" . $params['staff_name'] . "%");
        }

        // Add Filter Topic
        if (isset($params['topic_id']) && $params['topic_id']) {
            if (is_array($params['topic_id']) && count($params['topic_id']))
                $select->where('clt.id IN (?)', $params['topic_id']);
            elseif (is_numeric($params['topic_id']))
                $select->where('clt.id = ?', intval($params['topic_id']));
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

        $select->group('cdl.id');
        $select->order('cdl.id DESC');


        if ($limit)
        	$select->limitPage($page, $limit);

        $result = $db->fetchAll($select);
        $total = $db->fetchOne("select FOUND_ROWS()");

        return $result;
    }

    function reportExcelCheckList($params){

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
            'id'             => new Zend_Db_Expr('SQL_CALC_FOUND_ROWS cdl.id'),
            'area'           => 'a.name',
            'topic'          => 'clt.name',
            'pc_id'          => 'pc.id',
            'pc_code'        => 'pc.code',
            'pc_name'        => new Zend_Db_Expr('CONCAT(pc.firstname, " ", pc.lastname)'),
            'pcm_code'       => 'pcm.code',
            'pcm_name'       => new Zend_Db_Expr('CONCAT(pcm.firstname, " ", pcm.lastname)'),
            'store_id'       => 'st.id',
            'store_name'     => 'st.name',
            'store_type'     => 'stt.store_type_name',
            'market_name'    => 'mn.name',
            'status'         => 'cdl.status',
            'solve'          => 'cdl.solve',
            'created_at'     => 'cdl.created_at',
        );

        $select = $db->select()
            ->from(array('cdl' => $this->_name), $get)
            ->join(array('clt' => 'check_list_topic'), 'clt.id = cdl.topic_id', array())
            ->join(array('pc'  => 'staff'), 'pc.id = cdl.pc_id', array())
            ->join(array('pcm' => 'staff'), 'pcm.id = cdl.pcm_id', array())
            ->join(array('rm'  => 'regional_market'), 'rm.id = pc.regional_market', array())
            ->join(array('a'   => 'area'), 'a.id = rm.area_id', array())
            ->join(array('ss'  => 'store_staff'), 'ss.staff_id = cdl.pc_id', array())
            ->join(array('st'  => 'store'), 'st.id = ss.store_id', array())
            ->join(array('o'   => 'org'), 'o.org_id = st.org_dealer', array())
            ->join(array('stt' => 'store_type'), 'stt.store_type_id = o.store_type_id', array())
            ->join(array('sm'  => 'store_market'), 'sm.store_id = ss.store_id', array())
            ->join(array('mn'  => 'market_name'), 'mn.id = sm.market_name_id', array());

        $select->where('cdl.created_at >= ?', $from . ' 00:00:00');
        $select->where('cdl.created_at <= ?', $to . ' 23:59:59');

        // Add Filter Staff Code
        if (isset($params['staff_code']) && $params['staff_code']) {
            $select->where('pc.code = ?', $params['staff_code']);
        }

        // Add Filter Staff Name
        if (isset($params['staff_name']) && $params['staff_name']) {
            $select->where("CONCAT(pc.firstname, ' ', pc.lastname) LIKE ?", "%" . $params['staff_name'] . "%");
        }

        // Add Filter Topic
        if (isset($params['topic_id']) && $params['topic_id']) {
            if (is_array($params['topic_id']) && count($params['topic_id']))
                $select->where('clt.id IN (?)', $params['topic_id']);
            elseif (is_numeric($params['topic_id']))
                $select->where('clt.id = ?', intval($params['topic_id']));
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

        $select->group('cdl.id');
        $select->order('cdl.id DESC');

        $result = $db->fetchAll($select);

        return $result;
    }

    function getTitleQuestions($topic_id)
    {
        $db = Zend_Registry::get('db');

        $get = array(
            'title' => 't.title',
        );

        $select = $db->select()
            ->from(array('q' => 'check_list_questions'), $get)
            ->join(array('t'  => 'check_list_questions_title'), 't.id = q.question_title_id', array())
            ->where('FIND_IN_SET(? , q.topic_id)', $topic_id)
            ->order(array('q.sort ASC'));

        $arr = $db->fetchAll($select);

        $result = array();
        foreach ($arr as $value) {
            $result[] = $value['title'];
        }

        return $result;
    }

    function getHeadQuestions($topic_id)
    {
        $db = Zend_Registry::get('db');

        $get = array(
            'questions' => 'q.questions',
        );

        $select = $db->select()
            ->from(array('q' => 'check_list_questions'), $get)
            ->where('FIND_IN_SET(? , q.topic_id)', $topic_id)
            ->order(array('q.sort ASC'));

        $arr = $db->fetchAll($select);

        $result = array();
        foreach ($arr as $value) {
            $result[] = $value['questions'];
        }

        return $result;
    }

    function getAnswerLog($pc_id, $data_id)
    {
        $db = Zend_Registry::get('db');

        $get = array(
            'answer' => 'cla.answer'
        );

        $select = $db->select()
            ->from(array('cdl' => $this->_name), $get)
            ->join(array('cla' => 'check_list_answer_log'), 'cla.data_id = cdl.id', array())
            ->where('cdl.pc_id = ?', $pc_id)
            ->where('cdl.id = ?', $data_id);

        //echo $select; die;
        $result = $db->fetchAll($select);
        return $result;
    }

    function getTopicCheckList()
    {

        $db = Zend_Registry::get('db');

        $select = $db->select()
            ->from('check_list_topic')
            ->order(array('from_date DESC'));

        $result = $db->fetchAll($select);

        return $result;
    }
}
