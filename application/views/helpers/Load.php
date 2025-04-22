<?php
/**
 * Created by PhpStorm.
 * User: thong
 * Date: 10/29/13
 * Time: 3:37 PM
 */
class Zend_View_Helper_Load extends Zend_View_Helper_Abstract
{
    public function load($type = "sort")
    {
        $script = "";

        switch ($type) {
            case 'sort':
                $script = '<script>
                    $(".sortable,  .paging-link").click(function(e){
                        e.preventDefault();
                        var _self = $(this);
                        var targetUrl = _self.attr("href");

                        //get ajax content
                        if ($(this).parents(".load-ajax").length){
                            $(this).parents("ul").after("<span class=\'loading\'></span>");
                            $.get(targetUrl
                                ,function(data,status){
                                    _self.parents(".load-ajax").html(data);
                                    $(".loading").remove();
                                });
                        } else
                            window.location.href = targetUrl;

                        return false;
                    });
                </script>';
                break;
            case 'combobox': // load nội dung của combobox tiếp theo theo ID từ combobox trước
                $script = '<script>
                    $(".change").change(function(){
                        var _self = $(this);
                        var id = _self.val();
                        var targetUrl = _self.data("href") + id;

                        $(_self).parent().nextAll().find(".load-ajax").html("");
                        if (id != 0) {
                            //get ajax content
                            if (_self.parent().next().find(".load-ajax:first").length){
                                $(_self).parent().next().find(".load-ajax:first").after("<span class=\'loading\'></span>");
                                $.get(targetUrl
                                    ,function(data,status){
                                        $(_self).parent().next().find(".load-ajax:first").html(data);
                                        $(_self).parent().next().find(".loading").remove();
                                    });
                            }
                        }

                        return false;
                    });
                </script>';
                break;
            default:
                
                break;
        }
        
        return $script;
    }
}