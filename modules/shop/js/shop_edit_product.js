/**
 * module shop for Cotonti Siena
 * 
 * @package shop
 * @subpackage product
 * @author Alex
 * @copyright http://portal30.ru
 */
var priceCount = 1;
var priceMax = 100;

$(".deloption").live("click",function () {
	$(this).parents('.addprice').find('input[type|="text"]').attr('value', '');
    $(this).parents('.addprice').find('select').val("");
	if (priceCount > 2){
		priceCount--;
		$(this).parents('.addprice').remove();
	}
	if (priceCount <= priceMax){
		$("#addoption").removeAttr('disabled');
	}
	return false;
});

$(document).ready(function(){
	priceCount = $('.addprice').length;
	$("#addoption").click(function () {
		if (priceCount < priceMax){
			$('#add_price_0').clone().attr("id", '').insertAfter($('.addprice').last()).slideDown('slow');
			priceCount++;
		}
		if (priceCount >= priceMax){
			$("#addoption").attr('disabled', 'disabled');
		}
		return false;
	});
	$('#addoption').show();
	$('.deloption').show();
});