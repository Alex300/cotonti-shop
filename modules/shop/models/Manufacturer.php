<?php
/**
 * Model class for shop Manufacturer
 * 
 * @package shop
 * @subpackage Manufacturer
 *
 */
defined('COT_CODE') or die('Wrong URL.');

/**
 * Model class for Manufacturer
 * @package shop
 * @subpackage Manufacturer
 */
class Manufacturer {
    
    
    /**
     * Retireve a key => val list of Manufacturers from the database.
     * @global CotDB $db
     * @return array 
     * @todo cache
     */
    public static function getKeyValPairsList() {
        global $db, $db_pages, $cfg;
        
        $cats = cot_structure_children('page', $cfg["shop"]['manuf_cat']);
        if(!$cats) return false;
        
        $q = "SELECT page_id, page_title FROM `$db_pages` 
            WHERE page_state=0 AND page_cat IN ('".implode("', '", $cats)."') ORDER BY `page_title` ASC";
        $sql = $db->query($q);
        
        return $sql->fetchAll(PDO::FETCH_KEY_PAIR);
    }
    
    
}