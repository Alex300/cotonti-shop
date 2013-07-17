
--
-- Table structure for table `cot_shop_currencies`
--
CREATE TABLE IF NOT EXISTS `cot_shop_currencies` (
  `curr_id` smallint(1) UNSIGNED NOT NULL AUTO_INCREMENT,
  `vendor_id` smallint(1) UNSIGNED NOT NULL DEFAULT '1',
  `curr_title` char(64),
  `curr_code_2` char(2),
  `curr_code_3` char(3),
  `curr_numeric_code` int(4),
  `curr_exchange_rate` float,
  `curr_symbol` char(4),
  `curr_decimal_place` char(4),
  `curr_decimal_symbol` char(4),
  `curr_thousands` char(4),
  `curr_positive_style` char(64),
  `curr_negative_style` char(64),
  `curr_order` int(2) NOT NULL DEFAULT '0',
  `curr_shared` tinyint(1) NOT NULL DEFAULT '1',
  `curr_published` tinyint(1) NOT NULL DEFAULT '1',
  `curr_created_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `curr_created_by` int(11) NOT NULL DEFAULT '0',
  `curr_updated_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `curr_updated_by` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`curr_id`),
  KEY `vendor_id` (`vendor_id`),
  KEY `idx_currency_code_3` (`curr_code_3`),
  KEY `idx_currency_numeric_code` (`curr_numeric_code`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Used to store currencies';


-- --------------------------------------------------------
--
-- Table structure for table `cot_shop_calcs`
--
CREATE TABLE IF NOT EXISTS `cot_shop_calcs` (
  `calc_id` int(11)  UNSIGNED NOT NULL AUTO_INCREMENT,
  `vendor_id` int(11)  UNSIGNED NOT NULL DEFAULT '1' COMMENT 'Belongs to vendor',
  `calc_title` varchar(255) NOT NULL DEFAULT '' COMMENT 'Name of the rule',
  `calc_desc` varchar(255) NOT NULL DEFAULT '' COMMENT 'Description',
  `calc_kind` char(16) NOT NULL DEFAULT '' COMMENT 'Discount/Tax/Margin/Commission',
  `calc_value_mathop` char(8) NOT NULL DEFAULT '' COMMENT 'the mathematical operation like (+,-,+%,-%)',
  `calc_value` float NOT NULL DEFAULT '0' COMMENT 'The Amount',
  `curr_id` int(11)  UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Currency of the Rule',
  `calc_shopper_published` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Visible for Shoppers',
  `calc_vendor_published` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Visible for Vendors',
  `calc_publish_up` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Startdate if nothing is set = permanent',
  `calc_publish_down` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Enddate if nothing is set = permanent',
  `calc_for_override` tinyint(1) NOT NULL DEFAULT '0',
  `calc_params` text,
  `calc_order` int(2) NOT NULL DEFAULT '0',
  `calc_shared` tinyint(1) NOT NULL DEFAULT '0',
  `calc_published` tinyint(1) NOT NULL DEFAULT '1',
  `calc_created_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `calc_created_by` int(11) NOT NULL DEFAULT '0',
  `calc_updated_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `calc_updated_by` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`calc_id`),
  KEY `i_vendor_id` (`vendor_id`) 
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


-- --------------------------------------------------------
--
-- Table structure for table `cot_shop_calc_categories`
--
CREATE TABLE IF NOT EXISTS `cot_shop_calc_categories` (
  `calcс_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `calc_id`  int(11) UNSIGNED NOT NULL DEFAULT 0,
  `structure_code` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`calcс_id`),
  UNIQUE KEY `i_calc_id` (`calc_id`,`structure_code`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------
--
-- Table structure for table `cot_shop_calc_groups`
--
CREATE TABLE IF NOT EXISTS `cot_shop_calc_groups` (
  `calcg_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `calc_id`  int(11) UNSIGNED NOT NULL DEFAULT 0,
  `grp_id` int(11) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`calcg_id`),
  UNIQUE KEY `i_calc_id` (`calc_id`,`grp_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------
--
-- Table structure for table `cot_shop_calc_countries`
--
CREATE TABLE IF NOT EXISTS `cot_shop_calc_countries` (
  `calcco_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `calc_id`  int(11) UNSIGNED NOT NULL DEFAULT 0,
  `country` char(2) NOT NULL DEFAULT '',
  PRIMARY KEY (`calcco_id`),
  UNIQUE KEY `i_calc_id` (`calc_id`,`country`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


-- --------------------------------------------------------
--
-- Table structure for table `cot_shop_calc_states`
--
CREATE TABLE IF NOT EXISTS `cot_shop_calc_states` (
  `calcst_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `calc_id`  int(11) UNSIGNED NOT NULL DEFAULT 0,
  `state_id` int(11) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`calcst_id`),
  UNIQUE KEY `i_calc_id` (`calc_id`,`state_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------
--
-- Table structure for table `cot_shop_coupons`
--
CREATE TABLE IF NOT EXISTS `cot_shop_coupons` (
  `coupon_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `coupon_code` char(32) NOT NULL DEFAULT '',
  `coupon_percent_or_total` enum('percent','total') NOT NULL DEFAULT 'percent',
  `coupon_type` enum('gift','permanent') NOT NULL DEFAULT 'gift',
  `coupon_value` decimal(15,5) NOT NULL DEFAULT '0.00000',
  `coupon_vdate` datetime,
  `coupon_edate` datetime,
  `coupon_min_order_total` decimal(15,5) NOT NULL DEFAULT '0.00000',
  `coupon_published` tinyint(1) NOT NULL DEFAULT '1',
  `coupon_created_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `coupon_created_by` int(11) NOT NULL DEFAULT '0',
  `coupon_updated_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `coupon_updated_by` int(11) NOT NULL DEFAULT '0',
   PRIMARY KEY (`coupon_id`),
   KEY `idx_coupon_code` (`coupon_code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 COMMENT='Used to store coupon codes' AUTO_INCREMENT=1;

-- --------------------------------------------------------
--
-- Table structure for table `cot_shop_product_prices`
--
CREATE TABLE IF NOT EXISTS `cot_shop_product_prices` (
  `price_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` int(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'page_id',
  `price_primary` tinyint(1) DEFAULT 1 COMMENT 'is primary price',
  `price_price` decimal(15,5),
  `price_override` tinyint(1),
  `price_override_price` decimal(15,5),
  `price_tax_id` int(11),
  `price_discount_id` int(11),
  `price_currency` MEDIUMINT(3),
  `price_vdate` datetime,
  `price_edate` datetime,
  `price_quantity_start` int(11) unsigned,
  `price_quantity_end` int(11) unsigned,
  `price_created_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `price_created_by` int(11) NOT NULL DEFAULT '0',
  `price_updated_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `price_updated_by` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`price_id`),
  KEY `idx_product_price_product_id` (`product_id`)

) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Holds price records for a product' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------
--
-- Table structure for table `cot_shop_product_prices_groups`
--
CREATE TABLE IF NOT EXISTS `cot_shop_product_prices_groups` (
  `ppg_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `price_id`  int(11) UNSIGNED NOT NULL DEFAULT 0,
  `grp_id` int(11) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`ppg_id`),
  UNIQUE KEY `i_ppriceg_id` (`price_id`,`grp_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------
--
-- Table structure for table `cot_shop_shipmentmethods`
--
CREATE TABLE IF NOT EXISTS `cot_shop_shipmentmethods` (
  `shipm_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `shipm_title` char(180) DEFAULT '',
  `shipm_desc` varchar(255) DEFAULT '',
  `vendor_id` int(11) NOT NULL DEFAULT '1',
  `pl_code` varchar(64) NOT NULL DEFAULT '' COMMENT 'shipment plugin code',
  `shipm_alias` varchar(255) NOT NULL DEFAULT '',
  `shipm_params` text,
  `shipm_order` int(2) NOT NULL DEFAULT '0',
  `shipm_shared` tinyint(1) NOT NULL DEFAULT '0',
  `shipm_published` tinyint(1) NOT NULL DEFAULT '1',
  `shipm_created_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `shipm_created_by` int(11) NOT NULL DEFAULT '0',
  `shipm_updated_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `shipm_updated_by` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`shipm_id`),
	KEY `idx_shipment_pl_code` (`pl_code`),
	KEY `idx_shipm_element` (pl_code,`vendor_id`),
	KEY `idx_shipm_order` (`shipm_order`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Shipment created from the shipment plugins' AUTO_INCREMENT=1 ;


-- --------------------------------------------------------
--
-- Table structure for table `cot_shop_shipmentmethods_groups`
--
CREATE TABLE IF NOT EXISTS `cot_shop_shipmentmethods_groups` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `shipm_id` int(11) UNSIGNED,
  `grp_id` int(11) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `i_shipmentmethod_id` (`shipm_id`,`grp_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='xref table for shipment methods to user groups' AUTO_INCREMENT=1 ;


-- --------------------------------------------------------
--
-- Table structure for table `cot_shop_paymentmethods`
--
CREATE TABLE IF NOT EXISTS `cot_shop_paymentmethods` (
  `paym_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `paym_title` char(180) DEFAULT '',
  `paym_desc` varchar(255) DEFAULT '',
  `vendor_id` int(11) NOT NULL DEFAULT '1',
  `pl_code` varchar(64) NOT NULL DEFAULT '' COMMENT 'payment plugin code',
  `paym_alias` varchar(255) NOT NULL DEFAULT '',
  `paym_params` text,
  `paym_shared` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'valide for all vendors?',
  `paym_order` int(2) NOT NULL DEFAULT '0',
  `paym_published` tinyint(1) NOT NULL DEFAULT '1',
  `paym_created_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `paym_created_by` int(11) NOT NULL DEFAULT '0',
  `paym_updated_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `paym_updated_by` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`paym_id`),
	KEY `idx_payment_pl_code` (`pl_code`),
	KEY `idx_paym_element` (pl_code,`vendor_id`),
	KEY `idx_paym_order` (`paym_order`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='The payment methods of your store created from the payment plugins' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------
--
-- Table structure for table `cot_shop_paymentmethods_groups`
--
CREATE TABLE IF NOT EXISTS `cot_shop_paymentmethods_groups` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `paym_id` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `grp_id` int(11) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `i_paymentmethod_id` (`paym_id`,`grp_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='xref table for payment methods to user groups' AUTO_INCREMENT=1;


-- --------------------------------------------------------
--
-- Table structure for table `cot_shop_orders`
--
CREATE TABLE IF NOT EXISTS `cot_shop_orders` (
  `order_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `vendor_id` int(11) unsigned NOT NULL DEFAULT '0',
  `order_number` varchar(64) DEFAULT NULL,
  `order_pass` varchar(8) DEFAULT NULL,
  `order_total` decimal(15,5) NOT NULL DEFAULT '0.00000',
  `order_salesPrice` decimal(15,5) NOT NULL DEFAULT '0.00000',
  `order_salesPrice_origin` decimal(15,5) NOT NULL DEFAULT '0.00000' COMMENT 'price at the time of ordering',
  `order_billTaxAmount` decimal(15,5) NOT NULL DEFAULT '0.00000',
  `order_billDiscountAmount` decimal(15,5) NOT NULL DEFAULT '0.00000',
  `order_discountAmount` decimal(15,5) NOT NULL DEFAULT '0.00000',
  `order_subtotal` decimal(15,5) DEFAULT NULL,
  `order_subtotal_cost` decimal(15,5) NOT NULL DEFAULT '0.00000' COMMENT 'products cost price in shop currency',
  `order_tax` decimal(10,5) DEFAULT NULL,
  `order_shipment` decimal(10,2) DEFAULT NULL,
  `order_shipment_tax` decimal(10,5) DEFAULT NULL,
  `order_payment` decimal(10,2) DEFAULT NULL,
  `order_payment_tax` decimal(10,5) DEFAULT NULL,
  `coupon_discount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `coupon_code` varchar(32) DEFAULT NULL,
  `order_discount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `order_currency` smallint(1) DEFAULT NULL,
  `order_status` char(1) DEFAULT NULL,
  `order_user_currency_id` char(4) DEFAULT NULL,
  `order_user_currency_rate` decimal(10,5) NOT NULL DEFAULT '1.00000',
  `paym_id` int(11) unsigned DEFAULT NULL,
  `shipm_id` int(11) unsigned DEFAULT NULL,
  `order_customer_note` text,
  `order_ip_address` char(15) NOT NULL DEFAULT '',
  `order_created_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `order_created_by` int(11) NOT NULL DEFAULT '0',
  `order_updated_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `order_updated_by` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`order_id`),
  KEY `idx_orders_user_id` (`user_id`),
  KEY `idx_orders_vendor_id` (`vendor_id`),
  KEY `idx_orders_order_number` (`order_number`),
  KEY `idx_orders_paym_id` (`paym_id`),
  KEY `idx_orders_shipm_id` (`shipm_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Used to store all orders';

-- --------------------------------------------------------
--
-- Table structure for table `cot_shop_order_items`
--
CREATE TABLE IF NOT EXISTS `cot_shop_order_items` (
  `oi_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(11) DEFAULT NULL,
  `vendor_id` int(11) NOT NULL DEFAULT '1',
  `prod_id` int(11) DEFAULT NULL COMMENT 'page_id',
  `prod_sku` varchar(64) NOT NULL DEFAULT '',
  `prod_title` varchar(255) NOT NULL DEFAULT '',
  `prod_quantity` double DEFAULT NULL,
  `prod_price` decimal(15,5) NOT NULL DEFAULT '0.00000' COMMENT 'cost price in shop currency',
  `prod_base_price` decimal(15,5) DEFAULT NULL,
  `prod_tax` decimal(15,5) DEFAULT NULL,
  `prod_basePriceWithTax` decimal(15,5) DEFAULT NULL,
  `prod_sales_price` decimal(15,5) NOT NULL DEFAULT '0.00000',
  `prod_sales_price_origin` decimal(15,5) NOT NULL DEFAULT '0.00000' COMMENT 'original product price at time of ordering',
  `prod_no_coupon_discount` tinyint(1) DEFAULT '0',
  `prod_subtotal_tax` decimal(15,5) DEFAULT NULL,
  `prod_subtotal_discount` decimal(15,5) NOT NULL DEFAULT '0.00000',
  `prod_subtotal_with_tax` decimal(15,5) NOT NULL DEFAULT '0.00000',
  `curr_id` int(11) DEFAULT NULL,
  `order_status` char(1) DEFAULT NULL,
  `prod_attribute` text,
  `oi_created_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `oi_created_by` int(11) NOT NULL DEFAULT '0',
  `oi_updated_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `oi_updated_by` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`oi_id`),
  KEY `idx_order_item_product_id` (`prod_id`),
  KEY `idx_order_item_order_id` (`order_id`),
  KEY `idx_order_item_vendor_id` (`vendor_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Stores all items (products) which are part of an order';

--
-- Table structure for table `cot_shop_order_calc_rules`
--
CREATE TABLE IF NOT EXISTS `cot_shop_order_calc_rules` (
  `ocr_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `vendor_id` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `calc_title`  varchar(255) NOT NULL DEFAULT '' COMMENT 'Name of the rule',
  `calc_kind` char(16) NOT NULL DEFAULT '' COMMENT 'Discount/Tax/Margin/Commission',
  `calc_value` decimal(15,5) NOT NULL DEFAULT '0.00000',
  PRIMARY KEY (`ocr_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Stores all calculation rules which are part of an order' AUTO_INCREMENT=1 ;
--
-- Table structure for table `cot_shop_order_history`
--
CREATE TABLE IF NOT EXISTS `cot_shop_order_history` (
  `oh_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `order_status` char(1),
  `oh_customer_notified` tinyint(1) NOT NULL DEFAULT '0',
  `oh_comment` text,
  `oh_created_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `oh_created_by` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`oh_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Stores all actions and changes that occur to an order' AUTO_INCREMENT=1 ;

--
-- Table structure for table `cot_shop_order_userinfo`
--
CREATE TABLE IF NOT EXISTS `cot_shop_order_userinfo` (
  `oui_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `user_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `ui_id` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `oui_address_type` char(2),
  `oui_address_type_title` char(32),
  `oui_company` varchar(64),
  `oui_title` varchar(32),
  `oui_lastname` varchar(32),
  `oui_firstname` varchar(32),
  `oui_middlename` varchar(32),
  `oui_phone` varchar(24),
  `oui_address` varchar(255) NOT NULL DEFAULT '',
  `oui_city` int(11) UNSIGNED DEFAULT '0',
  `oui_city_name` varchar(255) NOT NULL DEFAULT '',
  `oui_region` int(11) UNSIGNED DEFAULT '0',
  `oui_region_name` varchar(255) NOT NULL DEFAULT '',
  `oui_country` char(2) DEFAULT '',
  `oui_zip` varchar(16) DEFAULT '',
  `oui_email` varchar(64) DEFAULT '',
  `agreed` tinyint(1) NOT NULL DEFAULT '0',
  `oui_created_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `oui_created_by` int(11) NOT NULL DEFAULT '0',
  `oui_updated_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `oui_updated_by` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`oui_id`),
  KEY `i_order_id` (`order_id`),
  KEY `i_user_id` (`user_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Stores the BillTo and ShipTo Information at order time';

-- --------------------------------------------------------
--
-- Table structure for table `cot_shop_order_status`
--
CREATE TABLE IF NOT EXISTS `cot_shop_order_status` (
  `os_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `vendor_id` int(11) NOT NULL DEFAULT '1',
  `os_code` char(1) NOT NULL DEFAULT '',
  `os_title` char(64),
  `os_desc` text,
  `os_stock_handle` char(1) NOT NULL DEFAULT 'A',
  `os_order` int(2) NOT NULL DEFAULT '0',
  `os_published` tinyint(1) NOT NULL DEFAULT '1',
  `os_system` tinyint(1) NOT NULL DEFAULT '0',
  `os_created_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `os_created_by` int(11) NOT NULL DEFAULT '0',
  `os_updated_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `os_updated_by` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`os_id`),
  KEY `idx_order_status_ordering` (`os_order`),
  KEY `idx_order_status_vendor_id` (`vendor_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='All available order statuses';

-- --------------------------------------------------------
--
-- Table structure for table `cot_shop_vendors`
--

CREATE TABLE IF NOT EXISTS `cot_shop_vendors` (
  `vendor_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `vendor_title` varchar(255) DEFAULT '',
  `vendor_alias` varchar(255) DEFAULT '',
  `vendor_desc` varchar(255) DEFAULT '',
  `vendor_text` text DEFAULT '',
  `vendor_legal_info` varchar(255) DEFAULT '',
  `curr_id` int(11) UNSIGNED,
  `vendor_acc_currencies` varchar(1024) NOT NULL DEFAULT '',
  `vendor_params` text,
  `vendor_ownerid` int(11) UNSIGNED DEFAULT 0 COMMENT 'user_id - owner',
  `vendor_created_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `vendor_created_by` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `vendor_updated_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `vendor_updated_by` int(11) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`vendor_id`),
  KEY `idx_vendor_title` (`vendor_title`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Vendors manage their products in your store';


--
-- Table structure for table `cot_shop_userinfo`
--
CREATE TABLE IF NOT EXISTS `cot_shop_userinfo` (
  `ui_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `ui_address_type` char(2) NOT NULL DEFAULT '',
  `ui_address_type_title` char(32) NOT NULL DEFAULT '',
  `ui_title` varchar(255) NOT NULL DEFAULT '',
  `ui_created_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `ui_created_by` int(11) NOT NULL DEFAULT '0',
  `ui_updated_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ui_updated_by` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ui_id`),
  KEY `idx_userinfo_user_id` (`ui_id`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Customer Information, BT = BillTo and ST = ShipTo';


-- --------------------------------------------------------
--
-- Table structure for table `cot_shop_waitingusers`
--
CREATE TABLE IF NOT EXISTS `cot_shop_waitingusers` (
  `wu_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(11) COMMENT 'page_id',
  `user_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `wu_notify_name` varchar(150) DEFAULT '',
  `wu_notify_email` varchar(150) NOT NULL DEFAULT '',
  `wu_notify_phone` varchar(15) DEFAULT '',
  `wu_notified` tinyint(1) DEFAULT '0',
  `wu_notify_date` datetime DEFAULT '0000-00-00 00:00:00',
  `wu_created_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `wu_created_by` int(11) DEFAULT '0',
  `wu_updated_on` datetime DEFAULT '0000-00-00 00:00:00',
  `wu_updated_by` int(11) DEFAULT '0',
  PRIMARY KEY (`wu_id`),
  KEY `product_id` (`product_id`),
  KEY `wu_notify_email` (`wu_notify_email`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Stores notifications, users waiting f. products out of stock';




