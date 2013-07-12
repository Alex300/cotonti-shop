<?php
defined('COT_CODE') or die('Wrong URL.');

/**
 * Abstract Model class
 *
 * @package shop
 * @subpackage DB
 * @todo Перекрестные ссылки (как в SOM)
 * @todo валидация типов при __set, сохранении и вставке
 * @todo доработка сеттеров по аналогии с laPazzle
 *      $methodName = 'set' . ucfirst($name);
        if (method_exists($this, $methodName)) {
            return $this->$methodName($name, $val);
        }
 * @todo связи
 */
abstract class ShopModelAbstract{

    /**
     * SQL table name
     * В php 5.3 с норамльным наследованием статиуи это должно быть protected
     * в php 5.2 использыем эти свойства только от наследуемых слассов
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
     * Дополнение к Select метода fetch
     * @var array
     */
    public static $_fetchСolumns = array();

    /**
     * Дополнение к FROM метода fetch
     * @var array
     */
    public static $_fetchJoins = array();

    protected static $_stCache = array();

    /**
     * Object data
     * @var array
     */
    protected $_data = array();

    /**
     * Static constructor
     * global CotDb $db
     */
    public static function __init(){
        global $db;

        $class = get_called_class();
        $cond = false;
        eval('$cond = empty('.$class.'::$_columns);');
        if ($cond){
            //static::$_fields - не работает в php 5.2
            //$table = self::$_table_name;  // self указывает на себя а не потомка
            $table = '';
            eval('$table = '.$class.'::$_table_name;');
            $columns = $db->query("SHOW COLUMNS FROM `{$table}`")->fetchAll(PDO::FETCH_COLUMN);
            eval($class.'::$_columns = $columns;');
        }
    }

    /**
     * Instance constructor
     *
     * @param array $data Raw data
     * @global CotDb $db
     */
    public function __construct($data = array()){
        global $db;

        $class = get_class($this);
        $columns = array();
        eval('$columns = '.$class.'::$_columns;');

        // Инициализация полей
        foreach($columns as $col){
            $this->_data[$col] = null;
        }

        // в свойства заполнять только те поля, что есть в таблице
        if (!empty($data)){
            $this->setData($data);
        }
    }

    /**
     * isset() handler for object properties.
     * @param string $column Column name
     * @return boolean TRUE if the column has a value, FALSE otherwise.
     */
    public function __isset($column){
        // Если есть специальный метод, вызываем его
        $methodName = 'isset' . ucfirst($column);
        if (method_exists($this, $methodName)) {
            return $this->$methodName($column);
        }
        return isset($this->_data[$column]);
    }

    /**
     * Getter for a column.
     * @param string $column Column name
     * @return mixed Column value
     */
    public function __get($column){
        // Если есть специальный метод, вызываем его
        $methodName = 'get' . ucfirst($column);
        if (method_exists($this, $methodName)) {
            return $this->$methodName($column);
        }
        return $this->_data[$column];
    }

    /**
     * Setter for a column.
     * @param string $column Column name
     * @param mixed $value Column value
     * @return bool
     */
    public function __set($column, $value){
        // Если есть специальный метод, вызываем его
        $methodName = 'set' . ucfirst($column);
        if (method_exists($this, $methodName)) {
            return $this->$methodName($column, $value);
        }

        $class = get_class($this);
        $columns = array();
        eval('$columns = '.$class.'::$_columns;');

        if (!is_null($column) && !is_null($value)){ // Setter
            if (in_array($column, $columns)){
                $this->_data[$column] = $value;
                return true;
            }
            return false;
        }
    }

    /**
     * unset() handler for object properties.
     * @param string $column Column name
     */
    public function __unset($column){
        // Если есть специальный метод, вызываем его
        $methodName = 'unset' . ucfirst($column);
        if (method_exists($this, $methodName)) {
            return $this->$methodName($column);
        }
        if (isset($this->_data[$column])) unset($this->_data[$column]);
    }

    /**
     * @param ModelAbstract|array $data
     * @throws Exception
     */
    public function setData($data){
        $class = get_class($this);
        if ($data instanceof $class) $data = $data->toArray();
        if (!is_array($data)){
            throw new  Exception("Data must be an Array or instance of $class Class");
        }
        foreach($data as $key => $value){
            $this->__set($key, $value);
        }
    }
    
    /**
     * Объект перевести в массив
     * @return array
     */
    public function toArray(){
        return $this->_data;
    }

