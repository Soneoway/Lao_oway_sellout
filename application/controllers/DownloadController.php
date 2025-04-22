<?php

class DownloadController extends My_Controller_Action
{
    public function notificationAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $id = $this->getRequest()->getParam('id');
        if (!$id) $this->_redirect(HOST.'user/notification');

        $filename = $this->getRequest()->getParam('filename');
        if (!$filename) $this->_redirect(HOST.'user/notification');

        $hash = $this->getRequest()->getParam('hash');
        if (!$hash) $this->_redirect(HOST.'user/notification');

        // get file
        $QNotificationFile = new Application_Model_NotificationFile();
        $where = array();
        $where[] = $QNotificationFile->getAdapter()->quoteInto('id = ?', $id);
        $where[] = $QNotificationFile->getAdapter()->quoteInto('file_real_name = ?', $filename);

        $file = $QNotificationFile->fetchRow($where);

        if (!$file) $this->_redirect(HOST.'user/notification');

        // check hash
        $userStorage = Zend_Auth::getInstance()->getStorage()->read();
        $hash_check = hash("sha256", md5($userStorage->id) . $filename . $id);

        if ($hash != $hash_check) $this->_redirect(HOST.'user/notification');

        $uploaded_dir = APPLICATION_PATH . DIRECTORY_SEPARATOR . '..' 
            . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'files' 
            . DIRECTORY_SEPARATOR . 'notification'
            . DIRECTORY_SEPARATOR . $file['unique_folder'];

        $file_path = $uploaded_dir . DIRECTORY_SEPARATOR . $filename;

        if (file_exists($file_path)) {
            header('Content-Description: File Transfer');

            $info = new SplFileInfo($file_path);
            $ext = $info->getExtension();

            switch ($ext) {
                case 'xls':
                    header('Content-Type: application/vnd.ms-excel');
                    break;
                case 'xlsx':
                    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                    break;
                case 'ppt':
                    header('Content-Type: application/vnd.ms-powerpoint');
                    break;
                case 'pptx':
                    header('Content-Type: application/vnd.openxmlformats-officedocument.presentationml.presentation');
                    break;
                case 'jpg':
                    header('Content-Type: image/jpeg');
                    break;
                case 'jpeg':
                    header('Content-Type: image/jpeg');
                    break;
                case 'png':
                    header('Content-Type: image/png');
                    break;
                case 'pdf':
                    header('Content-Type: application/pdf');
                    break;
                case 'doc':
                    header('Content-Type: application/msword');
                    break;
                case 'docx':
                    header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
                    break;
                case 'zip':
                    header('Content-Type: application/zip');
                    break;
                case 'rar':
                    header('Content-Type: application/x-rar-compressed');
                    break;
                case '7z':
                    header('Content-Type: application/x-7z-compressed');
                    break;
                
                default:
                    header('Content-Type: application/octet-stream');
                    break;
            }

            $download_file_name = substr( // lấy tối đa 120 kí tự
                                    preg_replace( "/[^a-zA-Z0-9-_]/", "_", // đổi các kí tự khác a-zA-Z0-9-_ thành _
                                            My_String::khongdau(
                                                preg_replace('/\.[a-z]{3,4}$/', '', $file['file_display_name']) // bỏ đuôi
                                                ) // chuyển thành không dấu
                                            ), 0, 120);

            header('Cache-Control: s-maxage=7200');
            header('Content-Disposition: attachment; filename='.$download_file_name.'.'.$ext);
            header('Content-Description: File Transfer');
            header('ETag: ' . hash("sha1", $file['unique_folder'] . $file['file_display_name']));
            header('Content-Encoding: UTF-8');
            header('Content-Length: ' . filesize($file_path));
            header('Pragma: public');
            header('X-Rack-Cache: miss');

            ob_clean();   // discard any data in the output buffer (if possible)
            flush();      // flush headers (if possible)

            readfile($file_path);
            exit;
        } else {
            $flashMessenger = $this->_helper->flashMessenger;
            $flashMessenger->setNamespace('error')->addMessage('File not found.');
            $this->_redirect(HOST.'user/notification');
        }

