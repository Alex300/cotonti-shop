<!-- BEGIN: MAIN -->

<!-- IF {PAGE_TITLE} -->
<h2 class="tags">{PAGE_TITLE}</h2>
<!-- ENDIF -->

<div style="text-align: right">
    {SORT_BY} {SORT_WAY}
</div>


<form method="post" action="{LIST_URL}">
<input type="hidden" name ="a" value="masssave" />
<input type="hidden" name ="ret" value="{LIST_URL}" />

<table class="cells">
    <tr>
        <td class="coltop"></td>
        <!--<td class="coltop"><input type="checkbox" onclick="checkAll(20)" value="" name="toggle"></td>-->
        <td class="coltop">{PHP.L.shop.payment_title}</td>
        <td class="coltop">{PHP.L.shop.payment_description}</td>
        <!-- IF {PHP.usr.isadmin} -->
		<td class="coltop">{PHP.L.shop.vendor}</td>
        <!-- ENDIF -->
        <td class="coltop">{PHP.L.Groups}</td>
        <td class="coltop">{PHP.L.shop.payment_plugin}</td>
        <td class="coltop">Order</td>
        <td class="coltop">{PHP.L.shop.published}</td>
        <td class="coltop">{PHP.L.shop.shared}</td>
        <td class="coltop">Edit.</td>
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
            <a href="{LIST_ROW_ID|cot_url('admin', 'm=shop&n=paymentmethod&a=edit&id=$this')}">{LIST_ROW_TITLE}</a>
        </td>
        <td class="{LIST_ROW_ODDEVEN}">{LIST_ROW_DESC}</td>
        <td class="{LIST_ROW_ODDEVEN}">{LIST_ROW_VENDOR_ID}</td>
        <td class="{LIST_ROW_ODDEVEN}">
            <!-- FOR {KEY},{VALUE} IN {LIST_ROW_GROUPS_ARR_RAW} -->
            {VALUE.title}<br />
            <!-- ENDFOR -->
            
        </td>
        <td class="{LIST_ROW_ODDEVEN}">{LIST_ROW_PLUGIN_TITLE}</td>
        <td class="{LIST_ROW_ODDEVEN} centerall">{LIST_ROW_PUBLISH}</td>
        <td class="{LIST_ROW_ODDEVEN} centerall">{LIST_ROW_PUBLISHED}</td>
        <td class="{LIST_ROW_ODDEVEN} centerall">{LIST_ROW_SHARED}</td>
        <td class="{LIST_ROW_ODDEVEN} centerall">
            <a href="{LIST_ROW_ID|cot_url('admin', 'm=shop&n=paymentmethod&a=edit&id=$this')}"><img src="images/icons/default/prefs.png" /></a>
            
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

<!-- IF {LIST_PAY_PLUG_INSTALLED} -->
<input type="button" onclick="window.location='{PHP|cot_url('admin', 'm=shop&n=paymentmethod&a=edit')}'" value="{PHP.L.Add}" />
<!-- ENDIF -->
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
    <form method="post" action="{PHP|cot_url('admin', 'm=shop&n=paymentmethod&a=edit')}">
        <input type="hidden" name="rid" value="{FORM_ID}" />
        <input type="hidden" name="act" value="save" />
        <h3>{PHP.L.shop.payment_method}</h3>
        <table class="cells">
            <tr>
                <td>{PHP.L.shop.payment_title}:</td>
                <td>{FORM_TITLE}</td>
            </tr>
            <tr>
                <td>{PHP.L.shop.payment_description}:</td>
                <td>{FORM_DESC}</td>
            </tr>
            <tr>
                <td>{PHP.L.shop.published}:</td>
                <td>{FORM_PUBLISHED}</td>
            </tr>
            <tr>
                <td>{PHP.L.shop.payment_plugin}:</td>
                <td>{FORM_PLUGIN_CODE}</td>
            </tr>
            <tr>
                <td>{PHP.L.Groups}:</td>
                <td>{FORM_USER_GROUP}</td>
            </tr>
            <!-- IF {PHP.cfg.shop.multix} != 0 -->
            <tr>
                <td>{PHP.L.shop.vendor}:</td>
                <td>{FORM_VENDOR}</td>
            </tr>
            <!-- ENDIF -->
        </table>
        
        <!-- IF !{FORM_ID} -->
        <p class="strong">{PHP.L.shop.select_payment_method}</p>
        <!-- ENDIF -->
        
        <!-- BEGIN: PLUG_CONFIG -->
        <h3><img src="system/admin/img/config.png" style="vertical-align:middle;" /> {PHP.L.Configuration}</h3>
            <table class="cells">
            <!-- BEGIN: CONFIG_ROW -->
                <!-- BEGIN: CONFIG_FIELDSET_BEGIN -->
                    <tr>
                        <td class="group_begin" colspan="2">
                            <h4>{CONFIG_FIELDSET_TITLE}</h4>
                        </td>
                    </tr>
                <!-- END: CONFIG_FIELDSET_BEGIN -->

                <!-- BEGIN: CONFIG_ROW_OPTION -->
                    <tr>
                        <td>{CONFIG_ROW_CONFIG_TITLE}:</td>
                        <td>{CONFIG_ROW_CONFIG}<br />{CONFIG_ROW_CONFIG_MORE}</td>
                    </tr>
                <!-- END: CONFIG_ROW_OPTION -->
                
                <!-- BEGIN: CONFIG_FIELDSET_END -->
                    <tr>
                        <td class="group_end" colspan="2"></td>
                    </tr>
                <!-- END: CONFIG_FIELDSET_END -->                
            <!-- END: CONFIG_ROW -->
            </table>
       <!-- END: PLUG_CONFIG -->
            
        <input type="submit" value="{PHP.L.Submit}" />
    </form>
    <!-- END: FORM -->
<!-- END: EDIT -->