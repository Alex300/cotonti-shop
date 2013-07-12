<?php
/**
 * Model class for the userfields
 * реквизиты вынесены в отдельную таблицу, по крайней мере реквизиты доставки (ST)
 * 
 * @package shop
 * @subpackage user
 *
 */
defined('COT_CODE') or die('Wrong URL.');

/**
 * Model class for the userfields
 * Поля заполняемые пользователем, спецефичные для магазина (реквизиты, адреса доставки и т.п.)
 * @package shop
 * @subpackage user
 */
class Userfields {

    /**
     * Получить поля пользователя, используемые для регистрации в системе (только необходимые)
     * @return array поля 
     */
    public static function getCoreFields(){
        global $cfg, $cot_extrafields, $db_users, $L;
        
        $fields = array(
            'name' => array(
                    'field_name' => 'name', 'field_required' => true
                ),
            'email'=> array(
                    'field_name' => 'email', 'field_required' => true, 'field_type' => 'input'
                ),
            'password' => array('field_name' => 'password', 'field_required' => true),
            'password2' => array('field_name' => 'password2', 'field_required' => true),
            'country' => array('field_name' => 'country', 'field_required' => false, 'field_type' => 'country'),
         );
        foreach($cot_extrafields[$db_users] as $key => $val){
            if($val['field_required'] && $val["field_enabled"]) {
                //$fields[$val['field_name']] = array('field_name' => $val['field_name'], 'field_required' => $val['field_required']);
                $fields[$val['field_name']] = $val;
            }
        }
        return $fields;
    }
    
    /**
     * Получить поля пользователя по его данным
     *
     * @param OrderUserInfo|array|int $userData массив с данными пользователя или его id
     * @param array|string $type массив требуемых полей или тип: BT, ST
     * @param string $tagPrefix
     * @param string $dataPrefix
     * @return array поля для редактирования 
     */
    public static function getFieldsData($userData, $type = 'BT', $tagPrefix = '', $dataPrefix = ''){
        global $db_users, $cfg, $cot_extrafields, $L, $cot_countries, $db;

        if ($userData instanceof UserInfo) $userData = $userData->toArray();
        if ($userData instanceof OrderUserInfo) {
            $tmp = $userData->toArray();
            $userData = array();
            foreach($tmp as $key => $value){
                $key = str_replace('oui_', '', $key);
                $userData[$key] = $value;
            }
        }

        if (!is_array($userData) && $type = 'BT'){
			$sql = $db->query("SELECT * FROM $db_users WHERE user_id = '" . (int) $userData . "' LIMIT 1");
			$userData = $sql->fetch();
            if (!$userData) return false;
		}
        if (!isset($userData[$dataPrefix.'id'])) $userData[$dataPrefix.'id'] = 0;
        //$protected = (!(bool)$cfg['users']['useremailchange'] && $userData[$dataPrefix.'id'] > 0) ? array('disabled' => 'disabled') : array();
        $protected = (!(bool)$cfg['users']['useremailchange'] && $userData[$dataPrefix.'id'] > 0) ? array('readonly' => 'readonly') : array();
        $profile_form_email = cot_inputbox('text', $tagPrefix.'email', $userData[$dataPrefix.'email'],
                array('size' => 32, 'maxlength' => 64) + $protected);
        $coreFields = array(
            'email' => array('field_editCode' => $profile_form_email),
            'country' => array(
                'field_editCode' => cot_selectbox_countries($userData[$dataPrefix.'country'], $tagPrefix.'country'),
                'field_val' => $cot_countries[$userData[$dataPrefix.'country']]
             ),
            'name' => array('field_editCode' => cot_inputbox('text', $tagPrefix.'name', $userData['user_name'],
                                                        array('size' => 24, 'maxlength' => 100)) ),
            'password' => array('field_editCode' => cot_inputbox('password', $tagPrefix.'password', '', 
                                            array('size' => 8, 'maxlength' => 32)) ),
            'password2' => array(
                'field_editCode' => cot_inputbox('password', $tagPrefix.'password2', '', 
                                            array('size' => 8, 'maxlength' => 32)),
                'field_title' => $L['users_confirmpass']
            ),
        );
        $fields = array();
        if(is_array($type)){
            $uFieids = $type;
        }else{
            $uFieids = self::getUserFields($type);
        }

        foreach ($uFieids as $fld){
            if (is_string($fld)) $fld = array('field_name' => $fld);
            $la = mb_convert_case($fld['field_name'], MB_CASE_TITLE);
            $title = isset($L[$la]) ? $L[$la] : '';
            if ($title == ''){
                $title = isset($L['user_' . $fld['field_name'] . '_title']) ? 
                    $L['user_' . $fld['field_name'] . '_title'] : 
                    $cot_extrafields[$db_users][$fld['field_name']]['field_description'];
            }
            if ($type != 'ST' && array_key_exists($fld['field_name'], $coreFields)){
                $editCode = $coreFields[$fld['field_name']]['field_editCode'];
                $value = $userData[$dataPrefix.$fld['field_name']];
                $val = (!empty($coreFields[$fld['field_name']]['field_val'])) ? $coreFields[$fld['field_name']]['field_val'] :
                        $value;
                if (!empty($coreFields[$fld['field_name']]['field_title'])) $title = $coreFields[$fld['field_name']]['field_title'];
            }else{

                $value = $userData[$dataPrefix.$fld['field_name']];
                $val = cot_build_extrafields_data('', $fld, $userData[$dataPrefix.$fld['field_name']]);
                 $editCode = cot_build_extrafields($tagPrefix.$fld['field_name'], $fld, $value);
            }
            if($fld['field_type'] == 'country'){
                $val = (!empty($value) && $value != '00') ? $cot_countries[$value] : null;
            }
            // field_val - там значение для вывода
            $fields[$fld['field_name']] = array(
                'field_name' => $fld['field_name'],
                'field_required' => $fld['field_required'],
                'field_title' => $title,
                'field_editCode' => $editCode,
                'field_value' => $value,
                'field_val' => $val,
            );
        }

        if(cot_plugin_active('regioncity')){
            // Подменим код для редактирования
            $regionFld = $cfg['shop']['uextf_region'];
            $cityFld = $cfg['shop']['uextf_city'];
            $regionNameFld = $cfg['shop']['uextf_region_name'];
            $cityNameFld = $cfg['shop']['uextf_city_name'];

            // Тут не важно $regionFld проверять или $regionNameFld т.к. они оба в любом случае
            if(array_key_exists($regionNameFld, $fields) || array_key_exists($cityNameFld, $fields) ){

                $selLoc = rec_select_location(
                    array('ruserfcountry', 'country'),
                    array("ruserf{$regionFld}", $regionFld),
                    array("ruserf{$cityFld}",   $cityFld),
                    $fields['country']['field_value'], $fields[$regionFld]['field_value'], $fields[$cityFld]['field_value']);

                if(!empty($fields['country'])){
                    $fields['country']['field_editCode'] = $selLoc['country'];
                }

                foreach($fields as $key => $fld){
                    // Подменяем оба поля
                    if(in_array($key, array($regionFld, $regionNameFld))){
                        $fields[$key]['field_editCode'] = '<span>'.$selLoc[$regionFld].'</span>';
                    }elseif(in_array($key, array($cityFld, $cityNameFld))){
                        $fields[$key]['field_editCode'] = '<span>'.$selLoc[$cityFld].'</span>';
                    }
                }
            }
        }

        return $fields;
    }
    
