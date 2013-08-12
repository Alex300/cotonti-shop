<!-- BEGIN: MAIN -->
<script type="text/javascript">
jQuery(document).ready(function($) {
	$(".edit_price").click(function(){
		var attr = $(this).attr('id');
        var prodId = attr.replace('price_', '');
        var url = "/index.php?e=shop&m=product&a=edit_price&prod_id=_prodid_&_ajax=1";
        url = url.replace('_prodid_', prodId);
        //alert(url);
        shopDialog({
            text  : '<iframe src="'+url+'" width="700" height="400"><p>Your browser does not support iframes.</p> </iframe>',
            title : 'Редактировать цену',
            close : (function (){
                $('#price_'+prodId).fadeTo("fast", 0.3);
                // 'vendor': 1 - отобразить цены в валюте продавца
                $.post("/index.php?e=shop&m=product&a=recalculate", {'shop_product_id': prodId, 'vendor': 1, 'x' : '{PHP.sys.xk}'},
                    function(datas, textStatus) {
                        // refresh price
                        $('#price_'+prodId).html(datas.basePrice);
                        $('#price_'+prodId).fadeTo("fast", 1);
                    }, "json");
                })
            })
        return false;
	});
});    
</script>


{PHP.L.Filters}:
    <form method="get">
        <input type="hidden" name="m" value="shop" />
        <input type="hidden" name="n" value="product" />
        {PHP.L.Title}:
        <input type="text" name="fil[title]" value="{LIST_FILTER_TITLE}" />
        &nbsp;
        {PHP.L.Category}: {LIST_FILTER_CAT}<br />
        <!-- IF {LIST_FILTER_MF} -->
        {PHP.L.shop.manufacturer}: {LIST_FILTER_MF}
        <!-- ENDIF -->
        <input type="submit" value="{PHP.L.Show}" />
    </form>
<div style="text-align: right">
    {SORT_BY} {SORT_WAY}
</div>


<form method="post" action="{LIST_URL}">
<input type="hidden" name ="a" value="save" />
<input type="hidden" name ="ret" value="{LIST_URL}" />

<table class="cells">
    <tr>
        <td class="coltop"></td>
        <!--<td class="coltop"><input type="checkbox" onclick="checkAll(20)" value="" name="toggle"></td>-->
        <td class="coltop">{PHP.L.shop.cart_name}</td>
        <td class="coltop">{PHP.L.shop.cart_sku}</td>
        <td class="coltop">{PHP.L.shop.cart_price}</td>
        <td class="coltop">{PHP.L.Category}</td>
        <!-- IF {LIST_COMMENTS_ON} -->
        <td class="coltop">Comm.</td>
        <!-- ENDIF -->
        <td class="coltop">{PHP.L.Status}</td>
        <!--<td class="coltop">Б.правка.</td> -->
        <td class="coltop">{PHP.L.Edit}</td>
        <!--<td class="coltop">Del.</td>-->
        <!--<td class="coltop">Copy</td>-->
        <td class="coltop">{PHP.L.Open}</td>
        <td class="coltop">ID</td>
    </tr>
    <!-- BEGIN: LIST_ROW -->
    <tr>
        <td rowspan="2" class="{LIST_ROW_ODDEVEN}">{LIST_ROW_NUM}</td>
        <!-- <td><input id="cb{LIST_ROW_ID}" type="checkbox" onclick="isChecked(this.checked);" value="25" 
                   name="product_id[]">
        </td>-->
        <td class="{LIST_ROW_ODDEVEN}">
            {LIST_ROW_EDIT_TITLE}
        <td class="{LIST_ROW_ODDEVEN}">{LIST_ROW_EDIT_SKU}</td>
        <td class="{LIST_ROW_ODDEVEN}">{LIST_ROW_EDIT_PRICE}</td>
        <td class="{LIST_ROW_ODDEVEN}">{LIST_ROW_CATTITLE}</td>
        
        <!-- IF {LIST_ROW_COMMENTS} -->
        <td rowspan="2" class="{LIST_ROW_ODDEVEN} centerall">{LIST_ROW_COMMENTS}</td>
        <!-- ENDIF -->
        <td rowspan="2" class="{LIST_ROW_ODDEVEN} centerall">{LIST_ROW_STATUS}</td>
       <!-- <td>
            <a href="#" onclick="aptQEdit('{LIST_ROW_ID}'); return false" >
                <img src="{PHP.cfg.plugins_dir}/admin_pages/tpl/images/edit1.png" />
            </a>
        </td> -->
        <td rowspan="2" class="{LIST_ROW_ODDEVEN} centerall">{LIST_ROW_ADMIN_EDIT}</td>
        <!-- <td rowspan="2" class="{LIST_ROW_ODDEVEN} centerall">{LIST_ROW_DELETE}</td> -->
        <!--<td rowspan="2">...</td>-->
        <td rowspan="2" class="{LIST_ROW_ODDEVEN} centerall">
            <a href="{LIST_ROW_URL}" target="_blank" >
                <img src="/images/icons/default/arrow-follow.png" />
            </a>
        </td>
        <td rowspan="2" class="{LIST_ROW_ODDEVEN} centerall">{LIST_ROW_ID}</td>
    </tr>
    <tr>
        <td class="{LIST_ROW_ODDEVEN}" colspan="4">
            <!-- IF {LIST_ROW_EDIT_TAGS} -->
            {PHP.L.Tags}: {LIST_ROW_EDIT_TAGS} &nbsp;
            <!-- ENDIF -->
            {PHP.L.shop.manufacturer}: {LIST_ROW_EDIT_PROD_MANUFACTURER_ID}
            <!-- <br />{LIST_ROW_EDIT_SOME_EXTRA_TITLE}: {LIST_ROW_EDIT_SOME_EXTRA} -->
        </td>
    </tr>
    <!-- END: LIST_ROW -->
    
    <!-- IF {LIST_TOTALLINES} == '0' -->
    <tr>
        <td class="odd centerall" colspan="12">{PHP.L.None}</td>
    </tr>
    <!-- ENDIF -->
    
