<?php
/**
 * module shop for Cotonti Siena
 * Update to version 1.0.1
 *
 * @package shop
 * @author Alex
 */
defined('COT_CODE') or die('Wrong URL.');

//require_once cot_incfile('shop', 'module');

// Add groups fields if missing
$dbres = cot::$db->query("SHOW COLUMNS FROM `".cot::$db->groups."` WHERE `Field` = 'grp_shop_min_purchase'");
if ($dbres->rowCount() == 0) {
	cot::$db->query("ALTER TABLE `".cot::$db->groups."` ADD COLUMN `grp_shop_min_purchase` DOUBLE DEFAULT 0");
}
$dbres->closeCursor();