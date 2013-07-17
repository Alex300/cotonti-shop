<?php
/* ====================
[BEGIN_COT_EXT]
Hooks=rc
[END_COT_EXT]
==================== */
 /**
  * module shop for Cotonti Siena
  * 
  * @package shop
  * @author Alex
  * @copyright http://portal30.ru
  */
defined('COT_CODE') or die('Wrong URL.');

// Загрузка JS CSS
// для фронтенд в глобал еще не определена $env, а нам нада выводить JS CSS в зависимости от положения юзера
global $env, $m, $n, $a, $o, $p;

if (!empty($env['location'])){
    // Для админки все ок
    if ($env['location'] == 'administration' && $m == 'shop'){

        cot_rc_link_file($cfg['modules_dir'].'/shop/js/shop_dialog.js');
        cot_rc_link_file($cfg['modules_dir'].'/shop/tpl/shop.admin.css', 'css');

        if ($n == 'order' && $a == 'edit'){
//            cot_rc_link_file($cfg['modules_dir'].'/shop/js/shop.admin.order.js');
//            cot_rc_link_file($cfg['modules_dir'].'/shop/tpl/shop.css', 'css');
        }
    }
}

//if ($env['location'] == 'list' || $env['location'] == 'pages'){
//    die('1');
//    require_once cot_incfile('shop', 'module');
//    
//    $tmp = (isset($pag['page_cat'])) ? $pag['page_cat'] : $c;
//    if (inShopCat($tmp)){
//        //cot_rc_link_file($cfg['modules_dir'].'/shop/js/shop_prices.js');    // без консолидации
//        cot_rc_link_file($cfg['modules_dir'].'/shop/tpl/shop.css');
//        cot_rc_add_file($cfg['modules_dir'].'/shop/js/shop_prices.js'); // с консолидацией
//        var_dump($cot_rc_reg);
//    }
//}