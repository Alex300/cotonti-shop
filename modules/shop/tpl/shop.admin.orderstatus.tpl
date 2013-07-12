<!-- BEGIN: MAIN -->

<!-- IF {PAGE_TITLE} -->
<h2 class="tags">{PAGE_TITLE}</h2>
<!-- ENDIF -->

<div style="text-align: right">
    {SORT_BY} {SORT_WAY}
</div>


<!-- TODO фильтры -->

<form method="post" action="{LIST_URL}">
<input type="hidden" name ="a" value="masssave" />

<table class="cells">
    <tr>
        <td class="coltop"></td>
        <!--<td class="coltop"><input type="checkbox" onclick="checkAll(20)" value="" name="toggle"></td>-->
        <td class="coltop" colspan="2">{PHP.L.Title} / {PHP.lang}</td>
        <td class="coltop">{PHP.L.Code}</td>
        <td class="coltop">{PHP.L.Description}</td>
        <!--<td class="coltop">{PHP.L.Order}</td> -->
        <td class="coltop">{PHP.L.shop.published}</td>
        <td class="coltop"></td>
        <td class="coltop"></td>
        <td class="coltop">ID</td>
    </tr>
    <!-- BEGIN: LIST_ROW -->
    <tr>
        <td class="{LIST_ROW_ODDEVEN} centerall">{LIST_ROW_NUM}</td>
        <!-- <td><input id="cb{LIST_ROW_ID}" type="checkbox" onclick="isChecked(this.checked);" value="25" 
                   name="product_id[]">
        </td>-->
        <td class="{LIST_ROW_ODDEVEN}" style="border-right: none"><a href="{LIST_ROW_URL}">{LIST_ROW_TITLE}</a></td>
        <td class="{LIST_ROW_ODDEVEN}" style="border-left: none">{LIST_ROW_TITLE_LOCAL}</td>
        <td class="{LIST_ROW_ODDEVEN} centerall">{LIST_ROW_CODE}</td>
        <td class="{LIST_ROW_ODDEVEN}">{LIST_ROW_DESC}</td>
        <!--<td class="{LIST_ROW_ODDEVEN}">{LIST_ROW_ODRER}</td>-->
        <td class="{LIST_ROW_ODDEVEN} centerall">{LIST_ROW_PUBLISHED}</td>
        <td class="{LIST_ROW_ODDEVEN} centerall">
            <a href="{LIST_ROW_ID|cot_url('admin', 'm=shop&n=orderstatus&a=edit&id=$this')}"><img src="images/icons/default/arrow-follow.png" /></a>
        </td>
        <td class="{LIST_ROW_ODDEVEN} centerall">
            <!-- IF {LIST_ROW_DELETE_URL} != '' -->
            <a href="{LIST_ROW_DELETE_URL}" class="confirmLink"><img src="images/icons/default/delete.png" /></a>
            <!-- ENDIF -->
        </td>
        <td class="{LIST_ROW_ODDEVEN} centerall">{LIST_ROW_ID}</td>
    </tr>
    <!-- END: LIST_ROW -->
    
    <!-- IF {LIST_TOTALLINES} == '0' -->
    <tr>
        <td class="odd centerall" colspan="12">{PHP.L.None}</td>
    </tr>
    <!-- ENDIF -->
    
</table>

<!-- IF {LIST_TOTALLINES} > 0 AND 0 -->
        пока убрано
<input type="submit" value="{PHP.L.Submit}" />
<!-- ENDIF -->

<input type="button" onclick="window.location='{PHP|cot_url('admin', 'm=shop&n=orderstatus&a=edit')}'" value="{PHP.L.Add}" />
</form>

<!-- IF {LIST_CURRENTPAGE} -->
<div class="paging">
{LIST_PAGEPREV}{LIST_PAGINATION}{LIST_PAGENEXT}<span>{PHP.L.Total}: {LIST_TOTALLINES},
    {PHP.L.Onpage}: {LIST_ITEMS_ON_PAGE}</span>
</div>
<!-- ENDIF -->


<!-- END: MAIN -->


<!-- BEGIN: EDIT -->
    <!-- IF {PAGE_TITLE} -->
    <h2 class="tags">{PAGE_TITLE}</h2>
    <!-- ENDIF -->
    
    <!-- BEGIN: FORM -->
    <form action="{PHP|cot_url('admin', 'm=shop&n=orderstatus&a=edit')}" method="POST">
        <input type="hidden" name="rid" value="{FORM_ID}" />
        <input type="hidden" name="act" value="save" />

        <table class="cells">
            <tr>
                <td>{PHP.L.Title}:</td>
                <td>{FORM_TITLE}</td>
            </tr>
            <tr>
                <td>{PHP.L.shop.from_lang_file} <i>({PHP.lang})</i>:</td>
                <td>{TITLE_LOCAL}</td>
            </tr>
            <tr>
                <td>{PHP.L.shop.stock_handle}:</td>
                <td>{FORM_STOCK_HANDLE}<br />{PHP.L.shop.stock_handle_tip}</td>
            </tr>
            <tr>
                <td>{PHP.L.Code}:</td>
                <td>{FORM_CODE}</td>
            </tr>
            <tr>
                <td>{PHP.L.Description}:</td>
                <td>{FORM_DESC}</td>
            </tr>
            <!-- TODO Vendor -->
            <!-- TODO Order (порядок) -->
        </table>

        <input type="submit" value="{PHP.L.Submit}" />

        <!-- IF {FORM_ID} > 0 AND {FORM_DELETE_URL} != '' -->
        <a href="{FORM_DELETE_URL}" class="confirmLink button"><img src="images/icons/default/delete.png" style="vertical-align: middle;" />
        {PHP.L.Delete}</a>
        <!-- ENDIF -->
    </form>
    <!-- END: FORM -->


<!-- END: EDIT -->