    /**
     * Получить поля пользователя
     * @param string $type 'BT', 'ST'
     * @return array Массив полей пользователя
     *
     */
    public static function getUserFields($type = 'BT'){
        global $cfg, $db_users, $cot_extrafields, $db_shop_shop_userinfo, $usr;

        $ret = array();
        if ($type == 'BT'){
            $fields = $cfg["shop"]['user_fields'][$type];
            $coreFields = self::getCoreFields();
        }else{
            $fields = array();
            if ($usr['id'] > 0){
                $fields['title'] = array(
                    'field_name' => 'title', 'field_required' => true, 'field_type' => 'input'
                );
            }
            // TODO отсортировать порядок полей чем то типа asort
            if(!empty($cot_extrafields[$db_shop_shop_userinfo])){
                $fields = $fields + $cot_extrafields[$db_shop_shop_userinfo];
            }
            return Userfields::checkRegionCityFields($fields);
        }

        // ST уже вернули, далее обработака только для BT

        foreach($fields as $fld){
            if (isset($cot_extrafields[$db_users][$fld['field_name']])){
                $ret[$fld['field_name']] = $cot_extrafields[$db_users][$fld['field_name']];
            }elseif($type == 'BT' && isset($coreFields[$fld['field_name']])){
                $ret[$fld['field_name']] = $coreFields[$fld['field_name']];
            }
            // Поле будет обязательное, если оно обязательное в настройках экстраполей или магазина
            $ret[$fld['field_name']]['field_required'] = (int)((bool)$ret[$fld['field_name']]['field_required'] ||
                (bool)$fld['field_required']);
        }
        return Userfields::checkRegionCityFields($ret);
    }

    /**
     * Получить массив экстраполей пользователя ($db_users) по умолчанию
     * @return array
     */
    public static function getShopDefaultUserExtrafields(){
        global $cfg;
        $extFields = parse_ini_file($cfg['modules_dir'].DS.'shop'.DS.'setup'.DS.'user_def_extrafields.ini', true);

        return $extFields;
    }

    protected static function checkRegionCityFields($fields){
        global $cfg;

        if(cot_plugin_active('regioncity')){
            // Убедимся, что в массиве обязательно присутствуют пары
            // region_id - region_name и city_id - city_name

            $regionFld = $cfg['shop']['uextf_region'];
            $cityFld = $cfg['shop']['uextf_city'];
            $regionNameFld = $cfg['shop']['uextf_region_name'];
            $cityNameFld = $cfg['shop']['uextf_city_name'];

            if(array_key_exists($regionFld, $fields) || array_key_exists($regionNameFld, $fields) ){
                if(!array_key_exists($regionFld, $fields)){
                    $fields[$regionFld] = array(
                        'field_name'    => $regionFld,
                        'field_required' => 0,
                        'field_type' => "inputint"
                    );
                }

                if(!array_key_exists($regionNameFld, $fields)){
                    $fields[$regionNameFld] = array(
                        'field_name'    => $regionNameFld,
                        'field_required' => 0,
                        'field_type' => "input"
                    );
                }
            }

            if(array_key_exists($cityFld, $fields) || array_key_exists($cityNameFld, $fields) ){
                if(!array_key_exists($cityFld, $fields)){
                    $fields[$cityFld] = array(
                        'field_name'    => $cityFld,
                        'field_required' => 0,
                        'field_type' => "inputint"
                    );
                }

                if(!array_key_exists($cityNameFld, $fields)){
                    $fields[$cityNameFld] = array(
                        'field_name'    => $cityNameFld,
                        'field_required' => 0,
                        'field_type' => "input"
                    );
                }
            }

        }

        return $fields;
    }
}