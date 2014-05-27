<?php
/**
 * module shop for Cotonti Siena
 * 
 * @package shop
 * @author Alex
 * @copyright http://portal30.ru
 * @todo Статус заказа по умолчанию при формировании заказа
 */
defined('COT_CODE') or die('Wrong URL.');
if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);

// Disable messages "Strict standards"... for now
// @todo fix all "Strict standards"
error_reporting(error_reporting() ^ E_STRICT);

require_once cot_langfile('shop', 'module');
require_once cot_incfile('shop', 'module', 'resources');

// === Конфиг ===
// TODO регулярки для проверки правильности экстраполей
// Префиксы:
// pextf - page extra field
// uextf - user extra field

/**
 * Системные статусы заказа
 *  stockOut is in normal case shipped product
 *  order_stock_handle
 *  'A' : stock Available
 *  'O' : stock Out
 *  'R' : stock reserved
 */

// === Эти настройки пока не используются (или для разработки) ===
// Экстраполя пользователя (пока рекомендуется назвать именно так)
//$cfg["shop"]['uextf_city_id'] = 'city_id';  // Пока не используется. Это как Альтернатива city
//$cfg["shop"]['uextf_state_id'] = '';        // Пока не используется.


$cfg["shop"]['max_recent_products'] = 3; // Количество недавно просмотренных товаров
$cfg["shop"]['price_show_packaging_pricelabel'] = 1;    // Цена упаковки (пачки). Когда включено, цена расчитывается
                                                        //     за пачку
                                                        // (или с ценой упаковки... TODO разобраться) (def. 0)

$cfg["shop"]['useSSL'] = 0;             // Использовать ssl для оформоения заказа
$cfg["shop"]['multix'] = 0;             // Несколько продавцов (Только для разработчиков) 	(def. 0)
/** @deprecated Use Vendor setting instead */
$cfg["shop"]['default_currency'] = 131; // Валюта по-умолчанию (def. 47 - Euro)
// === /Эти настройки пока не используются (или для разработки) ===

// Поля реквизитов пользователя для валидации пользовательских данных
$cfg["shop"]['user_fields'] = array('BT' => array());
if(isset($cfg["shop"]['bt_fields'])){
    $cfg["shop"]['bt_fields'] = str_replace(array("\n", "\r"), '', $cfg["shop"]['bt_fields']);
    $tmp = explode(',', $cfg["shop"]['bt_fields']);
    if (count($tmp) > 0){
        foreach($tmp as $fld){
            list($name, $req) = explode('|', trim($fld));
            $cfg["shop"]['user_fields']['BT'][] = array(
                'field_name' => (string)$name,
                'field_required' => ((bool)$req) ? 1 : 0
            );
        }
    }
}


// настройки по отображению цен:
$shopPriceTypes = array('basePrice', 'variantModification', 'basePriceVariant', 'discountedPriceWithoutTax',
    'priceWithoutTax', 'taxAmount', 'basePriceWithTax', 'salesPrice', 'salesPriceWithDiscount', 'discountAmount',
    'unitPrice');
foreach($shopPriceTypes as $pt){
    $tmp = explode('|', trim($cfg["shop"]['s'.$pt]));
    $cfg["shop"][$pt] = ($tmp[0]) ? 1 : 0;
    $cfg["shop"][$pt.'Text'] = ($tmp[1]) ? 1 : 0;
    $cfg["shop"][$pt.'Rounding'] = (isset($tmp[2])) ? (int)$tmp[2] : 2;
}


// Главная страница магазина
//$cfg["shop"]['mainPage'] = cot_url('shop');
// или лучше так:
$cfg["shop"]['mainPageUrl'] = shop_readShopCats();
$cfg["shop"]['mainPageUrl'] = cot_url('page', array('c' => $cfg["shop"]['mainPageUrl'][0]));
if (!cot_url_check($cfg["shop"]['mainPageUrl'])) $cfg["shop"]['mainPageUrl'] = $cfg['mainurl'].'/'.$cfg["shop"]['mainPageUrl'];
$cfg["shop"]['mainPageTitle'] = $structure['page'][$cfg["shop"]['cats'][0]]['title'];


