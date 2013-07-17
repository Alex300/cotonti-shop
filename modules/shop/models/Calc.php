<?php
defined('COT_CODE') or die('Wrong URL.');

/**
 * Model class for the Calc Rules
 *
 * @property int calc_id
 * @property string $calc_title
 * @property string $calc_desc
 * @property string $calc_kind
 * @property string $calc_value_mathop
 * @property float $calc_value
 * @property int $curr_id
 * @property bool $calc_shopper_published
 *
 *
 * @method static Calc getById(int $pk)
 * @method static Calc[] find($conditions, $limit = 0, $offset = 0, $order = '')
 *
 * @package shop
 * @subpackage Calc Rules
 * @todo New type of calculation rule "VAT tax"
 */
class Calc extends ShopModelAbstract{

    /**
     * SQL table name
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
     * Типы операций
     * @var array 
     */
    public static $_entryPoints = array();

    /**
     * Категории, к кторым применяется правило
     * @var array
     */
    public $categories = array();

    /**
     * Группы пользователей, к кторым применяется правило
     * @var array
     */
    public $user_groups = array();

    /**
     * Страны, к кторым применяется правило
     * @var array
     */
    public $countries = array();

    /**
     * Регионы, к кторым применяется правило
     * @var array
     */
    public $regions = array();


    /**
     * Static constructor
     */
    public static function __init(){
        global $db_shop_calcs, $L ;

        self::$_table_name = $db_shop_calcs;
        self::$_primary_key = 'calc_id';

        self::$_entryPoints = array(
            'Marge' => $L['shop']['calc_epoint_pmargin'],
            'DBTax' => $L['shop']['calc_epoint_dbtax'],
            'Tax' => $L['shop']['calc_epoint_tax'],
            'VatTax' => $L['shop']['calc_epoint_vattax'],
            'DBTax' => $L['shop']['calc_epoint_dbtax'],
            'DATax' => $L['shop']['calc_epoint_datax'],
            'DBTaxBill' => $L['shop']['calc_epoint_dbtaxbill'],
            'TaxBill' => $L['shop']['calc_epoint_taxbill'],
            'DATaxBill' => $L['shop']['calc_epoint_dataxbill'],
        );

        parent::__init();
    }


    /**
     * Save Calc Rule
     * @param type $data 
     * @return int id of saved record
     */
	public function save($data = false){
        global $db, $db_shop_calcs, $db_shop_calc_categories, $db_shop_calc_groups, $db_shop_calc_countries,
                $db_shop_calc_states, $sys, $usr, $L, $cfg;
        
        if(!$data) $data = $this;

        if ($data instanceof Calc) {
            $categories = $data->categories;
            $uGroups = $data->user_groups;
            $countries = $data->countries;
            $states = $data->regions;
            $data = $data->toArray();
        }elseif(is_array($data)){
            $categories = $data['categories'];
            unset($data['categories']);
            $uGroups = $data['user_groups'];
            unset($data['user_groups']);
            $countries = $data['countries'];
            unset($data['countries']);
            $states = isset($data['regions']) ? $data['regions'] : array();
            unset($data['regions']);
        }

        cot_check(mb_strlen($data['calc_title']) < 2, $L['shop']['titletooshort'], 'shop');
        
        $data['calc_id'] = (int)$data['calc_id'];

        if (empty($data['vendor_id'])) $data['vendor_id'] = Vendor::getLoggedVendorId();
        
        if (cot_error_found()) return false;
        
        $data['calc_updated_on'] = date('Y-m-d H:i:s', $sys['now']);
        $data['calc_updated_by'] = $usr['id'];
        
        if(!$data['calc_id']) {
           // Добавить новый
           $data['calc_created_on'] = date('Y-m-d H:i:s', $sys['now']);
           $data['calc_created_by'] = $usr['id'];
           $res = $db->insert($db_shop_calcs, $data);
           $id = $db->lastInsertId();
           
           $this->saveXRef($db_shop_calc_groups, $uGroups, 'grp_id', $id);
           $this->saveXRef($db_shop_calc_categories, $categories, 'structure_code', $id);
           $this->saveXRef($db_shop_calc_countries, $countries, 'country', $id);
           $this->saveXRef($db_shop_calc_states, $states, 'state_id', $id);
           cot_log("Shop. Added Calc Rule #".$id,'plg');
       }else{
           // Сохранить существующий
           $id = $data['calc_id'];
           unset($data['calc_id']);
           $res = $db->update($db_shop_calcs, $data, "calc_id={$id}");
           
           $this->saveXRef($db_shop_calc_groups, $uGroups, 'grp_id', $id);
           $this->saveXRef($db_shop_calc_categories, $categories, 'structure_code', $id);
           $this->saveXRef($db_shop_calc_countries, $countries, 'country', $id);
           $this->saveXRef($db_shop_calc_states, $states, 'state_id', $id);
           cot_log("Shop. Edited Calc Rule #".$id,'plg');
       }
      
       return $id;
    }
    
