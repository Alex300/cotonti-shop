<?php
(defined('COT_CODE') && defined('COT_ADMIN')) or die('Wrong URL.');

/**
 * Admin Controller class for the Currency
 *
 * @package shop
 * @subpackage admin
 * @copyright http://portal30.ru
 *
 */
class CurrencyController{
    

    /**
     * Construct the controller
     * @access public
     */
    public function __construct() {
        global $cfg;

    }
    
    /**
     * Main (index) Action.
     * Coupons List
     */
    public function indexAction(){
        global $adminpath, $cfg,  $L, $sys, $a;
        
        $adminpath[] = '&nbsp;'.$L['shop']['currency'];
        //$adminhelp = $L['adm_help_page'];
        
        $so = cot_import('so', 'G', 'ALP'); // order field name without 'page_'
        $w = cot_import('w', 'G', 'ALP', 4); // order way (asc, desc)
        //$fil = cot_import('fil', 'G', 'ARR');  // filters
        
        $maxrowsperpage = $cfg['maxrowsperpage'];
        list($pg, $d, $durl) = cot_import_pagenav('d', $maxrowsperpage); //page number for pages list

        $list_url_path = array('m' => 'shop', 'n' => 'currency');
        if(empty($so)){
            $so = 'curr_title';
        }else{
            $list_url_path['so'] = $so;
        }
        if(empty($w)){
            $w = 'ASC';
        }else{
            $list_url_path['w'] = $w;
        }
        $vendorId = Vendor::getLoggedVendorId();
        $cond = "vendor_id={$vendorId} OR curr_shared=1";
        /** @var Currency[] $list  */
        $list = Currency::find($cond, $maxrowsperpage, $d, $so, $w);
        if(!$list) $list = array();
        $totallines = Currency::count($cond);

        $pagenav = cot_pagenav('admin', $list_url_path, $d, $totallines, $maxrowsperpage);

        if($a == 'delete'){
            $urlArr = $list_url_path;
            if($pagenav['current'] > 0) $urlArr['d'] = $pagenav['current'];
            $id = cot_import('id', 'G', 'INT');
            cot_check_xg();
            /** @var Coupon $coupon  */
            $item = Currency::getById($id);
            if (!$item){
                cot_error($L['No_items']." id# ".$id);
                cot_redirect(cot_url('admin', $urlArr, '', true));
            }
            $item->delete();
            cot_message($L['alreadydeletednewentry']." # $id - {$item->curr_title}");
            cot_redirect(cot_url('admin', $urlArr, '', true));
        }

        $tpl = new XTemplate(cot_tplfile('shop.admin.currency'));
        $i = $d+1;
        foreach ($list as $item){
            $tpl->assign(Currency::generateTags($item, 'LIST_ROW_', 'admin'));
            $delUrlArr = $list_url_path;
            $delUrlArr['a'] = 'delete';
            $delUrlArr['id'] = $item->curr_id;
            if($pagenav['current'] > 0) $delUrlArr['d'] = $pagenav['current'];
            $delUrlArr['x'] = $sys['xk'];

            $tpl->assign(array(
                'LIST_ROW_NUM' => $i,
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
            'PAGE_TITLE' => $L['shop']['currency'],

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
        
        $adminpath[] = array(cot_url('admin', array('m' => 'shop', 'n' => 'currency')),
            $L['shop']['currency']);
        
        $id = cot_import('id', 'G', 'INT');

        $act = cot_import('act', 'P', 'ALP');
        if(!$id){
            $id = 0;
            $adminpath[] = '&nbsp;'.$L['Add'];
        }else{
            if ($act != 'save'){
                $currency = Currency::getById($id);
            }
            $adminpath[] = $L['shop']['currency_edit'].": ".htmlspecialchars($currency->curr_title);
        }

        if ($act == 'save'){
            $item = array();
            $item['curr_id'] = cot_import('rid', 'P', 'INT');
            $item['vendor_id'] = Vendor::getLoggedVendorId();
            $item['curr_title'] = cot_import('rtitle', 'P', 'TXT', 64);
            $item['curr_code_2'] = cot_import('rcode_2', 'P', 'TXT', 2);
            $item['curr_code_3'] = cot_import('rcode_3', 'P', 'TXT', 3);
            $item['curr_numeric_code'] = cot_import('rnumeric_code', 'P', 'INT', 4);
            $item['curr_exchange_rate'] = cot_import('rexchange_rate', 'P', 'NUM');
            $item['curr_symbol'] = cot_import('rsymbol', 'P', 'TXT', 4);
            $item['curr_decimal_place'] = cot_import('rdecimal_place', 'P', 'TXT', 4);
            $item['curr_decimal_symbol'] = cot_import('rdecimal_symbol', 'P', 'TXT', 4);
            $item['curr_thousands'] = cot_import('rthousands', 'P', 'TXT', 4);
            $item['curr_positive_style'] = cot_import('rpositive_style', 'P', 'HTM', 64);
            $item['curr_negative_style'] = cot_import('rnegative_style', 'P', 'HTM', 64);
            $item['curr_published'] = cot_import('rpublished', 'P', 'BOL');

            $currency = new Currency($item);

            if ($id = $currency->save()){
                cot_message($L['shop']['saved']);
            }
            cot_redirect(cot_url('admin', array('m'=>'shop', 'n'=>'currency','a'=>'edit', 'id'=>$id),
                '', true));
        }

        $tpl = new XTemplate(cot_tplfile('shop.admin.currency'));

        $delUrl = '';
        if($currency->curr_id > 0){
            $delUrl = cot_confirm_url(cot_url('admin', 'm=shop&n=currency&a=delete&id='.$currency->curr_id.'&'.cot_xg()), 'admin');
        }

        $tpl->assign(array(
            'FORM_ID' => $currency->curr_id,
            'FORM_TITLE' => cot_inputbox('text', 'rtitle', $currency->curr_title, array('size' => '20',
                                                                                       'maxlength' => '32')),
            'FORM_EXCHANGE_RATE' => cot_inputbox('text', 'rexchange_rate', $currency->curr_exchange_rate, array('size' => '20',
                                                                                                  'maxlength' => '32')),
            'FORM_CODE_2' => cot_inputbox('text', 'rcode_2', $currency->curr_code_2, array('size' => '20',
                                                                                      'maxlength' => '2')),
            'FORM_CODE_3' => cot_inputbox('text', 'rcode_3', $currency->curr_code_3, array('size' => '20',
                                                                                         'maxlength' => '3')),
            'FORM_NUMERIC_CODE' => cot_inputbox('text', 'rnumeric_code', $currency->curr_numeric_code, array('size' => '20',
                                                                                           'maxlength' => '4')),
            'FORM_SYMBOL' => cot_inputbox('text', 'rsymbol', $currency->curr_symbol, array('size' => '20',
                                                                                           'maxlength' => '4')),
            'FORM_DECIMAL_PLACE' => cot_inputbox('text', 'rdecimal_place', $currency->curr_decimal_place, array('size' => '20',
                                                                                             'maxlength' => '4')),
            'FORM_DECIMAL_SYMBOL' => cot_inputbox('text', 'rdecimal_symbol', $currency->curr_decimal_symbol,
                                                                            array('size' => '20', 'maxlength' => '4')),
            'FORM_THOUSANDS' => cot_inputbox('text', 'rthousands', $currency->curr_thousands, array('size' => '20',
                                                                                                   'maxlength' => '4')),
            'FORM_POSITIVE_STYLE' => cot_inputbox('text', 'rpositive_style', $currency->curr_positive_style,
                                                                           array('size' => '20',  'maxlength' => '64')),
            'FORM_NEGATIVE_STYLE' => cot_inputbox('text', 'rnegative_style', $currency->curr_negative_style,
                                                                        array('size' => '20',  'maxlength' => '64')),
            'FORM_PUBLISHED' => cot_radiobox( isset($currency->curr_published) ? $currency->curr_published : 1,
                'rpublished', array(1, 0), array($L['Yes'], $L['No'])),
            'FORM_DELETE_URL' => $delUrl,
        ));


        $tpl->parse('EDIT.FORM');

        $tpl->assign(array(
            'PAGE_TITLE' => isset($currency->curr_id) ? $L['shop']['currency_edit'].": ".htmlspecialchars($currency->curr_title) :
                $L['shop']['currency_new'],
        ));
        $tpl->parse('EDIT');
        return $tpl->text('EDIT');
    }

}