<?php
class IndexController extends My_Application_Controller_Cli
{

    public function indexAction(){
        ignore_user_abort();
        set_time_limit(0);
        ini_set('memory_limit', '-1');
        error_reporting(0);
        ini_set('display_error', 0);

        $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini');

        $config = $config->toArray();

        $con=mysqli_connect($config['resources']['db']['params']['host'],$config['resources']['db']['params']['username'],$config['resources']['db']['params']['password'],$config['resources']['db']['params']['dbname']);

        // Check connection
        if (mysqli_connect_errno())
        {
            echo "Failed to connect to MySQL: " . mysqli_connect_error();
        }

        $sql = 'truncate wh_imei;';
        mysqli_query($con,$sql);

        $sql = ' 	replace into '.$config['resources']['db']['params']['dbname'].'.wh_imei
						select * from warehouse_new.`imei`
		';

        mysqli_query($con,$sql);

        mysqli_close($con);

        exit;
    }

    public function imeiactivationAction(){
        ignore_user_abort();
        set_time_limit(0);
        ini_set('memory_limit', '-1');
        error_reporting(0);
        ini_set('display_error', 0);

        $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini');

        $config = $config->toArray();

        $con=mysqli_connect($config['resources']['db']['params']['host'],$config['resources']['db']['params']['username'],$config['resources']['db']['params']['password'],$config['resources']['db']['params']['dbname']);

        // Check connection
        if (mysqli_connect_errno())
        {
            echo "Failed to connect to MySQL: " . mysqli_connect_error();
        }

        $sql = 'truncate wh_imei_activation;';
        mysqli_query($con,$sql);

        $sql = ' 	replace into '.$config['resources']['db']['params']['dbname'].'.wh_imei_activation
						select * from warehouse_new.`imei_activation`
		';

        mysqli_query($con,$sql);

        mysqli_close($con);

        exit;
    }

    public function marketAction(){
        ignore_user_abort();
        set_time_limit(0);
        ini_set('memory_limit', '-1');
        error_reporting(0);
        ini_set('display_error', 0);

        $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini');

        $config = $config->toArray();

        $con=mysqli_connect($config['resources']['db']['params']['host'],$config['resources']['db']['params']['username'],$config['resources']['db']['params']['password'],$config['resources']['db']['params']['dbname']);

        // Check connection
        if (mysqli_connect_errno())
        {
            echo "Failed to connect to MySQL: " . mysqli_connect_error();
        }

        $sql = 'truncate wh_market;';
        mysqli_query($con,$sql);

        $sql = ' 	replace into '.$config['resources']['db']['params']['dbname'].'.wh_market
						select * from warehouse_new.`market`
		';

        mysqli_query($con,$sql);

        mysqli_close($con);

        exit;
    }

