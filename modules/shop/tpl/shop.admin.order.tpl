<!-- BEGIN: MAIN -->

<!-- IF {PAGE_TITLE} -->
<h2 class="tags">{PAGE_TITLE}</h2>
<!-- ENDIF -->

{PHP.L.Filters}:
<form method="get" action="{LIST_URL}">
    {PHP.L.shop.order_number}: <input type="text" name="fil[order_number]" value="{FILTER_VALUES.order_number}" />
    {PHP.L.shop.customer}: <input type="text" name="fil[customer]" value="{FILTER_VALUES.customer}" />
    {PHP.L.shop.payment_method}: {FILTER_PAYMENT_METHOD}
    {PHP.L.shop.shipment_method}: {FILTER_SHIPMENT_METHOD} <br />
    {PHP.L.shop.order_create_date}: {FILTER_CREATED_FROM} - {FILTER_CREATED_TO}
    {PHP.L.shop.last_modified}: {FILTER_UPDATED_FROM} - {FILTER_UPDATED_TO}<br />
    {PHP.L.Status}: {FILTER_STATUS}
    <div style="text-align: right">
       <input type="hidden" name="m" value="{PHP.m}">
       <input type="hidden" name="n" value="{PHP.n}">
       <!-- IF {LIST_CURRENTPAGE} > 0 --><input type="hidden" name="d" value="{LIST_CURRENTPAGE}"><!-- ENDIF -->
       {PHP.L.adm_sort}: {SORT_BY} {SORT_WAY}
    </div>
    <input type="submit" value="{PHP.L.Show}" />
</form>


<form method="post" action="{LIST_URL}">
<input type="hidden" name ="a" value="masssave" />

<table class="cells">
    <tr>
        <td class="coltop"></td>
        <!--<td class="coltop"><input type="checkbox" onclick="checkAll(20)" value="" name="toggle"></td>-->
        <td class="coltop">{PHP.L.shop.order_number}</td>
        <td class="coltop">{PHP.L.shop.customer}</td>
        <!-- IF 0 == 1 AND {PHP.usr.isadmin} -->
        <!-- Пока отключено -->
		<td class="coltop">{PHP.L.shop.vendor}</td>
        <!-- ENDIF -->
        <td class="coltop">{PHP.L.shop.payment_method}</td>
        <td class="coltop">{PHP.L.shop.order_create_date}</td>
        <td class="coltop">{PHP.L.shop.last_modified}</td>
        <td class="coltop">{PHP.L.Status}</td>
        <td class="coltop">{PHP.L.shop.order_total}</td>
        <td class="coltop">{PHP.L.Open}</td>
        <td class="coltop">{PHP.L.Delete}</td>
        <td class="coltop">ID</td>
    </tr>
    <!-- BEGIN: LIST_ROW -->
    <tr>
        <td class="{LIST_ROW_ODDEVEN} centerall">{LIST_ROW_NUM}</td>
        <!-- <td><input id="cb{LIST_ROW_ID}" type="checkbox" onclick="isChecked(this.checked);" value="25" 
                   name="product_id[]">
        </td>-->
        <td class="{LIST_ROW_ODDEVEN}">
            <a href="{LIST_ROW_URL}">{LIST_ROW_NUMBER}</a>
        </td>
        <td class="{LIST_ROW_ODDEVEN}">
            <a href="{LIST_ROW_USER_PROFILE_URL}">{LIST_ROW_BILL_TO.lastname} {LIST_ROW_BILL_TO.firstname}
           {LIST_ROW_BILL_TO.middlename}</a>
        </td>
        <!-- IF 0 == 1 AND {PHP.usr.isadmin} -->
        <!-- Пока отключено -->
        <td class="{LIST_ROW_ODDEVEN}">{LIST_ROW_VENDOR_ID}</td>
        <!-- ENDIF -->
        <td class="{LIST_ROW_ODDEVEN}">{LIST_ROW_PAYMENT_TITLE}</td>
        <td class="{LIST_ROW_ODDEVEN}">{LIST_ROW_CREATE_DATE}</td>
        <td class="{LIST_ROW_ODDEVEN}">{LIST_ROW_MODIFY_DATE}</td>
        <td class="{LIST_ROW_ODDEVEN}">{LIST_ROW_STATUS_TITLE}</td>
        <td class="{LIST_ROW_ODDEVEN} centerall">{LIST_ROW_TOTAL}</td>
        <td class="{LIST_ROW_ODDEVEN} centerall">
            <a href="{LIST_ROW_ID|cot_url('admin', 'm=shop&n=order&a=edit&id=$this')}"><img src="images/icons/default/arrow-follow.png" /></a>
            
        </td>
        <td class="{LIST_ROW_ODDEVEN} centerall">
            <a href="{LIST_ROW_DELETE_URL}" class="confirmLink"><img src="images/icons/default/delete.png" /></a>
        </td>
        <td class="{LIST_ROW_ODDEVEN} centerall">{LIST_ROW_ID}</td>
    </tr>
    <!-- END: LIST_ROW -->
    
    <!-- IF {LIST_TOTALLINES} == '0' -->
    <tr>
        <td class="odd centerall" colspan="12">{PHP.L.None}</td>
    </tr>
    <!-- ENDIF -->
    
