<?php
defined('COT_CODE') or die('Wrong URL.');
/**
 * Model class for User Info
 * @package shop
 * @subpackage Users
 *
 * @property int $oui_id;
 * @property int $order_id;
 * @property int $user_id;
 * @property int $ui_id
 * @property string	$oui_address_type
 * @property string	$oui_address_type_title
 * @property bool   $agreed
 * @property string	$oui_email
 * @property string	$oui_lastname
 * @property string	$oui_firstname
 * @property string	$oui_middlename
 * @property string	$oui_zip            Почтовый индекс
 * @property string	$oui_country
 * @property int	$oui_region
 * @property string	$oui_phone
 *
 * @method static OrderUserInfo getById(int $pk)
 * @method static OrderUserInfo[] getList(int $limit = 0, int $offset = 0, string $order = '')
 * @method static OrderUserInfo[] find(mixed $conditions, int $limit = 0, int $offset = 0, string $order = '')
 */
class OrderUserInfo extends ShopModelAbstract{

    /**
     * SQL table name
     * Fatal error: Access level to Currency::$_table_name must be public (as in class ShopModelAbstract) in
     *   .../modules/shop/models/Currency.php on line 17
     * @var string
     */
    public static $_table_name = '';

    /**
     * @var string
     */
    public static $_primary_key = '';

    /**
     * Column definitions
     * @var array
     */
    public static $_columns = array();

    /**
     * Static constructor
     */
    public static function __init(){
        global $db_shop_order_userinfo;

        self::$_table_name = $db_shop_order_userinfo;
        self::$_primary_key = 'oui_id';
        parent::__init();
    }


    /**
     * @param ModelAbstract|array $data
     * @throws Exception
     */
    public function setData($data){
        global $cfg;

        $class = get_class($this);
        $columns = array();
        eval('$columns = '.$class.'::$_columns;');

        if(is_array($data)){
            $getFromUserProfile = false;
            foreach($data as $key => $value){
                if(mb_strpos($key, 'user_') !== false && $key != 'user_id') {
                    // Данные пришли из профиля пользователя
                    $getFromUserProfile = true;
                    $key = str_replace('user_', '', $key);
                }
                if(!in_array($key, $columns) && mb_strpos($key, 'oui_') === false) $key = 'oui_'.$key;
                $this->__set($key, $value);
            }

            if($getFromUserProfile){

                // принять данные согласно настроек экстраполей пользователя
                foreach($columns as $column){
                    if(in_array($column,
                        array('oui_id', "order_id", "oui_created_on", "address_type", "oui_created_by", "oui_updated_on",
                            "oui_updated_by" ))) continue;

                    $column = str_replace('oui_', '', $column);

                    if(isset($cfg['shop']["uextf_{$column}"])){
                        $key = 'oui_'.$column;
                        $this->__set($key, $data['user_'.$cfg['shop']["uextf_{$column}"]]);

                    };
                    $this->_data['ui_id'] = 0;
                    $this->_data['user_id'] = $data['user_id'];
                    $this->_data['agreed']  = $data['user_agreed'];
                }

            }
            return;
        }

        // Принимаем данные от объекта UserInfo
        if ($data instanceof UserInfo){
            $data = $data->toArray();

            foreach($data as $key => $value){
                if(mb_strpos($key, 'ui_') !== false && $key != 'user_id' && $key != 'ui_id') {
                    $key = str_replace('ui_', '', $key);
                    $key = 'oui_'.$key;
                }
                $this->__set($key, $value);
            }

            $this->_data['oui_address_type'] = $data['ui_address_type'];
            return;
        }

        if ($data instanceof $class) $data = $data->toArray();
        if (!is_array($data)){
            throw new  Exception("Data must be an Array or instance of $class Class");
        }
        foreach($data as $key => $value){
            $this->__set($key, $value);
        }
    }

    /**
     * Проверка на присутствие всех необходимых полей
     */
    public function validate(){
        global $L;

        // Получить обязательные экстраполя в зависимости от типа BT/ST и проеверить что все заполнено
        $neededFields = $this->getRequiredFields();

        $type = $this->_data['oui_address_type'];
        if(!$type) $type = 'BT';

        $redirectMsg = '';
        $i = 0;
        foreach ($neededFields as $field) {
            // Галочку "Я согласен с условиями обслуживания пропускаем"
            if ($field == 'agreed') continue;

            if(empty($this->$field)){
                // TODO заполнить название поля (localize me ))) )
                $redirectMsg = sprintf($L['shop']['missing_value_for_field'], $field); // временно
                $i++;
                if($i > 2 && $type=='BT'){
                    $redirectMsg = $L['shop']['checkout_please_enter_address'];
                }
            }
        }

        if($i == 0) return true;
        return $redirectMsg;
    }

