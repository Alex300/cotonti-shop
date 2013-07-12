<?php
defined('COT_CODE') or die('Wrong URL.');

/**
 * Model class for shop payment
 *
 * @package shop
 * @subpackage Payment
 * @author Alex
 * @copyright Studio Portal30 http://portal30.ru
 *
 * @property int $paym_id
 * @property int $vendor_id
 * @property string $paym_title
 * @property string pl_code
 * @property array $paym_params
 * @property array $user_groups
 *
 * @method static PaymentMethod[] find
 * @method static PaymentMethod getById
 * @todo не срочно оптимизировать метод fetch - вожножно группы пользователя имеет смысл грузить пачкой для всей выборки
 */
class PaymentMethod extends ShopModelAbstract {

    /**
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
     * @var int Итоговая плата за транзакцию
     */
    public $totalPrice = 0;

    /**
     * @var array Многие ко многим
     */
    protected $_user_groups;

    /**
     * Static constructor
     */
    public static function __init(){
        global $db_shop_paymethods;

        self::$_table_name = $db_shop_paymethods;
        self::$_primary_key = 'paym_id';
        parent::__init();
    }

    /**
     * Установка конфига
     * @param $column
     * @param $value
     * @return bool
     */
    public function setPaym_params($column, $value){
        if(empty($value)) return false;
        if(is_array($value)) $value = serialize($value);

         $this->_data['paym_params'] = $value;
    }

    /**
     * Получение конфига
     * @param $column
     * @return array|bool
     */
    public function getPaym_params($column){
        if(empty($this->_data['paym_params'])) return false;

        $value = unserialize($this->_data['paym_params']);
        if (!is_array($value)) return false;
        return $value;
    }

    /**
     * Получить группы пользователя
     * @param $column
     * @return array|bool
     */
    public function getUser_groups($column = ''){
        global $db_shop_paymethods_gr, $db;
        if(empty($this->_user_groups) && !empty($this->_data['paym_id'])){
            $query = "SELECT `grp_id` FROM $db_shop_paymethods_gr WHERE paym_id={$this->_data['paym_id']}";
            $this->_user_groups = $db->query($query)->fetchAll(PDO::FETCH_COLUMN);
        }

        return $this->_user_groups;
    }

    /**
     * Установить группы пользователя
     * @param $column
     * @param array $value
     */
    public function setUser_groups($column, $value){
        if (!empty($value) && is_array($value)) $this->_user_groups = $value;
    }

    public function issetUser_groups($column){
        $this->getUser_groups();
        return isset($this->_user_groups);
    }

    /**
     * Объект перевести в массив
     * @return array
     */
    public function toArray(){

        $data = $this->_data;
        if(!empty($data['paym_params'])) $data['paym_params'] = unserialize($data['paym_params']);
        $data['user_groups'] = $this->getUser_groups();
        $data['totalPrice'] = (!empty($this->totalPrice)) ? $this->totalPrice : 0;

        return $data;
    }

