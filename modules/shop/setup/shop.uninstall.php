<?php
/**
 * module shop for Cotonti Siena
 * 
 * @package shop
 * @author Alex
 * @copyright http://portal30.ru
 *
 */
defined('COT_CODE') or die('Wrong URL.');

global $db_shop_shop_userinfo;
if(!isset($db_shop_shop_userinfo)) require cot_incfile('shop', 'module');


// Поля для адресов доставки
//cot_extrafield_remove($db_shop_shop_userinfo, 'title');
cot_extrafield_remove($db_shop_shop_userinfo, 'company');
cot_extrafield_remove($db_shop_shop_userinfo, 'firstname');
cot_extrafield_remove($db_shop_shop_userinfo, 'middlename');
cot_extrafield_remove($db_shop_shop_userinfo, 'lastname');
cot_extrafield_remove($db_shop_shop_userinfo, 'address');
cot_extrafield_remove($db_shop_shop_userinfo, 'zip');
cot_extrafield_remove($db_shop_shop_userinfo, 'city');
cot_extrafield_remove($db_shop_shop_userinfo, 'city_name');
cot_extrafield_remove($db_shop_shop_userinfo, 'region');
cot_extrafield_remove($db_shop_shop_userinfo, 'region_name');
cot_extrafield_remove($db_shop_shop_userinfo, 'country');
cot_extrafield_remove($db_shop_shop_userinfo, 'phone');
//cot_extrafield_remove($db_shop_shop_userinfo, 'phone_2');
// remove table
cot::$db->query("DROP TABLE IF EXISTS `$db_shop_shop_userinfo`");


// Remove Files columns from groups table
$dbres = cot::$db->query("SHOW COLUMNS FROM `".cot::$db->groups."` WHERE `Field` = 'grp_shop_min_purchase'");
if ($dbres->rowCount() == 1) {
    cot::$db->query("ALTER TABLE `".cot::$db->groups."` DROP COLUMN `grp_shop_min_purchase`");
}
$dbres->closeCursor();