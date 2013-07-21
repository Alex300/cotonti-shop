<!-- BEGIN: MAIN -->
<div id="breadcrumbs">{BREAD_CRUMBS}</div>

<h2 class="tags">{PAGE_TITLE}</h2>

{FILE "{PHP.cfg.themes_dir}/{PHP.cfg.defaulttheme}/warnings.tpl"}

<div>
    {PHP.L.shop.order_number}: <strong>{ORDER_NUMBER}</strong><br/>
    {PHP.L.shop.order_create_date}: <strong>{ORDER_CREATE_DATE}</strong><br/>
    <!-- todo last updated date -->
    {PHP.L.shop.order_status}: <strong>{ORDER_STATUS_TITLE}</strong><br/>
    {PHP.L.shop.payment_method}: {ORDER_PAYMENT_TITLE}
    <!-- IF {ORDER_PAYMENT_DESC} --><span class="small italic">({ORDER_PAYMENT_DESC})</span><!-- ENDIF --><br/>
    {PHP.L.shop.shipment_method}: {ORDER_SHIPMENT_TITLE}
    <!-- IF {ORDER_SHIPMENT_DESC} --><span class="small italic">({ORDER_SHIPMENT_DESC})</span><!-- ENDIF --><br/>

    {PHP.L.shop.order_total}: <strong>{ORDER_TOTAL}</strong>
</div>

<!-- IF {ORDER_CUSTOMER_NOTE} != '' -->
    <div class="row-fluid margintop10">
    <!-- IF {PHP.usr.id} == {ORDER_USER_ID} -->
    <h3>{PHP.L.shop.mail_shopper_comment}:</h3>
    <!-- ELSE -->
    <h3>{PHP.L.shop.mail_vendor_shopper_comment}:</h3>
    <!-- ENDIF -->
    {ORDER_CUSTOMER_NOTE}
</div>
<!-- ENDIF -->

<table class="width100 margintop10" style="table-layout: fixed;">
    <tr>
        <td class="width50">
            <table class="cells">
                <tr>
                    <td class="coltop" colspan="2">{PHP.L.shop.user_form_billto_lbl}:</td>
                </tr>

                <tr>
                    <td>
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
                </tr>
            </table>

        </td>
        <!-- IF {ORDER_SHIP_TO_RAW} -->
        <td class="width50">
            <table class="cells">
                <tr>
                    <td class="coltop" colspan="2">{PHP.L.shop.user_form_shipto}:</td>
                </tr>
                <tr>
                    <td>
                        <!-- IF {ORDER_SHIP_TO.company} -->
                        <p class="strong margin0">{ORDER_SHIP_TO.company}</p>
                        <!-- ENDIF -->
                        <!-- IF {ORDER_SHIP_TO.firstname} OR {ORDER_SHIP_TO.middlename} OR {ORDER_SHIP_TO.lastname}  -->
                        <p class="strong margin0">{ORDER_SHIP_TO.firstname} {ORDER_SHIP_TO.middlename} {ORDER_SHIP_TO.lastname}</p>
                        <!-- ENDIF -->

                        <!-- IF {ORDER_SHIP_TO.country} OR {ORDER_SHIP_TO.city_name} OR {ORDER_SHIP_TO.address} -->
                        <!-- IF {ORDER_SHIP_TO.zip} -->{ORDER_SHIP_TO.zip} <!-- ENDIF -->
                        {ORDER_SHIP_TO.country_name},
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
        </td>
        <!-- ENDIF -->
    </tr>
</table>

<!-- IF {ORDER_PAYMENT_TEXT} -->
<div class="row-fluid margintop10 marginbottom10">{ORDER_PAYMENT_TEXT}</div>
<!-- ENDIF -->

<!-- IF {ORDER_SHIPMENT_TEXT} -->
<div class="row-fluid margintop10 marginbottom10">{ORDER_SHIPMENT_TEXT}</div>
<!-- ENDIF -->

<h3 class="margintop10">{PHP.L.shop.order_items}:</h3>
{FILE "{PHP.priceTpl}.tpl"}

<!-- END: MAIN -->