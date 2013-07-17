/**
 * Настройки модуля
 * @package shop
 * @subpackage Admin
 * @author Alex
 * @copyright http://portal30.ru
 */

var shopImgPath = '/modules/shop/tpl/images'

var img = new Object();
img["load"] = new Image();
img["load"].src = shopImgPath + "/loading.gif";


$(document).ready(function(){

    // Екстраполя страниц
    $('#makePextf').click(function(){
        $(this).attr('disabled', "disabled");
        var loadImg = $('<img>', { 'id': "loadImg", 'src': img["load"].src }).css('vertical-align', 'middle');
        $(this).after(loadImg);

        // Creating Extra fields
        $.post("/admin.php?m=shop&n=product&a=addextfields", {'x': $('input[name|="x"]').val()},
            function(data){
                if (data.error != ''){
                    title = shopCartError;
                    shopDialog({
                        text  : data.message,
                        title : data.title,
                        dialogClass: 'error'
                    });
                }else{
                    shopDialog({
                        text  : data.message,
                        title : data.title,
                        dialogClass: 'done',
                        buttons: [
                            {
                             text: data.buttonText,
                             click: function(){return shopDialogClose()}
                            }
                        ]
                    });
                }
            }, "json");


        $(this).removeAttr('disabled');
        loadImg.remove();
        return false;
    });


    // Екстраполя пользователей
    $('#makeUextf').click(function(){
        $(this).attr('disabled', "disabled");
        var loadImg = $('<img>', { 'id': "loadImg", 'src': img["load"].src }).css('vertical-align', 'middle');
        $(this).after(loadImg);

        // Creating Extra fields
        $.post("/admin.php?m=shop&n=user&a=addextfields", {'x': $('input[name|="x"]').val()},
            function(data){
                if (data.error != ''){
                    title = shopCartError;
                    shopDialog({
                        text  : data.message,
                        title : data.title,
                        dialogClass: 'error'
                    });
                }else{
                    shopDialog({
                        text  : data.message,
                        title : data.title,
                        dialogClass: 'done',
                        buttons: [
                            {
                                text: data.buttonText,
                                click: function(){return shopDialogClose()}
                            }
                        ]
                    });
                }
            }, "json");


        $(this).removeAttr('disabled');
        loadImg.remove();
        return false;
    });

});