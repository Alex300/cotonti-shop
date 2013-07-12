<!-- BEGIN: MAIN -->
<div class="col3-2 first">
    <div class="block">
        <div id="breadcrumb">{BREAD_CRUMBS}</div>
        
		<h2 class="tags">{PAGE_TITLE}</h2>
        
        {FILE "{PHP.cfg.themes_dir}/{PHP.cfg.defaulttheme}/warnings.tpl"}
        
        <!-- IF {PHP.cfg.shop.oncheckout_show_steps} -->
            <div class="checkoutStep" id="checkoutStep3">{PHP.L.shop.user_form_cart_step3}</div>
        <!-- ENDIF -->
        
        <!-- BEGIN: SELECT_PAYMENT -->
        <form method="post" id="userForm" name="choosePaymentRate" action="{PHP|cot_url('shop', 'm=cart&a=setpayment')}" class="">
            <!-- BEGIN: ROW -->
            <p><label><input type="radio" value="{METHOD_ROW_ID}" name="paymentmethod_id" <!-- IF {PAYMENT_SELECTED} == {METHOD_ROW_ID} -->checked="checked" <!-- ENDIF --> />
                <strong>{METHOD_ROW_TITLE}</strong> 
                <!-- IF {METHOD_ROW_DESC} -->
                    <em>({METHOD_ROW_DESC})</em>
                    - <strong>{METHOD_ROW_SALES_PRICE}</strong>
                <!-- ENDIF -->
            </label></p>
            <!-- END: ROW -->
            <div class="margintop10">
            <button class="" type="submit">{PHP.L.Submit}</button> &nbsp;
            <button class="" type="reset" onClick="window.location.href='{PHP|cot_url('shop', 'm=cart')}'" >{PHP.L.Cancel}</button>
            </div>
        </form>
        <!-- END: SELECT_PAYMENT -->
        
        <!-- IF !{FOUND_PAYMENT_METHOD} -->
            <h3>{PHP.L.shop.cart_no_payment_method_public}</h3>
            <div class="textright">
            <button class="" type="reset" onClick="window.location.href='{PHP|cot_url('shop', 'm=cart')}'" >{PHP.L.Close}</button>
            </div>
        <!-- ENDIF -->
        
    </div>
</div>
<!-- END: MAIN -->