    /**
     * Соханить внешние связи (many to many)
     * @param string $table
     * @param array $data
     * @param string $xField - имя поля с id внешней таблицы (structure_code, grp_id и т.д.)
     * @param int $id - значение _primary (calc_id) таблицы, для которой обновляем связи)
     * @param string $field - имя поля для свази, если не указано используется $this->_primary
     * @todo перенести метод в ShopModelAbstract
     */
    protected function saveXRef($table, $data, $xField, $id = false, $field = false){
        global $db;
        
        $id =  (int)$id;
        
        if (!$field) $field = self::$_primary_key;
        if (!$id) $id = $this->{$field};
        if (!$id) return false;
            
        $query = "SELECT `$xField` FROM $table WHERE {$field}=$id";
        $old_xRefs = $db->query($query)->fetchAll(PDO::FETCH_COLUMN);
        
        if (!$old_xRefs) $old_xRefs = array();
        $kept_xRefs = array();
        $new_xRefs = array();
        // Find new groups, count old groups that have been left
        $cnt = 0;
        $isstr = false;
        foreach ($data as $item){
            if (!is_int($item)) $isstr = true;
            $p = array_search($item, $old_xRefs);
            if($p !== false){
                $kept_xRefs[] = $old_xRefs[$p];
                $cnt++;
            }else{
                $new_xRefs[] = $item;
            }
        }
        // Remove old user groups that have been removed
        $rem_xRefs = array_diff($old_xRefs, $kept_xRefs);
        if (count($rem_xRefs) > 0) {
               if ($isstr){
                   $inCond = "('".implode("','", $rem_xRefs)."')";
               }else{
                   $inCond = "(".implode(",", $rem_xRefs).")";
               }
               $db->delete($table, "$field=$id AND $xField IN $inCond");
        }
        // Add new xRefs
        foreach($new_xRefs as $item){
               if ((!$isstr && $item > 0) || ($isstr && $item!='')){
                    $upData = array(
                        $field  => $id,
                        $xField => $item,
                    );
                    $res = $db->insert($table, $upData);
               }
        }
    }

    /**
     * Получить 
     * @param type $kind
     * @return Calc[]
     */
    public static function getRule($kind){
        global $db, $db_shop_calcs, $sys;
        
        if (empty($kind)) return false;
        
        if (!is_array($kind)) $kind = array($kind);
        
		$_nullDate	= date('Y-m-d H:i:s', 0);
		$_now			= date('Y-m-d H:i:s', $sys['now']);
        
		$q = "SELECT * FROM `$db_shop_calcs` WHERE calc_kind IN ('".implode("','", $kind)."')
            AND ( calc_publish_up=".$db->quote($_nullDate)." OR calc_publish_up <= ".$db->quote($_now)." )
			AND ( calc_publish_down =".$db->quote($_nullDate)." OR calc_publish_down >= ".$db->quote($_now)." )";


		$sql = $db->query($q);
		$data = $sql->fetchAll(PDO::FETCH_CLASS, 'Calc');
//		if (!$data) {
//   			$data = new stdClass();
//  		}
  		return $data;
	}
    
