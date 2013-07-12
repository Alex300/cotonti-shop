<?php
/* ====================
[BEGIN_COT_EXT]
Hooks=urleditor.rewrite.first
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

//global $cot_urltrans;
//
//// Дабы не перекрыть запись из handy.dat: page	m=*						page?m={$m}
//// прописываем все урлы вручную
//$cot_urltrans['shop'] = array(
//    array("trans"=> 'shop/{$m}',
//          "params"=> array("m"=>"cart")
//    ),
//);
//
////var_dump($cot_urltrans);
//echo "<br />==<br />";
//
//var_dump($path);
//var_dump($count);
//return die;
//
//if ($count > 1 && $path[0] == 'shop'){
//    return true;
//    switch ($path[1]) {
//        case 'cart':
//        case 'order':
//            $_GET['e'] = $path[0];
//            $_GET['m'] = $path[1];
//            //var_dump($_GET);
//            return true;
//            break;
//
//        default:
//            break;
//    }
//}


?>