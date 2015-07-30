<?php
/* ====================
[BEGIN_COT_EXT]
Hooks=admin.users.add.first
[END_COT_EXT]
==================== */

/**
 * Shop module for Cotonti Siena
 * User group admin add new
 *
 * @package Shop
 * @author  Kalnov Alexey    <kalnovalexey@yandex.ru>
 * @copyright Portal30 Studio http://portal30.ru
 */
defined('COT_CODE') or die('Wrong URL.');

// Минимальная сумма заказа для этой группы
$rgroups['grp_shop_min_purchase'] = (int)cot_import('rshop_min_purchase', 'P', 'INT');