/**
 * This number is for obstruction, similar to the prefix cot_ of cotonti it should be avoided
 * to use the standard 7, choose something else between 1 and 99, it is added to the ordernumber as counter
 * and must not be lowered.
 */
define('SHOP_ORDER_OFFSET',3);

// Global variables
global $db_shop_currencies, $db_shop_calcs, $db_shop_calc_categories, $db_shop_calc_countries, $db_shop_calc_groups,
       $db_shop_calc_states, $db_shop_coupons, $db_shop_product_prices, $db_shop_product_prices_gr, $db_shop_shipmethods,
       $db_shop_shipmethods_gr, $db_shop_paymethods, $db_shop_paymethods_gr, $db_shop_orders, $db_shop_order_items,
       $db_shop_vendors, $db_shop_order_history, $db_shop_order_calc_rules, $db_shop_order_userinfo, $db_shop_waitingusers,
       $db_shop_order_status, $db_x;

$db_shop_currencies         = (isset($db_shop_currencies)) ? $db_shop_currencies : $db_x . 'shop_currencies';
$db_shop_calcs              = (isset($db_shop_calcs)) ? $db_shop_calcs : $db_x . 'shop_calcs';
$db_shop_calc_categories    = (isset($db_shop_calc_categories)) ? $db_shop_calc_categories : $db_x . 'shop_calc_categories';
$db_shop_calc_countries     = (isset($db_shop_calc_countries)) ? $db_shop_calc_countries : $db_x . 'shop_calc_countries';
$db_shop_calc_groups        = (isset($db_shop_calc_groups)) ? $db_shop_calc_groups : $db_x . 'shop_calc_groups';
$db_shop_calc_states        = (isset($db_shop_calc_states)) ? $db_shop_calc_states : $db_x . 'shop_calc_states';
$db_shop_coupons            = (isset($db_shop_coupons)) ? $db_shop_coupons : $db_x . 'shop_coupons';
$db_shop_product_prices     = (isset($db_shop_product_prices)) ? $db_shop_product_prices : $db_x . 'shop_product_prices';
$db_shop_product_prices_gr  = (isset($db_shop_product_prices_gr)) ? $db_shop_product_prices_gr : 
    $db_x . 'shop_product_prices_groups';
$db_shop_shipmethods    = (isset($db_shop_shipmethods)) ? $db_shop_shipmethods : 
    $db_x . 'shop_shipmentmethods';
$db_shop_shipmethods_gr = (isset($db_shop_shipmethods_gr)) ? $db_shop_shipmethods_gr :
    $db_x . 'shop_shipmentmethods_groups';
$db_shop_paymethods     = (isset($db_shop_paymethods)) ? $db_shop_paymethods : 
    $db_x . 'shop_paymentmethods';
$db_shop_paymethods_gr  = (isset($db_shop_paymethods_gr)) ? $db_shop_paymethods_gr :
    $db_x . 'shop_paymentmethods_groups';
// Заказы
$db_shop_orders         = (isset($db_shop_orders)) ? $db_shop_orders : $db_x . 'shop_orders';
// Элементы заказов (товары)
$db_shop_order_items    = (isset($db_shop_order_items)) ? $db_shop_order_items : $db_x . 'shop_order_items';
// Продавцы
$db_shop_vendors    = (isset($db_shop_vendors)) ? $db_shop_vendors : $db_x . 'shop_vendors';
// История заказов
$db_shop_order_history      = (isset($db_shop_order_history)) ? $db_shop_order_history : $db_x . 'shop_order_history';
// Правила расчета цен в заказе
$db_shop_order_calc_rules  = (isset($db_shop_order_calc_rules)) ? $db_shop_order_calc_rules :
    $db_x . 'shop_order_calc_rules';
// Информация о заказчике
$db_shop_order_userinfo  = (isset($db_shop_order_userinfo)) ? $db_shop_order_userinfo : $db_x . 'shop_order_userinfo';
// Пользователи, ожидающие поступления товара
$db_shop_waitingusers  = (isset($db_shop_waitingusers)) ? $db_shop_waitingusers : $db_x . 'shop_waitingusers';
/** @var string $db_shop_order_status All available order statuses */
$db_shop_order_status  = (isset($db_shop_order_status)) ? $db_shop_order_status : $db_x.'shop_order_status';
/** @var string $db_shop_shop_userinfo Customer Information  */
$db_shop_shop_userinfo  = (isset($db_shop_shop_userinfo)) ? $db_shop_shop_userinfo : $db_x.'shop_userinfo';

