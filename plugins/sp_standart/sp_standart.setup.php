<?php
/* ====================
[BEGIN_COT_EXT]
Code=sp_standart
Name=Shop - Payment plugin Standard
Description=Standard payment plugin
Category=shop
Version=1.0.0
Date=2013-08-20
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
payment_currency=03:callback:shop_selectbox_acceptedCurrency('payment_currency'):0:
min_amount=04:string:::
max_amount=05:string:::
cost_per_transaction=06:string:::
cost_percent_total=07:string:::
tax_id=08:custom:shop_selectbox_taxes():0:
payment_info=09:text::add a message to display with the order:
[END_COT_PLG_CONFIG] 
==================== */

/**
 * Calculate the price (value, tax_id) of the selected method
 * It is called by the calculator 
 * 
 * В настройках блок [BEGIN_COT_PLG_CONFIG] содержит настройки для магазина. Они используются для создания
 * способов оплаты на базе этого плагина и не добавляются в настройки в админке самого плагина.
 * 
 * @package shop
 * @subpackage Plugins - Payment
 * @copyright (с) 2012-2013 Portal30 Studio http://portal30.ru (Begin: 2012-feb-20)
 */
defined('COT_CODE') or die('Wrong URL');