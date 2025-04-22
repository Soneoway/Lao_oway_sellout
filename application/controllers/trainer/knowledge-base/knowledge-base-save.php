<?php
if ($this->getRequest()->getMethod() == 'POST') {
    $QModel = new Application_Model_SalesKnowledgeBase();

    $id = $this->getRequest()->getParam('id');
    $name = $this->getRequest()->getParam('title');
    $type_level = $this->getRequest()->getParam('type_level');
    $knowledge_type = $this->getRequest()->getParam('knowledge_type');
    $img_title = $this->getRequest()->getParam('img_title');
    $img_title_edit = $this->getRequest()->getParam('img_title_edit');
    $detail = $this->getRequest()->getParam('detail');
    $enable = $this->getRequest()->getParam('enable');

    $userStorage = Zend_Auth::getInstance()->getStorage()->read();

    $public_path = realpath(APPLICATION_PATH . '/../public/');
    $uploaded_dir = $public_path . DIRECTORY_SEPARATOR . 'trainer';

    $upload = new Zend_File_Transfer();
    $files = $upload->getFileInfo();

    $img_title = '';
    $val = $uploaded_dir;

    $key_img_title = 'img_title';
    $fileContentInfo = (isset($files[$key_img_title]) and $files[$key_img_title]) ? $files[$key_img_title] : null;
    if (isset($fileContentInfo['name']) and $fileContentInfo['name']) {

        if (!is_dir($val))
            @mkdir($val, 0777, true);

        $upload->setDestination($val);
        $old_name = $fileContentInfo['name'];
        $tExplode = explode('.', $old_name);
        $extension = end($tExplode);
        $img_title = 'imgtitle-' . md5(uniqid('', true)) . '.' . $extension;
        $upload->addFilter('Rename', array('target' => $val . DIRECTORY_SEPARATOR . $img_title));
        $r = $upload->receive(array($key_img_title));

        if ($r)
            $data[$key_img_title] = $img_title;
        else {
            $messages = $upload->getMessages();
            foreach ($messages as $msg)
                throw new Exception($msg);
        }
    }

    function LinkYoutube($link_youtube)
    {
        preg_match('#(?:https://)?(?:http://)?(?:www\.)?(?:youtube\.com/(?:v/|watch\?v=)|youtu\.be/)([\w-]+)(?:\S+)?#', $link_youtube, $match);
        $embed = "https://www.youtube.com/embed/$match[1]";
        return str_replace($match[0], $embed, $link_youtube);
    }

    function UpdateFileEmpty($new_name, $file_edit)
    {
        if ($new_name) {
            return $new_name;
        } else {
            return $file_edit;
        }
    }

    $data_insert = array(
        'title' => $name,
        'knowledge_type' => $knowledge_type,
        'img_title' => $img_title,
        'detail' => $detail,
        'enable' => $enable,
        'created_at' => date('Y-m-d H:i:s'),
        'created_by' => $userStorage->id,
    );

    $data_update = array(
        'title' => $name,
        'knowledge_type' => $knowledge_type,
        'img_title' => UpdateFileEmpty($img_title, $img_title_edit),
        'detail' => $detail,
        'enable' => $enable,
        'updated_at' => date('Y-m-d H:i:s'),
        'updated_by' => $userStorage->id,
    );

    if ($id) {
        $where = $QModel->getAdapter()->quoteInto('id = ?', $id);
        $QModel->update($data_update, $where);
    } else {
        $QModel->insert($data_insert);
    }

    $flashMessenger = $this->_helper->flashMessenger;
    $flashMessenger->setNamespace('success')->addMessage('Done!');
}

$back_url = $this->getRequest()->getParam('back_url');
$this->_redirect(($back_url ? $back_url : HOST . 'trainer/index'));