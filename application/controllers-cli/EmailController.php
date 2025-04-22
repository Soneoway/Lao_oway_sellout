<?php
class EmailController extends My_Application_Controller_Cli
{
    private $hr_signature = '<h3 style="color: #00b050;margin-bottom: 0;">Hr Department</h3>
                    <p style="margin-bottom: 0;
                        margin-top: 0;">__________________________________</p>
                    <p style="margin-bottom: 5px;
                        margin-top: 5px;">T (84-8) 39202555 -ext:111</p>
                    <p style="margin-bottom: 5px;
                        margin-top: 5px;">F (84-8) 39204095</p>
                    <p style="margin-bottom: 5px;
                        margin-top: 5px;">E <a href="mailto:hr@oppomobile.vn">hr@oppomobile.vn</a></p>
                    <p style="margin-bottom: 5px;
                        margin-top: 5px;">OPPO Science & Technology Co.,Ltd</p>
                    <p style="margin-bottom: 5px;
                        margin-top: 5px;">SCB Tower, 242 Cong Quynh St, Pham Ngu Lao Ward, Dist 1, HCMC.</p>
                    <p style="margin-bottom: 0;
                        margin-top: 0;">__________________________________</p>
                    <p style="margin-bottom: 5px;
                        margin-top: 0;"><a href="http://www.oppomobile.vn/">www.oppomobile.vn</a></p>';

