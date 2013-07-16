<!-- BEGIN: MAIN -->
<div id="breadcrumbs">{BREAD_CRUMBS}</div>

<h2 class="tags">{PAGE_TITLE}</h2>

{FILE "{PHP.cfg.themes_dir}/{PHP.cfg.defaulttheme}/warnings.tpl"}

<!-- IF {PHP.cfg.shop.oncheckout_show_steps} AND {CHECKOUT_TASK} == 'confirm' -->
<div class="checkoutStep" id="checkoutStep4">{PHP.L.shop.user_form_cart_step4}</div>
<!-- ENDIF -->

<!-- IF {CONTINUE_URL} -->
<div style="text-align: right">
    <a href="{CONTINUE_URL}">{PHP.L.shop.continue_shopping}</a>
</div>
<!-- ENDIF -->

<!-- IF {PHP.usr.id} == 0 -->
{FILE "{PHP.loginTpl}"}
<!-- ENDIF -->


<!-- Реквизиты покупателя -->
<div class="row-fluid margintop10">

    <!-- Покупатель-реквизиты -->
    <div class="span6">
        <p class="strong">
            <img src="/images/icons/default/tags.png" style="vertical-align: middle"/>
            {PHP.L.shop.user_form_billto_lbl}:
        </p>
        <!-- IF {ORDER_BILL_TO.company} -->
        <p class="strong margin0">{ORDER_BILL_TO.company}</p>
        <!-- ENDIF -->
        <!-- IF {ORDER_BILL_TO.firstname} OR {ORDER_BILL_TO.middlename} OR {ORDER_BILL_TO.lastname}  -->
        <p class="strong margin0">{ORDER_BILL_TO.firstname} {ORDER_BILL_TO.middlename} {ORDER_BILL_TO.lastname}</p>
        <!-- ENDIF -->

        <!-- IF {ORDER_BILL_TO.country} OR {ORDER_BILL_TO.city_name} OR {ORDER_BILL_TO.address} -->
            <!-- IF {ORDER_BILL_TO.zip} -->{ORDER_BILL_TO.zip}<!-- ENDIF -->
            {ORDER_BILL_TO.country_name},
            <!-- IF {ORDER_BILL_TO.region_name} -->{ORDER_BILL_TO.region_name},<!-- ENDIF -->
            {ORDER_BILL_TO.city_name}, {ORDER_BILL_TO.address}
        <!-- ENDIF -->

        <table class="table margintop10 small">
        <!-- FOR {KEY}, {VALUE} IN {ORDER_BILL_TO_RAW} -->
        <!-- IF {KEY} != 'agreed' AND {VALUE} != '' AND {KEY} != 'title' AND {KEY} != 'company' AND {KEY} != 'firstname' AND {KEY} != 'middlename' AND {KEY} != 'lastname' AND {KEY} != 'country' AND {KEY} != 'address' AND {KEY} != 'zip' AND {KEY} != 'city_name' AND {KEY} != 'city' AND {KEY} != 'region_name' AND {KEY} != 'region' -->
        <tr>
            <td style="padding: 0 5px">{VALUE.field_title}</td>
            <td style="padding: 0 5px">{VALUE.field_val}</td>
        </tr>
        <!-- ENDIF -->
        <!-- ENDFOR -->
        </table>

        <p class="margintop10">
            <a href="{PHP|cot_url('shop' 'm=user&a=editaddress&addrtype=BT&r=cart')}">
                {PHP.L.shop.user_form_billto_edit}
            </a>
        </p>

        <input type="hidden" name="billto" value="{PHP.cart.lists.billTo}"/>

    </div>

    <!-- адрес доставки -->
    <div class="span6">

        <img src="/images/icons/default/message.png" style="vertical-align: middle"/>
        <strong>{PHP.L.shop.user_form_shipto}:</strong> <em>{ST_TITLE}</em>

        <!-- IF {PHP.cart.shipTo} == 0 -->
            <p>{PHP.L.shop.user_form_edit_billto_explain}</p>
        <!-- ELSE -->
            <p>{ST_SAME_AS_BT}</p>

            <!-- IF {ORDER_SHIP_TO.company} -->
            <p class="strong margin0">{ORDER_SHIP_TO.company}</p>
            <!-- ENDIF -->
            <!-- IF {ORDER_SHIP_TO.firstname} OR {ORDER_SHIP_TO.middlename} OR {ORDER_SHIP_TO.lastname}  -->
                <p class="strong margin0">{ORDER_SHIP_TO.firstname} {ORDER_SHIP_TO.middlename} {ORDER_SHIP_TO.lastname}</p>
            <!-- ENDIF -->

            <!-- IF {ORDER_SHIP_TO.country} OR {ORDER_SHIP_TO.city_name} OR {ORDER_SHIP_TO.address} -->
                <!-- IF {ORDER_SHIP_TO.zip} -->{ORDER_SHIP_TO.zip} <!-- ENDIF -->
                {ORDER_SHIP_TO.country_name},
                <!-- IF {ORDER_SHIP_TO.region_name} -->{ORDER_SHIP_TO.region_name},<!-- ENDIF -->
                {ORDER_SHIP_TO.city_name}, {ORDER_SHIP_TO.address}
            <!-- ENDIF -->

            <table class="table margintop10 small">
                <!-- FOR {KEY}, {VALUE} IN {ORDER_SHIP_TO_RAW} -->
                <!-- IF {KEY} != 'agreed' AND {VALUE} != '' AND {KEY} != 'title' AND {KEY} != 'company' AND {KEY} != 'firstname' AND {KEY} != 'middlename' AND {KEY} != 'lastname' AND {KEY} != 'country' AND {KEY} != 'address' AND {KEY} != 'zip' AND {KEY} != 'city_name' AND {KEY} != 'city' AND {KEY} != 'region_name' AND {KEY} != 'region' -->
                <tr>
                    <td style="padding: 0 5px">{VALUE.field_title}</td>
                    <td style="padding: 0 5px">{VALUE.field_val}</td>
                </tr>
                <!-- ENDIF -->
                <!-- ENDFOR -->
            </table>

        <!-- ENDIF -->

        <p class="margintop10">
            <a id="select_shipto"
               href="{PHP.cart.lists.current_id|cot_url('shop', 'm=user&a=editaddress&addrtype=ST&r=cart&id=$this')}">
                {PHP.L.shop.user_form_shipto_add_edit}
            </a>
        </p>

    </div>

    <div class="clearfix"></div>
