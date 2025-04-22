<?php
/**
 * Để chứa các AJAX action dùng lấy một phần giao diện
 * Ví dụ load một hộp thoại bằng AJAX
 */
class PartitionController extends My_Controller_Action
{
	public function stockAction()
	{
		$this->_helper->layout->disableLayout();
		
		if (! $this->getRequest()->isXmlHttpRequest() ) {
    		$this->_redirect(HOST);
    	}

        //get product
        $QGood = new Application_Model_Good();
        $where = $QGood->getAdapter()->quoteInto('cat_id = ?', PHONE_CAT_ID);
        $goods = $QGood->fetchAll($where, 'desc');
        $this->view->goods = $goods;

        // load all model
        $QGoodColor = new Application_Model_GoodColor();
        $result = $QGoodColor->fetchAll();

        $data = null;
        if ($result->count()) {
            foreach ($goods as $good) {
                $colors = explode(',', $good->color);

                $temp = array();
                foreach ($result as $item){
                    if (in_array($item['id'], $colors)) {
                        $temp[] = array(
                            'id' => $item->id,
                            'model' => $item->name,
                        );
                    }
                }
                $data[$good['id']] = $temp;
            }
        }
        $this->view->good_colors = json_encode($data);
	}

    public function staffTransferAction()
    {
        $this->_helper->layout->disableLayout();
        $id = $this->getRequest()->getParam('staff');
        $type = $this->getRequest()->getParam('type');

        $QStaff = new Application_Model_Staff();
        $staff_check = $QStaff->find($id);
        $staff_check = $staff_check->current();

        if (!$staff_check) {
            return;
        }

        if ($type) {
            switch ($type) {
                case My_Staff_Info_Type::Team:
                    $QModel = new Application_Model_Team();
                    $this->view->list = $QModel->get_cache();
                    $this->view->current_value = $staff_check['team'];
                    break;
                case My_Staff_Info_Type::Department:
                    $QModel = new Application_Model_Department();
                    $this->view->list = $QModel->get_cache();
                    $this->view->current_value = $staff_check['department'];
                    break;
                case My_Staff_Info_Type::Region:
                    $QModel = new Application_Model_RegionalMarket();
                    $this->view->list = $QModel->get_cache();
                    $this->view->current_value = $staff_check['regional_market'];
                    break;
                case My_Staff_Info_Type::Area:
                    $QModel = new Application_Model_Area();
                    $this->view->list = $QModel->get_cache();
                    break;
                case My_Staff_Info_Type::Company:
                    $QModel = new Application_Model_Company();
                    $this->view->list = $QModel->get_cache();
                    $this->view->current_value = $staff_check['company_id'];
                    break;
                case My_Staff_Info_Type::Group:
                    $QModel = new Application_Model_Group();
                    $this->view->list = $QModel->get_cache();
                    $this->view->current_value = $staff_check['group_id'];
                    break;
                case My_Staff_Info_Type::Status:
                    $this->view->list = array(
                                            My_Staff_Status::On => 'On',
                                            My_Staff_Status::Off => 'Off',
                                            My_Staff_Status::Temporary_Off => 'Temporary Off',
                                        );
                    $this->view->current_value = $staff_check['status'];
                    break;
                
                default:
                    # code...
                    break;
            }

            $this->view->type = $type;
        }
    }
}