/**
 * Дополнительный тип конфига.
 * Функция, которой передаются первыми параметрами: текущее значение, имя элемента, другие параметры из настроек....
 * Eсли callback функция возвращает массив, то выводится Select как в эл-то CALLBACK, иначе - выводится возвращаемый
 *    функцией текст
 */
define('SHOP_CONFIG_TYPE_CUSTOM', 21);

// === /Конфиг ===

function shopAutoLoader($class){
    global $cfg;
    $fName = $cfg['modules_dir'].DS.'shop'.DS.'models'.DS.$class.'.php';

    if(file_exists($fName)){
        include($fName);
    }else{
        $fName = $cfg['modules_dir'].DS.'shop'.DS.'lib'.DS.$class.'.php';
        if(file_exists($fName)){
            include($fName);
        }else{
            return false;
        }
    }
}


/**
 * Находимся ли сейчас в магазине
 * @return bool
 */
function isShop(){
    global $env, $c, $pag;

    if($env['location'] == 'shop')  return true;

    if ($env['location'] == 'list' || $env['location'] == 'pages'){
        $tmp = (isset($pag['page_cat'])) ? $pag['page_cat'] : $c;
        if (inShopCat($tmp)) return true;
    }
    
    return false;
}

/**
 * Находится ли категория в магазине
 * @param bool|string $c - код категории
 * @return bool
 */
function inShopCat($c = false){
    global $cfg;
    
    if (!isset($cfg["shop"]['cats']) || !$cfg["shop"]['cats']) shop_readShopCats();

    return in_array($c, $cfg['shop']['cats']);
}

/**
 * Все категории магазина
 * @todo кеширование (х.з категории вроде и так кешируются)
 * @return array коды категорий магазина
 */
function shop_readShopCats(){
    global $cfg, $structure;
    
    if(is_array($cfg["shop"]['cats'])) return $cfg["shop"]['cats'];
    
    // Получить вложенные категории
    $tmpCats = explode(',', $cfg["shop"]['rootCats']);
    $cats = array();
    foreach ($tmpCats as $key => $val){
        $tmpCats[$key] = trim($tmpCats[$key]);
        if (!isset($structure['page'][$tmpCats[$key]])) continue;
        $cats = array_merge($cats, cot_structure_children('page', $tmpCats[$key], true, true, true, false));
        
    }
    $cats = array_unique($cats);
    //natsort($cats);
    $cfg["shop"]['cats'] = $cats;
    
    return $cfg["shop"]['cats'];
}

/**
 * Получить последнюю посещенную категорию
 */
function shop_getLastVisitedCategory(){
    return $_SESSION['__shop']['lastVisitedCategory'];
}

/**
 * Запомнить последнюю посещенную категорию
 * @param type $category - category code
 * @return boolean 
 */
function shop_setLastVisitedCategory($category){
    $_SESSION['__shop']['lastVisitedCategory'] = $category;
    return true;
}

/**
 * Добавить товар в последние просмотренные 
 */
function shop_addProductToRecent($productId){
    global $cfg;
    $productId = (int)$productId;
    if ($productId < 1) return;
    
    $products_ids = $_SESSION['__shop']['lastVisitedProducts'];
    if (!$products_ids) $products_ids = array();
    $key = array_search($productId, $products_ids);
    if($key!==FALSE){
        unset($products_ids[$key]);
    }
    array_unshift($products_ids, $productId);
    $products_ids = array_unique($products_ids);

    $maxSize = $cfg["shop"]['max_recent_products'];
    if(count($products_ids) > $maxSize){
        array_splice($products_ids, $maxSize);
    }
    $_SESSION['__shop']['lastVisitedProducts'] =  $products_ids;
}

/**
 * Получить максимальную цену в магазине. Абсолютное значение без учета курсов валют
 * @global CotDB $db
 * @param array $cats
 * @param array|string $where
 * @return int
 * @todo cache
 */
