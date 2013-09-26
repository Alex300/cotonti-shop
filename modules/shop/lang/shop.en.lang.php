<?php
/**
 * module shop for Cotonti Siena
 * 
 * @package shop
 * @version 1.0.0
 * @author Alex
 * @copyright http://portal30.ru
 */
defined('COT_CODE') or die('Wrong URL');
$L['info_desc'] = 'Online shop for Cotonti Siena';


/**
 * Admin
 */
$L['shop']['calc'] = "Tax & Calculation Rules";
$L['shop']['calc_epoint_datax'] = "Price modifier after tax";
$L['shop']['calc_epoint_dataxbill'] = "Price modifier after tax per bill";
$L['shop']['calc_epoint_dbtax'] = "Price modifier before tax";
$L['shop']['calc_epoint_dbtaxbill'] = "Price modifier before tax per bill";
$L['shop']['calc_epoint_pmargin'] = "Price modifier for Profit Margin";
$L['shop']['calc_epoint_tax'] = "Tax per product";
$L['shop']['calc_epoint_taxbill'] = "Tax per bill";
$L['shop']['calc_epoint_vattax'] = 'Vat tax per product';
$L['shop']['calc_kind'] = "Type of Arithmetic Operation";
$L['shop']['calc_value_mathop'] = "Math Operation";
$L['shop']['cats_with_no_calcs'] = "Categories with no calc rules";
$L['shop']['check_updates'] = "Check for updates";
$L['shop']['control_panel'] = "Control Panel";
$L['shop']['countries'] = "Сountries";
$L['shop']['coupons'] = "Сoupons";
$L['shop']['coupon_deleted'] = 'Coupon deleted';
$L['shop']['coupon_expiry'] = 'Coupon Expiry Date';
$L['shop']['coupon_expiry_tip'] = 'Leave empty to never expiry';
$L['shop']['coupon_new'] = "New Coupon";
$L['shop']['coupon_percent'] = 'Percent';
$L['shop']['coupon_percent_total'] = "Percent or Total";
$L['shop']['coupon_start'] = 'Coupon Start Date';
$L['shop']['coupon_total'] ='Total';
$L['shop']['coupon_type'] = 'Coupon Type';
$L['shop']['coupon_type_gift'] = 'Gift Coupon';
$L['shop']['coupon_type_permanent'] = 'Permanent Coupon';
$L['shop']['coupon_type_tip'] = 'A Gift Coupon is deleted after it was used for discounting an order. A permanent coupon
    can be used as often as the shopper wants to.';
$L['shop']['customer_notify'] = 'Notify Shopper?';
$L['shop']['customer_notified'] = 'Shopper Notified?';
$L['shop']['currency_decimal_symbol'] = 'Decimal Symbol';
$L['shop']['currency_decimals'] = 'Number of displayed decimals';
$L['shop']['currency_decimals_tip'] = '(can be 0). Performs rounding if value has different number of decimals';
$L['shop']['currency_edit'] = 'Edit currency';
$L['shop']['currency_exchange_rate'] = 'Exchange Rate';
$L['shop']['currency_format_tip ']= 'For Positive or Negative Format, use <ul><li>{sign} for the algebraic sign, </li><li>{number} for the value and</li><li> {symbol} for the currency </li></ul>as placeholder, all html tags are allowed, except the character &#124; is not allowed <br /> For example <pre><code>&quot;{sign} &lt;b&gt;{number}&lt;/b&gt; {symbol}&quot;</code></pre> will display a price like &quot;+ <b>42,23</b> &#8364;&quot';
$L['shop']['currency_negative_format'] = 'Negative Format';
$L['shop']['currency_new'] = 'Add new currency';
$L['shop']['currency_positive_format'] = 'Positive Format';
$L['shop']['currency_symbol'] = 'Currency Symbol';
$L['shop']['currency_thousands'] = 'Thousands Separator';
$L['shop']['currency_title'] = 'Currency name';
$L['shop']['documentation'] = 'Documentation';
$L['shop']['documentation_url'] = '';
$L['shop']['extfields_added'] = 'extra fields added';
$L['shop']['extfields_skipped'] = 'extra fields skipped';
$L['shop']['from_lang_file'] = 'From lang-file';
$L['shop']['include_comment'] = 'Include this Comment?';
$L['shop']['module_homepage_url'] = "http://www.cotonti.com/extensions/commerce/608";
$L['shop']['module_version'] = "Module version";
$L['shop']['never'] = "Never";
$L['shop']['numeric_code'] = 'Numeric Code';
$L['shop']['order'] = "Order";
$L['shop']['orders'] = "Orders";
$L['shop']['order_min_total'] = "Minimum Total Order";
//$L['shop']['order_history_date_added'] ="Date Added";
$L['shop']['order_pass'] = "Secret Key";
$L['shop']['order_update_status'] = "Update Status";
$L['shop']['order_updated_success'] = "Order have been updated";
$L['shop']['order_statuses'] = 'Order Statuses';
$L['shop']['price_modifier'] = 'Price modifier';
$L['shop']['stock_handle'] = 'Stock handling';
$L['shop']['stock_handle_A'] = 'Is available';
$L['shop']['stock_handle_O'] = 'Is removed';
$L['shop']['stock_handle_R'] = 'Is reserved';
$L['shop']['stock_handle_tip'] = 'Choose the movement of stock to make when changing order status.<br />Reserved Stocks
    are deducted from the Stock to sale, but are in Stock';
