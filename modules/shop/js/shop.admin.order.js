/**
 * Order Admin
 * @package shop
 * @subpackage Admin
 * @author Alex
 * @copyright http://portal30.ru
 */

// todo перерасчет стоимости в зависимости от количества

var shopImgPath = '/modules/shop/tpl/images'

var img = new Object();
img["load"] = new Image();
img["load"].src = shopImgPath + "/loading.gif";

(function($) {
    $.fn.product = function(options) {

        this.each(function(){
            var cart = $(this),
                plus   = cart.find('.quantity-plus'),
                minus  = cart.find('.quantity-minus'),
                product_id = cart.find('input[name="shop_product_id"]').val(),
                quantity = cart.find('.quantity-input'),
                min_order = parseFloat(cart.find('input[name="min_order"]').val()),
                step = parseFloat(cart.find('input[name="step"]').val());

            if (isNaN(step) || step == 0) step = 1;
            if (isNaN(min_order) || min_order == 0) min_order = 1;

//            addtocart.click(function(e) {
//                sendtocart(cart);
//                return false;
//            });

            plus.click(function() {
                var Qtt = quantity.val();
                Qtt = Qtt.replace(',', '.');
                Qtt = parseFloat(Qtt);
                if (!isNaN(Qtt)) {
                    var newQtt = shop_nearMultiple(Qtt + step, step);
                    quantity.val(newQtt);
                    $.setproducttype(cart,product_id);
                }else{
                    quantity.val(min_order);
                }
            });
            minus.click(function() {
                var Qtt = quantity.val();
                Qtt = Qtt.replace(',', '.');
                Qtt = parseFloat(Qtt);
                if (!isNaN(Qtt) && Qtt>min_order) {
                    var newQtt = shop_nearMultiple(Qtt - step, step);
                    quantity.val(newQtt);
                    $.setproducttype(cart,product_id);
                }else{
                    quantity.val(min_order);
                }
            });


            /**
             * Проверка step
             */
            quantity.blur(function() {
                var Qtt = parseFloat(quantity.val());
                if (Qtt == min_order)  return ;

                if (Qtt < min_order){
                    Qtt = min_order;
                    quantity.val(Qtt);
                    $.setproducttype(cart, product_id);
                    return ;
                }
                if (min_order != step){
                    var tmp = Qtt - min_order;
                    var newQtt = shop_nearMultiple(tmp, step) + min_order;

                }else{
                    var newQtt = shop_nearMultiple(Qtt, step);
                }

                if(Qtt != newQtt){
                    if (newQtt < min_order) newQtt = min_order;
                    quantity.val(newQtt);
                    $.setproducttype(cart, product_id);
                }
            });

            quantity.keyup(function() {
                var Qtt = quantity.val();
                var allowDecimal = cart.find('input[name="allow_decimal"]').val();
                if(allowDecimal == 1){
                    Qtt = Qtt.match(new RegExp("[\\d]+[.,]{0,1}[\\d]*",'g'));
                    if (Qtt){
                        Qtt = Qtt[0];
                        Qtt = Qtt.replace(',', '.');
                    }
                }else{
                    Qtt = parseInt(Qtt);
                }
                if (isNaN(Qtt)){
                    Qtt = min_order;
                    quantity.val(min_order);
                }else{
                    quantity.val(Qtt);
                }
                $.setproducttype(cart, product_id);
            });
        });


    }
    $.setproducttype = function(form, id){
        var datas = form.serialize();
        var in_pack = parseFloat(form.find('input[name="in_pack"]').val());
        var Qtt = parseFloat(form.find('input[name="quantity"]').val());
        var unit = form.find('input[name="unit"]').val();
        var priceOverride = parseFloat(form.find('input[name="finalprice_override"]').val());

        $.post('/admin.php?m=shop&n=order&a=recalculateItem', datas,
            function(datas, textStatus) {
                // refresh price
                for(key in datas) {
                    var value = datas[key];
                    //var priceSpan = prices.find("span.Price"+key);
                    var priceSpan = $("span#addItem_"+key);
                    if (value!=0) {
                        var blink = false;
                        if (priceSpan.html() != value){
                            priceSpan.fadeTo("fast", 0.5);
                            blink = true;
                        }
                        priceSpan.html(value);
                        if (blink) priceSpan.fadeTo("fast", 1);
                    }else{
                        priceSpan.html(0);
                    }
                };
                if(!isNaN(priceOverride)){
                    $('#addItem_total').html(Math.round(priceOverride * Qtt * 100) / 100);
                }
                var packsQt = Math.floor(Qtt/in_pack);
                var ost = Qtt - (in_pack * packsQt);
                ost = Math.round(ost * 1000) / 1000;
                var inPackHtml = packsQt;
                if (ost > 0) inPackHtml = inPackHtml + ' (+'+ ost +' '+ unit +')';
                $('#packsTotal_'+id).html(inPackHtml);
            }, "json");
        return false; // prevent reload
    };

})(jQuery);


