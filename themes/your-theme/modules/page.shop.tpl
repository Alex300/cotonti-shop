<!-- BEGIN: MAIN -->
<div id="breadcrumbs">
    <!-- IF {PAGE_CATPATH} -->{PAGE_CATPATH}<!-- ENDIF -->
    {PHP.cfg.separator} {PAGE_SHORTTITLE}
</div>

<h1>{PAGE_SHORTTITLE}</h1>

<div class="article-tools small" style="border-top: none">{PAGE_BEGIN_STAMP|cot_date('datetime_fulltext', $this)}</div>

<!-- IF {PPVE_PRINT_VERSION} != '' OR {PPVE_PDF_VERSION} != '' OR {PPVE_EMAIL_TO_FRIEND} -->
<div style="text-align:right; margin:5px;">
	<noindex>{PPVE_PRINT_VERSION} {PPVE_PDF_VERSION} {PPVE_EMAIL_TO_FRIEND}</noindex>
</div>
<!-- ENDIF -->

{FILE "{PHP.cfg.themes_dir}/{PHP.cfg.defaulttheme}/warnings.tpl"}

<div class="margintop10 marginbottom10">
    <!-- IF {PAGE_ID|att_count('page',$this,'images')} > 0 -->
    <div class="pull-left thumbnail marginright10 marginbottom10">
        <a href="{PAGE_ID|att_get('page',$this,'path')}" title="{PAGE_SHORTTITLE}"><img src="{PAGE_ID|att_get('page',$this)|att_thumb($this,160,120,'auto')}" alt="{PAGE_SHORTTITLE}" /></a>
    </div>
    <!-- ENDIF -->

    {PAGE_TEXT}

    <!-- manufacturers -->
    <!-- IF {PAGE_PROD_MANUFACTURER_NAME} != '' -->
    <p>
        <strong>{PHP.L.shop.manufacturer}:</strong>
        <a href="{PAGE_PROD_MANUFACTURER_URL}">{PAGE_PROD_MANUFACTURER_NAME}</a>
    </p>
    <!-- ENDIF -->
    <!-- /manufacturers -->

    <div class="pull-left">
        <!-- prices -->
        <!-- important: id="productPrice{PAGE_ID}" need for JS  -->
        <div class="product-price" id="productPrice{PAGE_ID}">
            <!-- IF {PAGE_PROD_UNIT} AND {PHP.cfg.shop.price_show_packaging_pricelabel} -->
            <strong>{PHP.L.shop.cart_price_per_unit} ({PAGE_PROD_UNIT}):</strong>
            <!-- ELSE -->
            <strong>{PHP.L.shop.cart_price}:</strong>
            <!-- ENDIF -->
            {PAGE_PROD_BASE_PRICE}
            {PAGE_PROD_BASE_PRICE_VARIANT}

            {PAGE_PROD_BASE_PRICE_WIDTH_TAX}
            {PAGE_PROD_DISCOUNTED_PRICE_WIDTHOUT_TAX}
            {PAGE_PROD_SALES_PRICE_WIDTH_DISCOUNT}
            {PAGE_PROD_SALES_PRICE}
            {PAGE_PROD_PRICE_WIDTHOUT_TAX}
            {PAGE_PROD_DISCOUNT}
        </div>
        <!-- /prices -->

        <!-- Additional prices -->
        <!-- IF {PAGE_PROD_ADD_PRICES} -->
        <div class="desc" style="margin: 10px;">
            <!-- FOR {KEY}, {VALUE} IN {PAGE_PROD_ADD_PRICES}  -->
            <p>
                При покупке от {VALUE.MIN} <!-- IF {VALUE.MAX} -->до {VALUE.MAX}<!-- ENDIF -->
                цена составит: {VALUE.PRICE}
            </p>
            <!-- ENDFOR -->
        </div>
        <!-- ENDIF -->
        <!-- /Additional prices -->
    </div>

    <!-- add to cart -->
    <div class="pull-right">
        {PAGE_PROD_ADD_TO_CART}
    </div>
    <!-- /add to cart -->

    <div class="clearfix"></div>
    <!-- IF {PAGE_ID|att_count('page',$this,'images')} > 1 -->
    <div class="row-fluid">
        {PAGE_ID|att_gallery('page',$this)}
    </div>
    <!-- ENDIF -->

    <!-- IF {PAGE_ID|att_count('page',$this,'files')} > 0 -->
    <div class="row-fluid margintop10">
        <div class="strong">{PHP.L.Files}</div>
        {PAGE_ID|att_downloads('page',$this)}
    </div>
    <!-- ENDIF -->

    <!-- BEGIN: PAGE_MULTI -->
    <!-- TODO FIX -->
    <div class="block">
        <h2 class="info">{PHP.L.Summary}:</h2>
        {PAGE_MULTI_TABTITLES}
        <p class="paging">{PAGE_MULTI_TABNAV}</p>
    </div>
    <!-- END: PAGE_MULTI -->
		
