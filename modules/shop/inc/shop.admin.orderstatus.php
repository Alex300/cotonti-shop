<?php
(defined('COT_CODE') && defined('COT_ADMIN')) or die('Wrong URL.');

/**
 * Admin Controller class for the Order Statuses
 *
 * @package shop
 * @subpackage admin
 * @copyright http://portal30.ru
 *
 */
class OrderstatusController{
    

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
     * @todo Сортировка по os_order
     */
    public function indexAction(){
        global $adminpath, $cfg,  $L, $sys, $a;
        
        $adminpath[] = '&nbsp;'.$L['shop']['order_statuses'];
        //$adminhelp = $L['adm_help_page'];
        
        $so = cot_import('so', 'G', 'ALP'); // order field name without 'page_'
        $w = cot_import('w', 'G', 'ALP', 4); // order way (asc, desc)
        //$fil = cot_import('fil', 'G', 'ARR');  // filters
        
        $maxrowsperpage = $cfg['maxrowsperpage'];
        list($pg, $d, $durl) = cot_import_pagenav('d', $maxrowsperpage); //page number for pages list

        $list_url_path = array('m' => 'shop', 'n' => 'orderstatus');
        if(empty($so)){
            $so = 'os_title';
        }else{
            $list_url_path['so'] = $so;
        }
        if(empty($w)){
            $w = 'ASC';
        }else{
            $list_url_path['w'] = $w;
        }
        $list = OrderStatus::getList($maxrowsperpage, $d, $so, $w);
        if(!$list) $list = array();
        $totallines = OrderStatus::count();

        $pagenav = cot_pagenav('admin', $list_url_path, $d, $totallines, $maxrowsperpage);

        if($a == 'delete'){
            $urlArr = $list_url_path;
            if($pagenav['current'] > 0) $urlArr['d'] = $pagenav['current'];
            $id = cot_import('id', 'G', 'INT');
            cot_check_xg();
            $item = OrderStatus::getById($id);
            if (!$item){
                cot_error($L['No_items']." id# ".$id);
                cot_redirect(cot_url('admin', $urlArr, '', true));
            }
            $item->delete();
            cot_message($L['alreadydeletednewentry']." # $id - {$item->os_title}");
            cot_redirect(cot_url('admin', $urlArr, '', true));
        }

        $tpl = new XTemplate(cot_tplfile('shop.admin.orderstatus'));
        $i = $d+1;
        foreach ($list as $item){
            $tpl->assign(OrderStatus::generateTags($item, 'LIST_ROW_', 'admin'));
            $delUrlArr = '';
            if($item->os_id > 0 && $item->os_system == 0){
                $delUrlArr = $list_url_path;
                $delUrlArr['a'] = 'delete';
                $delUrlArr['id'] = $item->os_id;
                if($pagenav['current'] > 0) $delUrlArr['d'] = $pagenav['current'];
                $delUrlArr['x'] = $sys['xk'];
            }

            $tpl->assign(array(
                'LIST_ROW_NUM' => $i,
                'LIST_ROW_DELETE_URL' => ($delUrlArr != '') ? cot_confirm_url(cot_url('admin', $delUrlArr), 'admin') : '',
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
            'PAGE_TITLE' => $L['shop']['order_statuses'],

        ));
        $tpl->parse('MAIN');
        return $tpl->text();

	}
    
    /**
     * Создание / редактирование купона
     * @return string
     * @todo order (порядок)
     * @todo продавец
     */
    public function editAction(){
        global $adminpath, $cfg,  $L, $usr, $sys;
        
        $adminpath[] = array(cot_url('admin', array('m' => 'shop', 'n' => 'orderstatus')),
            $L['shop']['order_statuses']);
        
        $id = cot_import('id', 'G', 'INT');

        $act = cot_import('act', 'P', 'ALP');
        if(!$id){
            $id = 0;
            $adminpath[] = '&nbsp;'.$L['Add'];
        }else{
            if ($act != 'save'){
                $orderStatus = OrderStatus::getById($id);
            }
            $adminpath[] = htmlspecialchars($orderStatus->os_title);
        }

        if ($act == 'save'){
            $item = array();
            $item['os_id'] = cot_import('rid', 'P', 'INT');
            $item['vendor_id'] = Vendor::getLoggedVendorId();
            $item['os_title'] = cot_import('rtitle', 'P', 'TXT', 64);
            $item['os_code'] = cot_import('rcode', 'P', 'ALP', 1);
            $item['os_stock_handle'] = cot_import('rstock_handle', 'P', 'ALP', 1);
            $item['os_desc'] = cot_import('rdesc', 'P', 'TXT');
            //$item['os_published'] = cot_import('rpublished', 'P', 'BOL');
            $item['os_published'] = 1;  // Пока всегда опубликовано
             
            $orderStatus = new OrderStatus($item);
            if ($id = $orderStatus->save()){
                cot_message($L['shop']['saved']);
            }
            cot_redirect(cot_url('admin', array('m'=>'shop', 'n'=>'orderstatus','a'=>'edit', 'id'=>$id),
                '', true));
        }

        $shlList = array(
            'A' => $L['shop']['stock_handle_A'],
            'R' => $L['shop']['stock_handle_R'],
            'O' => $L['shop']['stock_handle_O']
        );

        $tpl = new XTemplate(cot_tplfile('shop.admin.orderstatus'));

        $delUrl = '';
        if($orderStatus->os_id > 0 && $orderStatus->os_system == 0){
            $delUrl = cot_confirm_url(cot_url('admin', 'm=shop&n=orderstatus&a=delete&id='.$orderStatus->os_id.'&'.cot_xg()), 'admin');
        }

        $tpl->assign(array(
            'FORM_ID' => $orderStatus->os_id,
            'FORM_TITLE' => cot_inputbox('text', 'rtitle', $orderStatus->os_title, array('size' => '20',
                                                                                       'maxlength' => '64')),
            'TITLE_LOCAL' => (!empty($L['shop']['order_'.$orderStatus->os_code])) ?
                                                                      $L['shop']['order_'.$orderStatus->os_code] : '',
            'FORM_CODE' => cot_inputbox('text', 'rcode', $orderStatus->os_code, array('size' => '20',
                                                                                      'maxlength' => '1')),
            'FORM_STOCK_HANDLE' => cot_selectbox($orderStatus->os_stock_handle, 'rstock_handle', array_keys($shlList),
                                                        array_values($shlList), false),
            'FORM_DESC' => cot_textarea('rdesc', $orderStatus->os_desc, 5, 70),
            'FORM_PUBLISHED' => cot_radiobox( isset($orderStatus->os_published) ? $orderStatus->os_published : 1,
                'rpublished', array(1, 0), array($L['Yes'], $L['No'])),
            'FORM_DELETE_URL' => $delUrl,
        ));


        $tpl->parse('EDIT.FORM');

        $tpl->assign(array(
            'PAGE_TITLE' => isset($orderStatus->os_id) ? htmlspecialchars($orderStatus->os_title) : $L['Add'],
        ));
        $tpl->parse('EDIT');
        return $tpl->text('EDIT');
    }

}