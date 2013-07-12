<?php
/**
 * Admin Controller class for the users
 * 
 * @package shop
 * @subpackage admin
 * @copyright http://portal30.ru
 *
 */
(defined('COT_CODE') && defined('COT_ADMIN')) or die('Wrong URL.');

class UserController{

    /**
     * Ajax Создание необходимых экстраполей для пользователей
     */
    public function addextfieldsAction(){
        global $L, $cfg, $db_users;

        $extFields = Userfields::getShopDefaultUserExtrafields();

        $ret = array(
            'error' => '',
            'skiped' => 0,
            'added' => 0,
            'message' => '',
        );
        if (count($extFields) > 0){
            foreach($extFields as $key => $field){
                if (!empty($cfg['shop']['uextf_'.$key])){
                    $field['name'] = $cfg['shop']['uextf_'.$key];
                }else{
                    $ret['message'] .= "Field name empty for '$key'. Skipped<br />";
                    $ret['skiped']++;
                    continue;
                }
                $field['description'] = '';
                if (!empty($L['cfg_pextf_'.$key][0])){
                    $field['description'] = $L['cfg_uextf_'.$key][0];
                }else{
                    $field['description'] = $key;
                }

                if (cot_extrafield_add($db_users, $field['name'], $field['type'], '', '', $field['default'], false, 'HTML',
                    $field['description'], $field['params'])){
                    $ret['added']++;
                }else{
                    $ret['skiped']++;
                }
            }
        }
        if ($ret['message'] != '') $ret['message'] .= '<br />';
//        $ret['message'] .= "{$L['Done']}<br />";
        $ret['message'] .= "- {$L['shop']['extfields_added']}: {$ret['added']}<br />";
        $ret['message'] .= "- {$L['shop']['extfields_skipped']}: {$ret['skiped']}<br />";
        $ret['buttonText'] = 'Ok';
        $ret['title'] = "{$L['Done']}";

        return json_encode($ret);
    }

    // === Служебные методы ===

}