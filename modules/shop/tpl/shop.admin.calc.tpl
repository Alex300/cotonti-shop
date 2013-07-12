<!-- BEGIN: MAIN -->

<!-- IF {PAGE_TITLE} -->
<h2 class="tags">{PAGE_TITLE}</h2>
<!-- ENDIF -->

<div style="text-align: right">
    {SORT_BY} {SORT_WAY}
</div>

<!-- BEGIN: NR_CATS -->
<div class="margin10">
    <h4>{PHP.L.shop.cats_with_no_calcs}:</h4>
    <!-- BEGIN: ROW -->
        <!-- IF {NR_ROWCAT_NUM} > 1 -->, <!-- ENDIF -->
        <a href="{NR_ROWCAT_URL}" target="_blank">{NR_ROWCAT_TITLE}</a>
    <!-- END: ROW -->
</div>
<!-- END: NR_CATS -->

<form method="post" action="{LIST_URL}">
<input type="hidden" name ="a" value="masssave" />
<input type="hidden" name ="ret" value="{LIST_URL}" />

<table class="cells">
    <tr>
        <td class="coltop"></td>
        <!--<td class="coltop"><input type="checkbox" onclick="checkAll(20)" value="" name="toggle"></td>-->
        <td class="coltop">{PHP.L.Title}</td>
        <td class="coltop">{PHP.L.Description}</td>
        <!-- IF {PHP.usr.isadmin} -->
		<td class="coltop">{PHP.L.shop.vendor}</td>
        <!-- ENDIF -->
        <td class="coltop">{PHP.L.shop.calc_kind}</td>
        <td class="coltop">{PHP.L.shop.calc_value_mathop}</td>
        <td class="coltop">{PHP.L.Value}</td>
        <td class="coltop">{PHP.L.shop.currency}</td>
        <td class="coltop">{PHP.L.Categories}</td>
        <td class="coltop">{PHP.L.Groups}</td>
        <td class="coltop">{PHP.L.shop.visible_for_shopper}</td>
        <td class="coltop">{PHP.L.Begin}</td>
        <td class="coltop">{PHP.L.Expire}</td>
        <td class="coltop">{PHP.L.shop.countries}</td>
        <!-- TODO штат/область/регион -->
        <!-- <td class="coltop">Order</td>-->
        <td class="coltop">{PHP.L.shop.published}</td>
        <td class="coltop">{PHP.L.Edit}</td>
        <td class="coltop">Del.</td>
        <td class="coltop">ID</td>
    </tr>
    <!-- BEGIN: LIST_ROW -->
    <tr>
        <td class="{LIST_ROW_ODDEVEN} centerall">{LIST_ROW_NUM}</td>
        <!-- <td><input id="cb{LIST_ROW_ID}" type="checkbox" onclick="isChecked(this.checked);" value="25" 
                   name="product_id[]">
        </td>-->
        <td class="{LIST_ROW_ODDEVEN}">
            <a href="{LIST_ROW_ID|cot_url('admin', 'm=shop&n=calc&a=edit&id=$this')}">{LIST_ROW_TITLE}</a>
        </td>
        <td class="{LIST_ROW_ODDEVEN}">{LIST_ROW_DESC}</td>
        <td class="{LIST_ROW_ODDEVEN} centerall">{LIST_ROW_VENDOR_ID}</td>
        <td class="{LIST_ROW_ODDEVEN} centerall">{LIST_ROW_KIND_TITLE}</td>
        <td class="{LIST_ROW_ODDEVEN} centerall">{LIST_ROW_MATH_OPERATION}</td>
        <td class="{LIST_ROW_ODDEVEN} centerall">{LIST_ROW_VALUE}</td>
        <td class="{LIST_ROW_ODDEVEN}">{LIST_ROW_CURRENCY_TITLE} ({LIST_ROW_CURRENCY_SYMBOL})</td>
        <td class="{LIST_ROW_ODDEVEN}">
            <!-- FOR {KEY},{VALUE} IN {LIST_ROW_CATEGORIES_ARR_RAW} -->
            {VALUE.title}<br />
            <!-- ENDFOR -->
        </td>
        <td class="{LIST_ROW_ODDEVEN}">
            <!-- FOR {KEY},{VALUE} IN {LIST_ROW_GROUPS_ARR_RAW} -->
            {VALUE.title}<br />
            <!-- ENDFOR -->
        </td>
        <td class="{LIST_ROW_ODDEVEN} centerall">{LIST_ROW_SHOPPER_PUBLISHED_TITLE}</td>
        <td class="{LIST_ROW_ODDEVEN} centerall">{LIST_ROW_BEGIN}</td>
        <td class="{LIST_ROW_ODDEVEN} centerall">{LIST_ROW_EXPIRE}</td>
        <td class="{LIST_ROW_ODDEVEN}">
             <!-- FOR {KEY},{VALUE} IN {LIST_ROW_COUNTRIES_ARR_RAW} -->
            {VALUE}<br />
            <!-- ENDFOR -->
        </td>
        <!-- <td class="{LIST_ROW_ODDEVEN} centerall">{LIST_ROW_ORDER}</td>-->
        <td class="{LIST_ROW_ODDEVEN} centerall">{LIST_ROW_PUBLISHED_TITLE}</td>
        <td class="{LIST_ROW_ODDEVEN} centerall">
            <a href="{LIST_ROW_ID|cot_url('admin', 'm=shop&n=calc&a=edit&id=$this')}"><img src="images/icons/default/prefs.png" /></a>
            
        </td>
        <td class="{LIST_ROW_ODDEVEN} centerall">
            <a href="{LIST_ROW_DELETE_URL}" class="confirmLink"><img src="images/icons/default/delete.png" /></a>
        </td>
        <td class="{LIST_ROW_ODDEVEN} centerall">{LIST_ROW_ID}</td>
    </tr>
    <!-- END: LIST_ROW -->
    
    <!-- IF {LIST_TOTALLINES} == '0' -->
    <tr>
        <td class="odd centerall" colspan="19">{PHP.L.None}</td>
    </tr>
    <!-- ENDIF -->
    
