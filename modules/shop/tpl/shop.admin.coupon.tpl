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
        <td class="coltop">{PHP.L.shop.coupon_code}</td>
        <td class="coltop">{PHP.L.shop.coupon_percent_total}</td>
        <td class="coltop">{PHP.L.shop.coupon_type}</td>
        <td class="coltop">{PHP.L.Value}</td>
        <td class="coltop">{PHP.L.shop.order_min_total}</td>
        <td class="coltop">{PHP.L.shop.coupon_start}</td>
        <td class="coltop">{PHP.L.shop.coupon_expiry}</td>
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
        <td class="{LIST_ROW_ODDEVEN}"><a href="{LIST_ROW_URL}">{LIST_ROW_CODE}</a></td>
        <td class="{LIST_ROW_ODDEVEN}">{LIST_ROW_PER_O_TOTAL}</td>
        <td class="{LIST_ROW_ODDEVEN}">{LIST_ROW_TYPE}</td>
        <td class="{LIST_ROW_ODDEVEN}">
            {LIST_ROW_VALUE}
            <!-- IF {LIST_ROW_PER_O_TOTAL_RAW} == 'percent' -->%<!-- ELSE -->{LIST_ROW_CURRENCY_SYMBOL}<!-- ENDIF -->
        </td>
        <td class="{LIST_ROW_ODDEVEN}">{LIST_ROW_MIN_ORDER_TOTAL} {LIST_ROW_CURRENCY_SYMBOL}</td>
        <td class="{LIST_ROW_ODDEVEN}">{LIST_ROW_START_DATE}</td>
        <td class="{LIST_ROW_ODDEVEN}">{LIST_ROW_EXPIRY_DATE}</td>
        <td class="{LIST_ROW_ODDEVEN} centerall">{LIST_ROW_PUBLISHED}</td>
        <td class="{LIST_ROW_ODDEVEN} centerall">
            <a href="{LIST_ROW_ID|cot_url('admin', 'm=shop&n=coupon&a=edit&id=$this')}"><img src="images/icons/default/arrow-follow.png" /></a>
            
        </td>
        <td class="{LIST_ROW_ODDEVEN} centerall">
            <a href="{LIST_ROW_DELETE_URL}" class="confirmLink"><img src="images/icons/default/delete.png" /></a>
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

<input type="button" onclick="window.location='{PHP|cot_url('admin', 'm=shop&n=coupon&a=edit')}'" value="{PHP.L.Add}" />
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
    <form action="{PHP|cot_url('admin', 'm=shop&n=coupon&a=edit')}" method="POST">
        <input type="hidden" name="rid" value="{FORM_ID}" />
        <input type="hidden" name="act" value="save" />

        <table class="cells">
            <tr>
                <td>{PHP.L.shop.coupon_code}:</td>
                <td>{FORM_CODE}</td>
            </tr>
            <tr>
                <td>{PHP.L.Value}:</td>
                <td>{FORM_VALUE}</td>
            </tr>
            <tr>
                <td>{PHP.L.shop.coupon_percent_total}:</td>
                <td>{FORM_PERSENT_TOTAL}</td>
            </tr>
            <tr>
                <td>{PHP.L.shop.coupon_type}:</td>
                <td>{FORM_TYPE}<br />{PHP.L.shop.coupon_type_tip}</td>
            </tr>
            <tr>
                <td>{PHP.L.shop.order_min_total}:</td>
                <td>{FORM_MIN_TOTAL} {FORM_CURRENCY_SYMBOL}</td>
            </tr>
            <tr>
                <td>{PHP.L.shop.coupon_start}:</td>
                <td>{FORM_START_DATE}</td>
            </tr>
            <tr>
                <td>{PHP.L.shop.coupon_expiry}:</td>
                <td>{FORM_END_DATE}<br />{PHP.L.shop.coupon_expiry_tip}</td>
            </tr>
            <tr>
                <td>{PHP.L.shop.published}:</td>
                <td>{FORM_PUBLISHED}</td>
            </tr>
        </table>

        <input type="submit" value="{PHP.L.Submit}" />

        <!-- IF {FORM_ID} > 0 -->
        <a href="{FORM_DELETE_URL}" class="confirmLink button"><img src="images/icons/default/delete.png" style="vertical-align: middle;" />
        {PHP.L.Delete}</a>
        <!-- ENDIF -->
    </form>
    <!-- END: FORM -->


<!-- END: EDIT -->