function shop_getAbsMaxPrice($cats = array(), $where = array()){
    global $db, $db_shop_product_prices, $db_pages;

    $sqlWhere = array("pa.page_state = 0");
    if (is_array($cats) && count($cats) > 0){
        $sqlWhere[] = "page_cat IN('".implode("', '", $cats)."')";
    }
    if(is_string($where) || (is_array($where) &&  count($where) > 0) ){
        $sqlWhere = $where;
    }
    if (is_array($sqlWhere)){
        $sqlWhere = implode(' AND ', $sqlWhere);
    }
    $sqlWhere = str_replace('WHERE', '', $sqlWhere);
    $sql = "SELECT MAX(pp.price_price) as max_price
        FROM $db_shop_product_prices as pp
        JOIN $db_pages as pa ON pp.product_id=pa.page_id
        WHERE $sqlWhere";

    $res[0] = $db->query($sql)->fetchColumn();

    $sql = "SELECT MAX(pp.price_override_price) as max_o_price
        FROM $db_shop_product_prices as pp
        JOIN $db_pages as pa ON pp.product_id=pa.page_id
        WHERE $sqlWhere AND pp.price_override=1";

    $res[1] = $db->query($sql)->fetchColumn();

    $mPrice = ceil(max($res));

    return $mPrice;
}
/**
 * Найти число ближайшее к $num и кратное $k
 * @param float $num
 * @param float $k
 * @return float
 */
function shop_nearMultiple($num, $k){
    //$tmp = $num % $k;
    $tmp = fmod($num, $k);
    $min = $num - $tmp;
    $max = $min + $k;

    if (abs($num - $min) < abs($max - $num)) return $min;

    return $max;
}

/**
 * Рендерит настройки плагинов
 */