    /**
     * Get List
     * @static
     * @param int $limit Maximum number of returned objects
     * @param int $offset Offset from where to begin returning objects
     * @param string $order Column name to order on and WAY
     * @return ShopModelAbstract[]
     */
    public static function getList( $limit = 0, $offset = 0, $order = ''){
        return self::fetch(array(), $limit, $offset, $order);
    }

    /**
     * Retrieve all existing objects from database
     *
     * @param mixed $conditions Numeric array of SQL WHERE conditions or a single
     *      condition as a string
     * @param int $limit Maximum number of returned objects
     * @param int $offset Offset from where to begin returning objects
     * @param string $order Column name to order on
     * @return ShopModelAbstract[]
     */
    public static function find($conditions, $limit = 0, $offset = 0, $order = ''){
        $className = get_called_class();

        $res = null;
        eval('$res = '.$className.'::fetch($conditions, $limit, $offset, $order);');
        return $res;
    }

    /**
     * Retrieve existing object from database by primary key
     *
     * @param int $pk Primary key
     * @return ShopModelAbstract
     */
    public static function getById($pk){
        $pk = (int)$pk;
        if(!$pk) return null;

        $className = get_called_class();

        if (isset(self::$_stCache[$className][$pk])){
//            echo "$className -> $pk : From Cache<br />";
//            var_dump(self::$_stCache);
            return self::$_stCache[$className][$pk];
        }
//        echo "$className -> $pk : No Cache<br />";

        $prKey = '';
        eval('$prKey = '.$className.'::$_primary_key;');
        $res = null;
        eval('$res = '.$className.'::fetch($prKey." = \'$pk\'", 1);');

        if($res) self::$_stCache[$className][$pk] = $res[0];

        return ($res) ? $res[0] : null;
    }

    /**
     * Returns SQL COUNT for given conditions
     *
     * @param mixed $conditions Array of SQL WHERE conditions or a single
     *      condition as a string
     * @global CotDb $db
     * @return int
     */
    public static function count($conditions = array()){
        global $db;

        $class = get_called_class();
        $table = '';
        eval('$table = '.$class.'::$_table_name;');
        list($where, $params) = self::parseConditions($conditions);

        return (int) $db->query("SELECT COUNT(*) FROM ".$table." $where ", $params)->fetchColumn();
    }

    /**
     * Save data
     * @param Object|array|null $data
     * @global CotDB $db;
     * @return int id of saved record
     */
    public function save($data = null){
        global $db, $sys, $usr;

        $class = get_class($this);
        if(!$data) $data = $this->_data;

        $prKey = '';
        $table = '';
        $columns = array();
        eval('$columns = '.$class.'::$_columns;');
        eval('$prKey = '.$class.'::$_primary_key;');
        eval('$table = '.$class.'::$_table_name;');

        if ($data instanceof ShopModelAbstract) {
            $data = $data->toArray();
        }

        $prefix = substr($columns[0], 0, strpos($columns[0], "_"));

        if (in_array("{$prefix}_updated_on", $columns)){
            $data["{$prefix}_updated_on"] = date('Y-m-d H:i:s', $sys['now']);
        }
        if (in_array("{$prefix}_updated_by", $columns)){
            $data["{$prefix}_updated_by"] = $usr['id'];
        }

        if(!$data[$prKey]) {
            // Добавить новый
            if (in_array("{$prefix}_created_on", $columns)){
                $data["{$prefix}_created_on"] = date('Y-m-d H:i:s', $sys['now']);
            }
            if (in_array("{$prefix}_created_by", $columns)){
                $data["{$prefix}_created_by"] = $usr['id'];
            }
            $res = $db->insert($table, $data);
            $id = $db->lastInsertId();
            $this->_data[$prKey] = $id;
        }else{
            // Сохранить существующий
            $id = $data[$prKey];
            unset($data[$prKey]);
            $res = $db->update($table, $data, "{$prKey}={$id}");
            unset(self::$_stCache[$class][$id]);
        }
        return $id;
    }

    /**
     * Delete db record
     * @global CotDb $db
     * @return bool
     */
    public function delete(){
        global $db;

        $class = get_class($this);
        $prKey = '';
        $table = '';
        eval('$prKey = '.$class.'::$_primary_key;');
        eval('$table = '.$class.'::$_table_name;');

        $ret = $db->delete($table, "{$prKey}=".$this->_data[$prKey]);
        unset(self::$_stCache[$class][$this->_data[$prKey]]);

        return (bool)$ret;
    }

