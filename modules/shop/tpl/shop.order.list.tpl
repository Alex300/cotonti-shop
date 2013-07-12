<!-- BEGIN: MAIN -->
<div id="breadcrumbs">{BREAD_CRUMBS}</div>

<h2 class="tags">{PAGE_TITLE}</h2>

{FILE "{PHP.cfg.themes_dir}/{PHP.cfg.defaulttheme}/warnings.tpl"}

<div class="row-fluid block">

    <div class="span6">
        <h3>{PHP.L.shop.user_form_billto}:</h3>

        <a href="{PHP|cot_url('shop', 'm=user&a=editaddress&addrtype=BT')}">{PHP.L.shop.user_form_billto_edit}</a>
    </div>

    <div class="span6">
        <h4>{PHP.L.shop.user_form_shipto}:</h4>
        <!-- BEGIN: USER_INFO_ROW -->
        <p>{USER_INFO_ROW_NUM}: <a href="{USER_INFO_ROW_EDTI_URL}"><strong>{USER_INFO_ROW_TITLE}</strong></a></p>
        <!-- END: USER_INFO_ROW -->

        <a href="{PHP|cot_url('shop', 'm=user&a=editaddress&addrtype=ST')}">{PHP.L.shop.user_form_shipto_add}</a>
    </div>
</div>

<div style="text-align: right">
    {SORT_BY} {SORT_WAY}
</div>


<!-- TODO фильтры -->

<!-- BEGIN: LIST_ROW -->
<div class="block {LIST_ROW_ODDEVEN} paddingleft10 paddingright10">
    <h3 style="margin-bottom: 0">{LIST_ROW_NUM}. <a href="{LIST_ROW_URL}">{LIST_ROW_NUMBER}</a></h3>
    <table class="table">
        <tr>
            <td class="{LIST_ROW_ODDEVEN} width20">
                {PHP.L.shop.order_number}: {LIST_ROW_NUMBER}<br/>
                {PHP.L.shop.order_create_date}: {LIST_ROW_CREATE_DATE}<br/>
            </td>
            <td class="{LIST_ROW_ODDEVEN} paddingleft10 width55">
                {PHP.L.Status}: <strong>{LIST_ROW_STATUS_TITLE}</strong><br/>
                {PHP.L.shop.payment_method}: {LIST_ROW_PAYMENT_TITLE}<br/>
                {LIST_ROW_PAYMENT_TEXT}
            </td>
            <td class="width20">
                {PHP.L.shop.order_total}:<br /> <strong>{LIST_ROW_TOTAL}</strong><br/>
            </td>
            <!-- IF {PHP.usr.isadmin} -->
            <td class="{LIST_ROW_ODDEVEN} paddingleft10 centerall">
                <a href="{LIST_ROW_ID|cot_url('admin', 'm=shop&n=order&a=edit&id=$this')}"><img
                            src="images/icons/default/arrow-follow.png"/></a>

            </td>
            <!-- ENDIF -->
        </tr>
    </table>
</div>
<!-- END: LIST_ROW -->

<!-- IF {LIST_TOTALLINES} == '0' -->
<tr>
    <div class="block">
        <h3>{PHP.L.None}</h3>
    </div>
</tr>
<!-- ENDIF -->

<!-- IF {LIST_CURRENTPAGE} -->
<div class="paging">
    {LIST_PAGEPREV}{LIST_PAGINATION}{LIST_PAGENEXT}<span>{PHP.L.Total}: {LIST_TOTALLINES},
        {PHP.L.Onpage}: {LIST_ITEMS_ON_PAGE}</span>
</div>
<!-- ENDIF -->

<!-- END: MAIN -->