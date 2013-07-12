<!-- BEGIN: MAIN -->
<div class="col3-2 first">
    <div class="block">
        <div id="breadcrumb">{BREAD_CRUMBS}</div>
        
		<h2 class="tags">{PAGE_TITLE}</h2>
        
        {FILE "{PHP.cfg.themes_dir}/{PHP.cfg.defaulttheme}/warnings.tpl"}
        
        <!-- IF {PHP.cfg.shop.oncheckout_show_steps} -->
            <div class="checkoutStep" id="checkoutStep2">{PHP.L.shop.user_form_cart_step2}</div>
        <!-- ENDIF -->
        
        <!-- BEGIN: SELECT_SHIPMENT -->
        <form method="post" id="userForm" name="chooseShipmentRate" action="{PHP|cot_url('shop', 'm=cart&a=setshipment')}" class="form-validate">
            <!-- BEGIN: ROW -->
            <p><label><input type="radio" value="{METHOD_ROW_ID}" name="shipmentmethod_id" <!-- IF {SHIPMENT_SELECTED} == {METHOD_ROW_ID} -->checked="checked"<!-- ENDIF --> />
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
        <!-- END: SELECT_SHIPMENT -->
        
        <!-- IF !{FOUND_SHIPMENT_METHOD} -->
            <h3>{PHP.L.shop.cart_no_shipping_method_public}</h3>
            <div class="textright">
            <button class="" type="reset" onClick="window.location.href='{PHP|cot_url('shop', 'm=cart')}'" >{PHP.L.Close}</button>
            </div>
        <!-- ENDIF -->
        
    </div>
</div>
<!-- END: MAIN -->
