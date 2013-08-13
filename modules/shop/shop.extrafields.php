<?php
/* ====================
  [BEGIN_COT_EXT]
  Hooks=admin.extrafields.first
  [END_COT_EXT]
  ==================== */
/**
 * Shop module
 *
 * @package shop
 * @author Alex
 * @copyright http://portal30.ru
 */
defined('COT_CODE') or die('Wrong URL');

require_once cot_incfile('shop', 'module');

$extra_whitelist[$db_shop_shop_userinfo] = array(
	'name' => $db_shop_shop_userinfo,
	'caption' => $L['Module'].' «Shop»',
	'type' => 'module',
	'code' => 'shop',
	'tags' => array(
//		'page.list.tpl' => '{LIST_ROW_XXXXX}, {LIST_TOP_XXXXX}',
//		'page.tpl' => '{PAGE_XXXXX}, {PAGE_XXXXX_TITLE}',
//		'page.add.tpl' => '{PAGEADD_FORM_XXXXX}, {PAGEADD_FORM_XXXXX_TITLE}',
//		'page.edit.tpl' => '{PAGEEDIT_FORM_XXXXX}, {PAGEEDIT_FORM_XXXXX_TITLE}',
//		'news.tpl' => '{PAGE_ROW_XXXXX}',
//		'recentitems.pages.tpl' => '{PAGE_ROW_XXXXX}',
	)
);

$extra_whitelist[$db_shop_orders] = array(
    'name' => $db_shop_orders,
    'caption' => $L['Module'].' «Shop»',
    'type' => 'module',
    'code' => 'shop',
    'tags' => array(
        'shop.cart.tpl' => '{ORDER_FORM_XXXXX}, {ORDER_FORM_XXXXX_TITLE}',
        'shop.order.list.tpl' => '{LIST_ROW_XXXXX}, {LIST_ROW_XXXXX_TITLE}',
        'shop.order.details.tpl' => '{ORDER_XXXXX}, {ORDER_XXXXX_TITLE}',
        'shop.admin.order.tpl' => '{LIST_ROW_XXXXX}, {LIST_ROW_XXXXX_TITLE}, {ORDER_XXXXX}, {ORDER_XXXXX_TITLE}, {ORDER_FORM_XXXXX}, {ORDER_FORM_XXXXX_TITLE}',
    )
);

$extra_whitelist[$db_shop_order_items] = array(
    'name' => $db_shop_order_items,
    'caption' => $L['Module'].' «Shop»',
    'type' => 'module',
    'code' => 'shop',
    'tags' => array()
);

$extra_whitelist[$db_shop_order_userinfo] = array(
    'name' => $db_shop_order_userinfo,
    'caption' => $L['Module'].' «Shop»',
    'type' => 'module',
    'code' => 'shop',
    'tags' => array()
);