    /**
     * Сохранить cпособ оплаты
     * @param array|bool $data
     * @global CotDB $db
     * @return int id of saved record
     */
	public function save($data = false){
        global $db, $db_shop_paymethods, $db_shop_paymethods_gr, $sys, $usr, $L;

        if(!$data) $data = $this->toArray();
        if ($data instanceof PaymentMethod) {
            $data = $data->toArray();
        }

       $uGroups = $data['user_groups'];
       unset($data['user_groups']);
       cot_check(mb_strlen($data['paym_title']) < 2, $L['shop']['titletooshort'], 'shop');
       
       $data['paym_id'] = (int)$data['paym_id'];
       // TODO использовать залогиненного вендора
       // $res['vendor_id'] = Vendor::getLoggedVendor(); - в будущем
       
       if (cot_error_found()) return false;
       
       if (!$data['vendor_id']) $data['vendor_id'] = 1;
       $data['paym_updated_on'] = date('Y-m-d H:i:s', $sys['now']);
       $data['paym_updated_by'] = $usr['id'];
       if (isset($data['paym_params']) && is_array($data['paym_params'])){
           $data['paym_params'] = serialize($data['paym_params']);
       }
       
       if(!$data['paym_id']) {
           // Добавить новый
           $data['paym_created_on'] = date('Y-m-d H:i:s', $sys['now']);
           $data['paym_created_by'] = $usr['id'];
           
           $res = $db->insert($db_shop_paymethods, $data);
           $id = $db->lastInsertId();
           
           foreach($uGroups as $grp){
               if ($grp > 0){
                    $ugData = array(
                        'paym_id' => $id,
                        'grp_id'   => $grp,
                    );
                    $res = $db->insert($db_shop_paymethods_gr, $ugData);
               }
           }
           cot_log("Added Payment method #".$id." - ".$data['paym_title'],'adm');
       }else{
           // Сохранить существующий
           $id = $data['paym_id'];
           unset($data['paym_id']);
           $res = $db->update($db_shop_paymethods, $data, "paym_id={$id}");
                      
           // Сохраняем группы пользователей
           $query = "SELECT `grp_id` FROM $db_shop_paymethods_gr WHERE paym_id=$id";
           $old_groups = $db->query($query)->fetchAll(PDO::FETCH_COLUMN);
           if (!$old_groups) $old_groups = array();
           $kept_groups = array();
           $new_groups = array();
           // Find new groups, count old groups that have been left
           $cnt = 0;
           foreach ($uGroups as $tag){
                $p = array_search($tag, $old_groups);
                if($p !== false){
                    $kept_groups[] = $old_groups[$p];
                    $cnt++;
                }else{
                    $new_groups[] = $tag;
                }
           }
           // Remove old user groups that have been removed
           $rem_groups = array_diff($old_groups, $kept_groups);
           if (count($rem_groups) > 0) {
               $db->delete($db_shop_paymethods_gr, "paym_id=$id AND grp_id IN (".implode(',', $rem_groups).")");
           }
           // Add new user groups
           foreach($new_groups as $grp){
               if ($grp > 0){
                    $ugData = array(
                        'paym_id' => $id,
                        'grp_id'   => $grp,
                    );
                    $res = $db->insert($db_shop_paymethods_gr, $ugData);
               }
           }
           cot_log("Edited Payment method #".$id." - ".$data['paym_title'],'adm');
      }
       
       return $id;
    }

    /**
     * Получить все методы оплаты для указанного пользователя
     * @param int $userId
     * @param int $vendorid
     * @global CotDB $db
     * @return array
     */
    public static function getListByUserId($userId = 0, $vendorid = 0){
        global $db, $db_groups_users, $db_shop_paymethods, $db_shop_paymethods_gr;

        $userId = (int)$userId;
        $vendorid = (int)$vendorid;

        $uGroups = array();
        if ($userId > 0){
            // Получить группы пользователя
            $uGroups = $db->query("SELECT gru_groupid FROM $db_groups_users WHERE gru_userid={$userId}")
                ->fetchAll(PDO::FETCH_COLUMN);
        }else{
            $uGroups[] = COT_GROUP_GUESTS;
        }
        $uGroups[] = COT_GROUP_DEFAULT;

        $vend = '';
        if($vendorid > 0) $vend = "OR vendor_id={$vendorid}";

        $cond = array(
            array('paym_published', 1),
            array("SQL", "vendor_id=0 $vend OR paym_shared=1")
        );

        $methods = PaymentMethod::fetch($cond, 0, 0, 'paym_order');
        if(!$methods) return NULL;

        foreach($methods as $key => $method){
            // Если установлено ограничение по группам пользователя, то проверить принадлежность к ним
            // покупателя
            if (is_array($method->user_groups) && count($method->user_groups) > 0){
                $tmp = array_intersect($uGroups, $method->user_groups);
                // Этот метод не подходит для данного пользователя
                if (count($tmp) == 0){
                    unset($methods[$key]);
                    continue;
                }
            }
        }
        reset($methods);

        return $methods;
    }

}
PaymentMethod::__init();