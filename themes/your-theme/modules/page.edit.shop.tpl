<!-- BEGIN: MAIN -->
<div id="breadcrumbs">
    <a href="{PHP|cot_url('index')}">{PHP.L.Home}</a> {PHP.cfg.separator} {PHP.catpath} {PHP.cfg.separator}
    <a href="{PHP.pag.page_pageurl}">{PHP.pag.page_title}</a> {PHP.cfg.separator} {PAGEEDIT_PAGETITLE}
</div>

<h2 class="page margintop10">{PAGEEDIT_PAGETITLE} #{PAGEEDIT_FORM_ID}: {PHP.pag.page_title}</h2>

{FILE "{PHP.cfg.themes_dir}/{PHP.cfg.defaulttheme}/warnings.tpl"}

<h4 style="margin: 10px 0;">{PAGEEDIT_SUBTITLE}</h4>

<p>{PHP.L.page_pageid}: #{PAGEEDIT_FORM_ID}</p>
<p>{PHP.L.Status}: <strong>{PAGEEDIT_FORM_LOCALSTATUS}</strong></p>

{FILE "{PHP.cfg.themes_dir}/{PHP.cfg.defaulttheme}/warnings.tpl"}

<form action="{PAGEEDIT_FORM_SEND}" enctype="multipart/form-data" method="post" name="pageform"
      class="form-inline form-fullwidth">

<ul class="nav nav-tabs">
    <li class="active"><a href="#prod-info" data-toggle="tab">Информация / описание</a></li>
    <li><a href="#prod-price" data-toggle="tab">Стоимость</a></li>
    <li><a href="#prod-status" data-toggle="tab">{PHP.L.Status} / Покупатели</a></li>
    <li><a href="#prod-dimensions" data-toggle="tab">{PHP.L.shop.product_dim}</a></li>
    <li><a href="#prod-images" data-toggle="tab">Изображения / файлы</a></li>
</ul>
<div class="tab-content">
<!-- Информация / описание -->
<div class="tab-pane fade active in" id="prod-info">
    <table class="table table-striped">
        <tr>
            <td class="width20">{PHP.L.Category}:</td>
            <td class="">{PAGEEDIT_FORM_CAT}</td>
        </tr>
        <tr>
            <td>{PHP.L.Title}:</td>
            <td>{PAGEEDIT_FORM_TITLE}</td>
        </tr>
        <tr>
            <td>{PHP.L.shop.cart_sku}:</td>
            <td>{PAGEEDIT_FORM_PROD_SKU}</td>
        </tr>
        <tr>
            <td>{PHP.L.Description}:</td>
            <td>{PAGEEDIT_FORM_DESC}</td>
        </tr>
        <tr>
            <td>{PHP.L.Date}:</td>
            <td>{PAGEEDIT_FORM_DATE} {PAGEEDIT_FORM_DATENOW} {PHP.L.ant.pagedatenow}</td>
        </tr>
        <tr>
            <td>{PHP.L.Begin}:</td>
            <td>{PAGEEDIT_FORM_BEGIN}</td>
        </tr>
        <tr>
            <td>{PHP.L.Expire}:</td>
            <td>{PAGEEDIT_FORM_EXPIRE}</td>
        </tr>
        <!-- BEGIN: TAGS -->
        <tr>
            <td>{PAGEEDIT_TOP_TAGS}:</td>
            <td>{PAGEEDIT_FORM_TAGS} <span class="desc italic">({PAGEEDIT_TOP_TAGS_HINT}, {PHP.L.ant.optional})</span></td>
        </tr>
        <!-- END: TAGS -->
        <tr>
            <td>{PHP.L.Author}:</td>
            <td>{PAGEEDIT_FORM_AUTHOR}</td>
        </tr>
        <!-- BEGIN: ADMIN -->
        <tr>
            <td>{PHP.L.page_metakeywords}:</td>
            <td>{PAGEEDIT_FORM_KEYWORDS}</td>
        </tr>
        <tr>
            <td>{PHP.L.page_metatitle}:</td>
            <td>{PAGEEDIT_FORM_METATITLE}</td>
        </tr>
        <tr>
            <td>{PHP.L.page_metadesc}:</td>
            <td>{PAGEEDIT_FORM_METADESC}</td>
        </tr>
        <tr>
            <td>{PHP.L.Alias}:</td>
            <td>{PAGEEDIT_FORM_ALIAS} <span class="desc italic">({PHP.L.ant.optional})</span></td>
        </tr>
        <!-- IF {PHP.pageOrder} == 'order' -->
        <tr>
            <td>{PAGEEDIT_FORM_ORDER_TITLE}:</td>
            <td>{PAGEEDIT_FORM_ORDER}</td>
        </tr>
        <!-- ENDIF -->

        <tr>
            <td>{PHP.L.shop.manufacturer}:</td>
            <td>{PAGEEDIT_FORM_PROD_MANUFACTURER_ID}</td>
        </tr>


        <tr>
            <td>{PHP.L.Owner}:</td>
            <td class="input-small">{PAGEEDIT_FORM_OWNERID}</td>
        </tr>
        <tr>
            <td>{PHP.L.Hits}:</td>
            <td class="input-small">{PAGEEDIT_FORM_PAGECOUNT}</td>
        </tr>
        <tr>
            <td>{PHP.L.Parser}:</td>
            <td>{PAGEEDIT_FORM_PARSER}</td>
        </tr>
        <!-- END: ADMIN -->
        <tr>
            <td colspan="2">
                {PAGEEDIT_FORM_TEXT}
                <!-- IF {PAGEEDIT_FORM_PFS} -->{PAGEEDIT_FORM_PFS}<!-- ENDIF -->
                <!-- IF {PAGEEDIT_FORM_SFS} -->{PAGEEDIT_FORM_SFS}<!-- ENDIF -->
            </td>
        </tr>
    </table>
