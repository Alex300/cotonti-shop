<form method="post" id="userForm" name="enterCouponCode" action="{PHP|cot_url('shop', 'm=cart&a=setcoupon')}"
      class="form-inline margin0">
    <input type="text" name="coupon_code" size="20" maxlength="50" class="coupon" alt="{COUPON_TEXT}"
           value="{COUPON_TEXT}" 
           onblur="if(this.value=='') this.value='{COUPON_TEXT}';" onfocus="if(this.value=='{COUPON_TEXT}') this.value='';" />
    <button type="submit"><i class=" icon-gift"></i> {PHP.L.Submit}</button>
</form>