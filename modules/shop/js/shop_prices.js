/**
 * module Shop for Cotonti Siena
 *
 * @package Shop
 * @author  Kalnov Alexey    <kalnovalexey@yandex.ru>
 * @copyright (с) Portal30 Studio http://portal30.ru
 */


/**
 * Пример обработчика события
 * "Товар добавлен в корзину"
 */
//$(document).on('shop.cart.added', function(e) {
    // Add some text to displayed message
    //e.text = 'Some text' + e.text;

    // Or turn off the message
    //e.act = '';
//});

// TODO защита от двойного включения
// TODO при изменении количества считать и выводить цену умножая значения кол-ва на цену без ajax
// TODO если мин. кол-во > 1 проверка на скидку сразу

var shop;
var shop_userInfo = '';

(function($) {
    //'use strict';

	$.fn.productUpdate = function() {
        var mod = $(this);

 		$.getJSON("/index.php?e=shop&m=cart&a=viewJS",
			function(datas, textStatus) {
				if (datas.totalProduct > 0) {
					mod.find(".shop_cart_products").html("").fadeTo('fast', 0.3);
                    mod.find(".total_products").fadeTo('fast', 0.3);
					$.each(datas.products, function(key, val) {
						$("#hiddencontainer .miniCart-container").clone().appendTo(shop.cart.miniCartClass + ' .shop_cart_products');
						$.each(val, function(key, val) {
							if ($("#hiddencontainer .miniCart-container ."+key)) mod.find(".shop_cart_products ."+key+":last").html(val) ;
						});
					});
					mod.find(".total").html(datas.billTotal);
					mod.find(".show_cart").html(datas.cart_show);

                    mod.addClass('shop-cart-full').removeClass('shop-cart-empty');
				} else {
                    mod.removeClass('shop-cart-full').addClass('shop-cart-empty');
                }
				mod.find(".total_products").html(datas.totalProductTxt).fadeTo('fast', 1);
                mod.find(".shop_cart_products").fadeTo('fast', 1).css('border-bottom', '1px dotted');
			}
		);

        return mod;
	};

    // ==== Shop Object ====
    shop = {
        // Селектор для миникорзины
        miniCartClass: '.shopMiniCart',

        cart: {
            // Миникорзина на странице сайта
            miniCart: null,

            /**
             * Add to Cart
             * @param productData Объект формы добавления в корзину или соотвествующие данные
             * @returns {boolean}
             */
            add: function(productData) {
                'use strict';

                var datas = productData;
                // Если передали реально форму
                if(productData.serialize) {
                    datas = productData.serialize();
                }

                var title = productData.title || '';
                if(title == '' && productData.find) {
                    title = productData.find('input[name="ptitle"]').val();
                }

                $.post('/index.php?e=shop&m=cart&a=ajxAdd', datas,
                    function(datas, textStatus) {

                        if(datas.stat == 1) title = title + ' ' + shopCartText;

                        var addEvent = jQuery.Event('shop.cart.added', {
                            act: datas.act,
                            title: title,
                            text: datas.msg,
                            cartElement: shop.cart.miniCart,
                            status: datas.stat,
                            productData: productData,
                            datas: datas
                        });
                        shop.cart.miniCart.trigger(addEvent);
                        if (addEvent.isDefaultPrevented()) return;

                        if(datas.stat == 1) {
                            if (addEvent.act == 'popup'){
                                shopDialog({
                                    text  : addEvent.text,
                                    title : addEvent.title,
                                    dialogClass: 'done'
                                });

                            }else if(addEvent.act == 'cart'){
                                window.location.href = datas.cartlink;
                            }

                        } else if(datas.stat == 2) {
                            var value = productData.find('.quantity-input').val() ;
                            shopDialog({
                                text  : addEvent.text,
                                title : addEvent.title,
                                dialogClass: 'warning'
                            })

                        } else {
                            title = shopCartError;
                            shopDialog({
                                text  : addEvent.text,
                                title : title,
                                dialogClass: 'error'
                            });
                        }

                        shop.cart.update();
                    }, "json");

                return false;

            },

            /**
             * Обновление отображения миникорзины
             */
            update: function() {
                if (shop.cart.miniCart.length > 0) {
                    shop.cart.miniCart.productUpdate();
                }
            }
        },

        /**
         * Добавить пользователя в наблюдатели за поступлением товара
         * @param form form Объект формы добавления в корзину или соотвествующие данные
         * @returns {boolean}
         */
        notifyUser: function(form) {
            'use strict';

            var notifyTextElement = $('.notify_text'),
                content = notifyTextElement.html(),
                notifyText = content;

            notifyTextElement.html('');

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
                                            shopDialogClose();
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
        },

        /**
         * Товар, "Летит в корзину"
         * @param el елемент страницы, который должен "полететь"
         */
        productFlyToCart: function(el) {
            'use strict';

            var width       = el.width(),
                height      = el.height(),
                flyEl       = $('<div />'),
                offset      = el.offset(),                  // Координаты элемента;
                cartOffset  = shop.cart.miniCart.offset(),  // Координаты корзины
                duration    = Math.ceil(offset.top * 1.5);

            flyEl.attr('id', el.attr('id') + 'flying').addClass('prod-flying').
                css({
                    //'z-index': '99999',
                    zIndex: '100',
                    border: '2px solid #ddd',
                    boxShadow: '0 5px 10px rgba(0, 0, 0, 0.2)',
                    position:  'absolute'
                });
            el.clone().css({opacity: 1}).appendTo(flyEl);
            flyEl.appendTo('body').offset(offset);
            flyEl.animate({
                    opacity: 0.7,
                    top:  Math.ceil(cartOffset.top),
                    left: Math.ceil(cartOffset.left),
                    width: 50,
                    height: 50
                }, duration,
                function() {
                    $(this).remove();
                }
            );
        },

        /**
         * Установка параметров товара
         * @param form
         * @param id
         */
        setProductType: function(form, id) {
            'use strict';

            var datas = form.serialize(),
                prices = $("#productPrice"+id);
            var in_pack = parseFloat(form.find('input[name="in_pack"]').val());
            var Qtt = parseFloat(form.find('input[name="quantity[]"]').val());
            var unit = form.find('input[name="unit"]').val();

            $.post('/index.php?e=shop&m=product&a=recalculate', datas,
                function(datas, textStatus) {
                    // refresh price
                    for(var key in datas) {
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
                    }
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
        },

        init: function () {
            'use strict';

            if(shop.cart.miniCart == null) {
                shop.cart.miniCart = $(shop.cart.miniCartClass);
            }


        }
    };
    // ==== /Shop Object ====

    // ==== Bind Event Handlers ====
    $(document).on('click', '.addtocart-button', function(e) {
        e.preventDefault();
        var form = $(this).closest('form.product');
        shop.cart.add(form);

        return false;
    });

    $(document).on('click', '.notify-button', function(e) {
        e.preventDefault();
        var form = $(this).closest('form.product');
        shop.notifyUser(form);

        return false;
    });

    $(document).on('click', 'form.product .quantity-plus', function(e) {
        e.preventDefault();

        var form        = $(this).closest('form.product'),
            product_id  = form.find('input[name="shop_product_id[]"]').val(),
            quantity    = form.find('.quantity-input'),
            Qtt         = quantity.val(),
            min_order   = parseFloat(form.find('input[name="min_order"]').val()),
            step        = parseFloat(form.find('input[name="step"]').val());

        if (isNaN(step) || step == 0) step = 1;
        if (isNaN(min_order) || min_order == 0) min_order = 1;

        Qtt = Qtt.replace(',', '.');
        Qtt = parseFloat(Qtt);
        if (!isNaN(Qtt)) {
            var newQtt = shop_nearMultiple(Qtt + step, step);
            quantity.val(newQtt);
            shop.setProductType(form, product_id);

        }else{
            quantity.val(min_order);
        }

        return false;
    });

    $(document).on('click', 'form.product .quantity-minus', function(e) {
        e.preventDefault();

        var form        = $(this).closest('form.product'),
            product_id  = form.find('input[name="shop_product_id[]"]').val(),
            quantity    = form.find('.quantity-input'),
            Qtt         = quantity.val(),
            min_order   = parseFloat(form.find('input[name="min_order"]').val()),
            step        = parseFloat(form.find('input[name="step"]').val());

        if (isNaN(step) || step == 0) step = 1;
        if (isNaN(min_order) || min_order == 0) min_order = 1;

        Qtt = Qtt.replace(',', '.');
        Qtt = parseFloat(Qtt);
        if (!isNaN(Qtt) && Qtt > min_order) {
            var newQtt = shop_nearMultiple(Qtt - step, step);
            quantity.val(newQtt);
            shop.setProductType(form, product_id);

        } else {
            quantity.val(min_order);
        }

        return false;
    });

    $(document).on('click', '.product select', function(e) {
        var form        = $(this).closest('form.product');
        shop.setProductType(form, product_id);
    });

    $(document).on('click', '.product input:radio', function(e) {
        var form        = $(this).closest('form.product');
        shop.setProductType(form, product_id);
    });

    // Проверяем шаг при выборе количества товара
    $(document).on('blur', '.product .quantity-input', function(e) {
        var form        = $(this).closest('form.product'),
            product_id  = form.find('input[name="shop_product_id[]"]').val(),
            Qtt         = parseFloat($(this).val()),
            min_order   = parseFloat(form.find('input[name="min_order"]').val()),
            step        = parseFloat(form.find('input[name="step"]').val());

        if (isNaN(step) || step == 0) step = 1;
        if (isNaN(min_order) || min_order == 0) min_order = 1;

        if (Qtt == min_order)  return ;

        if (Qtt < min_order){
            Qtt = min_order;
            $(this).val(Qtt);
            shop.setProductType(form, product_id);
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
            $(this).val(newQtt);
            shop.setProductType(form, product_id);
        }
    });

    $(document).on('keyup', '.product .quantity-input', function(e) {
        var form        = $(this).closest('form.product'),
            product_id  = form.find('input[name="shop_product_id[]"]').val(),
            Qtt         = $(this).val(),
            allowDecimal = form.find('input[name="allow_decimal"]').val(),
            min_order   = parseFloat(form.find('input[name="min_order"]').val());

        if(allowDecimal == 1){
            Qtt = Qtt.match(new RegExp("[\\d]+[.,]{0,1}[\\d]*",'g'));
            if (Qtt) {
                Qtt = Qtt[0];
                Qtt = Qtt.replace(',', '.');
            }

        } else {
            Qtt = parseInt(Qtt);
        }
        if (isNaN(Qtt)){
            Qtt = min_order;
            $(this).val(min_order);

        } else {
            $(this).val(Qtt);
        }
        shop.setProductType(form, product_id);
    });
    // ==== /Bind Event Handlers ====

})(jQuery);


/**
 * Найти число ближайшее к $num и кратное $k
 * @param num float
 * @param k   float
 * @returns float
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
    shop.init();

	$("form.js-recalculate").each(function(){
		if ($(this).find(".product-fields").length) {
			var id= $(this).find('input[name="shop_product_id[]"]').val();
            shop.setProductType($(this),id);
		}
	});
});