$L['shop']['titletooshort'] = 'The title is too short or missing';
$L['shop']['update_linestatus'] ='Update status for all lines?';
$L['shop']['user_account_here'] = 'Personal users accounts are here';
$L['shop']['visible_for_shopper'] = "Visible for shopper";
$L['shop']['visible_for_vendor'] = "Visible for vendor";
$L['shop']['order_alredy_present'] = 'Product «%1$s» alredy present in order';

/**
 * Main
 */
$L['shop']['accepted_currencies'] = "List of accepted currencies";
$L['shop']['accepted_currencies_desc'] = "The buyer may see the product prices in these currencies";
$L['shop']['address'] = "Address";
$L['shop']['address_2'] = "Address 2";
$L['shop']['ajax_cart_plz_wait'] = "Please wait";
$L['shop']['cart_action'] = "Update";
$L['shop']['cart_add_to'] = 'Add to Cart';
$L['shop']['cart_add_notify'] = 'Notify Me';
$L['shop']['cart_change_payment'] = "Change Payment";
$L['shop']['cart_change_shipping'] = "Change Shipment";
$L['shop']['cart_checkout_done_confirm_order'] = "Checkout done, please confirm the order";
$L['shop']['cart_confirm'] = "Confirm cart";
$L['shop']['cart_data_not_valid'] = "Invalid data entered";
$L['shop']['cart_delete'] = "Delete Product From Cart";
$L['shop']['cart_edit_coupon'] = "Edit coupon";
$L['shop']['cart_edit_payment'] = "Select payment";
$L['shop']['cart_edit_shipping'] = "Select shipment";
$L['shop']['cart_empty_cart'] = "Cart empty";
$L['shop']['cart_error_no_product_ids'] = "Error while adding product in cart: no product ids";
$L['shop']['cart_error_no_valid_quantity'] = "Please enter a valid quantity for this item.";
$L['shop']['cart_max_order'] = 'The maximum order level for this product is %d items.';
$L['shop']['cart_min_order'] = 'The minimum order level for this product is %d items.';
$L['shop']['cart_name'] = "Name";
$L['shop']['cart_no_payment_method_public'] = "We are sorry, no payment method matches the characteristics of your order.";
$L['shop']['cart_no_payment_selected'] = "No payment selected";
$L['shop']['cart_no_product'] = "There are no products in your cart.";
$L['shop']['cart_no_shipment_selected'] = "No shipment selected";
$L['shop']['cart_no_shipping_method_public'] = "We are sorry, no shipment method matches the characteristics of your
    order.";
$L['shop']['cart_one_product'] = "1 product";
$L['shop']['cart_only_registered'] = "Please register to checkout";
$L['shop']['cart_overview'] = "Shopping cart";
$L['shop']['cart_sku'] = "SKU";
$L['shop']['cart_payment'] = "Payment";
$L['shop']['cart_please_accept_tos'] = "Please accept the terms of service to confirm";
$L['shop']['cart_price'] = "Price";
$L['shop']['cart_price_free'] = "Free";
$L['shop']['cart_price_per_unit'] = "Price per Unit";
$L['shop']['cart_product_added'] = "The product was added to your cart.";
$L['shop']['cart_product_updated'] = "The product quantity has been updated.";
$L['shop']['cart_quantity'] = "Quantity";
$L['shop']['cart_selectpayment'] = "Select payment method";
$L['shop']['cart_selectshipment'] = "Select shipment";
$L['shop']['cart_shipping'] = "Shipment";
$L['shop']['cart_show'] = "Show Cart";
$L['shop']['cart_subtotal_discount_amount'] = "Discount";
$L['shop']['cart_subtotal_tax_amount'] = "Tax";
$L['shop']['cart_title'] = "Cart";
$L['shop']['cart_thankyou'] = "Thank you for your Order!";
$L['shop']['cart_tos'] = "Terms of service";
$L['shop']['cart_tos_read_and_accepted'] = "Please read and accept the terms of service.";
$L['shop']['cart_total'] = "Total";
$L['shop']['cart_total_payment'] = "Total in Payment Currency";
$L['shop']['cart_update'] = "Update Quantity In Cart";
$L['shop']['cart_x_products']= "%s products";
$L['shop']['checkout_as_guest']= "Checkout as Guest";
$L['shop']['checkout_please_enter_address']= "Please enter your billto address";
$L['shop']['city'] = "City";
$L['shop']['comment'] = "Comment";
$L['shop']['company'] = "Company name";
$L['shop']['conf_warn_no_currency_defined'] = "No Shop Currency defined!";
$L['shop']['conf_warn_no_format_defined'] = "Currency is not formatted!";
$L['shop']['continue_shopping'] = "Continue Shopping";
$L['shop']['coupon_code'] = 'Coupon Code';
$L['shop']['coupon_code_change'] = "Change your Coupon code";
$L['shop']['coupon_code_enter'] = "Enter your Coupon code";
$L['shop']['coupon_code_expired'] = 'This coupon is expired';
$L['shop']['coupon_code_notyet'] = 'Coupon is not yet active, it can be used after %1$s';
$L['shop']['coupon_code_tolow'] = 'This coupon is valid for an order with a minimum of %1$s';
$L['shop']['coupon_discount'] = "Coupon Discount";
$L['shop']['coupon_notfound'] = 'Coupon code not found. Please try again.';
$L['shop']['currency'] = "Currency";
$L['shop']['currency_select'] = "Select currency";
$L['shop']['customer'] = "Customer";
$L['shop']['discount'] = "Discount";
$L['shop']['default_vendor_currency'] = "Default Vendor Currency";
$L['shop']['height'] = 'Product Height';
$L['shop']['last_modified'] = "Last Modified";
$L['shop']['leave_comment'] = "Leave a Comment";
$L['shop']['lenght'] = 'Product Length';
$L['shop']['login_form'] = "When you are already registered, please login";
$L['shop']['manufacturer'] = "Manufacturer";
$L['shop']['minicart_added_js'] = " was added to your cart.";
$L['shop']['minicart_error_js'] = "There was an error while updating your cart.";
$L['shop']['missing_value_for_field'] = "Missing value for %s";
$L['shop']['mobile_phone'] = "Mobile Phone";
$L['shop']['my_orders'] = 'My orders';
$L['shop']['no_payment_methods_configured'] = 'No payment method has been configurated%1$s';
$L['shop']['no_payment_methods_configured_link'] = ', please visit %1$s';
$L['shop']['no_payment_plugins_installed'] = "There are no payment plugins installed, please download and install them.";
$L['shop']['no_price_set'] = "No price set";
$L['shop']['no_shipment_plugins_installed'] = 'There are no shipment plugins installed, please download and install them.';
$L['shop']['no_shipping_methods_configured'] = 'No shipment method has been configurated %1$s';
$L['shop']['no_shipping_methods_configured_link'] = ', please visit %1$s';
$L['shop']['notify_me'] = "Notify Me";
$L['shop']['notify_me_desc'] = 'We regret to inform you that this product (<b>%1$s</b>) is either out of stock or
    insufficient in stock for your order. Please submit your email address if you would like to be notified when new
    stock arrives for this product.<br /><br />Thank you!';
