<?php
/* ====================
[BEGIN_COT_EXT]
Code=shop
Name=Shop
Description=RC e-shop for Cotonti Siena
Version=1.0.0
Date=2013-08-11
Author=Alex
Copyright=(с) 2012-2013 Portal30 Studio http://portal30.ru
Auth_guests=R
Lock_guests=12345A
Auth_members=RW
Requires_modules=page,users
Recommends_plugins=autocomplete,regioncity
[END_COT_EXT]

[BEGIN_COT_EXT_CONFIG]
rootCats=01:string::shop:Shop Root Catecory (or comma sep)
manuf_cat=02:string::manufacturers:Manufacturers Root Catecory
use_as_catalog=05:radio::0:Use only as catalogue
display_stock=07:radio::0:Display stock level
coupons_enable=09:radio::0:Enable Coupon Usage
coupons_default_expire=10:select:1_day,1_week,2_weeks,1_month,3_months:1_day:Default Coupon Lifetime
weight_unit_default=10:select:KG,GR,MG,LB,OZ:KG:Default Weight Unit
lwh_unit_default=11:select:M,CM,MM,YD,FT,IN:M:Default LWH Unit

mCartOnShopOnly=15:radio::0:
mCartShowPrice=16:radio::1:
mCartShowProdList=17:radio::1:

currency_converter=19:callback:shop_getCurrencyConverters():convertECB:

checkout=22:separator:::
addtocart_act=23:select:popup,cart,none:popup:
automatic_shipment=24:radio::0:
automatic_payment=25:radio::0:
agree_to_tos_onorder=26:radio::0:
oncheckout_show_legal_info=27:radio::1:
oncheckout_show_register=28:radio::1:
oncheckout_only_registered=30:radio::0:
oncheckout_show_steps=31:radio::0:
notify_if_user_admin=32:radio::1:

outofstock=35:separator:::
lstockmail=36:radio::0:Send low stock notification
stockhandle=37:select:none,disableit,disableit_children,disableadd,risetime:none:
rised_availability=38:string:::Availability

page_extfields=40:separator:::
pextf_min_order_level=42:string::prod_min_order_level:Minimum order level
pextf_max_order_level=43:string::prod_max_order_level:
pextf_sku=44:string::prod_sku:
pextf_unit=45:string::prod_unit:
pextf_manufacturer_id=46:string::prod_manufacturer_id:
pextf_in_stock=47:string::prod_in_stock:
pextf_ordered=48:string::prod_ordered:
pextf_low_stock_notification=49:string::prod_low_stock_notification:
pextf_in_pack=50:string::prod_in_pack:
pextf_order_by_pack=51:string::prod_order_by_pack:
pextf_step=52:string::prod_step:
pextf_allow_decimal_quantity=53:string::prod_allow_decimal_quantity:
pextf_length=54:string::prod_length:
pextf_width=55:string::prod_width:
pextf_height=56:string::prod_height:
pextf_lwh_uom=57:string::prod_lwh_uom:
pextf_weight=58:string::prod_weight:
pextf_weight_uom=59:string::prod_weight_uom:
pextf_no_coupon_discount=60:string::prod_no_coupon_discount:

user_extfields=70:separator:::
uextf_agreed=72:string::agreed:
uextf_company=74:string::company:
uextf_firstname=76:string::firstname:
uextf_middlename=78:string::middlename:
uextf_lastname=80:string::lastname:
uextf_address=81:string::address:
uextf_zip=82:string::zip:
uextf_city=83:string::city:
uextf_city_name=84:string::city_name:
uextf_region=85:string::region:
uextf_region_name=86:string::region_name:
uextf_phone=87:string::phone:

bt_fields_setup=90:separator:::
bt_fields=91:text::company|0,email|1,firstname|1,lastname|1,middlename|0,zip|1,country|1,region_name|1,city_name|0,address|1,phone|1,agreed|1:

showprices=93:separator:::Show Following Prices
show_prices=94:radio::1:Show Prices
show_tax=95:radio::1:Show Tax in Cart
checkout_show_origprice=96:radio::0:Show Origin Price in Cart
askprice=98:radio::0:Show Prices
sbasePrice=99:string::1|1|2:Baseprice
svariantModification=a0:string::1|1|2:Baseprice modificator
sbasePriceVariant=a1:string::1|1|2:New baseprice modified by chosen product variant
sdiscountedPriceWithoutTax=a2:string::1|1|2:Discounted Price without tax
spriceWithoutTax=a3:string::1|1|2:Salesprice without tax
staxAmount=a5:string::1|1|2:Tax amount
sbasePriceWithTax=a5:string::1|0|2:Baseprice with Tax, but without discounts
ssalesPrice=a6:string::1|0|2:Final salesprice
ssalesPriceWithDiscount=a7:string::1|1|2:Salesprice with discount, but without override
sdiscountAmount=a8:string::1|1|2:Discount amount
sunitPrice=a9:string::1|1|2:Standarized price

[END_COT_EXT_CONFIG]
==================== */

/**
 * module shop for Cotonti Siena
 * 
 * @package shop
 * @author Alex
 * @copyright (с) 2012-2013 Portal30 Studio http://portal30.ru (Begin: 2012-01-16)
 */
defined('COT_CODE') or die('Wrong URL');
