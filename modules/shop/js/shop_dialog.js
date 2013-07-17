//buttons: [
//    {text: updaterOptions.localized.accept,
//        click: function() {
//            invAccepted = true;
//            oc_responseInvite(1);
//            shopDialogClose();
//        }},
//    {text: updaterOptions.localized.reject,
//        click: function() { shopDialogClose(); }}
//],

/**
 * Диалог. Стандартный для Cotonti Shop
 * @package shop
 * @author Alex
 * @copyright http://portal30.ru
 * @param string title - заголовок окна
 * @param string text - текст
 * @param string dialogClass - тип: done, info, message или error
 * @param array buttons - кнопки
 * @param function open - callback function to handle the open event
 * @param function close - callback при закрытии диалога
 */
function shopDialog(options){
    if (shopDialogIsUI()){
        uiDialog(options);
    }else{
        jqmDialog(options);
    }

}

function shopDialogIsUI(){
    var isUI = true;
    if (jQuery.type(jQuery.ui) == 'undefined'){
        isUI = false;
    }else{
        if (jQuery.type(jQuery.ui.dialog) == 'undefined'){
            isUI = false;
        }
    }
    return isUI;
}

/**
 * Закрыть диалог
 */
function shopDialogClose(options){
    if(shopDialogIsUI()){
        $('#confirmBox').dialog( "close");
        $('#confirmBox').dialog( "destroy");
    }else{
        $('#confirmBox').jqmHide();
    }
    $('#confirmBox').remove();
}

//(function($) {
//    /**
//     * Закрыть диалог
//     */
//    $.fn.shopDialogClose = function() {
//        var dialog = $(this)
//        if(shopDialogIsUI()){
//            $(this).dialog( "destroy");
//        }else{
//            $(this).jqmHide();
//        }
//    }
//
//})(jQuery);

function uiDialog(options){
    options.title = options.title || '';
    options.dialogClass = options.dialogClass || 'message';
    options.buttons = options.buttons || false;

    var text = '<div class="'+options.dialogClass+'">' + options.text + '</div>';

    var bodyDiv = $('<div>', { 'id': "confirmBox" }).css('overflow', 'visible').html(text);
    $(bodyDiv).find('.jqmClose').click(function(){
        shopDialogClose();
        return false;
    });
    $(bodyDiv).dialog({
        title: options.title,
        buttons: options.buttons,
        width: 'auto',
        modal: true,
        close: function(){
            if (options.close) options.close();
            $('#confirmBox').remove();
        }
    });
}

function jqmDialog(options){
    // опции
    options.title = options.title || '';
    options.dialogClass = options.dialogClass || 'message';
    options.buttons = options.buttons || false;

    var title = '';
    if (options.title != ''){
        title = '<h2 class="info">'+options.title+'</h2>';
    }

    var closeBtn = '<div style="float: right; margin: -20px -20px 0 0"><img class="jqmClose" src="/modules/shop/tpl/images/close.png" style="cursor: pointer" /></div>';
    var text = closeBtn + title + '<div class="'+options.dialogClass+'">' + options.text + '</div>';

    var bodyDiv = $('<div>', { 'id': "confirmBox", 'class':  "jqmWindow" }).css('overflow', 'visible').html(text);
    $('body').prepend(bodyDiv);

    if (options.buttons != false){
        $.each(options.buttons, function(index, value) {
            var btnClass = '';
            value.action = value.action || false;
            /** deprecated use click: function() { shopDialogClose();}  instead **/
            if (value.action == 'close'){
                alert('using deprecated action == close');
                btnClass = "jqmClose ";
            }

            var btn = $('<button>', { 'id': "btn_"+index, 'class':  btnClass+"button" }).css('overflow', 'visible').html(value.text);
            // Поддержка события click для кнопки
            if (typeof value.click === 'function') $(btn).click(function(){ return value.click() });

            $(bodyDiv).append(btn);
        });
    }

    //$("#confirmBox").jqm({
    $(bodyDiv).jqm({
        modal:true,
        onShow:function(hash){
            hash.w.show();
            hash.w.css('margin-left', '-'+(hash.w.width()/2)+'px');
            hash.w.css('margin-top', '-'+(hash.w.height()/2)+'px');
            if (options.open) options.open();
        },
        onHide:function(hash){
            hash.w.fadeOut('2000',function(){ hash.o.remove(); });
            $('#confirmBox').remove();
            if (options.close) options.close();
        }
    });
    $('#confirmBox').jqmShow();
    
    return false;
}