</div>

<!-- Price -->
<div class="tab-pane fade" id="prod-price">
    <fieldset>
        <legend>{PHP.L.shop.product_form_prices}</legend>
        <table class="table table-striped">
            <tr>
                <td class="width20">{PHP.L.shop.product_form_price_cost}:</td>
                <td class="input-medium">{PAGE_FORM_PROD_PRICE} {PAGE_FORM_CURRENCY}<br/>
                    <span class="desc italic">{PHP.L.shop.product_form_price_cost_tip}</span>
                </td>
            </tr>
            <tr>
                <td>{PHP.L.shop.product_baseprice}:</td>
                <td class="input-medium">
                    {PAGE_FORM_PROD_BASE_PRICE}
                    {PAGE_VENDOR_CURRENCY_SYMBOL}<br/>
                    <span class="desc italic">{PHP.L.shop.product_form_price_base_tip}</span>
                </td>
            </tr>
            <tr>
                <td>{PHP.L.shop.product_form_price_final}:</td>
                <td class="input-medium">{PAGE_FORM_PROD_SALES_PRICE} {PAGE_VENDOR_CURRENCY_SYMBOL}
                    {PAGE_FORM_PROD_SALES}<br/>
                    <span class="desc italic">{PHP.L.shop.product_form_price_final_tip}</span><br/>
                    <span class="desc italic"><strong>{PHP.L.shop.product_form_calc_base_price}</strong> -
                        {PHP.L.shop.product_form_calc_base_price_tip}</span>
                </td>
            </tr>
            <tr>
                <td>{PHP.L.shop.product_discount_override}:</td>
                <td class="input-medium">
                    {PAGE_FORM_PROD_PRICE_OVERRIDE_PRICE} {PAGE_VENDOR_CURRENCY_SYMBOL}
                    {PAGE_FORM_PROD_PRICE_OVERRIDE}<br/>
                    <span class="desc italic">{PHP.L.shop.product_discount_override_tip}</span>
                </td>
            </tr>
            <tr>
                <td>Не применять скидку по купону:</td>
                <td>
                    {PAGEEDIT_FORM_PROD_NO_COUPON_DISCOUNT}
                </td>
            </tr>
        </table>
    </fieldset>

    <fieldset>
        <legend>{PHP.L.shop.product_form_add_prices}</legend>
        <table class="table table-striped">
            <!-- BEGIN: ADD_PRICES_ROW -->
            <tr class="addprice" id="add_price_{PAGE_FORM_PROD_ADDP_ROW_ID}"
            <!-- IF {PAGE_FORM_PROD_ADDP_ROW_ID} == 0 -->style="display: none"<!-- ENDIF --> >
            <td class="input-small">
                {PHP.L.shop.cart_price}: {PAGE_FORM_PROD_ADDP_ROW} {PAGE_VENDOR_CURRENCY_SYMBOL} &nbsp; &nbsp;
                {PHP.L.Groups}: {PAGE_FORM_PROD_ADDP_ROW_GROUPS}
                <span class="input-mini">
                    {PHP.L.shop.product_form_min_quantity}: {PAGE_FORM_PROD_ADDP_ROW_MIN}
                    {PHP.L.shop.product_form_max_quantity}: {PAGE_FORM_PROD_ADDP_ROW_MAX}
                </span>
                <button class="deloption" type="button" style="" name="deloption"><i class="icon-trash"></i></button>
            </td>
            </tr>
            <!-- END: ADD_PRICES_ROW -->
        </table>
        <button id="addoption" name="addoption" type="button" style="display:none;"><i class="icon-plus"></i>
            {PHP.L.Add}</button>
    </fieldset>

    <fieldset>
        <legend>{PHP.L.shop.product_form_rules_overrides}</legend>
        <table class="table table-striped">
            <tr>
                <td>{PHP.L.shop.tax}:</td>
                <td>
                    {PAGE_FORM_PROD_TAX_RATES}
                    <!-- IF {PAGE_PROD_TAX_RULES_ARR} -->
                    <br/><i>{PHP.L.shop.tax_affecting}:
                        <!-- FOR {VALUE} IN {PAGE_PROD_TAX_RULES_ARR} -->
                        {VALUE}<br/>
                        <!-- ENDFOR -->
                    </i>
                    <!-- ENDIF -->
                </td>
            </tr>
            <tr>
                <td>{PHP.L.shop.discount}:</td>
                <td>
                    {PAGE_FORM_PROD_DISCOUNTS}
                    <!-- IF {PAGE_PROD_TAX_RULES_ARR} OR {PAGE_PROD_DATAX_RULES_ARR} -->
                    <br/><i>{PHP.L.shop.rules_affecting}:
                        <!-- FOR {VALUE} IN {PAGE_PROD_DBTAX_RULES_ARR} -->
                        {VALUE}<br/>
                        <!-- ENDFOR -->
                        <!-- FOR {VALUE} IN {PAGE_PROD_DATAX_RULES_ARR} -->
                        {VALUE}<br/>
                        <!-- ENDFOR -->
                    </i>
                    <!-- ENDIF -->
                </td>
            </tr>
        </table>
    </fieldset>