$L['shop']['oncheckout_default_text_register'] = "Please use <strong>Register And Checkout</strong> to easily get access to your order history, or use <strong>Checkout as Guest</strong>";
$L['shop']['order_confirm_mnu'] = "Confirm Order";
$L['shop']['order_create_date'] = "Order Date";
$L['shop']['order_deleted'] = 'Order id# %1$s deleted';
$L['shop']['order_id'] = "Order ID";
$L['shop']['order_info'] = "Order Informations";
$L['shop']['order_items'] = "Order Items";
$L['shop']['order_number'] = "Order number";
$L['shop']['order_notfound'] = "Order not found! It may have been deleted.";
$L['shop']['order_pay_description'] = 'Payment Orderа № %1$s - %2$s';
$L['shop']['order_print_product_prices_total'] = "Product prices result";
$L['shop']['order_status'] = "Order Status";
$L['shop']['order_total'] = "Order Total";
$L['shop']['packs_count'] = "Packages count";
$L['shop']['pay'] = 'Pay';
$L['shop']['payment_description'] = "Payment Description";
$L['shop']['payment_method'] = "Payment Method";
$L['shop']['payment_methods'] = "Payment Methods";
$L['shop']['payment_title'] = "Payment Name";
$L['shop']['payment_plugin'] = "Payment plugin";
$L['shop']['phone'] = "Phone";
$L['shop']['pls_configure'] = "Please configure";
$L['shop']['plug_not_installed_but_used'] = 'Plugin &laquo;%1$s&raquo; Not installed, but used in &laquo;%2$s&raquo;';
$L['shop']['products'] = "Products";
$L['shop']['product_addprice'] = "price will be";
$L['shop']['product_addprice_from'] = "In ordering product amount from";
$L['shop']['product_addprice_to'] = "to";
$L['shop']['product_baseprice'] = "Base price";
$L['shop']['product_baseprice_variant'] = "Base price for variant";
$L['shop']['product_baseprice_withtax'] = "Base price with tax";
$L['shop']['product_dim'] = 'Product Dimensions and Weight';
$L['shop']['product_discoun_amount'] = "Discount";
$L['shop']['product_discount_override'] = 'Override';
$L['shop']['product_discount_override_tip'] = "You can use this to temporarly discount a product";
$L['shop']['product_discounted_price'] = "Price with discount";
$L['shop']['product_form_add_prices'] = "Additional prices";
$L['shop']['product_form_calc_base_price'] = 'Calculate the costprice';
$L['shop']['product_form_calc_base_price_tip'] = 'Check this to calculate the costprice with the desired final price';
$L['shop']['product_form_in_stock'] = "In Stock";
$L['shop']['product_form_max_quantity'] = "Max quantity";
$L['shop']['product_form_min_quantity'] = "Min quantity";
$L['shop']['product_form_max_order'] = "Maximum Purchase Quantity";
$L['shop']['product_form_min_order'] = "Minimum Purchase Quantity";
$L['shop']['product_form_ordered_stock'] = "Booked, ordered products";
$L['shop']['product_form_prices'] = "Product pricing";
$L['shop']['product_form_price_base_tip'] = "The base price is the cost price converted into vendor default currency";
$L['shop']['product_form_price_cost'] = "Cost price";
$L['shop']['product_form_price_cost_tip'] = "This is actual cost price in the currency selected";
$L['shop']['product_form_price_final'] = "Final price";
$L['shop']['product_form_price_final_tip'] = "The final price is the baseprice with all affecting rules applied in vendor default currency";
$L['shop']['product_form_override_final'] = 'Overwrite final';
$L['shop']['product_form_override_to_tax'] = 'Overwrite price to be taxed';
$L['shop']['product_form_rules_overrides'] = "Pricing rules overrides";
$L['shop']['product_form_step'] = "Step";
$L['shop']['product_form_step_tip'] = 'The value that is added to the product quantity by clicking +/-.
    Default 1.<br />If decimal, user can order a decimal product amount';
