<?php
(defined('COT_CODE') && defined('COT_ADMIN')) or die('Wrong URL.');

//require_once($cfg['modules_dir'].DS.'shop'.DS.'models'.DS.'coupon.php');

/**
 * Admin Controller class for the Coupons
 *
 * @package shop
 * @subpackage admin
 * @copyright http://portal30.ru
 *
 */
class CouponController{
    

    /**
     * Construct the controller
     * @access public
     */
    public function __construct() {
        global $cfg;
        
      //  $this->_paymentAdapter = new PaymentMethod();

    }
    
    /**
     * Main (index) Action.
     * Coupons List
     */
    public function indexAction(){
        global $adminpath, $cfg,  $L, $sys, $a;
        
        $adminpath[] = '&nbsp;'.$L['shop']['coupons'];
        //$adminhelp = $L['adm_help_page'];
        
        $so = cot_import('so', 'G', 'ALP'); // order field name without 'page_'
        $w = cot_import('w', 'G', 'ALP', 4); // order way (asc, desc)
        //$fil = cot_import('fil', 'G', 'ARR');  // filters
        
        $maxrowsperpage = $cfg['maxrowsperpage'];
        list($pg, $d, $durl) = cot_import_pagenav('d', $maxrowsperpage); //page number for pages list

        $list_url_path = array('m' => 'shop', 'n' => 'coupon');
        if(empty($so)){
            $so = 'coupon_updated_on';
        }else{
            $list_url_path['so'] = $so;
        }
        if(empty($w)){
            $w = 'DESC';
        }else{
            $list_url_path['w'] = $w;
        }
        /** @var Coupon[] $couponList  */
        $couponList = Coupon::getList($maxrowsperpage, $d, $so, $w);
        if(!$couponList) $couponList = array();
        $totallines = Coupon::count();

        $pagenav = cot_pagenav('admin', $list_url_path, $d, $totallines, $maxrowsperpage);

        if($a == 'delete'){
            $urlArr = $list_url_path;
            if($pagenav['current'] > 0) $urlArr['d'] = $pagenav['current'];
            $id = cot_import('id', 'G', 'INT');
            cot_check_xg();
            /** @var Coupon $coupon  */
            $coupon = Coupon::getById($id);
            if (!$coupon){
                cot_error($L['shop']['coupon_notfound']. "id# ".$id);
                cot_redirect(cot_url('admin', $urlArr, '', true));
            }
            $coupon->delete();
            cot_message(sprintf($L['shop']['coupon_deleted'], $id));
            cot_redirect(cot_url('admin', $urlArr, '', true));
        }
        $vendorId = Vendor::getLoggedVendorId();
        $currId = Vendor::getCurrencyId($vendorId);
        $currency = Currency::getById($currId);

        $tpl = new XTemplate(cot_tplfile('shop.admin.coupon'));
        $i = $d+1;
        foreach ($couponList as $coupon){
            $tpl->assign(Coupon::generateTags($coupon, 'LIST_ROW_', 'admin'));
            $delUrlArr = $list_url_path;
            $delUrlArr['a'] = 'delete';
            $delUrlArr['id'] = $coupon->coupon_id;
            if($pagenav['current'] > 0) $delUrlArr['d'] = $pagenav['current'];
            $delUrlArr['x'] = $sys['xk'];

            $tpl->assign(array(
                'LIST_ROW_NUM' => $i,
                'LIST_ROW_CURRENCY_SYMBOL' => $currency->curr_symbol,
                'LIST_ROW_DELETE_URL' => cot_confirm_url(cot_url('admin', $delUrlArr), 'admin'),
            ));
            $i++;
            $tpl->parse('MAIN.LIST_ROW');
        }

        $tpl->assign(array(
            'LIST_PAGINATION' => $pagenav['main'],
            'LIST_PAGEPREV' => $pagenav['prev'],
            'LIST_PAGENEXT' => $pagenav['next'],
            'LIST_CURRENTPAGE' => $pagenav['current'],
            'LIST_TOTALLINES' => $totallines,
            'LIST_MAXPERPAGE' => $maxrowsperpage,
            'LIST_TOTALPAGES' => $pagenav['total'],
            'LIST_ITEMS_ON_PAGE' => $pagenav['onpage'],
            'LIST_URL' =>  cot_url('admin', $list_url_path, '', true),
            'PAGE_TITLE' => $L['shop']['coupons'],

        ));
        $tpl->parse('MAIN');
        return $tpl->text();

	}
    