</table>

<!-- IF {LIST_TOTALLINES} > 0 AND 0 -->
        пока убрано
<input type="submit" value="{PHP.L.Submit}" />
<!-- ENDIF -->

</form>

<!-- IF {LIST_CURRENTPAGE} -->
<div class="paging">
{LIST_PAGEPREV}{LIST_PAGINATION}{LIST_PAGENEXT}<span>{PHP.L.Total}: {LIST_TOTALLINES},
    {PHP.L.Onpage}: {LIST_ITEMS_ON_PAGE}</span>
</div>
<!-- ENDIF -->


<!-- END: MAIN -->


<!-- BEGIN: EDIT -->
    <!-- IF {PAGE_TITLE} -->
    <h2 class="tags">{PAGE_TITLE}</h2>
    <!-- ENDIF -->
    
    <!-- BEGIN: FORM -->

        <table class="width100" style="table-layout: fixed;">
            <tr>
                <td class="width50">
                    <table class="cells">
                        <tr>
                            <td class="coltop" colspan="2">{PHP.L.shop.order_info}</td>
                        </tr>

                        <tr>
                            <td class="odd" style="padding: 0 5px"><strong>{PHP.L.shop.order_id}</strong></td>
                            <td style="padding: 0 5px">{ORDER_ID}</td>
                        </tr>
                        <tr>
                            <td class="even" style="padding: 0 5px"><strong>{PHP.L.shop.order_number}</strong></td>
                            <td style="padding: 0 5px">{ORDER_NUMBER}</td>
                        </tr>
                        <tr>
                            <td class="odd" style="padding: 0 5px"><strong>{PHP.L.shop.order_pass}</strong></td>
                            <td style="padding: 0 5px">{ORDER_PASS}</td>
                        </tr>
                        <tr>
                            <td class="even" style="padding: 0 5px"><strong>{PHP.L.shop.order_create_date}</strong></td>
                            <td style="padding: 0 5px">{ORDER_CREATE_DATE}</td>
                        </tr>
                        <tr>
                            <td class="odd" style="padding: 0 5px"><strong>{PHP.L.shop.order_status}</strong></td>
                            <td style="padding: 0 5px">{ORDER_STATUS_TITLE}</td>
                        </tr>
                        <tr>
                            <td class="even" style="padding: 0 5px"><strong>{PHP.L.Ip}</strong></td>
                            <td style="padding: 0 5px">{ORDER_IP}</td>
                        </tr>
                        <!-- IF {PHP.cfg.shop.coupons_enable} == 1 OR {ORDER_COUPON_CODE} != '' -->
                        <tr>
                            <td class="key" style="padding: 0 5px"><strong>{PHP.L.shop.coupon_code}</strong></td>
                            <td style="padding: 0 5px">{ORDER_COUPON_CODE}</td>
                        </tr>
                        <!-- ENDIF -->
                    </table>
                </td>
                <td class="width50">
                    <table class="cells">
                        <tr>
                            <td class="coltop">{PHP.L.Date}</td>
                            <td class="coltop">{PHP.L.shop.customer_notified}</td>
                            <td class="coltop">{PHP.L.shop.order_status}</td>
                            <td class="coltop">{PHP.L.shop.comment}</td>
                        </tr>
                        <!-- BEGIN: HISTORY_ROW -->
                        <tr>
                            <td style="padding: 0 5px">{ORDER_HISTORY_ROW_DATE}</td>
                            <td style="padding: 0 5px">{ORDER_HISTORY_ROW_NOTIFY}</td>
                            <td style="padding: 0 5px">{ORDER_HISTORY_ROW_STATUS}</td>
                            <td style="padding: 0 5px">{ORDER_HISTORY_ROW_COMMENT}</td>
                        </tr>
                        <!-- END: HISTORY_ROW -->
                        <tr>
                            <td colspan="4">
                                <div  style="position: relative;">
                                    <a href="#" id="show_updStatus"><img src="/images/icons/default/prefs.png"
                                        style="vertical-align: middle;" />{PHP.L.shop.order_update_status}</a>
                                    <div style="position: absolute; left: 0; top: 24px; border: #CCCCCC 1px solid;
                                        border-radius: 5px; box-shadow: 3px 3px 4px #969696;" class="jqmWindow"
                                         id="updOrderStatus">
                                        {FILE "{PHP.updStatusTpl}"}
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <table class="width100" style="table-layout: fixed;">
            <tr>
                <td class="width50">
                    <table class="cells">
                        <tr>
                            <td class="coltop" colspan="2">{PHP.L.shop.shopper_info}</td>
                        </tr>
                        <!-- FOR {VALUE} IN {ORDER_BILL_TO_RAW} -->
                            <!-- IF {VALUE.field_name} != 'agreed'  AND {VALUE.field_name} != 'region' AND {VALUE.field_name} != 'city' -->
                                <tr>
                                    <td style="padding: 0 5px">{VALUE.field_title}</td>
                                    <td style="padding: 0 5px">{VALUE.field_val}</td>
                                </tr>
                            <!-- ENDIF -->
                        <!-- ENDFOR -->
                    </table>

                </td>

                <td class="width50">
                    <table class="cells">
                        <table class="cells">
                            <tr>
                                <td class="coltop" colspan="2">{PHP.L.shop.user_form_shipto}:</td>
                            </tr>
                            <!-- FOR {VALUE} IN {ORDER_SHIP_TO_RAW} -->
                            <!-- IF  {VALUE.field_val} != '' AND {VALUE.field_name} != 'region' AND {VALUE.field_name} != 'city' -->
                            <tr>
                                <td style="padding: 0 5px">{VALUE.field_title}</td>
                                <td style="padding: 0 5px">{VALUE.field_val}</td>
                            </tr>
                            <!-- ENDIF -->
                            <!-- ENDFOR -->
                        </table>
                    </table>
                </td>
            </tr>
        </table>

        <h3>{PHP.L.shop.order_items}:</h3>
        <form method="post" id="orderItemForm" name="orderItemForm"
              action="{ORDER_ID|cot_url('admin', 'm=shop&n=order&a=edit&id=$this')}">
            <input type="hidden" name="act" value="update_items" />
            <input type="hidden" name="id" value="{ORDER_ID}" />
        <table class="cells">
            <tr>
                <td class="coltop width5">
                    <!--<input type="checkbox" id="chk_oitems" title="Select All" />--></td>
                <td class="coltop" style="min-width: 30%">{PHP.L.shop.cart_name}</td>
                <td class="coltop width5">{PHP.L.shop.cart_sku}</td>
                <td class="coltop width10">{PHP.L.shop.product_form_price_cost}</td>
                <td class="coltop width10">{PHP.L.shop.product_baseprice}</td>
                <td class="coltop width5">{PHP.L.shop.cart_quantity}</td>
                <td class="coltop">{PHP.L.shop.product_baseprice_withtax}</td>
                <td class="coltop">{PHP.L.shop.product_form_price_final}</td>
                <td class="coltop width5">{PHP.L.shop.tax}</td>
                <td class="coltop width5">{PHP.L.shop.cart_total}<br />{PHP.L.shop.product_form_price_cost}</td>
                <td class="coltop width5">{PHP.L.shop.cart_total}</td>
                <td class="coltop">{PHP.L.shop.order_status}</td>
                <td class="coltop"></td>
            </tr>
            <!-- BEGIN: ROW -->
            <tr>
                <td class="centerall {ODDEVEN}">
                    <!--<input type="checkbox" id="oitems_{ROW_PROD_ID}" name="oitems[{ROW_PROD_ID}]" value="1" />-->
                    {ROW_PROD_NUMBER}
                </td>
                <td class="{ODDEVEN}" style="vertical-align: middle;">{ROW_PROD_TITLE}</td>
                <td class="centerall {ODDEVEN}">{ROW_PROD_SKU}</td>
                <td class="centerall {ODDEVEN}">{ROW_PROD_PRICE}</td>
                <td class="centerall {ODDEVEN}">{ROW_PROD_BASE_PRICE}</td>
                <td class="centerall {ODDEVEN}">{ROW_PROD_QUANTITY}<br />{ROW_EDIT_PROD_QUANTITY_OVERRIDE}</td>
                <td class="centerall {ODDEVEN}">{ROW_PROD_BASE_PRICE_WIDTH_TAX}</td>
                <td class="centerall {ODDEVEN}">{ROW_PROD_SALES_PRICE_ORIGINAL}<br />{ROW_EDIT_PROD_SALES_PRICE_OVERRIDE}</td>
                <td class="centerall {ODDEVEN}">{ROW_PROD_SUBTOTAL_TAX_AMOUNT}</td>
                <td class="centerall {ODDEVEN}">
                    {ROW_PROD_SUBTOTAL}
                        /
                    <!-- Доход по товару -->
                    {ROW_PROD_SUBTOTAL_WIDTH_TAX_MINUS_PRICE}</td>
                <td class="centerall {ODDEVEN}">
                    <!-- IF {ROW_PROD_SUBTOTAL_WIDTH_TAX_ORIGINAL} != {ROW_PROD_SUBTOTAL_WIDTH_TAX} -->
                        <span style="color: #7c7c7c">{ROW_PROD_SUBTOTAL_WIDTH_TAX_ORIGINAL}</span><br />{ROW_PROD_SUBTOTAL_WIDTH_TAX}
                    <!-- ELSE -->
                        {ROW_PROD_SUBTOTAL_WIDTH_TAX_ORIGINAL}
                    <!-- ENDIF -->
                </td>
                <!--<td class="{ODDEVEN}" style="vertical-align: middle;">{ROW_EDIT_PROD_ORDER_STATUS}</td>-->
                <td class="{ODDEVEN}" style="vertical-align: middle;">{ROW_EDIT_PROD_ORDER_STATUS}</td>
                <td class="centerall {ODDEVEN}">
                    <a href="{ROW_DELETE_ITEM_URL}" class="confirmLink"><img src="/images/icons/default/delete.png"></a></td>
            </tr>
            <!-- END: ROW -->

            <tr id="updateOrderItemStatus">
                <td colspan="8">
                    <button type="button" id="addItem_btn">{PHP.L.Add}</button>
                    <button type="submit" id='updOrderStatusOk'>{PHP.L.Submit}</button>
                    <button type="reset" id='updOrderStatusCancel'>{PHP.L.Cancel}</button>
                </td>

                <td colspan="5">&nbsp;</td>
            </tr>
            <tr>
                <td class="textright" style="vertical-align: middle;" colspan="3">
                    <strong>{PHP.L.shop.order_print_product_prices_total}:</strong>
                </td>
                <td class="centerall" style="padding-right: 5px;"></td>
                <td class="centerall" style="padding-right: 5px;"></td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td class="centerall" style="padding-right: 5px;">{ORDER_TAX_AMOUNT}</td>
                <td class="centerall" style="padding-right: 5px;">{ORDER_SUBTOTAL_COST}</td>
                <td class="centerall" style="padding-right: 5px;">
                    <!-- IF {ORDER_SALES_PRICE_ORIGINAL} != {ORDER_SALES_PRICE} -->
                    <div style="color: #7c7c7c">{ORDER_SALES_PRICE_ORIGINAL}</div>{ORDER_SALES_PRICE}
                    <!-- ELSE -->
                        {ORDER_SALES_PRICE_ORIGINAL}
                    <!-- ENDIF -->

                </td>
                <td colspan="2"></td>
            </tr>

            <!-- IF {ORDER_SALES_PRICE_COUPON} != 0 -->
            <tr>
                <td class="textright" style="vertical-align: middle;" colspan="3">
                    <strong>{PHP.L.shop.coupon_discount}:</strong>
                </td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td class="centerall" style="padding-right: 5px;"  colspan="2">- {ORDER_SALES_PRICE_COUPON}</td>
            </tr>
            <!-- ENDIF -->

            <!-- BEGIN: CALC_RULE_ROW -->
            <tr>
                <td class="textright" style="vertical-align: middle;" colspan="3">
                    <strong>{ROW_TAX_TITLE}:</strong>
                </td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td><!-- IF {ROW_TAX_KIND} != 'DBTaxRulesBill' -->{ROW_TAX_AMOUNT}<!-- ENDIF --></td>
                <td>&nbsp;</td>
                <td class="centerall" style="padding-right: 5px;">{ROW_TAX_AMOUNT}</td>
                <td  colspan="2">&nbsp;</td>
            </tr>
            <!-- END: CALC_RULE_ROW -->

            <tr>
                <td class="textright" style="vertical-align: middle;" colspan="3"><strong>{PHP.L.shop.cart_shipping}:</strong></td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td class="centerall" style="padding-right: 5px;">{ORDER_SHIPMENT_PRICE}</td>
                <td class="centerall" style="padding-right: 5px;">{ORDER_SHIPMENT_TAX}</td>
                <td>&nbsp;</td>
                <td class="centerall" style="padding-right: 5px;">{ORDER_SALES_PRICE_SHIPMENT}</td>
                <td  colspan="2">&nbsp;</td>
            </tr>

            <tr>
                <td class="textright" style="vertical-align: middle;" colspan="3"><strong>{PHP.L.shop.payment_method}:</strong></td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td class="centerall" style="padding-right: 5px;">{ORDER_PAYMENT_PRICE}</td>
                <td class="centerall" style="padding-right: 5px;">{ORDER_PAYMENT_TAX}</td>
                <td>&nbsp;</td>
                <td class="centerall" style="padding-right: 5px;">{ORDER_SALES_PRICE_PAYMENT}</td>
                <td  colspan="2">&nbsp;</td>
            </tr>

            <tr>
                <td class="textright" style="vertical-align: middle;" colspan="3"><strong>{PHP.L.shop.cart_total}:</strong></td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td class="centerall" style="padding-right: 5px;">{ORDER_BILL_TAX_AMOUNT}</td>
                <td>&nbsp;</td>
                <td class="centerall" style="padding-right: 5px;"><strong>{ORDER_TOTAL}</strong></td>
                <td  colspan="2">&nbsp;</td>
            </tr>

        </table>
        </form>

        <p>&nbsp;</p>
        <table class="cells paddingtop10" >
            <tr>
                <td class="coltop width50">{PHP.L.shop.shipment_method}</td>
                <td class="coltop width50">{PHP.L.shop.payment_method}</td>
            </tr>
            <tr>
                <td>
                    <strong>{ORDER_SHIPMENT_TITLE}</strong><br />
                    <em>{ORDER_SHIPMENT_DESC}</em>
                    <!-- IF {SHIPMENT_TEXT} -->
                    <hr />
                    {SHIPMENT_TEXT}
                    <!-- ENDIF -->
                </td>
                <td>
                    <div>
                        <strong id="payment_title">{ORDER_PAYMENT_TITLE}</strong><br />
                        <em id="payment_desc">{ORDER_PAYMENT_DESC}</em>
                    </div>
                    <!-- {PAYMENT_SELECT} -->
                    {PAYMENT_SELECT} <button id="save_payment" disabled="disabled">{PHP.L.Submit}</button>
                    <!-- ENDIF -->
                    <!-- IF {PAYMENT_TEXT} -->
                    <hr />
                    {PAYMENT_TEXT}
                    <!-- ENDIF -->
                </td>
            </tr>
        </table>

        <!-- IF {ORDER_FORM_EXTRAFLD} -->
        <form method="post" id="orderItemForm" name="orderItemForm"
              action="{ORDER_ID|cot_url('admin', 'm=shop&n=order&a=edit&id=$this')}">
            <input type="hidden" name="act" value="save" />
            <input type="hidden" name="id" value="{ORDER_ID}" />
            <table class="cells paddingtop10 margintop10" >
                <!-- BEGIN: EXTRAFLD -->
                <tr>
                    <td class="width20">{ORDER_FORM_EXTRAFLD_TITLE}:</td>
                    <td>{ORDER_FORM_EXTRAFLD}</td>
                </tr>
                <!-- END: EXTRAFLD -->
            </table>
            <button type="submit" id='updOrderStatusOk'>{PHP.L.Submit}</button>
        </form>
        <!-- ENDIF -->

        <p>&nbsp;</p>
        <table class="cells paddingtop10" >
            <tr>
                <td class="coltop width50">{PHP.L.shop.mail_vendor_shopper_comment}:</td>
            </tr>
            <tr>
                <td>{ORDER_CUSTOMER_NOTE}</td>
            </tr>
        </table>

    <!-- END: FORM -->

