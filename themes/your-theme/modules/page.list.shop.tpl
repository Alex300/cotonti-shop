<!-- BEGIN: MAIN -->
<div id="breadcrumbs">
    <div class="rss-icon-title">
        <a href="{LIST_CAT_RSS}"><img src="{PHP.cfg.mainurl}/{PHP.cfg.themes_dir}/{PHP.theme}/img/rss-icon.png" alt="" /></a>
    </div>
    {LIST_CATPATH}
</div>

<h1>{LIST_CATTITLE}</h1>

<!-- IF {PPVE_PRINT_VERSION} != '' OR {PPVE_PDF_VERSION} != '' OR {PPVE_EMAIL_TO_FRIEND} -->
<div style="text-align:right; margin:5px;">
    <noindex>{PPVE_PRINT_VERSION} {PPVE_PDF_VERSION} {PPVE_EMAIL_TO_FRIEND}</noindex>
</div>
<!-- ENDIF -->

<!-- IF {LIST_CAT_TEXT} != '' AND {PHP.d} < 2 AND {PHP.dc} < 2 -->
<div class="content">{LIST_CAT_TEXT}</div>
<!-- ELSE -->
<div class="content text-justify">{LIST_CATDESC}</div>
<!-- ENDIF -->


<!-- IF {LIST_SUBMITNEWPAGE} -->
<div style="margin-top: 20px;" class="text-right">
    <a class="btn btn-info btn-small" href="/admin.php?m=structure&n=page&id={PHP.cat.id}&x={PHP.sys.xk}" >
        <span class="icon-folder-open icon-white"></span> Редактировать описание раздела</a>
    {LIST_SUBMITNEWPAGE}
</div>
<!-- ENDIF -->


<!-- IF {LISTCAT_PAGNAV} -->
<div class="pagination text-right">
    {LISTCAT_PAGEPREV}{LISTCAT_PAGNAV}{LISTCAT_PAGENEXT}
</div>
<!-- ENDIF -->

<!-- BEGIN: LIST_ROWCAT -->
<div class="list-row">
    <span style="float:left; margin-right:10px;">{LIST_ROWCAT_ICON}</span>
    <h3 class="lhn"><a href="{LIST_ROWCAT_URL}">{LIST_ROWCAT_TITLE} ...</a>  ({LIST_ROWCAT_COUNT})</h3>
    <div class="text-justify" style="margin:0 0 0 15px">{LIST_ROWCAT_DESC}</div>
</div>
<!-- END: LIST_ROWCAT -->

<!-- IF {LISTCAT_PAGNAV} -->
<div class="pagination text-right">
    {LISTCAT_PAGEPREV}{LISTCAT_PAGNAV}{LISTCAT_PAGENEXT}
</div>
<!-- ENDIF -->


<!-- IF {LIST_TOP_PAGINATION} -->
<div class="pagination text-right" style="margin-top: 5px">
    {LIST_TOP_PAGEPREV}{LIST_TOP_PAGINATION}{LIST_TOP_PAGENEXT}
</div>
<!-- ENDIF -->

