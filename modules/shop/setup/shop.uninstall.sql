-- ---
-- Completely removes shop data
--
-- cot_shop_userinfo   deletes in shop.uninstall.php
-- ---

DROP TABLE IF EXISTS `cot_shop_currencies`, `cot_shop_calcs`, `cot_shop_calc_categories`, `cot_shop_calc_countries`,
 `cot_shop_calc_groups`, `cot_shop_calc_states`, `cot_shop_coupons`, `cot_shop_product_prices`,
 `cot_shop_product_prices_groups`, `cot_shop_shipmentmethods`, `cot_shop_shipmentmethods_groups`,
 `cot_shop_paymentmethods`, `cot_shop_paymentmethods_groups`, `cot_shop_orders`, `cot_shop_order_items`, `cot_shop_vendors`,
 `cot_shop_order_history`, `cot_shop_order_calc_rules`, `cot_shop_order_userinfo`, `cot_shop_waitingusers`,
 `cot_shop_order_status`;