function addOrderItem(prodId, Qtt, priceOverride){
    var order_id = $('#orderItemForm input[name="id"]').val();
    var x = $('input[name="x"]').val();
    $.post('/admin.php?m=shop&n=order&a=ajxAddItem',
        {order_id: order_id, prod_id : prodId, quantity: Qtt, priceOverride: priceOverride, x : x },
        function(datas, textStatus) {
            if(datas.error != ''){
                shopDialogClose();
                alert(datas.error);
                return false;
            }
            if(datas.message != ''){
                shopDialogClose();
                alert(datas.message);
            }
            location.reload();
        }, "json");
    return false;
}

$(document).ready(function(){

    $('#show_updStatus').click(function(){
        $('#updOrderStatus').slideToggle();
        return false;
    });
    $('#updOrderStatusCancel').click(function(){
        $('#updOrderStatus').slideUp();
        //return false;
    });

    // Выбрать все позиции заказа
    $('#chk_oitems').click(function(){
        var state = $(this).prop("checked");
        $(':checkbox[name^="oitems"]').prop("checked", state);

        //return false;
    });

    $(document).on("focus", ".acomplete", function(){
//    $(".acomplete").live("focus", function() {
        var ac_set = $(this).attr('ac_set');
        if (!ac_set){
            var param =  $(this).attr('name');
            var x = $('input[name="x"]').val();
            $(this).autocomplete("/index.php?e=shop&m=product&a=autocomplete", {
                multiple: false,
                minChars: '1',
                extraParams : {param : param, _ajax : '1'},
                formatItem: function(row) {
                    var ret = '<span><em>'+row[1]+'</em> &nbsp; &nbsp;'+row[0];
                    if (row[2] != ''){
                        ret = ret + " &nbsp; ("+row[2]+")";
                    }
                    ret = ret + '</span>';
                    return ret;
                }
            });

            $(this).result(function(event, data, formatted) {
                $.post('/index.php?e=shop&m=product&a=getProductAC',
                    {rprod_id : data[1], x : x },
                    function(datas, textStatus) {
                        if(datas.error != ''){
                            alert(datas.error);
                            return false;
                        }
                        if(datas.message != ''){
                            alert(datas.message);
                        }
                        datas.product.PROD_PROD_MANUFACTURER_ID = datas.product.PROD_PROD_MANUFACTURER_ID | 0;
                        //Заполняем данные товара
                        var total = Math.round(datas.product.PROD_PROD_PRICES.salesPrice * datas.product.PROD_PROD_MIN_ORDER_LEVEL * 100) / 100;
                        $('form.product input[name="quantity"]').val(datas.product.PROD_PROD_MIN_ORDER_LEVEL);
                        $('form.product input[name="shop_product_id"]').val(datas.product.PROD_ID);
                        $('form.product input[name="in_pack"]').val(datas.product.PROD_PROD_IN_PACK);
                        $('form.product input[name="unit"]').val(datas.product.PROD_PROD_UNIT);
                        $('form.product input[name="step"]').val(datas.product.PROD_PROD_STEP);
                        $('form.product input[name="allow_decimal"]').val(datas.product.PROD_PROD_ALLOW_DECIMAL);
                        $('form.product input[name="min_order"]').val(datas.product.PROD_PROD_MIN_ORDER_LEVEL);
                        $('form.product input[name="shop_category_id"]').val(datas.product.PROD_CAT);
                        $('#addItem_total').text(total);
                        $('#addItem_title').html('<a href="'+datas.product.PROD_URL+'" target="_blank">'+datas.product.PROD_SHORTTITLE+'</a>');
                        $('#addItem_id').html(datas.product.PROD_ID);
                        $('#addItem_sku').html(datas.product.PROD_PROD_SKU);
                        $('#addItem_cat').html(datas.product.PROD_CATPATH_SHORT);
                        $('#addItem_salesPrice').html(datas.product.PROD_PROD_PRICES.salesPrice);
                        $('#addItem_basePrice').html(datas.product.PROD_PROD_PRICES.basePrice);
                        $('#addItem_cost').html(datas.product.PROD_PROD_PRICES.costPriceShopCurrency);
                        if(datas.product.PROD_PROD_MANUFACTURER_NAME != ''){
                            $('#addItem_manufacturer').html('<a href="'+datas.product.PROD_PROD_MANUFACTURER_URL+'" target="_blank">'+datas.product.PROD_PROD_MANUFACTURER_NAME+'</a>');
                        }else{
                            $('#addItem_manufacturer').html('');
                        }
                        $('#addItem_inStock').html(datas.product.PROD_PROD_IN_STOCK);
                        $('#addItem_ordered').html(datas.product.PROD_PROD_ORDERED);
                        $('#addItem_min_order').html(datas.product.PROD_PROD_MIN_ORDER_LEVEL);
                        $('#addItem_max_order').html(datas.product.PROD_PROD_MAX_ORDER_LEVEL);
                        $('#addItem_step').html(datas.product.PROD_PROD_STEP);

                        // Сюда указать форму
                        $("#addItemForm").product();

                        $('#addItemInfo').slideToggle(50, function(){
                            //'-'+($('#confirmBox').height()/2)+'px';
//                            $margTop = - ($('#confirmBox').height()/2);
                            $('#confirmBox').animate({
                                'margin-top': - ($('#confirmBox').height()/2)
                            }, 400, function() {
                                // Animation complete.
                            });
//                            $('#confirmBox').css('margin-top', '-'+($('#confirmBox').height()/2)+'px');
                        });

                    }, "json");
                return false;
            });
            $(this).attr('ac_set', '1');

        }
    });

    // Кнопка Добавить позицию
    $('#addItem_btn').click(function(){
        var Content = $('#addItem').html();
        $('#addItem').html('');
        shopDialog({
            text  : Content,
            title : 'Add Order Item',
            close: function(){
                $('#addItem').html(Content);
            },
            buttons: [
                {text: 'Ok',
                    click: function(){
                        var prodId = $('#addItemInfo input[name="shop_product_id"]').val();
                        var Qtt = $('#addItemInfo input[name="quantity"]').val();
                        var priceOverride = $('#addItemInfo input[name="finalprice_override"]').val();
                        addOrderItem(prodId, Qtt, priceOverride);
                        shopDialogClose();
                        return false;
                    }
                },
                {text: 'Cancel',
                    click: function(){$('#addItem').html(Content); return shopDialogClose()}
                }
            ]
        });
        return false;

    });

    // Выбор способа оплаты
    $('#select_paym_id').change(function() {
        var state = $(this).attr('state');
        if(state == $(this).val()) {
            $('#save_payment').attr('disabled', 'disabled');
        }else{
            $('#save_payment').removeAttr( 'disabled' );
        }
    });
    $('#save_payment').click(function(){
        if($('#save_payment').attr('disabled') == 'disabled') return false;

        var x = $('input[name="x"]').val();
        var order_id = $('#orderItemForm input[name="id"]').val();

        var parent = $('#payment_title').parent();
        parent.css('opacity', 0.4);

        $.post('/admin.php?m=shop&n=order&a=setPayment',
            {order_id: order_id, rpaym_id : $('#select_paym_id').val(), x : x },
            function(datas, textStatus) {
                if(datas.error != ''){
                    alert(datas.error);
                    return false;
                }
                if(datas.message != ''){
                    alert(datas.message);
                }
                parent.css('opacity', 1);
                $('#payment_title').html(datas.paym_title);
                $('#payment_desc').html(datas.paym_desc);
                $('#select_paym_id').attr('state', datas.paym_id);
            }, 'json');

        return false;
    });
    // /Выбор способа оплаты
});

/**
 * Найти число ближайшее к $num и кратное $k
 * @param float $num
 * @param float $k
 * @return float
 */
function shop_nearMultiple(num, k){
    var tmp = num % k;
    var min = num - tmp;
    var max = min + k;

    min = Math.round(min * 1000) / 1000;
    max = Math.round(max * 1000) / 1000;

    if (Math.abs(num - min) < Math.abs(max - num)) return min;

    return max;
}