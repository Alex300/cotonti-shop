<?php
/**
 * Model class for shop shipment
 * 
 * @package shop
 * @subpackage Shipment
 * @author Alex
 * @copyright http://portal30.ru
 * @todo наследовать от абстрактного
 *
 */
defined('COT_CODE') or die('Wrong URL.');

/**
 * @property int $shipm_id
 * @property int $vendor_id
 * @property string $shipm_title
 * @property string $shipm_desc
 * @property string pl_code
 * @property array $shipm_params
 * @property array $user_groups
 *
 * @method static ShipmentMethod[] find
 * @method static ShipmentMethod getById
 */
class ShipmentMethod extends ShopModelAbstract {
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
        global $db_shop_shipmethods;

        self::$_table_name = $db_shop_shipmethods;
        self::$_primary_key = 'shipm_id';
        parent::__init();
    }

    /**
     * Установка конфига
     * @param $column
     * @param $value
     * @return bool
     */
    public function setShipm_params($column, $value){
        if(empty($value)) return false;
        if(is_array($value)) $value = serialize($value);

        $this->_data['shipm_params'] = $value;
    }

    /**
     * Получение конфига
     * @param $column
     * @return array|bool
     */
    public function getShipm_params($column){
        if(empty($this->_data['shipm_params'])) return false;

        $value = unserialize($this->_data['shipm_params']);
        if (!is_array($value)) return false;
        return $value;
    }

    /**
     * Получить группы пользователя
     * @param $column
     * @return array|bool
     */
    public function getUser_groups($column = ''){
        global $db_shop_shipmethods_gr, $db;
        if(empty($this->_user_groups) && !empty($this->_data['shipm_id'])){
            $query = "SELECT `grp_id` FROM $db_shop_shipmethods_gr WHERE shipm_id={$this->_data['shipm_id']}";
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
        if(!empty($data['shipm_params'])) $data['shipm_params'] = unserialize($data['shipm_params']);
        $data['user_groups'] = $this->getUser_groups();
        $data['totalPrice'] = (!empty($this->totalPrice)) ? $this->totalPrice : 0;

        return $data;
    }

    /**
     * Сохранить cпособ доставки
     * @param type $data 
     * @return int id of saved record
     */
	public function save($data = false){
        global $db, $db_shop_shipmethods, $db_shop_shipmethods_gr, $sys, $usr, $L;

        if(!$data) $data = $this->toArray();
        if ($data instanceof ShipmentMethod) {
            $data = $data->toArray();
        }

        $uGroups = $data['user_groups'];
        unset($data['user_groups']);
        cot_check(mb_strlen($data['shipm_title']) < 2, $L['shop']['titletooshort'], 'shop');
       
        $data['shipm_id'] = (int)$data['shipm_id'];
        // TODO использовать залогиненного вендора
        // $res['vendor_id'] = Vendor::getLoggedVendor(); - в будущем
       
       if (cot_error_found()) return false;
       
       if (!$data['vendor_id']) $data['vendor_id'] = 1;
       $data['shipm_updated_on'] = date('Y-m-d H:i:s', $sys['now']);
       $data['shipm_updated_by'] = $usr['id'];
       if (isset($data['shipm_params']) && is_array($data['shipm_params'])){
           $data['shipm_params'] = serialize($data['shipm_params']);
       }
       
       if(!$data['shipm_id']) {
           // Добавить новый
           $data['shipm_created_on'] = date('Y-m-d H:i:s', $sys['now']);
           $data['shipm_created_by'] = $usr['id'];
           
           $res = $db->insert($db_shop_shipmethods, $data);
           $id = $db->lastInsertId();
           
           foreach($uGroups as $grp){
               if ($grp > 0){
                    $ugData = array(
                        'shipm_id' => $id,
                        'grp_id'   => $grp,
                    );
                    $res = $db->insert($db_shop_shipmethods_gr, $ugData);
               }
           }
           cot_log("Added Shipment method #".$id." - ".$data['shipm_title'],'adm');
       }else{
           // Сохранить существующий
           $id = $data['shipm_id'];
           unset($data['shipm_id']);
           $res = $db->update($db_shop_shipmethods, $data, "shipm_id={$id}");
                      
           // Сохраняем группы пользователей
           $query = "SELECT `grp_id` FROM $db_shop_shipmethods_gr WHERE shipm_id=$id";
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
               $db->delete($db_shop_shipmethods_gr, "shipm_id=$id AND grp_id IN (".implode(',', $rem_groups).")");
           }
           // Add new user groups
           foreach($new_groups as $grp){
               if ($grp > 0){
                    $ugData = array(
                        'shipm_id' => $id,
                        'grp_id'   => $grp,
                    );
                    $res = $db->insert($db_shop_shipmethods_gr, $ugData);
               }
           }
           cot_log("Edited Shipment method #".$id." - ".$data['shipm_title'],'adm');
      }
       
       return $id;
    }

    /**
     * Получить все методы доставки для указанного пользователя
     * @param int $userId
     * @param int $vendorid
     * @return array
     * @todo может надо Hook - Все плагины доставки просматривают массив $shipmentMethods и исключают из него те методы,
     *      которые неподходят для данного заказа
     */
    public static function getListByUserId($userId = 0, $vendorid = 0){
        global $db, $db_groups_users;

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
            array('shipm_published', 1),
            array("SQL", "vendor_id=0 $vend OR shipm_shared=1")
        );

        $methods = ShipmentMethod::fetch($cond, 0, 0, 'shipm_order');
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
ShipmentMethod::__init();