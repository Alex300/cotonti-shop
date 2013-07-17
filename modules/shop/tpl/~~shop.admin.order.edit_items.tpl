<!-- BEGIN: MAIN -->
<head>
    <link href="system/admin/tpl/admin.css" type="text/css" rel="stylesheet" />
    <script src="js/jquery.min.js" type="text/javascript"></script>
    <script src="{PHP.cfg.modules_dir}/shop/js/shop_edit_product.js" type="text/javascript"></script>
</head>
<body style="background: none; font-size: 11px !important;">

{FILE "{PHP.cfg.themes_dir}/{PHP.cfg.defaulttheme}/warnings.tpl"}

<h4>{PHP.L.shop.order_update_status}</h4>
<form action="{ORDER_ID|cot_url('admin', 'm=shop&n=order&a=saveItems&id=$this')}" method="post" name="orderItemsForm" id="orderItemsForm">

    <h3>{PHP.L.shop.order_items}:</h3>
    <!-- BEGIN: FORM -->
    <form method="post" id="orderItemForm" name="orderItemForm"
          action="{ORDER_ID|cot_url('admin', 'm=shop&n=order&a=edit&id=$this')}">
        <!--<input type="hidden" name="act" value="update_items" />-->
        <input type="hidden" name="id" value="{ORDER_ID}" />
        <!--<input type="hidden" name="act" value="edit_items" />-->
        <table class="cells">
            <tr>
                <td class="coltop width5">&nbsp;</td>
                <td class="coltop">{PHP.L.shop.cart_name}</td>
                <td class="coltop width5">{PHP.L.shop.cart_sku}</td>
                <td class="coltop width10">{PHP.L.shop.product_form_price_cost}</td>
                <td class="coltop width5">{PHP.L.shop.cart_quantity}</td>
                <td class="coltop">{PHP.L.shop.product_form_price_final}</td>
                <td class="coltop width5">{PHP.L.shop.cart_total}<br />{PHP.L.shop.product_form_price_cost}</td>
                <td class="coltop width5">{PHP.L.shop.cart_total}</td>
                <td class="coltop">{PHP.L.shop.order_status}</td>
            </tr>
            <!-- BEGIN: ROW -->
            <tr>
                <td class="centerall {ODDEVEN}">
                    {ROW_PROD_NUMBER}
                </td>
                <td class="{ODDEVEN}" style="vertical-align: middle;">{ROW_PROD_TITLE}</td>
                <td class="centerall {ODDEVEN}">{ROW_PROD_SKU}</td>
                <td class="centerall {ODDEVEN}">{ROW_PROD_PRICE}</td>
                <td class="centerall {ODDEVEN}">{ROW_PROD_QUANTITY}</td>
                <td class="centerall {ODDEVEN}">{ROW_PROD_FINAL_PRICE}</td>
                <td class="centerall {ODDEVEN}">{ROW_PROD_SUBTOTAL_COST}</td>
                <td class="centerall {ODDEVEN}">{ROW_PROD_SUBTOTAL_WIDTH_TAX}</td>
                <!--<td class="{ODDEVEN}" style="vertical-align: middle;">{ROW_EDIT_PROD_ORDER_STATUS}</td>-->
                <td class="{ODDEVEN}" style="vertical-align: middle;">{ROW_PROD_ORDER_STATUS}</td>
            </tr>
            <!-- END: ROW -->
        </table>
    <!-- END: FORM -->
</form>
</body>
<!-- END: MAIN -->