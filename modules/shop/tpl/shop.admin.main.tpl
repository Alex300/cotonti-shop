<!-- BEGIN: MAIN -->

<!-- IF {PAGE_TITLE} -->
<h2 class="tags"><img src="{PHP.cfg.modules_dir}/shop/shop.png" style="vertical-align: middle;" /> {PAGE_TITLE}</h2>
<!-- ENDIF -->


<ul id="shop_cpanel" class="body">
    <li>
        <a href="{PHP|cot_url('admin', 'm=shop&n=product')}">
            <img alt="{PHP.L.shop.products}" src="{PHP.cfg.modules_dir}/shop/tpl/images/products.png">
            <span>{PHP.L.shop.products}</span>
        </a>
    </li>
    <li>
        <a href="{PHP|cot_url('admin', 'm=shop&n=shipmentmethod')}">
            <img alt="{PHP.L.shop.shipment_methods}" src="{PHP.cfg.modules_dir}/shop/tpl/images/shipment.png">
            <span>{PHP.L.shop.shipment_methods}</span>
        </a>
    </li>
    <li>
        <a href="{PHP|cot_url('admin', 'm=shop&n=paymentmethod')}">
            <img alt="{PHP.L.shop.payment_methods}" src="{PHP.cfg.modules_dir}/shop/tpl/images/payment.png">
            <span>{PHP.L.shop.payment_methods}</span>
        </a>
    </li>
    <li>
        <a href="{PHP|cot_url('admin', 'm=shop&n=vendor&a=edit')}">
            <img alt="{PHP.L.shop.shop}" src="{PHP.cfg.modules_dir}/shop/shop.png">
            <span>{PHP.L.shop.shop}</span>
        </a>
    </li>
    <li>
        <a href="{PHP|cot_url('admin', 'm=shop&n=calc')}" title="{PHP.L.shop.calc}">
            <img alt="{PHP.L.shop.calc}" src="{PHP.cfg.modules_dir}/shop/tpl/images/calc.png">
            <span>{PHP.L.shop.calc}</span>
        </a>
    </li>
    <li>
        <a href="{PHP|cot_url('admin', 'm=shop&n=order')}">
            <img alt="{PHP.L.shop.orders}" src="{PHP.cfg.modules_dir}/shop/tpl/images/orders.png">
            <span>{PHP.L.shop.orders}</span>
        </a>
    </li>
    <li>
        <a href="{PHP|cot_url('admin', 'm=shop&n=orderstatus')}">
            <img alt="{PHP.L.shop.order_statuses}" src="{PHP.cfg.modules_dir}/shop/tpl/images/orderstatus.png">
            <span>{PHP.L.shop.order_statuses}</span>
        </a>
    </li>
    <li>
        <a href="{PHP|cot_url('admin', 'm=shop&n=coupon')}">
            <img alt="{PHP.L.shop.coupons}" src="{PHP.cfg.modules_dir}/shop/tpl/images/coupon.png">
            <span>{PHP.L.shop.coupons}</span>
        </a>
    </li>
    <li>
        <a href="{PHP|cot_url('admin', 'm=shop&n=currency')}">
            <img alt="{PHP.L.shop.currency}" src="{PHP.cfg.modules_dir}/shop/tpl/images/currency.png">
            <span>{PHP.L.shop.currency}</span>
        </a>
    </li>

    <!-- IF {PHP.L.shop.documentation_url} != '' -->
    <li>
        <a href="{PHP.L.shop.documentation_url}" target="_blank">
            <img alt="{PHP.L.shop.documentation}" src="{PHP.cfg.system_dir}/admin/tpl/img/help.png">
            <span>{PHP.L.shop.documentation}</span>
        </a>
    </li>
    <!-- ENDIF -->
</ul>

<div class="clear" style="height: 1px;"></div>

<!-- END: MAIN -->