function shop_renderPlgConfig($plg, $plgConfig, $t, $block = 'MAIN.EDIT'){
    global $cfg, $L;
    
    if (file_exists(cot_langfile($plg))){
        require_once cot_langfile($plg);
    }
    $dir = $cfg['plugins_dir'];
    $setup_file = $dir . '/' . $plg . '/' . $plg . '.setup.php';
    $info = false;
    if (file_exists($setup_file)) $info = cot_infoget($setup_file, 'COT_EXT');
    if (!$info) return false;

    // Plugin Config
    $info_cfg = cot_infoget($setup_file, 'COT_PLG_CONFIG');
    $options = cot_config_parse($info_cfg);
    foreach ($options as $key => $row){
		$config_owner = 'plug';
		$config_cat = $plg;
//		$config_subcat = $row['config_subcat'];
		$config_name = $row['name'];
        $config_default = $row['default'];
        // если не существует такой опуии, то брать дефолтную
        if(!isset($plgConfig[$config_name]) && !empty($config_default)){
            // Ищем нужную опцию
            $config_value = $config_default;
        }else{
            $config_value = $plgConfig[$config_name];
        }
		$config_type = $row['type'];
        $config_title = $L['cfg_'.$config_name][0];
		$config_text = htmlspecialchars($row['text']);
		$config_more = $L['cfg_'.$config_name][1];
        // TODO  FIX it
        if ($config_subcat == '__default' && $prev_subcat == '' && $config_type != COT_CONFIG_TYPE_SEPARATOR){
				if ($inside_fieldset){
					// Close previous fieldset
					$t->parse($block.'.CONFIG_FIELDSET_END');
				}
				$inside_fieldset = true;
				$t->assign('CONFIG_FIELDSET_TITLE', $L['cfg_struct_defaults']);
				$t->parse($block.'.CONFIG_ROW.CONFIG_FIELDSET_BEGIN');
	    }
					
			if ($config_type == COT_CONFIG_TYPE_STRING){
				$config_input = cot_inputbox('text', $config_name, $config_value);
                
			}elseif ($config_type == COT_CONFIG_TYPE_SELECT){
				if (!empty($row['variants'])){
					$cfg_params = explode(',', $row['variants']);
                    $cfg_params_titles = cot_admin_config_get_titles($config_name, $cfg_params);
				}
                $config_input = (is_array($cfg_params))
                    ? cot_selectbox($config_value, $config_name, $cfg_params, $cfg_params_titles, false)
                    : cot_inputbox('text', $config_name, $config_value);
			}
			elseif ($config_type == COT_CONFIG_TYPE_RADIO){
                $config_input = cot_radiobox($config_value, $config_name, array(1, 0), array($L['Yes'], $L['No']), '', ' ');

			}elseif ($config_type == COT_CONFIG_TYPE_CALLBACK){
                // Preload module/plugin functions
                if (file_exists(cot_incfile($config_cat, $config_owner)))
                {
                    require_once cot_incfile($config_cat, $config_owner);
                }
                if ((preg_match('#^(\w+)\((.*?)\)$#', $row['variants'], $mt) && function_exists($mt[1])))
                {
                    $callback_params = preg_split('#\s*,\s*#', $mt[2]);
                    if (count($callback_params) > 0 && !empty($callback_params[0]))
                    {
                        for ($i = 0; $i < count($callback_params); $i++)
                        {
                            $callback_params[$i] = str_replace("'", '', $callback_params[$i]);
                            $callback_params[$i] = str_replace('"', '', $callback_params[$i]);
                        }
                        $cfg_params = call_user_func_array($mt[1], $callback_params);
                    }
                    else
                    {
                        $cfg_params = call_user_func($mt[1]);
                    }
                    $cfg_params_titles = cot_admin_config_get_titles($config_name, $cfg_params);
                    $config_input = cot_selectbox($config_value, $config_name, $cfg_params, $cfg_params_titles, false);
                }
                else{
                    $config_input = '';
                }
            
			}elseif ($config_type == COT_CONFIG_TYPE_HIDDEN){
				continue;

			}elseif ($config_type == COT_CONFIG_TYPE_SEPARATOR){
                if ($inside_fieldset){
                    // Close previous fieldset
                    $t->parse($block.'.CONFIG_ROW.CONFIG_FIELDSET_END');
                }
                $inside_fieldset = true;

			}elseif ($config_type == COT_CONFIG_TYPE_RANGE){
                $range_params = preg_split('#\s*,\s*#', $row['variants']);
                $cfg_params = count($range_params) == 3 ? range($range_params[0], $range_params[1], $range_params[2])
                    : range($range_params[0], $range_params[1]);
                $config_input = cot_selectbox($config_value, $config_name, $cfg_params, $cfg_params, false);

            }elseif ($config_type == COT_CONFIG_TYPE_CUSTOM){
                // Preload module/plugin functions
                if (file_exists(cot_incfile($config_cat, $config_owner)))
                {
                    require_once cot_incfile($config_cat, $config_owner);
                }
                if ((preg_match('#^(\w+)\((.*?)\)$#', $row['variants'], $mt) && function_exists($mt[1])))
                {
                    $callback_params = preg_split('#\s*,\s*#', $mt[2]);
                    if (count($callback_params) > 0 && !empty($callback_params[0]))
                    {
                        for ($i = 0; $i < count($callback_params); $i++)
                        {
                            $callback_params[$i] = str_replace("'", '', $callback_params[$i]);
                            $callback_params[$i] = str_replace('"', '', $callback_params[$i]);
                        }
                        $config_input = call_user_func_array($mt[1],
                            array_merge(array($config_name, $config_value), $callback_params));
                    }
                    else
                    {
                        $config_input = call_user_func_array($mt[1], array($config_name, $config_value));
                    }
                }
                else
                {
                    $config_input = '';
                }

			}else{
                $config_input = cot_textarea($config_name, $config_value, 8, 56);
			}

            //var_dump($row);
			if ($config_type == COT_CONFIG_TYPE_SEPARATOR){
                $cfg_title = is_array($L['cfg_' . $row['name']]) ? $L['cfg_' . $row['name']][0] : $L['cfg_' . $row['name']];
                $t->assign('CONFIG_FIELDSET_TITLE', $cfg_title);
                $t->parse($block.'.CONFIG_ROW.CONFIG_FIELDSET_BEGIN');
			}else{
				$t->assign(array(
					'CONFIG_ROW_CONFIG' => $config_input,
					'CONFIG_ROW_CONFIG_TITLE' => (empty($config_title) && !empty($config_text))
                        ? $config_text : $config_title,
					'CONFIG_ROW_CONFIG_MORE_URL' =>
						cot_url('admin', "m=config&n=edit&o=$o&p=$p&a=reset&v=$config_name&sub=$sub"),
					'CONFIG_ROW_CONFIG_MORE' => $config_more
				));
				/* === Hook - Part2 : Include === */
//				foreach ($extp as $pl)
//				{
//					include $pl;
//				}
				/* ===== */
				$t->parse($block.'.CONFIG_ROW.CONFIG_ROW_OPTION');
			}
			$t->parse($block.'.CONFIG_ROW');
			
			$prev_subcat = $config_subcat;
    } // foreach ($options as $key => $row){

    if ($inside_fieldset){
        // Close the last fieldset
        $t->parse($block.'.ADMIN_CONFIG_ROW.ADMIN_CONFIG_FIELDSET_END');
        $t->parse($block.'.ADMIN_CONFIG_ROW');
    }
    /* === Hook  === */
    foreach (cot_getextplugins('shop.plugin.config.tags') as $pl)
    {
        include $pl;
    }
    /* ===== */
    $t->parse($block);
    //var_dump($options);

    //Получить структуру конфига плагина
}

