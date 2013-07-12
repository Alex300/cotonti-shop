<?php
/* ====================
[BEGIN_COT_EXT]
Code=ss_weight_countries
Name=Shop - Shipment, By weight, ZIP and countries
Description=Shipment plugin for weight-countries shipments, like regular postal services
Category=shop
Version=1.0.0
Date=2013-08-12
Author=Alex
Copyright=(c) http://portal30.ru 2012
Notes=BSD License
SQL=
Auth_guests=R
Lock_guests=A
Auth_members=R
Lock_members=A
Requires_modules=shop
[END_COT_EXT]

[BEGIN_COT_EXT_CONFIG]
[END_COT_EXT_CONFIG] 

[BEGIN_COT_PLG_CONFIG]
shipment_logos=01:string:::
countries=02:custom:shop_selectbox_countries(false, true, 10):ru:
zip_start=03:string:::
zip_stop=04:string:::
weight_start=05:string:::
weight_stop=06:string:::
weight_unit=07:callback:shop_getWeightUnits():kg:
nbproducts_start=08:string:::
nbproducts_stop=09:string:::
orderamount_start=10:string:::
orderamount_stop=11:string:::
pirice=12:separator:::
cost=13:string:::
package_fee=14:string:::
tax_id=15:custom:shop_selectbox_taxes():0:
free_shipment=16:string:::
[END_COT_PLG_CONFIG] 
==================== */

/**
 * Calculate the price (value, tax_id) of the selected method
 * It is called by the calculator 
 * 
 * В настройках блок [BEGIN_COT_PLG_CONFIG] содержит настройки для магазина. Они используются для создания
 * способов доставки на базе этого плагина и не добавляются в настройки в админке самого плагина.
 * 
 * @package shop
 * @subpackage Plugins - shipment
 * @copyright (с) 2012-2013 Portal30 Studio http://portal30.ru (Begin: 2012-feb-20)
 */
defined('COT_CODE') or die('Wrong URL');
