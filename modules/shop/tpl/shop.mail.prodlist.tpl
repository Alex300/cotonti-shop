<table class="cells" style="width: 100%">
    <tr style="text-align:center; vertical-align:middle; background-color: #cccccc">
		<td class="coltop width5">&nbsp;</td>
		<td class="coltop">{PHP.L.shop.cart_name}</td>
		<td class="coltop width5">{PHP.L.shop.cart_sku}</td>
		<td class="coltop width10">{PHP.L.shop.cart_price}</td>
        <td class="coltop width15" style="width: 90px;">{PHP.L.shop.cart_quantity}</td>
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
                <a href="{ROW_PROD_URL}"><img src="{PHP.cfg.mainurl}/{ROW_PROD_PROD_ID|att_get('page',$this)|att_thumb($this,60,60, 'crop')}" style="vertical-align: middle" /></a>
            <!-- ENDIF -->
            <!-- IF {ROW_PROD_URL} --><a href="{ROW_PROD_URL}" title="{ROW_PROD_DESC}"><!-- ENDIF -->{ROW_PROD_TITLE}<!-- IF {ROW_PROD_URL} --></a><!-- ENDIF -->
        </td>
		<td class="centerall {ODDEVEN}">{ROW_PROD_SKU}</td>
		<td style="text-align: center">
            <!-- IF {ROW_PROD_BASE_PRICE_WIDTH_TAX} -->
            <span style="text-decoration:line-through">{ROW_PROD_BASE_PRICE_WIDTH_TAX}</span><br />
            <!-- ENDIF -->
            {ROW_PROD_SALES_PRICE}
        </td>
        <td class="centerall {ODDEVEN}" style="text-align:center; vertical-align:middle;">{ROW_PROD_QUANTITY}</td>
        <!-- IF {PHP.cfg.shop.show_tax} -->
        <td style="text-align: center"><span  style='color:gray'>{ROW_PROD_SUBTOTAL_TAX_AMOUNT}</span></td>
        <!-- ENDIF -->
        <td style="text-align: center"><span  style='color:gray'>{ROW_PROD_SUBTOTAL_DISCOUNT}</span></td>
        <td style="text-align: right">{ROW_PROD_SUBTOTAL_WIDTH_TAX}</td>
	</tr>
    <!-- END: ROW -->
    <tr>
        <td colspan="5">&nbsp;</td>
        <!-- IF {PHP.cfg.shop.show_tax} -->
        <td colspan="3"><hr /></td>
        <!-- ELSE -->
        <td colspan="2"><hr /></td>
        <!-- ENDIF -->
    </tr>
    <tr>
        <td colspan="5" align="right">{PHP.L.shop.order_print_product_prices_total}:</td>
        <!-- IF {PHP.cfg.shop.show_tax} -->
        <td align="right"><span  style='color:gray'>{ORDER_TAX_AMOUNT}</span></td>
        <!-- ENDIF -->
        <td align="right"><span  style='color:gray'>{ORDER_DISCOUNT_AMOUNT}</span></td>
        <td align="right">{ORDER_SALES_PRICE}</td>
    </tr>
    
    <!-- IF {ORDER_COUPON_CODE} -->
    <tr>
        <td colspan="5" align="left">{PHP.L.shop.coupon_discount} ({ORDER_COUPON_CODE})</td>
        <!-- IF {PHP.cfg.shop.show_tax} -->
        <td align="right">{ORDER_COUPON_TAX}</td>
        <!-- ENDIF -->
        <td align="right">&nbsp;</td>
        <td align="right">{ORDER_SALES_PRICE_COUPON}</td>
    </tr>
    <!-- ENDIF -->
    
    <!-- BEGIN: ROW_TAX_RULES_BILL -->
	<tr>
        <td class="{ODDEVEN}" colspan="5" align="right">{ROW_TAX_TITLE}</td>
        <!-- IF {PHP.cfg.shop.show_tax} -->
        <td class="{ODDEVEN}" align="right"><!-- IF {ROW_TAX_KIND} != 'DBTaxRulesBill' -->{ROW_TAX_AMOUNT}<!-- ENDIF --></td>
        <!-- ENDIF -->
        <td class="{ODDEVEN}" align="right"><!-- IF {ROW_TAX_KIND} == 'DBTaxRulesBill' -->{ROW_TAX_AMOUNT}<!-- ENDIF --></td>
        <td class="{ODDEVEN}" align="right">{ROW_TAX_AMOUNT}</td>
    </tr>
	<!-- END: ROW_TAX_RULES_BILL -->
    
    <tr>
        <td colspan="5" style="text-align: right; padding-right: 10px;">
            {ORDER_SHIPMENT_TITLE}
            <!-- IF {ORDER_SHIPMENT_DESC} --><em class="grey">({ORDER_SHIPMENT_DESC})</em><!-- ENDIF -->
        </td>
        <!-- IF {PHP.cfg.shop.show_tax} -->
        <td align="right"><span  style='color:gray'>{ORDER_SHIPMENT_TAX}</span></td>
        <!-- ENDIF -->
        <td></td>
        <td align="right">{ORDER_SALES_PRICE_SHIPMENT}</td>
    </tr>
    
    <tr>
        <td colspan="5" style="text-align: right; padding-right: 10px;">
            {ORDER_PAYMENT_TITLE}
            <!-- IF {ORDER_PAYMENT_DESC} --><em class="grey">({ORDER_PAYMENT_DESC})</em><!-- ENDIF -->
        </td>
        <!-- IF {PHP.cfg.shop.show_tax} -->
        <td align="right"><span  style='color:gray'>{ORDER_PAYMENT_TAX}</span></td>
        <!-- ENDIF -->
        <td align="right"></td>
        <td align="right">{ORDER_SALES_PRICE_PAYMENT}</td>
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
        <td colspan="5" style="text-align: right">{PHP.L.shop.cart_total}: </td>
        <!-- IF {PHP.cfg.shop.show_tax} -->
        <td style="text-align: right"><span  style='color:gray'>{ORDER_BILL_TAX_AMOUNT}</span></td>
        <!-- ENDIF -->
        <td style="text-align: right"><span  style='color:gray'>{ORDER_BILL_DISCOUNT_AMOUNT}</span></td>
        <td style="text-align: right"><strong>{ORDER_TOTAL}</strong></td>
    </tr>
    
</table>