    /**
     * Get all objects from the database matching given conditions
     *
     * @param mixed $conditions Array of SQL WHERE conditions or a single
     * condition as a string
     * @param int $limit Maximum number of returned records or 0 for unlimited
     * @param int $offset Return records starting from offset (requires $limit > 0)
     * @param string $order Sorting order and WAY
     * @return ShopModelAbstract[] List of objects matching conditions or null
     * @global CotDb $db
     */
    protected static function fetch($conditions = array(), $limit = 0, $offset = 0, $order = ''){
        global $db;

        $table = '';
        $calssCols = array();
        $class = get_called_class();
        $joins = array();
        $addColumns = array();
        eval('$table = '.$class.'::$_table_name;');
        eval('$calssCols = '.$class.'::$_columns;');
        eval('$joins = '.$class.'::$_fetchJoins;');
        eval('$addColumns = '.$class.'::$_fetchСolumns;');

        $columns = array();
        foreach ($calssCols as $col){
            $columns[] = "`$table`.`$col`";
        }
        if(!empty($addColumns)) $columns = array_merge($columns, $addColumns);
        $columns = implode(', ', $columns);
        $joins = implode(' ', $joins);

        list($where, $params) = self::parseConditions($conditions);

        if(!empty($order)) $order = self::parseOrder($order);
        $order = ($order) ? "ORDER BY $order" : '';
        $limit = ($limit) ? "LIMIT $offset, $limit" : '';

        $objects = array();
        $res = $db->query("SELECT $columns FROM $table $joins $where $order $limit ", $params);
        while ($row = $res->fetch(PDO::FETCH_ASSOC)){
            $obj = new $class($row);
            $objects[] = $obj;
        }

        $res->closeCursor();

        return (count($objects) > 0) ? $objects : null;

    }

    /**
     * Parses query conditions from string or array
     *
     * @param mixed $conditions SQL WHERE conditions as string or numeric array of strings or array of arrays
     * @param array $params Optional PDO params to pass through
     * @return array SQL WHERE part and PDO params
     * @todo описание условий
     */
    protected static function parseConditions($conditions, $params = array()){
        $where = '';

        $table = '';
        $class = get_called_class();
        eval('$table = '.$class.'::$_table_name;');

        //$table = self::$_table_name;
        if (!is_array($conditions)) $conditions = array($conditions);
        if (count($conditions) > 0)
        {
            $where = array();
            $orWhere = array();
            $i = 0;
            foreach ($conditions as $condition)
            {
                $i++;
                if(is_array($condition)){
                    // todo проверка существования колонки $cond[0]
                    if (empty($condition[2])) $condition[2] = '=';
                    if (empty($condition[3]) || $condition[3] != 'OR') $condition[3] = 'AND';

                    $column   = $condition[0];
                    $value    = $condition[1];
                    $operand  = $condition[2];

                    $tblCol = "`$table`.`$column`";
                    if (mb_strpos($column, '.') !== false){
                        $tmp = explode('.', $column);
                        $tmp[0] = ($tmp[0] != '') ? $tmp[0] : $table;
                        $tblCol = "`{$tmp[0]}`.`{$tmp[1]}`";
                        $column = $tmp[1];
                    }
                    if($column == 'RAW' || $column == 'SQL'){
                        $wh = $value ? $value : '';
                    }elseif(is_array($value)) {
                        $wh = ($value ? ($tblCol.($operand == '<>' ? ' NOT' : '').' IN ('.implode(',', self::quote($value)).')') : '0');
                    }
                    elseif($value === null) {
                        $wh = ("{$tblCol} IS ".($operand == '<>' ? 'NOT ' : '') . 'NULL');
                    }
                    else {
                        if (strpos($value, '*') !== false) {
                            $wh = "$tblCol LIKE :$column".$i;
                            $params[$column.$i] = str_replace('*', '%', $value);
                        }else{
                            $wh = $tblCol.' '.$operand.' :'.$column.$i;
                            $params[$column.$i] = $value;
                        }
                    }
                    if ($condition[3] != 'OR') {
                        $where[] = $wh;
                    }else{
                        $orWhere[] = $wh;
                    }
                }else{
                    // Парсим строковые условия
                    $parts = array();
                    // TODO support more SQL operators
                    preg_match_all('/(.+?)([<>= ]+)(.+)/', $condition, $parts);
                    $column = trim($parts[1][0]);
                    $operator = trim($parts[2][0]);
                    $value = trim(trim($parts[3][0]), '\'"`');
                    if ($column && $operator)
                    {
                        $where[] = "`$table`.`$column` $operator :$column";
                        //if (intval($value) == $value) $value = intval($value);    // не работает. Даже для строк дает true
                        if ((intval($value) == $value) && (strval(intval($value)) == $value)) $value = intval($value);
                        $params[$column] = $value;
                    }else{
                            // Поддержка прямых условий НЕ Безопасно!!!
                            $where[] = "$condition";

                    }
                }
            }
            $where = 'WHERE ('.implode(') AND (', $where).')';
            if (count ($orWhere) > 0)  $where .= ' OR ('.implode(') OR (', $orWhere).")";
        }
        return array($where, $params);
    }

