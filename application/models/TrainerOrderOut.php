<?php
class Application_Model_TrainerOrderOut extends Zend_Db_Table_Abstract
{
    protected $_name = 'trainer_order_out';

    function fetchPagination($page, $limit, &$total, $params){

        $db = Zend_Registry::get('db');

        $select = $db->select()
            ->from(array('p' => $this->_name),
                array(new Zend_Db_Expr('SQL_CALC_FOUND_ROWS p.id'), 'p.*'));


        if (isset($params['sn']) and $params['sn'])
            $select->where('p.sn = ?', $params['sn']);

        if (isset($params['asset_id']) and $params['asset_id'])
        {
            $select->joinleft(array('tood'=>'trainer_order_out_detail'),'tood.order_out_id = p.id', array('*'));
            $select->where('tood.asset_id = ?', $params['asset_id']);
            $select->where('tood.asset_id = ?', $params['asset_id']);
        }


        if (isset($params['confirmed_at_from']) and $params['confirmed_at_from'])
            $select->where('p.confirmed_at >= ?', $params['confirmed_at_from']);

        if (isset($params['confirmed_at_to']) and $params['confirmed_at_to'])
            $select->where('p.confirmed_at <= ?', $params['confirmed_at_to']);

        if (isset($params['created_at_from']) and $params['created_at_from'])
            $select->where('p.created_at >= ?', $params['created_at_from']);

        if (isset($params['created_at_to']) and $params['created_at_to'])
            $select->where('p.created_at <= ?', $params['created_at_to']);

        if (isset($params['status']) and $params['status'])
            $select->where('p.status = ?', $params['status']);

        if (isset($params['output_to_staff_id']) and $params['output_to_staff_id'])
        {
            $select->joinleft(array('tood'=>'trainer_order_out_detail'),'tood.order_out_id = p.id', array('*'));
            $select->where('tood.output_to_staff_id = ?', $params['output_to_staff_id']);
        }

        $select->where('p.del = ? OR p.del IS NULL',0);


        if(!isset($params['export']) and $params['export'] != 1)
        {
            $select->group(array('p.sn'));

            if ($limit)
                $select->limitPage($page, $limit);

            $result = $db->fetchAll($select);
        }
        else
        {
            $result = $select->__toString();
        }

        $total = $db->fetchOne("select FOUND_ROWS()");

        return $result;
    }