</table>

<!-- IF {LIST_TOTALLINES} > 0 -->
<input type="submit" value="{PHP.L.Submit}" />
<!-- ENDIF -->
</form>

<!-- IF {LIST_CURRENTPAGE} -->
<div class="paging">
{LIST_PAGEPREV}{LIST_PAGINATION}{LIST_PAGENEXT}<span>{PHP.L.Page}: {LIST_CURRENTPAGE}, {PHP.L.Total}: {LIST_TOTALLINES}, 
    {PHP.L.Onpage}: {LIST_ITEMS_ON_PAGE}</span>
</div>
<!-- ENDIF -->


<!-- END: MAIN -->


<!-- BEGIN: EDIT_PRICE -->
<head>
<link href="system/admin/tpl/admin.css" type="text/css" rel="stylesheet" />
<script src="js/jquery.min.js" type="text/javascript"></script>
<script src="{PHP.cfg.modules_dir}/shop/js/shop_edit_product.js" type="text/javascript"></script>
</head>
<body style="background: none; font-size: 11px !important;">

{FILE "{PHP.cfg.themes_dir}/{PHP.cfg.defaulttheme}/warnings.tpl"}

<form action="/index.php?e=shop&m=product&a=edit_price&b=save&prod_id={PAGE_FORM_ID}&_ajax=1" 
      enctype="multipart/form-data"       method="post" name="priceform">
