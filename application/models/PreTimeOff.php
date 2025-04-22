<?php
class Application_Model_PreTimeOff extends Zend_Db_Table_Abstract
{
	protected $_name = 'pre_time_off';

	function fetchPagination($page, $limit, &$total, $params){
		$db = Zend_Registry::get('db');
        $select = $db->select()
            ->from(array('p'=>'pre_time_off'),array(
                new Zend_Db_Expr('SQL_CALC_FOUND_ROWS DISTINCT p.id'),
				'p.*',
				'staff_name' =>'CONCAT(s.firstname," ",s.lastname)',
				'title'      =>'t1.name',
				'team'       =>'t2.name',
				's.code',
				'p.off_type',
                'bh' => 'CASE WHEN p.off_type = 3 THEN 1 WHEN DATEDIFF(p.to_date,p.from_date) + 1  >= 14 THEN 1 ELSE 0 END'
                ))
            ->join(array('s'=>'staff'),'p.staff_id = s.id',array())
            ->join(array('t1'=>'team'),'t1.id = s.title',array())
            ->join(array('t2'=>'team'),'t2.id = t1.parent_id',array())
        ;

        if(isset($params['from_date']) AND $params['from_date']){
            $from = explode('/',$params['from_date']);
            $from = date('Y-m-d',strtotime($from[2].'-'.$from[1].'-'.$from[0]));
            $select->where('p.from_date >= ?',$from);
        }

        if(isset($params['to_date']) AND $params['to_date']){
            $to = explode('/',$params['to_date']);
            $to = date('Y-m-d',strtotime($to[2].'-'.$to[1].'-'.$to[0]));
            $select->where('p.from_date <= ?',$to);
        }

        if(isset($params['code']) AND $params['code']){
            $select->where('s.code = ?',$params['code']);
        }

        if(isset($params['email']) AND $params['email']){
            $select->where('s.email LIKE ?','%'.$params['email'].'%');
        }

        if(isset($params['name']) AND $params['name']){
            $select->where('CONCAT(s.firstname," ",s.lastname) LIKE ?','%'.$params['name'].'%');
        }

        if(isset($params['off_type']) AND $params['off_type']){
            $select->where('p.off_type = ?',$params['off_type']);
        }

        if(isset($params['bh']) AND $params['bh']){
            $select->where('p.off_type = '.CHILDBEARING.' OR ( DATEDIFF(p.to_date,p.from_date) + 1 ) >= 14');
        }

        if(isset($params['detach']) AND $params['detach']){
            if($params['detach'] == 1)
                $select->where('p.approved_at IS NULL OR p.approved_at = "0000-00-00 00:00:00"');
            elseif($params['detach'] == 2){
                $select->where('p.approved_at IS NOT NULL OR p.approved_at != "0000-00-00 00:00:00"');
            }
        }

        $select->order('p.created_at DESC');

        if ($limit)
            $select->limitPage($page, $limit);
        $result = $db->fetchAll($select);

        if ($limit)
            $total = $db->fetchOne("select FOUND_ROWS()");
        return $result;
	}
    
}