</table>

<!-- IF {LIST_TOTALLINES} > 0 AND 0 -->
        пока убрано
<input type="submit" value="{PHP.L.Submit}" />
<!-- ENDIF -->

<input type="button" onclick="window.location='{PHP|cot_url('admin', 'm=shop&n=calc&a=edit')}'" value="{PHP.L.Add}" />

</form>

<!-- IF {LIST_CURRENTPAGE} -->
<div class="paging">
{LIST_PAGEPREV}{LIST_PAGINATION}{LIST_PAGENEXT}<span>{PHP.L.Page}: {LIST_CURRENTPAGE}, {PHP.L.Total}: {LIST_TOTALLINES}, 
    {PHP.L.Onpage}: {LIST_ITEMS_ON_PAGE}</span>
</div>
<!-- ENDIF -->


<!-- END: MAIN -->


<!-- BEGIN: EDIT -->
    <!-- IF {PAGE_TITLE} -->
    <h2 class="tags">{PAGE_TITLE}</h2>
    <!-- ENDIF -->
    
    <!-- BEGIN: FORM -->
    <form method="post" action="{PHP|cot_url('admin', 'm=shop&n=calc&a=edit')}">
        <input type="hidden" name="rid" value="{FORM_ID}" />
        <input type="hidden" name="act" value="save" />

        <table class="cells">
            <tr>
                <td>{PHP.L.Title}:</td>
                <td>{FORM_TITLE}</td>
            </tr>
            <tr>
                <td>{PHP.L.Description}:</td>
                <td>{FORM_DESC}</td>
            </tr>
            <tr>
                <td>{PHP.L.shop.calc_kind}:</td>
                <td>{FORM_CALC_KIND}<br />{PHP.L.shop.calc_kind_desc}</td>
            </tr>
            <tr>
                <td>{PHP.L.shop.calc_value_mathop}:</td>
                <td>{FORM_MATH_OPERATION}</td>
            </tr>
            <tr>
                <td>{PHP.L.Value}:</td>
                <td>{FORM_VALUE}</td>
            </tr>
            <tr>
                <td>{PHP.L.shop.currency}:</td>
                <td>{FORM_CURRENCY}</td>
            </tr>
            <tr>
                <td>{PHP.L.Categories}:</td>
                <td>{FORM_CATEGORIES}</td>
            </tr>
            <tr>
                <td>{PHP.L.Groups}:</td>
                <td>{FORM_USER_GROUP}</td>
            </tr>
            <tr>
                <td>{PHP.L.shop.countries}:</td>
                <td>{FORM_COUNTRIES}</td>
            </tr>
            <!-- TODO штат/область/регион -->
            <tr>
                <td>{PHP.L.shop.visible_for_shopper}:</td>
                <td>{FORM_SHOPPER_PUBLISHED}</td>
            </tr>
            <tr>
                <td>{PHP.L.shop.visible_for_vendor}:</td>
                <td>{FORM_VENDOR_PUBLISHED}</td>
            </tr>
            <tr>
                <td>{PHP.L.Begin}:</td>
                <td>{FORM_BEGIN}</td>
            </tr>
            <tr>
                <td>{PHP.L.Expire}:</td>
                <td>{FORM_EXPIRE}</td>
            </tr>
            <tr>
                <td>{PHP.L.shop.published}:</td>
                <td>{FORM_PUBLISHED}</td>
            </tr>
            
            <!-- IF {FORM_VENDOR} -->
            <tr>
                <td>{PHP.L.shop.vendor}:</td>
                <td>{FORM_VENDOR}</td>
            </tr>
            <!-- ENDIF -->
        </table>
            
        <input type="submit" value="{PHP.L.Submit}" />
    </form>
    <!-- END: FORM -->
<!-- END: EDIT -->