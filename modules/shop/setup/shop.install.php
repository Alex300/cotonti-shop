<?php
/**
 * module shop for Cotonti Siena
 * Install
 *
 * @package shop
 * @author Alex
 */
defined('COT_CODE') or die('Wrong URL.');

global $db_shop_currencies, $db_shop_order_status, $db_shop_shop_userinfo;

require_once cot_incfile('shop', 'module');

// Установка необходимых данных

// Устанавливаем валюты
$dbres = cot::$db->query("SELECT COUNT(*) FROM `$db_shop_currencies`");
if ($dbres->fetchColumn() == 0){
    $sqlFName = "$path/setup/$name.install.currencies.sql";
	// Run SQL install script
    $sql_err = cot::$db->runScript( file_get_contents($sqlFName) );
    if (empty($sql_err)){
        cot_message(cot_rc('ext_executed_sql', array('ret' => 'Currencies: OK')));
    }else{
        cot_error(cot_rc('ext_executed_sql', array('ret' => 'Currencies: '.$sql_err)));
        return false;
    }
}

// Устанавливаем Статусы заказов
$dbres = cot::$db->query("SELECT COUNT(*) FROM `$db_shop_order_status`");
if ($dbres->fetchColumn() == 0){
    $shopSQl = "
    --
    -- Dumping data for table `cot_shop_order_status`
    --
    INSERT INTO `{$db_shop_order_status}` (`os_code`, `os_title`, `os_desc`, `os_stock_handle`, `os_order`,
      `vendor_id`, `os_system`) VALUES
        ('P', 'Pending', '', 'R',1, 1, 1),
    ('U', 'Confirmed by shopper', '', 'R',2, 1, 0),
    ('C', 'Confirmed', '', 'R', 3, 1, 1),
    ('X', 'Cancelled', '', 'A',4, 1, 1),
    ('R', 'Refunded', '', 'A',5, 1, 1),
    ('S', 'Shipped', '', 'O',6, 1, 1);";
    cot::$db->query($shopSQl);
}

if(!function_exists('cot_extrafield_add')){
    require_once cot_incfile('extrafields');
}

// Поля для адресов доставки
//cot_extrafield_add($db_shop_shop_userinfo, 'title', 'input', '', '', '', true);
cot_extrafield_add($db_shop_shop_userinfo, 'company', 'input');
cot_extrafield_add($db_shop_shop_userinfo, 'firstname', 'input', '', '', '', true);
cot_extrafield_add($db_shop_shop_userinfo, 'middlename', 'input');
cot_extrafield_add($db_shop_shop_userinfo, 'lastname', 'input',  '', '', '', true);
cot_extrafield_add($db_shop_shop_userinfo, 'zip', 'input',  '', '', '', true);
cot_extrafield_add($db_shop_shop_userinfo, 'country', 'country',  '', '', '', true);
cot_extrafield_add($db_shop_shop_userinfo, 'city', 'inputint');
cot_extrafield_add($db_shop_shop_userinfo, 'city_name', 'input',  '', '', '', true);
cot_extrafield_add($db_shop_shop_userinfo, 'region', 'inputint');
cot_extrafield_add($db_shop_shop_userinfo, 'region_name', 'input');
cot_extrafield_add($db_shop_shop_userinfo, 'address', 'input',  '', '', '', true);
cot_extrafield_add($db_shop_shop_userinfo, 'phone', 'input',  '', '', '', true);
//cot_extrafield_add($db_shop_shop_userinfo, 'phone_2', 'input');

// Теже самые поля для $db_shop_order_userinfo устанавливаются по-умолчанию (не являются экстраполями)


// Add groups fields if missing
$dbres = cot::$db->query("SHOW COLUMNS FROM `".cot::$db->groups."` WHERE `Field` = 'grp_shop_min_purchase'");
if ($dbres->rowCount() == 0) {
    cot::$db->query("ALTER TABLE `".cot::$db->groups."` ADD COLUMN `grp_shop_min_purchase` DOUBLE DEFAULT 0");
}
$dbres->closeCursor();