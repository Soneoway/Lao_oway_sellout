<?php
class MigrationController extends My_Application_Controller_Cli
{
    public function init()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);    
    }

    /**
     * Chuyển leader_ids từ bảng area ra bảng asm để quản lý asm/area ở bảng riêng
     * @return [type] [description]
     */
    public function asmAreaAction()
    {
        $db = Zend_Registry::get('db');
        $sql = "SELECT * FROM area";
        $areas = $db->query($sql);

        $QAsm = new Application_Model_Asm();

        foreach ($areas as $area) {
            $asms = explode(',', $area['leader_ids']);

            foreach ($asms as $s_id) {
                $data = array(
                    'area_id' => $area['id'],
                    'staff_id' => $s_id
                    );
                $QAsm->insert($data);
            }
        }

        exit;
    }
}