/**
 * Импорт настроек плагина
 * @param type $plg - plugin code
 * @param type $src - SRC: 'P' - POST, 'G' - GET , etc. see cot_import()
 * @return boolean|array 
 * @todo контроль типов, проверка значений на безопасность
 */
function shop_importPlgConig($plg, $src = 'P'){
    global $cfg, $L;
    
    if (file_exists(cot_langfile($plg))){
        require_once cot_langfile($plg);
    }
    $dir = $cfg['plugins_dir'];
    $setup_file = $dir . '/' . $plg . '/' . $plg . '.setup.php';
    $info = false;
    if (file_exists($setup_file)) $info = cot_infoget($setup_file, 'COT_EXT');
    if (!$info) return false;
    
    // Plugin Config
    $info_cfg = cot_infoget($setup_file, 'COT_PLG_CONFIG');
    $options = cot_config_parse($info_cfg);
    $ret = array();
    foreach ($options as $key => $row){
        if($row['type'] == COT_CONFIG_TYPE_SEPARATOR) continue;
        $ret[$row['name']] = cot_import($row['name'], $src, 'NOC');
        if (!is_array($ret[$row['name']])) $ret[$row['name']] = trim($ret[$row['name']]);
    }

    return $ret;
}

/**
 * Обертка для selectbox_countries т.к. в настройках неудается передать параметром массив
 * @param type $name - имя элемента SELECT
 * @param type $chosen - Выбранный элемент или массив выбранных элементов
 * @param bool $add_empty - добавить пустой
 * @param bool|\type $multiple - Мульти ?
 * @param int $size
 * @return string HTML код элемента SELECT
 */
function shop_selectbox_countries($name, $chosen,  $add_empty = true, $multiple = false, $size = 0){
    
    if (mb_strtolower($add_empty) == 'false') $add_empty = false;
    if (mb_strtolower($multiple) == 'false') $multiple = false;
    $attrs = array('placeholder' => 'Select...');
    if ($multiple){
        $attrs['multiple'] = 'multiple';
        if (mb_strpos('[]', $name) === false) $name .= '[]';
    }
    if($size > 0) $attrs['size'] = $size;

    return cot_selectbox_countries($chosen, $name, $add_empty, $attrs);
}

/**
 * Выбор валюты из всех возможных
 * @param type $name - имя элемента SELECT
 * @param type $chosen - Выбранный элемент или массив выбранных элементов
 * @param bool $add_empty - добавить пустой
 * @param bool|\type $multiple - Мульти ?
 * @param int $size
 * @return string HTML код элемента SELECT
 */
function shop_selectbox_currency($name, $chosen,  $add_empty = true, $multiple = false, $size = 0){

    if (mb_strtolower($add_empty) == 'false') $add_empty = false;
    if (mb_strtolower($multiple) == 'false') $multiple = false;
    $attrs = array('placeholder' => 'Select...');
    if ($multiple){
        $attrs['multiple'] = 'multiple';
        if (mb_strpos('[]', $name) === false) $name .= '[]';
    }
    if($size > 0) $attrs['size'] = $size;

    $items = Currency::getKeyValPairsList();

    return cot_selectbox($chosen, $name, array_keys($items), array_values($items), $add_empty, $attrs);
}