$L['shop']['product_not_found'] = "The requested product does not exist.";
$L['shop']['product_out_of_stock'] = "Product out of stock";
$L['shop']['product_out_of_quantity'] = 'Max quantity reached, new quantity set to %s';
$L['shop']['product_out_of_pack'] = 'This product is only sold in packs of %2$s %3$s, new quantity set to %1$s';
$L['shop']['product_order_by_pack'] = "Order by box only?";
$L['shop']['product_packaging'] = "Units in Box";
$L['shop']['product_quantity_corrected'] = 'Product &laquo;%1$s&raquo; can be ordered in step &laquo;%2$s&raquo;.
 Product quantity is set to &laquo;%3$s&raquo;';
$L['shop']['product_quantity_corrected_min'] = 'Product &laquo;%1$s&raquo; can be ordered in step &laquo;%2$s&raquo; starting
 from &laquo;%3$s&raquo;.  Product quantity is set to &laquo;%4$s&raquo;';
$L['shop']['product_quantity_error'] = "Product quantity not successfully updated";
$L['shop']['product_quantity_success'] = "Product quantity successfully updated";
$L['shop']['product_remove_error'] = "Product not successfully removed";
$L['shop']['product_removed'] = "Product successfully removed";
$L['shop']['product_tax_amount'] = "Tax amount";
$L['shop']['product_tax_no_special'] = "Apply default rules";
$L['shop']['product_tax_none'] = "Apply no rule";
$L['shop']['product_salesprice'] = "Sales price";
$L['shop']['product_salesprice_widthout_tax'] = "Sales price without tax";
$L['shop']['product_salesprice_width_discount'] = "Salesprice with discount";
$L['shop']['product_unit'] = 'Product Unit';
$L['shop']['product_variant_mod'] = "Variant price modifier";
$L['shop']['published'] = "Published";
$L['shop']['receipt_goods'] = 'Receipt goods';
$L['shop']['register_and_checkout'] = "Register And Checkout";
$L['shop']['request_accepted'] = 'Your request has been accepted';
$L['shop']['rules_affecting'] = 'Rules Affecting';
$L['shop']['saved'] = "Saved";
$L['shop']['select_payment_method'] = "Please select a Payment Method above,
    and click &laquo;{$L['Submit']}&raquo; button to display the appropriate parameters here.";
$L['shop']['select_shipping_method'] = "Please select a Shipment Method above,
    and click &laquo;{$L['Submit']}&raquo; button to display the appropriate parameters here.";
$L['shop']['shared'] = "Shared";
$L['shop']['shipment_description'] = "Shipment Description";
$L['shop']['shipment_method'] = "Shipment Method";
$L['shop']['shipment_methods'] = "Shipment Methods";
$L['shop']['shipment_plugin'] = "Shipment plugin";
$L['shop']['shipment_title'] = "Shipment Title";
$L['shop']['tax'] = "Tax";
$L['shop']['tax_affecting'] = "Tax Affecting";
$L['shop']['total'] = "Total";
$L['shop']['shop'] = "Shop";
$L['shop']['shopper_info'] = "Bill To";
$L['shop']['user_form_billto'] = "Billing address information";
$L['shop']['user_form_billto_lbl'] = "Bill To";
$L['shop']['user_form_billto_edit'] = "Add/Edit billing address information";
$L['shop']['user_form_billto_as_shipto'] = "Use for the shipto same as billto address";
$L['shop']['user_form_cart_step2'] = "Checkout Step 2";
$L['shop']['user_form_cart_step3'] = "Checkout Step 3";
$L['shop']['user_form_cart_step4'] = "Checkout Step 4";
$L['shop']['user_form_edit_billto_explain'] = "Only in case shipment address is different from billing address,<br />
    click link below";
$L['shop']['user_form_shipto'] = "Ship To";
$L['shop']['user_form_shipto_add'] = "Add shipment address";
$L['shop']['user_form_shipto_add_edit'] = "Add/Edit shipment address";
$L['shop']['user_form_shipto_edit'] = 'Edit shipment address';
$L['shop']['user_guest_checkout'] = "Now you can continue checkout as Guest.";
$L['shop']['vendor'] = "Vendor";
$L['shop']['vendors'] = "Vendors";
$L['shop']['waiting_users'] = "Users waiting for products out of stock";
$L['shop']['waiting_users_notify'] = 'Notify these users now (when you have updated the number of products stock)';
$L['shop']['weight'] = "Product Weight";
$L['shop']['width'] = 'Product Width';
$L['shop']['your_account_details'] = "Your account Details";
$L['shop']['zip'] = "Zip / Postal Code";

/**
 * Order State
 */
$L['shop']['order_P'] = 'Pending';
$L['shop']['order_C'] = 'Confirmed';
$L['shop']['order_X'] = 'Cancelled';
$L['shop']['order_R'] = 'Refunded';
$L['shop']['order_S'] = 'Shipped';
$L['shop']['order_U'] = 'Confirmed by shopper';