    /**
     * Получить обязательные поля
     */
    public function getRequiredFields(){
        global $cfg, $cot_extrafields, $db_shop_shop_userinfo;

        $class = get_class($this);

        $type = $this->_data['oui_address_type'];
        if(!$type) $type = 'BT';

        $columns = array();
        eval('$columns = '.$class.'::$_columns;');

        foreach($columns as $key => $column){
            if(in_array($column,
                array('oui_id', "order_id", "oui_created_on", "address_type", "oui_created_by", "oui_updated_on",
                    "oui_updated_by" ))) unset($columns[$key]);
        }

        if($type == 'BT'){
            $neededFields = $cfg["shop"]['user_fields'][$type];

            foreach($columns as $key => $column){

                $column = str_replace('oui_', '', $column);

                if(isset($cfg['shop']["uextf_{$column}"])){
                    $field = $cfg['shop']["uextf_{$column}"];
                }else{
                    $field = $column;
                }
                $need = false;
                foreach($neededFields as $fld){
                    if($fld['field_name'] == $field && $fld['field_required'] == 1) {
                        $need = true;
                        break;
                    }
                }
                if(!$need) unset($columns[$key]);
            }
            return($columns);
        }

        if($type == 'ST'){
            foreach($columns as $key => $column){
                if(in_array($column,
                    array('oui_id', "order_id", "oui_created_on", "address_type", "oui_created_by", "oui_updated_on",
                        "oui_updated_by" ))) unset($columns[$key]);

                $column = str_replace('oui_', '', $column);

                $need = false;
                if(isset($cot_extrafields[$db_shop_shop_userinfo][$column])
                        && $cot_extrafields[$db_shop_shop_userinfo][$column]['field_required'] == 1) {
                    $need = true;
                }
                if(!$need) unset($columns[$key]);
            }

            return($columns);
        }

        return array();
    }

    /**
     * Save data
     * @return int id of saved record
     */
    public function save($data = null){

        $itemId = $this->_data['oi_id'];
        if(empty($this->oui_address_type_title)){
            if($this->oui_address_type == 'BT'){
                $this->oui_address_type_title = 'Bill to';
            }elseif($this->oui_address_type == 'ST'){
                $this->oui_address_type_title = 'Ship to';
            }
        }

        $id = parent::save();

        if($id){
            if(!$itemId['ui_id']) {
                cot_log("Added new user adress # {$id}",'adm');
            }else{
                cot_log("Edited user adress # {$id}",'adm');
            }
        }
        return $id;
    }


    // === Методы для работы с шаблонами ===
    /**
     * Returns all UserInfo tags for coTemplate
     *
     * @param UserInfo|int $item UserInfo object or ID
     * @param string $tagPrefix Prefix for tags
     * @param bool $cacheitem Cache tags
     * @return array|void
     */
    public static function generateTags($item, $tagPrefix = '', $cacheitem = true){
        global $cfg, $L;

        static $extp_first = null, $extp_main = null;
        static $userinfo_cache = array();

        if (is_null($extp_first)){
            $extp_first = cot_getextplugins('shop.userinfo.tags.first');
            $extp_main  = cot_getextplugins('shop.userinfo.tags.main');
        }

        /* === Hook === */
        foreach ($extp_first as $pl){
            include $pl;
        }
        /* ===== */

        if ( ($item instanceof UserInfo) && is_array($userinfo_cache[$item->ui_id]) ) {
            $temp_array = $userinfo_cache[$item->ui_id];
        }elseif (is_int($item) && is_array($userinfo_cache[$item])){
            $temp_array = $userinfo_cache[$item];
        }else{
            if (is_int($item) && $item > 0){
                $item = self::getById($item);
                if(!$item) return false;
            }
            /** @var UserInfo $item  */
            if ($item->ui_id > 0){
                $item_link = cot_url('shop', array('m'=>'user', 'a'=>'editaddress', 'addrtype' => 'ST', 'uiid'=>$item->ui_id));
                $date_format = 'datetime_medium';
                $temp_array = array(
                    'EDTI_URL' => $item_link,
                    'ID' => $item->ui_id,
                    'TITLE' => htmlspecialchars($item->ui_title),
                    'CREATE_DATE' => cot_date($date_format, strtotime($item->ui_created_on)),
                    'MODIFY_DATE' => cot_date($date_format, strtotime($item->ui_updated_on)),
                    'DELETE_URL' => cot_confirm_url(cot_url('admin', 'm=shop&n=currency&a=delete&id='.$item->curr_id.'&'.cot_xg()), 'admin'),
                );

                // Extrafields
//                if (isset($cot_extrafields[$db_pages])){
//                    foreach ($cot_extrafields[$db_pages] as $row) {
//                        $tag = mb_strtoupper($row['field_name']);
//                        $temp_array[$tag.'_TITLE'] = isset($L['page_'.$row['field_name'].'_title']) ?  $L['page_'.$row['field_name'].'_title'] : $row['field_description'];
//                        $temp_array[$tag] = cot_build_extrafields_data('page', $row, $order["page_{$row['field_name']}"], $order['page_parser']);
//                    }
//                }

                /* === Hook === */
                foreach ($extp_main as $pl)
                {
                    include $pl;
                }
                /* ===== */
                $cacheitem && $userinfo_cache[$item->ui_id] = $temp_array;
            }else{
                // Нет записи
            }
        }
        $return_array = array();
        foreach ($temp_array as $key => $val){
            $return_array[$tagPrefix . $key] = $val;
        }

        return $return_array;
    }

}
// Class initialization for some static variables
OrderUserInfo::__init();