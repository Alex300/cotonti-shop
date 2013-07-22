<?php
defined('COT_CODE') or die('Wrong URL.');
require_once cot_incfile('forms');
require_once cot_incfile('users', 'module');

/**
 * Controller class for the user
 * 
 * @package shop
 * @subpackage user
 * Типы адресов: BT - bill-to реквизиты, в них основной адрес
 *               ST - ship-to адрес доставки (несколько) - дополнительные
 */
class UserController{

    public function __construct() {
        global $cfg;

    }
    
    /**
     * Редактировать адрес
     */
    public function editaddressAction(){
        global $L, $cfg, $out, $usr, $R, $redirect, $sys, $cot_extrafields, $db_users;
        
        $type = cot_import('addrtype', 'G', 'ALP', 2);
        if (!$type) $type = cot_import('addrtype', 'P', 'ALP', 2);
        if (!$type) $type = 'BT';
        
        $sys['sublocation'] = $L['shop']['your_account_details'];
        $out['subtitle'] = $L['shop']['your_account_details'];
        $out['canonical_uri'] = cot_url('shop', array('m'=>'cart', 'a'=>'editaddres','addrtype'=>$type));
        // Подключаем JS,CSS
        cot_rc_link_file($cfg['modules_dir'].'/shop/js/shop.user.js');
        cot_rc_link_file($cfg['modules_dir'].'/shop/tpl/shop.css');
        $L['aut_passwordmismatch'];
        $jsVars = "var Lshop = { 
                'imgUrl' : '{$cfg['modules_dir']}/shop/tpl/images',
                'aut_passwordmismatch': '{$L['aut_passwordmismatch']}'
            }  ;\n" ;
        cot_rc_embed($jsVars);
        $ui_id = 0;
        if($type == 'ST'){
            $ui_id = cot_import('uiid', 'G', 'INT');
            $title = ($ui_id > 0) ? $L['shop']['user_form_shipto_edit'] : $L['shop']['user_form_shipto_add'] ;
        }else{
            $title = ($usr['id'] > 0) ? $L['shop']['user_form_billto_edit'] : $L['shop']['user_form_billto'] ;
        }

        if ($usr['id'] > 0){
            // TODO проверить это. Может ссылклу на страницу пользователя а не на редактирование профиля. А то не
            // логично
            $crumbs = array(
                    array(cot_url('users', array('m'=>'profile')), $L['pro_title']),
                );
        }else{
            $crumbs = array(
                    array($cfg["shop"]['mainPageUrl'], $cfg["shop"]['mainPageTitle']),
                );
        }
        $crumbs[] = $title;
        $breadcrumbs = cot_breadcrumbs($crumbs, $cfg['homebreadcrumb'], true);

        $uData = array();
        $userFields = Userfields::getUserFields($type);
        $task = cot_import('task', 'P', 'ALP');

        // Cохранить данные
        if ($task == 'save' || $task == 'saveregister'){
            $data = array();
            foreach($userFields as $fld){
                $uData[$fld['field_name']] = cot_import_extrafields('ruserf'.$fld['field_name'], $fld);
            }
            if (isset($uData['email']) && !cot_check_email($uData['email'])){
                cot_error('aut_emailtooshort', 'ruserfemail'); 
            }
            if(!cot_error_found()){
                if ($ui_id > 0) $uData['id'] = $ui_id;
                $this->saveAdress($uData, $type);
            }
        
        }
        if(count($uData) == 0){
            $prefix = '';
            if ($usr['id'] == 0) {
                //New Address is filled here with the data of the cart (we are in the cart)
                $tmp = ($type == 'BT') ? 'billTo' : 'shipTo';
                $cart = ShopCart::getInstance();
                $uData = $cart->$tmp;
                if(!$uData) $uData = array();
            } else {
                if ($type == 'BT'){
                    $tmp = Userfields::getFieldsData($usr['profile'], 'BT', '', 'user_' );
                    foreach ($tmp as $fld){
                        $uData[$fld['field_name']] = $fld['field_value'];
                    }
                    $uData['id'] = $usr['profile']['user_id'];
                    $uData['country'] = $usr['profile']['user_country'];
                }else{
                    if($ui_id > 0){
                        $uData = UserInfo::find(array("ui_id={$ui_id}", "user_id={$usr['id']}"));
                        //$uData = UserInfo::find( array(array('ui_id',$ui_id), array('user_id', $usr['id'])) );
                        if (!$uData || count($uData) == 0){
                            $ui_id = 0;
                            $title = $L['shop']['user_form_shipto_add'];
                        }
                        $prefix = 'ui_';
                        $uData = $uData[0];
                    }
                }
            }
        }
        global $loginTpl;
        $loginTpl = cot_tplfile('shop.user.login_form');