    //get imei
    public function index2Action ()
    {
        ignore_user_abort();
        set_time_limit(0);
        ini_set('memory_limit', '-1');
        error_reporting(0);
        ini_set('display_error', 0);

        require_once 'My'.DIRECTORY_SEPARATOR.'nusoap'.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'nusoap.php';


        $client = new soapclient(WSS_MK_URI);

        // Check for an error
        $err = $client->getError();
        if ($err) {
            // Display the error
            echo '<p><b>Constructor error: ' . $err . '</b></p>';
            // At this point, you know the call that follows will fail
        }

        // Call the SOAP method
        $result = $client->call(
            'fetch_imei'
        );

        // Check for a fault
        if ($client->fault) {
            echo '<p><b>Fault: ';
            print_r($result);
            echo '</b></p>';
        } else {
            // Check for errors
            $err = $client->getError();
            if ($err) {
                // Display the error
                echo '<p><b>Error: ' . $err . '</b></p>';
            } else {

                if ($result){

                    //decode
                    $imeis = json_decode($result, true);


                    if ($imeis) {

                        $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini');

                        $config = $config->toArray();

                        $con=mysqli_connect($config['resources']['db']['params']['host'],$config['resources']['db']['params']['username'],$config['resources']['db']['params']['password'],$config['resources']['db']['params']['dbname']);

                        // Check connection
                        if (mysqli_connect_errno())
                        {
                            echo "Failed to connect to MySQL: " . mysqli_connect_error();
                        }

                        $sql = ' replace into '.$config['resources']['db']['params']['dbname'].'.wh_imei ( `id`, `imei_sn`, `good_id`, `good_color`, `po_sn`, `distributor_id`, `into_date`, `out_date`, `sales_sn`, `sales_id`, `activated_date`, `out_price`, `price_date`, `out_user`, `return_sn`, `changed_sn`, `warehouse_id`, `status`, `shape`) VALUES ';

                        $count = count($imeis);

                        for ($i=0; $i<$count; $i++){
                            $sql .= ($i>0 ? ', ' : '') . '(' .
                                '"'. $imeis[$i]['id'] . '", '.
                                ( $imeis[$i]['imei_sn']===NULL ? 'NULL' : ( '\'' . mysqli_real_escape_string( $con, $imeis[$i]['imei_sn'] )  . '\'' ) ) .', '.
                                ( $imeis[$i]['good_id']===NULL ? 'NULL' : ( '\'' . mysqli_real_escape_string( $con, $imeis[$i]['good_id'] )  . '\'' ) ) .', '.
                                ( $imeis[$i]['good_color']===NULL ? 'NULL' : ( '\'' . mysqli_real_escape_string( $con, $imeis[$i]['good_color'] )  . '\'' ) ) .', '.
                                ( $imeis[$i]['po_sn']===NULL ? 'NULL' : ( '\'' . mysqli_real_escape_string( $con, $imeis[$i]['po_sn'] )  . '\'' ) ) .', '.
                                ( $imeis[$i]['distributor_id']===NULL ? 'NULL' : ( '\'' . mysqli_real_escape_string( $con, $imeis[$i]['distributor_id'] )  . '\'' ) ) .', '.
                                ( $imeis[$i]['into_date']===NULL ? 'NULL' : ( '\'' . mysqli_real_escape_string( $con, $imeis[$i]['into_date'] )  . '\'' ) ) .', '.
                                ( $imeis[$i]['out_date']===NULL ? 'NULL' : ( '\'' . mysqli_real_escape_string( $con, $imeis[$i]['out_date'] )  . '\'' ) ) .', '.
                                ( $imeis[$i]['sales_sn']===NULL ? 'NULL' : ( '\'' . mysqli_real_escape_string( $con, $imeis[$i]['sales_sn'] )  . '\'' ) ) .', '.
                                ( $imeis[$i]['sales_id']===NULL ? 'NULL' : ( '\'' . mysqli_real_escape_string( $con, $imeis[$i]['sales_id'] )  . '\'' ) ) .', '.
                                ( $imeis[$i]['activated_date']===NULL ? 'NULL' : ( '\'' . mysqli_real_escape_string( $con, $imeis[$i]['activated_date'] )  . '\'' ) ) .', '.
                                ( $imeis[$i]['out_price']===NULL ? 'NULL' : ( '\'' . mysqli_real_escape_string( $con, $imeis[$i]['out_price'] )  . '\'' ) ) .', '.
                                ( $imeis[$i]['price_date']===NULL ? 'NULL' : ( '\'' . mysqli_real_escape_string( $con, $imeis[$i]['price_date'] )  . '\'' ) ) .', '.
                                ( $imeis[$i]['out_user']===NULL ? 'NULL' : ( '\'' . mysqli_real_escape_string( $con, $imeis[$i]['out_user'] )  . '\'' ) ) .', '.
                                ( $imeis[$i]['return_sn']===NULL ? 'NULL' : ( '\'' . mysqli_real_escape_string( $con, $imeis[$i]['return_sn'] )  . '\'' ) ) .', '.
                                ( $imeis[$i]['changed_sn']===NULL ? 'NULL' : ( '\'' . mysqli_real_escape_string( $con, $imeis[$i]['changed_sn'] )  . '\'' ) ) .', '.
                                ( $imeis[$i]['warehouse_id']===NULL ? 'NULL' : ( '\'' . mysqli_real_escape_string( $con, $imeis[$i]['warehouse_id'] )  . '\'' ) ) .', '.
                                ( $imeis[$i]['status']===NULL ? 'NULL' : ( '\'' . mysqli_real_escape_string( $con, $imeis[$i]['status'] )  . '\'' ) ) .', '.
                                ( $imeis[$i]['shape']===NULL ? 'NULL' : ( '\'' . mysqli_real_escape_string( $con, $imeis[$i]['shape'] )  . '\'' ) )

                                . ') ';
                        }

                        $sql .= ' ; ';

                        mysqli_query($con,$sql);

                        mysqli_close($con);

                        $content = date('Y-m-d H:i:s') . ' imported imei ';

                        echo 'imported imei: done' . "\n";

                        error_log($content . "\n", 3, APPLICATION_PATH.'/../bin/access.log');
                    }

                }
            }
        }



        exit;
    }

    //import imei activation
    public function imeiactivation2Action ()
    {
        ignore_user_abort();
        set_time_limit(0);
        ini_set('memory_limit', '-1');
        error_reporting(0);
        ini_set('display_error', 0);

        require_once 'My'.DIRECTORY_SEPARATOR.'nusoap'.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'nusoap.php';


        $client = new soapclient(WSS_MK_URI);

        // Check for an error
        $err = $client->getError();
        if ($err) {
            // Display the error
            echo '<p><b>Constructor error: ' . $err . '</b></p>';
            // At this point, you know the call that follows will fail
        }

        // Call the SOAP method
        $result = $client->call(
            'fetch_imeiactivation'
        );

        // Check for a fault
        if ($client->fault) {
            echo '<p><b>Fault: ';
            print_r($result);
            echo '</b></p>';
        } else {
            // Check for errors
            $err = $client->getError();
            if ($err) {
                // Display the error
                echo '<p><b>Error: ' . $err . '</b></p>';
            } else {

                if ($result){

                    //decode
                    $imeis = json_decode($result, true);


                    if ($imeis) {

                        $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini');

                        $config = $config->toArray();

                        $con=mysqli_connect($config['resources']['db']['params']['host'],$config['resources']['db']['params']['username'],$config['resources']['db']['params']['password'],$config['resources']['db']['params']['dbname']);

                        // Check connection
                        if (mysqli_connect_errno())
                        {
                            echo "Failed to connect to MySQL: " . mysqli_connect_error();
                        }

                        $sql = ' replace into '.$config['resources']['db']['params']['dbname'].'.wh_imei_activation ( `imei_sn`, `activated_at`, `status`) VALUES ';

                        $count = count($imeis);

                        for ($i=0; $i<$count; $i++){
                            $sql .= ($i>0 ? ', ' : '') . '(' .
                                ( $imeis[$i]['imei_sn']===NULL ? 'NULL' : ( '\'' . mysqli_real_escape_string( $con, $imeis[$i]['imei_sn'] )  . '\'' ) ) .', '.
                                ( $imeis[$i]['activated_at']===NULL ? 'NULL' : ( '\'' . mysqli_real_escape_string( $con, $imeis[$i]['activated_at'] )  . '\'' ) ) .', '.
                                ( $imeis[$i]['status']===NULL ? 'NULL' : ( '\'' . mysqli_real_escape_string( $con, $imeis[$i]['status'] )  . '\'' ) )

                                . ') ';
                        }

                        $sql .= ' ; ';

                        mysqli_query($con,$sql);

                        mysqli_close($con);

                        $content = date('Y-m-d H:i:s') . ' imported imei activation ';

                        echo 'imported imei activation: done' . "\n";

                        error_log($content . "\n", 3, APPLICATION_PATH.'/../bin/access.log');
                    }

                }
            }
        }



        exit;
    }

