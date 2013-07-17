<?php
/* ====================
[BEGIN_COT_EXT]
Hooks=header.main
[END_COT_EXT]
==================== */
 /**
  * module shop for Cotonti Siena
  * 
  * @package shop
  * @author Alex
  */
defined('COT_CODE') or die('Wrong URL.');
//var_dump($env['location']);
// загрузка JS CSS
if ($env['ext'] == 'page' || $env['location'] == 'shop'){
    require_once cot_incfile('shop', 'module');
    require_once cot_langfile('shop', 'module');
    
    $tmp = (isset($pag['page_cat'])) ? $pag['page_cat'] : $c;

    if(empty($shop_priceScript)){
        if (!in_array($m, array('add', 'edit', 'user')) && ($env['location'] == 'shop' || inShopCat($tmp)) ){
            cot_rc_link_file($cfg['modules_dir'].'/shop/js/shop_prices.js');    // без консолидации
            cot_rc_link_file($cfg['modules_dir'].'/shop/js/shop_dialog.js');
            cot_rc_link_file($cfg['modules_dir'].'/shop/tpl/shop.css', 'css');
            $jsVars = '';
            $jsVars .= "shopCartText = '". addslashes( $L['shop']['minicart_added_js'] )."' ;\n" ;
            $jsVars .= "shopCartError = '". addslashes( $L['shop']['minicart_error_js'] )."' ;\n" ;
            cot_rc_embed($jsVars);
            $jsVars = '';
            //cot_rc_add_file($cfg['modules_dir'].'/shop/js/shop_prices.js'); // с консолидацией выводится в глобал'е

           /**
            * Хеадер выполнился 
            */
            $shop_priceScript = true;
        }
    }
}
if ($env['location'] == 'pages' && in_array($m, array('add', 'edit'))){
    $tmp = (isset($pag['page_cat'])) ? $pag['page_cat'] : $c;
    if (inShopCat($tmp)){
        cot_rc_link_file($cfg['modules_dir'].'/shop/js/shop_edit_product.js');  
    }
}

// на страницах редактирования/добавления не отображаем корзину
if (!defined('COT_ADMIN') && (!defined('COT_AJAX') || !COT_AJAX ) && $m != 'edit'){
    if ($cfg["shop"]['mCartOnShopOnly'] == 0 || ($cfg["shop"]['mCartOnShopOnly']==1 && isShop())){
        // Если включено кеширование статичных страниц для незарегов, корзина тоже попадает в кеш.
        // В этом случае надо ее обновить
        if($cfg['cache'] && $usr['id'] == 0){
            if( ($cfg['cache_index'] && $env['location'] == 'home') || ($cfg['cache_page'] && $env['ext'] == 'page') ||
                ($cfg['cache_forums'] && $env['ext'] == 'forums') ){

                // TODO выводить где-нить в JS файле
                $jsVars  = ' jQuery(document).ready(function(){
                    jQuery(".shopMiniCart").productUpdate();

                });' ;
                cot_rc_embed($jsVars);
                $jsVars = '';
            }
        }
    }
}

// Конфигурация модуля Shop
if ($env['location'] == 'administration' && $m == 'config' && $n == 'edit' && $o == 'module' && $p == 'shop'){
    cot_rc_link_file($cfg['modules_dir'].'/shop/js/shop_dialog.js');
    cot_rc_link_file($cfg['modules_dir'].'/shop/js/shop.config.js');
}
//var_dump($env['location']);

// Правильные селекты в админке
if ($env['location'] == 'administration' && $m == 'shop' && !in_array($n,array('order'))){
    cot_rc_link_file($cfg['modules_dir'].'/shop/js/select2/select2.min.js');
    cot_rc_link_file($cfg['modules_dir'].'/shop/js/select2/select2.css');
    cot_rc_embed_footer(
        '$(document).ready(function() { $("select").select2(); });'
    );
}

/**
 * Хеадер выполнился 
 */
$shop_headerDone = true;