    public function init()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
    }

    public function birthdayAction()
    {
        /////////////////////////
        /////// CONFIG
        /////////////////////////
        error_reporting(~E_ALL);
        ini_set("display_error", 0);
        set_time_limit(0);

        $app_config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini');

        $config = array(
            'auth'     => $app_config->mail->smtp->auth,
            'username' => $app_config->mail->smtp->user2,
            'password' => $app_config->mail->smtp->pass2,
            'port'     => $app_config->mail->smtp->port,
            'ssl'      => $app_config->mail->smtp->ssl
        );

        // Thay mặt cho BCH Công đoàn và BGĐ Công ty
        $wish = array(
            'Thay mặt BCH Công Đoàn & BGĐ Công Ty, Chúc bạn Sinh nhật đầy áp yêu thuơng và tiếng cười, thêm tuổi, thêm hạnh phúc, thêm nhiều niềm vui nhé. Happy Birthday!',
            'Thay mặt BCH Công Đoàn & BGĐ Công Ty, Chúc bạn có những phút giây thật tuyệt vời bên bạn bè và người thân trong ngày quan trọng này. Hi vọng bạn luôn thành công và hạnh phúc trong cuộc sống. Happy Birthday!',
            'Thay mặt BCH Công Đoàn & BGĐ Công Ty gửi đến bạn: Hôm nay không như ngày hôm qua, hôm nay là một ngày đặc biệt, là ngày mà một thiên thần đáng yêu đã có mặt trên thế giới này. Luôn mỉm cười và may mắn nhé.',
            'Thay mặt BCH Công Đoàn & BGĐ Công Ty, Chúc mọi điều ước trong ngày sinh nhật của bạn đều trở thành hiện thực, hãy thổi nến trên bánh sinh nhật để ước mơ được nhiệm màu.',
            'Nhân dịp sinh nhật của bạn, thay mặt BCH Công Đoàn & BGĐ Công Ty chúc bạn luôn tươi khỏe, trẻ đẹp. Cầu mong những gì may mắn nhất, tốt đẹp nhất và hạnh phúc nhất sẽ đến với bạn trong tuổi mới.',
            );

        /////////////////////////
        /////// GET STAFFs HAVING BIRTHDAY TODAY
        /////////////////////////
        $QTeam   = new Application_Model_Team();
        $QArea   = new Application_Model_Area();
        $QStaff  = new Application_Model_Staff();
        $QRegion = new Application_Model_RegionalMarket();
        $QStaff  = new Application_Model_Staff();

        $team_cache = $QTeam->get_all_cache();
        $areas      = $QArea->get_cache();

        $id = $this->getRequest()->getParam('id');

        if ($id && intval($id) > 0) {
            $where = $QStaff->getAdapter()->quoteInto('id = ?', $id);
        } else {
            $where   = array();
            $date = date('d/m/').'%';
            $date2 = date('j/n/').'%';
            $where[] = $QStaff->getAdapter()->quoteInto(sprintf("dob LIKE '%s' OR dob LIKE '%s'", $date, $date2), 1);
            $where[] = $QStaff->getAdapter()->quoteInto('off_date IS NULL', 1);
        }

        $staffs  = $QStaff->fetchAll($where);

        /////////////////////////
        /////// SEND EMAIL
        /////////////////////////
        foreach ($staffs as $k => $staff) {
            $transport = new Zend_Mail_Transport_Smtp ($app_config->mail->smtp->host, $config);

            $mail = new My_Mail($app_config->mail->smtp->charset);

            $mail->setFrom($app_config->mail->smtp->from2, $app_config->mail->smtp->from2);

            $mail->setSubject('Happy Birthday');

            $d = explode('/', $staff['dob']);
            $d = $d[0].'/'.$d[1];

            $header = '<div>';
                $header .= '<table style="width: 900px;border: 1px solid #aaa;">';
                $header .= "<tr>";
                $header .= '<td style="width: 440px;padding:10px;">';

                $header .= "<img src='http://center.opposhop.vn/photo/mail/birthday/".rand(1,23).".jpg'
                            width='440px' alt='happy birthday' />";

                $header .= "</td>";
                $header .= "<td style='width: 440px;padding:10px;'>";

                $header .= "<h2 style=\"font-size: 26.0pt;
                        font-family: \'Times New Roman\',\'serif\';
                        color: red;
                        font-style: italic;
                        text-align:center;
                        margin-top:10px;\">Happy birthday!!!</h2>";

                $header .= "<p style=\"font-size: 20.0pt;
                        font-family: \'Times New Roman\',\'serif\';
                        font-style: italic;
                        color: #00b050;\">Nhân ngày sinh nhật, gửi lời chúc tốt đẹp đến bạn</p>";

                $content = $header;

                $content .= "<p  style=\"font-size: 19.0pt;
                                font-family: \'Times New Roman\',\'serif\';
                                font-weight: bold;
                                color: red;
                                margin-bottom: 5px;
                                margin-top: 10px;\">".$this->mb_ucwords( $staff['firstname']." ".$staff['lastname'] )."</p>";

                $content .= "<p style=\"font-size: 17.0pt;
                                font-family: \'Times New Roman\',\'serif\';
                                color: red;
                                margin-bottom: 4px;
                                margin-top: 5px;\">Bộ phận: ".$this->mb_ucwords( @$team_cache[ $staff['department'] ]['name'] )."</p>";

                $content .= "<p style=\"font-size: 17.0pt;
                                font-family: \'Times New Roman\',\'serif\';
                                color: red;
                                margin-bottom: 4px;
                                margin-top: 5px;\">Team: ".$this->mb_ucwords( @$team_cache[ $staff['team'] ]['name'] )."</p>";

                $content .= "<p style=\"font-size: 17.0pt;
                                font-family: \'Times New Roman\',\'serif\';
                                color: red;
                                margin-bottom: 5px;
                                margin-top: 5px;\">Chức vụ: ".$this->mb_ucwords( @$team_cache[ $staff['title'] ]['name'] )."</p>";

                $content .= "<p style=\"font-size: 17.0pt;
                                font-family: \'Times New Roman\',\'serif\';
                                color: red;
                                margin-bottom: 10px;
                                margin-top: 5px;\">Sinh nhật: ".$d."</p>";



                $footer = "<p style=\"font-size: 20.0pt;
                        font-family: \'Times New Roman\',\'serif\';
                        font-style: italic;
                        color: #0070c0;
                        margin-bottom: 5px;
                        margin-top: 10px;
                        text-align:justify;\">".$wish[array_rand($wish, 1)]."</p>";

                $footer .= "</td>";
                $footer .= "</tr>";
                $footer .= "</table>";
            $footer .= '</div>';

            $content .= $footer;
            $content .= $this->hr_signature;

            $mail->setBodyHtml($content);

            $mailto = array(); // Config this

            // lấy khu vực để chọn email gửi tới
            $region_id = (isset($staff['regional_market']) && $staff['regional_market']) ? $staff['regional_market'] : 0;
            $region = $QRegion->find($region_id);
            $region = $region->current();

            if ($region) {
                $area = $QArea->find($region['area_id']);
                $area = $area->current();

                if ($area) {

                    if ( $area['id'] == HCMC4
                            && isset($staff['is_officer']) && $staff['is_officer']) {
                        $mailto[] = EMAIL_HCM_OFFICE; // gửi nv văn phòng HCM

                    } elseif ( !empty($area['email']) ) {
                        $mailto[] = $area['email']; // email khu vực
                    }
                }
            }

            // email cá nhân
            if (!empty($staff['email'])) {
                $mailto[] = $staff['email'];
            }

            // nếu là asm thì gửi vào EMAIL_ASM
            if ( isset($staff['group_id']) && in_array($staff['group_id'], array(ASM_ID, ASMSTANDBY_ID)) ) {
                $mailto[] = EMAIL_ASM;

            } elseif ( isset($staff['group_id']) && $staff['group_id'] == SALES_ADMIN_ID ) {
                $mailto[] = EMAIL_SALESADMIN;
            }

            if ( !empty( $team_cache[ $staff['team'] ]['email'] ) ) {
                $mailto[] = $team_cache[ $staff['team'] ]['email'];

            } elseif ( !empty( $team_cache[ $staff['department'] ]['email'] ) ) {
                $mailto[] = $team_cache[ $staff['department'] ]['email'];
            }

            $mailto[] = 'buu.pham@oppomobile.vn'; // gửi thêm vô đây để check xem mail nó ổn ko :-s haiz, hồi hộp v~ đạn.
            $mailto[] = 'yen.dao@oppomobile.vn';

            // for debug only
            foreach ($mailto as $key => $value) {
                echo date('Y-m-d H:i:s') .": ".$value."\n";

            }//

            if (isset($mailto) && count($mailto)) {
                $mail->addTo($mailto);

                $r = $mail->send($transport);
                sleep(1);
            }
        }

        exit;
    }

    public function welcomeAction()
    {
        /////////////////////////
        /////// CONFIG
        /////////////////////////
        error_reporting(~E_ALL);
        ini_set("display_error", 0);
        set_time_limit(0);
        $mailfrom = 'hr@oppomobile.vn'; // Config this
        $mailfromname = "HR"; // Config this

        $subject = 'Chào đón nhân viên mới!';

        /////////////////////////
        /////// GET STAFFs HAVING BIRTHDAY TODAY
        /////////////////////////
        $QRegion = new Application_Model_RegionalMarket();
        $QTeam   = new Application_Model_Team();
        $QArea   = new Application_Model_Area();
        $QStaff  = new Application_Model_Staff();

        $team_cache = $QTeam->get_all_cache();

        $areas   = $QArea->get_cache();
        $regional_market_cache = $QRegion->get_cache_all();

        $db = Zend_Registry::get('db');

        $sql = "SELECT
                    *
                FROM
                    staff s
                WHERE
                    s.id NOT IN(
                        SELECT
                            staff_id
                        FROM
                            welcome_email
                    )
                AND s.email IS NOT NULL
                AND s.email <> ''
                AND s.title <> 0
                AND s.title <> ''
                AND s.title IS NOT NULL
                AND s.group_id NOT IN (".implode(',', array(PGPB_ID, SALES_ID, LEADER_ID)).")
                AND s.group_id <> 0
                AND s.group_id IS NOT NULL
                AND s.is_officer = 1
                AND s.joined_at <= '".date( 'Y-m-d' )."'
                AND s.off_date IS NULL
                AND s.code NOT IN ('14050110', '14050124', '14070095',
                    '14070096', '14050096', '14080335', '14080334',
                    '1308HN142', '14090369', '14090370', '15070090')
                ";

        $staffs = $db->query( $sql );
        $to_send_emails = array();

        $sql = "INSERT INTO welcome_email(staff_id) VALUES ";

        // find to-send email per staff
        foreach ($staffs as $k => $staff) {
            if (trim($staff['email']) == '')
                continue;

            $mailto = array(); // Config this

            $region_id = (isset($staff['regional_market']) && $staff['regional_market']) ? $staff['regional_market'] : 0;
            $region = $QRegion->find($region_id);
            $region = $region->current();

            if ($region) {
                $area = $QArea->find($region['area_id']);
                $area = $area->current();

                if ($area) {
                    if ($staff['team'] == TRAINING_TEAM) {
                        if ( $area['id'] == HCMC4 )
                            $mailto[] = EMAIL_HCM_OFFICE; // gửi nv văn phòng HCM

                    } else {
                        if ( $area['id'] == HCMC4
                                && isset($staff['is_officer']) && $staff['is_officer']
                                && !in_array($staff['department'], array(WARRANTY_CENTER, SERVICE_CENTER, CALL_CENTER))
                                ) {
                            $mailto[] = EMAIL_HCM_OFFICE; // gửi nv văn phòng HCM

                        } elseif ( !empty($area['email']) ) {
                            $mailto[] = $area['email']; // email khu vực
                        }
                    }
                }

                // email cá nhân
                if (!empty($staff['email'])) {
                    $mailto[] = $staff['email'];
                }

                // nếu là asm thì gửi vào EMAIL_ASM
                if ( isset($staff['group_id']) && $staff['group_id'] == ASM_ID ) {
                    $mailto[] = EMAIL_ASM;
                } elseif ( isset($staff['group_id']) && $staff['group_id'] == SALES_ADMIN_ID ) {
                    $mailto[] = EMAIL_SALESADMIN;
                }

                if ( !empty( $team_cache[ $staff['team'] ]['email'] ) ) {
                    $mailto[] = $team_cache[ $staff['team'] ]['email'];

                } elseif ( !empty( $team_cache[ $staff['department'] ]['email'] ) ) {
                    $mailto[] = $team_cache[ $staff['department'] ]['email'];
                }

                // echo $staff['email'];
                // $mailto = array();
                $mailto[] = 'buu.pham@oppomobile.vn';
                $mailto[] = 'yen.dao@oppomobile.vn';
                $mailto[] = 'hr@oppomobile.vn';

                $mailto = array_unique($mailto);

                $to_send_emails[$staff['id']] = $mailto;
            }

            $sql .= " (".$staff['id']."),";

        }

        // list to-send emails
        $to_send_group = array();

        foreach ($to_send_emails as $staff_email => $mailto_list) {
            if ( ! $this->array_in_array($mailto_list, $to_send_group) ) {
                $to_send_group[] = array('group' => $mailto_list);
            }
        }

        // group staffs by to-send emails
        foreach ($to_send_group as $key => $item) {
            foreach ($to_send_emails as $staff_email => $mailto_list) {
                if (0 == count( array_diff($item['group'], $mailto_list) ) && 0 == count( array_diff($mailto_list, $item['group']) )) {

                    if ( ! isset( $to_send_group[$key]['staff'] ) ) {
                        $to_send_group[$key]['staff'] = array();
                    }

                    $to_send_group[$key]['staff'][] = $staff_email;
                }
            }
        }

        $sql = trim($sql, ',');

        /////////////////////////
        /////// SEND EMAIL
        /////////////////////////
        foreach ($to_send_group as $group) {

            if (count($group['staff']) == 0) {
                continue;
            }

            $content = '<div><p class="">
                Dear all,
                <br>OPPO chào đón thêm thành viên mới';

            foreach ($group['staff'] as $staff_id) {
                $staff = $QStaff->find($staff_id);
                $staff = $staff->current();

                if (!$staff)
                    continue;

                echo $staff['email']."\n";
                $content .= '<br>
                    <br>'. $this->mb_ucwords( $staff['firstname'] . ' ' . $staff['lastname'] ) .'
                    <br>Bộ phận: '. $this->mb_ucwords(  @$team_cache[$staff['department']]['name'] ).'
                    <br>Team: '. $this->mb_ucwords(  @$team_cache[$staff['team']]['name'] ).'
                    <br>Chức vụ: '. $this->mb_ucwords(  @$team_cache[$staff['title']]['name'] ) .
                    (
                        isset( $areas[ $regional_market_cache[ $staff['regional_market'] ]['area_id'] ] )
                            ? ( '<br>Khu vực: '.$areas[ $regional_market_cache[ $staff['regional_market'] ]['area_id'] ] )
                            : ''
                    ) .
                    (
                        isset( $regional_market_cache[ $staff['regional_market'] ]['name'] )
                            ? ( '<br>Tỉnh: '.$regional_market_cache[ $staff['regional_market'] ]['name'] )
                            : ''
                    ) .'
                    <br>Mail liên lạc: <span><a href="mailto:'.$staff['email'].'" target="_blank">'.$staff['email'].'</a></span>';
            }

            $content .= '<br><br>Mọi người công tác và hỗ trợ nhân viên mới hoàn thành nhiệm vụ nha.
                    <br>Thân mến!';

            $content .= '</p></div>';

            $content .= $this->hr_signature;

            $mailto = $group['group']; // Config this

            $this->sendmail($mailto, $subject, $content);
            sleep(1);

            // for debug only
            foreach ($mailto as $key => $value) {
                echo date('Y-m-d H:i:s') .": ".$value."\n";

            }//
        }

        if(isset($mailto) && count($mailto))
            $db->query($sql);

        exit;
    }

    private function sendmail($to, $subject, $maildata, $image = ''){
        include_once APPLICATION_PATH.'/../library/phpmail/class.phpmailer.php';
        $app_config   = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini');
        $config       = $app_config->mail->smtp;

        $mailfrom     = $config->user2;
        $mailfromname = $config->fromname2;
        $mail = new PHPMailer();

        $body = $maildata; // nội dung email
        $body = eregi_replace("[\]",'',$body);

        $mail->IsHTML();

        $mail->IsSMTP();
        $mail->SMTPDebug  = 1;                     // enables SMTP debug information (for testing)
                                                   // 1 = errors and messages
                                                   // 2 = messages only
        $mail->SMTPAuth   = true;                  // enable SMTP authentication
        $mail->SMTPSecure = $config->ssl;                 // sets the prefix to the servier // Config this
        $mail->Host       = $config->host;      // sets GMAIL as the SMTP server // Config this
        $mail->Port       = $config->port;                   // set the SMTP port for the GMAIL server // Config this
        $mail->Username   = $config->user2;  // GMAIL username // Config this
        $mail->Password   = $config->pass2;            // GMAIL password // Config this

        $mail->From = $mailfrom;
        $mail->FromName = $mailfromname;

        $mail->SetFrom($mailfrom, $mailfromname); //Định danh người gửi

        //$mail->AddReplyTo($mailfrom, $mailfromname); //Định danh người sẽ nhận trả lời

        $mail->Subject    = $subject; //Tiêu đề Mail

        $mail->AltBody    = "Để xem tin này, vui lòng bật chế độ hiển thị mã HTML!"; // optional, comment out and test

        // $mail->AddAttachment($image);
        if($image != '')
            $mail->AddEmbeddedImage($image, 'embed_img');

        $mail->MsgHTML($body);

        if (is_array($to)) {
            foreach ($to as $key => $value) {
                $mail->AddAddress($value, ''); //Gửi tới ai ?
            }
        } else {
            $mail->AddAddress($to, ''); //Gửi tới ai ?
        }


        if(!$mail->Send() ) {
            echo date('Y-m-d H:i:s')." - Failed\n";
        } else {
            echo date('Y-m-d H:i:s')." - Done\n"; //DEBUG
        }

    }

    // public function testAction(){
    //     include_once APPLICATION_PATH.'/../library/phpmail/class.phpmailer.php';
    //     $app_config   = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini');
    //     $config       = $app_config->mail->smtp;
    //     $mailfrom     = $config->user2;
    //     $mailfromname = $config->fromname2;

    //     $mail = new PHPMailer();

    //     $to = array(
    //         'buu.pham@oppomobile.vn',
    //         'tuan.huynh@oppomobile.vn',
    //         'thong.duong@oppomobile.vn',
    //         'hac.tran@oppomobile.vn',
    //         'duoc.vo@oppomobile.vn',
    //         'huycuong.nguyen@oppomobile.vn',
    //         'hau.khuu@oppomobile.vn',
    //         'thanhtruc.nguyen@oppomobile.vn',
    //     );
    //     $subject = 'Vậy hôi đi nha #' . date('Y-m-d/H:i:s');
    //     $maildata = 'bean mother the mail server; // ' . date('Y-m-d H:i:s');

    //     $image = '';

    //     $body = $maildata; // nội dung email
    //     $body = eregi_replace("[\]",'',$body);

    //     $mail->IsHTML();

    //     $mail->IsSMTP();
    //     $mail->SMTPDebug  = 1;                     // enables SMTP debug information (for testing)
    //                                                // 1 = errors and messages
    //                                                // 2 = messages only
    //     $mail->SMTPAuth   = true;                  // enable SMTP authentication
    //     $mail->SMTPSecure = $config->ssl;                 // sets the prefix to the servier // Config this
    //     $mail->Host       = $config->host;      // sets GMAIL as the SMTP server // Config this
    //     $mail->Port       = $config->port;                   // set the SMTP port for the GMAIL server // Config this
    //     $mail->Username   = $config->user2;  // GMAIL username // Config this
    //     $mail->Password   = $config->pass2;            // GMAIL password // Config this

    //     $mail->From = $mailfrom;
    //     $mail->FromName = $mailfromname;

    //     $mail->SetFrom($mailfrom, $mailfromname); //Định danh người gửi

    //     //$mail->AddReplyTo($mailfrom, $mailfromname); //Định danh người sẽ nhận trả lời

    //     $mail->Subject    = $subject; //Tiêu đề Mail

    //     $mail->AltBody    = "Để xem tin này, vui lòng bật chế độ hiển thị mã HTML!"; // optional, comment out and test

    //     // $mail->AddAttachment($image);
    //     if($image != '')
    //         $mail->AddEmbeddedImage($image, 'embed_img');

    //     $mail->MsgHTML($body);

    //     if (is_array($to)) {
    //         foreach ($to as $key => $value) {
    //             $mail->AddAddress($value, ''); //Gửi tới ai ?
    //         }
    //     } else {
    //         $mail->AddAddress($to, ''); //Gửi tới ai ?
    //     }


    //     if(!$mail->Send() ) {
    //         echo date('Y-m-d H:i:s')." - Failed\n";
    //     } else {
    //         echo date('Y-m-d H:i:s')." - Done\n"; //DEBUG
    //     }

    // }

    private function mb_ucwords($str) {
        $str = mb_convert_case($str, MB_CASE_TITLE, "UTF-8");
        return ($str);
    }

    private function data_uri($file, $mime) {
        return "data:$mime;base64," . base64_encode(file_get_contents($file));
    }

    private function array_in_array($needle, $haystack) {
        foreach ($haystack as $key => $value) {
            if (0 == count( array_diff($needle, $value['group']) ) && 0 == count( array_diff($value['group'], $needle) )) {
                return true;
            }
        }

        return false;
    }
}

