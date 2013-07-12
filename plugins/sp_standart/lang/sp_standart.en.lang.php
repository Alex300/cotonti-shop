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

$L['cfg_shipment_logos'] = array('Logo', 'Logos to be displayed with the payment name');
$L['cfg_countries'] = array('Countries', 'Please select the countries for which this payment method applies. 
    If no country is selected, this payment method will be applied for all countries');
$L['cfg_payment_currency'] = array('Accepted Currency', 'Accepted Currency for this payment');
$L['cfg_min_amount'] = array('Minimum Amount', 'Minimum Order Amount to offer this Payment');
$L['cfg_max_amount'] = array('Maximum Amount', 'Maximum Order Amount to offer this Payment');
$L['cfg_pirice'] = '<hr />';
$L['cfg_cost_per_transaction'] = array('Fee per transaction', 'Flat amount to apply per transaction');
$L['cfg_cost_percent_total'] = array('Percent of the total amount', 'Percent to apply to the total amount');
$L['cfg_tax_id'] = array('Tax', 'Tax to apply to the fee');
$L['cfg_payment_info'] = array('Payment Info', 'Payment Extra Info');
?>