<!-- BEGIN: PRODUCTS -->
<table class="cells">
    <tr>
		<td class="coltop width5">&nbsp;</td>
		<td class="coltop">{PHP.L.shop.cart_name}</td>
		<td class="coltop width5">{PHP.L.shop.cart_sku}</td>
		<td class="coltop width10">{PHP.L.shop.cart_price}</td>
        <td class="coltop width15" style="width: 90px;">{PHP.L.shop.cart_quantity} / {PHP.L.shop.cart_action}</td>
        <!-- IF {PHP.cfg.shop.show_tax} -->
        <td class="coltop width5"><span  style='color:gray'>{PHP.L.shop.cart_subtotal_tax_amount}</span></td>
        <!-- ENDIF -->
        <td class="coltop width5"><span  style='color:gray'>{PHP.L.shop.cart_subtotal_discount_amount}</span></td>
        <td class="coltop width5">{PHP.L.shop.cart_total}</td>
	</tr>
    <!-- BEGIN: ROW -->
    <tr>
		<td class="centerall {ODDEVEN}">{ROW_PROD_NUMBER}</td>
		<td class="{ODDEVEN}" style="vertical-align:middle;">
            <!-- IF {ROW_PROD_PROD_ID|att_count('page',$this,'images')} > 0 -->
            <div class="pull-left thumbnail marginright10">
                <a href="{ROW_PROD_URL}"><img src="{ROW_PROD_PROD_ID|att_get('page',$this)|att_thumb($this,60,60, 'crop')}" alt="{ROW_PROD_SHORTTITLE}" /></a>
            </div>
            <!-- ENDIF -->
            <a href="{ROW_PROD_URL}" title="{ROW_PROD_DESC}" class="toolTipR">{ROW_PROD_SHORTTITLE}</a>
        </td>
		<td class="centerall {ODDEVEN}">{ROW_PROD_SKU}</td>
		<td class="centerall {ODDEVEN}">
            <!-- IF {ROW_PROD_BASE_PRICE_WIDTH_TAX} -->
            <span style="text-decoration:line-through">{ROW_PROD_BASE_PRICE_WIDTH_TAX}</span><br />
            <!-- ENDIF -->
            {ROW_PROD_SALES_PRICE}
        </td>
        <td class="centerall {ODDEVEN}" style="white-space: nowrap">
            <form action="{PHP|cot_url('shop', 'm=cart&a=update')}" method="post" class="form-inline margin0">
                <input type="hidden" name="product_id" value="{ROW_PROD_PROD_ID}" />
                <div class="input-append">
                    <input type="text" title="{PHP.L.shop.cart_update}" class="input-mini" size="3" maxlength="4"
                           name="quantity" value="{ROW_PROD_QUANTITY}" />
                    <button type="submit" class="shop-add_quantity_cart" name="update" title="{PHP.L.shop.cart_update}"><i
                                class=" icon-refresh"></i> </button>
                </div>
                <a class="shop-remove_from_cart btn" title="{PHP.L.shop.cart_delete}"
                   href="{ROW_PROD_PROD_ID|cot_url('shop', 'm=cart&a=delete&product_id=$this')}"><i class="icon-trash"></i> </a>
			</form>
		</td>
        <!-- IF {PHP.cfg.shop.show_tax} -->
        <td class="centerall {ODDEVEN} grey">{ROW_PROD_SUBTOTAL_TAX_AMOUNT}</td>
        <!-- ENDIF -->
        <td class="centerall {ODDEVEN} grey">{ROW_PROD_SUBTOTAL_DISCOUNT}</td>
        <td class="centerall {ODDEVEN}">
            <!-- IF {ROW_PROD_SUBTOTAL_WIDTHOUT_DISCOUNT} -->
            <span style="text-decoration:line-through">{ROW_PROD_SUBTOTAL_WIDTHOUT_DISCOUNT}</span><br />
            <!-- ENDIF -->
            {ROW_PROD_SUBTOTAL_WIDTH_TAX}
        </td>
	</tr>
    <!-- END: ROW -->
    
    <!--Begin of SubTotal, Tax, Shipment, Coupon Discount and Total listing -->
    <tr>
        <td colspan="5">&nbsp;</td>
        <!-- IF {PHP.cfg.shop.show_tax} -->
        <td colspan="3"><hr /></td>
        <!-- ELSE -->
        <td colspan="2"><hr /></td>
        <!-- ENDIF -->
    </tr>
    <tr>
        <td colspan="5" class="textright">{PHP.L.shop.order_print_product_prices_total}:</td>
        <!-- IF {PHP.cfg.shop.show_tax} -->
        <td class="textright grey">{ORDER_TAX_AMOUNT}</td>
        <!-- ENDIF -->
        <td class="textright grey">{ORDER_DISCOUNT_AMOUNT}</td>
        <td class="textright">{ORDER_SALES_PRICE}</td>
    </tr>
    
    
    <!-- IF {PHP.cfg.shop.coupons_enable} -->
    <tr>
        <td colspan="5">
            {FILE "{PHP.couponTpl}"}
        <!-- IF {COUPON_CODE} -->
                {COUPON_CODE} {COUPON_DESCR}
        </td>
            <!-- IF {PHP.cfg.shop.show_tax} -->
            <td class="textright">{COUPON_TAX}</td>
            <!-- ENDIF -->
            <td class="textright">&nbsp;</td>
            <td class="textright">{SALES_PRICE_COUPON}</td>
        <!-- ELSE -->
        </td>
        <td colspan="3" align="left">&nbsp;</td>
        <!-- ENDIF -->
    </tr>
    <!-- ENDIF -->

    <!-- BEGIN: ROW_TAX_RULES_BILL -->
	<tr>
        <td class="textright" colspan="5">{ROW_TAX_TITLE}</td>
        <!-- IF {PHP.cfg.shop.show_tax} -->
        <td class="textright"><!-- IF {ROW_TAX_KIND} != 'DBTaxRulesBill' -->{ROW_TAX_AMOUNT}<!-- ENDIF --></td>
        <!-- ENDIF -->
        <td class="textright"><!-- IF {ROW_TAX_KIND} == 'DBTaxRulesBill' -->{ROW_TAX_AMOUNT}<!-- ENDIF --></td>
        <td class="textright">{ROW_TAX_AMOUNT}</td>
    </tr>
	<!-- END: ROW_TAX_RULES_BILL -->
    
    <tr>
        <td colspan="5" class="textleft">
            {ORDER_SHIPMENT_TITLE}
            <!-- IF {ORDER_SHIPMENT_DESC} --><em class="grey">({ORDER_SHIPMENT_DESC})</em><!-- ENDIF -->
           <!-- IF {AUTO_SELECTED_SHIPMENT} == 0 -->
            <br />
            <a id="select_shipment" href="{PHP|cot_url('shop', 'm=cart&a=edit_shipment')}">{ORDER_SELECT_SHIPMENT_TEXT}</a>
            <!-- ENDIF -->
        </td>
        <!-- IF {PHP.cfg.shop.show_tax} -->
        <td class="textright grey">{ORDER_SHIPMENT_TAX}</td>
        <!-- ENDIF -->
        <td></td>
        <td class="textright">{ORDER_SALES_PRICE_SHIPMENT}</td>
    </tr>
    
    <tr>
        <td colspan="5" class="textleft">
            {ORDER_PAYMENT_TITLE}
            <!-- IF {ORDER_PAYMENT_DESC} --><em class="grey">({ORDER_PAYMENT_DESC})</em><!-- ENDIF -->
            <!-- IF {AUTO_SELECTED_PAYMENT} == 0 -->
                <br />
                <a id="select_payment" href="{PHP|cot_url('shop', 'm=cart&a=edit_payment')}">{ORDER_SELECT_PAYMENT_TEXT}</a>
            <!-- ENDIF -->
        </td>
        <!-- IF {PHP.cfg.shop.show_tax} -->
        <td class="textright grey">{ORDER_PAYMENT_TAX}</td>
        <!-- ENDIF -->
        <td>&nbsp;</td>
        <td class="textright">{ORDER_SALES_PRICE_PAYMENT}</td>
    </tr>
  
    <tr>
        <td colspan="5">&nbsp;</td>
        <!-- IF {PHP.cfg.shop.show_tax} -->
        <td colspan="3"><hr /></td>
        <!-- ELSE -->
        <td colspan="2"><hr /></td>
        <!-- ENDIF -->
    </tr>
    
    <tr>
        <td colspan="5" class="textright">{PHP.L.shop.cart_total}: </td>
        <!-- IF {PHP.cfg.shop.show_tax} -->
        <td class="textright grey">{ORDER_BILL_TAX_AMOUNT}</td>
        <!-- ENDIF -->
        <td class="textright grey">{ORDER_BILL_DISCOUNT_AMOUNT}</td>
        <td class="textright strong" style="white-space: nowrap"><strong>{ORDER_TOTAL}</strong></td>
    </tr>
    
    <!-- IF {ORDER_TOTAL_IN_PAYMENT_CURRENCY} -->
    <tr>
        <td colspan="5" style="text-align: right">{PHP.L.shop.cart_total_payment}: </td>
        <!-- IF {PHP.cfg.shop.show_tax} -->
        <td style="text-align: right">  </td>
        <!-- ENDIF -->
        <td style="text-align: right">  </td>
        <td style="text-align: right"><strong>{ORDER_TOTAL_IN_PAYMENT_CURRENCY}</strong></td>
    </tr>
    <!-- ENDIF -->
    
</table>
<!-- END: PRODUCTS -->