        // Куда вернуться при сохранении
        $r = 'cart';
        if ($usr['id'] > 0) {
            $r = cot_import('r', 'G', 'ALP');
            if ($r != '') $r = 'cart';
        }

        $formAction = array('m'=>'user', 'a'=>'editaddress');
        if($type == 'ST'){
            $formAction['addrtype'] = 'ST';
            if ($ui_id > 0) $formAction['uiid'] = $ui_id;
        }
        if ($r != '')  $formAction['r'] = $r;

        $t = new XTemplate(cot_tplfile('shop.user.edit_adress'));

        $tmp = array();
        $i = 0;
        $fields = Userfields::getFieldsData($uData, $type, 'ruserf', $prefix);

        foreach ($fields as $fld){
            $tag = 'USERS_PROFILE_'.mb_strtoupper($fld['field_name']);
            $tmp["{$tag}_TITLE"] = $fld['field_title'];
            $tmp[$tag] = $fld['field_editCode'];
            /*
            Выводим поля в цикле. Это позволяет добавлять поля в админке без необходимости править шаблоны
             */
            $t->assign(array(
                'ROW_TITLE' => $fld['field_title'],
                'ROW_NAME' => "ruserf{$fld['field_name']}",
                'ROW_FIELD_NAME' => $fld['field_name'],
                'ROW_REQUIRED' => $fld['field_required'],
                'ROW_EDITCODE' => $fld['field_editCode'],
                'ODDEVEN' => cot_build_oddeven($i),
            ));
            $t->parse('MAIN.USER_FIELDS.ROW');
            $i++;
        }
        /*
         Выводим поля традиционным способом как все екстраполя: {COUNTRY_TITLE} {COUNTRY} 
        */
        $t->assign($tmp);
        $t->assign(array(
            'FORM_ACTION' => cot_url('shop', $formAction),
            'USER_FIELDS_ARR' => $fields
        ));
        if ($i > 0) $t->parse('MAIN.USER_FIELDS');
        
        // Форма регистрации при вормировании заказа. (Мы должны быть в корзине ))) )
        // TODO нужна ли капча? Робот вряд ли мог бы дойти до оформления заказа
        if ($usr['id'] == 0 && $cfg["shop"]['oncheckout_show_register'] && !$cfg["users"]["disablereg"] 
                && $type == 'BT'){
            $cFields = Userfields::getCoreFields();
            foreach($cFields as $key => $fld){
                if (array_key_exists($fld['field_name'], $fields)){
                    unset($cFields[$key]);
                    //continue;
                }
            }
            $i = 0;
            $tmp = array();
            $cFieldsData = Userfields::getFieldsData(array(), $cFields, 'ruserf');

            foreach ($cFieldsData as $fld){
                $tag = 'USERS_REGISTER_'.mb_strtoupper($fld['field_name']);
                $tmp["{$tag}_TITLE"] = $fld['title'];
                $tmp[$tag] = $fld['editCode'];
                /*
                Выводим поля в цикле. Это позволяет добавлять поля в админке без необходимости править шаблоны
                */
                $t->assign(array(
                    'ROW_TITLE' => $fld['field_title'],
                    'ROW_NAME' => "ruserf{$fld['field_name']}",
                    'ROW_REQUIRED' => $fld['field_required'],
                    'ROW_EDITCODE' => $fld['field_editCode'],
                    'ODDEVEN' => cot_build_oddeven($i),
                ));
                $t->parse('MAIN.USER_REGISTER.ROW');
                $i++;
            }
            $t->assign($tmp);
            $t->parse('MAIN.USER_REGISTER');
        }