</div>

<!-- Статус -->
<div class="tab-pane fade" id="prod-status">
    <table class="table table-striped">
        <tr>
            <td class="width20">{PHP.L.shop.product_form_in_stock}:</td>
            <td class="input-medium">{PAGEEDIT_FORM_PROD_IN_STOCK}</td>
            <td class="width20">{PHP.L.shop.product_form_ordered_stock}:</td>
            <td class="input-medium">{PAGEEDIT_FORM_PROD_ORDERED}</td>
        </tr>
        <tr>
            <td>{PHP.L.shop.product_form_min_order}:</td>
            <td class="input-medium">{PAGEEDIT_FORM_PROD_MIN_ORDER_LEVEL}</td>
            <td>{PHP.L.shop.product_form_max_order}:</td>
            <td class="input-medium">{PAGEEDIT_FORM_PROD_MAX_ORDER_LEVEL}</td>
        </tr>
        <tr>
            <td>{PHP.L.shop.product_form_step}:</td>
            <td colspan="3" class="input-small">
                {PAGEEDIT_FORM_PROD_STEP} {PAGEEDIT_FORM_PROD_ALLOW_DECIMAL_QUANTITY}
                <br/><span class="desc">{PHP.L.shop.product_form_step_tip}</span>
            </td>
        </tr>
    </table>

    <!-- BEGIN: WAITING_USERS -->
    <fieldset class="marginbottom10">
        <legend onclick="$('#waitingUsers').slideToggle();" style="cursor: pointer">{PHP.L.shop.waiting_users}:
            {PAGE_PROD_WU_COUNT}...
        </legend>
        {PAGE_PROD_WU_NOTIFY}
        <div id="waitingUsers" style="display: none;">
            <table class="cells">
                <tr>
                    <td class="coltop"></td>
                    <td class="coltop">{PHP.L.User}</td>
                    <td class="coltop">{PHP.L.Name}</td>
                    <td class="coltop">{PHP.L.Email}</td>
                    <td class="coltop">{PHP.L.shop.phone}</td>
                    <td class="coltop">{PHP.L.Date}</td>
                </tr>
                <!-- BEGIN: ROW -->
                <tr>
                    <td class="{ODD_EVEN}">{PAGE_PROD_WU_NUM}</td>
                    <td class="{ODD_EVEN}">{PAGE_PROD_WU_USER}</td>
                    <td class="{ODD_EVEN}">{PAGE_PROD_WU_NAME}</td>
                    <td class="{ODD_EVEN}">{PAGE_PROD_WU_EMAIL}</td>
                    <td class="{ODD_EVEN}">{PAGE_PROD_WU_PHONE}</td>
                    <td class="{ODD_EVEN}">{PAGE_PROD_WU_DATE}</td>
                </tr>
                <!-- END: ROW -->
            </table>
        </div>
    </fieldset>
    <!-- END: WAITING_USERS -->


    <fieldset class="marginbottom10">
        <legend onclick="$('#waitingUsers').slideToggle();" style="cursor: pointer">Список покупателей, которые
            приобрели
        </legend>

        <div id="waitingUsers" style="display: none;">
            <table class="cells">
                <tr>
                    <td class="coltop"></td>
                    <td class="coltop">{PHP.L.User}</td>
                    <td class="coltop">{PHP.L.Name}</td>
                    <td class="coltop">{PHP.L.Email}</td>
                    <td class="coltop">{PHP.L.shop.phone}</td>
                    <td class="coltop">{PHP.L.Date}</td>
                </tr>
                <!-- BEGIN: ROW -->
                <tr>
                    <td colspan="6">Блок в разработке</td>
                </tr>
                <!-- END: ROW -->
            </table>
        </div>
    </fieldset>

