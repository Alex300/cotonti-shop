<?php
(defined('COT_CODE') && defined('COT_ADMIN')) or die('Wrong URL.');

/**
 * Admin Controller class for the Payment Methods
 *
 * @package shop
 * @subpackage admin
 * @copyright http://portal30.ru
 *
 */
class PaymentmethodController{

    /**
     * Main (index) Action.
     * Payment Methods List
     */
    public function indexAction(){
        global $adminpath, $db, $db_shop_paymethods, $db_shop_paymethods_gr, $cfg,  $L, $cot_plugins, $cot_groups,
                $cot_plugins_enabled;
        
        $adminpath[] = '&nbsp;'.$L['shop']['payment_methods'];
        //$adminhelp = $L['adm_help_page'];
        
        $so = cot_import('so', 'G', 'ALP'); // order field name without 'page_'
        $w = cot_import('w', 'G', 'ALP', 4); // order way (asc, desc)
        //$fil = cot_import('fil', 'G', 'ARR');  // filters
        
        $maxrowsperpage = $cfg['maxrowsperpage'];
        //$maxrowsperpage = 1;
        list($pg, $d, $durl) = cot_import_pagenav('d', $maxrowsperpage); //page number for pages list
        
        $so = empty($s) ? 'paym_title' : $so;   // Или order?
        $w = empty($w) ? 'ASC' : $w;
        $where = array();
        
        $list_url_path = array('m' => 'shop', 'n' => 'paymentmethod', 'fil' => $fil, 'so' => $so, 'w' => $w);
        
        // Выбрать все установленные плагины оплаты
        // Годится любой хук впринципе. Нам нужно проверить установленные
        // плагины доставки
        $payPlgs = array();
        $sqllist_rowset = array();
        if (isset($cot_plugins['shop.payment.calc_price']) && count($cot_plugins['shop.payment.calc_price']) > 0){
            foreach($cot_plugins['shop.payment.calc_price'] as $pl){
                $payPlgs[] = $pl['pl_code'];
            }
            $totallines = PaymentMethod::count($where);
            $sqllist_rowset = PaymentMethod::find($where,  $d, $maxrowsperpage, $so.' '.$w);
            $pagenav = cot_pagenav('admin', $list_url_path, $d, $totallines, $maxrowsperpage);
        }
        
        if (count($payPlgs) == 0) cot_message ($L['shop']['no_payment_plugins_installed'] , 'warning');
        
        $tpl = new XTemplate(cot_tplfile('shop.admin.paymentmethod'));

        if(count($sqllist_rowset) > 0){
            $uGrpRaw = array();
            $i = $d;
            foreach ($sqllist_rowset as $row) {
                $i++;
                if(!empty($row->user_groups)){
                    foreach($row->user_groups as $grp){
                        $uGrpRaw[$row->paym_id][] = $cot_groups[$grp];
                    }
                }
                //var_dump($cot_plugins_enabled[$row['pl_code']]);
                $tpl->assign(array(
                    'LIST_ROW_ID' => $row->paym_id,
                    'LIST_ROW_NUM' => $i,
                    'LIST_ROW_TITLE' => htmlspecialchars($row->paym_title),
                    'LIST_ROW_DESC' => htmlspecialchars($row->paym_desc),
                    'LIST_ROW_GROUPS_ARR' => $row->user_groups,
                    'LIST_ROW_GROUPS_ARR_RAW' => $uGrpRaw[$row->paym_id],
                    'LIST_ROW_VENDOR_ID' => $row->vendor_id,
                    'LIST_ROW_PUBLISHED' => $row->paym_published ? $L['Yes'] : $L['No'],
                    'LIST_ROW_SHARED' => $row->paym_shared ? $L['Yes'] : $L['No'],
                    'LIST_ROW_PLUGIN_CODE' => $row->pl_code,
                     // TODO Текст подтверждения
                    'LIST_ROW_DELETE_URL' => cot_confirm_url(cot_url('admin', 'm=shop&n=paymentmethod&a=del&id='.
                        $row->paym_id.'&'.cot_xg()), 'shop', ''),
                ));
                if ( isset($cot_plugins_enabled[$row->pl_code])){
                    $tpl->assign(array(
                       'LIST_ROW_PLUGIN_TITLE' =>  $cot_plugins_enabled[$row->pl_code]['title'],
                    ));
                }else{
                    $ext_info = $cfg['plugins_dir'].'/'.$row->pl_code.'/'.$row->pl_code.'.setup.php';
                    if (file_exists($ext_info)){
                        $info = cot_infoget($ext_info, 'COT_EXT');
                    }else{
                        $info = array('Name' => $row->pl_code);
                    }
                    $tpl->assign(array(
                       'LIST_ROW_PLUGIN_TITLE' =>  $info['Name']."<br /><b>{$L['adm_notinstalled']}</b>",
                    ));
                    cot_message(sprintf($L['shop']['plug_not_installed_but_used'], $row['pl_code'], 
                            htmlspecialchars($row['paym_title'])), 'warning');
                    
                }
                $tpl->parse('MAIN.LIST_ROW');
            }
        }
        $tpl->assign(array(
            'LIST_PAGINATION' => $pagenav['main'],
            'LIST_PAY_PLUG_INSTALLED' => (count($payPlgs) > 0) ? true : false,
            'LIST_PAGEPREV' => $pagenav['prev'],
            'LIST_PAGENEXT' => $pagenav['next'],
            'LIST_CURRENTPAGE' => $pagenav['current'],
            'LIST_TOTALLINES' => $totallines,
            'LIST_MAXPERPAGE' => $maxrowsperpage,
            'LIST_TOTALPAGES' => $pagenav['total'],
            //'LIST_ITEMS_ON_PAGE' => $jj
            'LIST_ITEMS_ON_PAGE' => $pagenav['onpage'],
            'LIST_URL' =>  cot_url('admin', $list_url_path, '', true),
            'PAGE_TITLE' => $L['shop']['payment_methods'],

        ));
        $tpl->parse('MAIN');
        return $tpl->text();
        //$productlist = $this->_productAdapter->getProductListing(false,false,false,false,true);
	}
    