<a href="{ORDER_DELETE_URL}" class="confirmLink button"><img src="images/icons/default/delete.png" style="vertical-align: middle;" />
    {PHP.L.Delete}</a>

<!-- Форма добавления позиции к заказу -->
<style>
    .ac_results {
        z-index: 99999;
    }
</style>
<div style="display: none" id="addItem">

    {PHP.L.Title}, id, {PHP.L.shop.cart_sku}: <input type="text" name="title" value="" class="acomplete" style="width:250px" />

    <div id="addItemInfo" class="textleft" style="display: none">
        {PHP.L.Title}: <h4 id="addItem_title" class="strong"></h4>
        <table class="flat">
            <tr>
                <td class="padding10">
                    ID: <span id="addItem_id"></span><br />
                    {PHP.L.shop.cart_sku}: <span id="addItem_sku"></span><br />
                    {PHP.L.Category}: <span id="addItem_cat"></span><br />
                    {PHP.L.shop.product_form_price_cost}: <span id="addItem_cost"></span><br />
                    {PHP.L.shop.product_baseprice}: <span id="addItem_basePrice"></span><br />
                    {PHP.L.shop.product_form_price_final}: <span id="addItem_salesPrice"></span><br />
                </td>
                <td class="padding10">
                    {PHP.L.shop.manufacturer}: <span id="addItem_manufacturer"></span><br />
                    {PHP.L.shop.product_form_in_stock}: <span id="addItem_inStock"></span><br />
                    {PHP.L.shop.product_form_ordered_stock}: <span id="addItem_ordered"></span><br />
                    {PHP.L.shop.product_form_min_order}: <span id="addItem_min_order"></span><br />
                    {PHP.L.shop.product_form_max_order}: <span id="addItem_max_order"></span><br />
                    {PHP.L.shop.product_form_step}: <span id="addItem_step"></span><br />

                </td>
            </tr>
        </table>

        <form method="post" id="addItemForm" class="product js-recalculate" action="" >
            <table class="flat">
                <tr>
                    <td class="centerall">{PHP.L.shop.product_form_override_final}:</td>
                    <td class="centerall">{PHP.L.shop.cart_quantity}:</td>
                </tr>
                <tr>
                    <td class="centerall"><input type="text" name="finalprice_override" value="" /> </td>
                    <td class="centerall">
                        <!--<div class="addtocart-bar padding0">-->
                            <span class="quantity-box">
                                <input type="text" class="quantity-input" name="quantity" value="{PROD_MIN_ORDER_LEVEL}" />
                            </span>
                            <span class="quantity-controls">
                                <input type="button" class="quantity-controls quantity-plus" />
                                <input type="button" class="quantity-controls quantity-minus margin0" />
                            </span>
                        <!--</div>-->
                    </td>
                </tr>
                <tr>
                    <td colspan="2" class="textright">
                        <div id="prodTotal">{PHP.L.shop.total}: <span id="addItem_total">{PROD_PRICE_TOTAL}</span></div>
                        <!-- IF {PROD_IN_PACK} > 1 -->
                        <div id="prodPacks_{PROD_ID}">{PHP.L.shop.packs_count}: <span id="packsTotal_{PROD_ID}">{PROD_PACKS}</span></div>
                        <!-- ENDIF -->
                    </td>
                </tr>
            </table>
            <input type="hidden" name="ptitle" class="pname" value="" />
            <input type="hidden" name="shop_product_id" value="" />
            <input type="hidden" name="in_pack" value="" />
            <input type="hidden" name="unit" value="" />
            <input type="hidden" name="step" value="" />
            <input type="hidden" name="allow_decimal" value="" />
            <input type="hidden" name="min_order" value="" />
            <input type="hidden" name="shop_category_id" value="" />
        </form>

    </div>
</div>
<!-- END: EDIT -->