<?php
class Application_Model_TrainerInventoryHistoryAsset extends Zend_Db_Table_Abstract
{
    protected $_name = 'trainer_inventory_history_asset';

    function fetchPagination($page, $limit, &$total, $params){

        $db = Zend_Registry::get('db');

        $select = $db->select()
            ->from(array('p' => $this->_name),
                array(new Zend_Db_Expr('SQL_CALC_FOUND_ROWS p.id'), 'p.*'));

        if (isset($params['asset_id']) and $params['asset_id'])
            $select->where('p.asset_id = ?', $params['asset_id']);

        if(isset($params['export']) and $params['export'])
        {
            $result = $select->__toString();
        }
        else
        {
            $select->limitPage($page, $limit);
            $result = $db->fetchAll($select);
            $total  = $db->fetchOne("select FOUND_ROWS()");
        }


        return $result;
    }

    function updateInventoryHistory($params)
    {
        /* params --> in , params --> out , params --> asset id */

        $asset_id     = (isset($params['asset_id']) and $params['asset_id'] ) ?  $params['asset_id'] : null;
        $quantity_in  = (isset($params['quantity_in']) and $params['quantity_in'] ) ?  $params['quantity_in'] : null;
        $quantity_out = (isset($params['quantity_out']) and $params['quantity_out'] ) ?  $params['quantity_out'] : null;
        $ip           = (isset($params['ip']) and $params['ip'] ) ?  $params['ip'] : null;
        $userStorage  = (isset($params['userStorage']) and $params['userStorage'] ) ?  $params['userStorage'] : null;
        $time         = (isset($params['time']) and $params['time'] ) ?  $params['time'] : null;

        /*-----------------------------------------------*/
        $QTrainerInventoryHistoryAsset = new Application_Model_TrainerInventoryHistoryAsset();
        $QTrainerTypeAsset             = new Application_Model_TrainerTypeAsset();
        $cachedTypeAssetTrainer        = $QTrainerTypeAsset->get_cache();

        /*--------------------------------------------------------*/
         $QLog               = new Application_Model_Log();
        /* ------------------------------------------------------ */

        $result = array(
            'code'      => 0,
            'message'   => null
        );

        try
        {
            /* Check asset*/
            if(!isset($asset_id))
                throw new Exception(' Can Not Found Asset '.(isset($cachedTypeAssetTrainer[$asset_id]) and $cachedTypeAssetTrainer[$asset_id]) ? $cachedTypeAssetTrainer[$asset_id]: null);

            $QTrainerInventoryAsset        = new Application_Model_TrainerInventoryAsset();
            $whereInventoryAsset           = $QTrainerInventoryAsset->getAdapter()->quoteInto('asset_id = ?', $asset_id);
            $rowAssetInventoryAsset        = $QTrainerInventoryAsset->fetchRow($whereInventoryAsset);
            /* -- - - - - - - - ------------------------------------*/

            /* Check quantity In */
            if(isset($quantity_in) and $quantity_in)
            {
                    if($rowAssetInventoryAsset)
                    {
                        // Tồn Đầu
                        $inventoryFirstIN  = $rowAssetInventoryAsset['total_quantity'];

                        $dataInsertIN = array(
                            'asset_id'        => $asset_id,
                            'inventory_first' => $inventoryFirstIN ,
                            'inventory_in'    => $quantity_in,
                            'inventory_end'   => $inventoryFirstIN + $quantity_in,
                            'created_at'      => $time,
                            'created_by'      => $userStorage->id
                        );
                    }
                    else
                    {
                        /* Thì Nhập vào Luôn */
                        $dataInsertIN = array(
                            'asset_id'        => $asset_id,
                            'inventory_first' => 0,
                            'inventory_in'    => $quantity_in,
                            'inventory_end'   => $quantity_in,
                            'created_at'      => $time,
                            'created_by'      => $userStorage->id
                        );
                    }

                    $QTrainerInventoryHistoryAsset->insert($dataInsertIN);

                    // to do log
                    $info = array('Insert Inventory History In','Asset Id'=>$asset_id,'value'=>array('new'=>$dataInsertIN));
                    $QLog->insert( array (
                        'info'          => json_encode($info),
                        'user_id'       => $userStorage->id,
                        'ip_address'    => $ip,
                        'time'          => $time
                    ) );



            }

            /* Check quantity Out */

            if(isset($quantity_out) and $quantity_out)
            {


                if($rowAssetInventoryAsset)
                {
                    // Tồn Đầu
                    $inventoryFirstOUT  = $rowAssetInventoryAsset['total_quantity'];

                    $inventoryOutAssetEND = $inventoryFirstOUT - $quantity_out;

                    if($inventoryOutAssetEND <0)
                        throw new Exception('Quantity '.((isset($cachedTypeAssetTrainer[$asset_id]) and $cachedTypeAssetTrainer[$asset_id]) ? $cachedTypeAssetTrainer[$asset_id]: null).' NOT ENOUGH !');

                    $dataInsertOUT = array(
                        'asset_id'        => $asset_id,
                        'inventory_first' => $inventoryFirstOUT ,
                        'inventory_out'   => $quantity_out,
                        'inventory_end'   => $inventoryOutAssetEND,
                        'created_at'      => $time,
                        'created_by'      => $userStorage->id

                    );
                }
                else
                {
                    throw new Exception('ASSET '.((isset($cachedTypeAssetTrainer[$asset_id]) and $cachedTypeAssetTrainer[$asset_id]) ? $cachedTypeAssetTrainer[$asset_id]: null).' NOT IN SYSTEM !');
                }

                $QTrainerInventoryHistoryAsset->insert($dataInsertOUT);

                // to do log
                $info = array('Insert Inventory History Out','Asset Id'=>$asset_id,'value'=>array('new'=>$dataInsertOUT));
                $QLog->insert( array (
                    'info'          => json_encode($info),
                    'user_id'       => $userStorage->id,
                    'ip_address'    => $ip,
                    'time'          => $time
                ) );

            }

            return $result;

        }
        catch(Exception $e)
        {
            $result['code'] = -1;
            $result['message'] = $e->getMessage();
            return $result;
        }

    }
}
