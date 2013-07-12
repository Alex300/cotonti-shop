<!-- BEGIN: MAIN -->
<div class="shopMiniCart block">
    <h2 class="cart"><i class="icon-shopping-cart"></i> {PHP.L.shop.cart_title}</h2>
    <!-- BEGIN: LIST -->
	<div id="hiddencontainer" style=" display: none; ">
		<div class="miniCart-container">
			<!-- IF {PHP.cfg.shop.mCartShowPrice} == 1 -->
			  <div class="prices" style="float: right;"></div>
			<!-- ENDIF -->
			<div class="product_row">
				<span class="quantity"></span>&nbsp;x&nbsp;<span class="product_name"></span>
			</div>

			<div class="product_attributes"></div>
		</div>
	</div>
    <div class="shop_cart_products" <!-- IF {TOTAL_PRODUCT} -->style="border-bottom: 1px dotted"<!-- ENDIF -->>
		<div class="miniCart-container">
		<!-- BEGIN: ROW -->
			<!-- IF {PHP.cfg.shop.mCartShowPrice} == 1 -->
				  <div class="prices" style="float: right;">{ROW_PRICE}</div>
			<!-- ENDIF -->
			<div class="product_row">
				<span class="quantity">{ROW_QUANTITY}</span>&nbsp;x&nbsp;
                <span class="product_name"><a href="{ROW_URL}">{ROW_TITLE}</a></span>
			</div>
			<!-- IF {ROW_ATTRIBUTES} -->
				<div class="product_attributes">{ROW_ATTRIBUTES}</div>
            <!-- ENDIF -->
		<!-- END: ROW -->
		</div>
	</div>
    <!-- END: LIST -->

    <div class="total" style="float: right;">
        <!-- IF {TOTAL_PRODUCT} -->
        {PHP.L.shop.cart_total}: <strong>{BILL_TOTAL}</strong>
        <!-- ENDIF -->
    </div>
    <div class="total_products">{TOTAL_PRODUCT_TXT}</div>
    <div class="show_cart">
        <!-- IF {TOTAL_PRODUCT} -->
        <a style ="float:right;" href="{PHP|cot_url('shop', 'm=cart')}">{PHP.L.shop.cart_show}</a>
        <!-- ENDIF -->
    </div>
    <div style="clear:both;"></div>

    <noscript>
        {PHP.L.shop.ajax_cart_plz_wait}
    </noscript>
</div>
<!-- END: MAIN -->