<?php

class GroupController extends My_Controller_Action
{
    private $data;

    public function indexAction()
    {
        $page = $this->getRequest()->getParam('page', 1);
        $limit = LIMITATION;
        $total = 0;

        $params = array();

        $QGroup = new Application_Model_Group();
        $groups = $QGroup->fetchPagination($page, $limit, $total, $params);

        $this->view->groups = $groups;

        $this->view->limit = $limit;
        $this->view->total = $total;
        $this->view->url = HOST.'group/'.( $params ? '?'.http_build_query($params).'&' : '?' );
        $this->view->offset = $limit*($page-1);

        $flashMessenger = $this->_helper->flashMessenger;
        $messages = $flashMessenger->setNamespace('success')->getMessages();
        $this->view->messages = $messages;
    }

    public function createAction(){

        $id = $this->getRequest()->getParam('id');

        $group_menus = null;

        if ($id) {
            $QGroup = new Application_Model_Group();
            $groupRowset = $QGroup->find($id);
            $group = $groupRowset->current();

            $this->view->group_accesses = json_decode($group->access);

            $group_menus = $group->menu ? explode(',', $group->menu) : null;

            $this->view->group = $group;
        }

        //get all controller and action
        $front = $this->getFrontController();
        $acl = array();

        foreach ($front->getControllerDirectory() as $module => $path) {

            foreach (scandir($path) as $file) {

                if (strstr($file, "Controller.php") !== false) {

                    include_once $path . DIRECTORY_SEPARATOR . $file;

                    foreach (get_declared_classes() as $class) {

                        if (is_subclass_of($class, 'Zend_Controller_Action')) {

                            $controller = lcfirst(substr($class, 0, strpos($class, "Controller")));
                            $tem = '';

                            for ($i=0; $i<strlen($controller); $i++){
                                $char = $controller[$i];
                                if (ord($char)<97)
                                    $tem .= '-'.chr(ord($char)+32);
                                else
                                    $tem .= $char;
                            }
                            $controller = $tem;

                            $actions = array();

                            foreach (get_class_methods($class) as $action) {

                                if (strstr($action, "Action") !== false) {
                                    $action = substr($action, 0, strpos($action, "Action"));
                                    $tem = '';

                                    for ($i=0; $i<strlen($action); $i++){
                                        $char = $action[$i];
                                        if (ord($char)<97)
                                            if ( ord($char) >= 48 && ord($char) <= 57) 
                                                $tem .= $char;
                                            else 
                                                $tem .= '-'.chr(ord($char)+32);
                                        else
                                            $tem .= $char;
                                    }
                                    $actions[] = $tem;
                                }
                            }
                        }
                    }

                    $acl[$module][$controller] = $actions;
                }
            }
        }
        //set current method
        $actions = array();
        foreach (get_class_methods($this) as $action){
            if (strstr($action, "Action") !== false) {
                $action = substr($action, 0, strpos($action, "Action"));
                $tem = '';

                for ($i=0; $i<strlen($action); $i++){
                    $char = $action[$i];
                    if (ord($char)<97)
                        $tem .= '-'.chr(ord($char)+32);
                    else
                        $tem .= $char;
                }
                $actions[] = $tem;
            }
        }

        $acl[$this->getRequest()->getModuleName()][$this->getRequest()->getControllerName()] = $actions;

        $this->view->acl = $acl;

        $QMenu = new Application_Model_Menu();
        $where = $QMenu->getAdapter()->quoteInto('group_id = ?', 1);
        $menus = $QMenu->fetchAll($where, array('parent_id', 'position'));
        foreach ($menus as $menu)
            $this->add_row($menu->id, $menu->parent_id, $menu->title);

        $this->view->menus = $this->generate_list($group_menus);

        $flashMessenger = $this->_helper->flashMessenger;
        $messages = $flashMessenger->setNamespace('error')->getMessages();
        $this->view->messages = $messages;
    }

    public function saveAction(){

        if ($this->getRequest()->getMethod() == 'POST'){
            $group = new Application_Model_Group();

            $id = $this->getRequest()->getParam('id');
            $gid = $this->getRequest()->getParam('gid');
            $name = $this->getRequest()->getParam('name');
            $default_page = $this->getRequest()->getParam('default_page');
            $raw_access = $this->getRequest()->getParam('access');
            $menus = $this->getRequest()->getParam('menus');

            $access = array();
            if (is_array($raw_access)){
                foreach ($raw_access as $module=>$item)
                    foreach ($item as $controller=>$item_2)
                        foreach ($item_2 as $action=>$item_3){
                            if ($item_3)
                                $access[] = $module.'::'.$controller.'::'.$action;
                        }
            }


            $data = array(
                'id'   => $gid,
                'name' => $name,
                'default_page' => $default_page,
                'menu' => ( is_array($menus) ? implode(',', $menus) : null ),
                'access' => json_encode($access),
            );

            if ($id){
                $where = $group->getAdapter()->quoteInto('group.id = ?', $id);

                $group->update($data, $where);
            } else {
                $group->insert($data);
            }

            $flashMessenger = $this->_helper->flashMessenger;
            $flashMessenger->setNamespace('success')->addMessage('Done!');
        }
        $this->_redirect(HOST.'group');
    }

    public function delAction(){
        $id = $this->getRequest()->getParam('id');

        $group = new Application_Model_Group();
        $where = $group->getAdapter()->quoteInto('id = ?', $id);
        $group->delete($where);
        $this->_redirect('/group');
    }

    private function generate_list($group_menus) {
        return $this->ul(0, '', $group_menus);
    }

    function ul($parent = 0, $attr = '', $group_menus = null) {
        static $i = 1;
        $indent = str_repeat("\t\t", $i);
        if (isset($this->data[$parent])) {
            if ($attr) {
                $attr = ' ' . $attr;
            }
            $html = "\n$indent";
            $html .= "<ul$attr>";
            $i++;
            foreach ($this->data[$parent] as $row) {
                $child = $this->ul($row['id'], '', $group_menus);
                $html .= "\n\t$indent";
                $html .= '<li>';
                $html .= '<input value="'.$row['id'].'" '.( ($group_menus and in_array($row['id'] , $group_menus)) ? 'checked' : '' ).' type="checkbox" id="menus_'.$row['id'].'" name="menus[]"><label>'.$row['label'].'</label>';
                if ($child) {
                    $i--;
                    $html .= $child;
                    $html .= "\n\t$indent";
                }
                $html .= '</li>';
            }
            $html .= "\n$indent</ul>";
            return $html;
        } else {
            return false;
        }
    }

    private function add_row($id, $parent, $label) {
        $this->data[$parent][] = array('id' => $id, 'label' => $label);
    }
}