/**
 * Выбор статуса заказа из всех возможных
 * @param type $name - имя элемента SELECT
 * @param type $chosen - Выбранный элемент или массив выбранных элементов
 * @param bool $add_empty - добавить пустой
 * @param bool|\type $multiple - Мульти ?
 * @param int $size
 * @return string HTML код элемента SELECT
 */
function shop_selectbox_orderStatus($name, $chosen,  $add_empty = true, $multiple = false, $size = 0){

    if (mb_strtolower($add_empty) == 'false') $add_empty = false;
    if (mb_strtolower($multiple) == 'false') $multiple = false;
    $attrs = array('placeholder' => 'Select...');
    if ($multiple){
        $attrs['multiple'] = 'multiple';
        if (mb_strpos('[]', $name) === false) $name .= '[]';
    }
    if($size > 0) $attrs['size'] = $size;

    $items = OrderStatus::getKeyValPairsList();

    return cot_selectbox($chosen, $name, array_keys($items), array_values($items), $add_empty, $attrs);
}


/**
 * Select box валюты, принимаемые продавцом 
 */
function shop_selectbox_acceptedCurrency($name){
    global $L;
    
    $L['cfg_'.$name.'_params'][0] = $L['shop']['default_vendor_currency'];
    $ret = array(0);
    return $ret;
}

function shop_selectbox_taxes($name, $chosen, $attrs = array()){
    global $cfg, $L;
    // Список налогов
    if(!class_exists('Calc')) require_once($cfg['modules_dir'].DS.'shop'.DS.'models'.DS.'calc.php');
    $taxes = Calc::getTaxes();
    $taxRates = array(
        '-1' => $L['shop']['product_tax_none'],
        '0'  => $L['shop']['product_tax_no_special']
    );
    if(!empty($taxes)){
        foreach ($taxes as $tax) {
            $taxRates[$tax->calc_id] = $tax->calc_title;
        }
    }
    return cot_selectbox($chosen, $name, array_keys($taxRates), array_values($taxRates), false, $attrs);
}

function shop_getWeightUnits(){
    global $shop_weightUnit;

    return $shop_weightUnit;
}

/**
 * Получить список конвертеров валют
 * @todo плагины конвертеров
 * @return array
 */
function shop_getCurrencyConverters(){
    global $cfg;
    // Получить список файлов конвертеров
    $converters = array();
    $files = shop_readDirectory($cfg['modules_dir'].DS.'shop'.DS.'inc'.DS.'currency_converter');
    foreach($files as $file){
        $path_info = pathinfo($file);
        if($path_info['extension'] == 'php' && $path_info['filename'] != 'interface') $converters[] = $path_info['filename'];
    }
    return $converters;
}

/**
 * Получить список файлов в папке
 * @param string $dir
 * @return array
 */
function shop_readDirectory ($dir){

    if ( $dir [ mb_strlen($dir)-1 ] != '/' )  $dir .= '/'; //добавляем слеш в конец если его нет
    $nDir = opendir( $dir );

    while ( false !== ( $file = readdir( $nDir ) ) ){
        if ( $file != "." && $file != ".." ){
            if ( !is_dir( $dir . $file ) ) {
                //если это не директория
                $files[] = $file;
            }
        }
    }

    closedir( $nDir );

    return $files;
}

/**
 * Виджет миникорзины
 * @param string $tpl
 * @param bool $cacheitem
 * @return string
 */
