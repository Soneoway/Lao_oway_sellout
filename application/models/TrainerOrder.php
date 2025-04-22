<?php
class Application_Model_TrainerOrder extends Zend_Db_Table_Abstract
{
    protected $_name = 'trainer_order';

    function fetchPagination($page, $limit, &$total, $params){

        $db = Zend_Registry::get('db');

        $select = $db->select()
            ->from(array('p' => $this->_name),
                array(new Zend_Db_Expr('SQL_CALC_FOUND_ROWS p.id'), 'p.*'));

        if (isset($params['sn']) and $params['sn'])
            $select->where('p.sn = ?', $params['sn']);

        if (isset($params['asset_id']) and $params['asset_id'])
        {
            $select->joinleft(array('tod'=>'trainer_order_detail'),'tod.order_id = p.id', array('*'));
            $select->where('tod.asset_id = ?', $params['asset_id']);
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

        $select->where('p.del = ? OR p.del IS NULL',0);

        $select->group(array('p.sn'));

        $select->limitPage($page, $limit);
        $result = $db->fetchAll($select);
        $total = $db->fetchOne("select FOUND_ROWS()");
        return $result;
    }

    function save($params)
    {
        $order_id                   = isset($params['order_id']) ? $params['order_id'] : null;
        $ip                         = isset($params['ip']) ? $params['ip'] : null;
        $userStorage                = isset($params['userStorage']) ? $params['userStorage'] : null;
        $date                       = isset($params['date']) ? $params['date'] : null;
        $quantity                   = isset($params['quantity']) ? $params['quantity'] : null;
        $note                       = isset($params['note']) ? $params['note'] : null;
        $asset                      = isset($params['asset']) ? $params['asset'] : null;
        $ids                        = isset($params['ids']) ? $params['ids'] : null;

        $QLog                       = new Application_Model_Log();
        $QTrainerOrder              = new Application_Model_TrainerOrder();
        $QTrainerOrderDetail        = new Application_Model_TrainerOrderDetail();

        $result = array(
            'code'    => 0,
            'message' => null
        );

        $currentTime = date('Y-m-d H:i:s');

        try
        {
            if(isset($order_id) and $order_id)
            {
                // dau tien mình lay tat ca danh sach cac id cua don hàng đó
                $whereID   = array();
                $whereID[] = $QTrainerOrder->getAdapter()->quoteInto('id = ?', $order_id);
                $whereID[] = $QTrainerOrder->getAdapter()->quoteInto('del = ? OR del IS NULL', 0);
                $rowID     = $QTrainerOrder->fetchRow($whereID);

                if($rowID)
                {
                    /* nếu co sn number */
                    $whereOrderDetail =  array();
                    $whereOrderDetail[] = $QTrainerOrderDetail->getAdapter()->quoteInto('order_id = ?',$order_id);
                    $whereOrderDetail[] = $QTrainerOrderDetail->getAdapter()->quoteInto('del = ? OR del IS NULL',0);
                    $rowsOrderDetail    = $QTrainerOrderDetail->fetchAll($whereOrderDetail);

                    if($rowsOrderDetail->count())
                    {
                        $arrayOrderDetailID = array();
                        foreach($rowsOrderDetail as $key => $value)
                        {
                            $arrayOrderDetailID[] = $value['id'];
                        }

                        $arrayDiff = array_diff($arrayOrderDetailID,$ids);

                        if(is_array($arrayDiff) and count($arrayDiff))
                        {
                            $whereUpdateDelOrderDetail = $QTrainerOrderDetail->getAdapter()->quoteInto('id IN (?)',$arrayDiff );

                            $dataDel = array(
                                'del'=> 1
                            );
                            $QTrainerOrderDetail->update($dataDel,$whereUpdateDelOrderDetail);
                            // to do log
                            $info = array('Del Trainer Order Detail','Order Detail Id'=>$arrayDiff);
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
                                and isset($date) and $date
                            )
                            {

                                $dataTrainerOrderDetail = array(
                                    'quantity'  => $quantity[$key],
                                    'asset_id'  => $asset[$key],
                                    'note'      => $note[$key]

                                );

                                if(isset($id) and $id)
                                {
                                    // update lai dong do thoi
                                    $whereTrainerDetailOrderUpdate = $QTrainerOrderDetail->getAdapter()->quoteInto('id = ?',$id);
                                    $QTrainerOrderDetail->update($dataTrainerOrderDetail,$whereTrainerDetailOrderUpdate);

                                    // to do log
                                    $info = array('Update Trainer Order Detail','Order Detail Id'=>$id,'value'=>$dataTrainerOrderDetail);
                                    $QLog->insert( array (
                                        'info'          => json_encode($info),
                                        'user_id'       => $userStorage->id,
                                        'ip_address'    => $ip,
                                        'time'          => $currentTime
                                    ) );

                                }
                                else
                                {
                                    $dataTrainerOrderDetail['order_id'] = $order_id;
                                    $QTrainerOrderDetail->insert($dataTrainerOrderDetail);
                                    // to do log
                                    $info = array('Insert Trainer Order Detail','Order Detail ID'=>$order_id,'value'=>$dataTrainerOrderDetail);
                                    $QLog->insert( array (
                                        'info'          => json_encode($info),
                                        'user_id'       => $userStorage->id,
                                        'ip_address'    => $ip,
                                        'time'          => $currentTime
                                    ) );
                                }

                            }
                            else
                            {
                                throw new Exception('Input Value Not True !');
                            }
                        }
                    }
                    else
                    {
                        throw new Exception('Can Not Found Value: '.$rowID['sn']);
                    }

                }
                else
                {
                    throw new Exception('Can Not Found Sn '.$rowID['sn']);
                }

                // update sn

                $dataUpdateSn = array(
                    'updated_at'=> $currentTime,
                    'updated_by'=> $userStorage->id
                );

                $QTrainerOrder->update($dataUpdateSn,$whereID);
                // to do log
                $info = array('Update Trainer Order','Order ID'=>$order_id,'value'=>array('new'=>$dataUpdateSn,'old'=>$rowID));
                $QLog->insert( array (
                    'info'          => json_encode($info),
                    'user_id'       => $userStorage->id,
                    'ip_address'    => $ip,
                    'time'          => $currentTime
                ) );

            }
            else
            {
                //insert
                if(is_array($ids))
                {
                    $serialNumber = date ('YmdHis') . substr ( microtime (), 2, 4 );

                    $dataOrder = array(
                        'sn'         => $serialNumber,
                        'date'       => $date,
                        'created_at' => $currentTime,
                        'created_by' => $userStorage->id,
                        'status'     => ORDER_STATUS_PENDING_TRAINING
                    );

                    $OrderID = $QTrainerOrder->insert($dataOrder);

                    // to do log
                    $info = array('Insert Trainer Order','Order ID'=>$OrderID,'value'=>$dataOrder);
                    $QLog->insert( array (
                        'info'          => json_encode($info),
                        'user_id'       => $userStorage->id,
                        'ip_address'    => $ip,
                        'time'          => $currentTime
                    ) );


                    foreach($ids as $k => $value)
                    {
                        if (
                            isset($quantity[$k]) and $quantity[$k] and $quantity[$k] > 0
                            and isset($asset[$k]) and $asset[$k]
                            and isset($date) and $date
                        )
                        {
                            $dataOrderDetail = array(
                                'order_id'  => $OrderID,
                                'quantity'  => $quantity[$k],
                                'asset_id'  => $asset[$k],
                                'note'      => $note[$k]
                            );

                            $idOrderDetail = $QTrainerOrderDetail->insert($dataOrderDetail);

                            // to do log
                            $info = array('Insert Trainer Order Detail','Order Detail ID'=>$idOrderDetail,'new'=>$dataOrderDetail);
                            $QLog->insert( array (
                                'info'          => json_encode($info),
                                'user_id'       => $userStorage->id,
                                'ip_address'    => $ip,
                                'time'          => date('Y-m-d H:i:s')
                            ) );

                        }
                        else
                        {
                            throw new Exception('Input Value Not True');
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

}