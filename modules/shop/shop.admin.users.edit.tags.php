<?php
/* ====================
[BEGIN_COT_EXT]
Hooks=admin.users.edit.tags
Tags=admin.users.tpl:{ADMIN_USERS_EDITFORM_GRP_MIN_PURCHASE},{ADMIN_USERS_EDITFORM_GRP_VENDOR_CURRENCY}
[END_COT_EXT]
==================== */


/**
 * Shop module for Cotonti Siena
 * Users admin edit tags
 *
 * @package Shop
 * @author  Kalnov Alexey    <kalnovalexey@yandex.ru>
 * @copyright Portal30 Studio http://portal30.ru
 */
defined('COT_CODE') or die('Wrong URL.');

$vendor = Vendor::getById(1);
if(!$vendor){
	$vendor = Vendor::getById(Vendor::getLoggedVendorId());
}
$venCurrency    = Currency::getById($vendor->curr_id);

$t->assign(array(
    'ADMIN_USERS_EDITFORM_GRP_MIN_PURCHASE' => cot_inputbox('text', 'rshop_min_purchase', htmlspecialchars($row['grp_shop_min_purchase'])),
    'ADMIN_USERS_EDITFORM_GRP_VENDOR_CURRENCY' => $venCurrency->curr_symbol,
));