        $t->assign(array(
            'PAGE_TITLE'        => $title,
            'BREAD_CRUMBS'      => $breadcrumbs,
            'ADDRESS_TYPE'      => $type,
            'FORM_CANCEL_URL'   => ($r == 'cart') ? cot_url('shop', 'm=cart') : cot_url('shop', 'm=order'),
        ));
        // Авторизация для гостей
        if ($usr['id'] == 0){
            $t->assign(array(
                'USERS_AUTH_SEND' => cot_url('login', 'a=check' . (empty($redirect) ? '' : "&redirect=$redirect")),
                'USERS_AUTH_USER' => cot_inputbox('text', 'rusername', $rusername, array('size' => '16', 
                    'maxlength' => '32')),
                'USERS_AUTH_PASSWORD' => cot_inputbox('password', 'rpassword', '', array('size' => '16', 
                    'maxlength' => '32')),
                'USERS_AUTH_REGISTER' => cot_url('users', 'm=register'),
                'USERS_AUTH_REMEMBER' => $cfg['forcerememberme'] ? $R['form_guest_remember_forced'] : $R['form_guest_remember']
           ));
        }
        
         // Error and message handling
        cot_display_messages($t);
        
        $t->parse('MAIN');
        return $t->text('MAIN');
    }
    
    /**
     * Action. Проверка регистрационных данных 
     * @return bool|string  строка JSON - если запрос пришел по Ajax
     */
    public function checkRegFormAction(){
        global $L, $cot_extrafields, $db_users, $cfg, $db;
        
        $res = array('error' => '0', 'fields' => array());
        // Записываем источники сообщений
        // Для проверки экстраполей
        if (COT_AJAX) $cfg['msg_separate'] = true;
        
        $cFields = Userfields::getCoreFields();
        foreach($cFields as $key => $fld){
            $res['fields']['ruserf'.$fld['name']] = array('error' => 0, 'msg'=> 'Ok');
        }
        
        $ruser['user_name'] = cot_import('ruserfname','P','TXT', 100, TRUE);
        $ruser['user_email'] = cot_import('ruserfemail','P','TXT',64, TRUE);
        $rpassword  = cot_import('ruserfpassword' ,'P','TXT',16);
        $rpassword2 = cot_import('ruserfpassword2','P','TXT',16);
        $ruser['user_email'] = mb_strtolower($ruser['user_email']);

        // Extra fields
        foreach($cot_extrafields[$db_users] as $exfld){
            $ruser['user_'.$exfld['field_name']] = cot_import_extrafields('ruserf'.$exfld['field_name'], $exfld);
        }

        $user_exists = (bool)$db->query("SELECT user_id FROM $db_users WHERE user_name = ? LIMIT 1", array($ruser['user_name']))->fetch();
        $email_exists = (bool)$db->query("SELECT user_id FROM $db_users WHERE user_email = ? LIMIT 1", array($ruser['user_email']))->fetch();

        if (preg_match('/&#\d+;/', $ruser['user_name']) || preg_match('/[<>#\'"\/]/', $ruser['user_name'])){
            cot_error('aut_invalidloginchars', 'rusername');
        }
        
        if (mb_strlen($ruser['user_name']) < 2) cot_error('aut_usernametooshort', 'ruserfname');
        if (mb_strlen($rpassword) < 4) cot_error('aut_passwordtooshort', 'ruserfpassword');
        if (mb_strlen($rpassword2) < 4) cot_error('aut_passwordtooshort', 'ruserfpassword2');
        if (!cot_check_email($ruser['user_email'])) cot_error('aut_emailtooshort', 'ruserfemail'); 
        if ($user_exists) cot_error('aut_usernamealreadyindb', 'ruserfname');
        if ($email_exists) cot_error('aut_emailalreadyindb', 'ruserfemail');
        if ($rpassword != $rpassword2){
            cot_error('aut_passwordmismatch', 'ruserfpassword2');
        }
        
        if (COT_AJAX){
            if (cot_error_found()){
                $res['error'] = 1;
                foreach($_SESSION['cot_messages'] as $key => $err){
                    $field = str_replace('ruserf', '', $key);
                    if(array_key_exists($field, $cFields)){
                        $msg = isset($L[$err[0]['text']]) ? $L[$err[0]['text']] : $err[0]['text'];
                        //var_dump($err);
                        $res['fields'][$key] = array('error' => 1, 'msg'=> $msg);
                    }
                }
            }
            cot_clear_messages();
            return json_encode($res);
        }
           
        return !cot_error_found();
    }


    // ==== Служебные методы ====
    /**
     * Save User Adress 
     * @param array $uData
     * @param string $type 'BT'-реквизиты или 'ST' - доставка
     * @return bool
     */
    protected function saveAdress($uData, $type =  'BT'){
        global $cfg, $usr, $db_users, $db, $cot_extrafields, $L;
        $task = cot_import('task', 'P', 'ALP');

        // Куда вернуться при сохранении
        $r = 'cart';
        if ($usr['id'] > 0) {
            $r = cot_import('r', 'G', 'ALP');
            if ($r != '') $r = 'cart';
        }

        // если мы в корзине - сохранить в корзину
        if ($r == 'cart'){
            $cart = ShopCart::getInstance();
            $cart->saveAddress($uData, $type);
        }
       // Если незарег и переданы данные для регистрации и позволена регистрация во время заказа,
       //  то зарегистрировать пользователя
        if ($cfg["shop"]['oncheckout_show_register'] == 1 && 
                $type == 'BT' && $task == 'saveregister' && $usr['id'] == 0 && $this->checkRegFormAction()) {
            $ruser['user_name'] = cot_import('ruserfname','P','TXT', 100, TRUE);
            $ruser['user_email'] = cot_import('ruserfemail','P','TXT',64, TRUE);
            $rpassword1 = cot_import('ruserfpassword','P','TXT',16);
            //$rpassword2 = cot_import('rpassword2','P','TXT',16);
            $ruser['user_country'] = cot_import('ruserfcountry','P','TXT');
            $ruser['user_timezone'] = cot_import('rtimezone','P','TXT',5);
            $ruser['user_timezone'] = is_null($ruser['user_timezone']) ? $cfg['defaulttimezone'] : (float) $ruser['user_timezone'];
            $ruser['user_gender'] = cot_import('rusergender','P','TXT');
            $ruser['user_email'] = mb_strtolower($ruser['user_email']);

            // Extra fields
            foreach($cot_extrafields[$db_users] as $exfld){
                $ruser['user_'.$exfld['field_name']] = cot_import_extrafields('ruser'.$exfld['field_name'], $exfld);
            }
            $ruser['user_birthdate'] = (int)cot_import_date('ruserbirthdate', false);
            
            $ruser['user_password'] = $rpassword1;

            $userid = cot_add_user($ruser);
            require_once cot_langfile('message', 'core');

            if ($cfg['users']['regnoactivation'] || $db->countRows($db_users) == 1){
                cot_message($L['msg106_body']);
                //cot_redirect(cot_url('message', 'msg=106', '', true));
            }elseif ($cfg['users']['regrequireadmin']){
                cot_message($L['msg118_body']);
               // cot_redirect(cot_url('message', 'msg=118', '', true));
            }else{
                $mess = $L['msg105_body'];
                if ($cfg["shop"]['oncheckout_only_registered'] == 0){
                    $mess .= '<br /><br />'.$L['shop']['user_guest_checkout'];
                }
                cot_message($mess);
               // cot_redirect(cot_url('message', 'msg=105', '', true));
            }
        }
                
       // Если зарег - обновить профиль
       if ($usr['id'] > 0 && $type == 'BT'){
           $data = array();
           foreach($uData as $key => $val){
                $data['user_'.$key] = $val;
           }
           //var_dump($uData);
           //die;
           if (!(bool)$cfg['users']['useremailchange']) unset($data['user_email']);
           // todo обработка сохранения email
           unset($data['user_auth']);
           $db->update($db_users, $data, "user_id='".$usr['id']."'");

       }
       // Или сохранить адрес доставки
       if ($usr['id'] > 0 && $type == 'ST'){
           $data = array();
           foreach($uData as $key => $val){
               $data['ui_'.$key] = $val;
           }
           // Проверить принадлежность пользователю
           if ($data['ui_id'] > 0){
               $cansave = UserInfo::count(array("ui_id={$data['ui_id']}", "user_id={$usr['id']}"));
           }
           $data['ui_address_type'] = 'ST';
           $data['user_id'] = $usr['id'];
           $uInfo = new UserInfo($data);
           if($cansave > 0 || cot_auth('shop', 'any', 'A')){
               if ($id = $uInfo->save()){
                   cot_message($L['shop']['saved']);
               }
           }
       }

       if (cot_error_found()) return false;
       
       // Если мы в корзине редирект в корзину, иначе для зарегов, редирект обратно в личный кабинет
       if($r == 'cart'){
           if ($cart->getInCheckOut()) {
                cot_redirect(cot_url('shop', array('m'=>'cart', 'a'=>'checkout'), '', true));
           }else{
                cot_redirect(cot_url('shop', array('m'=>'cart'), '', true));
           }
       }else{
           cot_redirect(cot_url('shop', array('m'=>'order'), '', true));
        }
    }
}