<fieldset>
    <legend>{PHP.L.shop.product_form_prices}</legend>
    <table class="cells">
        <tr>
            <td style="font-size: 11px">{PHP.L.shop.product_form_price_cost}: </td>
            <td style="font-size: 11px">{PAGE_FORM_PROD_PRICE} {PAGE_FORM_CURRENCY}<br />
                    <i>{PHP.L.shop.product_form_price_cost_tip}</i>
            </td>
        </tr>
        <tr>
            <td style="font-size: 11px">{PHP.L.shop.product_baseprice}: </td>
            <td style="font-size: 11px">
                {PAGE_FORM_PROD_BASE_PRICE}
                    {PAGE_VENDOR_CURRENCY_SYMBOL}<br />
                    <i>{PHP.L.shop.product_form_price_base_tip}</i>
            </td>
        </tr>
        <tr>
            <td style="font-size: 11px">{PHP.L.shop.product_form_price_final}:</td>
            <td style="font-size: 11px">{PAGE_FORM_PROD_SALES_PRICE}
                    {PAGE_VENDOR_CURRENCY_SYMBOL} {PAGE_FORM_PROD_SALES}<br />
                <i>{PHP.L.shop.product_form_price_final_tip}</i><br />
                <i><strong>{PHP.L.shop.product_form_calc_base_price}</strong> -
                {PHP.L.shop.product_form_calc_base_price_tip}</i>
            </td>
        </tr>
        <tr>
            <td style="font-size: 11px">{PHP.L.shop.product_discount_override}:</td>
            <td style="font-size: 11px">
                <table class="flat">
                    <tr>
                        <td style="font-size: 11px">{PAGE_FORM_PROD_PRICE_OVERRIDE_PRICE}  {PAGE_VENDOR_CURRENCY_SYMBOL}</td>
                        <td style="font-size: 11px">{PAGE_FORM_PROD_PRICE_OVERRIDE}</td>
                    </tr>
                </table>
            {PHP.L.shop.product_discount_override_tip}
            </td>
        </tr>
    </table>
</fieldset>    
    
<fieldset>
    <legend>{PHP.L.shop.product_form_add_prices}</legend>
    <table class="cells">
    <!-- BEGIN: ADD_PRICES_ROW -->
    <tr class="addprice" id="add_price_{PAGE_FORM_PROD_ADDP_ROW_ID}" <!-- IF {PAGE_FORM_PROD_ADDP_ROW_ID} == 0 -->style="display: none"<!-- ENDIF --> >
        <td style="font-size: 11px !important;">
        {PHP.L.shop.cart_price}: {PAGE_FORM_PROD_ADDP_ROW}  {PAGE_VENDOR_CURRENCY_SYMBOL} &nbsp; &nbsp;
        {PHP.L.Groups}: {PAGE_FORM_PROD_ADDP_ROW_GROUPS}
        {PHP.L.shop.product_form_min_quantity}: {PAGE_FORM_PROD_ADDP_ROW_MIN}
        {PHP.L.shop.product_form_max_quantity}: {PAGE_FORM_PROD_ADDP_ROW_MAX}
        <input class="deloption" type="button" style="" value="x" name="deloption">
        </td>
    </tr>
    <!-- END: ADD_PRICES_ROW -->
    </table>
    <input id="addoption" name="addoption" value="{PHP.L.Add}" type="button" style="display:none;" />
</fieldset>

<fieldset>
    <legend>{PHP.L.shop.product_form_rules_overrides}</legend>
    <table class="cells">
        <tr>
            <td style="font-size: 11px">{PHP.L.shop.tax}: </td>
            <td style="font-size: 11px">
                {PAGE_FORM_PROD_TAX_RATES}
                <!-- IF {PAGE_PROD_TAX_RULES_ARR} -->
                    <br /><i>{PHP.L.shop.tax_affecting}: 
                    <!-- FOR {VALUE} IN {PAGE_PROD_TAX_RULES_ARR} -->
                    {VALUE}<br />
                    <!-- ENDFOR -->    
                    </i>
                    <!-- ENDIF -->
            </td>
        </tr>
        <tr>
            <td style="font-size: 11px">{PHP.L.shop.discount}:</td>
            <td style="font-size: 11px"> 
                {PAGE_FORM_PROD_DISCOUNTS}
                <!-- IF {PAGE_PROD_TAX_RULES_ARR} OR {PAGE_PROD_DATAX_RULES_ARR} -->
                    <br /><i>{PHP.L.shop.rules_affecting}:
                    <!-- FOR {VALUE} IN {PAGE_PROD_DBTAX_RULES_ARR} -->
                    {VALUE}<br />
                    <!-- ENDFOR -->
                    <!-- FOR {VALUE} IN {PAGE_PROD_DATAX_RULES_ARR} -->
                    {VALUE}<br />
                    <!-- ENDFOR -->
                    </i>
                    <!-- ENDIF -->
            </td>
        </tr>
    </table>
</fieldset>   
 
<div class="textcenter margin10">
    <button class="strong" type="submit" name="rpagestate" value="0">{PHP.L.Submit}</button></div>
</form>
</body>
<!-- END: EDIT_PRICE -->