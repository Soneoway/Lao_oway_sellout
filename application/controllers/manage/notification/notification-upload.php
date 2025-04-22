<?php
$this->_helper->layout->disableLayout();
$this->_helper->viewRenderer->setNoRender(true);

$save_folder   = 'notification';
$new_file_path = '';
$requirement   = array(
                'Size'      => array('min' => 50, 'max' => 5000000),
                'Count'     => array('min' => 1, 'max' => 1),
                'Extension' => array('jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'zip', 'rar', '7z'),
            );

$file = My_File::get($save_folder, $requirement);
$result = array();

if ($file && count($file)) {
    $result['success']           = true;
    $result['file_id']           = $file['log_id'];
    $result['keypass']           = uniqid();    
    $result['file_display_name'] = $file['real_file_name'];
    $result['hashstr']           = hash("sha256", $file['folder'] . $result['keypass'] . $file['filename'] . $save_folder .$file['log_id']);
} else
    $result['success'] = false;

echo htmlspecialchars(json_encode($result), ENT_NOQUOTES);

exit;