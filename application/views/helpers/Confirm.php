<?php
/**
 * Created by PhpStorm.
 * User: thong
 * Date: 10/29/13
 * Time: 3:37 PM
 */
class Zend_View_Helper_Confirm extends Zend_View_Helper_Abstract
{
    public function confirm()
    {
        return '<script>
            $("#dialog").dialog({
                autoOpen: false,
                modal: true
            });
            $(".confirmLink").click(function(e) {
                e.preventDefault();
                var targetUrl = $(this).attr("href");

                $("#dialog").dialog({
                    buttons : {
                        "Confirm" : function() {
                            window.location.href = targetUrl;
                        },
                        "Cancel" : function() {
                            $(this).dialog("close");
                        }
                    }
                });

                $("#dialog").dialog("open");
            });
        </script>
        <div id="dialog" title="Confirmation Required">
            Are you sure about this?
        </div>
        ';
    }
}