        exit;
    }
    
    public function informAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $id = $this->getRequest()->getParam('id');
        if (!$id) {

            $this->_redirect(HOST.'manage/inform');
            
        }

        $filename = $this->getRequest()->getParam('filename');
        if (!$filename)
         $this->_redirect(HOST.'manage/inform');

        $hash = $this->getRequest()->getParam('hash');
        
       
        if (!$hash) $this->_redirect(HOST.'manage/inform');

        // get file
        $QInformFile = new Application_Model_InformFile();
        $where = array();
        $where[] = $QInformFile->getAdapter()->quoteInto('id = ?', $id);
        $where[] = $QInformFile->getAdapter()->quoteInto('name = ?', $filename);

        $file = $QInformFile->fetchRow($where);
         

        if (!$file) $this->_redirect(HOST.'manage/inform');

        // check hash
        $userStorage = Zend_Auth::getInstance()->getStorage()->read();
        $hash_check = hash("sha256", md5($userStorage->id) . $filename . $id);

        if ($hash != $hash_check) $this->_redirect(HOST.'manage/inform');

        $uploaded_dir = APPLICATION_PATH . DIRECTORY_SEPARATOR . '..' 
            . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'files' 
            . DIRECTORY_SEPARATOR . 'inform'
            . DIRECTORY_SEPARATOR . $file['unique_folder'];

        $file_path = $uploaded_dir . DIRECTORY_SEPARATOR . $filename;

        if (file_exists($file_path)) {
            header('Content-Description: File Transfer');

            $info = new SplFileInfo($file_path);
            $ext = $info->getExtension();

            switch ($ext) {
                case 'xls':
                    header('Content-Type: application/vnd.ms-excel');
                    break;
                case 'xlsx':
                    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                    break;
                case 'ppt':
                    header('Content-Type: application/vnd.ms-powerpoint');
                    break;
                case 'pptx':
                    header('Content-Type: application/vnd.openxmlformats-officedocument.presentationml.presentation');
                    break;
                case 'jpg':
                    header('Content-Type: image/jpeg');
                    break;
                case 'jpeg':
                    header('Content-Type: image/jpeg');
                    break;
                case 'png':
                    header('Content-Type: image/png');
                    break;
                case 'pdf':
                    header('Content-Type: application/pdf');
                    break;
                case 'doc':
                    header('Content-Type: application/msword');
                    break;
                case 'docx':
                    header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
                    break;
                case 'zip':
                    header('Content-Type: application/zip');
                    break;
                case 'rar':
                    header('Content-Type: application/x-rar-compressed');
                    break;
                case '7z':
                    header('Content-Type: application/x-7z-compressed');
                    break;
                
                default:
                    header('Content-Type: application/octet-stream');
                    break;
            }

            $download_file_name = substr(
                                    preg_replace( "/[^a-zA-Z0-9-_]/", "_", // đổi các kí tự khác a-zA-Z0-9-_ thành _
                                            My_String::khongdau(
                                                preg_replace('/\.[a-z]{3,4}$/', '', $file['file_display_name']) // bỏ đuôi
                                                ) // chuyển thành không dấu
                                            ), 0, 120);

            header('Cache-Control: s-maxage=7200');
            header('Content-Disposition: attachment; filename='.$download_file_name.'.'.$ext);
            header('Content-Description: File Transfer');
            header('ETag: ' . hash("sha1", $file['unique_folder'] . $file['file_display_name']));
            header('Content-Encoding: UTF-8');
            header('Content-Length: ' . filesize($file_path));
            header('Pragma: public');
            header('X-Rack-Cache: miss');

            ob_clean();   // discard any data in the output buffer (if possible)
            flush();      // flush headers (if possible)

            readfile($file_path);
            exit;
        } else {
            $flashMessenger = $this->_helper->flashMessenger;
            $flashMessenger->setNamespace('error')->addMessage('File not found.');
            $this->_redirect(HOST.'manage/inform');
        }

        exit;
    }

}