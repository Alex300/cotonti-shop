<?php
/**
 * English Language File for Shipment, By weight, ZIP and countries Plugin
 *
 * @package shop
 * @subpackage Plugins - shipment
 */
defined('COT_CODE') or die('Wrong URL.');

/**
 * Plugin Title & Subtitle
 */

//$L['forumstats_title'] = 'Forums Statistics';

/**
 * Plugin Body
 */

$L['cfg_shipment_logos'] = array('Logo', '');
$L['cfg_countries'] = array('Countries', 'Please select the countries for which this rate applies. If no country is 
    selected, this rate will be applied for all country');
$L['cfg_zip_start'] = array('ZIP range start', 'Please enter the ZIP range start. If no value is entered for ZIP range
    start and Zip range stop, no zip condition will be applied');
$L['cfg_zip_stop'] = array('ZIP range end', 'Please enter the ZIP range stop. If no value is entered for ZIP range 
    start and Zip range stop, no zip condition will be applied');
$L['cfg_weight_start'] = array('Lowest Weight', 'Please enter the Lowest Weight. If no value is entered for Lowest 
    Weight, no Lowest Weight condition will be applied');
$L['cfg_weight_stop'] = array('Highest Weight', 'Please enter the Highest Weight. If no value is entered for Highest 
    Weight, no Highest Weight condition will be applied');
$L['cfg_weight_unit'] = array('Weight Unit', 'The Weight Unit in which the Weight is given');
$L['cfg_nbproducts_start'] = array('Minimum number of products');
$L['cfg_nbproducts_stop'] = array('Maximum number of products');
$L['cfg_orderamount_start'] = array('Minimum order amount');
$L['cfg_orderamount_stop'] = array('Maximum order amount');
$L['cfg_pirice'] = '<hr />';
$L['cfg_cost'] = array('Shipment Cost', 'Shipment Cost to apply for all orders when the weight is between Lowest 
    Weight and Highest Weight');
$L['cfg_package_fee'] = array('Package Fee', '');
$L['cfg_tax_id'] = array('Tax', 'Tax to apply to the cost');
$L['cfg_free_shipment'] = array('Minimum Amount for Free Shipment', 'Minimum order amount for Free Shipment');
?>