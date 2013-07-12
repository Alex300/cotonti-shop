/**
 * module shop for Cotonti Siena
 * 
 * @package shop
 * @subpackage user
 * @author Alex
 * @copyright http://portal30.ru
 */

function isUserFormValid(options){
    options = options || '';
    var valid = true;
    $('#adress').find('.required').children('input').each(function(index, Element){
      if((!$(this).val() || $(this).val() == 0) && $(this).attr('type') != 'hidden'){
          $(this).removeClass('shop_valid').addClass('shop_notvalid');
          valid = false;
      }else{
          $(this).removeClass('shop_notvalid').addClass( 'shop_valid' );
      }
    });
    if (!valid) return false;
    
    return true;
}

$(document).ready(function(){
    $('#reg_chekout').click(function(){
        var valid = true;
        var valid2 = true;
        var uData = {
            'ruserfemail' : $('input[name|="ruserfemail"]').val(),
            'x' : $('input[name|="x"]').val()
        };
        $('#register').find('.required').children('input').each(function(index, Element){
            uData[$(this).attr('name')] = $(this).val();
            if(!$(this).val() || $(this).val() == 0){
                $(this).removeClass('shop_valid').addClass('shop_notvalid');
                valid = false;
            }else{
                $(this).removeClass('shop_notvalid').addClass( 'shop_valid' );
            }
        });
        valid2 = isUserFormValid();
        if (valid) valid = valid2;
        
//        if(uData.ruserfpassword2 != '' && uData.ruserfpassword != uData.ruserfpassword2){
//            $('input[name|="ruserfpassword2"]').removeClass('shop_valid').addClass('shop_notvalid');
//            $('#ruserfpassword2_desc').removeClass('shop_valid').addClass('shop_notvalid');
//            $('#ruserfpassword2_desc').html(Lshop.aut_passwordmismatch);
//        }else if(uData.ruserpassword2 != ''){
//            $('#ruserfpassword2_desc').removeClass('shop_notvalid').addClass('shop_valid');
//            $('#ruserfpassword2_desc').html('Ok');
//        }
        
        // Отправить данные для проверки
        $('#preloader').html('<img src="'+Lshop.imgUrl+'/loading.gif" />')
        $.post('/index.php?e=shop&m=user&a=checkRegForm', uData,
				function(datas, textStatus) {
					$('#preloader').html('');
                    if (datas.error == 1) valid = false;
                    for(var i in datas.fields) {
                       if(datas.fields[i]['error'] == 1){
                            $('input[name|="'+i+'"]').removeClass('shop_valid').addClass('shop_notvalid');
                            $('#'+i+'_desc').removeClass('shop_valid').addClass('shop_notvalid');
                        }else{
                            $('input[name|="'+i+'"]').removeClass('shop_notvalid').addClass('shop_valid');
                            $('#'+i+'_desc').removeClass('shop_notvalid').addClass('shop_valid');
                        }
                        $('#'+i+'_desc').html(datas.fields[i]['msg']);
                	}   
				}, "json");
        $('#task').val('saveregister');
        return valid;
    });
    
    $('#chekout').click(function(){
        $('#register').find('.required').children('input').each(function(index, Element){
            $(this).removeClass('shop_notvalid').removeClass( 'shop_valid' );
            var name = $(this).attr('name');
            $('#'+name+'_desc').removeClass('shop_notvalid').removeClass( 'shop_valid' );
            $('#'+name+'_desc').html('');
        });
        $('#task').val('save');
        return isUserFormValid();
    });
});