    /**
     * Создание редактирование способа оплаты 
     * @todo Мультипродавец
     */
    public function editAction(){
        global $adminpath, $cfg,  $L, $cot_plugins, $usr, $cot_groups, $cot_plugins_enabled;
        
        $adminpath[] = array(cot_url('admin', array('m' => 'shop', 'n' => 'paymentmethod')), 
            $L['shop']['payment_methods']);
        
        $id = cot_import('id', 'G', 'INT'); 
        
        $item = array();
        $act = cot_import('act', 'P', 'ALP'); 
        if ($act == 'save'){
            $item['paym_id']       = cot_import('rid', 'P', 'INT');
            $item['paym_title']    = cot_import('rtitle', 'P', 'TXT'); 
            $item['paym_desc']     = cot_import('rdesc', 'P', 'TXT');
            $item['paym_published'] = cot_import('rpublished', 'P', 'BOL');
            $item['pl_code']        = cot_import('rplugincode', 'P', 'TXT');
            $item['user_groups']    = cot_import('rusergroup', 'P', 'ARR');
            $item['vendor_id']      = cot_import('rvendor', 'P', 'ARR');
            // импорт настроек (только для существующих методов)
            if ($item['paym_id'] > 0) $item['paym_params']   = shop_importPlgConig($item['pl_code']);
            $method = new PaymentMethod($item);
            $sid = $method->save($item);
            if ($sid > 0){
                cot_message($L['shop']['saved']);
                cot_redirect(cot_url('admin', array('m'=>'shop', 'n'=>'paymentmethod','a'=>'edit', 'id'=>$sid), 
                        '', true));
            }
        }
        if(!$id){
            $id = 0;
            $adminpath[] = '&nbsp;'.$L['Add'];
        }else{
            if ($act != 'save'){
                $item = PaymentMethod::getById($id);
                if($item){
                    $item = $item->toArray();
                }else{
                    $item = array();
                }
            }
            $adminpath[] = $item['paym_title']." [".$L['Edit']."]";
        }
        
        $tpl = new XTemplate(cot_tplfile('shop.admin.paymentmethod'));

        if (isset($cot_plugins['shop.payment.calc_price']) && count($cot_plugins['shop.payment.calc_price']) > 0){
            $payPlgsInstalled = true;
            $payPlgs = array();
            foreach($cot_plugins['shop.payment.calc_price'] as $pl){
                // Может быть путаница т.к. локализованное название не соотвествует названию в списке плагинов
                $payPlgs[$pl['pl_code']] = $cot_plugins_enabled[$pl['pl_code']]['title'];
                
            }
            $uGroups = array();
            foreach($cot_groups as $k => $i){
                $uGroups[$k] = $cot_groups[$k]['title'];
            }
            
            $tpl->assign(array(
                'FORM_ID' => $id,
                'FORM_TITLE' => cot_inputbox('text', 'rtitle', $item['paym_title'], array('size' => '64', 
                    'maxlength' => '255')),
                'FORM_DESC' => cot_inputbox('text', 'rdesc', $item['paym_desc'], array('size' => '64', 
                    'maxlength' => '255')),
                'FORM_PUBLISHED' => cot_radiobox( isset($item['paym_published']) ? $item['paym_published'] : 1,
                        'rpublished', array(1, 0), array($L['Yes'], $L['No'])),
                'FORM_PLUGIN_CODE' => cot_selectbox($item['pl_code'], 'rplugincode', array_keys($payPlgs), 
                        array_values($payPlgs), false),
                'FORM_USER_GROUP' => cot_selectbox($item['user_groups'], 'rusergroup[]', array_keys($uGroups),
                        array_values($uGroups), false, array('multiple'=>'multiple', 'placeholder'=>'Select...')),
                'FORM_VENDOR' => 'In development',
            ));
           
            // Вывод специфических настроек
            if ($id > 0){
                shop_renderPlgConfig($item['pl_code'], $item['paym_params'], $tpl, 'EDIT.FORM.PLUG_CONFIG');
            }
            
            $tpl->parse('EDIT.FORM');
        }else{
            $payPlgsInstalled = false;
            cot_message ($L['shop']['no_payment_plugins_installed'], 'warning');
        }
        
        $tpl->assign(array(
            'LIST_PAY_PLUG_INSTALLED' => $payPlgsInstalled,
            'PAGE_TITLE' => isset($item['paym_title']) ? $L['shop']['payment_method'].': '.
                    htmlspecialchars($item['paym_title']) : '',
        ));
        $tpl->parse('EDIT');
        return $tpl->text('EDIT');
    }

}