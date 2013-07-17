<h4>{PHP.L.shop.order_update_status}</h4>
<form action="{ORDER_ID|cot_url('admin', 'm=shop&n=order&a=edit&id=$this')}" method="post" name="orderStatForm" id="orderStatForm">
        <table class="flat" width="100%">

            <tr>
                <td class="textleft">{PHP.L.shop.order_status}</td>
                <td class="textleft">{UPDST_ORDER_STATUS}</td>
            </tr>
            <tr>
                <td class="textleft">{PHP.L.shop.comment}</td>
                <td class="textleft"><textarea rows="6" cols="35" name="rcomment"></textarea></td>
            </tr>
            <tr>
                <td class="textleft">{PHP.L.shop.customer_notify}</td>
                <td class="textleft">{UPDST_NOTIFY}</td>
            </tr>
            <tr>
                <td class="textleft">{PHP.L.shop.include_comment}</td>
                <td class="textleft">{UPDST_INC_COMMENT}</td>
            </tr>
            <!--<tr>
                <td class="textleft">{PHP.L.shop.update_linestatus}</td>
                <td class="textleft">{UPDST_LINE_STATUS}</td>
            </tr>-->
            <tr>
                <td colspan="2"class="">
                    <input type="submit" id='updOrderStatusOk' value="{PHP.L.Submit}" />
                    <input type="reset" id='updOrderStatusCancel' value="{PHP.L.Cancel}" />
                </td>
            </tr>
        </table>

    <!-- Hidden Fields -->
    <input type="hidden" name="act" value="update_o_status" />
</form>