/**
 * LWH Units
 */
$L['shop']['LWH_unit_CM']     = 'Centimeters';
$L['shop']['LWH_unit_FT']     = 'Foot';
$L['shop']['LWH_unit_IN']     = 'Inches';
$L['shop']['LWH_unit_M']      = 'Meters';
$L['shop']['LWH_unit_MM']     = 'Millimeters';
$L['shop']['LWH_unit_YD']     = 'Yards';
$L['shop']['LWH_unit_symbol_CM']   = 'cm';
$L['shop']['LWH_unit_symbol_FT']   = 'ft';
$L['shop']['LWH_unit_symbol_IN']   = 'in';
$L['shop']['LWH_unit_symbol_M']    = 'm';
$L['shop']['LWH_unit_symbol_MM']   = 'mm';
$L['shop']['LWH_unit_symbol_YD']   = 'yd';

/**
 * Weight Units
 */
$L['shop']['weight_unit_KG']     = 'Kilogramme';
$L['shop']['weight_unit_GR']     = 'Gramme';
$L['shop']['weight_unit_MG']     = 'Milligramme';
$L['shop']['weight_unit_LB']     = 'Pounds';
$L['shop']['weight_unit_OZ']     = 'Ounce';
$L['shop']['weight_unit_symbol_KG'] = 'Kg';
$L['shop']['weight_unit_symbol_GR'] = 'g';
$L['shop']['weight_unit_symbol_MG'] = 'Mg';
$L['shop']['weight_unit_symbol_LB'] = 'Lb';
$L['shop']['weight_unit_symbol_OZ'] = 'Oz';

/**
 * Mail 
 */
$L['shop']['mail_default_subj'] = '[%3$s], Order Information, %1$s, total %2$s';
$L['shop']['mail_hello'] = "Hello";
$L['shop']['mail_shopper_info_lbl'] = "Information about your order is as follows";
$L['shop']['mail_shopper_comment'] = "Your comment";
$L['shop']['mail_shopper_your_order_link'] = "View your order online";
$L['shop']['mail_shopper_question'] = "If you have any questions, please contact";
$L['shop']['mail_shopper_new_order_confirmed'] = '[%3$s], Confirmed order at %1$s, total %2$s';
$L['shop']['mail_shopper_subj_C'] = '[%3$s], Confirmed order at %1$s, total %2$s';
$L['shop']['mail_shopper_subj_P'] = '[%3$s], Order is pending at %1$s, total %2$s';
$L['shop']['mail_shopper_subj_R'] = '[%3$s], Refunded order by %1$s, total %2$s';
$L['shop']['mail_shopper_subj_S'] = '[%3$s], Shipped order from %1$s, total %2$s';
$L['shop']['mail_shopper_subj_X'] = '[%3$s], Cancelled order by %1$s, total %2$s';

$L['shop']['mail_vendor_order_confimed'] = 'New order confirmed';
$L['shop']['mail_vendor_shopper_comment'] = "The shopper commented the order";
$L['shop']['mail_vendor_new_order_confirmed'] = '[%3$s], Confirmed order by %1$s, total %2$s';
$L['shop']['mail_vendor_subj_P'] = '[%3$s], Pending order by %1$s, total %2$s';
$L['shop']['mail_vendor_subj_C'] = '[%3$s], Confirmed order by %1$s, total %2$s';
$L['shop']['mail_vendor_subj_R'] = '[%3$s], refunded order for %1$s, total %2$s';
$L['shop']['mail_vendor_subj_S'] = '[%3$s], Shipped order for %1$s, total %2$s';
$L['shop']['mail_vendor_subj_X'] = '[%3$s], cancelled order for %1$s, total %2$s';

$L['shop']['mail_wu_notify_subj'] = '%1$s has arrived!';
$L['shop']['mail_wu_notify_text'] = 'Thank you for your patience. Our <b>&laquo;%1$s&raquo;</b> is now in stock and can
    be purchased by following this link %2$s';

/**
 * Module Config
 */