function minicart($tpl = 'shop.minicart', $cacheitem = true){
    global $cfg, $L, $m;

    $minicart = '';
    static $stCache = false;

    if($stCache !== false){
        return $stCache;
    }

    // на страницах редактирования/добавления не отображаем корзину
    if (!defined('COT_ADMIN') && (!defined('COT_AJAX') || !COT_AJAX ) && !in_array($m, array('add', 'edit')) ){

        // выводим мини корзину
        if ($cfg["shop"]['mCartOnShopOnly'] == 0 || ($cfg["shop"]['mCartOnShopOnly']==1 && isShop())){

            $cart = ShopCart::getInstance(false,false);
            $miniCartData = $cart->prepareAjaxData();

            if ($miniCartData->totalProduct > 1){
                $miniCartData->totalProductTxt = sprintf($L['shop']['cart_x_products'], $miniCartData->totalProduct);
            }else if ($miniCartData->totalProduct == 1){
                $miniCartData->totalProductTxt = $L['shop']['cart_one_product'];
            }else{
                $miniCartData->totalProductTxt = $L['shop']['cart_empty_cart'];
            }

            // Даже если данные проверены, все равно с миникорзины уходим в большую
            $taskRoute = '';
            $linkName = $L['shop']['cart_show'];

            if(!$tpl) $tpl = 'shop.minicart';
            $tpl = new XTemplate(cot_tplfile($tpl));

            $tpl->assign(array(
                'TOTAL_PRODUCT' => $miniCartData->totalProduct,
                'TOTAL_PRODUCT_TXT' => $miniCartData->totalProductTxt,
                'BILL_TOTAL' => $miniCartData->billTotal,
                'SHOW_CART' => cot_rc('shop_minicart_showcart', array(
                            'url' => cot_url('shop', 'm=cart'.$taskRoute),
                            'text' => $linkName
                        )),
            ));
            if($cfg['shop']['mCartShowProdList'] == 1){

                foreach ($miniCartData->products as $product){
                    $product['attributes'] = (isset($product['attributes'])) ? $product['attributes'] : '';
                    $tpl->assign(array(
                        'ROW_PRICE' => $product['prices'],
                        'ROW_QUANTITY' => $product['quantity'],
                        'ROW_TITLE' => $product['page_title'],
                        'ROW_NAME' => $product['product_name'],
                        'ROW_URL' => $product['url'],
                        'ROW_ATTRIBUTES' => $product['attributes'],
                    ));
                    $tpl->parse('MAIN.LIST.ROW');
                }
                $tpl->parse('MAIN.LIST');
            }

            $tpl->parse('MAIN');
            $minicart = $tpl->text('MAIN');
        }
        // /выводим мини корзину

    }
    if($cacheitem){
        $stCache = $minicart;
    }

    return ($minicart);
}


if(!function_exists('cot_admin_config_get_titles') && !($env["location"] == "administration" && $m == 'config') ){
    /**
     * Helper function that generates selection titles.
     * @param  string $config_name Current config name
     * @param  array  $cfg_params  Array of config params
     * @return array               Selection titles
     */
    function cot_admin_config_get_titles($config_name, $cfg_params)
    {
        global $L;
        if (isset($L['cfg_'.$config_name.'_params']))
        {
            if (!is_array($L['cfg_'.$config_name.'_params']))
            {
                $L['cfg_'.$config_name.'_params'] = preg_split('#\s*,\s*#', $L['cfg_'.$config_name.'_params']);
                if (preg_match('#^[\w-]+\s*:#', $L['cfg_'.$config_name.'_params'][0]))
                {
                    // Support for assoc arrays
                    $temp = array();
                    foreach ($L['cfg_'.$config_name.'_params'] as $item)
                    {
                        if (preg_match('#^([\w-]+)\s*:\s*(.*)$#', $item, $mt))
                        {
                            $temp[$mt[1]] = $mt[2];
                        }
                    }
                    if (count($temp) > 0)
                        $L['cfg_'.$config_name.'_params'] = $temp;
                }
            }
            $lang_params_keys = array_keys($L['cfg_'.$config_name.'_params']);
            if (is_numeric($lang_params_keys[0]))
            {
                // Numeric array, simply use it
                $cfg_params_titles = $L['cfg_'.$config_name.'_params'];
            }
            else
            {
                // Associative, match entries
                $cfg_params_titles = array();
                foreach ($cfg_params as $val)
                {
                    if (isset($L['cfg_'.$config_name.'_params'][$val]))
                    {
                        $cfg_params_titles[] = $L['cfg_'.$config_name.'_params'][$val];
                    }
                    else
                    {
                        $cfg_params_titles[] = $val;
                    }
                }
            }
        }
        else
        {
            $cfg_params_titles = $cfg_params;
        }
        return $cfg_params_titles;
    }
}
spl_autoload_register('shopAutoLoader');