<?php 
$this->_helper->layout->disableLayout();
$this->_helper->viewRenderer->setRender('notification/mass-upload-save');

$save_folder   = 'notification';
$new_file_path = '';
$requirement   = array(
    'Size'      => array('min' => 5, 'max' => 5000000),
    'Count'     => array('min' => 1, 'max' => 1),
    'Extension' => array('xls', 'xlsx'),
    );

try {
    set_time_limit(0);
    ini_set('memory_limit', -1);

    $file = My_File::get($save_folder, $requirement);

    if (!$file || !count($file))
        throw new Exception("Upload failed");

    $inputFileName = My_File::getDefaultDir() . DIRECTORY_SEPARATOR . $save_folder .
        DIRECTORY_SEPARATOR . $file['folder'] . DIRECTORY_SEPARATOR . $file['filename'];

    $userStorage = Zend_Auth::getInstance()->getStorage()->read();
}
catch (exception $e) {
    $this->view->errors = $e->getMessage();
    return;
}

//read file
include 'PHPExcel/IOFactory.php';

//  Read your Excel workbook
try {
    $inputFileType = PHPExcel_IOFactory::identify($inputFileName);
    $objReader = PHPExcel_IOFactory::createReader($inputFileType);
    $objPHPExcel = $objReader->load($inputFileName);
}
catch (exception $e) {
    $this->view->errors = $e->getMessage();
    return;
}

//  Get worksheet dimensions
$sheet = $objPHPExcel->getSheet(0);
$highestRow = $sheet->getHighestRow();
$highestColumn = $sheet->getHighestColumn();
$db = Zend_Registry::get('db');

$title = $this->getRequest()->getParam('title');
$content = $this->getRequest()->getParam('content');
$category = $this->getRequest()->getParam('category');
$category  = intval($category);

if (!$title) {
    $this->view->errors = 'Title cannot be blank';
    return;
}

if (!$content) {
    $this->view->errors = 'Content cannot be blank';
    return;
}

// kiểm tra category
$QCategory = new Application_Model_NotificationCategory();
$category_check = $QCategory->find($category);
$category_check = $category_check->current();

if (!$category_check) {
    $this->view->errors = 'Invalid category';
    return;
}

$QStaff = new Application_Model_Staff();

$i = 0;
$total = $highestRow - 2;

$error_list = array();

for ($row = 3; $row <= $highestRow; $row++) {
    //  Read a row of data into an array
    $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, null, true, false);
    $rowData = $rowData[0];

    if (!is_array($rowData) || !count($rowData))
        continue;

    if (empty($rowData[0]) && !empty($rowData[1])) {
        $error_list[$row] = "Empty code";
        continue;
    }

    foreach ($rowData as $key => $value)
        $rowData[$key] = trim($value);

    $staff_code = $rowData[0];
    $where = $QStaff->getAdapter()->quoteInto('code = ?', $staff_code);
    $staff_check = $QStaff->fetchRow($where);

    if (!$staff_check) {
        $error_list[$row] = "Wrong staff code: " . $staff_code;
        continue;
    }

    $new_content = preg_replace_callback('/{(\d+)}/', function($matches) use (&$rowData) {
        return $rowData[ $matches[1] ];
    }, $content);

    My_Notification::add(
        $title, 
        $new_content, 
        $category, 
        array(
            'staff' => $staff_check['id'],
            'from'  => date('d/m/Y H:i:s'),
            'to'    => (new DateTime('now'))
                ->add(new DateInterval('P'.My_Notification_Type::MassUpload_Expire.'D'))
                ->format('d/m/Y H:i:s'),
        ),
        false,
        My_Notification_Type::MassUpload
    );

} //end for

$this->view->result = "Done";
$this->view->error_list = $error_list;