    //import market order
    public function market2Action ()
    {
        ignore_user_abort();
        set_time_limit(0);
        ini_set('memory_limit', '-1');
        error_reporting(0);
        ini_set('display_error', 0);

        require_once 'My'.DIRECTORY_SEPARATOR.'nusoap'.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'nusoap.php';


        $client = new soapclient(WSS_MK_URI);

        // Check for an error
        $err = $client->getError();
        if ($err) {
            // Display the error
            echo '<p><b>Constructor error: ' . $err . '</b></p>';
            // At this point, you know the call that follows will fail
        }

        // Call the SOAP method
        $result = $client->call(
            'fetch_market'
        );

        // Check for a fault
        if ($client->fault) {
            echo '<p><b>Fault: ';
            print_r($result);
            echo '</b></p>';
        } else {
            // Check for errors
            $err = $client->getError();
            if ($err) {
                // Display the error
                echo '<p><b>Error: ' . $err . '</b></p>';
            } else {

                if ($result){

                    //decode
                    $imeis = json_decode($result, true);


                    if ($imeis) {


                        $count = count($imeis);

                        $QMarket = new Application_Model_Market();

                        for ($i=0; $i<$count; $i++){

                            try {
                                $QMarket->insert($imeis[$i]);
                            } catch (Exception $e){
                                $where = $QMarket->getAdapter()->quoteInto('id = ?', $imeis[$i]['id']);
                                $QMarket->update($imeis[$i], $where);
                            }

                        }


                        $content = date('Y-m-d H:i:s') . ' imported market ';

                        echo 'imported market: done' . "\n";

                        error_log($content . "\n", 3, APPLICATION_PATH.'/../bin/access.log');
                    }

                }
            }
        }



        exit;
    }

