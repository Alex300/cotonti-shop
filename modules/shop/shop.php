<?php
/* ====================
[BEGIN_COT_EXT]
Hooks=module
[END_COT_EXT]
==================== */
 /**
 * module shop for Cotonti Siena
 * 
 * @package shop
 * @version 1.0.0
 * @author Alex
 * @copyright http://portal30.ru
 */
defined('COT_CODE') or die('Wrong URL.');

// Environment setup
$env['location'] = 'shop';

// Self requirements
require_once cot_incfile('shop', 'module');

//if (COT_AJAX && !$m) $m = 'ajax';

// Only if the file exists...
if (file_exists(cot_incfile('shop', 'module', $m))) {
    require_once cot_incfile('shop', 'module', $m);
    /* Create the controller */
    $_class = ucfirst($m).'Controller';
    $controller = new $_class();
    
    // TODO кеширование
    /* Perform the Request task */
    $shop_action = $a.'Action';
    if (!$a && method_exists($controller, 'indexAction')){
        $content = $controller->indexAction();
    }elseif (method_exists($controller, $shop_action)){
        $content = $controller->$shop_action();
    }else{
        // Error page
		cot_die_message(404);
		exit;
    }
    
    //ob_clean();
    require_once $cfg['system_dir'] . '/header.php';
    if (isset($content)) echo $content;
    require_once $cfg['system_dir'] . '/footer.php';
}else{
    // Error page
    cot_die_message(404);
    exit;
}