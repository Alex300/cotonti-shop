<!-- BEGIN: MAIN -->
<style>
    .centerall 	{ text-align:center; vertical-align:middle; }
    .width5		{ width:5%; }
    .width10	{ width:10%; }
</style>
<table>
    <tr>
        <td><!-- Логотип продавца --></td>
        <td>
            <!-- TODO vendor info -->
            <!-- title - обращение, г-н, г-жа, mr. -->
            <p>{PHP.L.shop.mail_hello} <strong>{PHP.order.BT.title} {ORDER_BILL_TO.last_name}
                {ORDER_BILL_TO.first_name} {ORDER_BILL_TO.middle_name}!</strong></p>
            <p style="margin-top:10px">{PHP.L.shop.mail_shopper_thankyou} <a href="{PHP.cfg.shop.mainPageUrl}">{PHP.vendor.vendor_title|htmlspecialchars($this)}</a>.</p>
            <p style="margin-top:10px">{PHP.L.shop.mail_shopper_info_lbl}.</p>
        </td>
    </tr>
</table>
<h3>{PHP.L.shop.order_info}</h3>

{PHP.L.shop.order_status}: <strong>{ORDER_STATUS_TITLE}</strong><br />
{PHP.L.Date}: {DATE}
<!-- IF {ORDER_VENDOR_COMMENT} != '' -->
<br />{PHP.L.Notes}: {ORDER_VENDOR_COMMENT}
<!-- ENDIF -->
<p>&nbsp;</p>
{PHP.L.shop.order_number}: <strong>{ORDER_NUMBER}</strong><br />
{PHP.L.shop.order_create_date}: <strong>{ORDER_CREATE_DATE}</strong><br />
{PHP.L.shop.order_total}: <strong>{ORDER_TOTAL}</strong><br />
<p><a href="{ORDER_SHOPPER_LINK}">{PHP.L.shop.mail_shopper_your_order_link}</a></p>



<!-- IF {PHP.order.order_customer_note} -->
<h3>{PHP.L.shop.mail_shopper_comment}</h3>
{PHP.order.order_customer_note}
<!-- ENDIF -->

<p>&nbsp;</p>
<p style="margin-top: 15px;">
    {PHP.L.shop.mail_shopper_question}: <a href="mailto:{PHP.vendor.vendor_email}">{PHP.vendor.vendor_email}</a>
</p>
<!-- END: MAIN -->