</div>        

<!-- BEGIN: PAGE_FILE -->
<hr />
<div class="block">
    <!-- BEGIN: MEMBERSONLY -->
    {PAGE_FILE_ICON} {PAGE_FILETITLE}<br />
    <!-- END: MEMBERSONLY -->
    <!-- BEGIN: DOWNLOAD -->
    {PAGE_FILE_ICON} <a href="{PAGE_FILE_URL}">{PHP.L.Download} : {PAGE_FILETITLE}</a><br />
    <!-- END: DOWNLOAD -->
    {PHP.L.Size}: {PAGE_FILE_SIZE} {PHP.L.kb} 
    <!-- IF {PHP.usr.isadmin} -->
        , {PHP.L.Downloaded} {PAGE_FILE_COUNT}  {PHP.Ls.Times.0}
    <!-- ENDIF -->
</div>
<!-- END: PAGE_FILE -->

<!-- IF {PHP.cfg.menu1} -->
<div style="margin:20px 0 10px 0">{PHP.cfg.menu1}</div>
<!-- ENDIF -->

<div class="article-tools">
    <div class="text-right pull-right width45">
        <span class="rss-icon">
        <a href="{PAGE_COMMENTS_RSS}"><img src="{PHP.cfg.mainurl}/{PHP.cfg.themes_dir}/{PHP.theme}/img/rss-icon.png" alt="" style="vertical-align: middle;" /></a>
        </span>
        {PHP.L.comments_comments}: {PAGE_COMMENTS}<br />
        {PHP.L.Ratings}: &nbsp; <div class="pull-right">{PAGE_RATINGS_DISPLAY}</div>
    </div>

    <div class="pull-left width45 desc">
        <!-- IF {PAGE_AUTHOR} -->
            {PHP.L.Author}: {PAGE_AUTHOR}<br />
        <!-- ENDIF -->
        {PHP.L.Submittedby}: {PAGE_OWNER}
        <!-- IF {PAGE_KARMA} -->
        &nbsp; (<em>{PAGE_KARMA}</em> {PAGE_KARMA_ADD} {PAGE_KARMA_DEL})<!-- ENDIF -->

        <!-- BEGIN: PAGE_ADMIN -->
        <!-- END: PAGE_ADMIN -->
    </div>
    <div class="clearfix"></div>
</div>



<!-- IF {PAGE_ADMIN_EDIT} -->
<div class="margin10 desc">
    <a href="{PAGE_ADMIN_EDIT_URL}" class="btn btn-small"><i class="icon-edit"></i> {PHP.L.Edit}</a>
    <!-- IF {PHP.usr.isadmin} -->
    <a href="{PAGE_ADMIN_UNVALIDATE_URL}" class="btn btn-small confirmLink">
        <!-- IF {PAGE_STATE} == 1 -->
        <i class="icon-check"></i> {PHP.L.Validate}
        <!-- ELSE -->
        <i class="icon-time"></i> {PHP.L.Putinvalidationqueue}
        <!-- ENDIF --></a>
    <!-- ENDIF -->

    <a href="{PAGE_ADMIN_CLONE_URL}" class="btn btn-small">
        <i class="icon-share"></i> {PHP.L.page_clone}</a>

    <!-- IF {PAGE_ADMIN_DELETE_URL} -->
    <a href="{PAGE_ADMIN_DELETE_URL}" class="btn btn-small confirmLink">
        <i class="icon-trash"></i> {PHP.L.Delete}</a>
    <!-- ENDIF -->

    <!-- IF {PHP.usr.isadmin} -->
    &nbsp; ({PHP.L.Hits}: {PAGE_COUNT})
    <!-- ENDIF -->
</div>
<!-- ENDIF -->

<!-- IF {PHP.cfg.plugin.tags.noindex} == 1 -->
<noindex>
<!-- ENDIF -->
<div class="block">
    <strong>{PHP.L.Tags}: </strong>
    <!-- BEGIN: PAGE_TAGS_ROW -->
         <!-- IF {PHP.tag_i} > 0 -->, &nbsp;<!-- ENDIF -->
            <a <!-- IF {PHP.cfg.plugin.tags.noindex} == 1 -->rel="nofollow"<!-- ENDIF --> href="{PAGE_TAGS_ROW_URL}">{PAGE_TAGS_ROW_TAG}</a>
    <!-- END: PAGE_TAGS_ROW -->
    <!-- BEGIN: PAGE_NO_TAGS -->
        {PAGE_NO_TAGS}
    <!-- END: PAGE_NO_TAGS -->
</div>
<!-- IF {PHP.cfg.plugin.tags.noindex} == 1 -->
</noindex>
<!-- ENDIF -->

{PAGE_COMMENTS_DISPLAY}

<!-- IF {SIMILAR_PAGES} -->
<div class="block">
    {SIMILAR_PAGES} 
</div>
<!-- ENDIF -->

<!-- END: MAIN -->