</div>
<!-- /Реквизиты покупателя -->

{FILE "{PHP.priceTpl}"}

<!-- TODO Форма оформления заказа -->
<form method="post" id="checkoutForm" name="checkoutForm" action="{CHECKOUT_FORM_ACTION}">

    <div class="margintop10">
        <span class="comment">{PHP.L.shop.leave_comment}:</span><br/>
        <textarea class="customer-comment width100" name="customer_comment">{CHECKOUT_FORM_COMMENT}</textarea>
    </div>

    <!-- IF {PHP.cfg.shop.oncheckout_show_legal_info} == 1 -->
    <h2 class="page" style="margin-top: 15px">{PHP.L.shop.cart_tos}</h2>

    <div class="block">{CHECKOUT_TOS}</div>
    <!-- ENDIF -->

    <div class="checkout-button-top">
        <!-- IF {PHP.cfg.shop.agree_to_tos_onorder} == 1 -->
        {CHECKOUT_FORM_TOS_ACCEPT}
        <!-- ENDIF -->
        {CHECKOUT_FORM_SUBMIT}
    </div>

</form>



<!-- BEGIN: SELECT_SHIPMENT -->
<div id="select_shipment_form" style="display: none">
    <form method="post" id="shipmentForm" name="chooseShipmentRate"
          action="{PHP|cot_url('shop', 'm=cart&a=setshipment')}" class="form-inline">
        <table>
            <!-- BEGIN: ROW -->
            <tr>
                <td class="textleft">
                    <label>
                        <input type="radio" value="{METHOD_ROW_ID}" name="shipmentmethod_id"
                        <!-- IF {SHIPMENT_SELECTED} == {METHOD_ROW_ID} -->checked="checked"<!-- ENDIF --> />
                        <strong>{METHOD_ROW_TITLE}</strong>
                        <!-- IF {METHOD_ROW_DESC} -->
                        <em>({METHOD_ROW_DESC})</em>
                        <!-- ENDIF -->
                    </label>
                </td>
                <td class="textleft">- <strong>{METHOD_ROW_SALES_PRICE}</strong></td>
            </tr>
            <!-- END: ROW -->
        </table>
        <div class="margintop10">
            <input type="hidden" class="scrolltop" name="scroll" value="{SCROLL_TOP}"/>
            <button class="" type="submit">{PHP.L.Submit}</button>
            &nbsp;
            <button class="jqmClose" type="reset" onClick="">{PHP.L.Cancel}</button>
        </div>
    </form>
</div>
<!-- END: SELECT_SHIPMENT  -->

