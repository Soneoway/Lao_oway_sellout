<?php
/**
* 
*/
class My_File_Progress
{
    private $js_function_name = '';

    function __construct($js_function_name = null) {
        // Turn off output buffering
        ini_set('output_buffering', 'off');
        // Turn off PHP output compression
        ini_set('zlib.output_compression', false);
        //Flush (send) the output buffer and turn off output buffering
        while (@ob_end_flush());

        // Implicitly flush the buffer(s)
        ini_set('implicit_flush', true);
        ob_implicit_flush(true);

        if (!is_null($js_function_name))
            $this->js_function_name = $js_function_name;
    }

    public static function lock($type = 'mou')
    {
        file_put_contents(APPLICATION_PATH.'/../public/files/'.$type.'/lock', '1');
    }

    
    public static function unlock($type = 'mou')
    {
        unlink(APPLICATION_PATH.'/../public/files/'.$type.'/lock');
    }

    public function flush($percent, $js_function_name = null)
    {
        if (is_null($js_function_name) && !is_null($this->js_function_name))
            $js_function = $this->js_function_name;
        elseif (!is_null($js_function_name))
            $js_function = $js_function_name;
        else
            return false;

        printf("<script>;%s(%s);</script>", $js_function, $percent);
        
        echo str_pad("",1024," ");
        echo "\n".PHP_EOL;
    }
}
