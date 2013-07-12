<?php
/* ====================
[BEGIN_COT_EXT]
Hooks=input
Order=12
[END_COT_EXT]
==================== */
/**
 * module shop for Cotonti Siena
 * URL Rewrire SEF
 * @package shop
 * @subpackage sef
 * 
 */
defined('COT_CODE') or die('Wrong URL.');

// Обработка пресета "Удобный"

if (!defined('COT_ADMIN') && !COT_AJAX && cot_plugin_active('urleditor') && 
        $cfg['plugin']['urleditor']['preset'] == 'handy'){
    
   // Преобразоваие URL в параметы сайта
   if (isset($_GET['rwr']) && !empty($_GET['rwr'])){
       $tmp = $_GET['rwr'];
		// Ignore ending slash and split the path into parts
		$path = explode('/', (mb_strrpos($_GET['rwr'], '/') == mb_strlen($_GET['rwr']) - 1) ? 
                mb_substr($_GET['rwr'], 0, -1) : $_GET['rwr']);
		$count = count($path);
        
        if ($count > 1 && $path[0] == 'shop'){
            switch ($path[1]) {
                case 'cart':
                case 'user':
                case 'product':
                    unset($_GET['c'], $_GET['al'], $_GET['id']);
                    $_GET['e'] = $path[0];
                    $_GET['m'] = $path[1];
                    if ($path[2]) $_GET['a'] = $path[2];
                    break;
                case 'order':
                    unset($_GET['c'], $_GET['al'], $_GET['id']);
                    $_GET['e'] = $path[0];
                    $_GET['m'] = $path[1];
                    if($count == 4){
                        $_GET['order_number'] = $path[2];
                        $_GET['order_pass'] = $path[3];
                    }elseif($count == 3 && mb_strpos($path[2], 'num_') !== false){
                        $_GET['id'] = str_replace('num_', '', $path[2]);
                    }elseif($count == 3){
                        $_GET['order_number'] = $path[2];
                    }
                    break;
//                case 'user':
//                case 'product':
//                    unset($_GET['c'], $_GET['al'], $_GET['id']);
//                    $_GET['e'] = $path[0];
//                    $_GET['m'] = $path[1];
//                    $_GET['a'] = $path[2];
//                    break;

                default:
                    break;
            }
        }
        //var_dump($_GET);
        //die;
   }
   
}

if (cot_plugin_active('urleditor') && $cfg['plugin']['urleditor']['preset'] == 'handy'){
    
    //// Дабы не перекрыть запись из handy.dat: page	m=*						page?m={$m}
    // прописываем все урлы вручную
    $cot_urltrans['shop'] = array(
        // shop/order?order_number=ef71084&order_pass=p_46d39
        array("trans"=> 'shop/{$m}/{$order_number}/{$order_pass}',
            "params"=> array("m"=>"order", 'order_number'=>'*', 'order_pass' => '*')
        ),
        array("trans"=> 'shop/{$m}/{$order_number}',
            "params"=> array("m"=>"order", 'order_number'=>'*')
        ),
        array("trans"=> 'shop/{$m}/num_{$id}',
            "params"=> array("m"=>"order", 'id'=>'*')
        ),
        array("trans"=> 'shop/{$m}/{$a}',
            "params"=> array("m"=>"cart", 'a'=>'*')
        ),
        array("trans"=> 'shop/{$m}',
            "params"=> array("m"=>"cart")
        ),
        array("trans"=> 'shop/{$m}',
            "params"=> array("m"=>"order")
        ),
        array("trans"=> 'shop/{$m}',
            "params"=> array("m"=>"user")
        ),
        array("trans"=> 'shop/{$m}/{$a}',
            "params"=> array("m"=>"product", 'a' => '*')
        ),
        array("trans"=> 'shop/{$m}',
            "params"=> array("m"=>"product")
        ),
    );
    
    //var_dump($cot_urltrans);
}