    /**
     * Получить все налоги
     * @return \Calc[] налоги
     */
    public static function getTaxes() {

		return self::getRule(array('TAX','VatTax','TaxBill'));
	}
    
    /**
     * Получить все Изменения цен - скидки
     * @return Calc[] скидки
     */
    public static function getDiscounts(){
		return  self::getRule(array('DATax','DATaxBill','DBTax','DBTaxBill'));
	}

    public static function getDBDiscounts() {

        return self::getRule(array('DBTax','DBTaxBill'));
    }

    public static function getDADiscounts() {

        return self::getRule(array('DATax','DATaxBill'));
    }

    /**
     * Get all objects from the database matching given conditions
     *
     * @param mixed $conditions Array of SQL WHERE conditions or a single condition as a string
     * @param int $limit Maximum number of returned records or 0 for unlimited
     * @param int $offset Return records starting from offset (requires $limit > 0)
     * @param string $order
     * @return Calc[] List of objects matching conditions or null
     * @global CotDb $db
     */
    protected static function fetch($conditions = array(), $limit = 0, $offset = 0, $order = ''){
        global $db, $db_shop_calc_categories, $db_shop_calc_groups, $db_shop_calc_countries, $db_shop_calc_states;

        $table = '';
        $calssCols = array();
        $class = get_called_class();
        eval('$table = '.$class.'::$_table_name;');
        eval('$calssCols = '.$class.'::$_columns;');

        $columns = array();
        $joins = array();
        foreach ($calssCols as $col){
            $columns[] = "`$table`.`$col`";
        }
        $columns = implode(', ', $columns);
        $joins = implode(' ', $joins);

        list($where, $params) = Calc::parseConditions($conditions);

        $order = ($order) ? "ORDER BY $order" : '';
        $limit = ($limit) ? "LIMIT $offset, $limit" : '';

        /** @var Calc[] $Calcs  */
        $Calcs = array();
        $calcIds = array();
        $res = $db->query("SELECT $columns FROM $table $joins $where $order $limit ", $params);
        if($res->rowCount() == 0) return null;

        while ($row = $res->fetch(PDO::FETCH_ASSOC)){
            $calcIds[] = (int)$row['calc_id'];
            $obj = new Calc($row);
            $Calcs[$row['calc_id']] = $obj;
        }

        // Выбираем все Категории
        $q = "SELECT calc_id, structure_code
            FROM $db_shop_calc_categories WHERE calc_id IN (".implode(', ', $calcIds).")
            ORDER BY calc_id, structure_code";

        $res = $db->query($q);
        while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
            if ($row['structure_code'] == '') continue;
            $Calcs[$row['calc_id']]->categories[] = $row['structure_code'];
        }

        // Выбираем все группы пользователей
        $q = "SELECT calc_id, grp_id
            FROM $db_shop_calc_groups WHERE calc_id IN (".implode(', ', $calcIds).")
            ORDER BY calc_id, grp_id";

        $res = $db->query($q);
        while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
            $grpId = (int)$row['grp_id'];
            if ($grpId <= 0) continue;
            $Calcs[$row['calc_id']]->user_groups[] = $grpId;
        }

        // Выбираем все Страны
        $q = "SELECT calc_id, country
            FROM $db_shop_calc_countries WHERE calc_id IN (".implode(', ', $calcIds).")
            ORDER BY calc_id, country";

        $res = $db->query($q);
        while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
            if ($row['country'] == '') continue;
            $Calcs[$row['calc_id']]->countries[] = $row['country'];
        }

        // Выбираем все Регионы
        // todo fix
        $q = "SELECT calc_id, state_id
            FROM $db_shop_calc_states WHERE calc_id IN (".implode(', ', $calcIds).")
            ORDER BY calc_id, state_id";

        $res = $db->query($q);
        while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
            $stId = (int)$row['state_id'];
            if ($stId <= 0) continue;
            $Calcs[$row['calc_id']]->regions[] = $stId;
        }

        $ret = array();
        foreach($Calcs as $calc){
            $ret[] = $calc;
        }

        return (count($ret) > 0) ? $ret : null;
    }
            
}
// Class initialization for some static variables
Calc::__init();