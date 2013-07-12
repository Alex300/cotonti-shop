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
            <p>{PHP.L.shop.mail_hello} <strong>{WU_NAME}!</strong></p>
        </td>
    </tr>
</table>
<h3>{PHP.L.shop.receipt_goods}</h3>

{WU_MESSAGE}

<p>&nbsp;</p>
<p style="margin-top: 15px;">
    {PHP.L.shop.mail_shopper_question}: <a href="mailto:{PHP.vendor.vendor_email}">{PHP.vendor.vendor_email}</a>
</p>
<!-- END: MAIN -->