    function save($params)
    {
        $order_out_id               = isset($params['order_out_id']) ? $params['order_out_id'] : null;
        $ip                         = isset($params['ip']) ? $params['ip'] : null;
        $userStorage                = isset($params['userStorage']) ? $params['userStorage'] : null;
        $date                       = isset($params['date']) ? $params['date'] : null;
        $quantity                   = isset($params['quantity']) ? $params['quantity'] : null;
        $note                       = isset($params['note']) ? $params['note'] : null;
        $asset                      = isset($params['asset']) ? $params['asset'] : null;
        $ids                        = isset($params['ids']) ? $params['ids'] : null;
        $output_to_staff_id         = isset($params['output_to_staff_id']) ? $params['output_to_staff_id'] : null;

        $QLog                       = new Application_Model_Log();
        $QTrainerOrderOut           = new Application_Model_TrainerOrderOut();
        $QTrainerOrderOutDetail     = new Application_Model_TrainerOrderOutDetail();
        $QTrainerTypeAsset          = new Application_Model_TrainerTypeAsset();
        $cachedTypeAsset            = $QTrainerTypeAsset->get_cache();

        $result = array(
            'code'    => 0,
            'message' => null
        );

        $currentTime = date('Y-m-d H:i:s');

        try
        {
            if(isset($order_out_id) and $order_out_id)
            {
                // dau tien mình lay tat ca danh sach cac id cua don hàng đó

                $whereOrderOutID   = array();
                $whereOrderOutID[] = $QTrainerOrderOut->getAdapter()->quoteInto('id =  ?', $order_out_id);
                $whereOrderOutID[] = $QTrainerOrderOut->getAdapter()->quoteInto('del = ? OR del IS NULL', 0);
                $rowOrderOut       = $QTrainerOrderOut->fetchAll($whereOrderOutID);

                if($rowOrderOut)
                {
                    $whereOrderOutDetail   = array();
                    $whereOrderOutDetail[] = $QTrainerOrderOutDetail->getAdapter()->quoteInto('order_out_id = ?',$order_out_id);
                    $whereOrderOutDetail[] = $QTrainerOrderOutDetail->getAdapter()->quoteInto('del = ? OR del IS NULL ',0);
                    $rowsOrderOutDetail    = $QTrainerOrderOutDetail->fetchAll($whereOrderOutDetail);

                    if($rowsOrderOutDetail->count())
                    {
                        $arrayOldIDs = array();
                        foreach($rowsOrderOutDetail as $key => $value)
                        {
                            $arrayOldIDs[] = $value['id'];
                        }

                        $arrayDiff = array_diff($arrayOldIDs,$ids);

                        if(is_array($arrayDiff) and count($arrayDiff))
                        {
                            // bat dau update lai nhung don hang ma del di
                            $dataDel = array(
                                'del'=>1
                            );

                            $whereDelTrainerOrderDetail   = $QTrainerOrderOutDetail->getAdapter()->quoteInto('id IN (?)',$arrayDiff);
                            $rowsDelOrderOutDetail        = $QTrainerOrderOutDetail->fetchAll($whereDelTrainerOrderDetail);
                            if($rowsDelOrderOutDetail->count())
                            {
                                $QTrainerOrderOutDetail->update($dataDel,$whereDelTrainerOrderDetail);
                            }

                            // to do log
                            $info = array('Del Trainer Order Out Detail','Order Out Detail Id'=>$arrayDiff);
                            $QLog->insert( array (
                                'info'          => json_encode($info),
                                'user_id'       => $userStorage->id,
                                'ip_address'    => $ip,
                                'time'          => $currentTime
                            ) );

                        }

                        foreach($ids as $key => $id)
                        {
                            if (
                                isset($quantity[$key]) and $quantity[$key] and $quantity[$key] >0
                                and isset($asset[$key]) and $asset[$key]
                                and isset($output_to_staff_id[$key]) and $output_to_staff_id[$key]
                            )
                            {
                                $inventoryAsset = $QTrainerOrderOut->checkQuantityAsset($asset[$key]);

                                if(is_null($inventoryAsset))
                                    throw new Exception($cachedTypeAsset[$asset[$key]].' not in inventory asset !');

                                if($inventoryAsset < $quantity[$key] )
                                    throw new Exception($cachedTypeAsset[$asset[$key]].' not enough quantity !');

                                $data = array(
                                    'quantity'           => $quantity[$key],
                                    'asset_id'           => $asset[$key],
                                    'note'               => $note[$key],
                                    'output_to_staff_id' => $output_to_staff_id[$key]

                                );


                                if(isset($id) and $id)
                                {
                                    $whereOrderOutDetailUpdate  = $QTrainerOrderOutDetail->getAdapter()->quoteInto('id = ?',$id);
                                    $rowOrderOutDetailUpdate    = $QTrainerOrderOutDetail->fetchRow($whereOrderOutDetailUpdate);
                                    if($rowOrderOutDetailUpdate)
                                    {
                                        $QTrainerOrderOutDetail->update($data,$whereOrderOutDetailUpdate);
                                    }

                                    // to do log
                                    $info = array('Update Trainer Order Out Detail','Order Out Detail Id'=>$id,'value'=>$data);
                                    $QLog->insert( array (
                                        'info'          => json_encode($info),
                                        'user_id'       => $userStorage->id,
                                        'ip_address'    => $ip,
                                        'time'          => $currentTime
                                    ) );

                                }
                                else
                                {
                                    // insert
                                    $data['order_out_id']   = $order_out_id;
                                    $idOrderOutDetailInsert = $QTrainerOrderOutDetail->insert($data);

                                    // to do log
                                    $info = array('Insert Order Out Detail','Order Out Detail Id'=>$idOrderOutDetailInsert,'value'=>$data);
                                    $QLog->insert( array (
                                        'info'          => json_encode($info),
                                        'user_id'       => $userStorage->id,
                                        'ip_address'    => $ip,
                                        'time'          => date('Y-m-d H:i:s')
                                    ) );

                                }
                            }
                            else
                            {
                                throw new Exception('Input Value Not True !');
                            }


                        }
                    }


                }
                else
                {
                    throw new Exception ('Can Not Found SN');
                }

            }
            else
            {
                //insert
                if(is_array($ids))
                {
                    $serialNumber = date ('YmdHis') . substr ( microtime (), 2, 4 );

                    $dataInsertOrderOut = array(
                        'sn'         => $serialNumber,
                        'created_at' => $currentTime,
                        'status'     => ORDER_STATUS_PENDING_TRAINING,
                        'created_by' => $userStorage->id,
                        'date'       => $date
                    );

                    $order_out_insert_id = $QTrainerOrderOut->insert($dataInsertOrderOut);

                    // to do log
                    $info = array('Insert Order Out Id','Order Out Id'=>$order_out_insert_id,'value'=>$dataInsertOrderOut);
                    $QLog->insert( array (
                        'info'          => json_encode($info),
                        'user_id'       => $userStorage->id,
                        'ip_address'    => $ip,
                        'time'          => date('Y-m-d H:i:s')
                    ) );

                    foreach($ids as $k => $value)
                    {
                        if (
                            isset($quantity[$k]) and $quantity[$k] and $quantity[$k] > 0
                            and isset($asset[$k]) and $asset[$k]
                            and isset($output_to_staff_id[$k]) and $output_to_staff_id[$k]
                        )
                        {
                            $inventoryAsset = $QTrainerOrderOut->checkQuantityAsset($asset[$k]);

                            if(is_null($inventoryAsset))
                                throw new Exception($cachedTypeAsset[$asset[$k]].' not in inventory asset !');

                            if($inventoryAsset < $quantity[$k] )
                                throw new Exception($cachedTypeAsset[$asset[$k]].' not enough quantity !');

                            $data = array(
                                'order_out_id'      => $order_out_insert_id,
                                'quantity'          => $quantity[$k],
                                'asset_id'          => $asset[$k],
                                'note'              => $note[$k],
                                'output_to_staff_id'=> $output_to_staff_id[$k]
                            );


                            $idOrderOutDetailTrainerInsert = $QTrainerOrderOutDetail->insert($data);

                            // to do log
                            $info = array('Insert Order Out Detail Trainer','Order Out Detail Id'=>$idOrderOutDetailTrainerInsert,'value'=>$data);
                            $QLog->insert( array (
                                'info'          => json_encode($info),
                                'user_id'       => $userStorage->id,
                                'ip_address'    => $ip,
                                'time'          => date('Y-m-d H:i:s')
                            ) );

                        }
                        else
                        {
                            $result ['code']    = -1;
                            $result ['message'] = 'Input Value Not True';
                        }

                    }
                }


            }

            return $result;
        }
        catch (Exception $e)
        {
            $result['code']    = -1;
            $result['message'] = $e->getMessage();
            return $result;
        }

    }

    public function getStaffTrainer()
    {
        $db = Zend_Registry::get('db');
        $select = $db->select()
            ->from(array('p' => 'staff'),array('p.id','p.firstname','p.lastname','p.team'));
        $select->where('p.team = ?', TRAINING_TEAM);
        $select->where('p.off_date IS NULL');
        $rows = $db->fetchAll($select);
        $result = array();

        if(count($rows)>0)
        {
            foreach($rows as $key => $value)
            {
                $result[$value['id']] = $value['firstname'].' '.$value['lastname'];
            }
        }

        return $result;

    }

    public function checkQuantityAsset($asset_id)
    {
        $result =  null;

        $db     = Zend_Registry::get('db');

        $select = $db->select()
            ->from(array('p' => 'trainer_inventory_asset'),array('*'));

        $select->where('p.asset_id = ?', $asset_id);

        $row = $db->fetchRow($select);

        if($row)
        {
            $result = $row['total_quantity'];
        }

        return $result;
    }

}