<!-- BEGIN: LIST_ROW -->
<div class="list-row">
    <h2><a href="{LIST_ROW_URL}" title="{LIST_ROW_SHORTTITLE}">{LIST_ROW_SHORTTITLE}</a>  {LIST_ROW_FILE_ICON}</h2>
    <div class="list-row-text desc" style="padding: 0;">{LIST_ROW_BEGIN_STAMP|cot_date('datetime_fulltext', $this)}</div>
    <div class="list-row-text">
        <table class="flat">
            <tr>
                <td style="width: 130px">
                    <!-- IF {LIST_ROW_ID|att_count('page',$this,'images')} > 0 -->
                    <div class="pull-left thumbnail">
                        <a href="{LIST_ROW_URL}"><img src="{LIST_ROW_ID|att_get('page',$this)|att_thumb($this,120,120, 'crop')}" alt="{LIST_ROW_SHORTTITLE}" /></a>
                    </div>
                    <!-- ENDIF -->
                </td>
                <td class="">
                        <!-- IF {LIST_ROW_DESC_HTML} -->
                        {LIST_ROW_DESC_HTML}
                        <!-- ELSE -->
                        <!-- IF {LIST_ROW_DESC} -->
                        {LIST_ROW_DESC}
                        <!-- ELSE -->
                        {LIST_ROW_TEXT_CUT}
                        <!-- ENDIF -->
                        <!-- ENDIF -->

                        <!-- manufacturers -->
                        <!-- IF {LIST_ROW_PROD_MANUFACTURER_NAME} != '' -->
                        <p class="small">
                            <strong>{PHP.L.shop.manufacturer}:</strong>
                            <a href="{LIST_ROW_PROD_MANUFACTURER_URL}">{LIST_ROW_PROD_MANUFACTURER_NAME}</a>
                        </p>
                        <!-- ENDIF -->
                        <!-- /manufacturers -->

                    <!-- prices -->
                    <!-- important: id="productPrice{PAGE_ID}" need for JS  -->
                    <div class="product-price small lhn margintop10" id="productPrice{LIST_ROW_ID}">
                        <!-- IF {LIST_ROW_PROD_UNIT} AND {PHP.cfg.shop.price_show_packaging_pricelabel} -->
                        <strong>{PHP.L.shop.cart_price_per_unit} ({LIST_ROW_PROD_UNIT}):</strong>
                        <!-- ELSE -->
                        <strong>{PHP.L.shop.cart_price}:</strong>
                        <!-- ENDIF -->
                        {LIST_ROW_PROD_BASE_PRICE}
                        {LIST_ROW_PROD_BASE_PRICE_VARIANT}

                        {LIST_ROW_PROD_BASE_PRICE_WIDTH_TAX}
                        {LIST_ROW_PROD_DISCOUNTED_PRICE_WIDTHOUT_TAX}
                        {LIST_ROW_PROD_SALES_PRICE_WIDTH_DISCOUNT}
                        {LIST_ROW_PROD_SALES_PRICE}
                        {LIST_ROW_PROD_PRICE_WIDTHOUT_TAX}
                        {LIST_ROW_PROD_DISCOUNT}
                    </div>
                    <!-- /prices -->
                </td>
                <td style="width: 230px">
                    <!-- add to cart -->
                    <div class="pull-left marginleft10" style="margin-top: 13px">{LIST_ROW_PROD_ADD_TO_CART}</div>
                    <!-- /add to cart -->
                </td>
            </tr>
        </table>
    </div>

    <div class="text-right small desc">
        {LIST_ROW_RATINGS_DISPLAY} &nbsp;
        <!-- IF {LIST_ROW_AUTHOR} -->
        {PHP.L.Author}: <strong>{LIST_ROW_AUTHOR}</strong> |
        <!-- ENDIF -->
        <!-- IF {PHP.usr.isadmin} == TRUE -->
        ({PHP.L.Hits}: {LIST_ROW_COUNT}) |
        <!-- ENDIF -->
        {PHP.L.comments_comments}: {LIST_ROW_COMMENTS}
    </div>

    <!-- IF {PHP.usr.id} > 0 AND ( {LIST_ROW_OWNERID} == {PHP.usr.id} OR  {PHP.usr.isadmin} == 1 ) -->
    <div class="text-right">
        <a href="{LIST_ROW_ADMIN_EDIT_URL}" class="btn btn-mini">
            <i class="icon-edit"></i> {PHP.L.Edit}</a>

        <!-- IF {PHP.usr.isadmin} -->
        <a href="{LIST_ROW_ADMIN_UNVALIDATE_URL}" class="btn btn-mini confirmLink">
            <!-- IF {LIST_ROW_STATE} == 1 -->
            <i class="icon-check"></i> {PHP.L.Validate}
            <!-- ELSE -->
            <i class="icon-time"></i> {PHP.L.Putinvalidationqueue}
            <!-- ENDIF --></a>

        <a href="{LIST_ROW_ADMIN_CLONE_URL}" class="btn btn-mini">
            <i class="icon-share"></i> {PHP.L.page_clone}</a>

        <a href="{LIST_ROW_ADMIN_DELETE_URL}" class="btn btn-mini confirmLink">
            <i class="icon-trash"></i> {PHP.L.Delete}</a>
        <!-- ENDIF -->
    </div>
    <!-- ENDIF -->
</div>
<!-- END: LIST_ROW -->

<!-- IF {LIST_TOP_PAGINATION} -->
<div class="pagination text-right">
    {LIST_TOP_PAGEPREV}{LIST_TOP_PAGINATION}{LIST_TOP_PAGENEXT}<br />
    <div class="desc italic small">
        {PHP.L.Page}: {LIST_TOP_CURRENTPAGE} {PHP.L.Of} {LIST_TOP_TOTALPAGES} {PHP.cfg.separator} {PHP.L.ant.linesperpage}: {LIST_TOP_MAXPERPAGE} {PHP.cfg.separator} {PHP.L.Total}: {LIST_TOP_TOTALLINES}
    </div>
</div>
<!-- ENDIF -->

<!-- IF {PHP.cfg.menu1} -->
<div style="margin:20px 0 10px 0">{PHP.cfg.menu1}</div>
<!-- ENDIF -->

{LIST_COMMENTS_DISPLAY}

<!-- IF {PHP.cfg.plugin.tags.noindex} == 1 -->
<noindex>
    <!-- ENDIF -->
    <div class="block">
        <h4 class="tags">{PHP.L.Tags}</h4>
        {LIST_TAG_CLOUD}
        {LIST_TAG_CLOUD_ALL_LINK}
    </div>
    <!-- IF {PHP.cfg.plugin.tags.noindex} == 1 -->
</noindex>
<!-- ENDIF -->
<!-- END: MAIN -->