    /**
     * Создание / редактирование купона
     * @return string
     */
    public function editAction(){
        global $adminpath, $cfg,  $L, $usr, $sys;
        
        $adminpath[] = array(cot_url('admin', array('m' => 'shop', 'n' => 'coupon')),
            $L['shop']['coupons']);
        
        $id = cot_import('id', 'G', 'INT');

//        $order = null;
        $act = cot_import('act', 'P', 'ALP');
        if(!$id){
            $id = 0;
            $adminpath[] = '&nbsp;'.$L['Add'];
        }else{
            if ($act != 'save'){
                $coupon = Coupon::getById($id);
            }
            $adminpath[] = "id#: {$coupon->coupon_id} [".$L['Edit']."]";
        }

        if ($act == 'save'){
            $item = array();
            $item['coupon_id'] = cot_import('rid', 'P', 'INT');
            $item['coupon_code'] = cot_import('rcode', 'P', 'TXT');
            $item['coupon_percent_or_total'] = cot_import('rpercent_or_total', 'P', 'ALP');
            $item['coupon_type'] = cot_import('rtype', 'P', 'ALP');
            $item['coupon_value'] = cot_import('rvalue', 'P', 'TXT');
            $item['coupon_value'] = str_replace(array(',',' '), array('.',''), $item['coupon_value']);
            $item['coupon_vdate'] = date('Y-m-d H:i:s', cot_import_date('rvdate'));
            $item['coupon_edate'] = date('Y-m-d H:i:s', cot_import_date('redate'));
            $item['coupon_min_order_total'] = cot_import('rmin_order_total', 'P', 'TXT');
            $item['coupon_min_order_total'] = str_replace(array(',',' '), array('.',''), $item['coupon_min_order_total']);
            $item['coupon_published'] = cot_import('rpublished', 'P', 'BOL');

            $coupon = new Coupon($item);

            if ($id = $coupon->save()){
                cot_message($L['shop']['saved']);
            }
            cot_redirect(cot_url('admin', array('m'=>'shop', 'n'=>'coupon','a'=>'edit', 'id'=>$id),
                '', true));
        }
        $vendorId = Vendor::getLoggedVendorId();
        $currId = Vendor::getCurrencyId($vendorId);
        $currency = Currency::getById($currId);

        $tpl = new XTemplate(cot_tplfile('shop.admin.coupon'));

        $delUrl = '';
        if($coupon->coupon_id > 0){
            $delUrl = cot_confirm_url(cot_url('admin', 'm=shop&n=coupon&a=delete&id='.$coupon->coupon_id.'&'.cot_xg()), 'admin');
        }
        $ptOptions = array(
            'percent' => $L['shop']['coupon_percent'],
            'total' => $L['shop']['coupon_total'],
        );
        $tOptions = array(
            'permanent' => $L['shop']['coupon_type_permanent'],
            'gift' => $L['shop']['coupon_type_gift'],
        );
        if(!$coupon->coupon_vdate || $coupon->coupon_vdate == '') $coupon->coupon_vdate = '0000-00-00';
        if(!$coupon->coupon_edate || $coupon->coupon_edate == '') $coupon->coupon_edate = '0000-00-00';

        $startDate = cot_date2stamp($coupon->coupon_vdate);
        $endDate = cot_date2stamp($coupon->coupon_edate);
        if($startDate <= 0) $startDate = $sys['now'];
        if($endDate < 0) $endDate = 0;

        $tpl->assign(array(
            'FORM_ID' => $coupon->coupon_id,
            'FORM_CODE' => cot_inputbox('text', 'rcode', $coupon->coupon_code, array('size' => '20',
                                                                                       'maxlength' => '32')),
            'FORM_VALUE' => cot_inputbox('text', 'rvalue', $coupon->coupon_value, array('size' => '20',
                                                                                      'maxlength' => '32')),
            'FORM_PERSENT_TOTAL' => cot_radiobox( $coupon->coupon_percent_or_total, 'rpercent_or_total',
                array_keys($ptOptions), array_values($ptOptions)),
            'FORM_TYPE' => cot_selectbox($coupon->coupon_type, 'rtype', array_keys($tOptions),
                array_values($tOptions), false),
            'FORM_MIN_TOTAL' =>  cot_inputbox('text', 'rmin_order_total', $coupon->coupon_min_order_total,
                array('size' => '20', 'maxlength' => '255')),
            'FORM_CURRENCY_SYMBOL' => $currency->curr_symbol,
            'FORM_START_DATE' => cot_selectbox_date($startDate, 'long', 'rvdate'),
            'FORM_END_DATE' => cot_selectbox_date($endDate, 'long', 'redate'),
            'FORM_PUBLISHED' => cot_radiobox( isset($coupon->coupon_published) ? $coupon->coupon_published : 1,
                'rpublished', array(1, 0), array($L['Yes'], $L['No'])),
            'FORM_DELETE_URL' => $delUrl,
        ));


        $tpl->parse('EDIT.FORM');

        $tpl->assign(array(
            'PAGE_TITLE' => isset($coupon->coupon_id) ? $L['shop']['cart_edit_coupon'].": id#:{$coupon->coupon_id}" :
                $L['shop']['coupon_new'],
        ));
        $tpl->parse('EDIT');
        return $tpl->text('EDIT');
    }

}