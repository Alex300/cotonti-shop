<?php
/* ====================
[BEGIN_COT_EXT]
Hooks=admin
[END_COT_EXT]
==================== */

/**
 * Shop admin panel
 *
 * @package shop
 * @copyright http://portal30.ru 2012
 */
(defined('COT_CODE') && defined('COT_ADMIN')) or die('Wrong URL.');

list($usr['auth_read'], $usr['auth_write'], $usr['isadmin']) = cot_auth('shop', 'any');
cot_block($usr['isadmin']);

// Self requirements
require_once cot_incfile('shop', 'module');
require_once cot_langfile('shop', 'module');

$adminpath[] = array(cot_url('admin', 'm=extensions'), $L['Extensions']);
$adminpath[] = array(cot_url('admin', 'm=extensions&a=details&mod='.$m), $cot_modules[$m]['title']);
$adminpath[] = array(cot_url('admin', 'm='.$m), $L['Administration']);
$adminhelp = '';


// TODO кеширование
$t = new XTemplate(cot_tplfile('shop.admin'));

if (!$n) $n = 'main';

// Only if the file exists...
if (file_exists(cot_incfile('shop', 'module', 'admin.'.$n))) {
    require_once cot_incfile('shop', 'module','admin.'.$n);
    /* Create the controller */
    $_class = ucfirst($n).'Controller';

    $controller = new $_class();
    
    if(!$a) $a = cot_import('a', 'P', 'TXT');
    /* Perform the Request task */
    $shop_action = $a.'Action';
    if ($a && method_exists($controller, $shop_action)){
        $shopContent = $controller->$shop_action();
    }elseif(method_exists($controller, 'indexAction')){
        $shopContent = $controller->indexAction();
    }
}else{
    // Error page
    cot_die_message(404);
    exit;
}

if (COT_AJAX) {
    require_once $cfg['system_dir'] . '/header.php';
    echo $shopContent;
    require_once $cfg['system_dir'] . '/footer.php';
    exit;
}

$t->assign('CONTENT', $shopContent);

// Error and message handling
cot_display_messages($t);

$t->parse('MAIN');    
$adminmain = $t->text('MAIN');
