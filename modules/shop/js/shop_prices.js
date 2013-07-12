/**
 * module shop for Cotonti Siena
 * 
 * @package shop
 * @author Alex
 * @copyright http://portal30.ru
 */

// TODO защита от двойного включения
// TODO при изменении количества считать и выводить цену умножая значения кол-ва на цену без ajax
// TODO если мин. кол-во > 1 проверка на скидку сразу

var shop_userInfo = '';

(function($) {
	$.fn.product = function(options) {
		
		this.each(function(){
			var cart = $(this),
			addtocart = cart.find('.addtocart-button'),
            notify    = cart.find('.notify-button'),
			plus   = cart.find('.quantity-plus'),
			minus  = cart.find('.quantity-minus'),
			select = cart.find('select'),
			radio = cart.find('input:radio'),
			product_id = cart.find('input[name="shop_product_id[]"]').val(),
			quantity = cart.find('.quantity-input'),
            min_order = parseFloat(cart.find('input[name="min_order"]').val()),
            //in_pack = cart.find('input[name="in_pack"]').val(),
            step = parseFloat(cart.find('input[name="step"]').val());

            if (isNaN(step) || step == 0) step = 1;
            if (isNaN(min_order) || min_order == 0) min_order = 1;

			addtocart.click(function(e) { 
				sendtocart(cart);
				return false;
			});
            notify.click(function(e) {
                notifyMe(cart);
                return false;
            });
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

			select.change(function() {
				$.setproducttype(cart, product_id);
			});

			radio.change(function() {
				$.setproducttype(cart, product_id);
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

		function sendtocart(form){
			var datas = form.serialize();
            $.post('/index.php?e=shop&m=cart&a=ajxAdd', datas,
				function(datas, textStatus) {
					if(datas.stat ==1){
                        var title = form.find('input[name="ptitle"]').val()+' '+shopCartText;
                        if (datas.act == 'popup'){
                            shopDialog({
                                text  : datas.msg,
                                title : title,
                                dialogClass: 'done'
                            });
                        }else if(datas.act == 'cart'){
                            window.location.href = datas.cartlink;
                        }
					} else if(datas.stat ==2){
						var value = form.find('.quantity-input').val() ;
						var title = form.find('input[name="ptitle"]').val();
                        shopDialog({
                            text  : datas.msg,
                            title : title,
                            dialogClass: 'warning'
                        })
					} else {
                        title = shopCartError;
                        shopDialog({
                            text  : datas.msg,
                            title : title,
                            dialogClass: 'error'
                        });
					}
                    // такой класс должен быть у дива корзины
					if ($(".shopMiniCart")[0]) {
						$(".shopMiniCart").productUpdate();
					}
				}, "json");

                return false;
		};

        function notifyMe(form){
            //var datas = form.serialize();
            var content = $('.notify_text').html();
            var notifyText = content;
            $('.notify_text').html('');
            var notifyTitle = $('.notify_title').html();
            var prodTitle = form.find('input[name="ptitle"]').val();
            notifyText = notifyText.replace('%1$s', prodTitle);
            var prod_id = form.find('input[name="shop_product_id[]"]').val();
            var x = form.find('input[name="x"]').val();
            shopDialog({
                text  : notifyText,
                title : notifyTitle,
                dialogClass: 'help',
                close: function(){ $('.notify_text').html(content);}
            });
            $('.notify_submit').unbind('click');
            $('.notify_submit').click(function(){
                var email = $('#prod_notify_email').val();
                var name = $('#prod_notify_name').val();
                var phone = $('#prod_notify_phone').val();
                $.post('/index.php?e=shop&m=product&a=ajx_notify_me',
                    {rprod_id : prod_id, remail: email, rname: name, rphone: phone, x : x },
                    function(datas, textStatus) {
                        if(datas.error != ''){
                            $('#notify_message').addClass('error').html(datas.error);
                            $('#notify_message').fadeIn('fast');
                            return false;
                        }
                        if(datas.message != ''){
                            $('#notify_message').removeClass().hide();
                            shopDialogClose();
                            shopDialog({
                                text  : datas.message,
                                title : notifyTitle,
                                buttons: [
                                    {text: 'Ok',
                                     click: function(){
//                                            $('.notify_text').html('content');
                                            shopDialogClose()
                                            return;
                                        }
                                    }
                                ],
                                dialogClass: 'done'
                            });
                        }
                    }, "json");
            });

            return false;
        };


	}
	$.setproducttype = function(form, id){
		var datas = form.serialize(),
		prices = $("#productPrice"+id);
        var in_pack = parseFloat(form.find('input[name="in_pack"]').val());
        var Qtt = parseFloat(form.find('input[name="quantity[]"]').val());
        var unit = form.find('input[name="unit"]').val();

        $.post('/index.php?e=shop&m=product&a=recalculate', datas,
			function(datas, textStatus) {
				// refresh price
				for(key in datas) {
					var value = datas[key];
                    var priceSpan = prices.find("span.Price"+key);
					if (value!=0) {
                        var blink = false;
                        if (priceSpan.html() != value){
                            priceSpan.fadeTo("fast", 0.5);
                            blink = true;
                        }
                        priceSpan.html(value);
                        prices.children(".Price"+key).show();
                        if (blink) priceSpan.fadeTo("fast", 1);
                    }else{
                        priceSpan.html(0);
                        prices.children(".Price"+key).hide();
                    }
				};
                $('#Pricetotal_'+id).html(datas.total);
                $('#prodTotal_'+id).fadeTo("fast", 1);
                var packsQt = Math.floor(Qtt/in_pack);
                var ost = Qtt - (in_pack * packsQt);
                ost = Math.round(ost * 1000) / 1000;
                var inPackHtml = packsQt;
                if (ost > 0) inPackHtml = inPackHtml + ' (+'+ ost +' '+ unit +')';
                $('#packsTotal_'+id).html(inPackHtml);
			}, "json");
		return false; // prevent reload
	};	
	$.fn.productUpdate = function() {
        mod = $(this);

		$.getJSON("/index.php?e=shop&m=cart&a=viewJS",
			function(datas, textStatus) {
				if (datas.totalProduct > 0) {
					mod.find(".shop_cart_products").html("")
                                                   .fadeTo('fast', 0.3);
					$.each(datas.products, function(key, val) {
						$("#hiddencontainer .miniCart-container").clone().appendTo(".shopMiniCart .shop_cart_products");
						$.each(val, function(key, val) {
							if ($("#hiddencontainer .miniCart-container ."+key)) mod.find(".shop_cart_products ."+key+":last").html(val) ;
						});
					});
					mod.find(".total").html(datas.billTotal);
					mod.find(".show_cart").html(datas.cart_show);
				}
				mod.find(".total_products").html(datas.totalProductTxt);
                mod.find(".shop_cart_products").fadeTo('fast', 1).css('border-bottom', '1px dotted');
			}
		);
	}

})(jQuery);

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

jQuery(document).ready(function($) {
	$(".product").product();
	$("form.js-recalculate").each(function(){
		if ($(this).find(".product-fields").length) {
			var id= $(this).find('input[name="shop_product_id[]"]').val();
			$.setproducttype($(this),id);
		}
	});
});