    /**
     * Parse Order clause
     * @param string|array $order
     * @throws Exception
     * @return string
     */
    protected static function parseOrder($order){

        // Если передали строку
        if (!is_array($order)) {
            return $order;
        }

        if (is_array($order)) {
            $ord = array();
            foreach ($order as $cond) {
                if (is_string($cond)) {
                    $ord[] = $cond;
                }elseif (is_array($cond)){
                    if (empty($cond[1])){
                        $cond[1] = 'ASC';
                    }else{
                        $cond[1] = trim(strtoupper($cond[1]));
                        if (!in_array($cond[1], array('ASC', 'DESC'))){
                            throw new Exception("Wrong order direct '{$cond[1]}'. Must be 'ASC', 'DESC' or empty ");
                        }
                    }
                    // todo проверка существования колонки $cond[0]
                    $ord[] = $cond[0].' '.$cond[1];
                }

            }
            if (count($ord) > 0) return implode(', ', $ord);
            return '';
        }

    }

    /**
     * Экранирование данных для запроса
     * @static
     * @param mixed $data строка или массив строк для экранирования
     * @global CotDb $db
     * @return array|string
     */
    public static function quote( $data ){
        global $db;

        if (is_string($data)) return $db->quote($data) ;

        if (!is_array($data)) return $data;

        $ret = array();
        foreach($data as $key => $str){
//            if (is_string($str)) $ret[$key] = $db->quote($str);
             if (!(strval(intval($ret[$key])) == $ret[$key])) $ret[$key] = $db->quote($str);
        }
        return $ret;
    }

    // === Методы для работы с шаблонами ===
    /**
     * Returns all order tags for coTemplate without order items
     *
     * @param Object|int $data Order object or ID
     * @param string $tagPrefix Prefix for tags
     * @param bool $cacheitem Cache tags
     */
    public static function generateTags($data, $tagPrefix = '', $cacheitem = true){

//        static $coupon_cache = array();

    }
}

if(!function_exists('get_called_class')) {

    /**
     * Получить имя вызвавшего класса
     * Долбаный кастыль для php 5.2
     * @param null $functionName
     * @return mixed
     */
    function get_called_class($functionName=null)
    {
        $btArray = debug_backtrace();
        //$btIndex = count($btArray) - 1;
        $btIndex = 1;   // Первый элемент - это вызов: get_called_class пропускаем
        //while($btIndex > -1)
        while($btIndex < count($btArray))
        {
//            echo "<br />==========<br />";
//            echo $btIndex."<br />";
//            var_dump($btArray[$btIndex]);
            if(!isset($btArray[$btIndex]['file']))
            {
                $btIndex--;
                if(isset($matches[1]))
                {
                    if(class_exists($matches[1]))
                    {
                        return $matches[1];
                    }
                    else
                    {
                        continue;
                    }
                }
                else
                {
                    continue;
                }
            }else{
                $fName = str_replace(": eval()'d code", '', $btArray[$btIndex]['file']);
                if(!file_exists($fName)) {
                    $btIndex++;
                    continue;
                }
                $lines = file($btArray[$btIndex]['file']);
                $callerLine = $lines[$btArray[$btIndex]['line']-1];
                if(!isset($functionName)){
                    preg_match('/([a-zA-Z\_]+)::/',
                        $callerLine,
                        $matches);
                }
                else
                {
                    preg_match('/([a-zA-Z\_]+)::'.$functionName.'/',
                        $callerLine,
                        $matches);
                }
                $btIndex++;
                if(isset($matches[1]))
                {
                    if(class_exists($matches[1])){
                        return $matches[1];
                    }else{
                        continue;
                    }
                }
                else
                {
                    continue;
                }
            }
        }
        return $matches[1];
    }

}

// Class initialization for some static variables
//ShopModelAbstract::__init();