    //import Good
    public function migrateGoodAction ()
    {
        ignore_user_abort();
        set_time_limit(0);
        ini_set('memory_limit', '-1');
        error_reporting(0);
        ini_set('display_error', 0);

        require_once 'My'.DIRECTORY_SEPARATOR.'nusoap'.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'nusoap.php';


        $client = new soapclient(WSS_MK_URI);

        // Check for an error
        $err = $client->getError();
        if ($err) {
            // Display the error
            echo '<p><b>Constructor error: ' . $err . '</b></p>';
            // At this point, you know the call that follows will fail
        }

        // Call the SOAP method
        $result = $client->call(
            'fetch_good'
        );

        // Check for a fault
        if ($client->fault) {
            echo '<p><b>Fault: ';
            print_r($result);
            echo '</b></p>';
        } else {
            // Check for errors
            $err = $client->getError();
            if ($err) {
                // Display the error
                echo '<p><b>Error: ' . $err . '</b></p>';
            } else {

                if ($result){

                    //decode
                    $imeis = json_decode($result, true);


                    if ($imeis) {

                        $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini');

                        $config = $config->toArray();

                        $con=mysqli_connect($config['resources']['db']['params']['host'],$config['resources']['db']['params']['username'],$config['resources']['db']['params']['password'],$config['resources']['db']['params']['dbname']);

                        // Check connection
                        if (mysqli_connect_errno())
                        {
                            echo "Failed to connect to MySQL: " . mysqli_connect_error();
                        }

                        $sql = ' replace into '.$config['resources']['db']['params']['dbname'].'.wh_good (
							`id`, 
							`cat_id`, 
							`name`, 
							`color`, 
							`brand_id`, 
							`price_4`, 
							`price_1`, 
							`price_2`, 
							`price_3`, 
							`price_5`, 
							`price_6`,
							`desc`,
							`add_time`, 
							`del`
							) VALUES ';

                        $count = count($imeis);

                        for ($i=0; $i<$count; $i++){
                            $sql .= ($i>0 ? ', ' : '') . '(' .
                                ( $imeis[$i]['id']===NULL ? 'NULL' : ( '\'' . mysqli_real_escape_string( $con, $imeis[$i]['id'] )  . '\'' ) ) .', '.
                                ( $imeis[$i]['cat_id']===NULL ? 'NULL' : ( '\'' . mysqli_real_escape_string( $con, $imeis[$i]['cat_id'] )  . '\'' ) ) .', '.
                                ( $imeis[$i]['name']===NULL ? 'NULL' : ( '\'' . mysqli_real_escape_string( $con, $imeis[$i]['name'] )  . '\'' ) ) .', '.
                                ( $imeis[$i]['color']===NULL ? 'NULL' : ( '\'' . mysqli_real_escape_string( $con, $imeis[$i]['color'] )  . '\'' ) ) .', '.
                                ( $imeis[$i]['brand_id']===NULL ? 'NULL' : ( '\'' . mysqli_real_escape_string( $con, $imeis[$i]['brand_id'] )  . '\'' ) ) .', '.
                                ( $imeis[$i]['price_4']===NULL ? 'NULL' : ( '\'' . mysqli_real_escape_string( $con, $imeis[$i]['price_4'] )  . '\'' ) ) .', '.
                                ( $imeis[$i]['price_1']===NULL ? 'NULL' : ( '\'' . mysqli_real_escape_string( $con, $imeis[$i]['price_1'] )  . '\'' ) ) .', '.
                                ( $imeis[$i]['price_2']===NULL ? 'NULL' : ( '\'' . mysqli_real_escape_string( $con, $imeis[$i]['price_2'] )  . '\'' ) ) .', '.
                                ( $imeis[$i]['price_3']===NULL ? 'NULL' : ( '\'' . mysqli_real_escape_string( $con, $imeis[$i]['price_3'] )  . '\'' ) ) .', '.
                                ( $imeis[$i]['price_5']===NULL ? 'NULL' : ( '\'' . mysqli_real_escape_string( $con, $imeis[$i]['price_5'] )  . '\'' ) ) .', '.
                                ( $imeis[$i]['price_6']===NULL ? 'NULL' : ( '\'' . mysqli_real_escape_string( $con, $imeis[$i]['price_5'] )  . '\'' ) ) .', '.
                                ( $imeis[$i]['desc']===NULL ? 'NULL' : ( '\'' . mysqli_real_escape_string( $con, $imeis[$i]['desc'] )  . '\'' ) ) .', '.
                                ( $imeis[$i]['add_time']===NULL ? 'NULL' : ( '\'' . mysqli_real_escape_string( $con, $imeis[$i]['add_time'] )  . '\'' ) ) .', '.
                                ( $imeis[$i]['del']===NULL ? 'NULL' : ( '\'' . mysqli_real_escape_string( $con, $imeis[$i]['del'] )  . '\'' ) )
                                . ') ';
                        }

                        $sql .= ' ; ';

                        mysqli_query($con,$sql);

                        mysqli_close($con);

                        $content = date('Y-m-d H:i:s') . ' imported good ';

                        echo 'imported good: done' . "\n";

                        error_log($content . "\n", 3, APPLICATION_PATH.'/../bin/access.log');
                    }

                }
            }
        }

        // Call the SOAP method
        $result = $client->call(
            'fetch_good_color'
        );

