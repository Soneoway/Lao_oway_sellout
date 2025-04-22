<?php
/**
* 
*/
class My_File
{
    public static function getDefaultDir()
    {
        return $uploaded_dir = APPLICATION_PATH . DIRECTORY_SEPARATOR . '..' 
            . DIRECTORY_SEPARATOR .'public' . DIRECTORY_SEPARATOR . 'files';
    }
    
    /**
     * MUST TRY-CATCH WHEN USING THIS FUNCTION
     *     File is save at
     *         APPLICATION_PATH/../public/files/$save_folder/$userStorage->id/$uniqid
     * @param  [type] $save_folder [description]
     * @param  [type] $uniqid      [description]
     * @param  [type] $new_name    [description]
     * @param  array  $requirement [description]
     * @return [type]              [description]
     *
     * array(
     *           'Size'  => array('min' => 50, 'max' => 5000000),
     *           'Count' => array('min' => 1, 'max' => 1),
     *           'Extension' => array('xlsx', 'xls'),
     *       )
     */
    public static function get($save_folder, $requirement = array())
    {
        set_time_limit(0);
        ini_set('memory_limit', -1);

        $upload = new Zend_File_Transfer();

        $uniqid = uniqid('', true);
        $userStorage = Zend_Auth::getInstance()->getStorage()->read();

        if (!$userStorage) exit;

        $uploaded_dir = APPLICATION_PATH . DIRECTORY_SEPARATOR . '..' 
            . DIRECTORY_SEPARATOR .'public' . DIRECTORY_SEPARATOR . 'files' 
            . DIRECTORY_SEPARATOR . $save_folder
            . DIRECTORY_SEPARATOR . $uniqid;

        if (!is_dir($uploaded_dir))
            @mkdir($uploaded_dir, 0777, true);

        $upload->setDestination($uploaded_dir);
        $upload->setValidators($requirement);

        if (!$upload->isValid()){ // validate IF
            $errors = $upload->getErrors();
            $sError = null;

            if ($errors and isset($errors[0]))
                switch ($errors[0]) {
                    case 'fileUploadErrorIniSize':
                        $sError = 'File size is too large' .( isset($requirement['Size']) ? (' (from '.$requirement['Size']['min'].' bytes to '.$requirement['Size']['max'].' bytes)') : '' );
                        break;
                    case 'fileMimeTypeFalse':
                        $sError = 'The file you selected weren\'t the type we were expecting' .( isset($requirement['Extension']) ? (' (in '.implode(', ', $requirement['Extension']).' format)') : '' );
                        break;
                    case 'fileExtensionFalse':
                        $sError = 'Please choose a file' .( isset($requirement['Extension']) ? (' (in '.implode(', ', $requirement['Extension']).' format)') : '' );
                        break;
                    case 'fileCountTooFew':
                        $sError = 'Please choose a file' .( isset($requirement['Extension']) ? (' (in '.implode(', ', $requirement['Extension']).' format)') : '' );
                        break;
                    case 'fileUploadErrorNoFile':
                        $sError = 'Please choose a file' .( isset($requirement['Extension']) ? (' (in '.implode(', ', $requirement['Extension']).' format)') : '' );
                        break;
                    case 'fileSizeTooBig':
                        $sError = 'File size is too big';
                        break;
                }

            if ($sError) throw new Exception($sError);

        } else {
            $upload->receive();
            
            $path_info = pathinfo($upload->getFileName());
            $filename = $path_info['filename'];
            $extension = $path_info['extension'];

            $old_name = $filename . '.'.$extension;
            $new_name = md5($filename . uniqid('', true)) . '.'.$extension;

            if (is_file($uploaded_dir . DIRECTORY_SEPARATOR . $old_name)){
                rename($uploaded_dir . DIRECTORY_SEPARATOR . $old_name, $uploaded_dir . DIRECTORY_SEPARATOR . $new_name);
            } else {
                $new_name = $old_name;
            }

            $QFileLog = new Application_Model_FileUploadLog();

            $data = array(
                'staff_id'       => $userStorage->id,
                'folder'         => $uniqid,
                'filename'       => $new_name,
                'type'           => $save_folder ? $save_folder : 'file_upload',
                'real_file_name' => $old_name,
                'uploaded_at'    => time(),
                );

            try {
                $log_id = $QFileLog->insert($data);

                return array(
                    'log_id'         => $log_id,
                    'folder'         => $uniqid,
                    'filename'       => $new_name,
                    'real_file_name' => $old_name,
                    );
            } catch (Exception $e) {
            }
            
            return false;
        } // END validate IF

        return false;
    }
}