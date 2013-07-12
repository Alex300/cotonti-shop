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
            <p>{PHP.L.shop.mail_hello} <strong>{PHP.vendor.vendor_title|htmlspecialchars($this)}!</strong></p>
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
{PHP.L.shop.order_id}: <strong>{ORDER_ID}</strong><br />
{PHP.L.shop.order_number}: <strong>{ORDER_NUMBER}</strong><br />
{PHP.L.shop.order_create_date}: <strong>{ORDER_CREATE_DATE}</strong><br />
{PHP.L.shop.order_total}: <strong>{ORDER_TOTAL}</strong><br />
<p><a href="{ORDER_VENDOR_LINK}">{PHP.L.shop.mail_shopper_your_order_link}</a></p>

<h3>{PHP.L.shop.shopper_info}</h3>
<table style="width: 100%">
    <tr>
        <td style="width: 50%">
            <p style="font-weight: bold">{PHP.L.shop.user_form_billto_lbl}:</p>
            <!-- IF {ORDER_BILL_TO.company} -->
            <p class="strong margin0">{ORDER_BILL_TO.company}</p>
            <!-- ENDIF -->
            <!-- IF {ORDER_BILL_TO.firstname} OR {ORDER_BILL_TO.middlename} OR {ORDER_BILL_TO.lastname}  -->
            <p class="strong margin0">{ORDER_BILL_TO.firstname} {ORDER_BILL_TO.middlename} {ORDER_BILL_TO.lastname}</p>
            <!-- ENDIF -->

            <!-- IF {ORDER_BILL_TO.country} OR {ORDER_BILL_TO.city_name} OR {ORDER_BILL_TO.address} -->
            <!-- IF {ORDER_BILL_TO.zip} -->{ORDER_BILL_TO.zip}<!-- ENDIF -->
            {ORDER_BILL_TO.country_name},
            <!-- IF {ORDER_BILL_TO.region_name} -->{ORDER_BILL_TO.region_name},<!-- ENDIF -->
            {ORDER_BILL_TO.city_name}, {ORDER_BILL_TO.address}
            <!-- ENDIF -->

            <table class="table margintop10 small">
                <!-- FOR {KEY}, {VALUE} IN {ORDER_BILL_TO_RAW} -->
                <!-- IF {KEY} != 'agreed' AND {VALUE} != '' AND {KEY} != 'title' AND {KEY} != 'company' AND {KEY} != 'firstname' AND {KEY} != 'middlename' AND {KEY} != 'lastname' AND {KEY} != 'country' AND {KEY} != 'address' AND {KEY} != 'zip' AND {KEY} != 'city_name' AND {KEY} != 'city' AND {KEY} != 'region_name' AND {KEY} != 'region' -->
                <tr>
                    <td style="padding: 0 5px">{VALUE.field_title}</td>
                    <td style="padding: 0 5px">{VALUE.field_val}</td>
                </tr>
                <!-- ENDIF -->
                <!-- ENDFOR -->
            </table>
        </td>
        <td style="width: 50%">
            <!-- IF {ORDER_SHIP_TO.company} -->
            <p class="strong margin0">{SHIP_TO.company}</p>
            <!-- ENDIF -->
            <!-- IF {ORDER_SHIP_TO.firstname} OR {ORDER_SHIP_TO.middlename} OR {ORDER_SHIP_TO.lastname}  -->
            <p class="strong margin0">{SHIP_TO.firstname} {SHIP_TO.middlename} {ORDER_SHIP_TO.lastname}</p>
            <!-- ENDIF -->

            <!-- IF {ORDER_SHIP_TO.country} OR {ORDER_SHIP_TO.city_name} OR {ORDER_SHIP_TO.address} -->
            <!-- IF {ORDER_SHIP_TO.zip} -->{ORDER_SHIP_TO.zip} <!-- ENDIF -->
            {SHIP_TO.country_name},
            <!-- IF {ORDER_SHIP_TO.region_name} -->{ORDER_SHIP_TO.region_name},<!-- ENDIF -->
            {ORDER_SHIP_TO.city_name}, {ORDER_SHIP_TO.address}
            <!-- ENDIF -->

            <table class="table margintop10 small">
                <!-- FOR {KEY}, {VALUE} IN {ORDER_SHIP_TO_RAW} -->
                <!-- IF {KEY} != 'agreed' AND {VALUE} != '' AND {KEY} != 'title' AND {KEY} != 'company' AND {KEY} != 'firstname' AND {KEY} != 'middlename' AND {KEY} != 'lastname' AND {KEY} != 'country' AND {KEY} != 'address' AND {KEY} != 'zip' AND {KEY} != 'city_name' AND {KEY} != 'city' AND {KEY} != 'region_name' AND {KEY} != 'region' -->
                <tr>
                    <td style="padding: 0 5px">{VALUE.field_title}</td>
                    <td style="padding: 0 5px">{VALUE.field_val}</td>
                </tr>
                <!-- ENDIF -->
                <!-- ENDFOR -->
            </table>
        </td>
    </tr>
</table>


<!-- IF {PHP.order.order_customer_note} -->
<h3>{PHP.L.shop.mail_shopper_comment}</h3>
{PHP.order.order_customer_note}
<!-- ENDIF -->


<!-- END: MAIN -->