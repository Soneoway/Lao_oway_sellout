$(function () {
	
	
	$('.subnavbar').find ('li').each (function (i) {
	
		var mod = i % 3;
		
		if (mod === 2) {
			$(this).addClass ('subnavbar-open-right');
		}
		
	});


	
});
var showOnlyOptionsSimilarToText = function (selectionEl, str, isCaseSensitive, callback) {
    if (typeof isCaseSensitive == 'undefined')
        isCaseSensitive = true;
    if (isCaseSensitive)
        str = str.toLowerCase();

    var $el = $(selectionEl);

    $el.children("option:selected").removeAttr('selected');
    $el.val('');
    $el.children("option").hide();

    $el.children("option").filter(function () {
        var text = $(this).text();
        if (isCaseSensitive)
            text = text.toLowerCase();

        var textNonUnicode = remove_unicode(text);
        if (text.indexOf(str) > -1 || textNonUnicode.indexOf(str) > -1){

            return true;
        }


        return false;
    }).show();

    if (callback)
        eval(callback+'();');
};

function remove_unicode(str)
{
    //str= str.toLowerCase();
    str= str.replace(/à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ/g,"a");
    str= str.replace(/è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ/g,"e");
    str= str.replace(/ì|í|ị|ỉ|ĩ/g,"i");
    str= str.replace(/ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ/g,"o");
    str= str.replace(/ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ/g,"u");
    str= str.replace(/ỳ|ý|ỵ|ỷ|ỹ/g,"y");
    str= str.replace(/đ/g,"d");
    return str;
}

function initSearchOption(elm, searchEl){
    var timeout;
    $('#'+searchEl).on("keyup", function (e) {
        if(e.keyCode == 40){
            $('#'+elm).focus();
            $('#'+elm + ' option:visible').first().attr('selected','selected');

        }else{
            var userInput = $('#'+searchEl).val();
            window.clearTimeout(timeout);
            timeout = window.setTimeout(function() {
                showOnlyOptionsSimilarToText($('#'+elm), userInput, true);
            }, 500);
        }
    });
}