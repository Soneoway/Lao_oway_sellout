<?php
/**
 * Các thống kê
 */
class StatisticsController extends My_Controller_Action
{
	public function stockAction()
	{
		$page   = $this->getRequest()->getParam('page', 1);
		$desc   = $this->getRequest()->getParam('desc', 1);
		$sort   = $this->getRequest()->getParam('sort', 'area');
		$store  = $this->getRequest()->getParam('store');
		$area   = $this->getRequest()->getParam('area');
		$region = $this->getRequest()->getParam('region');
		$dealer = $this->getRequest()->getParam('dealer');
		$date   = $this->getRequest()->getParam('date', date('d/m/Y'));

		$limit   = LIMITATION;
		$total   = 0;

		$QGood      = new Application_Model_Good();
		$QGoodColor = new Application_Model_GoodColor();
		$QArea      = new Application_Model_Area();
		$QStore     = new Application_Model_Store();
		$QRegion    = new Application_Model_RegionalMarket();

		// kiểm tra nếu là sales admin hay asm thì áp đặt area id của nó
		$userStorage = Zend_Auth::getInstance()->getStorage()->read();

		if (in_array($userStorage->group_id, array(SALES_ADMIN_ID, ASM_ID, ASMSTANDBY_ID))) {
			// lấy area id hiện tại của nó
			$region_cache = $QRegion->get_cache_all();

			$area = isset($region_cache[ $userStorage->regional_market ]) 
				? $region_cache[ $userStorage->regional_market ]['area_id'] 
				: 0;

			$region = (isset($region_cache[ $region ]) && $region_cache[ $region ]['area_id'] == $area)
				? $region 
				: 0;
			
			// sales admin quản lý 2 khu vực
			$area = $QArea->find($area);
			$area = $area->current();

			if ($area) {

				// sales admin HCM thì xem được toàn hcm
				if ( in_array( $area['id'], array(HCMC1, HCMC2, HCMC3, HCMC4) ) ) {
					$area = array(HCMC1, HCMC2, HCMC3, HCMC4);
				// khu vực khác thì lấy theo asm
				} elseif ( in_array( $area['id'], array(AG) )) {
					$area = array(AG);
				}  elseif ( in_array( $area['id'], array(VINH) )) {
					$area = array(VINH);
				} else {

					$user_id = $area['leader_ids'];
					$where_2 = $QArea->getAdapter()->quoteInto('FIND_IN_SET(?, leader_ids)', $user_id);
					$areas   = $QArea->fetchAll($where_2);
					$area    = array();

					foreach ($areas as $a) {
						$area[] = $a['id'];
					}
				}
			}
		}

		$params = array(
			'store'  => $store,
			'area'   => $area, // maybe an array or just a number
			'region' => $region,
			'dealer' => $dealer, // dealer name, not ID
			'date'   => $date,
			'sort'   => $sort,
			'desc'   => $desc,
			);

		$QStock = new Application_Model_StockCurrent();
		$stocks = $QStock->fetchPagination($page, $limit, $total, $params);

		$stocks_arr = array();
        $stock_by_model = array();
		$stock_by_store = array();
        $sum = 0;

		foreach ($stocks as $key => $value) {
			if ( !isset($stocks_arr[ $value['store_id'] ]) ) {
				$stocks_arr[ $value['store_id'] ] = array(
														'area' => $value['area_name'],
														'stock' => array()
													);
			}

			if ( ! isset( $stocks_arr[ $value['store_id'] ]['stock'][$value['product_id']] ) ) {
				$stocks_arr[ $value['store_id'] ]['stock'][$value['product_id']] = array();
			}

            // Tính tổng theo model
			if ( ! isset( $stock_by_model[ $value['product_id'] ] ) ) {
				$stock_by_model[ $value['product_id'] ] = array();
			}

			if ( ! isset( $stock_by_model[ $value['product_id'] ][ $value['model'] ] ) ) {
				$stock_by_model[ $value['product_id'] ][ $value['model'] ] = 0;
			}

			$stocks_arr[ $value['store_id'] ]['stock'][ $value['product_id'] ][ $value['model'] ] = array('number' => $value['number'], 'date' => $value['date']);
			$stock_by_model[ $value['product_id'] ][ $value['model'] ] += $value['number'];

            // tính tổng theo store
            if ( ! isset( $stock_by_store[ $value['store_id'] ] )  ) {
                $stock_by_store[ $value['store_id'] ] = 0;
            }

            $stock_by_store[ $value['store_id'] ] += $value['number'];

            $sum += $value['number'];
		}

		// $this->view->stocks = $stocks;
		$res = array_slice($stocks_arr, $limit*($page-1), LIMITATION, true);
		$total = count($stocks_arr);

		$this->view->stocks_arr = $res;
		// $this->view->stocks_arr = $stocks_arr;
		$this->view->desc   = $desc;
		$this->view->sort   = $sort;
		$this->view->params = $params;
		$this->view->limit  = $limit;
		$this->view->total  = $total;
		$this->view->url    = HOST.'statistics/stock'.( $params ? '?'.http_build_query($params).'&' : '?' );
		$this->view->offset = $limit*($page-1);

		$heads = array(
                '#',
                'area' => 'Area',
                'store' => 'Store',
                'Store Total',
            );

        $where = $QGood->getAdapter()->quoteInto('cat_id = ?', PHONE_CAT_ID);
        $goods = $QGood->fetchAll($where, 'desc');

		$this->view->goods       = $goods;
		$this->view->good_colors = $QGoodColor->get_cache();
		$this->view->areas       = $QArea->get_cache();

		// Nếu chọn sẵn area thì load các region thuộc area tương ứng
		if ( isset($area) && $area ) {
			if (is_array($area)) {
				$where = $QRegion->getAdapter()->quoteInto('area_id IN (?)', $area);
			} else {
				$where = $QRegion->getAdapter()->quoteInto('area_id = ?', $area);
			}
			
			$regions = $QRegion->fetchAll($where);
			$region_arr = array();

			foreach ($regions as $key => $value) {
				$region_arr[$value['id']] = $value['name'];
			}
			
			$this->view->regions  = $region_arr;
		}

		// nếu chọn sẵn region thì load store thuộc region tương ứng
		if ( isset( $region ) && $region ) {
			$where = $QStore->getAdapter()->quoteInto('regional_market = ?', $region);
			$stores = $QStore->fetchAll($where);

			$store_arr = array();

			foreach ($stores as $key => $value) {
				$store_arr[$value['id']] = $value['name'];
			}
			
			$this->view->stores  = $store_arr;

		// nếu chọn sẵn area mà ko chọn region thì load store thuộc area tương ứng
		} elseif ( isset($area) && $area ) {
			// tìm region thuộc area đó
			if (is_array($area)) {
				$where = $QRegion->getAdapter()->quoteInto('area_id IN (?)', $area);
			} else {
				$where = $QRegion->getAdapter()->quoteInto('area_id = ?', $area);
			}

			$regions = $QRegion->fetchAll($where);

			$region_arr = array();

			foreach ($regions as $key => $value) {
				$region_arr[] = $value['id'];
			}

			// tìm store thuộc các region trên
			$where = $QStore->getAdapter()->quoteInto('regional_market IN (?)', $region_arr);
			$stores = $QStore->fetchAll($where);

			$store_arr = array();

			foreach ($stores as $key => $value) {
				$store_arr[$value['id']] = $value['name'];
			}
			
			$this->view->stores  = $store_arr;

		// mặc định có nhiêu lấy hết
		} else {
			$this->view->stores   = $QStore->get_cache();
		}

		$good_colors = $QGoodColor->get_phone_colors($QStock->remove_list);

		foreach ($good_colors as $k => $v) {
            $str = $v['good_name'] .'<br />';
            $tmp = '';

            switch (strtoupper($v['color_id'])) {

                case 10:
                    $tmp = 'W';
                    break;
                case 9:
                    $tmp = 'B';
                    break;
                case 22:
                    $tmp = 'S';
                    break;
                case 25:
                    $tmp = 'G';
                    break;
                
                default:
                    
                    break;
            }

            $str .= $tmp;
			$heads[] = $str;
		}

        $this->view->sum = $sum;
		$this->view->good_colors = $good_colors;
		$this->view->heads = $heads;
        $this->view->stock_by_model = $stock_by_model;
		$this->view->stock_by_store = $stock_by_store;
	}
}