</div>

<!-- Габариты -->
<div class="tab-pane fade" id="prod-dimensions">
    <table class="table table-striped">
        <tr>
            <td class="width20">{PHP.L.shop.lenght}:</td>
            <td class="input-medium">{PAGEEDIT_FORM_PROD_LENGTH} {PAGEEDIT_FORM_PROD_LWH_UOM}</td>
        </tr>
        <tr>
            <td>{PHP.L.shop.width}:</td>
            <td class="input-medium">{PAGEEDIT_FORM_PROD_HEIGHT}</td>
        </tr>
        <tr>
            <td>{PHP.L.shop.height}:</td>
            <td class="input-medium">{PAGEEDIT_FORM_PROD_WIDTH}</td>
        </tr>
        <tr>
            <td>{PHP.L.shop.weight}:</td>
            <td class="input-medium">{PAGEEDIT_FORM_PROD_WEIGHT} {PAGEEDIT_FORM_PROD_WEIGHT_UOM}</td>
        </tr>
        <tr>
            <td>{PHP.L.shop.product_unit}:</td>
            <td class="input-medium">{PAGEEDIT_FORM_PROD_UNIT}</td>
        </tr>
        <tr>
            <td>{PHP.L.shop.product_packaging}:</td>
            <td class="input-medium">{PAGEEDIT_FORM_PROD_IN_PACK} {PAGEEDIT_FORM_PROD_ORDER_BY_PACK}</td>
        </tr>
    </table>
</div>

<!-- Изображения -->
<div class="tab-pane fade" id="prod-images">
    <table class="table table-striped">
        <tr>
            <td colspan="2">
                Изображения:
                <!-- IF {PHP|cot_auth('plug', 'attach2', 'W')} -->
                <div style="position: relative; padding: 0; width: 765px">
                    {PAGEEDIT_FORM_ID|att_widget('page',$this,'attach2.widget','100%', '300px' )}
                </div>
                <!-- ENDIF -->

            </td>
        </tr>
        <!-- IF {PHP.usr.isadmin} -->
        <tr>
            <td>{PHP.L.page_file}:<br/>
                {PHP.themelang.pageadd.Filehint}</td>
            <td>{PAGEEDIT_FORM_FILE}</td>
        </tr>
        <tr>
            <td>{PHP.L.URL}:<br/>{PHP.L.page_urlhint}</td>
            <td>{PAGEEDIT_FORM_URL}<br/>{PAGEEDIT_FORM_URL_PFS} {PAGEEDIT_FORM_URL_SFS}</td>
        </tr>
        <tr>
            <td>{PHP.L.page_filesize}:<br/>{PHP.L.page_filesizehint}</td>
            <td>{PAGEEDIT_FORM_SIZE}</td>
        </tr>
        <tr>
            <td>{PHP.L.page_filehitcount}:<br/>{PHP.L.page_filehitcounthint}</td>
            <td>{PAGEEDIT_FORM_FILECOUNT}</td>
        </tr>
        <!-- ENDIF -->
    </table>
</div>
</div>

<div class="text-left">{PHP.L.page_deletepage}: {PAGEEDIT_FORM_DELETE}</div>

<div class="valid" style="line-height: 48px;">
    <!-- IF {PHP.usr_can_publish} -->
    <button type="submit" name="rpagestate" value="0" class="btn btn-primary"><i
                class="icon-ok icon-white"></i>
        {PHP.L.Publish}</button>
    <!-- ENDIF -->
    <button type="submit" name="rpagestate" value="2" class="submit">{PHP.L.Saveasdraft}</button>
    <!-- IF !{PHP.usr_can_publish} OR {PHP.usr.isadmin} -->
    <button type="submit" name="rpagestate" value="1">{PHP.L.Submitforapproval}</button>
    <!-- ENDIF -->
</div>

</form>
<!-- END: MAIN -->
