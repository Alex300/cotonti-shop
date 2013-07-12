<!-- BEGIN: MAIN -->
<div id="breadcrumbs">
    <a href="{PHP|cot_url('index')}">{PHP.L.Home}</a> {PHP.cfg.separator} {PHP.catpath} {PHP.cfg.separator} {PAGEADD_PAGETITLE}
</div>

<h2 class="page margintop10">{PAGEADD_PAGETITLE}</h2>

{FILE "{PHP.cfg.themes_dir}/{PHP.cfg.defaulttheme}/warnings.tpl"}

<h4 style="margin: 10px 0;">{PAGEADD_SUBTITLE}</h4>

<form action="{PAGEADD_FORM_SEND}" enctype="multipart/form-data" method="post" name="pageform" class="form-inline form-fullwidth">

    <ul class="nav nav-tabs">
        <li class="active"><a href="#prod-info" data-toggle="tab">Информация / описание</a></li>
        <li><a href="#prod-price" data-toggle="tab">Стоимость</a></li>
        <li><a href="#prod-status" data-toggle="tab">{PHP.L.Status}</a></li>
        <li><a href="#prod-dimensions" data-toggle="tab">{PHP.L.shop.product_dim}</a></li>
        <li><a href="#prod-images" data-toggle="tab">Изображения / файлы</a></li>
    </ul>
    <div class="tab-content">
        <!-- Информация / описание -->
        <div class="tab-pane fade active in" id="prod-info">
            <table class="table table-striped">
                <tr>
                    <td style="width:176px;">{PHP.L.Category}:</td>
                    <td class="">{PAGEADD_FORM_CAT}</td>
                </tr>
                <tr>
                    <td>{PHP.L.Title}:</td>
                    <td>{PAGEADD_FORM_TITLE}</td>
                </tr>
                <tr>
                    <td>{PHP.L.shop.cart_sku}:</td>
                    <td>{PAGEADD_FORM_PROD_SKU}</td>
                </tr>
                <tr>
                    <td>{PHP.L.Description}:</td>
                    <td>{PAGEADD_FORM_DESC}</td>
                </tr>
                <tr>
                    <td>{PHP.L.Begin}:</td>
                    <td>{PAGEADD_FORM_BEGIN}</td>
                </tr>
                <tr>
                    <td>{PHP.L.Expire}:</td>
                    <td>{PAGEADD_FORM_EXPIRE}</td>
                </tr>
                <!-- BEGIN: TAGS -->
                <tr>
                    <td>{PAGEADD_TOP_TAGS}:</td>
                    <td>{PAGEADD_FORM_TAGS} <span class="desc italic">({PAGEADD_TOP_TAGS_HINT}, {PHP.L.ant.optional})</span></td>
                </tr>
                <!-- END: TAGS -->
                <tr>
                    <td>{PHP.L.Author}:</td>
                    <td>{PAGEADD_FORM_AUTHOR}</td>
                </tr>
                <!-- BEGIN: ADMIN -->
                <tr>
                    <td>{PHP.L.page_metakeywords}:</td>
                    <td>{PAGEADD_FORM_KEYWORDS}</td>
                </tr>
                <tr>
                    <td>{PHP.L.page_metatitle}:</td>
                    <td>{PAGEADD_FORM_METATITLE}</td>
                </tr>
                <tr>
                    <td>{PHP.L.page_metadesc}:</td>
                    <td>{PAGEADD_FORM_METADESC}</td>
                </tr>
                <tr>
                    <td>{PHP.L.Alias}:</td>
                    <td>{PAGEADD_FORM_ALIAS} <span class="desc italic">({PHP.L.ant.optional})</span></td>
                </tr>
                <tr>
                    <td>{PHP.L.shop.manufacturer}:</td>
                    <td>{PAGEADD_FORM_PROD_MANUFACTURER_ID}</td>
                </tr>
                <tr>
                    <td>{PHP.L.Owner}:</td>
                    <td>{PAGEADD_FORM_OWNER}</td>
                </tr>
                <!-- IF {PHP.pageOrder} == 'order' -->
                <tr>
                    <td>{PAGEADD_FORM_ORDER_TITLE}:</td>
                    <td>{PAGEADD_FORM_ORDER}</td>
                </tr>
                <!-- ENDIF -->
                <tr>
                    <td>{PHP.L.Parser}:</td>
                    <td>{PAGEADD_FORM_PARSER}</td>
                </tr>
                <!-- END: ADMIN -->
                <tr>
                    <td colspan="2">
                        {PAGEADD_FORM_TEXT}
                        <!-- IF {PAGEADD_FORM_PFS} -->{PAGEADD_FORM_PFS}<!-- ENDIF -->
                        <!-- IF {PAGEADD_FORM_SFS} -->{PAGEADD_FORM_SFS}<!-- ENDIF -->
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
                        <td class="width20">{PHP.L.shop.cart_price}: </td>
                        <td class="input-medium">
                            {PAGE_FORM_PROD_PRICE} {PAGE_FORM_CURRENCY}<br />
                            <span class="desc italic">{PHP.L.shop.product_form_price_cost_tip}</span>
                        </td>
                    </tr>
                    <tr>
                        <td>{PHP.L.shop.product_baseprice}: </td>
                        <td class="input-medium">
                            {PAGE_FORM_PROD_BASE_PRICE}
                            {PAGE_VENDOR_CURRENCY_SYMBOL}<br />
                            <span class="desc italic">{PHP.L.shop.product_form_price_base_tip}</span>
                        </td>
                    </tr>
                    <tr>
                        <td>{PHP.L.shop.product_form_price_final}:</td>
                        <td class="input-medium">{PAGE_FORM_PROD_SALES_PRICE}
                            {PAGE_VENDOR_CURRENCY_SYMBOL}<br />
                            <span class="desc italic">{PHP.L.shop.product_form_price_final_tip}</span>
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
                    <td class="input-medium">{PAGEADD_FORM_PROD_IN_STOCK}</td>
                    <td class="width20">{PHP.L.shop.product_form_ordered_stock}:</td>
                    <td class="input-medium">{PAGEADD_FORM_PROD_ORDERED}</td>
                </tr>
                <tr>
                    <td>{PHP.L.shop.product_form_min_order}:</td>
                    <td class="input-medium">{PAGEADD_FORM_PROD_MIN_ORDER_LEVEL}</td>
                    <td>{PHP.L.shop.product_form_max_order}:</td>
                    <td class="input-medium">{PAGEADD_FORM_PROD_MAX_ORDER_LEVEL}</td>
                </tr>
                <tr>
                    <td>{PHP.L.shop.product_form_step}:</td>
                    <td colspan="3" class="input-small">
                        {PAGEADD_FORM_PROD_STEP} {PAGEADD_FORM_PROD_ALLOW_DECIMAL_QUANTITY}
                        <br/><span class="desc">{PHP.L.shop.product_form_step_tip}</span>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Габариты -->
        <div class="tab-pane fade" id="prod-dimensions">
            <table class="table table-striped">
                <tr>
                    <td class="width20">{PHP.L.shop.lenght}:</td>
                    <td class="input-medium">{PAGEADD_FORM_PROD_LENGTH} {PAGEADD_FORM_PROD_LWH_UOM}</td>
                </tr>
                <tr>
                    <td>{PHP.L.shop.width}:</td>
                    <td class="input-medium">{PAGEADD_FORM_PROD_HEIGHT}</td>
                </tr>
                <tr>
                    <td>{PHP.L.shop.height}:</td>
                    <td class="input-medium">{PAGEADD_FORM_PROD_WIDTH}</td>
                </tr>
                <tr>
                    <td>{PHP.L.shop.weight}:</td>
                    <td class="input-medium">{PAGEADD_FORM_PROD_WEIGHT} {PAGEADD_FORM_PROD_WEIGHT_UOM}</td>
                </tr>
                <tr>
                    <td>{PHP.L.shop.product_unit}:</td>
                    <td class="input-medium">{PAGEADD_FORM_PROD_UNIT}</td>
                </tr>
                <tr>
                    <td>{PHP.L.shop.product_packaging}:</td>
                    <td class="input-medium">{PAGEADD_FORM_PROD_IN_PACK} {PAGEADD_FORM_PROD_ORDER_BY_PACK}</td>
                </tr>
            </table>
        </div>

        <!-- Изображения -->
        <div class="tab-pane fade" id="prod-images">
            <table class="table table-striped">
                <tr>
                    <td>Изображения:</td>
                    <td class="strong">
                        Сохраните страницу, чтобы добавить изображения.
                    </td>
                </tr>
                <!-- IF {PHP.usr.isadmin} -->
                <tr>
                    <td>{PHP.L.page_file}:</td>
                    <td>
                        {PAGEADD_FORM_FILE}
                        <p class="desc">{PHP.L.page_filehint}</p>
                    </td>
                </tr>
                <tr>
                    <td>{PHP.L.URL}:<br />{PHP.L.page_urlhint}</td>
                    <td>{PAGEADD_FORM_URL}<br />{PAGEADD_FORM_URL_PFS} {PAGEADD_FORM_URL_SFS}</td>
                </tr>
                <tr>
                    <td>{PHP.L.Filesize}:<br />{PHP.L.page_filesizehint}</td>
                    <td>{PAGEADD_FORM_SIZE}</td>
                </tr>
                <!-- ENDIF -->
            </table>
        </div>
    </div>

    <div class="valid" style="line-height: 48px;">
        <!-- IF {PHP.usr_can_publish} -->
        <button type="submit" name="rpagestate" value="0" class="btn btn-primary"><i class="icon-ok icon-white"></i>
            {PHP.L.Publish}</button>
        <!-- ENDIF -->
        <button type="submit" name="rpagestate" value="2" class="submit">{PHP.L.Saveasdraft} / Добавить изображения</button>
        <!-- IF !{PHP.usr_can_publish} OR {PHP.usr.isadmin} -->
        <button type="submit" name="rpagestate" value="1">{PHP.L.Submitforapproval}</button>
        <!-- ENDIF -->
    </div>

</form>


<!-- IF {PHP.usr_can_publish} == 0 -->
<div class="help">{PHP.L.page_formhint}</div>
<!-- ENDIF -->


<!-- END: MAIN -->