        // Check for a fault
        if ($client->fault) {
            echo '<p><b>Fault: ';
            print_r($result);
            echo '</b></p>';
        } else {
            // Check for errors
            $err = $client->getError();
            if ($err) {
                // Display the error
                echo '<p><b>Error: ' . $err . '</b></p>';
            } else {

                if ($result){

                    //decode
                    $imeis = json_decode($result, true);


                    if ($imeis) {

                        $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini');

                        $config = $config->toArray();

                        $con=mysqli_connect($config['resources']['db']['params']['host'],$config['resources']['db']['params']['username'],$config['resources']['db']['params']['password'],$config['resources']['db']['params']['dbname']);

                        // Check connection
                        if (mysqli_connect_errno())
                        {
                            echo "Failed to connect to MySQL: " . mysqli_connect_error();
                        }

                        $sql = ' replace into '.$config['resources']['db']['params']['dbname'].'.wh_good_color (
							`id`, 
							`name`, 
							`short_name`
							) VALUES ';

                        $count = count($imeis);

                        for ($i=0; $i<$count; $i++){
                            $sql .= ($i>0 ? ', ' : '') . '(' .
                                ( $imeis[$i]['id']===NULL ? 'NULL' : ( '\'' . mysqli_real_escape_string( $con, $imeis[$i]['id'] )  . '\'' ) ) .', '.
                                ( $imeis[$i]['name']===NULL ? 'NULL' : ( '\'' . mysqli_real_escape_string( $con, $imeis[$i]['name'] )  . '\'' ) ) .', '.
                                ( $imeis[$i]['short_name']===NULL ? 'NULL' : ( '\'' . mysqli_real_escape_string( $con, $imeis[$i]['short_name'] )  . '\'' ) )
                                . ') ';
                        }

                        $sql .= ' ; ';

                        mysqli_query($con,$sql);

                        mysqli_close($con);

                        $content = date('Y-m-d H:i:s') . ' imported good_color ';

                        echo 'imported good_color: done' . "\n";

                        error_log($content . "\n", 3, APPLICATION_PATH.'/../bin/access.log');
                    }

                }
            }
        }

        // Call the SOAP method
        $result = $client->call(
            'fetch_good_category'
        );

        // Check for a fault
        if ($client->fault) {
            echo '<p><b>Fault: ';
            print_r($result);
            echo '</b></p>';
        } else {
            // Check for errors
            $err = $client->getError();
            if ($err) {
                // Display the error
                echo '<p><b>Error: ' . $err . '</b></p>';
            } else {

                if ($result){

                    //decode
                    $imeis = json_decode($result, true);


                    if ($imeis) {

                        $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini');

                        $config = $config->toArray();

                        $con=mysqli_connect($config['resources']['db']['params']['host'],$config['resources']['db']['params']['username'],$config['resources']['db']['params']['password'],$config['resources']['db']['params']['dbname']);

                        // Check connection
                        if (mysqli_connect_errno())
                        {
                            echo "Failed to connect to MySQL: " . mysqli_connect_error();
                        }

                        $sql = ' replace into '.$config['resources']['db']['params']['dbname'].'.wh_good_category (
							`id`, 
							`name`
							) VALUES ';

                        $count = count($imeis);

                        for ($i=0; $i<$count; $i++){
                            $sql .= ($i>0 ? ', ' : '') . '(' .
                                ( $imeis[$i]['id']===NULL ? 'NULL' : ( '\'' . mysqli_real_escape_string( $con, $imeis[$i]['id'] )  . '\'' ) ) .', '.
                                ( $imeis[$i]['name']===NULL ? 'NULL' : ( '\'' . mysqli_real_escape_string( $con, $imeis[$i]['name'] )  . '\'' ) )
                                . ') ';
                        }

                        $sql .= ' ; ';

                        mysqli_query($con,$sql);

                        mysqli_close($con);

                        $content = date('Y-m-d H:i:s') . ' imported good_category ';

                        echo 'imported good_category: done' . "\n";

                        error_log($content . "\n", 3, APPLICATION_PATH.'/../bin/access.log');
                    }

                }
            }
        }

        // Call the SOAP method
        $result = $client->call(
            'fetch_good_color_combined'
        );

        // Check for a fault
        if ($client->fault) {
            echo '<p><b>Fault: ';
            print_r($result);
            echo '</b></p>';
        } else {
            // Check for errors
            $err = $client->getError();
            if ($err) {
                // Display the error
                echo '<p><b>Error: ' . $err . '</b></p>';
            } else {

                if ($result){

                    //decode
                    $imeis = json_decode($result, true);


                    if ($imeis) {

                        $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini');

                        $config = $config->toArray();

                        $con=mysqli_connect($config['resources']['db']['params']['host'],$config['resources']['db']['params']['username'],$config['resources']['db']['params']['password'],$config['resources']['db']['params']['dbname']);

                        // Check connection
                        if (mysqli_connect_errno())
                        {
                            echo "Failed to connect to MySQL: " . mysqli_connect_error();
                        }

                        $sql = ' replace into '.$config['resources']['db']['params']['dbname'].'.wh_good_color_combined (
							`id`, 
							`good_id`,
							`good_color_id`
							) VALUES ';

                        $count = count($imeis);

                        for ($i=0; $i<$count; $i++){
                            $sql .= ($i>0 ? ', ' : '') . '(' .
                                ( $imeis[$i]['id']===NULL ? 'NULL' : ( '\'' . mysqli_real_escape_string( $con, $imeis[$i]['id'] )  . '\'' ) ) .', '.
                                ( $imeis[$i]['good_id']===NULL ? 'NULL' : ( '\'' . mysqli_real_escape_string( $con, $imeis[$i]['good_id'] )  . '\'' ) ) .', '.
                                ( $imeis[$i]['good_color_id']===NULL ? 'NULL' : ( '\'' . mysqli_real_escape_string( $con, $imeis[$i]['good_color_id'] )  . '\'' ) )
                                . ') ';
                        }

                        $sql .= ' ; ';

                        mysqli_query($con,$sql);

                        mysqli_close($con);

                        $content = date('Y-m-d H:i:s') . ' imported good_color_combined ';

                        echo 'imported good_color_combined: done' . "\n";

                        error_log($content . "\n", 3, APPLICATION_PATH.'/../bin/access.log');
                    }

                }
            }
        }

        //clear cache
        $cache_folder = APPLICATION_PATH.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR;

        try {
            foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($cache_folder, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST) as $path) {
                if ($path->getFilename() == 'zend_cache---server_notifis_cache'
                    || $path->getFilename() == 'zend_cache---internal-metadatas---server_notifis_cache')
                    continue;

                $path->isFile() ? @unlink($path->getPathname()) : @rmdir($path->getPathname());
            }
        } catch (Exception $e){}

        echo 'clear cached: done' . "\n";

        exit;
    }

    //import distributor
    public function distributorAction ()
    {
        ignore_user_abort();
        set_time_limit(0);
        ini_set('memory_limit', '-1');
        //error_reporting(0);
        //ini_set('display_error', 0);

        require_once 'My'.DIRECTORY_SEPARATOR.'nusoap'.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'nusoap.php';


        $client = new soapclient(WSS_MK_URI);

        // Check for an error
        $err = $client->getError();
        if ($err) {
            // Display the error
            echo '<p><b>Constructor error: ' . $err . '</b></p>';
            // At this point, you know the call that follows will fail
        }

        // Call the SOAP method
        $result = $client->call(
            'fetch_distributor'
        );

        // Check for a fault
        if ($client->fault) {
            echo '<p><b>Fault: ';
            print_r($result);
            echo '</b></p>';
        } else {
            // Check for errors
            $err = $client->getError();
            if ($err) {
                // Display the error
                echo '<p><b>Error: ' . $err . '</b></p>';
            } else {

                if ($result){

                    //decode
                    $imeis = json_decode($result, true);


                    if ($imeis) {

                        $count = count($imeis);

                        $QDistributor = new Application_Model_Distributor();

                        for ($i=0; $i<$count; $i++){

                            try {
                                $QDistributor->insert($imeis[$i]);
                            } catch (Exception $e){
                                $where = $QDistributor->getAdapter()->quoteInto('id = ?', $imeis[$i]['id']);
                                $QDistributor->update($imeis[$i], $where);
                            }

                        }


                        $content = date('Y-m-d H:i:s') . ' imported distributor';

                        echo 'imported distributor: done' . "\n";

                        error_log($content . "\n", 3, APPLICATION_PATH.'/../bin/access.log');
                    }

                }
            }
        }

        //clear cache
        $cache_folder = APPLICATION_PATH.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR;

        try {
            foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($cache_folder, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST) as $path) {
                if ($path->getFilename() == 'zend_cache---server_notifis_cache'
                    || $path->getFilename() == 'zend_cache---internal-metadatas---server_notifis_cache')
                    continue;

                $path->isFile() ? @unlink($path->getPathname()) : @rmdir($path->getPathname());
            }
        } catch (Exception $e){}

        echo 'clear cached: done' . "\n";

        exit;
    }

    //import distributor
    public function areaAction ()
    {
        ignore_user_abort();
        set_time_limit(0);
        ini_set('memory_limit', '-1');
        //error_reporting(0);
        //ini_set('display_error', 0);

        require_once 'My'.DIRECTORY_SEPARATOR.'nusoap'.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'nusoap.php';


        $client = new soapclient(WSS_MK_URI);

        // Check for an error
        $err = $client->getError();
        if ($err) {
            // Display the error
            echo '<p><b>Constructor error: ' . $err . '</b></p>';
            // At this point, you know the call that follows will fail
        }

        // Call the SOAP method
        $result = $client->call(
            'fetch_area'
        );

        // Check for a fault
        if ($client->fault) {
            echo '<p><b>Fault: ';
            print_r($result);
            echo '</b></p>';
        } else {
            // Check for errors
            $err = $client->getError();
            if ($err) {
                // Display the error
                echo '<p><b>Error: ' . $err . '</b></p>';
            } else {

                if ($result){

                    //decode
                    $imeis = json_decode($result, true);


                    if ($imeis) {

                        $count = count($imeis);

                        $QArea = new Application_Model_WhArea();

                        for ($i=0; $i<$count; $i++){

                            try {
                                $QArea->insert($imeis[$i]);
                            } catch (Exception $e){
                                $where = $QArea->getAdapter()->quoteInto('id = ?', $imeis[$i]['id']);
                                $QArea->update($imeis[$i], $where);
                            }

                        }


                        $content = date('Y-m-d H:i:s') . ' imported areas';

                        echo 'imported areas: done' . "\n";

                        error_log($content . "\n", 3, APPLICATION_PATH.'/../bin/access.log');
                    }

                }
            }
        }

        //clear cache
        $cache_folder = APPLICATION_PATH.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR;

        try {
            foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($cache_folder, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST) as $path) {
                if ($path->getFilename() == 'zend_cache---server_notifis_cache'
                    || $path->getFilename() == 'zend_cache---internal-metadatas---server_notifis_cache')
                    continue;

                $path->isFile() ? @unlink($path->getPathname()) : @rmdir($path->getPathname());
            }
        } catch (Exception $e){}

        echo 'clear cached: done' . "\n";

        exit;
    }

    //import distributor
    public function regionAction ()
    {
        ignore_user_abort();
        set_time_limit(0);
        ini_set('memory_limit', '-1');
        //error_reporting(0);
        //ini_set('display_error', 0);

        require_once 'My'.DIRECTORY_SEPARATOR.'nusoap'.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'nusoap.php';


        $client = new soapclient(WSS_MK_URI);

        // Check for an error
        $err = $client->getError();
        if ($err) {
            // Display the error
            echo '<p><b>Constructor error: ' . $err . '</b></p>';
            // At this point, you know the call that follows will fail
        }

        // Call the SOAP method
        $result = $client->call(
            'fetch_region'
        );

        // Check for a fault
        if ($client->fault) {
            echo '<p><b>Fault: ';
            print_r($result);
            echo '</b></p>';
        } else {
            // Check for errors
            $err = $client->getError();
            if ($err) {
                // Display the error
                echo '<p><b>Error: ' . $err . '</b></p>';
            } else {

                if ($result){

                    //decode
                    $imeis = json_decode($result, true);


                    if ($imeis) {

                        $count = count($imeis);

                        $QRegion = new Application_Model_WhRegion();

                        for ($i=0; $i<$count; $i++){

                            try {
                                $QRegion->insert($imeis[$i]);
                            } catch (Exception $e){
                                $where = $QRegion->getAdapter()->quoteInto('id = ?', $imeis[$i]['id']);
                                $QRegion->update($imeis[$i], $where);
                            }

                        }


                        $content = date('Y-m-d H:i:s') . ' imported regions';

                        echo 'imported regions: done' . "\n";

                        error_log($content . "\n", 3, APPLICATION_PATH.'/../bin/access.log');
                    }

                }
            }
        }

        //clear cache
        $cache_folder = APPLICATION_PATH.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR;

        try {
            foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($cache_folder, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST) as $path) {
                if ($path->getFilename() == 'zend_cache---server_notifis_cache'
                    || $path->getFilename() == 'zend_cache---internal-metadatas---server_notifis_cache')
                    continue;

                $path->isFile() ? @unlink($path->getPathname()) : @rmdir($path->getPathname());
            }
        } catch (Exception $e){}

        echo 'clear cached: done' . "\n";

        exit;
    }

    //import distributor
    public function priceLogAction ()
    {
        ignore_user_abort();
        set_time_limit(0);
        ini_set('memory_limit', '-1');
        //error_reporting(0);
        //ini_set('display_error', 0);

        require_once 'My'.DIRECTORY_SEPARATOR.'nusoap'.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'nusoap.php';


        $client = new soapclient(WSS_MK_URI);

        // Check for an error
        $err = $client->getError();
        if ($err) {
            // Display the error
            echo '<p><b>Constructor error: ' . $err . '</b></p>';
            // At this point, you know the call that follows will fail
        }

        // Call the SOAP method
        $result = $client->call(
            'fetch_price_log'
        );

        // Check for a fault
        if ($client->fault) {
            echo '<p><b>Fault: ';
            print_r($result);
            echo '</b></p>';
        } else {
            // Check for errors
            $err = $client->getError();
            if ($err) {
                // Display the error
                echo '<p><b>Error: ' . $err . '</b></p>';
            } else {

                if ($result){

                    //decode
                    $imeis = json_decode($result, true);


                    if ($imeis) {

                        $count = count($imeis);

                        $QPrice = new Application_Model_GoodPriceLog();

                        for ($i=0; $i<$count; $i++){

                            try {
                                $QPrice->insert($imeis[$i]);
                            } catch (Exception $e){
                                $where = $QPrice->getAdapter()->quoteInto('id = ?', $imeis[$i]['id']);
                                $QPrice->update($imeis[$i], $where);
                            }

                        }


                        $content = date('Y-m-d H:i:s') . ' imported price log';

                        echo 'imported price log: done' . "\n";

                        error_log($content . "\n", 3, APPLICATION_PATH.'/../bin/access.log');
                    }

                }
            }
        }

        //clear cache
        $cache_folder = APPLICATION_PATH.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR;

        try {
            foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($cache_folder, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST) as $path) {
                if ($path->getFilename() == 'zend_cache---server_notifis_cache'
                    || $path->getFilename() == 'zend_cache---internal-metadatas---server_notifis_cache')
                    continue;

                $path->isFile() ? @unlink($path->getPathname()) : @rmdir($path->getPathname());
            }
        } catch (Exception $e){}

        echo 'clear cached: done' . "\n";

        exit;
    }

    public function downloadImeiAction(){


        set_include_path(
            realpath(APPLICATION_PATH . '/../library/phpseclib0.3.6')
        );

        include('Net/SFTP.php');

        $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini');

        $config = $config->toArray();

        $server		=	$config['sftp']['server'];
        $user_name	=	$config['sftp']['user_name'];
        $user_pass	=	$config['sftp']['user_pass'];

        $sftp = new Net_SFTP($server);
        if (!$sftp->login($user_name, $user_pass)) {
            exit('Login Failed');
        }

        // define some variables
        $local_file = '/var/www/html/imei.sql';
        $server_file = '/var/bk/imei.sql';

        // copies filename.remote to filename.local from the SFTP server
        $sftp->get($server_file, $local_file);

        //truncate table

        $con=mysqli_connect($config['resources']['db']['params']['host'],$config['resources']['db']['params']['username'],$config['resources']['db']['params']['password'],$config['resources']['db']['params']['dbname']);

        // Check connection
        if (mysqli_connect_errno())
        {
            echo "Failed to connect to MySQL: " . mysqli_connect_error();
        }

        $sql = ' TRUNCATE TABLE `imei` ';

        mysqli_query($con,$sql);

        mysqli_close($con);

        //ENTER THE RELEVANT INFO BELOW
        $mysqlDatabaseName =$config['resources']['db']['params']['dbname'];
        $mysqlUserName 	=	$config['resources']['db']['params']['username'];
        $mysqlPassword 	=	$config['resources']['db']['params']['password'];
        $mysqlHostName 	=	$config['resources']['db']['params']['host'];
        $mysqlImportFilename = $local_file;


        //Export the database and output the status to the page
        $command	= 'mysql -h' .$mysqlHostName .' -u' .$mysqlUserName .' -p' .$mysqlPassword .' ' .$mysqlDatabaseName .' < ' .$mysqlImportFilename;
        $output		= array();

        exec($command,$output,$worked);

        switch($worked){
            case 0:
                echo 'Import file ' .$mysqlImportFilename .' successfully imported to database ' .$mysqlDatabaseName . "\n";
                break;
            case 1:
                echo 'There was an error during import.' . "\n";
                break;
        }

        @unlink($local_file);

        exit;

    }

    public function downloadImeiActivationAction(){


        set_include_path(
            realpath(APPLICATION_PATH . '/../library/phpseclib0.3.6')
        );

        include('Net/SFTP.php');

        $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini');

        $config = $config->toArray();

        $server		=	$config['sftp']['server'];
        $user_name	=	$config['sftp']['user_name'];
        $user_pass	=	$config['sftp']['user_pass'];

        $sftp = new Net_SFTP($server);
        if (!$sftp->login($user_name, $user_pass)) {
            exit('Login Failed');
        }

        // define some variables
        $local_file = '/var/www/html/imei_activation.sql';
        $server_file = '/var/bk/imei_activation.sql';

        // copies filename.remote to filename.local from the SFTP server
        $sftp->get($server_file, $local_file);

        //truncate table


        $con=mysqli_connect($config['resources']['db']['params']['host'],$config['resources']['db']['params']['username'],$config['resources']['db']['params']['password'],$config['resources']['db']['params']['dbname']);

        // Check connection
        if (mysqli_connect_errno())
        {
            echo "Failed to connect to MySQL: " . mysqli_connect_error();
        }

        $sql = ' TRUNCATE TABLE `imei_activation` ';

        mysqli_query($con,$sql);

        mysqli_close($con);

        //ENTER THE RELEVANT INFO BELOW
        $mysqlDatabaseName =$config['resources']['db']['params']['dbname'];
        $mysqlUserName 	=	$config['resources']['db']['params']['username'];
        $mysqlPassword 	=	$config['resources']['db']['params']['password'];
        $mysqlHostName 	=	$config['resources']['db']['params']['host'];
        $mysqlImportFilename = $local_file;


        //Export the database and output the status to the page
        $command	= 'mysql -h' .$mysqlHostName .' -u' .$mysqlUserName .' -p' .$mysqlPassword .' ' .$mysqlDatabaseName .' < ' .$mysqlImportFilename;
        $output		= array();

        exec($command,$output,$worked);

        switch($worked){
            case 0:
                echo 'Import file ' .$mysqlImportFilename .' successfully imported to database ' .$mysqlDatabaseName . "\n";
                break;
            case 1:
                echo 'There was an error during import.' . "\n";
                break;
        }

        @unlink($local_file);

        exit;

    }

    public function downloadMarketAction(){


        set_include_path(
            realpath(APPLICATION_PATH . '/../library/phpseclib0.3.6')
        );

        include('Net/SFTP.php');

        $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini');

        $config = $config->toArray();

        $server		=	$config['sftp']['server'];
        $user_name	=	$config['sftp']['user_name'];
        $user_pass	=	$config['sftp']['user_pass'];

        $sftp = new Net_SFTP($server);
        if (!$sftp->login($user_name, $user_pass)) {
            exit('Login Failed');
        }

        // define some variables
        $local_file = '/var/www/html/market.sql';
        $server_file = '/var/bk/market.sql';

        // copies filename.remote to filename.local from the SFTP server
        $sftp->get($server_file, $local_file);

        //truncate table

        $con=mysqli_connect($config['resources']['db']['params']['host'],$config['resources']['db']['params']['username'],$config['resources']['db']['params']['password'],$config['resources']['db']['params']['dbname']);

        // Check connection
        if (mysqli_connect_errno())
        {
            echo "Failed to connect to MySQL: " . mysqli_connect_error();
        }

        $sql = ' TRUNCATE TABLE `market` ';

        mysqli_query($con,$sql);

        mysqli_close($con);

        //ENTER THE RELEVANT INFO BELOW
        $mysqlDatabaseName =$config['resources']['db']['params']['dbname'];
        $mysqlUserName 	=	$config['resources']['db']['params']['username'];
        $mysqlPassword 	=	$config['resources']['db']['params']['password'];
        $mysqlHostName 	=	$config['resources']['db']['params']['host'];
        $mysqlImportFilename = $local_file;


        //Export the database and output the status to the page
        $command	= 'mysql -h' .$mysqlHostName .' -u' .$mysqlUserName .' -p' .$mysqlPassword .' ' .$mysqlDatabaseName .' < ' .$mysqlImportFilename;
        $output		= array();

        exec($command,$output,$worked);

        switch($worked){
            case 0:
                echo 'Import file ' .$mysqlImportFilename .' successfully imported to database ' .$mysqlDatabaseName . "\n";
                break;
            case 1:
                echo 'There was an error during import.' . "\n";
                break;
        }

        @unlink($local_file);

        exit;

    }

    public function storeSoldProductAction(){
        ignore_user_abort();
        set_time_limit(0);
        ini_set('memory_limit', '-1');
        error_reporting(0);
        ini_set('display_error', 0);

        $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini');

        $config = $config->toArray();

        $con=mysqli_connect($config['resources']['db']['params']['host'],$config['resources']['db']['params']['username'],$config['resources']['db']['params']['password'],$config['resources']['db']['params']['dbname']);

        // Check connection
        if (mysqli_connect_errno())
        {
            echo "Failed to connect to MySQL: " . mysqli_connect_error();
        }

        $sql = ' 	    replace into '.$config['resources']['db']['params']['dbname'].'.store_sold_product

						select sto.id store_id, g.id good_id, count(*) as amount
                        from store sto
                         join timing t on sto.id = t.store
                         join timing_sale ts on t.id = ts.timing_id
                         join wh_good g on ts.product_id = g.id
                        where t.approved_at is not null
                        group by sto.id, g.id
		';

        mysqli_query($con,$sql);

        mysqli_close($con);

        exit;
    }
}