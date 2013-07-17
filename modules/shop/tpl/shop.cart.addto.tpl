<!-- BEGIN: MAIN -->

<!-- BEGIN: NOTIFY -->
<div class="notify_form" style="display: none">
    <div class='notify_title'>{PHP.L.shop.notify_me}</div>
    <div class="notify_text textjustify">
        <div class="textjustify" style="Width: 400px;">
            <div>{PHP.L.shop.notify_me_desc}</div>
            <div id="notify_message" style="display: none"></div>
            <table class="notify_form_table">
                <tr>
                    <td>{PHP.L.Name}:</td><td>{NOTIFY_NAME}</td>
                </tr>
                <tr>
                    <td>{PHP.L.Email}:</td><td>{NOTIFY_EMAIL}</td>
                </tr>
                <tr>
                    <td>{PHP.L.shop.phone}:</td><td>{NOTIFY_PHONE}</td>
                </tr>
            </table>
            <div class="textcenter">
                <button class="notify_submit" type="submit">{PHP.L.Submit}</button> &nbsp;
                <button class="jqmClose" type="reset" >{PHP.L.Cancel}</button>
            </div>
        </div>
    </div>
</div>
<!-- END: NOTIFY -->

<div class="addtocart-area">
    <form method="post" class="product js-recalculate form-inline" action="{PHP|cot_url('shop', 'm=cart')}" >
        <div class="addtocart-bar">
            <span class="quantity-box">
                <input type="text" class="quantity-input" name="quantity[]" value="{PROD_MIN_ORDER_LEVEL}" />
            </span>
            <span class="quantity-controls">
                <input type="button" class="quantity-controls quantity-plus" /><input type="button" class="quantity-controls quantity-minus" />
            </span>
            <span class="addtocart-button">
                <button type="submit" name="{ADD_BUTTON_NAME}"  class="btn btn-small btn-primary {ADD_BUTTON_CLS}"
                        title="{ADD_BUTTON_LBL}"><i class="icon-shopping-cart icon-white"></i> {ADD_BUTTON_LBL}</button>
            </span>

            <div style="clear:both"></div>
            <div id="prodTotal_{PROD_ID}" class="price-total desc italic">
                {PHP.L.shop.total}: <span id="Pricetotal_{PROD_ID}">{PROD_PRICE_TOTAL}</span>
            </div>
            <!-- IF {PROD_IN_PACK} > 1 -->
            <div id="prodPacks_{PROD_ID}">{PHP.L.shop.packs_count}: <span id="packsTotal_{PROD_ID}">{PROD_PACKS}</span></div>
            <!-- ENDIF -->

        </div>
        <input type="hidden" name="ptitle" class="pname" value="{PROD_SHORTTITLE}" />
        <noscript><input type="hidden" name="task" value="add" /></noscript>
        <input type="hidden" name="shop_product_id[]" value="{PROD_ID}" />
        <!-- @todo Handle the manufacturer view -->
        <input type="hidden" name="shop_manufacturer_id" value="{PROD_MANUFACTURER_ID}" />
        <input type="hidden" name="in_pack" value="{PROD_IN_PACK}" />
        <input type="hidden" name="unit" value="{PROD_UNIT}" />
        <input type="hidden" name="step" value="{STEP}" />
        <input type="hidden" name="allow_decimal" value="{ALLOW_DECIMAL}" />
        <input type="hidden" name="min_order" value="{PROD_MIN_ORDER_LEVEL}" />
        <input type="hidden" name="shop_category_id[]" value="{PROD_CAT}" />
    </form>
</div>
<!-- END: MAIN -->