$L['cfg_rootCats'] = array('Shop Categories', 'Page categories, which are root categories of the shop. All
 pages of these categories are shop products. <i>(Category Codes. Comma separated)</i>');
$L['cfg_manuf_cat'] = array('Manufacturer category', 'Pages in this category and it subcategories are manufacturer descriptions.
     <i>(Category code)</i>.');
$L['cfg_use_as_catalog'] = array("Use only as catalogue", 'If you check this, you disable all cart functionalities.');
$L['cfg_display_stock'] = array("Display stock level", 'If enabled the stock level will be displayed in product category
    layout');
$L['cfg_coupons_enable'] = array("Enable Coupon Usage", 'If you enable the Coupon Usage, you allow shoppers to fill in
    Coupon Numbers to gain discounts on their purchase.');
$L['cfg_coupons_default_expire'] = array("Default Coupon Lifetime", 'You can set a default lifetime for coupons here;
    they will expire the given amount of time after creation. This date can be changed per coupon.');
$L['cfg_coupons_default_expire_params'] = array(
    '1_day' => '1 Day',
    '1_week' => '1 Week',
    '2_weeks' => '2 Weeks',
    '1_month' => '1 Month',
    '3_months' => '3 Months',
);
$L['cfg_weight_unit_default'] = array("Default Weight Unit", 'Default Weight Unit used for the products. This value can
    be changed per product');
$L['cfg_weight_unit_default_params'] = array(
    'KG' => $L['shop']['weight_unit_KG'],
    'GR' => $L['shop']['weight_unit_GR'],
    'MG' => $L['shop']['weight_unit_MG'],
    'LB' => $L['shop']['weight_unit_LB'],
    'OZ' => $L['shop']['weight_unit_OZ'],
);
$L['cfg_lwh_unit_default'] = array("Default LWH Unit", 'Set the default unit for your shop');
$L['cfg_lwh_unit_default_params'] = array(
    'CM' => $L['shop']['LWH_unit_CM'],
    'FT' => $L['shop']['LWH_unit_FT'],
    'IN' => $L['shop']['LWH_unit_IN'],
    'M' => $L['shop']['LWH_unit_M'],
    'MM' => $L['shop']['LWH_unit_MM'],
    'YD' => $L['shop']['LWH_unit_YD'],
);
$L['cfg_mCartOnShopOnly'] = array('Show mini cart in the shop only?', 'If disabled, the mini cart displays
     everywhere on the site');
$L['cfg_mCartShowPrice'] = array("Show prices in mini cart", '');
$L['cfg_mCartShowProdList'] = array("Show product list in mini cart", '');
$L['cfg_currency_converter'] = array("Select a currency converter", '');


$L['cfg_checkout'] = array('Checkout');
/**
 * Ошибка в файле system/admin/admin.config.php на строке 361
 * $t->assign('ADMIN_CONFIG_FIELDSET_TITLE', $L['cfg_' . $row['config_name'][0]]);
 * должно быть:
 * $t->assign('ADMIN_CONFIG_FIELDSET_TITLE', $L['cfg_' . $row['config_name']][0]);
 * https://github.com/Cotonti/Cotonti/issues/1029
 */
$L['cfg_c'] = 'Checkout';
$L['cfg_addtocart_act'] = array('Add to cart action');
$L['cfg_addtocart_act_params'] = array(
    'popup' => 'Popup dialog',
    'cart' => 'Redirect to the cart (old shop behavior)',
    'none' => 'Do nothing. Simply add product to cart',
);
$L['cfg_automatic_shipment'] = array('Enable Automatic Selected Shipment?', 'When Automatic Selected Shipment is enabled,
    if only one shipment method is available, then it is preselected.<br />
    If Automatic Selected Shipment is NOT selected, even when there is only one shipment method is available, a new page
    is loaded. It is usefull if the shipment method must validate shipment data entered by the user.');
$L['cfg_automatic_payment'] = array('Enable Automatic Selected Payment?', 'When Automatic Selected Payment is enabled,
    if only one payment method is available, then it is preselected.<br />
    If Automatic Selected Payment is NOT selected, even when there is only one payment method is available, a new page
    is loaded. It is usefull if the payment method must validate payment data entered by the user.');
$L['cfg_agree_to_tos_onorder'] = array('Must agree to Terms of Service on EVERY ORDER?', 'Check if you want a shopper to
    agree to your terms of service on EVERY ORDER (before placing the order).');
$L['cfg_oncheckout_show_legal_info'] = array('Show Terms of Service on the cart/checkout?', 'Store owners are required
    by law to inform their shoppers about return and order cancellation policies in most European countries. So this
    should be enabled in most cases.');
$L['cfg_oncheckout_show_register'] = array('On checkout, ask for registration', 'During the checkout process, the client
    can register');
$L['cfg_oncheckout_only_registered'] = array('Only registered users can checkout', "This option let only registered
    users make a checkout, you should have 'On checkout, ask for registration' enabled");
$L['cfg_oncheckout_show_steps'] = array('Show checkout steps', "");
$L['cfg_notify_if_user_admin'] = array('Send order notification to the shopper if the shopper is Administrator?', "");

$L['cfg_outofstock'] = array('Action when a Product is Out of Stock');
/**
 * Ошибка в файле system/admin/admin.config.php на строке 361
 * $t->assign('ADMIN_CONFIG_FIELDSET_TITLE', $L['cfg_' . $row['config_name'][0]]);
 * должно быть:
 * $t->assign('ADMIN_CONFIG_FIELDSET_TITLE', $L['cfg_' . $row['config_name']][0]);
 * https://github.com/Cotonti/Cotonti/issues/1029
 */
$L['cfg_o'] = 'Action when a Product is Out of Stock';
$L['cfg_lstockmail'] = array('Send low stock notification', 'Sends a low stock notification if products in stock and
    booked are lower than the value set in the product edit');
$L['cfg_stockhandle'] = array('Action when a Product is Out of Stock', "
    none - Products Out of Stock are orderable, no special action<br />
    disableit - Do not Display Product<br />
    disableit_children - Do not Display Product, if child products also out of stock<br />
    disableadd - Displays '{$L['shop']['cart_add_notify']}' instead of '{$L['shop']['cart_add_to']}' button<br />
    risetime - Products Out of Stock are orderable, and the field 'Availability' below is displayed");
$L['cfg_stockhandle_params'] = array(
    'none' => "Products Out of Stock are orderable, no special action",
    'disableit' => 'Do not Display Product',
    'disableit_children' => 'Do not Display Product, if child products also out of stock (in develop)',
    'disableadd' => "Displays '{$L['shop']['cart_add_notify']}' instead of '{$L['shop']['cart_add_to']}' button",
    'risetime' => "Products Out of Stock are orderable, and the field 'Availability' below is displayed",
);
$L['cfg_rised_availability'] = array('Availability', 'Will be displayed when Products Out of stock are orderable. <br />
    e.g.: 24h, 48 hours, 3 - 5 days, On Order.....');


$L['cfg_page_extfields'] = array('Page Extra fields <i>(Values - Extra field titles)</i>');
/**
 * Ошибка в файле system/admin/admin.config.php на строке 361
 * $t->assign('ADMIN_CONFIG_FIELDSET_TITLE', $L['cfg_' . $row['config_name'][0]]);
 * должно быть:
 * $t->assign('ADMIN_CONFIG_FIELDSET_TITLE', $L['cfg_' . $row['config_name']][0]);
 * https://github.com/Cotonti/Cotonti/issues/1029
 */
$L['cfg_p'] = 'Page Extra fields <i>(Values - Extra field titles)</i>';

$L['cfg_pextf_min_order_level'] = array($L['shop']['product_form_min_order'], 'Page Extra field  which contains minimum
    quantity to order product.<br />type: double, default: 1, min: 0');
$L['cfg_pextf_max_order_level'] = array($L['shop']['product_form_max_order'], '0 - no limit<br />type: double, def.: 0,
    min: 0');
$L['cfg_pextf_sku'] = array($L['shop']['cart_sku'], 'Product sku must be unique<br />type: input;');
$L['cfg_pextf_unit'] = array($L['shop']['product_unit'], '(pcs. m2, etc.)<br />type: input;');
$L['cfg_pextf_manufacturer_id'] = array($L['shop']['manufacturer'], 'type: select, def.: 0');
$L['cfg_pextf_in_stock'] = array($L['shop']['product_form_in_stock'], 'type: double,  def.: 0, min: 0');
$L['cfg_pextf_ordered'] = array($L['shop']['product_form_ordered_stock'], 'type: double,  def.: 0, min: 0');
$L['cfg_pextf_low_stock_notification'] = array('Low Stock Notification', 'When stock is less than this value
    (0 - do not notify)<br />type: double,  def.: 0');
$L['cfg_pextf_in_pack'] = array('Units in Box', 'type: double,  def.: 0, min: 0');
$L['cfg_pextf_order_by_pack'] = array($L['shop']['product_order_by_pack'], 'type: checkbox, def.: 0');
$L['cfg_pextf_step'] = array('Step', 'Adding step by pressing the plus<br />type: double,  def.: 0');
$L['cfg_pextf_allow_decimal_quantity'] = array('Allow  add to cart decimal quantity', 'type: checkbox,  def.: 0');
$L['cfg_pextf_length'] = array($L['shop']['lenght'], 'type: double,  def.: 0, min: 0');
$L['cfg_pextf_width'] = array($L['shop']['width'], 'type: double,  def.: 0, min: 0');
$L['cfg_pextf_height'] = array($L['shop']['height'], 'type: double,  def.: 0, min: 0');
$L['cfg_pextf_lwh_uom'] = array('Product LWH Unit', 'type: select, def.: 0');
$L['cfg_pextf_weight'] = array($L['shop']['weight'], 'type: double,  def.: 0, min: 0');
$L['cfg_pextf_weight_uom'] = array('Product Weight Unit', 'type: select, def.: 0');
$L['cfg_pextf_no_coupon_discount'] = array('No coupon discount', 'тип: checkbox,  по-умолч.: 0<br /><br />
    <button id="makePextf">Create all Extra fields</button> Existing Extra fields will not be changed');

$L['cfg_page_extfields'] = array('User Extra fields (Billto info) <i>(Values - Extra field titles)</i>');
/**
 * Ошибка в файле system/admin/admin.config.php на строке 361
 * $t->assign('ADMIN_CONFIG_FIELDSET_TITLE', $L['cfg_' . $row['config_name'][0]]);
 * должно быть:
 * $t->assign('ADMIN_CONFIG_FIELDSET_TITLE', $L['cfg_' . $row['config_name']][0]);
 * https://github.com/Cotonti/Cotonti/issues/1029
 */
$L['cfg_u'] = 'User Extra fields (Billto info) <i>(Values - Extra field titles)</i>';
$L['cfg_uextf_agreed'] = array('I agree to the Terms of Service', 'type: checkbox, def.: 0,');
$L['cfg_uextf_company'] = array($L['shop']['company'], 'type: input');
$L['cfg_uextf_firstname'] = array('First Name', 'type: input');
$L['cfg_uextf_middlename'] = array('Middle Name', 'type: input');
$L['cfg_uextf_lastname'] = array('Last Name', 'type: input');
$L['cfg_uextf_address'] = array('Adress', 'type: inputint');
$L['cfg_uextf_zip'] = array('Zip / Postal Code', 'type: input');
$L['cfg_uextf_city'] = array('City ID', 'type: input');
$L['cfg_uextf_city_name'] = array('City title', 'type: input');
$L['cfg_uextf_region'] = array('State ID', 'type: inputint');
$L['cfg_uextf_region_name'] = array('State title', 'type: input');
$L['cfg_uextf_phone'] = array('Phone', 'type: input<br /><br />
    <button id="makeUextf">Create all Extra fields</button> Existing Extra fields will not be changed');

$L['cfg_bt_fields_setup'] = array('Заполняемые пользователем реквизиты');
/**
 * Ошибка в файле system/admin/admin.config.php на строке 361
 * $t->assign('ADMIN_CONFIG_FIELDSET_TITLE', $L['cfg_' . $row['config_name'][0]]);
 * должно быть:
 * $t->assign('ADMIN_CONFIG_FIELDSET_TITLE', $L['cfg_' . $row['config_name']][0]);
 * https://github.com/Cotonti/Cotonti/issues/1029
 */
$L['cfg_b'] = 'Billto fields filled by Shopper';
$L['cfg_bt_fields'] = array('Shopper Field List', 'format:<br />
    &lt;field_name_1&gt;|&lt;required (1 or 0)&gt;,<br />
    &lt;field_name_2&gt;|&lt;required (1 or 0)&gt;<br /><br />
    Entries are separated by commas. For convenience, you can use word wrap, but the comma - is necessary.<br />
    The fields will be displayed in the order in which they are listed.
    Shipping address fields <a href="/admin.php?m=extrafields&n=cot_shop_userinfo">you can set here</a>.
    It is desirable that the user profile field names and shipping addresses match');

$L['cfg_showprices'] = array('Pricing');
/**
 * Ошибка в файле system/admin/admin.config.php на строке 361
 * $t->assign('ADMIN_CONFIG_FIELDSET_TITLE', $L['cfg_' . $row['config_name'][0]]);
 * должно быть:
 * $t->assign('ADMIN_CONFIG_FIELDSET_TITLE', $L['cfg_' . $row['config_name']][0]);
 * https://github.com/Cotonti/Cotonti/issues/1029
 */
$L['cfg_s'] = 'Pricing';
$L['cfg_show_prices'] = array('Show Prices',
    "Check to show prices. If using catalogue functionality, some don't want prices to appear on pages.");
$L['cfg_show_tax'] = array('Show Tax in Cart', "Display Tax details in Cart");
$L['cfg_checkout_show_origprice'] = array('Show price without discount in Cart', "");
$L['cfg_askprice'] = array("Show call for price, when the price is empty", 'This gives the user the possibility to ask
    you for a price, when you dont like to publish it');
$L['cfg_sbasePrice'] = array('Baseprice',
    "Depending on where you do your profit/margin calculation it is either your cost price or your calculated price.<br />
    format: &lt;Show Price (1 or 0)&gt;|&lt;Show Label (1 or 0)&gt;|&lt;Rounding Digits (integer from 1 to 5)&gt;");
$L['cfg_svariantModification'] = array('Baseprice modificator',
    "The modificator of the baseprice due the chosen product variant<br />
    format: &lt;Show Price (1 or 0)&gt;|&lt;Show Label (1 or 0)&gt;|&lt;Rounding Digits (integer from 1 to 5)&gt;");
$L['cfg_sbasePriceVariant'] = array('New baseprice modified by chosen product variant',
    "The baseprice gets modified by the chosen product variant<br />
    format: &lt;Show Price (1 or 0)&gt;|&lt;Show Label (1 or 0)&gt;|&lt;Rounding Digits (integer from 1 to 5)&gt;");
$L['cfg_sdiscountedPriceWithoutTax'] = array('Discounted Price without tax',
    "This is interesting for Traders and Merchants (B2B)<br />
    format: &lt;Show Price (1 or 0)&gt;|&lt;Show Label (1 or 0)&gt;|&lt;Rounding Digits (integer from 1 to 5)&gt;");
$L['cfg_spriceWithoutTax'] = array('Salesprice without tax',
    "This is interesting for Traders and Merchants (B2B)<br />
    format: &lt;Show Price (1 or 0)&gt;|&lt;Show Label (1 or 0)&gt;|&lt;Rounding Digits (integer from 1 to 5)&gt;");
$L['cfg_staxAmount'] = array('Tax amount', "Shows only the tax<br />
    format: &lt;Show Price (1 or 0)&gt;|&lt;Show Label (1 or 0)&gt;|&lt;Rounding Digits (integer from 1 to 5)&gt;");
$L['cfg_sbasePriceWithTax'] = array('Baseprice with Tax, but without discounts',
    "Useful to show the old price without discount<br />
    format: &lt;Show Price (1 or 0)&gt;|&lt;Show Label (1 or 0)&gt;|&lt;Rounding Digits (integer from 1 to 5)&gt;");
$L['cfg_ssalesPrice'] = array('Final salesprice',
    "This is the price the shopper actually has to pay.<br />
    format: &lt;Show Price (1 or 0)&gt;|&lt;Show Label (1 or 0)&gt;|&lt;Rounding Digits (integer from 1 to 5)&gt;");
$L['cfg_ssalesPriceWithDiscount'] = array('Salesprice with discount, but without override',
    "This is the same as the salesprice, except you used the product specific override option<br />
    format: &lt;Show Price (1 or 0)&gt;|&lt;Show Label (1 or 0)&gt;|&lt;Rounding Digits (integer from 1 to 5)&gt;");
$L['cfg_sdiscountAmount'] = array('Discount amount',
    "Useful for the you save X money<br />
    format: &lt;Show Price (1 or 0)&gt;|&lt;Show Label (1 or 0)&gt;|&lt;Rounding Digits (integer from 1 to 5)&gt;");
$L['cfg_sunitPrice'] = array('Standarized price', "A standarized price for products sold in units, for example in m,l,kg<br />
    format: &lt;Show Price (1 or 0)&gt;|&lt;Show Label (1 or 0)&gt;|&lt;Rounding Digits (integer from 1 to 5)&gt;");