<!-- BEGIN: SELECT_PAYMENT -->
<div id="select_payment_form" style="display: none">
    <form method="post" id="paymentForm" name="choosePaymentRate" action="{PHP|cot_url('shop', 'm=cart&a=setpayment')}"
          class="form-inline">
        <table>
            <!-- BEGIN: ROW -->
            <tr>
                <td class="text-left lhn">
                    <label>
                        <input type="radio" value="{METHOD_ROW_ID}" name="paymentmethod_id"
                        <!-- IF {PAYMENT_SELECTED} == {METHOD_ROW_ID} -->checked="checked" <!-- ENDIF --> />
                        <strong>{METHOD_ROW_TITLE}</strong>
                        <!-- IF {METHOD_ROW_DESC} -->
                        <em>({METHOD_ROW_DESC})</em>
                        <!-- ENDIF -->
                    </label>
                </td>
                <td class="text-left">- <strong>{METHOD_ROW_SALES_PRICE}</strong></td>
            </tr>
            <!-- END: ROW -->
        </table>
        <div class="margintop10">
            <input type="hidden" class="scrolltop" name="scroll" value="{SCROLL_TOP}"/>
            <button class="" type="submit"><i class="icon-ok"></i> {PHP.L.Submit}</button>
            &nbsp;
            <button class="jqmClose" type="reset"><i class="icon-remove"></i> {PHP.L.Cancel}</button>
        </div>
    </form>
</div>
<!-- END: SELECT_PAYMENT -->

<!-- BEGIN: SELECT_SHIPTO -->
<div id="select_shipto_form" style="display: none">
    <form method="post" id="shiptoForm" name="chooseShipToAddress" action="{PHP|cot_url('shop', 'm=cart&a=setshipto')}"
          class="form-inline">
        <table class="width100">
            <!-- BEGIN: ROW -->
            <tr>
                <td class="textleft">
                    <label>
                        <input type="radio" value="{SHIPTO_ROW_ID}" name="shipto_id"
                        <!-- IF {SHIPTO_SELECTED} == {SHIPTO_ROW_ID} -->checked="checked" <!-- ENDIF --> />
                        <strong>{SHIPTO_ROW_TITLE}</strong>
                        <!-- IF {SHIPTO_ROW_DESC} -->
                        <em>({SHIPTO_ROW_DESC})</em>
                        <!-- ENDIF -->
                    </label>
                </td>
                <td class="textleft">
                    <a href="{SHIPTO_ROW_ID|cot_url('shop', 'm=user&a=editaddress&addrtype=ST&r=cart&uiid=$this')}"
                            class="btn btn-small" title="{PHP.L.Edit}"><i class="icon-edit"></i></a>
                </td>
            </tr>
            <!-- END: ROW -->
        </table>
        <div class="margintop10">
            <input type="hidden" class="scrolltop" name="scroll" value="{SCROLL_TOP}"/>
            <a href="{PHP|cot_url('shop', 'm=user&a=editaddress&addrtype=ST&r=cart')}">
                {PHP.L.shop.user_form_shipto_add}
            </a><br/>
            <!-- IF {SHIPTO_RADIO} -->
            <button class="btn" type="submit"><i class="icon-ok"></i> {PHP.L.Submit}</button>
            &nbsp;
            <!-- ENDIF -->
            <button class="jqmClose btn" type="reset">{PHP.L.Cancel}</button>
        </div>
    </form>
</div>
<!-- END: SELECT_SHIPTO -->


<script type="text/javascript">

    $('#select_shipment').click(function () {
        var url = $(this).attr('href');
        $('.scrolltop').val($(window).scrollTop());
        shopDialog({
            text: $('#select_shipment_form').html(),
            title: $('#select_shipment').html(),
            dialogClass: 'help',
            close: (function () {

            })
        })

        return false;
    });

    $('#select_payment').click(function () {
        var url = $(this).attr('href');
        $('.scrolltop').val($(window).scrollTop());
        shopDialog({
            text: $('#select_payment_form').html(),
            title: $('#select_payment').html(),
            dialogClass: 'help',
            close: (function () {

            })
        })

        return false;
    });

    <!-- IF {PHP.usr.id} > 0 -->
    $('#select_shipto').click(function () {
        $('.scrolltop').val($(window).scrollTop());
        shopDialog({
            text: $('#select_shipto_form').html(),
            title: $('#select_shipto').html(),
            dialogClass: 'help',
            close: (function () {

            })
        })

        return false;
    });
    <!-- ENDIF -->

    $(document).ready(function () {
        if ($('.scrolltop').val() > 0) $(window).scrollTop($('.scrolltop').val());
    });

</script>
<!-- END: MAIN -->